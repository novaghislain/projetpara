<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CatalogueService;

class CartController extends Controller
{
    /**
     * Contrôleur de gestion du panier d'achat en session.
     * Permet d'ajouter, supprimer, consulter et vider le panier
     * avant la soumission d'une commande.
     */

    /**
     * Affiche la page du panier.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function view(Request $request)
    {
        return view('app', [
            'page' => 'public-cart',
            'props' => []
        ]);
    }

    /**
     * Récupère le contenu actuel du panier en session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $cart = session()->get('cart', []);
        return response()->json($this->formatCartResponse($cart));
    }

    /**
     * Ajoute un service au panier. Si le service existe déjà,
     * la quantité est incrémentée.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * Retire un service du panier.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Identifiant du service à retirer
     * @return \Illuminate\Http\JsonResponse
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
     * Vide complètement le panier.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        session()->forget('cart');
        return response()->json($this->formatCartResponse([]));
    }

    /**
     * Formate le panier en une structure JSON pour le frontend.
     * Calcule le total FCFA des articles à tarif fixe.
     *
     * @param array $cart Données brutes du panier en session
     * @return array
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
