<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Model;

class EventAttendee extends Model
{
    protected $fillable = [
        'event_id',
        'email',
        'name',
        'status' // pending, accepted, declined
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
