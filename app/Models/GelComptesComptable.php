<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelComptesComptable extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_comptes_comptables';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'code',
        'intitule',
        'classe',
        'niveau',
        'type_compte',
        'actif',
        'est_syscohada',
        'parent_id',
        'description'
    ];

}
