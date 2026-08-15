<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    protected $fillable = [
        'client_id',
        'url',
        'events', // array of events (e.g. ['invoice.created', 'payment.received'])
        'secret',
        'is_active'
    ];

    protected $casts = [
        'events' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
