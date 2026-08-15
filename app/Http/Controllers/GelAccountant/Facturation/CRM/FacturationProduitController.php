<?php

namespace App\Http\Controllers\GelAccountant\Facturation\CRM;

use App\Http\Controllers\Controller;
use App\Models\Gel\GelProduit;
use App\Models\Gel\CompteComptable;
use Illuminate\Http\Request;

class FacturationProduitController extends Controller
{
    public function index()
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $produits = GelProduit::where('client_id', $clientId)
            ->with('compteComptable')
            ->orderBy('nom')
            ->paginate(20);

        return view('gel-accountant.facturation.produits.index', compact('produits'));
    }

    public function create()
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $comptes = CompteComptable::where('client_id', $clientId)
            ->where('numero', 'like', '7%') // Ventes/Produits (Classe 7)
            ->orderBy('numero')
            ->get();

        return view('gel-accountant.facturation.produits.create', compact('comptes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_unitaire' => 'required|numeric|min:0',
            'est_service' => 'boolean',
            'compte_comptable_id' => 'required|exists:gel_comptes_comptables,id',
        ]);

        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $validated['client_id'] = $clientId;

        GelProduit::create($validated);

        return redirect()->route('gel-accountant.facturation.produits.index')
            ->with('success', 'Produit / Service ajouté avec succès.');
    }

    public function edit($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $produit = GelProduit::where('client_id', $clientId)->findOrFail($id);
        
        $comptes = CompteComptable::where('client_id', $clientId)
            ->where('numero', 'like', '7%')
            ->orderBy('numero')
            ->get();

        return view('gel-accountant.facturation.produits.edit', compact('produit', 'comptes'));
    }

    public function update(Request $request, $id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $produit = GelProduit::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_unitaire' => 'required|numeric|min:0',
            'est_service' => 'boolean',
            'compte_comptable_id' => 'required|exists:gel_comptes_comptables,id',
        ]);

        $produit->update($validated);

        return redirect()->route('gel-accountant.facturation.produits.index')
            ->with('success', 'Produit / Service mis à jour.');
    }

    public function destroy($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $produit = GelProduit::where('client_id', $clientId)->findOrFail($id);
        $produit->delete();

        return redirect()->route('gel-accountant.facturation.produits.index')
            ->with('success', 'Produit / Service supprimé.');
    }
}
