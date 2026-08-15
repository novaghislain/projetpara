<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelFacturesHonoraire extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_factures_honoraires';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'numero',
        'date_facture',
        'montant_ht',
        'montant_ttc',
        'statut'
    ];

}
