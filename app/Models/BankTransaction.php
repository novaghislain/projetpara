<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'bank_account_id',
        'transaction_date', 'value_date',
        'description',
        'debit', 'credit', 'balance',
        'reference', 'cheque_number', 'category', 'status',
        'is_reconciled', 'is_imported',
        'journal_entry_id', 'invoice_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'value_date' => 'date',
            'is_reconciled' => 'boolean',
            'is_imported' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
