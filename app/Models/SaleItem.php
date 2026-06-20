<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'variant_id',
        'product_name',
        'barcode',
        'quantity',
        'unit_price_ht',
        'unit_price_ttc',
        'discount',
        'total_ht',
        'total_ttc',
        'tva_rate',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price_ht' => 'decimal:2',
            'unit_price_ttc' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_ht' => 'decimal:2',
            'total_ttc' => 'decimal:2',
            'tva_rate' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
