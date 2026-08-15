<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EntrepriseScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // On récupère l'entreprise_id active de la session (si définie)
        if (session()->has('active_entreprise_id')) {
            $builder->where($model->getTable() . '.entreprise_id', session('active_entreprise_id'));
        }
    }
}
