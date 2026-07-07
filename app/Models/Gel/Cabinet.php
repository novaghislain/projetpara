<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Cabinet extends Model
{
    protected $table = 'gel_cabinets';

    protected $fillable = [
        'nom',
        'slug',
        'email',
        'telephone',
        'adresse',
        'ville',
        'ifu',
        'rc',
        'logo',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'cabinet_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'cabinet_id');
    }

    public function comptesComptables()
    {
        return $this->hasMany(CompteComptable::class, 'cabinet_id');
    }

    public function journaux()
    {
        return $this->hasMany(Journal::class, 'cabinet_id');
    }

    public function exercices()
    {
        return $this->hasMany(ExerciceComptable::class, 'cabinet_id');
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'cabinet_id');
    }

    public function invitations()
    {
        return $this->hasMany(ClientInvitation::class, 'cabinet_id');
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
