<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

/**
 * Modèle représentant un budget prévisionnel pour un exercice comptable.
 *
 * Table associée : `budgets` (via convention Laravel, préfixe Compta)
 *
 * Relations :
 * - Un budget appartient à un client (Client)
 * - Un budget appartient à un exercice (Exercice)
 *
 * Les lignes budgétaires sont stockées sous forme de JSON dans le champ `lignes_budget`.
 */
class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'exercice_id', 'libelle', 'departement', 'lignes_budget', 'is_actif'
    ];

    protected $casts = [
        'lignes_budget' => 'array',
        'is_actif' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }
}
