<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AccountingJournalLine;

class JournalEntryPolicy
{
    /**
     * Voir une écriture comptable.
     */
    public function view(User $user, AccountingJournalLine $entry): bool
    {
        if (! $user->hasPermissionTo('comptabilite.consulter')) {
            return false;
        }

        return $this->isSameClient($user, $entry);
    }

    /**
     * Créer une écriture comptable.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('comptabilite.ecrire');
    }

    /**
     * Modifier une écriture comptable (non validée seulement).
     */
    public function update(User $user, AccountingJournalLine $entry): bool
    {
        if ($entry->is_validated) {
            return false; // Ne peut pas modifier une écriture validée
        }

        if (! $user->hasPermissionTo('comptabilite.ecrire')) {
            return false;
        }

        return $this->isSameClient($user, $entry);
    }

    /**
     * Valider une écriture comptable.
     */
    public function validate(User $user, AccountingJournalLine $entry): bool
    {
        if (! $user->hasPermissionTo('comptabilite.valider')) {
            return false;
        }

        return $this->isSameClient($user, $entry);
    }

    /**
     * Supprimer une écriture comptable (non validée seulement).
     */
    public function delete(User $user, AccountingJournalLine $entry): bool
    {
        if ($entry->is_validated) {
            return false;
        }

        if (! $user->hasPermissionTo('comptabilite.ecrire')) {
            return false;
        }

        return $this->isSameClient($user, $entry);
    }

    /**
     * Exporter les écritures.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo('comptabilite.exporter');
    }

    /**
     * Vérifie que l'utilisateur et l'écriture sont dans le même scope client.
     */
    private function isSameClient(User $user, AccountingJournalLine $entry): bool
    {
        $clientId = $user->active_client_id ?? $user->client_id;
        if (! $clientId) {
            return $user->isSuperAdmin() || $user->hasPermissionTo('comptabilite.consulter');
        }

        return (int) $entry->client_id === (int) $clientId;
    }
}
