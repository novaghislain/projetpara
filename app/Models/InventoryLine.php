<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    protected $fillable = [
        'inventory_session_id',
        'product_id',
        'theoretical_qty',
        'actual_qty',
        'difference',
        'motif',
    ];

    protected function casts(): array
    {
        return [
            'theoretical_qty' => 'decimal:2',
            'actual_qty' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class, 'inventory_session_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
