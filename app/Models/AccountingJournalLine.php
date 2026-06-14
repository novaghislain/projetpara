<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingJournalLine extends Model
{
    protected $fillable = [
        'journal_id', 'account_id', 'label', 'debit', 'credit',
        'tva_code', 'tva_rate', 'tva_amount', 'tva_type',
        'aib_rate', 'aib_amount',
        'due_date', 'lettering_code', 'cost_center_id',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'tva_rate' => 'decimal:2',
            'tva_amount' => 'decimal:2',
            'aib_rate' => 'decimal:2',
            'aib_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    // Relations
    public function journal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
}
