<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'email',
        'role',
        'token',
        'status',
        'expires_at',
        'portals',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'portals' => 'array',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
