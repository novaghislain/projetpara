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
        return in_array($user->role, ['secretaire', 'secretary'], true)
            || $user->role_secretaire
            || $user->hasRole(['secretaire', 'secretary']);
    }

    public static function isComptable(User $user): bool
    {
        return in_array($user->role, ['comptable', 'accountant', 'comptable_senior', 'chef_comptable', 'comptable_junior'], true)
            || $user->hasRole(['comptable', 'accountant', 'comptable_senior', 'chef_comptable', 'comptable_junior']);
    }

    /**
     * Un professionnel est-il rattaché à l'entreprise donnée (gel_clients.id) ?
     * Modèle 1 (invitation) : user_clients pivot / client_id.
     * Modèle 2 (pool GEL)  : workspace_type=gel_pool + clients_assignes.
     * Cabinet : un membre du cabinet (cabinet_id) sert tous les clients du cabinet.
     */
    public static function isAttachedToClient(User $user, int $clientId): bool
    {
        // Rattachement direct (Modèle 1)
        if ((int) $user->client_id === $clientId) {
            return true;
        }

        // Rattachement par pivot user_clients (Modèle 1 — invitation)
        if (\App\Models\UserClient::where('user_id', $user->id)
            ->where('client_id', $clientId)->exists()) {
            return true;
        }

        // Modèle 2 — pool GEL : affectation explicite via clients_assignes
        $assignes = $user->clients_assignes;
        if (is_array($assignes) && in_array($clientId, array_map('intval', $assignes), true)) {
            return true;
        }

        // Personnel de cabinet : sert tous les clients du cabinet
        $client = Client::find($clientId);
        if ($client && $user->cabinet_id && (int) $client->cabinet_id === (int) $user->cabinet_id) {
            return true;
        }

        return false;
    }

    /**
     * Le secrétaire rattaché à l'entreprise donnée (le plus précis d'abord).
     */
    public static function getSecretaireForClient(int $clientId): ?User
    {
        return self::staffForClient($clientId, 'secretaire');
    }

    /**
     * Le comptable rattaché à l'entreprise donnée (le plus précis d'abord).
     */
    public static function getComptableForClient(int $clientId): ?User
    {
        return self::staffForClient($clientId, 'comptable');
    }

    /**
     * Les deux professionnels rattachés en commun à l'entreprise.
     */
    public static function pairForClient(int $clientId): array
    {
        return [
            'secretaire' => self::getSecretaireForClient($clientId),
            'comptable'  => self::getComptableForClient($clientId),
        ];
    }

    private static function staffForClient(int $clientId, string $role): ?User
    {
        $candidates = User::where('is_active', true)
            ->orderByRaw('FIELD(client_id, ?) DESC', [$clientId]) // le rattachement direct d'abord
            ->get()
            ->filter(fn ($u) => $role === 'secretaire' ? self::isSecretaire($u) : self::isComptable($u))
            ->filter(fn ($u) => self::isAttachedToClient($u, $clientId))
            ->values();

        return $candidates->first();
    }

    /**
     * Journalise un événement de coordination (en français).
     */
    public static function log(
        int $clientId,
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
    public static function feedForClient(int $clientId, int $limit = 50): \Illuminate\Support\Collection
    {
        return CoordinationEvent::with(['actor:id,name,role', 'document:id,name'])
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
