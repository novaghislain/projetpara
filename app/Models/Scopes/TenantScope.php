<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Appliquer le scope : filtrer par le client actif de l'utilisateur.
     *
     * Ne s'applique QUE si :
     * - L'utilisateur est authentifié
     * - L'utilisateur a un active_client_id défini
     * - L'utilisateur n'est PAS super_admin
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (!$user) return;

        // Les Super Admin voient toutes les données
        if ($user->isSuperAdmin()) return;

        // Les comptables voient les données de leurs clients assignés
        // (cette logique est gérée par le contrôleur, pas par un scope global)
        // On filtre quand même par active_client_id si défini
        if ($user->isComptable() && !$user->active_client_id) return;

        $clientId = $user->active_client_id ?? $user->client_id;

        if (!$clientId) return;

        $builder->where($model->getTable() . '.client_id', $clientId);
    }
}
