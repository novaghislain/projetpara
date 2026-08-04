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
