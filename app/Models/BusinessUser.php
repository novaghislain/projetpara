<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessUser extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'role_id',
        'business_role',
        'is_active',
        'permissions',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(BusinessRole::class, 'role_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function posSessions(): HasMany
    {
        return $this->hasMany(PosSession::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
