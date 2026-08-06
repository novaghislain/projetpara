<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'it_ticket_id',
        'author_id',
        'message',
        'is_internal',
    ];

    public function ticket()
    {
        return $this->belongsTo(ItTicket::class, 'it_ticket_id');
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }
}
