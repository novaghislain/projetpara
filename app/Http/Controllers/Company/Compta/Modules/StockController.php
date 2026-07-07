<?php

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\ErpItem;
use App\Models\ErpStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function index()
    {
        $clientId = $this->getClientId();
        $items = ErpItem::where('client_id', $clientId)
            ->with('stockMovements')
            ->orderBy('designation')
            ->get()
            ->map(function ($item) {
                $in = $item->stockMovements->where('type', 'entry')->sum('quantity');
                $out = $item->stockMovements->where('type', 'exit')->sum('quantity');
                return [
                    'id' => $item->id,
                    'reference' => $item->reference,
                    'designation' => $item->designation,
                    'stock' => $in - $out,
                    'stock_alert' => $item->stock_alert,
                    'purchase_price' => (float) $item->purchase_price,
                    'selling_price' => (float) $item->selling_price,
                    'unit' => $item->unit,
                ];
            });

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'reference' => 'required|string|max:50',
            'designation' => 'required|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'stock_alert' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
        ]);

        $validated['client_id'] = $clientId;
        $item = ErpItem::create($validated);

        return response()->json(['message' => 'Article créé.', 'item' => $item], 201);
    }

    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $item = ErpItem::where('client_id', $clientId)->findOrFail($id);
        $item->update($request->all());
        return response()->json(['message' => 'Article mis à jour.', 'item' => $item]);
    }

    public function destroy($id)
    {
        $clientId = $this->getClientId();
        $item = ErpItem::where('client_id', $clientId)->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Article supprimé.']);
    }

    public function movement(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'type' => 'required|in:entry,exit',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        $item = ErpItem::where('client_id', $clientId)->findOrFail($id);
        ErpStockMovement::create([
            'item_id' => $item->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'] ?? null,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Mouvement enregistré.']);
    }

    public function alerts()
    {
        $clientId = $this->getClientId();
        $items = ErpItem::where('client_id', $clientId)->get()->filter(function ($item) {
            $in = $item->stockMovements->where('type', 'entry')->sum('quantity');
            $out = $item->stockMovements->where('type', 'exit')->sum('quantity');
            return ($in - $out) <= ($item->stock_alert ?? 0);
        })->values();

        return response()->json($items);
    }
}
