<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLearningLog extends Model
{
    protected $table = 'ai_learning_log';

    protected $fillable = [
        'client_id',
        'agent',
        'type',
        'input_data',
        'output_data',
        'correction',
        'metadata',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    // ── Scopes ──

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByAgent($query, string $agent)
    {
        return $query->where('agent', $agent);
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
}
