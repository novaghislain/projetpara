<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeOfficeSupply;
use App\Models\Dae\DaeOfficeSupplyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaeOfficeSuppliesController extends Controller
{
    // ─── Fournitures (stock) ──────────────────────────────

    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'dae-fournitures']);
        }

        $user = Auth::user();
        $query = DaeOfficeSupply::with('createdBy');

        if (!$user->isSuperAdmin()) {
            $clientIds = $user->clients_assignes ?? [];
            $query->whereIn('client_id', $clientIds);
        }

        if ($request->filled('categorie')) {
            $query->parCategorie($request->categorie);
        }
        if ($request->filled('alerte')) {
            $query->enAlerte();
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return response()->json(
            $query->orderBy('nom')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'categorie' => 'nullable|string|max:100',
            'quantite_stock' => 'nullable|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
            'unite' => 'nullable|string|max:50',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'fournisseur' => 'nullable|string|max:255',
            'emplacement' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['quantite_stock'] ??= 0;
        $validated['seuil_alerte'] ??= 5;
        $validated['unite'] ??= 'piece';

        $supply = DaeOfficeSupply::create($validated);

        return response()->json($supply->load('createdBy'), 201);
    }

    public function show($id)
    {
        $supply = DaeOfficeSupply::with('createdBy', 'requests.demandeur')->findOrFail($id);
        return response()->json($supply);
    }

    public function update(Request $request, $id)
    {
        $supply = DaeOfficeSupply::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
            'categorie' => 'nullable|string|max:100',
            'quantite_stock' => 'nullable|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
            'unite' => 'nullable|string|max:50',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'fournisseur' => 'nullable|string|max:255',
            'emplacement' => 'nullable|string|max:255',
        ]);

        $validated['updated_by'] = Auth::id();
        $supply->update($validated);

        return response()->json($supply->load('createdBy'));
    }

    public function destroy($id)
    {
        $supply = DaeOfficeSupply::findOrFail($id);
        $supply->delete();

        return response()->json(['message' => 'Fourniture supprimée.']);
    }

    public function ajusterStock(Request $request, $id)
    {
        $supply = DaeOfficeSupply::findOrFail($id);

        $validated = $request->validate([
            'quantite_stock' => 'required|integer|min:0',
            'motif' => 'nullable|string',
        ]);

        $supply->update([
            'quantite_stock' => $validated['quantite_stock'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json($supply);
    }

    // ─── Demandes de fournitures ──────────────────────────

    public function requestsIndex(Request $request)
    {
        $user = Auth::user();
        $query = DaeOfficeSupplyRequest::with('supply', 'demandeur', 'approuveur');

        if (!$user->isSuperAdmin()) {
            $clientIds = $user->clients_assignes ?? [];
            $query->whereIn('client_id', $clientIds);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('supply_id')) {
            $query->where('supply_id', $request->supply_id);
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate(20)
        );
    }

    public function requestsStore(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'supply_id' => 'required|exists:dae_office_supplies,id',
            'quantite_demandee' => 'required|integer|min:1',
            'motif' => 'nullable|string',
        ]);

        $validated['statut'] = 'en_attente';
        $validated['demande_par'] = Auth::id();
        $validated['created_by'] = Auth::id();

        $requestModel = DaeOfficeSupplyRequest::create($validated);

        return response()->json($requestModel->load('supply', 'demandeur'), 201);
    }

    public function requestsApprouver(Request $request, $id)
    {
        $requestModel = DaeOfficeSupplyRequest::findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|in:approuvee,refusee',
            'quantite_approuvee' => 'nullable|integer|min:1',
        ]);

        $requestModel->update([
            'statut' => $validated['statut'],
            'quantite_approuvee' => $validated['quantite_approuvee'] ?? $requestModel->quantite_demandee,
            'approuve_par' => Auth::id(),
            'approuve_at' => now(),
        ]);

        // Si approuvée, ajuster le stock
        if ($validated['statut'] === 'approuvee') {
            $supply = $requestModel->supply;
            $qte = $validated['quantite_approuvee'] ?? $requestModel->quantite_demandee;
            $supply->decrement('quantite_stock', $qte);
        }

        return response()->json($requestModel->load('supply', 'demandeur', 'approuveur'));
    }

    public function requestsLivrer($id)
    {
        $requestModel = DaeOfficeSupplyRequest::findOrFail($id);
        $requestModel->update(['statut' => 'livree']);

        return response()->json($requestModel->load('supply', 'demandeur', 'approuveur'));
    }

    public function categories()
    {
        $user = Auth::user();
        $clientIds = $user->isSuperAdmin() ? null : ($user->clients_assignes ?? []);

        $query = DaeOfficeSupply::select('categorie')
            ->whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie');

        if ($clientIds) {
            $query->whereIn('client_id', $clientIds);
        }

        return response()->json($query->pluck('categorie'));
    }

    public function stats()
    {
        $user = Auth::user();
        $clientIds = $user->isSuperAdmin() ? null : ($user->clients_assignes ?? []);

        $query = fn($model) => $clientIds
            ? $model->whereIn('client_id', $clientIds)
            : $model;

        return response()->json([
            'total' => $query(DaeOfficeSupply::query())->count(),
            'en_alerte' => $query(DaeOfficeSupply::query())->enAlerte()->count(),
            'demandes_en_attente' => $query(DaeOfficeSupplyRequest::query())->enAttente()->count(),
        ]);
    }
}
