<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\ClientScopedModel;

/**
 * Modèle CompteComptable (Plan comptable - espace Gel).
 *
 * Représente un compte du plan comptable SYSCOHADA dans le module Gel.
 * Structure hiérarchique par classe (1-8) avec support des sous-comptes.
 * Table associée : `gel_comptes_comptables`.
 *
 * @property int $id
 * @property int|null $cabinet_id ID du cabinet
 * @property int|null $client_id ID du client
 * @property string $code Code du compte comptable
 * @property string $intitule Intitulé/Libellé du compte
 * @property string $classe Classe SYSCOHADA (1-8)
 * @property string|null $type Type de compte (bilan, gestion, etc.)
 * @property bool $actif Si le compte est actif
 * @property bool $syscohada Si le compte provient du plan SYSCOHADA standard
 * @property int|null $niveau Niveau hiérarchique
 * @property string|null $code_parent Code du compte parent
 * @property string|null $notes Notes additionnelles
 *
 * @property-read \App\Models\Gel\Cabinet|null $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\LigneEcriture[] $lignesEcriture Lignes d'écriture liées
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\CompteComptable[] $enfants Comptes enfants
 * @property-read \App\Models\Gel\CompteComptable|null $parent Compte parent
 */
class CompteComptable extends Model
{
    use SoftDeletes, ClientScopedModel;

    protected $table = 'gel_comptes_comptables';

    protected $fillable = [
        'cabinet_id',
        'code',
        'intitule',
        'classe',
        'type',
        'actif',
        'syscohada',
        'niveau',
        'code_parent',
        'notes',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'syscohada' => 'boolean',
        'niveau' => 'integer',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function lignesEcriture()
    {
        return $this->hasMany(LigneEcriture::class, 'compte_id');
    }

    public function enfants()
    {
        return $this->hasMany(CompteComptable::class, 'code_parent', 'code');
    }

    public function parent()
    {
        return $this->belongsTo(CompteComptable::class, 'code_parent', 'code');
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeByClasse($query, $classe)
    {
        return $query->where('classe', $classe);
    }
}
