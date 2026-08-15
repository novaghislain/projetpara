<?php

namespace App\Services;

use App\Models\Gel\Client;
use App\Models\Gel\CoordinationEvent;
use App\Models\User;
use App\Notifications\RealTimeNotification;
use Illuminate\Support\Facades\Log;

/**
 * Coordination Secrétaire ↔ Comptable sur une même entreprise.
 *
 * Règles non négociables appliquées ici :
 *  - la relation n'existe que sur une entreprise où LES DEUX sont réellement
 *    rattachés en commun (client_id / clients_assignes / pivot user_clients) ;
 *  - aucune interaction possible si l'un des deux n'intervient pas sur ce client ;
 *  - chaque action est journalisée (coordination_events) et notifiée en temps
 *    réel (RealTimeNotification → user.{id}) — jamais d'action irréversible auto.
 */
class CoordinationService
{
    // ── Rôles professionnels (indépendants de l'attachement) ──────────────
    public static function isSecretaire(User $user): bool
    {
        return in_array($user->role, ['secretaire', 'secretary', 'admin', 'director', 'super_admin'], true)
            || $user->role_secretaire;
    }

    public static function isComptable(User $user): bool
    {
        return in_array($user->role, ['comptable', 'accountant', 'collaborator', 'pole_responsible', 'director', 'super_admin'], true);
    }

    /**
     * Un professionnel est-il rattaché à l'entreprise donnée (gel_clients.id) ?
     * Modèle 1 (invitation) : user_clients pivot / client_id.
     * Modèle 2 (pool GEL)  : workspace_type=gel_pool + clients_assignes.
     * Cabinet : un membre du cabinet (cabinet_id) sert tous les clients du cabinet.
     */
    public static function isAttachedToClient(User $user, $clientId): bool
    {
        if ($user->role === 'admin' || $user->isAutonomousSecretary()) {
            return true;
        }

        // Rattachement direct (Modèle 1)
        if ((string) $user->client_id === (string) $clientId) {
            return true;
        }

        // Rattachement par pivot user_clients (Modèle 1 — invitation)
        if (\App\Models\UserClient::where('user_id', $user->id)
            ->where('client_id', $clientId)->exists()) {
            return true;
        }

        // Modèle 2 — pool GEL : affectation explicite via clients_assignes
        // Feature removed as clients_assignes does not exist in utilisateurs table.

        // Personnel de cabinet : sert tous les clients du cabinet
        $client = Client::find($clientId);
        if ($client && $user->cabinet_id && (string) $client->cabinet_id === (string) $user->cabinet_id) {
            return true;
        }

        return false;
    }

    /**
     * Le secrétaire rattaché à l'entreprise donnée (le plus précis d'abord).
     */
    public static function getSecretaireForClient($clientId): ?User
    {
        return self::staffForClient($clientId, 'secretaire');
    }

    /**
     * Le comptable rattaché à l'entreprise donnée (le plus précis d'abord).
     */
    public static function getComptableForClient($clientId): ?User
    {
        return self::staffForClient($clientId, 'comptable');
    }

    /**
     * Les deux professionnels rattachés en commun à l'entreprise.
     */
    public static function pairForClient($clientId): array
    {
        return [
            'secretaire' => self::getSecretaireForClient($clientId),
            'comptable'  => self::getComptableForClient($clientId),
        ];
    }

    /**
     * Trouve un staff GEL (secrétaire ou comptable) rattaché à ce client.
     */
    private static function staffForClient($clientId, string $role): ?User
    {
        // 1. Chercher par pivot user_clients
        $userViaPivot = User::whereIn('id', function ($query) use ($clientId) {
            $query->select('user_id')->from('user_clients')->where('client_id', $clientId);
        })
        ->where(function ($q) use ($role) {
            $q->where('role', $role);
        })->first();

        if ($userViaPivot) {
            return $userViaPivot;
        }

        // 2. Chercher dans les assignations explicites (JSON)
        // Feature removed as the clients_assignes column does not exist in utilisateurs table.

        // 3. Fallback : n'importe quel staff du cabinet qui a ce rôle (s'il y a un cabinet_id)
        $client = Client::find($clientId);
        if ($client && $client->cabinet_id) {
            return User::where('cabinet_id', $client->cabinet_id)
                ->where(function ($q) use ($role) {
                    $q->where('role', $role);
                })->first();
        }

        return null;
    }

    /**
     * Journalise un événement de coordination (en français).
     */
    public static function log(
        $clientId,
        User $actor,
        ?int $recipientId,
        string $type,
        string $subject,
        ?string $body = null,
        ?string $link = null,
        ?int $documentId = null,
        ?int $taskId = null,
        ?int $messageId = null,
        string $icon = 'fas fa-people-arrows'
    ): CoordinationEvent {
        $role = self::isSecretaire($actor) ? 'secretaire' : (self::isComptable($actor) ? 'comptable' : ($actor->role ?? 'autre'));

        return CoordinationEvent::create([
            'client_id'    => $clientId,
            'cabinet_id'   => $actor->cabinet_id,
            'actor_id'     => $actor->id,
            'actor_role'   => $role,
            'actor_name'   => $actor->name,
            'recipient_id' => $recipientId,
            'type'         => $type,
            'document_id'  => $documentId,
            'task_id'      => $taskId,
            'message_id'   => $messageId,
            'subject'      => $subject,
            'body'         => $body,
            'link'         => $link,
            'icon'         => $icon,
        ]);
    }

    /**
     * Notifie le destinataire en temps réel (canal privé user.{id} → badge + son).
     */
    public static function notify(?User $recipient, string $title, string $description, string $link, string $icon = 'fas fa-people-arrows'): void
    {
        if (!$recipient) {
            return;
        }

        try {
            $recipient->notify(new RealTimeNotification($title, $description, $link, $icon));
        } catch (\Exception $e) {
            Log::warning('CoordinationService::notify échoué: ' . $e->getMessage());
        }
    }

    /**
     * Fil d'activité de coordination d'une entreprise (S4.3 / S1.4).
     */
    public static function feedForClient($clientId, int $limit = 50): \Illuminate\Support\Collection
    {
        return CoordinationEvent::with(['actor:id,name,role', 'document:id,name'])
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
