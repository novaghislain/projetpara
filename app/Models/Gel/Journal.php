<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

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
