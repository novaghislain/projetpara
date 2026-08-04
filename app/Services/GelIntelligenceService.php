<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Gel\Task;
use Illuminate\Support\Facades\Log;

class GelIntelligenceService
{
    /**
     * Traite un document entrant et déclenche les workflows automatisés.
     *
     * @param Document $document
     * @param string|null $documentType (ex: 'courrier', 'contrat', 'facture')
     * @return array Résumé des actions prises
     */
    public function processNewDocument(Document $document, ?string $documentType = null): array
    {
        $actionsTaken = [];

        // 1. Détection du type
        if (!$documentType && $document->tags) {
            $documentType = strtolower($document->tags);
        }

        // Si c'est un courrier entrant ou sortant
        if (str_contains((string)$documentType, 'courrier')) {
            // Créer une tâche de suivi
            $task = Task::create([
                'titre' => 'Assurer le suivi du courrier : ' . $document->file_name,
                'description' => 'Un nouveau courrier a été ajouté. Vérifier si une réponse est requise.',
                'statut' => 'a_faire',
                'date_echeance' => now()->addDays(3),
                'client_id' => $document->client_id,
                'cabinet_id' => $document->cabinet_id,
                'created_by' => $document->created_by
            ]);
            $actionsTaken[] = "Tâche de suivi de courrier créée (ID: {$task->id})";
        }

        // Si c'est un contrat ou document important nécessitant validation
        if (str_contains((string)$documentType, 'contrat') || str_contains((string)$documentType, 'validation')) {
            // Notifier le dirigeant (On enregistre une trace dans une table de notification ou de log)
            // Pour l'exemple, on crée une tâche assignée au boss (ou on utilise une vraie table de notifications)
            $task = Task::create([
                'titre' => 'VALIDATION REQUISE : ' . $document->file_name,
                'description' => 'Le contrat/document a été uploadé et nécessite votre validation finale.',
                'statut' => 'a_faire',
                'date_echeance' => now()->addDays(1),
                'client_id' => $document->client_id,
                'cabinet_id' => $document->cabinet_id,
                'created_by' => $document->created_by,
                // 'assigned_to' => ... (ID du dirigeant idéalement)
            ]);
            
            // On pourrait aussi utiliser la table `notifications` de Laravel
            $actionsTaken[] = "Demande de validation générée pour le Dirigeant";
        }

        // Si c'est une facture, on prépare une relance ou un rappel
        if (str_contains((string)$documentType, 'facture')) {
            $task = Task::create([
                'titre' => 'Vérifier l\'encaissement : ' . $document->file_name,
                'description' => 'Facture ajoutée. Programmer une vérification de paiement à J+30.',
                'statut' => 'a_faire',
                'date_echeance' => now()->addDays(30),
                'client_id' => $document->client_id,
                'cabinet_id' => $document->cabinet_id,
                'created_by' => $document->created_by
            ]);
            $actionsTaken[] = "Tâche de vérification de paiement créée (J+30)";
        }

        return $actionsTaken;
    }
}
