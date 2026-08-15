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
            $user = auth()->user();

            if ($user->isSuperAdmin && $user->isSuperAdmin()) {
                return;
            }

            $table = $model->getTable();
            $hasCabinet = \Schema::hasColumn($table, 'cabinet_id');
            $hasClient = \Schema::hasColumn($table, 'client_id');

            // 1. Isolation Cabinet
            if ($hasCabinet) {
                $cabinetId = $user->cabinet_id ?? null;
                if ($cabinetId) {
                    $builder->where($table . '.cabinet_id', $cabinetId);
                } else {
                    $builder->whereRaw('1 = 0');
                    return;
                }
            }

            // 2. Isolation Client / Entreprise
            if ($hasClient) {
                $activeEntrepriseId = session('active_entreprise_id');
                if ($activeEntrepriseId) {
                    $builder->where($table . '.client_id', $activeEntrepriseId);
                } else {
                    $clientIds = $user->affectations ? $user->affectations()->pluck('entreprise_id')->toArray() : [];
                    if (!empty($clientIds)) {
                        $builder->whereIn($table . '.client_id', $clientIds);
                    } else {
                        $builder->whereRaw('1 = 0');
                    }
                }
            }
        }
    }
}
