<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\InventorySession;
use App\Models\InventoryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function movements(Request $request)
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $query = StockMovement::with(['product:id,name,barcode', 'creator:id,name']);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($request->product_id) $query->where('product_id', $request->product_id);
        if ($request->type) $query->where('type', $request->type);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);

        return response()->json($query->latest()->paginate($request->per_page ?? 50));
    }

    public function stockStatus()
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $products = Product::with('category')
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->get()
            ->map(function ($p) {
                $status = 'ok';
                if ($p->stock_qty <= 0) $status = 'rupture';
                elseif ($p->stock_qty <= $p->stock_critical) $status = 'critique';
                elseif ($p->stock_qty <= $p->stock_alert) $status = 'alerte';

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'barcode' => $p->barcode,
                    'category' => $p->category->name ?? '',
                    'stock' => (float) $p->stock_qty,
                    'alert' => (float) $p->stock_alert,
                    'critical' => (float) $p->stock_critical,
                    'unit' => $p->unit,
                    'status' => $status,
                    'location' => $p->location,
                ];
            });

        return response()->json($products);
    }

    // ── Inventaire ──────────────────────────────────────────────

    public function inventoryIndex()
    {
        return view('app', ['page' => 'commerce-inventory']);
    }

    public function inventorySessions()
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $sessions = InventorySession::with(['creator:id,name', 'validator:id,name'])
            ->where('client_id', $clientId)
            ->withCount('lines')
            ->latest()
            ->paginate(20);

        return response()->json($sessions);
    }

    public function startInventory(Request $request)
    {
        $clientId = Auth::user()->client_id ?? Auth::id();

        $session = InventorySession::create([
            'client_id' => $clientId,
            'status' => 'draft',
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        // Pré-remplir avec les produits actuels
        $products = Product::where('client_id', $clientId)
            ->where('is_active', true)
            ->get(['id', 'stock_qty']);

        $lines = [];
        foreach ($products as $p) {
            $lines[] = [
                'inventory_session_id' => $session->id,
                'product_id' => $p->id,
                'theoretical_qty' => $p->stock_qty,
                'actual_qty' => $p->stock_qty,
                'difference' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($lines)) {
            InventoryLine::insert($lines);
        }

        return response()->json($session->load(['lines.product']), 201);
    }

    public function updateInventoryLine(Request $request, $sessionId, $lineId)
    {
        $line = InventoryLine::where('inventory_session_id', $sessionId)
            ->findOrFail($lineId);

        $validated = $request->validate([
            'actual_qty' => 'required|numeric|min:0',
            'motif' => 'nullable|string',
        ]);

        $line->update([
            'actual_qty' => $validated['actual_qty'],
            'difference' => $validated['actual_qty'] - $line->theoretical_qty,
            'motif' => $validated['motif'] ?? $line->motif,
        ]);

        return response()->json($line);
    }

    public function validateInventory($id)
    {
        $session = InventorySession::findOrFail($id);

        if ($session->status !== 'draft') {
            return response()->json(['message' => 'Session déjà validée ou annulée'], 422);
        }

        DB::transaction(function () use ($session) {
            foreach ($session->lines as $line) {
                if ($line->difference != 0) {
                    $product = Product::find($line->product_id);
                    if ($product) {
                        $before = $product->stock_qty;
                        $product->update(['stock_qty' => $line->actual_qty]);

                        StockMovement::create([
                            'client_id' => $session->client_id,
                            'product_id' => $product->id,
                            'type' => 'correction',
                            'quantity' => abs($line->difference),
                            'stock_before' => $before,
                            'stock_after' => $line->actual_qty,
                            'reference_type' => 'inventory',
                            'reference_id' => $session->id,
                            'motif' => $line->motif ?? 'Ajustement inventaire',
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            $session->update([
                'status' => 'validated',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Inventaire validé', 'session' => $session->fresh()]);
    }

    public function cancelInventory($id)
    {
        $session = InventorySession::findOrFail($id);
        $session->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Inventaire annulé']);
    }
}
