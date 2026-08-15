<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'respondent_email',
        'answers', // json
        'score' // ex: score NPS ou note sur 5
    ];

    protected $casts = [
        'answers' => 'array'
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
