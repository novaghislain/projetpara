<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelProduit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_produits';

    protected $fillable = [
        'client_id',
        'nom',
        'reference',
        'description',
        'prix_ht',
        'unite',
        'actif'
    ];

}
