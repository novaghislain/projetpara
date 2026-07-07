<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompteComptable extends Model
{
    use SoftDeletes;

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
