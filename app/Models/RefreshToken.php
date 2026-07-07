<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'revoked_at',
        'device_info',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return !$this->revoked_at && $this->expires_at->isFuture();
    }

    public function revoke(): void
    {
        $this->revoked_at = now();
        $this->save();
    }

    public static function generateUniqueToken(): string
    {
        return Str::random(80) . '.' . Str::random(40);
    }

    public static function createForUser(User $user, array $extra = []): self
    {
        $rawToken = self::generateUniqueToken();

        $model = self::create(array_merge([
            'user_id' => $user->id,
            'token' => hash('sha256', $rawToken),
            'expires_at' => now()->addDays(15),
        ], $extra));

        $model->raw_token = $rawToken;

        return $model;
    }

    public function scopeValid($query)
    {
        return $query->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
