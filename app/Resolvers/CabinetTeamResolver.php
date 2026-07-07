<?php

namespace App\Resolvers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

class CabinetTeamResolver implements PermissionsTeamResolver
{
    /**
     * Team ID temporairement forcé (pour les contextes où Auth n'est pas disponible).
     */
    protected static ?int $forcedTeamId = null;

    /**
     * Résout le cabinet_id pour le mode équipe (multi-tenant) de Spatie Permission.
     */
    public function resolve(): ?int
    {
        return $this->getPermissionsTeamId();
    }

    /**
     * Retourne le cabinet_id actif pour l'utilisateur connecté.
     */
    public function getPermissionsTeamId(): int|string|null
    {
        // Team ID forcé (pour les contextes hors requête HTTP)
        if (static::$forcedTeamId !== null) {
            return static::$forcedTeamId;
        }

        $user = Auth::user();

        if (! $user) {
            return session('current_cabinet_id', null);
        }

        // Si l'utilisateur a un cabinet_id directement
        if ($user->cabinet_id) {
            return (int) $user->cabinet_id;
        }

        // Si c'est un client lié à une company avec un cabinet_id
        if ($user->client_id && method_exists($user, 'client') && $user->client) {
            if ($user->client->company && $user->client->company->cabinet_id) {
                return (int) $user->client->company->cabinet_id;
            }
            // Fallback: le client_id lui-même sert de cabinet
            if ($user->client_id) {
                return (int) $user->client_id;
            }
        }

        // Fallback: via active_client_id
        $activeClientId = $user->active_client_id;
        if ($activeClientId) {
            return (int) $activeClientId;
        }

        return null;
    }

    /**
     * Force un team ID pour la requête courante.
     *
     * @param  int|string|Model|null  $id
     */
    public function setPermissionsTeamId($id): void
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        static::$forcedTeamId = $id ? (int) $id : null;
    }

    /**
     * Réinitialise le team ID forcé.
     */
    public static function clear(): void
    {
        static::$forcedTeamId = null;
    }
}
