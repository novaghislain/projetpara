<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une ligne d'écriture comptable (débit/crédit).
 *
 * Table associée : `ligne_ecritures` (via convention Laravel, préfixe Compta)
 *
 * Relations :
 * - Une ligne appartient à une écriture comptable (Ecriture)
 * - Une ligne est liée à un compte comptable (Compte)
 */
class LigneEcriture extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecriture_id', 'compte_id', 'libelle', 'debit', 'credit', 'is_lettree', 'lettrage'
    ];

    protected $casts = [
        'is_lettree' => 'boolean',
    ];

    public function ecriture()
    {
        return $this->belongsTo(Ecriture::class);
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
