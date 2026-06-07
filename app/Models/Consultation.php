<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'user_id',
        'blood_group',
        'health_info',
        'area',
        'analysis_type',
        'photo_path',
        'results',
        'answers',
        'skin_score',
        'diagnostic',
        'recommended_product_ids',
        'ai_analysis',
        'status',
    ];

    protected $casts = [
        'results' => 'array',
        'health_info' => 'array',
        'answers' => 'array',
        'skin_score' => 'array',
        'diagnostic' => 'array',
        'recommended_product_ids' => 'array',
        'ai_analysis' => 'array',
    ];
}
