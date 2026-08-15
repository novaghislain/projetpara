<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrInvoiceService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        // En production, utiliser l'API Anthropic ou OpenAI Vision.
        // On mocke la réponse ici pour la démonstration de l'architecture.
        $this->apiKey = config('services.anthropic.key', 'mock_key');
        $this->apiUrl = 'https://api.anthropic.com/v1/messages';
    }

    /**
     * Extrait les données structurées (JSON) d'une image de facture
     * 
     * @param string $base64Image L'image de la facture encodée en base64
     * @param string $mediaType Le type MIME (ex: image/jpeg)
     * @return array
     */
    public function extractDataFromImage(string $base64Image, string $mediaType = 'image/jpeg'): array
    {
        // --- DEBUT MOCK POUR DEMONSTRATION ---
        // Dans le projet réel, on envoie la requête HTTP à Anthropic Claude 3.5 Sonnet
        // en lui demandant de retourner un JSON strict.
        
        // Simulation d'une latence d'API IA
        sleep(2);

        $mockedExtractedData = [
            'supplier_name' => 'SOCIETE BENINOISE D\'ENERGIE ELECTRIQUE (SBEE)',
            'supplier_tax_id' => '1200000000000',
            'invoice_number' => 'FA-2026-08-987654',
            'invoice_date' => date('Y-m-d', strtotime('-2 days')),
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'subtotal' => 45000.00,
            'tax_amount' => 8100.00, // TVA 18%
            'total_amount' => 53100.00,
            'currency' => 'XOF'
        ];

        Log::info('OCR Extraction simulated with AI vision model.', $mockedExtractedData);

        return $mockedExtractedData;
        // --- FIN MOCK ---

        /*
        // Code de production
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post($this->apiUrl, [
            'model' => 'claude-3-5-sonnet-20240620',
            'max_tokens' => 1024,
            'system' => 'Tu es un expert-comptable très précis. Analyse la facture en image et retourne UNIQUEMENT un objet JSON valide avec les clés : supplier_name, supplier_tax_id, invoice_number, invoice_date, due_date, subtotal, tax_amount, total_amount, currency. Ne donne aucune explication textuelle.',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mediaType,
                                'data' => $base64Image,
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => 'Extrais les données de cette facture.'
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $jsonText = $response->json('content.0.text');
            // Trouver et parser le JSON dans la réponse
            preg_match('/\{.*\}/s', $jsonText, $matches);
            if (!empty($matches)) {
                return json_decode($matches[0], true);
            }
        }

        throw new \Exception("Erreur lors de l'extraction OCR par l'IA : " . $response->body());
        */
    }
}
