<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliation extends Model
{
    protected $fillable = [
        'client_id', 'fiscal_year_id', 'bank_account', 'bank_name',
        'period', 'statement_date', 'balance_per_statement',
        'balance_per_books', 'difference', 'outstanding_deposits',
        'outstanding_checks', 'bank_charges', 'interest_income',
        'unmatched_items', 'status',
    ];

    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'balance_per_statement' => 'decimal:2',
            'balance_per_books' => 'decimal:2',
            'difference' => 'decimal:2',
            'outstanding_deposits' => 'decimal:2',
            'outstanding_checks' => 'decimal:2',
            'bank_charges' => 'decimal:2',
            'interest_income' => 'decimal:2',
            'unmatched_items' => 'json',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }
}
