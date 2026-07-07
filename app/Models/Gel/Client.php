<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'gel_clients';

    protected $fillable = [
        'cabinet_id',
        'nom_entreprise',
        'sigle',
        'email',
        'telephone',
        'adresse',
        'ville',
        'ifu',
        'rc',
        'secteur_activite',
        'logo',
        'statut',
    ];

    protected $casts = [
        'statut' => 'string',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'client_id');
    }

    public function invitations()
    {
        return $this->hasMany(ClientInvitation::class, 'client_id');
    }

    public function journaux()
    {
        return $this->hasMany(Journal::class, 'client_id');
    }

    public function exercices()
    {
        return $this->hasMany(ExerciceComptable::class, 'client_id');
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'client_id');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }
}
