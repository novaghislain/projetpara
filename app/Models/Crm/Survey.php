<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'description',
        'type', // csat, nps, feedback
        'questions', // json array of questions
        'is_active',
        'expires_at'
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean'
    ];

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
