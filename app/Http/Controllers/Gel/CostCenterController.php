<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur des centres de coût.
 * Gère le CRUD des centres de coût associés aux clients,
 * avec journalisation des actions via AuditTrailService.
 */
class CostCenterController extends Controller
{
    /**
     * Liste paginée des centres de coût avec filtre optionnel par client.
     * Charge les relations client et parent pour l'affichage.
     *
     * @param Request $request La requête HTTP avec le filtre optionnel client_id
     * @return View
     */
    public function index(Request $request): View
    {
        $query = CostCenter::with('client', 'parent');
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $centers = $query->latest()->paginate(20);
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-cost-centers', 'props' => compact('centers', 'clients')]);
    }

    /**
     * Affiche le détail d'un centre de coût avec ses relations.
     * Charge le client, le parent et les enfants pour la hiérarchie.
     *
     * @param CostCenter $center Le centre de coût à afficher
     * @return View
     */
    public function show(CostCenter $center): View
    {
        $center->load('client', 'parent', 'children');
        return view('app', ['page' => 'gel-cost-centers-show', 'props' => compact('center')]);
    }

    /**
     * Affiche le formulaire de création d'un centre de coût.
     * Liste les clients actifs et les centres parents disponibles.
     *
     * @return View
     */
    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        $parents = CostCenter::whereNull('parent_id')->get(['id', 'name', 'code']);
        return view('app', ['page' => 'gel-cost-centers-form', 'props' => compact('clients', 'parents')]);
    }

    /**
     * Enregistre un nouveau centre de coût.
     * Valide les données et journalise la création dans AuditTrail.
     *
     * @param Request $request La requête HTTP avec les données du centre
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:department,project,product,region',
            'parent_id' => 'nullable|exists:cost_centers,id',
        ]);

        $center = CostCenter::create($validated);
        AuditTrailService::log($center, 'created', null, $validated, 'Centre de coût créé');

        return redirect()->route('gel.cost-centers.index')->with('success', 'Centre de coût créé.');
    }

    /**
     * Met à jour un centre de coût existant.
     * Journalise les modifications (anciennes et nouvelles valeurs) via AuditTrail.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param CostCenter $center Le centre de coût à modifier
     * @return RedirectResponse
     */
    public function update(Request $request, CostCenter $center): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:department,project,product,region',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'is_active' => 'boolean',
        ]);

        $old = $center->getAttributes();
        $center->update($validated);
        AuditTrailService::log($center, 'updated', $old, $center->getAttributes(), 'Centre de coût mis à jour');

        return redirect()->route('gel.cost-centers.index')->with('success', 'Centre de coût mis à jour.');
    }

    /**
     * Supprime un centre de coût.
     * Vérifie qu'il n'a pas d'enfants avant suppression, journalise l'action.
     *
     * @param CostCenter $center Le centre de coût à supprimer
     * @return RedirectResponse
     */
    public function destroy(CostCenter $center): RedirectResponse
    {
        // Vérifier que le centre n'a pas de sous-centres avant suppression
        if ($center->children()->count() > 0) {
            return back()->withErrors(['Supprimez d\'abord les centres de coût enfants.']);
        }
        $old = $center->getAttributes();
        $center->delete();
        AuditTrailService::log($center, 'deleted', $old, null, 'Centre de coût supprimé');
        return redirect()->route('gel.cost-centers.index')->with('success', 'Centre de coût supprimé.');
    }
}
