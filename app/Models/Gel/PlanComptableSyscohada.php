<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanComptableSyscohada extends Model
{
    use HasFactory;

    protected $table = 'plan_comptable_syscohada';

    protected $fillable = [
        'numero',
        'libelle',
        'classe',
        'est_actif'
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'classe' => 'integer'
    ];
}
