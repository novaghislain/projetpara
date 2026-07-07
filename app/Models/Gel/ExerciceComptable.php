<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class ExerciceComptable extends Model
{
    protected $table = 'gel_exercices';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'libelle',
        'date_debut',
        'date_fin',
        'cloture',
        'cloture_at',
        'cloture_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'cloture' => 'boolean',
        'cloture_at' => 'datetime',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function cloturePar()
    {
        return $this->belongsTo(User::class, 'cloture_par');
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'exercice_id');
    }

    public function scopeNonCloture($query)
    {
        return $query->where('cloture', false);
    }

    public function scopeEnCours($query)
    {
        return $query->where('cloture', false)
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now());
    }
}
