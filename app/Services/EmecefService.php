<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmecefService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected string $nim;
    protected bool $testMode;
    protected \App\Services\Fiscalite\RegleFiscaleService $regleFiscaleService;

    public function __construct()
    {
        $this->apiUrl = config('services.emecef.api_url', env('EMECEF_API_URL', 'https://sygmef.impots.bj/emcf/api'));
        $this->apiToken = config('services.emecef.api_token', env('EMECEF_API_TOKEN', ''));
        $this->nim = config('services.emecef.nim', env('EMECEF_NIM', ''));
        $this->testMode = config('services.emecef.test_mode', env('EMECEF_TEST_MODE', false));
        $this->regleFiscaleService = app(\App\Services\Fiscalite\RegleFiscaleService::class);
    }

    /**
     * Certifie une facture auprès de la DGI (Sygmef)
     */
    public function certifyInvoice(Invoice $invoice): bool
    {
        if (empty($this->apiToken) || empty($this->nim)) {
            Log::error('e-MECeF: Token ou NIM manquant.');
            return false;
        }

        // Construction du JSON demandé par la DGI (Format DGI Bénin)
        $payload = [
            'ifu' => $invoice->client->ifu,
            'nim' => $this->nim,
            'type' => 'FV', // Facture de Vente
            'items' => [],
            'client' => [
                'name' => $invoice->partner_name,
                'ifu' => $invoice->partner_tax_id
            ],
            'payment_type' => 'CASH', // A adapter selon $invoice->payment_method
            'total' => $invoice->total,
            'tax_base' => $invoice->tax_base,
            'vat_total' => $invoice->vat_total,
        ];

        // Résolution de la règle de TVA
        $regleTVA = $this->regleFiscaleService->resoudre('BJ', 'TVA', [], now());
        $taxGroup = ($regleTVA && isset($regleTVA->conditions['tax_group'])) ? $regleTVA->conditions['tax_group'] : 'B';

        foreach ($invoice->lines as $line) {
            $payload['items'][] = [
                'name' => $line->description,
                'price' => $line->unit_price,
                'quantity' => $line->quantity,
                'tax_group' => $taxGroup, // Dynamique depuis regle_fiscale
            ];
        }

        // Résolution de la règle AIB selon le centre d'impôts du client
        $centreImpots = $invoice->client->centre_impots_rattachement ?? null;
        $conditionsAib = $centreImpots ? ['centre_impots' => $centreImpots] : [];
        $regleAIB = $this->regleFiscaleService->resoudre('BJ', 'AIB', $conditionsAib, now());
        
        if ($regleAIB) {
            $payload['aib_rate'] = $regleAIB->taux;
            $payload['aib_amount'] = $invoice->tax_base * $regleAIB->taux;
        }

        $endpoint = $this->testMode ? "{$this->apiUrl}/test/invoices" : "{$this->apiUrl}/invoices";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($endpoint, $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            // Mise à jour de la facture avec les données de certification
            $invoice->update([
                'emecef_uid' => $data['uid'] ?? null,
                'emecef_qr' => $data['qrCode'] ?? null,
                'emecef_statut' => 'certified',
                'emecef_datetime' => now(),
                'emecef_response' => $data,
            ]);

            return true;
        }

        Log::error('e-MECeF Certification Error', [
            'invoice_id' => $invoice->id,
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return false;
    }
}