<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'name', 'bank_name', 'account_number',
        'iban', 'swift', 'currency', 'type',
        'accounting_account_id',
        'opening_balance', 'opening_date',
        'current_balance', 'reconciled_balance',
        'last_reconciliation_date',
        'contact_phone', 'contact_email', 'notes',
        'is_active', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'last_reconciliation_date' => 'date',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function accountingAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class, 'bank_account_id');
    }
}
