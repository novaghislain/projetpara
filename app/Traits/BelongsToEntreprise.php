<?php

namespace App\Traits;

use App\Models\Entreprise;
use App\Scopes\EntrepriseScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToEntreprise
{
    /**
     * Boot the trait and apply the global scope.
     */
    protected static function bootBelongsToEntreprise()
    {
        static::addGlobalScope(new EntrepriseScope);

        // Assign automatically the active entreprise_id on creation
        static::creating(function ($model) {
            if (empty($model->entreprise_id) && session()->has('active_entreprise_id')) {
                $model->entreprise_id = session('active_entreprise_id');
            }
        });
    }

    /**
     * Relation with Entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }
}
