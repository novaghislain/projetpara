<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'code',
        'type',
        'is_active',
        'is_open',
        'balance',
        'last_opened_at',
        'last_closed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_open' => 'boolean',
        'balance' => 'decimal:2',
        'last_opened_at' => 'datetime',
        'last_closed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CashRegisterLog::class);
    }

    /**
     * Calcule le solde à partir des transactions.
     */
    public function calculateBalance(): void
    {
        $in = $this->transactions()
            ->where('type', 'encaissement')
            ->sum('amount');

        $out = $this->transactions()
            ->where('type', 'decaissement')
            ->sum('amount');

        $this->balance = $in - $out;
        $this->saveQuietly();
    }
}
