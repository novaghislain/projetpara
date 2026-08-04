<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelDepense extends Model
{
    protected $table = 'gel_depenses';

    protected $fillable = [
        'client_id',
        'libelle',
        'montant',
        'date_depense',
        'mode_paiement',
        'categorie',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'date',
    ];
}
