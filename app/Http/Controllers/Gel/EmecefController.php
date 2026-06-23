<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ErpInvoice;
use App\Models\Notification;
use App\Services\EmecefService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EmecefController extends Controller
{
    public function __construct(
        private readonly EmecefService $emecef
    ) {}

    public function emitInvoice(ErpInvoice $invoice): JsonResponse
    {
        if ($invoice->emecef_statut === 'emise') {
            return response()->json(['success' => false, 'error' => 'Facture déjà émise à la DGI.'], 422);
        }

        $result = $this->emecef->emettreFactureNormalisee($invoice);

        if ($result['success']) {
            try {
                $this->createEmissionNotifications($invoice);
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

    public function cancelInvoice(ErpInvoice $invoice): JsonResponse
    {
        if (!$invoice->emecef_nim) {
            return response()->json(['success' => false, 'error' => 'Facture non émise à la DGI.'], 422);
        }

        $result = $this->emecef->annulerFacture($invoice);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

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
     */
    private function createEmissionNotifications(ErpInvoice $invoice): void
    {
        $client = $invoice->client;

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
