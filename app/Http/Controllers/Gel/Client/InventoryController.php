<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Product;
use App\Services\Inventory\InventoryService;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function getStockStatus($productId)
    {
        $product = Product::findOrFail($productId);
        
        return response()->json([
            'status' => 'success',
            'product' => $product->name,
            'reference' => $product->reference,
            'type' => $product->type,
            'quantity_in_stock' => $product->stock_quantity,
            'total_value' => $this->inventoryService->getStockValue($productId)
        ]);
    }

    public function addMovement(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'direction' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'reference' => 'nullable|string'
        ]);

        try {
            if ($request->direction === 'in') {
                $movement = $this->inventoryService->addStock(
                    $request->product_id, 
                    $request->quantity, 
                    $request->date, 
                    $request->reference
                );
            } else {
                $movement = $this->inventoryService->removeStock(
                    $request->product_id, 
                    $request->quantity, 
                    $request->date, 
                    $request->reference
                );
            }

            $product = Product::find($request->product_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Mouvement de stock enregistré.',
                'new_quantity' => $product->stock_quantity,
                'movement' => $movement
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
