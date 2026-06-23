<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.ai.api_key', '');
        $this->apiUrl = config('services.ai.api_url', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent');
        $this->model = config('services.ai.model', 'gemini-1.5-pro');
    }

    /**
     * Vérifier si l'IA est configurée avec une clé API valide.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Envoyer un prompt à Gemini et obtenir une réponse textuelle.
     */
    public function generate(string $prompt, float $temperature = 0.3): string
    {
        if (!$this->isConfigured()) {
            return $this->mockResponse($prompt);
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => 2048,
                        'topP' => 0.95,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            }

            Log::warning('IA API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->mockResponse($prompt);

        } catch (\Exception $e) {
            Log::error('IA API exception', [
                'message' => $e->getMessage(),
            ]);
            return $this->mockResponse($prompt);
        }
    }

    /**
     * Chat avec historique de messages.
     */
    public function chat(array $messages, string $systemPrompt = ''): string
    {
        $context = $systemPrompt ? $systemPrompt . "\n\n" : '';
        foreach ($messages as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = $msg['content'] ?? '';
            $context .= "{$role}: {$content}\n";
        }
        $context .= "assistant: ";

        return $this->generate($context, 0.5);
    }

    /**
     * Analyser un document avec l'IA.
     */
    public function analyzeDocument(string $filePath, string $mimeType): array
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $content = file_exists($filePath) ? file_get_contents($filePath) : '';
        $text = mb_substr(strip_tags($content), 0, 3000);

        if (!$this->isConfigured()) {
            return [
                'success' => true,
                'text' => "Analyse simulée du document " . basename($filePath),
                'pages' => 1,
                'language' => 'fr',
                'entities' => [
                    'dates' => [],
                    'amounts' => [],
                    'names' => [],
                ],
            ];
        }

        try {
            $prompt = "Analyse ce document et extrait les informations clés:\n\n" . $text . "\n\n"
                . "Reste uniquement les faits. Format JSON : {\"resume\":\"...\", \"type\":\"...\", \"montants\":[], \"dates\":[], \"entites\":[]}";

            $result = $this->generate($prompt, 0.2);

            return [
                'success' => true,
                'text' => $result,
                'pages' => 1,
                'language' => 'fr',
                'entities' => [
                    'dates' => [],
                    'amounts' => [],
                    'names' => [],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'text' => '',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Classifier un document.
     */
    public function classifyDocument(string $title, ?string $content = null): array
    {
        if (!$this->isConfigured()) {
            return $this->mockClassify($title, $content);
        }

        try {
            $text = $title . ($content ? "\n" . $content : '');
            $prompt = "Classe ce document en une catégorie parmi : facture, contrat, rapport, courrier, administratif, divers.\n"
                . "Titre: {$title}\n"
                . "Réponds uniquement par le nom de la catégorie.";

            $result = strtolower(trim($this->generate($prompt, 0.1)));

            $validCategories = ['facture', 'contrat', 'rapport', 'courrier', 'administratif'];
            $category = in_array($result, $validCategories) ? $result : 'divers';

            return [
                'category' => $category,
                'confidence' => $category !== 'divers' ? 85 : 40,
                'tags' => [$category],
            ];
        } catch (\Exception $e) {
            return $this->mockClassify($title, $content);
        }
    }

    // ─── Fallbacks simulés ─────────────────────────────────────

    private function mockResponse(string $prompt): string
    {
        $promptLower = strtolower($prompt);

        if (str_contains($promptLower, 'bonjour') || str_contains($promptLower, 'salut')) {
            return "Bonjour ! Comment puis-je vous aider avec votre entreprise ?";
        }
        if (str_contains($promptLower, 'compta') || str_contains($promptLower, 'comptabilité')) {
            return "Pour vos questions de comptabilité, je peux vous aider à comprendre votre plan comptable, vos journaux, ou générer des rapports.";
        }
        if (str_contains($promptLower, 'facture') || str_contains($promptLower, 'facturation')) {
            return "Je peux vous aider avec votre facturation : création de devis, suivi des paiements, relances automatiques.";
        }
        if (str_contains($promptLower, 'contrat') || str_contains($promptLower, 'juridique')) {
            return "Pour les questions juridiques, consultez la section Juridique de votre espace.";
        }
        if (str_contains($promptLower, 'syscohada') || str_contains($promptLower, 'ohada')) {
            return "Analyse SYSCOHADA effectuée. Vérifiez les suggestions dans le tableau de bord.";
        }
        if (str_contains($promptLower, 'tva') || str_contains($promptLower, 'fiscal')) {
            return "Analyse fiscale effectuée. Consultez les alertes dans le module Fiscal.";
        }

        return "Je suis l'assistant GEL Cabinet. Je suis là pour vous assister dans la gestion de votre entreprise.";
    }

    private function mockClassify(string $title, ?string $content = null): array
    {
        $categories = [
            'facture' => ['facture', 'invoice', 'bill', 'payment'],
            'contrat' => ['contrat', 'contract', 'agreement', 'convention'],
            'rapport' => ['rapport', 'report', 'bilan', 'compte rendu'],
            'courrier' => ['courrier', 'letter', 'mail', 'correspondance'],
            'administratif' => ['administratif', 'certificat', 'attestation', 'formulaire'],
        ];

        $text = strtolower($title . ' ' . ($content ?? ''));
        $bestCategory = 'divers';
        $bestScore = 0;

        foreach ($categories as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $score += 10;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCategory = $category;
            }
        }

        return [
            'category' => $bestCategory,
            'confidence' => $bestScore > 0 ? min(100, $bestScore * 10) : 20,
            'tags' => $bestScore > 0 ? [$bestCategory] : ['non classé'],
        ];
    }
}
