<?php

namespace App\Services\IA;

use App\Models\Client;
use App\Models\AiSuggestion;
use App\Models\AiLearningLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CustomerAiService
{
    /**
     * Calcule un score de lead (0-100) basé sur les critères suivants :
     * - Taille de l'entreprise (complétude du profil)
     * - Secteur d'activité
     * - Interaction récente (devis)
     * - Budget potentiel
     * - Complétude du profil
     */
    public function calculateLeadScore(Client $client): array
    {
        $score = 0;
        $factors = [];

        // --- 1. Complétude du profil (30 pts max) ---
        $completeness = 0;
        $fields = ['company_name', 'email', 'phone', 'secteur', 'city', 'address'];
        foreach ($fields as $field) {
            if (!empty($client->$field)) $completeness += 5;
        }
        $score += $completeness;
        $factors[] = [
            'name' => 'Complétude du profil',
            'points' => $completeness,
            'max' => 30,
            'detail' => $completeness . '/30 pts',
        ];

        // --- 2. Présence de contacts (10 pts max) ---
        $contactCount = $client->contacts()->count();
        $contactPoints = min($contactCount * 3, 10);
        $score += $contactPoints;
        $factors[] = [
            'name' => 'Contacts renseignés',
            'points' => $contactPoints,
            'max' => 10,
            'detail' => $contactCount . ' contact(s)',
        ];

        // --- 3. Devis récents (25 pts max) ---
        $recentDevis = $client->devis()
            ->where('created_at', '>=', now()->subMonths(6))
            ->get();

        $devisCount = $recentDevis->count();
        $devisPoints = min($devisCount * 5, 15);
        $score += $devisPoints;
        $factors[] = [
            'name' => 'Devis récents (6 mois)',
            'points' => $devisPoints,
            'max' => 15,
            'detail' => $devisCount . ' devis',
        ];

        // Bonus si devis signé
        $signedDevis = $recentDevis->where('statut', 'signe')->count();
        $signedPoints = min($signedDevis * 5, 10);
        $score += $signedPoints;
        if ($signedPoints > 0) {
            $factors[] = [
                'name' => 'Devis signés',
                'points' => $signedPoints,
                'max' => 10,
                'detail' => $signedDevis . ' signé(s)',
            ];
        }

        // --- 4. Secteur stratégique (15 pts max) ---
        $strategicSectors = [
            'banque', 'assurance', 'finance', 'microfinance',
            'commerce', 'import', 'export', 'industrie',
            'bâtiment', 'construction', 'immobilier',
            'santé', 'pharmaceutique',
        ];

        $sectorPoints = 0;
        if ($client->secteur) {
            $sector = strtolower($client->secteur);
            foreach ($strategicSectors as $strat) {
                if (str_contains($sector, $strat)) {
                    $sectorPoints = 15;
                    break;
                }
            }
            if ($sectorPoints === 0) $sectorPoints = 5;
        }
        $score += $sectorPoints;
        $factors[] = [
            'name' => "Secteur d'activité",
            'points' => $sectorPoints,
            'max' => 15,
            'detail' => $client->secteur ?? 'Non renseigné',
        ];

        // --- 5. Ancienneté relation (10 pts max) ---
        if ($client->created_at) {
            $monthsSinceCreation = $client->created_at->diffInMonths(now());
            $seniorityPoints = min($monthsSinceCreation, 10);
            $score += $seniorityPoints;
            $factors[] = [
                'name' => 'Ancienneté relation',
                'points' => $seniorityPoints,
                'max' => 10,
                'detail' => $monthsSinceCreation . ' mois',
            ];
        }

        // --- 6. Budget estimé via devis (10 pts max) ---
        $totalDevisAmount = $recentDevis->sum('montant');
        if ($totalDevisAmount > 0) {
            if ($totalDevisAmount >= 10000000) $budgetPoints = 10;
            elseif ($totalDevisAmount >= 5000000) $budgetPoints = 8;
            elseif ($totalDevisAmount >= 1000000) $budgetPoints = 6;
            elseif ($totalDevisAmount >= 500000) $budgetPoints = 4;
            else $budgetPoints = 2;

            $score += $budgetPoints;
            $factors[] = [
                'name' => 'Budget estimé',
                'points' => $budgetPoints,
                'max' => 10,
                'detail' => number_format($totalDevisAmount, 0, ',', ' ') . ' FCFA',
            ];
        }

        // Score final plafonné à 100
        $score = min($score, 100);

        // Niveau
        $level = $score >= 80 ? 'chaud' : ($score >= 50 ? 'tiède' : 'froid');

        return [
            'score' => $score,
            'level' => $level,
            'factors' => $factors,
            'profil' => [
                'id' => $client->id,
                'raison_sociale' => $client->company_name,
                'email' => $client->email,
                'telephone' => $client->phone,
                'secteur' => $client->secteur,
            ],
        ];
    }

    /**
     * Suggère des actions de relance personnalisées
     */
    public function suggestFollowUpActions(Client $client): array
    {
        $actions = [];
        $now = now();

        // 1. Dernier devis non suivi (devis envoyé mais sans suite depuis > 7 jours)
        $pendingDevis = $client->devis()
            ->whereIn('statut', ['envoye', 'relance'])
            ->where('date_validite', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($pendingDevis && $pendingDevis->created_at->diffInDays($now) >= 7) {
            $actions[] = [
                'type' => 'relance_devis',
                'priority' => 'haute',
                'title' => 'Relancer sur le devis #' . $pendingDevis->id,
                'message' => 'Le devis #' . $pendingDevis->id . ' de ' .
                    number_format($pendingDevis->montant, 0, ',', ' ') . ' FCFA est en attente depuis ' .
                    $pendingDevis->created_at->diffInDays($now) . ' jours.',
                'suggested_action' => 'Contacter le client pour connaître l\'avancement.',
                'channel' => 'telephone',
                'script' => 'Bonjour, je fais suite au devis que nous vous avons transmis le '
                    . $pendingDevis->created_at->format('d/m/Y') .
                    '. Avez-vous eu l\'occasion de l\'examiner ?',
            ];
        }

        // 2. Aucun contact depuis longtemps (> 60 jours)
        $latestDevis = $client->devis()->latest()->first();
        $lastInteraction = $latestDevis?->created_at ?? $client->created_at;

        if ($lastInteraction && $lastInteraction->diffInDays($now) >= 60) {
            $actions[] = [
                'type' => 'prise_contact',
                'priority' => 'moyenne',
                'title' => 'Reprendre contact',
                'message' => "Pas d'interaction depuis " . $lastInteraction->diffInDays($now) . ' jours.',
                'suggested_action' => 'Envoyer un email de prise de contact.',
                'channel' => 'email',
                'script' => "Cher/Chère {$client->company_name},\n\n"
                    . 'Nous espérons que tout va bien. Nous aimerions prendre de vos nouvelles '
                    . 'et voir si nous pouvons vous accompagner dans vos besoins comptables pour cette nouvelle période.',
            ];
        }

        // 3. Prospect sans devis (jamais fait de demande)
        $devisCount = $client->devis()->count();
        if ($devisCount === 0) {
            $actions[] = [
                'type' => 'offre_decouverte',
                'priority' => 'haute',
                'title' => 'Proposer une offre de découverte',
                'message' => "Ce prospect n'a encore jamais fait de devis.",
                'suggested_action' => 'Proposer un audit comptable gratuit ou une offre découverte.',
                'channel' => 'telephone',
                'script' => "Bonjour, nous avons remarqué que vous n'avez pas encore testé nos services. "
                    . 'Nous proposons actuellement un audit comptable gratuit pour les nouveaux clients.',
            ];
        }

        // 4. Devis arrivant à expiration
        $expiringDevis = $client->devis()
            ->where('statut', 'envoye')
            ->where('date_validite', '<=', $now->addDays(7))
            ->where('date_validite', '>=', $now)
            ->first();

        if ($expiringDevis) {
            $actions[] = [
                'type' => 'expiration_proche',
                'priority' => 'haute',
                'title' => 'Le devis #' . $expiringDevis->id . ' expire bientôt',
                'message' => 'Le devis expire le ' . $expiringDevis->date_validite->format('d/m/Y') . '.',
                'suggested_action' => 'Relancer en urgence avant expiration.',
                'channel' => 'telephone',
                'script' => 'Bonjour, je vous rappelle que notre devis expire le '
                    . $expiringDevis->date_validite->format('d/m/Y') .
                    '. Souhaitez-vous le valider avant cette date ?',
            ];
        }

        return $actions;
    }

    /**
     * Détecte les opportunités de vente additionnelle (cross-sell)
     */
    public function detectCrossSellOpportunities(Client $client): array
    {
        $opportunities = [];

        $activeModules = $client->active_modules;

        // Modules disponibles
        $allModules = [
            'comptabilite' => 'Comptabilité SYSCOHADA',
            'paie' => 'Gestion de la paie',
            'facturation' => 'Facturation électronique',
            'tva' => 'Déclaration TVA',
            'bilan' => 'États financiers (bilan/liasse)',
            'caisse' => 'Gestion de caisse',
            'stock' => 'Gestion des stocks',
            'fiscal' => 'Fiscalité Bénin',
        ];

        // Opportunité 1 : A comptabilité mais pas de facturation
        if (in_array('comptabilite', $activeModules) && !in_array('facturation', $activeModules)) {
            $opportunities[] = [
                'type' => 'cross_sell',
                'priority' => 'haute',
                'title' => 'Module Facturation',
                'message' => 'Ce client utilise la comptabilité mais pas la facturation électronique.',
                'benefit' => 'Gagner du temps avec une facturation intégrée à la compta.',
                'estimated_value' => 50000,
                'suggested_action' => 'Proposer le module Facturation à 50 000 FCFA/mois.',
            ];
        }

        // Opportunité 2 : A comptabilité mais pas de paie
        if (in_array('comptabilite', $activeModules) && !in_array('paie', $activeModules)) {
            $opportunities[] = [
                'type' => 'cross_sell',
                'priority' => 'haute',
                'title' => 'Module Paie',
                'message' => 'Ce client pourrait bénéficier de la gestion de paie intégrée.',
                'benefit' => 'Centraliser la paie et la compta dans un seul outil.',
                'estimated_value' => 75000,
                'suggested_action' => 'Proposer le module Paie à 75 000 FCFA/mois.',
            ];
        }

        // Opportunité 3 : A des modules, mais pas de fiscal
        if (!empty($activeModules) && !in_array('fiscal', $activeModules)) {
            $opportunities[] = [
                'type' => 'cross_sell',
                'priority' => 'moyenne',
                'title' => 'Module Fiscalité Bénin',
                'message' => 'Conformité fiscale automatisée (TVA, IR, CF).',
                'benefit' => 'Éviter les pénalités fiscales avec des déclarations automatiques.',
                'estimated_value' => 35000,
                'suggested_action' => 'Proposer le module Fiscalité à 35 000 FCFA/mois.',
            ];
        }

        // Opportunité 4 : Aucun module mais client existant
        if (empty($activeModules)) {
            $opportunities[] = [
                'type' => 'upsell',
                'priority' => 'haute',
                'title' => 'Module Comptabilité de base',
                'message' => "Ce client est enregistré mais n'utilise aucun module payant.",
                'benefit' => 'Découvrir la puissance de GEL Cabinet.',
                'estimated_value' => 100000,
                'suggested_action' => "Offrir un mois d'essai gratuit du module Comptabilité.",
            ];
        }

        return $opportunities;
    }

    /**
     * Analyse du risque de perte client (churn prediction simplifié)
     */
    public function predictChurnRisk(Client $client): array
    {
        $risk = 0;
        $signals = [];

        // Signal 1 : Pas d'interaction depuis longtemps (> 90 jours)
        $latestDevis = $client->devis()->latest()->first();
        $lastActivity = $latestDevis?->updated_at ?? $client->updated_at;

        if ($lastActivity && $lastActivity->diffInDays(now()) >= 90) {
            $risk += 30;
            $signals[] = [
                'signal' => 'inactivite_prolongee',
                'weight' => 30,
                'detail' => 'Dernière activité il y a ' . $lastActivity->diffInDays(now()) . ' jours',
            ];
        }

        // Signal 2 : Plus de devis depuis l'inscription (prospect jamais converti)
        $devisCount = $client->devis()->count();
        if ($devisCount === 0 && $client->created_at->diffInDays(now()) >= 30) {
            $risk += 25;
            $signals[] = [
                'signal' => 'jamais_converti',
                'weight' => 25,
                'detail' => 'Inscrit depuis ' . $client->created_at->diffInDays(now()) . ' jours sans devis',
            ];
        }

        // Signal 3 : Devis refusés récents
        $refusedDevis = $client->devis()
            ->where('statut', 'refuse')
            ->where('created_at', '>=', now()->subMonths(3))
            ->count();

        if ($refusedDevis >= 2) {
            $risk += 20;
            $signals[] = [
                'signal' => 'devis_refuses',
                'weight' => 20,
                'detail' => $refusedDevis . ' devis refusés récemment',
            ];
        }

        // Signal 5 : Baisse de budget (dernier devis bien inférieur aux précédents)
        $lastTwoDevis = $client->devis()
            ->where('statut', 'signe')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        if ($lastTwoDevis->count() === 2) {
            $lastAmount = $lastTwoDevis[0]->montant;
            $previousAmount = $lastTwoDevis[1]->montant;
            if ($previousAmount > 0 && ($lastAmount / $previousAmount) < 0.5) {
                $risk += 10;
                $signals[] = [
                    'signal' => 'baisse_budget',
                    'weight' => 10,
                    'detail' => 'Le dernier devis représente moins de 50% du précédent',
                ];
            }
        }

        // Niveau de risque
        $level = 'faible';
        if ($risk >= 60) $level = 'critique';
        elseif ($risk >= 35) $level = 'élevé';
        elseif ($risk >= 15) $level = 'moyen';

        return [
            'risk_score' => $risk,
            'risk_level' => $level,
            'signals' => $signals,
            'probability' => $risk . '%',
            'recommendation' => $this->getChurnRecommendation($level),
        ];
    }

    private function getChurnRecommendation(string $level): string
    {
        return match ($level) {
            'critique' => '🔴 Action urgente : contacter immédiatement le client, proposer une rétention avec 15% de réduction.',
            'élevé' => '🟠 Priorité haute : planifier un appel de suivi, envoyer une offre personnalisée.',
            'moyen' => '🟡 Surveiller : envoyer un email de satisfaction, proposer une démo des nouvelles fonctionnalités.',
            default => '🟢 OK : client actif et engagé. Maintenir la relation.',
        };
    }

    /**
     * Enregistre l'apprentissage continu
     */
    public function logLearning(
        string $action,
        array $inputData,
        array $suggestedOutput,
        ?array $actualOutput = null,
        ?bool $wasCorrect = null,
        ?int $clientId = null,
        ?int $userId = null
    ): void {
        AiLearningLog::create([
            'agent' => 'customer',
            'type' => $action,
            'input_data' => json_encode($inputData),
            'output_data' => json_encode($suggestedOutput),
            'correction' => $actualOutput !== null ? json_encode($actualOutput) : null,
            'metadata' => ['was_correct' => $wasCorrect],
            'client_id' => $clientId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Crée une suggestion dans la base (adaptée au modèle AiSuggestion réel)
     */
    public function createSuggestion(int $clientId, int $userId, array $data): AiSuggestion
    {
        return AiSuggestion::create([
            'client_id' => $clientId,
            'user_id' => $userId,
            'agent' => 'customer',
            'type' => $data['type'] ?? 'suggestion',
            'title' => $data['title'],
            'description' => $data['message'] ?? '',
            'data' => [
                'priority' => $data['priority'] ?? 'normal',
                'confidence' => $data['confidence'] ?? 70,
                'action_type' => $data['action_type'] ?? null,
                'action_payload' => $data['action_payload'] ?? null,
                'extra' => $data['data'] ?? null,
            ],
            'metadata' => [
                'source' => 'customer_ai',
                'generated_at' => now()->toDateTimeString(),
            ],
            'status' => 'pending',
        ]);
    }
}
