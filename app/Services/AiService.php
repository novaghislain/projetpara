<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.ai.api_key', env('AI_API_KEY', ''));
        $this->apiUrl = config('services.ai.api_url', env('AI_API_URL', 'https://api.anthropic.com/v1'));
    }

    /**
     * Envoyer un message au chat IA et obtenir une réponse.
     */
    public function chat(array $messages, string $context = ''): string
    {
        // Pour l'instant, retourne une simulation
        // À remplacer par un vrai appel API plus tard
        $lastMsg = end($messages);
        $content = $lastMsg['content'] ?? '';

        $response = "Je suis l'assistant Eden Cabinet. ";
        if (str_contains(strtolower($content), 'bonjour') || str_contains(strtolower($content), 'salut')) {
            $response .= "Bonjour ! Comment puis-je vous aider avec votre entreprise ?";
        } elseif (str_contains(strtolower($content), 'compta') || str_contains(strtolower($content), 'comptabilité')) {
            $response .= "Pour vos questions de comptabilité, je peux vous aider à comprendre votre plan comptable, vos journaux, ou générer des rapports. Que voulez-vous savoir ?";
        } elseif (str_contains(strtolower($content), 'facture') || str_contains(strtolower($content), 'facturation')) {
            $response .= "Je peux vous aider avec votre facturation : création de devis, suivi des paiements, relances automatiques. Dites-moi ce dont vous avez besoin.";
        } elseif (str_contains(strtolower($content), 'contrat') || str_contains(strtolower($content), 'juridique')) {
            $response .= "Pour les questions juridiques et les contrats, je vous recommande de consulter la section Juridique de votre espace. Je peux vous aider à comprendre les termes courants.";
        } else {
            $response .= "Je suis là pour vous assister dans la gestion de votre entreprise : comptabilité, facturation, RH, juridique, projets, CRM. Que puis-je faire pour vous ?";
        }

        return $response;
    }

    /**
     * Analyser un document pour en extraire les informations clés (OCR).
     * Simulé pour l'instant.
     */
    public function analyzeDocument(string $filePath, string $mimeType): array
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return [
            'success' => true,
            'text' => "Ceci est une analyse simulée du document " . basename($filePath) . ". L'OCR sera actif avec une vraie API.",
            'pages' => 1,
            'language' => 'fr',
            'entities' => [
                'dates' => [],
                'amounts' => [],
                'names' => [],
            ],
        ];
    }

    /**
     * Classifier un document dans une catégorie.
     */
    public function classifyDocument(string $title, ?string $content = null): array
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
