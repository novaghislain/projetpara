<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'barcode',
        'price_ht',
        'price_ttc',
        'stock_qty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_ht' => 'decimal:2',
            'price_ttc' => 'decimal:2',
            'stock_qty' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
