<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartAccount extends Model
{
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'class',
        'is_active',
        'is_syscohada',
        'parent_code',
        'tva_rate',
        'has_tva',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_syscohada' => 'boolean',
            'has_tva' => 'boolean',
            'tva_rate' => 'decimal:2',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByClass($query, string $class)
    {
        return $query->where('class', $class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
