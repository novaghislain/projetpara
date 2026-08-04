<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCallLog extends Model
{
    protected $table = 'client_call_logs';

    protected $fillable = [
        'client_id', 'user_id', 'direction', 'contact_name',
        'phone', 'notes', 'statut', 'called_at', 'duration_minutes',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
