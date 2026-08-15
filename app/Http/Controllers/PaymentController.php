<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Payments\MtnMomoService;
use App\Models\Gel\Client;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected MtnMomoService $momoService;

    public function __construct(MtnMomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    /**
     * Initie un paiement via MTN MoMo
     */
    public function initiateMomoPayment(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:100',
            'client_id' => 'required|exists:gel_clients,id'
        ]);

        $client = Client::findOrFail($request->client_id);
        
        $referenceId = $this->momoService->requestToPay(
            phoneNumber: $request->phone,
            amount: $request->amount,
            currency: 'XOF',
            externalId: 'INV-' . time(),
            payerMessage: "Paiement Abonnement " . $client->nom_entreprise
        );

        if (!$referenceId) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'initiation du paiement.'], 500);
        }

        // On pourrait stocker le referenceId dans une table `payments` pour suivre son statut
        return response()->json([
            'success' => true, 
            'reference_id' => $referenceId,
            'message' => 'Demande de paiement envoyée sur le téléphone.'
        ]);
    }

    /**
     * Webhook appelé par MTN MoMo lors de la confirmation/échec du paiement
     */
    public function momoWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('MTN MoMo Webhook Received:', $payload);

        // Dans un vrai scénario, on vérifierait le statut de la transaction et on validerait la facture
        if (isset($payload['status']) && $payload['status'] === 'SUCCESSFUL') {
            // Activer l'abonnement ou marquer la facture comme payée
        }

        return response()->json(['status' => 'OK']);
    }

    /**
     * Initie un paiement via FedaPay (UEMOA / Cartes)
     */
    public function initiateFedaPayPayment(Request $request, \App\Services\Payments\FedaPayService $fedaPayService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'client_id' => 'required|exists:gel_clients,id',
            'email' => 'required|email'
        ]);

        $client = Client::findOrFail($request->client_id);
        
        $result = $fedaPayService->createTransaction(
            amount: $request->amount,
            description: "Paiement Abonnement " . $client->nom_entreprise,
            customerEmail: $request->email,
            customerName: $client->nom_entreprise,
            customMetadata: ['client_id' => $client->id]
        );

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 500);
        }

        return response()->json([
            'success' => true,
            'payment_url' => $result['payment_url'],
            'transaction_id' => $result['transaction_id']
        ]);
    }

    /**
     * Webhook FedaPay
     */
    public function fedapayWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('FedaPay Webhook Received:', $payload);

        return response()->json(['status' => 'OK']);
    }
}
