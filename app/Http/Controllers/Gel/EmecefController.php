<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ErpInvoice;
use App\Models\Notification;
use App\Services\EmecefService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur d'interface avec e-MECeF (DG).
 * Gère l'émission, l'annulation et la vérification des factures
 * normalisées auprès de la Direction Générale des Impôts via le service EmecefService.
 */
class EmecefController extends Controller
{
    /**
     * Constructeur avec injection du service e-MECeF.
     *
     * @param EmecefService $emecef Le service de communication avec la DGI
     */
    public function __construct(
        private readonly EmecefService $emecef
    ) {}

    /**
     * Émet une facture normalisée auprès de la DGI via e-MECeF.
     * Vérifie que la facture n'a pas déjà été émise.
     * En cas de succès, crée les notifications pour l'admin et le comptable.
     *
     * @param ErpInvoice $invoice La facture à émettre
     * @return JsonResponse
     */
    public function emitInvoice(ErpInvoice $invoice): JsonResponse
    {
        // Vérifier que la facture n'est pas déjà émise
        if ($invoice->emecef_statut === 'emise') {
            return response()->json(['success' => false, 'error' => 'Facture déjà émise à la DGI.'], 422);
        }

        $result = $this->emecef->emettreFactureNormalisee($invoice);

        if ($result['success']) {
            try {
                $this->createEmissionNotifications($invoice, !empty($result['simulation']));
            } catch (\Exception $e) {
                // Les notifications sont secondaires — ne pas bloquer l'émission
                Log::warning('e-MECeF notification error', [
                    'invoice' => $invoice->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Annule une facture déjà émise auprès de la DGI.
     * Vérifie que la facture possède un NIM (numéro d'identification fiscale).
     *
     * @param ErpInvoice $invoice La facture à annuler
     * @return JsonResponse
     */
    public function cancelInvoice(ErpInvoice $invoice): JsonResponse
    {
        if (!$invoice->emecef_nim) {
            return response()->json(['success' => false, 'error' => 'Facture non émise à la DGI.'], 422);
        }

        $result = $this->emecef->annulerFacture($invoice);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Vérifie le statut d'une facture auprès de la DGI.
     * Nécessite le NIM et le compteur e-MECeF.
     *
     * @param ErpInvoice $invoice La facture à vérifier
     * @return JsonResponse
     */
    public function verifyInvoice(ErpInvoice $invoice): JsonResponse
    {
        if (!$invoice->emecef_nim || !$invoice->emecef_compteur) {
            return response()->json(['success' => false, 'error' => 'Facture non émise à la DGI.'], 422);
        }

        $result = $this->emecef->verifierFacture(
            $invoice->emecef_nim,
            $invoice->emecef_compteur
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Crée les notifications après une émission e-MECeF réussie.
     * 1. Notification pour l'admin de l'entreprise
     * 2. Notification pour le comptable qui a émis
     *
     * Le contenu des messages distingue explicitement une émission réelle
     * (transmise à la DGI) d'une SIMULATION (mode test) : aucune notification
     * ne prétend qu'une facture a été transmise à la DGI si elle ne l'a pas été.
     *
     * @param ErpInvoice $invoice La facture émise
     * @param bool       $simulation Émission simulée (mode test) ?
     * @return void
     */
    private function createEmissionNotifications(ErpInvoice $invoice, bool $simulation = false): void
    {
        $client = $invoice->client;

        if ($simulation) {
            // ─── MODE TEST : notification honnête, aucune mention "DGI" ───
            // 1. Notification pour l'admin entreprise
            if ($client) {
                $companyAdmin = $client->companyAdmins()->first();
                if ($companyAdmin) {
                    Notification::create([
                        'user_id' => $companyAdmin->id,
                        'type'    => 'emecef_simulation',
                        'title'   => 'Facture simulée e-MECeF (mode test)',
                        'message' => "La facture {$invoice->invoice_number} a été émise en SIMULATION (mode test). Aucun échange avec la DGI — à confirmer avant facturation réelle.",
                        'data'    => [
                            'invoice_id'   => $invoice->id,
                            'simulation'   => true,
                            'url'          => '/company/invoices',
                        ],
                    ]);
                }
            }

            // 2. Notification pour le comptable émetteur (copie)
            if ($invoice->created_by) {
                Notification::create([
                    'user_id' => $invoice->created_by,
                    'type'    => 'emecef_simulation',
                    'title'   => 'Facture simulée — copie disponible',
                    'message' => "La facture {$invoice->invoice_number} a été émise en SIMULATION (mode test). Aucun échange avec la DGI. Copie disponible dans votre tableau de bord.",
                    'data'    => [
                        'invoice_id' => $invoice->id,
                        'simulation' => true,
                        'url'        => '/gel/erp/invoices',
                    ],
                ]);
            }

            return;
        }

        // ─── ÉMISSION RÉELLE transmise à la DGI ───
        // 1. Notification pour l'admin entreprise
        if ($client) {
            $companyAdmin = $client->companyAdmins()->first();
            if ($companyAdmin) {
                Notification::create([
                    'user_id' => $companyAdmin->id,
                    'type'    => 'emecef_emise',
                    'title'   => 'Facture émise à la DGI',
                    'message' => "La facture {$invoice->invoice_number} a été transmise à la DGI avec succès.",
                    'data'    => [
                        'invoice_id' => $invoice->id,
                        'url'        => '/company/invoices',
                    ],
                ]);
            }
        }

        // 2. Notification pour le comptable émetteur (copie)
        if ($invoice->created_by) {
            Notification::create([
                'user_id' => $invoice->created_by,
                'type'    => 'emecef_emise',
                'title'   => 'Facture transmise — copie disponible',
                'message' => "La facture {$invoice->invoice_number} a été transmise à la DGI. Copie disponible dans votre tableau de bord.",
                'data'    => [
                    'invoice_id' => $invoice->id,
                    'url'        => '/gel/erp/invoices',
                ],
            ]);
        }
    }
}
