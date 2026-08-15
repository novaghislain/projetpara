<?php

namespace App\Services\Crm;

use App\Models\Crm\Opportunity;

class CrmService
{
    /**
     * Avance l'opportunité à l'étape suivante du Kanban
     */
    public function advanceOpportunityStage(int $opportunityId)
    {
        $opportunity = Opportunity::findOrFail($opportunityId);
        
        $stages = ['new', 'qualified', 'proposition', 'won', 'lost'];
        
        $currentIndex = array_search($opportunity->stage, $stages);
        
        // Si ce n'est pas la dernière étape et que ce n'est pas "lost" ou "won"
        if ($currentIndex !== false && $currentIndex < 3) {
            $opportunity->stage = $stages[$currentIndex + 1];
            // Augmenter la probabilité proportionnellement
            $opportunity->probability = min(100, $opportunity->probability + 25);
            $opportunity->save();
        }

        return $opportunity;
    }

    /**
     * Marque l'opportunité comme perdue
     */
    public function markAsLost(int $opportunityId)
    {
        $opportunity = Opportunity::findOrFail($opportunityId);
        $opportunity->stage = 'lost';
        $opportunity->probability = 0;
        $opportunity->save();

        return $opportunity;
    }
}
