<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Supplier - Fournisseur.
 *
 * Table associée : 'suppliers' (convention Laravel).
 * Fiche fournisseur avec coordonnées, délais de livraison et statut.
 * Relations :
 * - client() : appartient à un client (Client).
 * - products() : appartient à plusieurs produits (Product, pivot 'product_supplier').
 */
class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'contact_name',
        'phone',
        'email',
        'address',
        'delivery_delay',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'delivery_delay' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot('reference', 'price')
            ->withTimestamps();
    }
}
