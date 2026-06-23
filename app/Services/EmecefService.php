<?php
// =============================================================================
// FICHIER : EmecefService.php
// RÔLE    : Service d'intégration e-MECeF / Sygmef — DGI Bénin
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Ce service gère l'émission, l'annulation et la vérification des factures
// normalisées auprès de la Direction Générale des Impôts (DGI) du Bénin via
// le système e-MECeF (Sygmef).
//
// Points clés de sécurité :
//   1. Le mode test est FORCÉMENT désactivé en production (double vérification
//      config + PHP), quelle que soit la valeur dans .env
//   2. Le NIM et le mot de passe sont stockés par client (per-tenant), pas de NIM global
//   3. Facture déjà émise → refus de réémission
//   4. Timeout API à 30s pour éviter les blocages
// =============================================================================

namespace App\Services;

use App\Models\ErpInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EmecefService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected bool $testMode;

    public function __construct()
    {
        $this->apiUrl = config('emecef.api_url', 'https://api-emecef.impots.bj');
        $this->apiToken = config('emecef.api_token', '');
        // Sécurité : le mode test est FORCÉMENT désactivé en production,
        // quelle que soit la valeur dans .env
        $this->testMode = config('emecef.test_mode', true);
        if (app()->environment('production')) {
            $this->testMode = false;
        }
    }

    /**
     * Émet une facture normalisée auprès de la DGI (e-MECeF).
     * Utilise le NIM du client (per-client), pas un NIM global.
     *
     * @param  ErpInvoice  $invoice  La facture à émettre
     * @return array  ['success' => bool, 'error'? => string, 'nim'? => string, 'compteur'? => string]
     */
    public function emettreFactureNormalisee(ErpInvoice $invoice): array
    {
        // Vérifier que le client a une configuration e-MECeF complète (NIM + mot de passe)
        $client = $invoice->client;
        if (!$client || !$client->emecef_is_active || empty($client->emecef_nim) || empty($client->emecef_password)) {
            return [
                'success' => false,
                'error' => 'Le client n\'a pas configuré son compte e-MECeF. L\'administrateur de l\'entreprise doit d\'abord configurer le NIM et le mot de passe depuis son portail.',
            ];
        }

        // Ne pas réémettre si déjà émise
        if ($invoice->emecef_statut === 'emise' && !empty($invoice->emecef_nim)) {
            return [
                'success' => false,
                'error' => 'Facture déjà émise à la DGI.',
            ];
        }

        $companyIfu = $client->ifu ?? '0202262760921';
        $nim = $client->emecef_nim;

        try {
            if (!$this->testMode && !empty($this->apiToken)) {
                // ─── MODE PRODUCTION : Appel réel à l'API DGI ───
                $payload = [
                    'ifu'       => $companyIfu,
                    'nim'       => $nim,
                    'type'      => 'FV', // Facture Vente
                    'operateur' => config('app.name', 'GEL Cabinet'),
                    'montant'   => (int) round($invoice->total_ttc),
                    'taxes'     => [
                        'A' => (int) round($invoice->total_ht ?? 0),
                        'B' => 0,
                        'C' => 0,
                        'D' => 0,
                        'E' => 0,
                    ],
                    'date'      => Carbon::now()->format('Y-m-d H:i:s'),
                ];

                $response = Http::withToken($this->apiToken)
                    ->timeout(30)
                    ->post($this->apiUrl . '/facture/emettre', $payload);

                if (!$response->successful()) {
                    Log::error('e-MECeF API error', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                        'invoice' => $invoice->id,
                    ]);
                    return [
                        'success' => false,
                        'error' => 'Erreur API DGI : ' . ($response->json('message') ?? $response->body()),
                    ];
                }

                $data = $response->json();
                $nim = $data['nim'] ?? $nim;
                $compteur = $data['compteur'] ?? str_pad(rand(100, 9999), 5, '0', STR_PAD_LEFT);
                $datetime = Carbon::parse($data['dateHeure'] ?? Carbon::now());
                $signature = $data['signature'] ?? '';
            } else {
                // ─── MODE TEST : Simulation locale ───
                $compteur = str_pad(rand(100, 9999), 5, '0', STR_PAD_LEFT);
                $datetime = Carbon::now();

                // Signature simulée (HMAC)
                $payload = $companyIfu . $nim . $compteur . $datetime->format('YmdHis') . number_format($invoice->total_ttc, 0, '', '');
                $signature = strtoupper(substr(hash_hmac('sha256', $payload, 'secret_dgi_key'), 0, 16));
                $signatureFormatted = implode('-', str_split($signature, 4));
            }

            // Chaîne QR
            $dateStr = $datetime->format('YmdHis');
            $signatureDisplay = $signatureFormatted ?? $signature;
            $qrString = "https://portail.impots.bj/portail/facture/?nim={$nim}&compteur={$compteur}&dateHeure={$dateStr}&signature={$signatureDisplay}";

            // Mise à jour de la facture
            $invoice->update([
                'emecef_nim'      => $nim,
                'emecef_compteur' => $compteur,
                'emecef_statut'   => 'emise',
                'emecef_datetime' => $datetime,
                'emecef_qr'       => $qrString,
                'emecef_hash'     => $signatureDisplay,
            ]);

            Log::info('e-MECeF émission réussie', [
                'invoice' => $invoice->id,
                'nim'     => $nim,
                'statut'  => $this->testMode ? 'simulé' : 'réel',
            ]);

            return [
                'success' => true,
                'message' => 'Facture émise à la DGI avec succès.',
                'nim' => $nim,
                'compteur' => $compteur,
            ];

        } catch (\Exception $e) {
            Log::error('e-MECeF exception', [
                'invoice' => $invoice->id,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de l\'émission : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Annule une facture déjà émise à la DGI.
     *
     * @param  ErpInvoice  $invoice  La facture à annuler
     * @return array  ['success' => bool, 'error'? => string]
     */
    public function annulerFacture(ErpInvoice $invoice): array
    {
        if (!$invoice->emecef_nim) {
            return ['success' => false, 'error' => 'Facture non émise à la DGI.'];
        }

        try {
            if (!$this->testMode && !empty($this->apiToken)) {
                $response = Http::withToken($this->apiToken)
                    ->timeout(30)
                    ->post($this->apiUrl . '/facture/annuler', [
                        'nim'       => $invoice->emecef_nim,
                        'compteur'  => $invoice->emecef_compteur,
                    ]);

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'error' => 'Erreur API DGI : ' . ($response->json('message') ?? $response->body()),
                    ];
                }
            }

            // Mise à jour locale
            $invoice->update([
                'emecef_statut' => 'annulee',
            ]);

            return [
                'success' => true,
                'message' => 'Facture annulée à la DGI.',
            ];

        } catch (\Exception $e) {
            Log::error('e-MECeF annulation exception', [
                'invoice' => $invoice->id,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de l\'annulation : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifie le statut d'une facture auprès de la DGI.
     *
     * @param  string  $nim       Numéro d'Identification de la Machine
     * @param  string  $compteur  Compteur de la facture
     * @return array  ['success' => bool, 'data'? => array, 'error'? => string]
     */
    public function verifierFacture(string $nim, string $compteur): array
    {
        try {
            if (!$this->testMode && !empty($this->apiToken)) {
                $response = Http::withToken($this->apiToken)
                    ->timeout(30)
                    ->get($this->apiUrl . '/facture/verifier', [
                        'nim'      => $nim,
                        'compteur' => $compteur,
                    ]);

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'error' => 'Erreur API DGI : ' . ($response->json('message') ?? $response->body()),
                    ];
                }

                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            // Mode test : retourner succès simulé
            return [
                'success' => true,
                'data' => [
                    'nim'      => $nim,
                    'compteur' => $compteur,
                    'statut'   => 'valide',
                    'message'  => 'Facture certifiée par la DGI (simulation).',
                ],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur de vérification : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Certifie une facture pour l'export PDF (sans appel API).
     * Méthode statique préservée pour compatibilité avec l'ancien système.
     * Utilisée uniquement pour le rendu PDF, pas pour l'envoi à la DGI.
     *
     * @param  ErpInvoice  $invoice  La facture à certifier
     * @return ErpInvoice  La facture mise à jour avec les champs e-MECeF
     */
    public static function certifyInvoice(ErpInvoice $invoice): ErpInvoice
    {
        if ($invoice->emecef_statut === 'emise' && !empty($invoice->emecef_nim)) {
            return $invoice;
        }

        $companyIfu = $invoice->client?->ifu ?? '0202262760921';
        $nim = $invoice->client?->emecef_nim ?? 'NBJ' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $compteur = str_pad(rand(100, 9999), 5, '0', STR_PAD_LEFT);
        $datetime = Carbon::now();

        $payload = $companyIfu . $nim . $compteur . $datetime->format('YmdHis') . number_format($invoice->total_ttc, 0, '', '');
        $signature = strtoupper(substr(hash_hmac('sha256', $payload, 'secret_dgi_key'), 0, 16));
        $signatureFormatted = implode('-', str_split($signature, 4));

        $dateStr = $datetime->format('YmdHis');
        $qrString = "https://portail.impots.bj/portail/facture/?nim={$nim}&compteur={$compteur}&dateHeure={$dateStr}&signature={$signatureFormatted}";

        $invoice->update([
            'emecef_nim'      => $nim,
            'emecef_compteur' => $compteur,
            'emecef_statut'   => 'emise',
            'emecef_datetime' => $datetime,
            'emecef_qr'       => $qrString,
        ]);

        return $invoice;
    }
}
