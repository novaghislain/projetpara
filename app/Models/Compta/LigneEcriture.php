<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
