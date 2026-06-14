<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingJournal extends Model
{
    protected $fillable = [
        'client_id', 'journal_type', 'entry_date', 'reference',
        'description', 'status', 'created_by',
        'fiscal_year_id', 'numero_piece', 'validated_by', 'validated_at',
        'is_reversal', 'reversed_journal_id', 'source_module',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'validated_at' => 'datetime',
            'is_reversal' => 'boolean',
        ];
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingJournalLine::class, 'journal_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function reversedJournal(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_journal_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversed_journal_id');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('journal_type', $type);
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    // Accessors
    public function getDebitTotalAttribute()
    {
        return $this->lines()->sum('debit');
    }

    public function getCreditTotalAttribute()
    {
        return $this->lines()->sum('credit');
    }

    public function getIsBalancedAttribute()
    {
        return abs($this->debit_total - $this->credit_total) < 0.01;
    }
}
