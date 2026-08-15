<?php

namespace App\Models\Compliance;

use Illuminate\Database\Eloquent\Model;

class GdprConsent extends Model
{
    protected $fillable = [
        'user_id', // l'utilisateur ou contact concerné
        'consent_type', // terms_of_service, marketing, data_processing
        'is_granted',
        'ip_address',
        'user_agent',
        'granted_at',
        'revoked_at'
    ];

    protected $casts = [
        'is_granted' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
