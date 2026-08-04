<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle InventoryLine (Ligne d'inventaire).
 *
 * Représente une ligne de comptage physique d'inventaire pour un produit.
 * Compare la quantité théorique (en stock) avec la quantité réelle (comptée)
 * et calcule la différence (écart d'inventaire).
 *
 * @property int $id
 * @property int $inventory_session_id ID de la session d'inventaire
 * @property int $product_id ID du produit
 * @property float $theoretical_qty Quantité théorique (en stock)
 * @property float $actual_qty Quantité réelle (comptée)
 * @property float $difference Écart (réel - théorique)
 * @property string|null $motif Motif de l'écart
 *
 * @property-read \App\Models\InventorySession $session Session d'inventaire parente
 * @property-read \App\Models\Product $product Produit inventorié
 */
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
