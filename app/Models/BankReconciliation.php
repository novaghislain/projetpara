<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'bank_account_id',
        'reference', 'start_date', 'end_date',
        'opening_balance', 'closing_balance',
        'statement_balance', 'difference',
        'total_debit', 'total_credit',
        'adjusted_balance',
        'status', 'notes',
        'created_by', 'validated_by', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'validated_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class, 'reconciliation_id');
    }

    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
}
