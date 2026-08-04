<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Journal (Journal comptable - espace Gel).
 *
 * Représente un journal comptable au niveau du module Gel.
 * Chaque journal est défini par un code (AC, VE, BQ, CA, OD, SA, AN, IN)
 * et un type. Il peut être activé/désactivé.
 * Table associée : `gel_journaux`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int|null $client_id ID du client
 * @property string $code Code du journal
 * @property string $libelle Libellé du journal
 * @property string $type Type de journal
 * @property bool $actif Si le journal est actif
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\EcritureComptable[] $ecritures Écritures comptables
 */
class Journal extends Model
{
    protected $table = 'gel_journaux';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'code',
        'libelle',
        'type',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'journal_id');
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
