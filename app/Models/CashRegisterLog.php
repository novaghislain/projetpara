<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegisterLog extends Model
{
    protected $fillable = [
        'cash_register_id',
        'user_id',
        'action',
        'opened_balance',
        'closed_balance',
        'difference',
        'notes',
        'closed_at',
    ];

    protected $casts = [
        'opened_balance' => 'decimal:2',
        'closed_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
