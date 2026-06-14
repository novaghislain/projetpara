<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAiChat extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'title',
        'messages',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'messages' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
