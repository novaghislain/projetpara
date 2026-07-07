<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Filtre les requêtes pour ne retourner que les données du cabinet actif.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        if (! $user) return;

        // Super-admin voit tout
        if ($user->isSuperAdmin()) return;

        $cabinetId = $this->resolveCabinetId($user);
        if ($cabinetId && $this->modelHasCabinetId($model)) {
            $builder->where('cabinet_id', $cabinetId);
        }
    }

    /**
     * Résout le cabinet_id depuis l'utilisateur connecté.
     */
    private function resolveCabinetId($user): ?int
    {
        if ($user->cabinet_id) {
            return (int) $user->cabinet_id;
        }

        if ($user->client_id && $user->client && $user->client->company && $user->client->company->cabinet_id) {
            return (int) $user->client->company->cabinet_id;
        }

        return null;
    }

    /**
     * Vérifie si le modèle possède une colonne cabinet_id.
     */
    private function modelHasCabinetId(Model $model): bool
    {
        return $model->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($model->getTable(), 'cabinet_id');
    }
}
