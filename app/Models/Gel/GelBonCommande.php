<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelBonCommande extends Model
{
    protected $table = 'gel_bons_commande';

    protected $fillable = [
        'client_id',
        'numero',
        'fournisseur_nom',
        'montant',
        'date_commande',
        'statut',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_commande' => 'date',
    ];
}
