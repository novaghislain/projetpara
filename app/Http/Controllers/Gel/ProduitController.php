<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Gel\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    protected function getClientId(Request $request)
    {
        return $request->query('client_id');
    }

    public function index(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return response()->json(['error' => 'client_id missing'], 400);
        }

        $produits = Produit::where('client_id', $clientId)->get();
        return response()->json($produits);
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return response()->json(['error' => 'client_id missing'], 400);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_unitaire' => 'required|numeric|min:0',
            'est_service' => 'boolean',
            'compte_comptable_id' => 'nullable|integer',
        ]);

        $produit = Produit::create(array_merge($validated, ['client_id' => $clientId]));
        return response()->json($produit, 201);
    }

    public function show(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $produit = Produit::where('client_id', $clientId)->findOrFail($id);
        return response()->json($produit);
    }

    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $produit = Produit::where('client_id', $clientId)->findOrFail($id);
        
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_unitaire' => 'required|numeric|min:0',
            'est_service' => 'boolean',
            'compte_comptable_id' => 'nullable|integer',
        ]);

        $produit->update($validated);
        return response()->json($produit);
    }

    public function destroy(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $produit = Produit::where('client_id', $clientId)->findOrFail($id);
        $produit->delete();
        return response()->json(null, 204);
    }
}
