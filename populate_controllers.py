import os

controllers_dir = 'app/Http/Controllers/Eden/Erp'
os.makedirs(controllers_dir, exist_ok=True)

controllers = {
    'CatalogueController.php': """<?php
namespace App\\Http\\Controllers\\Eden\\Erp;

use App\\Http\\Controllers\\Controller;
use App\\Models\\ErpCategory;
use App\\Models\\ErpItem;
use Illuminate\\Http\\Request;

class CatalogueController extends Controller
{
    public function index()
    {
        $categories = ErpCategory::all();
        $items = ErpItem::with('category')->get();
        return inertia('Eden/Erp/Catalogue', [
            'categories' => $categories,
            'items' => $items
        ]);
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'erp_category_id' => 'nullable|exists:erp_categories,id',
            'reference' => 'required|string|unique:erp_items',
            'designation' => 'required|string',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_alert' => 'required|integer',
            'unit' => 'required|string',
        ]);
        ErpItem::create($validated);
        return redirect()->back()->with('success', 'Article créé avec succès');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'description' => 'nullable|string',
        ]);
        ErpCategory::create($validated);
        return redirect()->back()->with('success', 'Catégorie créée avec succès');
    }
}
""",
    'StockController.php': """<?php
namespace App\\Http\\Controllers\\Eden\\Erp;

use App\\Http\\Controllers\\Controller;
use App\\Models\\ErpWarehouse;
use App\\Models\\ErpStockMovement;
use App\\Models\\ErpItem;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;

class StockController extends Controller
{
    public function index()
    {
        $warehouses = ErpWarehouse::all();
        $movements = ErpStockMovement::with(['item', 'warehouse', 'creator'])->latest()->take(100)->get();
        $items = ErpItem::all();
        
        // Calculate current stock per item
        $stocks = ErpItem::with(['stockMovements'])->get()->map(function($item) {
            $in = $item->stockMovements->where('type', 'entry')->sum('quantity');
            $out = $item->stockMovements->where('type', 'exit')->sum('quantity');
            return [
                'id' => $item->id,
                'designation' => $item->designation,
                'reference' => $item->reference,
                'stock' => $in - $out,
                'alert' => $item->stock_alert
            ];
        });

        return inertia('Eden/Erp/Stock', [
            'warehouses' => $warehouses,
            'movements' => $movements,
            'items' => $items,
            'current_stocks' => $stocks
        ]);
    }

    public function storeMovement(Request $request)
    {
        $validated = $request->validate([
            'erp_item_id' => 'required|exists:erp_items,id',
            'erp_warehouse_id' => 'required|exists:erp_warehouses,id',
            'type' => 'required|string|in:entry,exit,transfer',
            'quantity' => 'required|integer|min:1',
            'reference_doc' => 'nullable|string',
            'movement_date' => 'required|date',
            'motif' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        ErpStockMovement::create($validated);

        return redirect()->back()->with('success', 'Mouvement de stock enregistré');
    }

    public function storeWarehouse(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'nullable|string',
        ]);
        ErpWarehouse::create($validated);
        return redirect()->back()->with('success', 'Magasin créé avec succès');
    }
}
"""
}

for filename, content in controllers.items():
    filepath = os.path.join(controllers_dir, filename)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Created {filename}")
