<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ClientScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // 1. Si on est en console (artisan) et non web, ne pas filtrer
        // 2. Si un client_id est défini dans la requête ou la session, filtrer
        if (!app()->runningInConsole()) {
            $clientId = request()->attributes->get('client_id')
                       ?? request()->input('client_id')
                       ?? session('current_client_id');

            if ($clientId) {
                $builder->where(function($q) use ($model, $clientId) {
                    $q->where($model->getTable() . '.client_id', $clientId)
                      ->orWhereNull($model->getTable() . '.client_id');
                });
            } else {
                // Règle 4: Si pas de client_id et pas en console, NE RIEN RENVOYER
                // On s'assure qu'on ne filtre pas les données du cabinet lui-même
                // (Seules les tables client ont le client_id, vérifié via trait)
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
