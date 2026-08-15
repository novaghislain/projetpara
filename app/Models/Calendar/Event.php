<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'calendar_events';

    protected $fillable = [
        'client_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location', // url meet ou adresse physique
        'type', // meeting, reminder, deadline
        'status' // scheduled, cancelled, completed
    ];

    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }
}
