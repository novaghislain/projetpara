<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Product - Produit ou service vendu par une entreprise.
 *
 * Table associée : 'products' (convention Laravel).
 * Gère le catalogue de produits avec prix HT/TTC, stock, TVA, codes-barres, etc.
 * Supporte la notion de lot (bundle) et le suivi de stock avec seuils d'alerte.
 * Relations :
 * - client() : appartient à un client (Client).
 * - category() : appartient à une catégorie (ProductCategory).
 * - images() : a plusieurs images (ProductImage).
 * - primaryImage() : a une image principale (HasOne, filtrée).
 * - variants() : a plusieurs variantes (ProductVariant).
 * - suppliers() : appartient à plusieurs fournisseurs (Supplier, pivot 'product_supplier').
 * - stockMovements() : a plusieurs mouvements de stock (StockMovement).
 */
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'category_id',
        'name',
        'description',
        'brand',
        'barcode',
        'sku',
        'price_ht',
        'price_ttc',
        'price_purchase',
        'tva_rate',
        'unit',
        'stock_qty',
        'stock_alert',
        'stock_critical',
        'location',
        'is_active',
        'is_bundle',
    ];

    protected function casts(): array
    {
        return [
            'price_ht' => 'decimal:2',
            'price_ttc' => 'decimal:2',
            'price_purchase' => 'decimal:2',
            'tva_rate' => 'decimal:2',
            'stock_qty' => 'decimal:2',
            'stock_alert' => 'decimal:2',
            'stock_critical' => 'decimal:2',
            'is_active' => 'boolean',
            'is_bundle' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->withPivot('reference', 'price')
            ->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
