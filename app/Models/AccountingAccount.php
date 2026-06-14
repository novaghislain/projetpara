<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingAccount extends Model
{
    protected $fillable = [
        'client_id', 'code', 'name', 'type', 'is_active',
        'syscohada_class', 'parent_id', 'is_syscohada',
        'tva_rate', 'has_tva',
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

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(AccountingJournalLine::class, 'account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function getDebitTotalAttribute()
    {
        return $this->journalLines()->sum('debit');
    }

    public function getCreditTotalAttribute()
    {
        return $this->journalLines()->sum('credit');
    }

    public function getBalanceAttribute()
    {
        return $this->debit_total - $this->credit_total;
    }
}
