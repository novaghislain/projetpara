<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * Contrôleur pour la gestion des fournisseurs.
     * Permet de créer, modifier, consulter et supprimer des fournisseurs
     * associés aux produits de l'entreprise.
     */

    /**
     * Affiche la page de gestion des fournisseurs.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'commerce-suppliers']);
    }

    /**
     * Retourne la liste de tous les fournisseurs avec le nombre de produits associés.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAll()
    {
        $user = Auth::user();
        $query = Supplier::withCount('products');

        if ($user->client_id) {
            $query->where('client_id', $user->client_id);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Crée un nouveau fournisseur.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'delivery_delay' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['client_id'] = Auth::user()->client_id ?? Auth::id();
        $supplier = Supplier::create($validated);

        return response()->json($supplier, 201);
    }

    /**
     * Affiche les détails d'un fournisseur avec ses produits associés.
     *
     * @param int $id Identifiant du fournisseur
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $supplier = Supplier::with('products')->findOrFail($id);
        return response()->json($supplier);
    }

    /**
     * Met à jour un fournisseur existant.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Identifiant du fournisseur
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'delivery_delay' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);
        return response()->json($supplier);
    }

    /**
     * Supprime un fournisseur.
     *
     * @param int $id Identifiant du fournisseur
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json(['message' => 'Fournisseur supprimé']);
    }
}
