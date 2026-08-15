<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelBonsCommande extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_bons_commande';

    protected $fillable = [
        'client_id',
        'numero',
        'date_commande',
        'total_ht',
        'total_ttc',
        'statut'
    ];

}
