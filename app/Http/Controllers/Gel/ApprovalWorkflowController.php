<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Models\Client;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des workflows d'approbation.
 * Permet de créer, modifier, visualiser et supprimer des workflows
 * qui déclenchent des processus d'approbation sur différents modèles
 * (écritures comptables, devis, factures, etc.).
 */
class ApprovalWorkflowController extends Controller
{
    /**
     * Affiche la liste paginée des workflows d'approbation.
     * Permet de filtrer par client.
     *
     * @param Request $request La requête HTTP contenant optionnellement le filtre client_id
     * @return View
     */
    public function index(Request $request): View
    {
        $query = ApprovalWorkflow::with('client');
        // Filtrer par client si spécifié dans la requête
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $workflows = $query->latest()->paginate(20);
        // Récupérer la liste des clients actifs pour le filtre
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-approval-workflows', 'props' => compact('workflows', 'clients')]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau workflow d'approbation.
     *
     * @return View
     */
    public function create(): View
    {
        $clients = Client::where('status', 'actif')->orderBy('company_name')->get(['id', 'company_name']);
        return view('app', ['page' => 'gel-approval-workflows-form', 'props' => compact('clients')]);
    }

    /**
     * Enregistre un nouveau workflow d'approbation.
     * Valide les données, crée le workflow et journalise l'action.
     *
     * @param Request $request La requête HTTP contenant les données du workflow
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'trigger_model' => 'required|string|max:100',
            'trigger_condition' => 'nullable|array',
            'steps' => 'required|array|min:1',
        ]);

        $workflow = ApprovalWorkflow::create($validated);
        AuditTrailService::log($workflow, 'created', null, $validated, 'Workflow d\'approbation créé');

        return redirect()->route('gel.approval-workflows.index')->with('success', 'Workflow créé.');
    }

    /**
     * Affiche les détails d'un workflow d'approbation.
     *
     * @param ApprovalWorkflow $workflow Le workflow à afficher (injection de modèle)
     * @return View
     */
    public function show(ApprovalWorkflow $workflow): View
    {
        $workflow->load('client');
        return view('app', ['page' => 'gel-approval-workflows-show', 'props' => compact('workflow')]);
    }

    /**
     * Met à jour un workflow d'approbation existant.
     * Journalise les anciennes et nouvelles valeurs.
     *
     * @param Request $request La requête HTTP contenant les données mises à jour
     * @param ApprovalWorkflow $workflow Le workflow à modifier (injection de modèle)
     * @return RedirectResponse
     */
    public function update(Request $request, ApprovalWorkflow $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_model' => 'required|string|max:100',
            'trigger_condition' => 'nullable|array',
            'steps' => 'required|array|min:1',
            'is_active' => 'boolean',
        ]);

        $old = $workflow->getAttributes();
        $workflow->update($validated);
        AuditTrailService::log($workflow, 'updated', $old, $workflow->getAttributes(), 'Workflow mis à jour');

        return redirect()->route('gel.approval-workflows.show', $workflow)->with('success', 'Workflow mis à jour.');
    }

    /**
     * Supprime un workflow d'approbation.
     * Journalise la suppression avant de supprimer définitivement.
     *
     * @param ApprovalWorkflow $workflow Le workflow à supprimer (injection de modèle)
     * @return RedirectResponse
     */
    public function destroy(ApprovalWorkflow $workflow): RedirectResponse
    {
        $old = $workflow->getAttributes();
        $workflow->delete();
        AuditTrailService::log($workflow, 'deleted', $old, null, 'Workflow supprimé');
        return redirect()->route('gel.approval-workflows.index')->with('success', 'Workflow supprimé.');
    }
}
