<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;

/**
 * Modèle représentant un exercice comptable (période annuelle).
 *
 * Table associée : `exercices` (via convention Laravel, préfixe Compta)
 *
 * Relations :
 * - Un exercice appartient à un client (Client)
 * - Un exercice peut avoir plusieurs écritures comptables (Ecriture)
 */
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
