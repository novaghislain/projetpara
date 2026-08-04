<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle StockMovement - Mouvement de stock (entrée/sortie).
 *
 * Table associée : 'stock_movements' (convention Laravel).
 * Enregistre chaque mouvement de stock avec les quantités avant/après,
 * le type (entrée/sortie/ajustement), la référence, et le motif.
 * Relations :
 * - client() : appartient à un client (Client).
 * - product() : appartient à un produit (Product).
 * - variant() : appartient à une variante de produit (ProductVariant).
 * - creator() : appartient à l'utilisateur (User) qui a effectué le mouvement.
 */
class StockMovement extends Model
{
    protected $fillable = [
        'client_id',
        'product_id',
        'variant_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'motif',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'stock_before' => 'decimal:2',
            'stock_after' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
