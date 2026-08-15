<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalAssembly extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_assemblies';

    protected $fillable = [
        'client_id',
        'type',
        'date_assemblee',
        'lieu',
        'ordre_du_jour',
        'deliberations',
        'statut'
    ];

}
