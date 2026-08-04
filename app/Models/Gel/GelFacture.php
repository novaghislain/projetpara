<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelFacture extends Model
{
    protected $table = 'gel_factures';

    protected $fillable = [
        'client_id',
        'type',
        'numero',
        'client_nom',
        'montant',
        'date_facture',
        'statut',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_facture' => 'date',
    ];
}
