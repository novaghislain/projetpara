<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelDevi extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_devis';

    protected $fillable = [
        'client_id',
        'user_id',
        'numero',
        'date_devis',
        'date_validite',
        'total_ht',
        'total_ttc',
        'statut'
    ];

}
