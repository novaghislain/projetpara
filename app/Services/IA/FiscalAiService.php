<?php

namespace App\Services\IA;

use App\Models\Client;
use App\Models\AiSuggestion;
use Illuminate\Support\Facades\Log;

class FiscalAiService
{
    /**
     * Analyse les données comptables et génère des suggestions fiscales pour un client.
     */
    public function generateTaxSuggestions(Client $client)
    {
        // Simulation de l'analyse IA des écritures comptables
        Log::info("Agent Fiscal : Analyse des données pour le client {$client->company_name}");
        
        // Suggestion 1 : Déclaration de TVA
        $this->createSuggestion(
            $client,
            'Déclaration TVA - Pré-remplissage',
            "D'après les écritures comptables du mois, la TVA collectée est estimée à 450 000 FCFA et la TVA déductible à 120 000 FCFA. Souhaitez-vous générer le brouillon de la télédéclaration ?",
            'fiscal',
            'pending'
        );

        // Suggestion 2 : Échéance IRPP/CNSS
        $this->createSuggestion(
            $client,
            'Échéance IRPP/CNSS imminente',
            "Les cotisations sociales et l'IRPP de ce mois arrivent à échéance dans 3 jours. Voulez-vous préparer l'ordre de virement ?",
            'fiscal',
            'pending'
        );

        return true;
    }

    private function createSuggestion(Client $client, $title, $description, $agent, $status)
    {
        return AiSuggestion::create([
            'client_id' => $client->id,
            'agent' => $agent,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'action_type' => 'generate_draft',
            'action_payload' => ['module' => 'tax'],
        ]);
    }
}
