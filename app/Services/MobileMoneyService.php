<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de paiement Mobile Money (Bénin)
 *
 * Supports MTN MoMo (API Collection) et Moov Money.
 */
class MobileMoneyService
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.mobile_money.default', 'mtn');
    }

    /**
     * Demander un paiement (request to pay).
     */
    public function requestPayment(string $phone, float $amount, string $reference, ?string $note = null): array
    {
        return match ($this->provider) {
            'mtn'  => $this->mtnRequestToPay($phone, $amount, $reference, $note),
            'moov' => $this->moovPayment($phone, $amount, $reference, $note),
            default => ['success' => false, 'error' => "Provider {$this->provider} non supporté."],
        };
    }

    /**
     * Effectuer un transfert (disbursement) vers un utilisateur.
     */
    public function transfer(string $phone, float $amount, string $reference): array
    {
        return match ($this->provider) {
            'mtn'  => $this->mtnTransfer($phone, $amount, $reference),
            default => ['success' => false, 'error' => "Provider {$this->provider} non supporté."],
        };
    }

    /**
     * Vérifier le statut d'une transaction.
     */
    public function checkTransactionStatus(string $transactionId): array
    {
        return match ($this->provider) {
            'mtn'  => $this->mtnCheckStatus($transactionId),
            default => ['success' => false, 'error' => 'Non supporté.'],
        };
    }

    // ─── MTN MoMo ──────────────────────────────────────────────────────────

    private function getMtnToken(): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(
                config('services.mobile_money.mtn.api_user') . ':' .
                config('services.mobile_money.mtn.api_key')
            ),
            'X-Reference-Id' => (string) \Illuminate\Support\Str::uuid(),
            'Ocp-Apim-Subscription-Key' => config('services.mobile_money.mtn.subscription_key'),
            'Content-Type' => 'application/json',
        ])->post(config('services.mobile_money.mtn.base_url') . '/collection/token/', []);

        return $response->successful() ? $response->json('access_token') : null;
    }

    private function mtnRequestToPay(string $phone, float $amount, string $reference, ?string $note): array
    {
        $token = $this->getMtnToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Authentification MTN MoMo échouée.'];
        }

        $transactionId = (string) \Illuminate\Support\Str::uuid();

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Reference-Id' => $transactionId,
                'X-Target-Environment' => config('services.mobile_money.mtn.environment'),
                'Ocp-Apim-Subscription-Key' => config('services.mobile_money.mtn.collection_primary_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.mobile_money.mtn.base_url') . '/collection/v1_0/requesttopay', [
                'amount' => ['amount' => (string) $amount, 'currency' => 'EUR'],
                'payer'  => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
                'payerMessage' => $note ?? "Paiement $reference",
                'externalId'   => $reference,
            ]);

            if ($response->successful()) {
                Log::info('MTN MoMo payment requested', ['phone' => $phone, 'amount' => $amount, 'reference' => $reference]);
                return ['success' => true, 'transaction_id' => $transactionId, 'status' => 'pending'];
            }

            Log::error('MTN MoMo error', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'error' => $response->json('message') ?? 'Erreur MTN MoMo'];
        } catch (\Exception $e) {
            Log::error('MTN MoMo exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function mtnTransfer(string $phone, float $amount, string $reference): array
    {
        $token = $this->getMtnToken();
        if (!$token) return ['success' => false, 'error' => 'Auth échouée.'];

        $transactionId = (string) \Illuminate\Support\Str::uuid();

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Reference-Id' => $transactionId,
                'X-Target-Environment' => config('services.mobile_money.mtn.environment'),
                'Ocp-Apim-Subscription-Key' => config('services.mobile_money.mtn.disbursement_primary_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.mobile_money.mtn.base_url') . '/disbursement/v1_0/transfer', [
                'amount' => ['amount' => (string) $amount, 'currency' => 'EUR'],
                'payee'  => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
                'externalId' => $reference,
                'payerMessage' => "Transfert $reference",
            ]);

            return $response->successful()
                ? ['success' => true, 'transaction_id' => $transactionId]
                : ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function mtnCheckStatus(string $transactionId): array
    {
        $token = $this->getMtnToken();
        if (!$token) return ['success' => false, 'error' => 'Auth échouée.'];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Target-Environment' => config('services.mobile_money.mtn.environment'),
                'Ocp-Apim-Subscription-Key' => config('services.mobile_money.mtn.collection_primary_key'),
            ])->get(config('services.mobile_money.mtn.base_url') . "/collection/v1_0/requesttopay/$transactionId");

            return $response->successful()
                ? ['success' => true, 'status' => $response->json('status'), 'data' => $response->json()]
                : ['success' => false, 'error' => 'Transaction introuvable.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── Moov Money ───────────────────────────────────────────────────────

    private function moovPayment(string $phone, float $amount, string $reference, ?string $note): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.mobile_money.moov.api_key'),
                'Content-Type'  => 'application/json',
            ])->post(config('services.mobile_money.moov.base_url') . '/payments/collections', [
                'merchant_id' => config('services.mobile_money.moov.merchant_id'),
                'phone'       => $phone,
                'amount'      => $amount,
                'reference'   => $reference,
                'description' => $note ?? "Paiement $reference",
            ]);

            return $response->successful()
                ? ['success' => true, 'transaction_id' => $response->json('transaction_id')]
                : ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
