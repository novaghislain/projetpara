<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeOfficeSupply extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_office_supplies';

    protected $fillable = [
        'client_id',
        'nom',
        'reference',
        'stock',
        'seuil_alerte',
        'unite'
    ];

}
