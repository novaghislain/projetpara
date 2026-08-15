<?php

namespace App\Services;

use App\Models\DunningCampaign;
use App\Models\DunningLog;
use Carbon\Carbon;

class RelanceIntelligenteService
{
    /**
     * Analyse le profil payeur pour prédire la date de paiement
     * (Simulation AI-Native)
     */
    public function predictPaymentDate(string $clientId, string $invoiceNumber): string
    {
        // Dans une vraie implémentation, appel à un modèle ML via API
        // Ici on simule une prédiction heuristique
        
        $predictedDays = rand(3, 14);
        return Carbon::now()->addDays($predictedDays)->toDateString();
    }

    /**
     * Détermine le meilleur canal d'escalade en fonction du niveau actuel
     */
    public function determineOptimalChannel(int $escalationLevel): string
    {
        if ($escalationLevel === 1) return 'email';
        if ($escalationLevel === 2) return 'sms';
        return 'call';
    }

    /**
     * Exécute une passe de relance sur une campagne active
     */
    public function processCampaign(DunningCampaign $campaign)
    {
        $channel = $this->determineOptimalChannel($campaign->escalation_level);
        
        // Simuler l'envoi de la relance
        DunningLog::create([
            'campaign_id' => $campaign->id,
            'action_type' => $channel . '_sent',
            'content' => "Relance de niveau {$campaign->escalation_level} envoyée via {$channel}.",
        ]);

        // Prédire quand le client paiera suite à cette relance
        $predictedDate = $this->predictPaymentDate($campaign->client_id, $campaign->invoice_number);

        // Mettre à jour la campagne (escalade, prochaine action à J+7)
        $campaign->update([
            'escalation_level' => min($campaign->escalation_level + 1, 3), // Max niveau 3
            'next_action_at' => Carbon::now()->addDays(7),
            'ai_predicted_payment_date' => $predictedDate,
        ]);
        
        // Log de la prédiction IA
        DunningLog::create([
            'campaign_id' => $campaign->id,
            'action_type' => 'ai_prediction',
            'content' => "L'IA a estimé le paiement probable au {$predictedDate} suite à l'envoi du {$channel}.",
        ]);
    }
}
