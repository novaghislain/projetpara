<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle ProductCategory - Catégorie de produits.
 *
 * Table associée : 'product_categories' (convention Laravel).
 * Permet de hiérarchiser les produits par catégorie, avec support
 * des catégories parent/enfant (arborescence).
 * Relations :
 * - client() : appartient à un client (Client).
 * - parent() : appartient à la catégorie parente (auto-référence).
 * - children() : a plusieurs catégories enfants (auto-référence).
 * - products() : a plusieurs produits (Product).
 */
class ProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'parent_id',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
