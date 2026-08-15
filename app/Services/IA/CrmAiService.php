<?php

namespace App\Services\IA;

use App\Models\CompanyCrmDeal;
use App\Models\CompanyCrmContact;
use Carbon\Carbon;

class CrmAiService
{
    /**
     * Calcule un score prédictif pour une opportunité (Deal).
     * Retourne un tableau avec le score et l'explication.
     */
    public function analyzeDeal(CompanyCrmDeal $deal): array
    {
        $interactions = $deal->interactions()->orderBy('created_at', 'desc')->get();
        $score = 50; // Score de base
        $reasons = [];

        // 1. Analyse des interactions
        if ($interactions->isEmpty()) {
            $score -= 20;
            $reasons[] = "Aucune interaction enregistrée.";
        } else {
            $lastInteraction = $interactions->first();
            $daysSinceLast = Carbon::parse($lastInteraction->created_at)->diffInDays(now());
            
            if ($daysSinceLast > 14) {
                $score -= 15;
                $reasons[] = "La dernière interaction date de plus de 14 jours (Risque de perte).";
            } elseif ($daysSinceLast <= 3) {
                $score += 20;
                $reasons[] = "Échanges récents (Dynamique positive).";
            }
            
            // Analyse de l'outcome (si existant)
            $positiveOutcomes = $interactions->filter(fn($i) => stripos($i->outcome, 'positif') !== false || stripos($i->outcome, 'intéressé') !== false)->count();
            if ($positiveOutcomes > 0) {
                $score += ($positiveOutcomes * 10);
                $reasons[] = "Plusieurs retours positifs enregistrés.";
            }
        }

        // 2. Probabilité renseignée manuellement
        if ($deal->probability >= 80) {
            $score += 15;
            $reasons[] = "Probabilité de clôture estimée très élevée.";
        } elseif ($deal->probability < 20) {
            $score -= 15;
            $reasons[] = "Probabilité estimée faible.";
        }

        // 3. Stade d'avancement
        if (in_array($deal->stage, ['negociation', 'closing'])) {
            $score += 10;
        }

        // Limitation du score entre 0 et 100
        $score = max(0, min(100, $score));

        // Détermination du badge (Temperature)
        if ($score >= 75) {
            $temperature = 'hot'; // 🔥 Chaud
            $color = 'danger'; // Rouge vif (Hot)
        } elseif ($score >= 40) {
            $temperature = 'warm'; // 🟡 Tiède
            $color = 'warning';
        } else {
            $temperature = 'cold'; // 🧊 Froid
            $color = 'info';
        }

        // Génération de la recommandation (Next-Action)
        $nextAction = $this->generateNextAction($interactions, $score, $deal->stage);

        return [
            'score' => $score,
            'temperature' => $temperature,
            'color' => $color,
            'reasons' => $reasons,
            'next_action' => $nextAction
        ];
    }

    /**
     * Analyse un Contact pour fournir des recommandations d'engagement.
     */
    public function analyzeContact(CompanyCrmContact $contact): array
    {
        $interactions = $contact->interactions()->orderBy('created_at', 'desc')->get();
        $deals = $contact->deals()->get();
        
        $activeDeals = $deals->whereNotIn('status', ['won', 'lost'])->count();
        $wonDeals = $deals->where('status', 'won')->count();
        
        $insight = "Contact standard.";
        $action = "Envoyez un email pour prendre des nouvelles.";

        if ($wonDeals > 0) {
            $insight = "Client existant avec historique d'achats.";
            $action = "Proposer de l'upsell ou demander une recommandation.";
        }
        
        if ($activeDeals > 0) {
            $insight = "Opportunités en cours.";
            if ($interactions->isNotEmpty()) {
                $days = Carbon::parse($interactions->first()->created_at)->diffInDays(now());
                if ($days > 7) {
                    $action = "Relancer le client sur ses opportunités en cours, aucune nouvelle depuis $days jours.";
                } else {
                    $action = "Laisser le client respirer, dernier contact récent.";
                }
            } else {
                $action = "Planifiez un appel de qualification immédiat.";
            }
        } elseif ($interactions->isEmpty()) {
            $insight = "Nouveau contact ou contact froid.";
            $action = "Définissez un premier point de contact (Appel/Email).";
        }

        return [
            'insight' => $insight,
            'suggested_action' => $action,
            'active_deals_count' => $activeDeals
        ];
    }

    /**
     * Helper interne pour générer l'action suivante (Next-Action).
     */
    private function generateNextAction($interactions, $score, $stage): string
    {
        if ($score >= 80) {
            return "🔥 Le deal est chaud. Envoyez le contrat ou planifiez l'appel de clôture (Closing).";
        }
        
        if ($interactions->isEmpty()) {
            return "👋 Aucune interaction. Envoyez un email de présentation ou d'introduction.";
        }

        $last = $interactions->first();
        if ($last->type === 'email' && stripos($last->outcome, 'pas de réponse') !== false) {
            return "📞 L'email n'a pas fonctionné. Tentez un appel téléphonique aujourd'hui.";
        }

        if ($stage === 'prospecting' || $stage === 'qualification') {
            return "🔍 Programmez une démo ou un rendez-vous découverte.";
        }

        return "📧 Maintenez le contact avec un contenu à valeur ajoutée.";
    }
}
