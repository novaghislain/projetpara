<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    protected $fillable = [
        'client_id',
        'business_user_id',
        'opened_at',
        'closed_at',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'difference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_amount' => 'decimal:2',
            'closing_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function businessUser(): BelongsTo
    {
        return $this->belongsTo(BusinessUser::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
