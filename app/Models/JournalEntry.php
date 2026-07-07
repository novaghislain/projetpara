<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'journal_id',
        'fiscal_period_id',
        'entry_number',
        'entry_date',
        'value_date',
        'reference',
        'description',
        'total_debit',
        'total_credit',
        'is_balanced',
        'status',
        'classified_by_ai',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'value_date' => 'date',
            'validated_at' => 'datetime',
            'is_balanced' => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function fiscalPeriod(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EntryLine::class, 'entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ─── Méthodes ─────────────────────────────────────────

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    // ─── Status ───────────────────────────────────────────

    const STATUS_DRAFT     = 'draft';
    const STATUS_POSTED    = 'posted';
    const STATUS_LOCKED    = 'locked';
    const STATUS_CANCELLED = 'cancelled';
}
