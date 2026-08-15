<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class SaaSIndustry extends Model
{
    protected $table = 'saas_industries';

    protected $fillable = [
        'name', // ex: BTP, Logistique, Clinique, Commerce de détail, Cabinet Comptable
        'description',
        'default_modules' // json array (ex: ['accounting', 'fleet', 'projects'])
    ];

    protected $casts = [
        'default_modules' => 'array'
    ];
}
