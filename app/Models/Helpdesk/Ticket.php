<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'client_id',
        'subject',
        'category', // billing, technical, accounting, general
        'status', // open, in_progress, resolved, closed
        'priority' // low, medium, high, urgent
    ];

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}
