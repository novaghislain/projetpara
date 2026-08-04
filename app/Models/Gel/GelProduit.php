<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelProduit extends Model
{
    protected $table = 'gel_produits';

    protected $fillable = [
        'client_id',
        'nom',
        'description',
        'prix',
        'stock',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'stock' => 'integer',
    ];
}
