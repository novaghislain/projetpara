<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ErpInvoice;
use App\Services\EmecefService;
use Illuminate\Http\JsonResponse;

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
}
