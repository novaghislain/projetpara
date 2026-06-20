<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserClient extends Model
{
    protected $table = 'user_clients';

    protected $fillable = [
        'user_id',
        'client_id',
        'role',
        'is_active',
        'invited_by',
        'joined_at',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
