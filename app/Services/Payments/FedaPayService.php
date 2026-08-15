<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FedaPayService
{
    protected string $secretKey;
    protected string $environment;
    protected string $baseUrl;

    public function __construct()
    {
        $this->environment = config('services.fedapay.environment', env('FEDAPAY_ENVIRONMENT', 'sandbox'));
        $this->secretKey = config('services.fedapay.secret_key', env('FEDAPAY_SECRET_KEY', 'sk_sandbox_xxxxx'));
        
        $this->baseUrl = $this->environment === 'live' 
            ? 'https://api.fedapay.com/v1' 
            : 'https://sandbox-api.fedapay.com/v1';
    }

    /**
     * Crée une transaction FedaPay et retourne l'URL de paiement
     */
    public function createTransaction(float $amount, string $description, string $customerEmail, string $customerName, array $customMetadata = [])
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transactions", [
                'description' => $description,
                'amount' => $amount,
                'currency' => ['iso' => 'XOF'],
                'callback_url' => route('fedapay.callback') ?? url('/'),
                'customer' => [
                    'email' => $customerEmail,
                    'firstname' => $customerName,
                    'lastname' => ' '
                ],
                'custom_metadata' => $customMetadata
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $transactionId = $data['v1/transaction']['id'] ?? null;
            
            // Génération du lien de paiement
            if ($transactionId) {
                $tokenResponse = Http::withToken($this->secretKey)
                    ->post("{$this->baseUrl}/transactions/{$transactionId}/token");
                
                if ($tokenResponse->successful()) {
                    $token = $tokenResponse->json('token');
                    $paymentUrl = $this->environment === 'live'
                        ? "https://checkout.fedapay.com/pay/{$token}"
                        : "https://sandbox-checkout.fedapay.com/pay/{$token}";
                    
                    return [
                        'success' => true,
                        'transaction_id' => $transactionId,
                        'payment_url' => $paymentUrl
                    ];
                }
            }
        }

        Log::error('FedaPay Transaction Creation Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return [
            'success' => false,
            'message' => 'Erreur lors de la création de la transaction FedaPay'
        ];
    }

    /**
     * Vérifie le statut d'une transaction FedaPay
     */
    public function verifyTransaction(int $transactionId)
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/{$transactionId}");

        if ($response->successful()) {
            $data = $response->json('v1/transaction');
            return [
                'success' => true,
                'status' => $data['status'], // 'approved', 'declined', 'pending', etc.
                'amount' => $data['amount']
            ];
        }

        return [
            'success' => false,
            'status' => 'unknown'
        ];
    }
}
