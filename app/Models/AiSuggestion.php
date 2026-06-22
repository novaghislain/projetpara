<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSuggestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'user_id',
        'agent',
        'type',
        'title',
        'description',
        'data',
        'metadata',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'metadata' => 'array',
            'approved_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    // ── Scopes ──

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByAgent($query, string $agent)
    {
        return $query->where('agent', $agent);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // ── Relations ──

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
