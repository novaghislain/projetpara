<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItTemporaryAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'informaticien_id',
        'reason',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function informaticien()
    {
        return $this->belongsTo(User::class, 'informaticien_id');
    }
}
