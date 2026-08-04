<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelRapprochement extends Model
{
    protected $table = 'gel_rapprochements';

    protected $fillable = [
        'client_id',
        'compte_id',
        'solde_bancaire',
        'solde_comptable',
        'date_rapprochement',
        'statut',
    ];

    protected $casts = [
        'solde_bancaire' => 'decimal:2',
        'solde_comptable' => 'decimal:2',
        'date_rapprochement' => 'date',
    ];
}
