<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'key',
        'last_used_at',
        'expires_at',
        'is_active'
    ];

    protected $hidden = [
        'key'
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
