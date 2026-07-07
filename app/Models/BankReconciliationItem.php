<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'reconciliation_id', 'transaction_id',
        'type', 'status', 'amount', 'notes',
    ];

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'reconciliation_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class, 'transaction_id');
    }
}
