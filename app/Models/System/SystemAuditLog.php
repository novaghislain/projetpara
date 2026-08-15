<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class SystemAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action', // create, update, delete, login, export
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
