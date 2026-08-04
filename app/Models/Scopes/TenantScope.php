<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scope global TenantScope - Filtre multi-entreprise par client actif.
 *
 * Applique automatiquement un filtre 'client_id' sur les requêtes Eloquent
 * pour isoler les données par entreprise. Ne s'applique pas aux super-admins
 * ni aux comptables sans contexte client actif.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!app()->runningInConsole() && auth()->check()) {
            $cabinetId = auth()->user()->cabinet_id ?? null;

            if ($cabinetId) {
                $builder->where($model->getTable() . '.cabinet_id', $cabinetId);
            } else {
                // If user doesn't have a cabinet_id, they shouldn't see tenant-scoped data
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
