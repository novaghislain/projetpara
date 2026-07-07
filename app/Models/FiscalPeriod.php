<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class FiscalPeriod extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'code',
        'label',
        'start_date',
        'end_date',
        'status',
        'is_current',
        'closed_at',
        'closed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function close(): void
    {
        $this->status = 'closed';
        $this->closed_at = now();
        $this->closed_by = Auth::id();
        $this->save();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByFiscalYear($query, int $fiscalYearId)
    {
        return $query->where('fiscal_year_id', $fiscalYearId);
    }
}
