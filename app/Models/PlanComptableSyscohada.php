<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanComptableSyscohada extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'plan_comptable_syscohada';

    protected $fillable = [
        'code',
        'intitule',
        'classe',
        'niveau',
        'type_compte'
    ];

}
