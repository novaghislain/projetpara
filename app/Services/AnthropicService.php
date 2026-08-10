<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicService
{
    protected string $apiKey;
    protected string $model;
    protected string $version;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', env('ANTHROPIC_API_KEY', ''));
        // Use Claude 3.5 Sonnet or Haiku depending on configuration, default to Haiku for speed
        $this->model = config('services.anthropic.model', env('ANTHROPIC_MODEL', 'claude-3-haiku-20240307'));
        $this->version = '2023-06-01'; // Anthropic API version
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Call the Anthropic API
     */
    public function generate(string $prompt, string $systemPrompt = '', array $options = []): string
    {
        if (!$this->isConfigured()) {
            Log::warning('AnthropicService: API Key not configured.');
            return $this->mockResponse($prompt, $systemPrompt);
        }

        try {
            $payload = [
                'model' => $this->model,
                'max_tokens' => $options['max_tokens'] ?? 1024,
                'temperature' => $options['temperature'] ?? 0.3,
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? '';
            }

            Log::error('Anthropic API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return "Erreur lors de la génération avec l'IA.";

        } catch (\Exception $e) {
            Log::error('Anthropic API Exception', [
                'message' => $e->getMessage()
            ]);
            return "Exception de connexion à l'IA.";
        }
    }

    /**
     * Force the AI to output valid JSON.
     */
    public function generateJson(string $prompt, string $systemPrompt = '', array $options = []): ?array
    {
        $systemPrompt .= "\n\nYou must respond ONLY with valid JSON. Do not include markdown formatting like ```json or any other text.";
        
        $response = $this->generate($prompt, $systemPrompt, $options);
        
        // Try to clean markdown if the AI ignored the instruction
        $response = str_replace(['```json', '```'], '', $response);
        $response = trim($response);
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('AnthropicService: Invalid JSON response', [
                'response' => $response,
                'error' => json_last_error_msg()
            ]);
            // Fallback for mocked response
            if (!$this->isConfigured()) {
                return $this->mockJsonResponse($prompt, $systemPrompt);
            }
            return null;
        }
        
        return $decoded;
    }

    /**
     * Appel MULTIMODAL (vision) : envoie une image locale + une instruction.
     * Utilisé pour l'OCR/la classification des documents scannés (Section 3).
     *
     * GARANTIE « aucune donnée fictive » : si la clé API est absente, on
     * retourne toujours null (jamais de réponse simulée), et l'appelant
     * affiche « OCR non disponible ».
     */
    public function generateWithVision(string $prompt, string $imagePath, string $mimeType = 'image/jpeg', string $systemPrompt = ''): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('AnthropicService: generateWithVision skipped — clé API non configurée.');
            return null;
        }

        if (!file_exists($imagePath)) {
            Log::warning('AnthropicService: image introuvable ' . $imagePath);
            return null;
        }

        $imageData = base64_encode(file_get_contents($imagePath));

        try {
            $payload = [
                'model' => config('services.anthropic.vision_model', env('ANTHROPIC_VISION_MODEL', 'claude-haiku-4-5-20251001')),
                'max_tokens' => 2048,
                'temperature' => 0.2,
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mimeType, 'data' => $imageData]],
                            ['type' => 'text', 'text' => $prompt],
                        ],
                    ],
                ],
            ];

            $response = Http::timeout(45)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->version,
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? '';
            }

            Log::error('Anthropic Vision API Error', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        } catch (\Exception $e) {
            Log::error('Anthropic Vision API Exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Version « JSON uniquement » de generateWithVision.
     * Retourne null si la réponse est illisible ou non confirmée.
     */
    public function generateVisionJson(string $prompt, string $imagePath, string $mimeType = 'image/jpeg', string $systemPrompt = ''): ?array
    {
        $systemPrompt .= "\n\nTu dois répondre UNIQUEMENT avec du JSON valide, sans balises markdown ni texte environnant.";

        $response = $this->generateWithVision($prompt, $imagePath, $mimeType, $systemPrompt);

        if ($response === null || $response === '') {
            return null;
        }

        $cleaned = trim(str_replace(['```json', '```'], '', $response));
        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('AnthropicService: réponse vision illisible', ['body' => mb_substr($response, 0, 500)]);

            return null;
        }

        return $decoded;
    }

    private function mockResponse(string $prompt, string $systemPrompt = ''): string
    {
        if (str_contains(strtolower($systemPrompt), 'tps moy traitement')) {
            return "Votre délai de traitement s'améliore de 15% cette semaine. Continuez ainsi !";
        }
        if (str_contains(strtolower($systemPrompt), 'anticipation')) {
            return "Préparez les documents récents et vérifiez le statut des dernières tâches pour ce rendez-vous.";
        }
        if (str_contains(strtolower($systemPrompt), 'relance')) {
            return "Bonjour,\n\nSauf erreur de notre part, nous n'avons pas reçu de retour de votre part concernant ce dossier. Pourriez-vous nous faire un retour dès que possible ?\n\nCordialement,";
        }
        if (str_contains(strtolower($systemPrompt), 'assistant ia') || str_contains(strtolower($systemPrompt), 'secrétariat')) {
            return "L'assistant IA n'est pas disponible pour le moment. Veuillez vérifier la configuration de la clé API Anthropic dans les paramètres.";
        }
        
        return "Le service IA est temporairement indisponible. Veuillez réessayer plus tard.";
    }

    private function mockJsonResponse(string $prompt, string $systemPrompt = ''): array
    {
        if (str_contains(strtolower($systemPrompt), 'checklist')) {
            return [
                ['label' => 'Valider les devis en attente', 'type' => 'tache', 'statut' => false],
                ['label' => 'Préparer la réunion client', 'type' => 'agenda', 'statut' => false],
                ['label' => 'Répondre aux messages urgents', 'type' => 'message', 'statut' => false],
                ['label' => 'Relancer appels manqués', 'type' => 'appel', 'statut' => false]
            ];
        }
        
        if (str_contains(strtolower($systemPrompt), 'classification de documents')) {
            return [
                'type' => 'Contrat',
                'tags' => ['Important', 'Juridique'],
                'est_contrat' => true,
                'rappel_necessaire' => true
            ];
        }
        
        return ['mock' => true];
    }
}
