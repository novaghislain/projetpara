<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{
    protected $fillable = [
        'client_id', 'journal_type', 'entry_date', 'reference',
        'description', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lines()
    {
        return $this->hasMany(AccountingJournalLine::class, 'journal_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
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
