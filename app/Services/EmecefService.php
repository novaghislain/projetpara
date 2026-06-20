<?php

namespace App\Services;

use App\Models\ErpInvoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'intégration e-MECeF / Sygmef — DGI Bénin
 *
 * Permet d'émettre des factures normalisées via l'API Sygmef
 * et d'enregistrer les NIU, hash et QR codes sur les factures.
 */
class EmecefService
{
    /**
     * Émettre une facture normalisée auprès de la DGI.
     */
    public function emettreFactureNormalisee(ErpInvoice $invoice): array
    {
        // ── Mode test : simule une émission réussie sans appeler l'API DGI ──
        if (config('emecef.test_mode', true)) {
            Log::info('e-MECeF test mode — émission simulée', ['invoice' => $invoice->id]);
            $invoice->update([
                'emecef_nim'      => config('emecef.nim', 'TESTNIM000001'),
                'emecef_compteur' => random_int(10000, 99999),
                'emecef_hash'     => hash('sha256', $invoice->id . time()),
                'emecef_qr'       => 'data:image/png;base64,' . base64_encode('TEST-QR-' . $invoice->id),
                'emecef_statut'   => 'emise',
                'emecef_datetime' => now(),
            ]);
            return [
                'success'  => true,
                'nim'      => config('emecef.nim', 'TESTNIM000001'),
                'compteur' => random_int(10000, 99999),
                'hash'     => hash('sha256', $invoice->id . time()),
                'qr_code'  => 'data:image/png;base64,TEST-QR-' . $invoice->id,
                'test_mode' => true,
            ];
        }

        $payload = $this->buildPayload($invoice);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('emecef.api_token'),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post(config('emecef.api_url') . '/emcf/emettre', $payload);

            if (!$response->successful()) {
                Log::error('e-MECeF API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'invoice' => $invoice->id,
                ]);
                return [
                    'success' => false,
                    'error'   => $response->json('message') ?? 'Erreur API e-MECeF',
                    'status'  => $response->status(),
                ];
            }

            $data = $response->json();

            // Enregistrer les informations e-MECeF sur la facture
            $invoice->update([
                'emecef_nim'      => $data['nim'] ?? config('emecef.nim'),
                'emecef_compteur' => $data['counters']['value'] ?? null,
                'emecef_hash'     => $data['signature']['value'] ?? null,
                'emecef_qr'       => $data['qrCode'] ?? null,
                'emecef_statut'   => 'emise',
                'emecef_datetime' => now(),
            ]);

            return [
                'success'  => true,
                'nim'      => $data['nim'] ?? null,
                'compteur' => $data['counters']['value'] ?? null,
                'hash'     => $data['signature']['value'] ?? null,
                'qr_code'  => $data['qrCode'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('e-MECeF exception', [
                'message' => $e->getMessage(),
                'invoice' => $invoice->id,
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Annuler une facture normalisée auprès de la DGI.
     */
    public function annulerFacture(ErpInvoice $invoice): array
    {
        if (config('emecef.test_mode', true)) {
            $invoice->update(['emecef_statut' => 'annulee']);
            return ['success' => true, 'test_mode' => true];
        }
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('emecef.api_token'),
                'Content-Type'  => 'application/json',
            ])->post(config('emecef.api_url') . '/emcf/annuler', [
                'nim'     => $invoice->emecef_nim,
                'compteur' => $invoice->emecef_compteur,
                'motif'   => 'Annulation facture ' . $invoice->invoice_number,
            ]);

            if ($response->successful()) {
                $invoice->update(['emecef_statut' => 'annulee']);
                return ['success' => true];
            }

            return [
                'success' => false,
                'error'   => $response->json('message') ?? 'Erreur annulation',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier le statut d'une facture auprès de la DGI.
     */
    public function verifierFacture(string $nim, string $compteur): array
    {
        if (config('emecef.test_mode', true)) {
            return ['success' => true, 'data' => ['statut' => 'emise', 'nim' => $nim, 'compteur' => $compteur], 'test_mode' => true];
        }
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('emecef.api_token'),
            ])->get(config('emecef.api_url') . '/emcf/verifier', [
                'nim'      => $nim,
                'compteur' => $compteur,
            ]);

            return $response->successful()
                ? ['success' => true, 'data' => $response->json()]
                : ['success' => false, 'error' => $response->json('message')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Construire le payload de la facture conforme à l'API Sygmef.
     */
    private function buildPayload(ErpInvoice $invoice): array
    {
        $client = $invoice->client;
        $aib = $this->calculerAib($invoice);

        return [
            'ifu'   => $client?->ifu ?? '0000000000001',
            'type'  => $this->determinerTypeFacture($invoice),
            'aib'   => $aib,
            'client' => [
                'ifu'  => $client?->ifu ?? '0000000000001',
                'nom'  => $client?->name ?? $invoice->client_name ?? 'Client',
                'type' => $this->determinerTypePersonne($client),
            ],
            'items' => $invoice->lineItems->map(fn ($line) => [
                'nom'          => $line->description ?? $line->product_name ?? 'Service',
                'qte'          => (int) ($line->quantity ?? 1),
                'prixUnitaire' => (float) ($line->unit_price ?? $line->unit_price ?? 0),
                'taxGroup'     => 'B', // B = TVA 18% standard
            ])->toArray(),
            'paiement' => [
                'mode'    => 'MMO', // Par défaut Mobile Money
                'montant' => (float) $invoice->total_ttc,
            ],
        ];
    }

    /**
     * Calculer l'AIB (Acompte sur Impôt sur les Bénéfices).
     * 1% pour les contribuables DGE/DME, 5% pour les CSI.
     */
    private function calculerAib(ErpInvoice $invoice): float
    {
        $client = $invoice->client;
        $regime = $client?->regime_fiscal ?? 'csi';
        $taux = match ($regime) {
            'dge', 'dme' => 0.01,
            'csi'        => 0.05,
            default      => 0.00,
        };
        return round($invoice->total_ht * $taux, 2);
    }

    private function determinerTypeFacture(ErpInvoice $invoice): string
    {
        $type = strtoupper($invoice->type ?? 'FV');
        return in_array($type, ['FV', 'FA', 'AV', 'EA']) ? $type : 'FV';
    }

    private function determinerTypePersonne($client): string
    {
        // PP = Personne Physique, PM = Personne Morale
        if (!$client) return 'PP';
        $type = $client->type_entreprise ?? $client->type ?? 'PP';
        return in_array($type, ['PP', 'PM']) ? $type : 'PP';
    }
}
