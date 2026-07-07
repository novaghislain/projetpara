<?php

namespace App\Services\IA;

use App\Models\Client;
use App\Models\AuditTrail;
use App\Models\Compta\Ecriture;
use App\Models\CompanyInvoice;
use App\Models\Notification;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatAiService
{
    protected GuzzleClient $httpClient;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->httpClient = new GuzzleClient(['timeout' => 60, 'http_errors' => false]);
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->model = config('services.openai.model', 'gpt-4');
    }

    /**
     * Génère une réponse via OpenAI avec le contexte métier du cabinet.
     */
    public function generate(string $message, array $historique, string $contexte, ?int $cabinetId = null): array
    {
        try {
            $contexteMetier = $this->getBusinessContext($contexte, $cabinetId);
            $systemPrompt = $this->buildSystemPrompt($contexte, $contexteMetier);

            // Construire l'historique formaté pour OpenAI
            $messages = [['role' => 'system', 'content' => $systemPrompt]];
            foreach ($historique as $msg) {
                $messages[] = [
                    'role' => $msg['role'] ?? 'user',
                    'content' => $msg['content'] ?? $msg['message'] ?? '',
                ];
            }
            $messages[] = ['role' => 'user', 'content' => $message];

            // Appel API OpenAI
            $response = $this->callOpenAI($messages);
            $responseText = $response['choices'][0]['message']['content'] ?? '';

            // Détection d'intention
            $intent = $this->detectIntent($message, $responseText);

            // Actions suggérées
            $actions = $this->extractActions($intent, $responseText);

            // Enregistrer l'audit
            AuditTrail::create([
                'user_id' => auth()->id(),
                'event' => 'ia_chat',
                'auditable_type' => 'App\Models\ChatConversation',
                'description' => 'Requête IA : ' . substr($message, 0, 150),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'message' => $responseText,
                'intent' => $intent,
                'actions' => $actions,
                'suggestions' => $this->suggestions($contexte, $message),
                'model' => $this->model,
            ];
        } catch (\Exception $e) {
            Log::error('Chat AI Error: ' . $e->getMessage());

            AuditTrail::create([
                'user_id' => auth()->id(),
                'event' => 'ia_chat_error',
                'auditable_type' => 'App\Models\ChatConversation',
                'description' => 'Erreur IA : ' . $e->getMessage(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $fallback = $this->fallbackResponse($message);
            return [
                'success' => false,
                'message' => $fallback,
                'intent' => 'fallback',
                'actions' => [],
                'suggestions' => [],
                'model' => null,
            ];
        }
    }

    /**
     * Suggestions rapides par contexte.
     */
    public function suggestions(string $contexte, string $query = ''): array
    {
        $cacheKey = 'ia_suggestions_' . $contexte . '_' . md5($query);
        $ttl = 3600; // 1h

        return Cache::remember($cacheKey, $ttl, function () use ($contexte, $query) {
            $suggestions = match ($contexte) {
                'comptabilite' => [
                    ['question' => 'Quelles sont les écritures de régularisation à passer en fin de mois ?', 'categorie' => 'Clôture'],
                    ['question' => 'Comment comptabiliser un achat de marchandises au Bénin ?', 'categorie' => 'Écritures'],
                    ['question' => 'Quels sont les comptes SYSCOHADA pour les immobilisations ?', 'categorie' => 'Plan comptable'],
                    ['question' => 'Comment gérer la TVA sur les importations ?', 'categorie' => 'Fiscalité'],
                    ['question' => 'Quelle est la procédure de clôture d\'exercice ?', 'categorie' => 'Clôture'],
                    ['question' => 'Comment lettrer les comptes clients ?', 'categorie' => 'Gestion'],
                ],
                'fiscal' => [
                    ['question' => 'Quelles sont les échéances fiscales du mois ?', 'categorie' => 'Échéances'],
                    ['question' => 'Comment calculer l\'IRPP au Bénin ?', 'categorie' => 'Impôts'],
                    ['question' => 'Quels sont les taux de TVA applicables ?', 'categorie' => 'TVA'],
                    ['question' => 'Comment déclarer la TVA en ligne ?', 'categorie' => 'Déclarations'],
                    ['question' => 'Quelles sont les pénalités pour retard de déclaration ?', 'categorie' => 'Risques'],
                    ['question' => 'Comment gérer le crédit de TVA ?', 'categorie' => 'TVA'],
                ],
                'paie' => [
                    ['question' => 'Comment calculer le salaire net au Bénin ?', 'categorie' => 'Calcul'],
                    ['question' => 'Quelles sont les charges sociales (CNSS) ?', 'categorie' => 'Charges'],
                    ['question' => 'Comment gérer les congés payés ?', 'categorie' => 'Gestion'],
                    ['question' => 'Quels sont les taux de cotisation employeur ?', 'categorie' => 'Cotisations'],
                    ['question' => 'Comment déclarer et payer la CNSS ?', 'categorie' => 'Déclarations'],
                    ['question' => 'Comment gérer les indemnités de licenciement ?', 'categorie' => 'Indemnités'],
                ],
                'client' => [
                    ['question' => 'Comment fidéliser mes clients ?', 'categorie' => 'Stratégie'],
                    ['question' => 'Quels sont les clients à relancer ?', 'categorie' => 'Recouvrement'],
                    ['question' => 'Comment analyser le chiffre d\'affaires par client ?', 'categorie' => 'Analyse'],
                    ['question' => 'Comment segmenter ma clientèle ?', 'categorie' => 'Marketing'],
                    ['question' => 'Quels clients sont les plus rentables ?', 'categorie' => 'Analyse'],
                    ['question' => 'Comment gérer les réclamations clients ?', 'categorie' => 'Relation'],
                ],
                'tresorerie' => [
                    ['question' => 'Quelle est ma position de trésorerie ?', 'categorie' => 'Synthèse'],
                    ['question' => 'Comment améliorer mon BFR ?', 'categorie' => 'Optimisation'],
                    ['question' => 'Quelles sont les échéances à venir ?', 'categorie' => 'Prévisions'],
                    ['question' => 'Comment négocier avec les banques ?', 'categorie' => 'Financement'],
                    ['question' => 'Comment gérer les découverts ?', 'categorie' => 'Risques'],
                    ['question' => 'Quels sont les délais de paiement moyens ?', 'categorie' => 'Analyse'],
                ],
                default => [
                    ['question' => 'Présentez GEL Cabinet', 'categorie' => 'Général'],
                    ['question' => 'Quels sont vos services ?', 'categorie' => 'Services'],
                    ['question' => 'Comment créer un devis ?', 'categorie' => 'Utilisation'],
                    ['question' => 'Comment suivre mes missions ?', 'categorie' => 'Suivi'],
                    ['question' => 'Quels sont les avantages SYSCOHADA ?', 'categorie' => 'Comptabilité'],
                    ['question' => 'Comment contacter le support ?', 'categorie' => 'Support'],
                ],
            };

            // Filtrer par requête si présente
            if (!empty($query)) {
                $suggestions = array_filter($suggestions, fn($s) =>
                    stripos($s['question'], $query) !== false
                );
                $suggestions = array_values($suggestions);
            }

            return $suggestions;
        });
    }

    /**
     * Construit le prompt système selon le contexte.
     */
    private function buildSystemPrompt(string $contexte, string $contexteMetier): string
    {
        $basePrompt = "Tu es un assistant expert en comptabilité et gestion d'entreprise, spécialisé dans le système SYSCOHADA (Système Comptable OHADA) et la réglementation fiscale béninoise.\n\n";
        $basePrompt .= "Réponds de manière précise, professionnelle et en français.\n";
        $basePrompt .= "Formate tes réponses en Markdown quand c'est pertinent.\n";
        $basePrompt .= "Propose toujours des actions concrètes.\n";

        $contextes = [
            'comptabilite' => "\nContexte : Comptabilité SYSCOHADA. Tu aides le comptable à analyser les écritures, équilibrer la balance, préparer la clôture, et respecter le plan comptable OHADA. Réponds avec des références aux articles du SYSCOHADA quand nécessaire.",
            'fiscal' => "\nContexte : Fiscalité béninoise. Tu aides à la déclaration TVA, IRPP, IS, CNSS, et aux obligations fiscales. Cite les textes de loi et les échéances. Sois précis sur les taux et les dates limites.",
            'paie' => "\nContexte : Gestion de la paie et RH. Tu aides au calcul des salaires, charges sociales CNSS, déclarations, et conformité sociale au Bénin.",
            'client' => "\nContexte : Relation client et CRM. Tu aides à analyser le portefeuille clients, suggérer des actions de relance, identifier les opportunités de vente additionnelle, et réduire le taux d'attrition.",
            'tresorerie' => "\nContexte : Trésorerie et gestion financière. Tu aides à analyser la trésorerie, optimiser le BFR, prévoir les flux, et conseiller sur les décisions financières.",
        ];

        $prompt = $basePrompt . ($contextes[$contexte] ?? "\nContexte : Assistance générale pour le cabinet GEL Cabinet. Réponds aux questions sur l'utilisation du logiciel, la comptabilité, la fiscalité, et la gestion d'entreprise.");

        // Ajouter le contexte métier en fin de prompt
        if (!empty($contexteMetier)) {
            $prompt .= "\n\n--- CONTEXTE DU CABINET ---\n" . $contexteMetier . "\n--- FIN CONTEXTE ---";
        }

        return $prompt;
    }

    /**
     * Récupère le contexte métier du cabinet (stats, données récentes).
     */
    private function getBusinessContext(string $contexte, ?int $cabinetId): string
    {
        if (!$cabinetId) {
            $cabinetId = auth()->user()->cabinet_id ?? null;
        }

        $parts = [];

        try {
            $clientCount = Client::count();
            $parts[] = "Nombre total de clients : {$clientCount}";
        } catch (\Exception $e) {
            $parts[] = "Données clients : non disponibles";
        }

        try {
            $pendingEntries = Ecriture::whereNull('is_validee')->count();
            $parts[] = "Écritures en attente de validation : {$pendingEntries}";
        } catch (\Exception $e) {
            $parts[] = "Écritures : non disponibles";
        }

        try {
            $recentInvoices = CompanyInvoice::whereMonth('created_at', now()->month)->count();
            $parts[] = "Factures du mois : {$recentInvoices}";
        } catch (\Exception $e) {
            $parts[] = "Factures : non disponibles";
        }

        return implode("\n", $parts);
    }

    /**
     * Appel à l'API OpenAI (via HTTP).
     */
    private function callOpenAI(array $messages): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Clé API OpenAI non configurée.');
        }

        $response = $this->httpClient->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1500,
                'top_p' => 0.95,
            ],
        ]);

        $status = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        if ($status !== 200) {
            $error = $body['error']['message'] ?? 'Erreur inconnue';
            throw new \RuntimeException('OpenAI API Error: ' . $error);
        }

        return $body;
    }

    /**
     * Réponse de secours quand l'API est indisponible.
     */
    private function fallbackResponse(string $message): string
    {
        $message = mb_strtolower(trim($message));

        $reponses = [
            'salaire' => "Pour le calcul des salaires au Bénin, voici les éléments à prendre en compte :\n\n"
                . "1. **Salaire de base** (conventionnel ou contractuel)\n"
                . "2. **Heures supplémentaires** (majorées à 15%, 30%, 60%)\n"
                . "3. **Primes** (ancienneté, rendement, panier, logement)\n"
                . "4. **Cotisations CNSS** (part salariale ~3.85%)\n"
                . "5. **IRPP** (calculé selon le barème progressif)\n\n"
                . "Le salaire net = Salaire brut - Cotisations CNSS - IRPP + Avantages en nature.",

            'tva' => "La TVA au Bénin (TVA) est actuellement de **18%**.\n\n"
                . "**Taux particuliers :**\n"
                . "- Produits de première nécessité : 18% (taux normal)\n"
                . "- Exportations : 0%\n"
                . "- Opérations exonérées : produits pétroliers, livres scolaires\n\n"
                . "**Échéances :** Déclaration mensuelle avant le 15 du mois suivant.",

            'cnss' => "Les cotisations CNSS au Bénin se décomposent comme suit :\n\n"
                . "**Part employeur :** Environ 14.35%\n"
                . "- Prestations familiales : 5%\n"
                . "- Accidents du travail : 1-5%\n"
                . "- Retraite : 7.35%\n"
                . "- Autres : variable\n\n"
                . "**Part salariale :** Environ 3.85%\n"
                . "- Retraite : 3.60%\n"
                . "- Autres : 0.25%\n\n"
                . "Déclaration et paiement avant le 15 de chaque mois.",
        ];

        foreach ($reponses as $motCle => $reponse) {
            if (str_contains($message, $motCle)) {
                return $reponse;
            }
        }

        return "Je suis désolé, je ne peux pas me connecter au service d'intelligence artificielle pour le moment. "
            . "Veuillez réessayer plus tard ou vérifier votre connexion API.\n\n"
            . "En attendant, vous pouvez consulter la documentation ou contacter le support technique.";
    }

    /**
     * Détecte l'intention de l'utilisateur.
     */
    private function detectIntent(string $message, string $response): string
    {
        $message = mb_strtolower($message);

        if (preg_match('/calcul|combien|montant|chiffre|nombre|statistiques|graphique/', $message)) {
            return 'analyse_chiffree';
        }
        if (preg_match('/comment|procédure|procédure|étapes|marche à suivre|processus/', $message)) {
            return 'procedure';
        }
        if (preg_match('/créer|ajouter|enregistrer|saisir|faire une écriture|nouveau/', $message)) {
            return 'creation';
        }
        if (preg_match('/loi|texte|article|décret|réglementation|obligation|légal/', $message)) {
            return 'reglementation';
        }
        if (preg_match('/relance|recouvrement|impayé|retard|échéance/', $message)) {
            return 'recouvrement';
        }

        return 'information_generale';
    }

    /**
     * Extrait des actions suggérées depuis la réponse.
     */
    private function extractActions(string $intent, string $response): array
    {
        // Actions génériques selon l'intention
        $actions = match ($intent) {
            'analyse_chiffree' => [
                ['type' => 'generer_rapport', 'label' => 'Générer un rapport', 'icon' => 'file-text'],
                ['type' => 'exporter_pdf', 'label' => 'Exporter en PDF', 'icon' => 'file-pdf'],
            ],
            'procedure' => [
                ['type' => 'voir_guide', 'label' => 'Voir le guide complet', 'icon' => 'book'],
                ['type' => 'video_tuto', 'label' => 'Voir le tutoriel vidéo', 'icon' => 'video'],
            ],
            'creation' => [
                ['type' => 'nouvelle_ecriture', 'label' => 'Nouvelle écriture', 'icon' => 'plus-circle'],
                ['type' => 'nouveau_client', 'label' => 'Nouveau client', 'icon' => 'user-plus'],
            ],
            'recouvrement' => [
                ['type' => 'relancer_clients', 'label' => 'Relancer les clients', 'icon' => 'bell'],
                ['type' => 'etat_impayes', 'label' => 'État des impayés', 'icon' => 'alert-triangle'],
            ],
            default => [
                ['type' => 'consulter_doc', 'label' => 'Consulter la documentation', 'icon' => 'book-open'],
                ['type' => 'contacter_support', 'label' => 'Contacter le support', 'icon' => 'headphones'],
            ],
        };

        return $actions;
    }
}
