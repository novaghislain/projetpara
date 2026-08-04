<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelDevis extends Model
{
    protected $table = 'gel_devis';

    protected $fillable = [
        'client_id',
        'numero',
        'client_nom',
        'montant',
        'date_devis',
        'statut',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_devis' => 'date',
    ];
}
