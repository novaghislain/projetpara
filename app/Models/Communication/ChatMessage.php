<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'sender_id', // utilisateur envoyant le message
        'receiver_id', // utilisateur destinataire (null si message de groupe)
        'client_id', // contexte du tenant
        'message',
        'is_read'
    ];

    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'receiver_id');
    }
}
