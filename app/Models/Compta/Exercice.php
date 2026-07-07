<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;

class Exercice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'libelle', 'date_debut', 'date_fin', 'is_clos', 
        'closed_at', 'closed_by', 'mois_clotures'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'closed_at' => 'datetime',
        'is_clos' => 'boolean',
        'mois_clotures' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }
}
