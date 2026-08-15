<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'direction', // 'in' or 'out'
        'quantity',
        'movement_date',
        'reference' // ex: num facture ou bon de livraison
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
