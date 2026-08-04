<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ItMaintenanceContract;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItMaintenanceContractController extends Controller
{
    /**
     * Contrôleur de gestion des contrats de maintenance IT.
     * Permet de gérer le cycle de vie des contrats de maintenance :
     * correctif, préventif, service complet et hotline.
     */

    /**
     * Liste paginée des contrats de maintenance avec filtres.
     *
     * @param Request $request La requête HTTP avec les filtres (client, statut)
     * @return View|JsonResponse
     */
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = ItMaintenanceContract::with('client');

        // Filtres optionnels
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('status')) $query->where('status', $request->status);

        $contracts = $query->latest()->paginate(20);

        // Réponse JSON pour les appels API
        if ($request->wantsJson()) {
            return response()->json($contracts);
        }

        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-it-maintenance-contracts', 'props' => compact('contracts', 'clients')]);
    }

    /**
     * Affiche le formulaire de création d'un contrat de maintenance.
     *
     * @return View
     */
    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-it-maintenance-contracts-form', 'props' => compact('clients')]);
    }

    /**
     * Enregistre un nouveau contrat de maintenance.
     *
     * @param Request $request La requête HTTP avec les données du contrat
     * @return RedirectResponse Redirection vers la liste
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'reference' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'type' => 'required|in:corrective,preventive,full_service,hotline',
            'status' => 'required|in:active,expired,suspended',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'monthly_amount' => 'nullable|numeric',
            'included_hours' => 'required|integer|min:0',
            'response_time_hours' => 'required|integer|min:1',
            'coverage_hours' => 'required|string|max:50',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $contract = ItMaintenanceContract::create($validated);
        AuditTrailService::log($contract, 'created', null, $validated, 'Contrat maintenance créé');

        return redirect()->route('gel.it-maintenance-contracts.index')->with('success', 'Contrat créé.');
    }

    /**
     * Affiche le détail d'un contrat de maintenance.
     *
     * @param ItMaintenanceContract $contract Le contrat à afficher (injection de modèle)
     * @return View
     */
    public function show(ItMaintenanceContract $contract): View
    {
        $contract->load('client');
        return view('app', ['page' => 'gel-it-maintenance-contracts-show', 'props' => compact('contract')]);
    }

    /**
     * Met à jour un contrat de maintenance existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param ItMaintenanceContract $contract Le contrat à modifier (injection de modèle)
     * @return RedirectResponse Redirection vers la liste
     */
    public function update(Request $request, ItMaintenanceContract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'type' => 'required|in:corrective,preventive,full_service,hotline',
            'status' => 'required|in:active,expired,suspended',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'monthly_amount' => 'nullable|numeric',
            'included_hours' => 'required|integer|min:0',
            'response_time_hours' => 'required|integer|min:1',
            'coverage_hours' => 'required|string|max:50',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $old = $contract->getAttributes();
        $contract->update($validated);
        AuditTrailService::log($contract, 'updated', $old, $contract->getAttributes(), 'Contrat maintenance mis à jour');

        return redirect()->route('gel.it-maintenance-contracts.index')->with('success', 'Contrat mis à jour.');
    }

    /**
     * Supprime un contrat de maintenance.
     *
     * @param ItMaintenanceContract $contract Le contrat à supprimer (injection de modèle)
     * @return RedirectResponse Redirection vers la liste
     */
    public function destroy(ItMaintenanceContract $contract): RedirectResponse
    {
        $old = $contract->getAttributes();
        $contract->delete();
        AuditTrailService::log($contract, 'deleted', $old, null, 'Contrat maintenance supprimé');
        return redirect()->route('gel.it-maintenance-contracts.index')->with('success', 'Contrat supprimé.');
    }
}
