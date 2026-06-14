<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    protected $fillable = [
        'client_id', 'code', 'name', 'type', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function journalLines()
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
