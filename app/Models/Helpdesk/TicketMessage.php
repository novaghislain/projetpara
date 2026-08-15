<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'sender_type', // client, staff, system
        'sender_id', // uuid ou id
        'message',
        'attachment_path'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
