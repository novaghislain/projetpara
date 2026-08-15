<?php

namespace App\Models\Governance;

use Illuminate\Database\Eloquent\Model;

class MeetingMinute extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'meeting_date',
        'type', // board_meeting, general_assembly, executive_committee
        'content', // Texte ou HTML du PV
        'decisions', // json list of decisions
        'file_path', // PDF du PV signé
        'status' // draft, published, signed
    ];

    protected $casts = [
        'decisions' => 'array',
        'meeting_date' => 'date'
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
