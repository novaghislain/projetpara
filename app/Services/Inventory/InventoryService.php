<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Product;
use App\Models\Inventory\StockMovement;

class InventoryService
{
    /**
     * Ajoute du stock (Entrée)
     */
    public function addStock(int $productId, int $quantity, string $date, string $reference = null)
    {
        return StockMovement::create([
            'product_id' => $productId,
            'direction' => 'in',
            'quantity' => $quantity,
            'movement_date' => $date,
            'reference' => $reference
        ]);
    }

    /**
     * Retire du stock (Sortie)
     */
    public function removeStock(int $productId, int $quantity, string $date, string $reference = null)
    {
        $product = Product::findOrFail($productId);
        
        if ($product->type === 'storable' && $product->stock_quantity < $quantity) {
            throw new \Exception("Stock insuffisant pour le produit : {$product->name}. Disponible : {$product->stock_quantity}");
        }

        return StockMovement::create([
            'product_id' => $productId,
            'direction' => 'out',
            'quantity' => $quantity,
            'movement_date' => $date,
            'reference' => $reference
        ]);
    }

    /**
     * Récupère la valeur du stock (Quantité * Prix d'Achat Moyen ou Prix d'Achat)
     */
    public function getStockValue(int $productId)
    {
        $product = Product::findOrFail($productId);
        if ($product->type !== 'storable') {
            return 0;
        }

        return $product->stock_quantity * $product->purchase_price;
    }
}
