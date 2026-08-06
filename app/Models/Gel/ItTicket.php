<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'author_id',
        'assigned_to',
        'subject',
        'status',
        'priority',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(ItTicketMessage::class);
    }
}
