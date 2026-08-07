<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItTemporaryAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'informaticien_id',
        'client_id',
        'reason',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function informaticien()
    {
        return $this->belongsTo(\App\Models\User::class, 'informaticien_id');
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function isValid()
    {
        return is_null($this->revoked_at) && $this->expires_at->isFuture();
    }
}
