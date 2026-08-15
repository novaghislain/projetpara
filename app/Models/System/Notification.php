<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'system_notifications';

    protected $fillable = [
        'user_id', // Destinataire
        'type', // info, warning, alert, message
        'title',
        'message',
        'data', // json payload (ex: link, id_document)
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
