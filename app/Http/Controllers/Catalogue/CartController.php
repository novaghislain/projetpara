<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CatalogueService;

class CartController extends Controller
{
    /**
     * View the Cart Page
     */
    public function view(Request $request)
    {
        return view('app', [
            'page' => 'public-cart',
            'props' => []
        ]);
    }

    /**
     * Get the current cart contents
     */
    public function index(Request $request)
    {
        $cart = session()->get('cart', []);
        return response()->json($this->formatCartResponse($cart));
    }

    /**
     * Add a service to the cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:catalogue_services,id',
            'quantity' => 'integer|min:1'
        ]);

        $serviceId = $request->id;
        $quantity = $request->quantity ?? 1;

        $cart = session()->get('cart', []);

        if (isset($cart[$serviceId])) {
            $cart[$serviceId]['quantity'] += $quantity;
        } else {
            $service = CatalogueService::findOrFail($serviceId);
            $cart[$serviceId] = [
                'id' => $service->id,
                'nom' => $service->nom,
                'tarif_type' => $service->tarif_type,
                'tarif_fcfa' => $service->tarif_fcfa,
                'icone' => $service->category->icone ?? 'bi-box',
                'category_nom' => $service->category->nom ?? '',
                'quantity' => $quantity
            ];
        }

        session()->put('cart', $cart);

        return response()->json($this->formatCartResponse($cart));
    }

    /**
     * Remove a service from the cart
     */
    public function remove(Request $request, $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return response()->json($this->formatCartResponse($cart));
    }

    /**
     * Clear the entire cart
     */
    public function clear(Request $request)
    {
        session()->forget('cart');
        return response()->json($this->formatCartResponse([]));
    }

    /**
     * Format cart array into JSON structure for frontend
     */
    private function formatCartResponse($cart)
    {
        $items = array_values($cart);
        
        $totalFcfa = 0;
        foreach ($items as $item) {
            if ($item['tarif_type'] === 'fixe' && $item['tarif_fcfa']) {
                $totalFcfa += ($item['tarif_fcfa'] * $item['quantity']);
            }
        }

        return [
            'items' => $items,
            'count' => count($items),
            'total' => $totalFcfa
        ];
    }
}
