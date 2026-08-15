<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'reference',
        'type', // 'service', 'consumable', 'storable'
        'purchase_price',
        'sale_price',
        'description'
    ];

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockQuantityAttribute()
    {
        if ($this->type !== 'storable') {
            return null; // Pas de stock pour les services
        }
        
        $in = $this->movements()->where('direction', 'in')->sum('quantity');
        $out = $this->movements()->where('direction', 'out')->sum('quantity');
        
        return $in - $out;
    }
}
