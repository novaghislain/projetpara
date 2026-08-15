<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entreprise extends Model
{
    use HasUuids;

    protected $table = 'entreprises';

    protected $fillable = [
        'raison_sociale',
        'pays_code',
        'ifu',
        'rccm',
        'secteur_activite',
        'regime_fiscal',
        'modele_usage',
        'statut_abonnement',
        'cree_le',
        'supprime_le',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = null;

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class, 'entreprise_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ModuleEntreprise::class, 'entreprise_id');
    }
}
