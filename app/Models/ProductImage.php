<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ProductImage - Image d'un produit.
 *
 * Table associée : 'product_images' (convention Laravel).
 * Stocke les chemins d'accès aux images d'un produit, avec la possibilité
 * de définir une image principale et un ordre d'affichage.
 * Relations :
 * - product() : appartient à un produit (Product).
 */
class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
