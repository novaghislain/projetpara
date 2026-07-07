<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    /**
     * Détermine si l'utilisateur peut voir la liste des clients.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'client.consulter',
            'client.creer',
            'client.modifier',
        ]);
    }

    /**
     * Détermine si l'utilisateur peut voir un client spécifique.
     */
    public function view(User $user, Client $client): bool
    {
        if (! $user->hasPermissionTo('client.consulter')) {
            return false;
        }

        // Vérification tenant : l'utilisateur doit être dans le même cabinet
        return $this->isSameCabinet($user, $client);
    }

    /**
     * Détermine si l'utilisateur peut créer un client.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('client.creer');
    }

    /**
     * Détermine si l'utilisateur peut modifier un client.
     */
    public function update(User $user, Client $client): bool
    {
        if (! $user->hasPermissionTo('client.modifier')) {
            return false;
        }

        return $this->isSameCabinet($user, $client);
    }

    /**
     * Détermine si l'utilisateur peut suspendre un client.
     */
    public function suspend(User $user, Client $client): bool
    {
        if (! $user->hasPermissionTo('client.suspendre')) {
            return false;
        }

        return $this->isSameCabinet($user, $client);
    }

    /**
     * Vérifie que l'utilisateur et le client appartiennent au même cabinet.
     */
    private function isSameCabinet(User $user, Client $client): bool
    {
        $userCabinetId = $user->cabinet_id
            ?? ($user->client?->company?->cabinet_id)
            ?? session('current_cabinet_id');

        $clientCabinetId = $client->company?->cabinet_id
            ?? $client->cabinet_id;

        if (! $userCabinetId || ! $clientCabinetId) {
            return $user->isSuperAdmin(); // Seul super-admin passe sans cabinet
        }

        return (int) $userCabinetId === (int) $clientCabinetId;
    }
}
