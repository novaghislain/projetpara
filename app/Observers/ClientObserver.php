<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\Dae\DaeAgendaEvent;
use Illuminate\Support\Facades\Auth;

class ClientObserver
{
    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        // Détecter si le régime fiscal vient d'être modifié
        if ($client->isDirty('regime_fiscal') && !empty($client->regime_fiscal)) {
            
            // Génération d'un calendrier fiscal fictif pour la démo
            // Dans un cas réel, on calculerait les vraies dates (15 de chaque mois, etc.)
            
            $userId = Auth::id() ?? $client->created_by; // Idéalement le collaborateur en charge
            $currentYear = date('Y');
            
            // Exemple : Création des déclarations TVA (Mensuelles)
            for ($month = 1; $month <= 12; $month++) {
                // Echéance le 15 de chaque mois
                $date = \Carbon\Carbon::create($currentYear, $month, 15, 8, 0, 0);
                
                // Si la date est passée dans l'année en cours, on l'ajoute pour l'année prochaine
                if ($date->isPast() && $month < date('n')) {
                    $date->addYear();
                }

                DaeAgendaEvent::firstOrCreate(
                    [
                        'client_id' => $client->id,
                        'type' => 'echeance_fiscale',
                        'title' => "TVA Mensuelle - " . $date->translatedFormat('F Y'),
                    ],
                    [
                        'user_id' => $userId,
                        'description' => 'Déclaration de TVA (Générée automatiquement suite au régime: ' . $client->regime_fiscal . ')',
                        'start_at' => $date,
                        'end_at' => $date->copy()->addHour(),
                        'status' => 'planned'
                    ]
                );
            }
        }
    }
}
