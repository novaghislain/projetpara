<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountConversionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'role',
        'message',
        'status',
        'processed_by',
        'processed_at'
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
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

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
