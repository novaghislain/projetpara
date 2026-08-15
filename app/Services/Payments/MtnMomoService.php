<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MtnMomoService
{
    protected string $baseUrl;
    protected string $subscriptionKey;
    protected string $targetEnvironment;
    protected string $apiUser;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.momo.base_url', env('MTN_MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'));
        $this->subscriptionKey = config('services.momo.subscription_key', env('MTN_MOMO_SUBSCRIPTION_KEY', ''));
        $this->targetEnvironment = config('services.momo.environment', env('MTN_MOMO_ENVIRONMENT', 'sandbox'));
        $this->apiUser = config('services.momo.api_user', env('MTN_MOMO_API_USER', ''));
        $this->apiKey = config('services.momo.api_key', env('MTN_MOMO_API_KEY', ''));
    }

    /**
     * Obtenir le token d'accès (Basic Auth => Bearer Token)
     */
    private function getAccessToken(): ?string
    {
        if (empty($this->apiUser) || empty($this->apiKey)) {
            Log::error('MTN MoMo: API User ou API Key manquant');
            return null;
        }

        $credentials = base64_encode($this->apiUser . ':' . $this->apiKey);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
        ])->post("{$this->baseUrl}/collection/token/");

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('MTN MoMo Access Token Error', ['status' => $response->status(), 'body' => $response->body()]);
        return null;
    }

    /**
     * Lancer une requête RequestToPay (Paiement Push)
     */
    public function requestToPay(string $phoneNumber, float $amount, string $currency = 'XOF', string $externalId = null, string $payerMessage = 'Paiement SaaS'): ?string
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $referenceId = Str::uuid()->toString();
        $externalId = $externalId ?? uniqid('PAY_');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Reference-Id' => $referenceId,
            'X-Target-Environment' => $this->targetEnvironment,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/collection/v1_0/requesttopay", [
            'amount' => (string) $amount,
            'currency' => $currency,
            'externalId' => $externalId,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $phoneNumber // Format international sans le '+'
            ],
            'payerMessage' => $payerMessage,
            'payeeNote' => 'Facture GEL Cabinet'
        ]);

        if ($response->status() === 202) {
            return $referenceId; // La transaction est en cours
        }

        Log::error('MTN MoMo RequestToPay Error', ['status' => $response->status(), 'body' => $response->body()]);
        return null;
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function getTransactionStatus(string $referenceId): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Target-Environment' => $this->targetEnvironment,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
        ])->get("{$this->baseUrl}/collection/v1_0/requesttopay/{$referenceId}");

        if ($response->successful()) {
            return $response->json(); // statuts : SUCCESSFUL, PENDING, FAILED
        }

        Log::error('MTN MoMo Get Status Error', ['status' => $response->status(), 'body' => $response->body()]);
        return null;
    }
}
