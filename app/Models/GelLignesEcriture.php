<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelLignesEcriture extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_lignes_ecriture';

    protected $fillable = [
        'ecriture_id',
        'compte_id',
        'tiers_id',
        'sens',
        'montant',
        'libelle_ligne',
        'reference',
        'date_echeance',
        'lettree',
        'lettre'
    ];

    public function ecriture()
    {
        return $this->belongsTo(GelEcriture::class, 'ecriture_id', 'id');
    }

}
