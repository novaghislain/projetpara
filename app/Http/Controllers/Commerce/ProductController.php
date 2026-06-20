<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'commerce-products']);
    }

    public function listAll(Request $request)
    {
        $user = Auth::user();
        $query = Product::with(['category', 'primaryImage']);

        if ($user->client_id) {
            $query->where('client_id', $user->client_id);
        }

        // Filtres
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%");
            });
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->stock === 'alert') {
            $query->whereColumn('stock_qty', '<=', 'stock_alert');
        }
        if ($request->stock === 'out') {
            $query->where('stock_qty', '<=', 0);
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 50));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'sku' => 'nullable|string|max:100',
            'price_ht' => 'required|numeric|min:0',
            'price_ttc' => 'required|numeric|min:0',
            'price_purchase' => 'nullable|numeric|min:0',
            'tva_rate' => 'nullable|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:20',
            'stock_qty' => 'nullable|numeric|min:0',
            'stock_alert' => 'nullable|numeric|min:0',
            'stock_critical' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_bundle' => 'boolean',
            'suppliers' => 'nullable|array',
            'suppliers.*.id' => 'exists:suppliers,id',
            'suppliers.*.reference' => 'nullable|string',
            'suppliers.*.price' => 'nullable|numeric',
        ]);

        $validated['client_id'] = Auth::user()->client_id ?? Auth::id();
        $validated['price_purchase'] = $validated['price_purchase'] ?? 0;
        $validated['tva_rate'] = $validated['tva_rate'] ?? 0;

        $product = DB::transaction(function () use ($validated) {
            $product = Product::create($validated);

            // Associer les fournisseurs
            if (!empty($validated['suppliers'])) {
                $pivotData = [];
                foreach ($validated['suppliers'] as $s) {
                    $pivotData[$s['id']] = [
                        'reference' => $s['reference'] ?? null,
                        'price' => $s['price'] ?? null,
                    ];
                }
                $product->suppliers()->attach($pivotData);
            }

            // Mouvement de stock initial
            if ($validated['stock_qty'] > 0) {
                StockMovement::create([
                    'client_id' => $product->client_id,
                    'product_id' => $product->id,
                    'type' => 'entry',
                    'quantity' => $validated['stock_qty'],
                    'stock_before' => 0,
                    'stock_after' => $validated['stock_qty'],
                    'reference_type' => 'initial',
                    'motif' => 'Stock initial',
                    'created_by' => Auth::id(),
                ]);
            }

            return $product;
        });

        return response()->json($product->load(['category', 'suppliers']), 201);
    }

    public function show($id)
    {
        $product = Product::with([
            'category', 'images', 'primaryImage', 'variants',
            'suppliers', 'stockMovements' => fn($q) => $q->latest()->limit(50),
        ])->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $id,
            'sku' => 'nullable|string|max:100',
            'price_ht' => 'sometimes|numeric|min:0',
            'price_ttc' => 'sometimes|numeric|min:0',
            'price_purchase' => 'nullable|numeric|min:0',
            'tva_rate' => 'nullable|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:20',
            'stock_qty' => 'nullable|numeric|min:0',
            'stock_alert' => 'nullable|numeric|min:0',
            'stock_critical' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_bundle' => 'boolean',
        ]);

        $product->update($validated);
        return response()->json($product->load(['category', 'suppliers']));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Produit supprimé']);
    }

    public function adjustStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|numeric',
            'type' => 'required|in:entry,exit,correction',
            'motif' => 'required|string|max:255',
        ]);

        $before = $product->stock_qty;
        $after = $validated['type'] === 'exit'
            ? max(0, $before - $validated['quantity'])
            : $before + $validated['quantity'];

        DB::transaction(function () use ($product, $validated, $before, $after) {
            $product->update(['stock_qty' => $after]);

            StockMovement::create([
                'client_id' => $product->client_id,
                'product_id' => $product->id,
                'type' => $validated['type'],
                'quantity' => abs($validated['quantity']),
                'stock_before' => $before,
                'stock_after' => $after,
                'motif' => $validated['motif'],
                'created_by' => Auth::id(),
            ]);
        });

        return response()->json([
            'message' => 'Stock mis à jour',
            'product' => $product->fresh(),
        ]);
    }

    public function importTemplate()
    {
        // Retourne un template CSV exemple
        $headers = ['name', 'barcode', 'category', 'price_ht', 'price_ttc', 'price_purchase', 'tva_rate', 'stock_qty', 'stock_alert', 'unit'];
        $csv = implode(',', $headers) . "\n";
        $csv .= "Produit exemple,1234567890123,Catégorie,1000,1180,800,18,50,10,pièce\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_produits.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $user = Auth::user();
        $clientId = $user->client_id ?? $user->id;
        $imported = 0;
        $errors = [];

        // Traitement simplifié du CSV
        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            return response()->json(['message' => 'Fichier vide ou invalide'], 422);
        }

        $lineNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $data = array_combine($headers, $row);

            try {
                // Trouver ou créer la catégorie
                $categoryId = null;
                if (!empty($data['category'])) {
                    $category = ProductCategory::firstOrCreate(
                        ['client_id' => $clientId, 'name' => $data['category']],
                        ['client_id' => $clientId, 'name' => $data['category']]
                    );
                    $categoryId = $category->id;
                }

                $barcode = $data['barcode'] ?? null;
                if ($barcode && Product::where('barcode', $barcode)->exists()) {
                    $errors[] = "Ligne {$lineNumber}: Code-barres '{$barcode}' déjà existant";
                    continue;
                }

                Product::create([
                    'client_id' => $clientId,
                    'category_id' => $categoryId,
                    'name' => $data['name'] ?? 'Produit sans nom',
                    'barcode' => $barcode,
                    'price_ht' => floatval($data['price_ht'] ?? 0),
                    'price_ttc' => floatval($data['price_ttc'] ?? 0),
                    'price_purchase' => floatval($data['price_purchase'] ?? 0),
                    'tva_rate' => floatval($data['tva_rate'] ?? 0),
                    'stock_qty' => floatval($data['stock_qty'] ?? 0),
                    'stock_alert' => floatval($data['stock_alert'] ?? 10),
                    'unit' => $data['unit'] ?? 'piece',
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Ligne {$lineNumber}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return response()->json([
            'message' => "{$imported} produit(s) importé(s)",
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    public function export()
    {
        $user = Auth::user();
        $query = Product::with('category')->where('client_id', $user->client_id ?? $user->id);

        $headers = ['name', 'barcode', 'category', 'price_ht', 'price_ttc', 'price_purchase', 'tva_rate', 'stock_qty', 'stock_alert', 'unit'];
        $csv = implode(',', $headers) . "\n";

        foreach ($query->cursor() as $product) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $product->name) . '"',
                $product->barcode ?? '',
                '"' . ($product->category->name ?? '') . '"',
                $product->price_ht,
                $product->price_ttc,
                $product->price_purchase,
                $product->tva_rate,
                $product->stock_qty,
                $product->stock_alert,
                $product->unit,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export_produits.csv"',
        ]);
    }
}
