<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingJournalLine extends Model
{
    protected $fillable = [
        'journal_id', 'account_id', 'label', 'debit', 'credit',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    // Relations
    public function journal()
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }
}
