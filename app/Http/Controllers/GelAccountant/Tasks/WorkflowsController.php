<?php

namespace App\Http\Controllers\GelAccountant\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Gel\Workflow;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStepLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des workflows automatisés.
 *
 * Permet de créer, lister, activer/désactiver et supprimer les
 * workflows d'un cabinet. Les workflows sont des processus
 * automatisés (récurrents ou immédiats) associés à un client
 * et déclenchés selon une fréquence définie.
 */
class WorkflowsController extends Controller
{
    /**
     * Affiche la liste paginée des workflows du cabinet.
     *
     * Les workflows peuvent être filtrés par type. Les statistiques
     * (workflows actifs, exécutions du mois) sont transmises à la vue.
     *
     * @param  Request $request La requête avec le filtre optionnel
     *                          `type`.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Workflow::where('cabinet_id', $cabinetId);

        // Filtre par type de workflow
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $workflows = $query->with('client:id,nom_entreprise')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'actifs' => Workflow::where('cabinet_id', $cabinetId)->where('actif', true)->count(),
            'executes_mois' => Workflow::where('cabinet_id', $cabinetId)
                ->where('actif', true)
                ->count(),
        ];

        return view('gel-accountant.workflows.index', compact('workflows', 'stats') + ['currentSection' => 'workflows', 'currentPage' => 'workflows']);
    }

    /**
     * Crée un nouveau workflow.
     *
     * Le workflow est rattaché au cabinet et activé par défaut.
     *
     * @param  Request $request La requête contenant les données du workflow.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'client_id' => 'nullable|exists:gel_clients,id',
            'conditions' => 'nullable|json',
            'actions' => 'nullable|json',
            'frequence' => 'required|in:immediate,quotidienne,hebdomadaire,personnalisee',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['actif'] = true;

        Workflow::create($validated);

        return redirect()->route('gel-accountant.workflows')
            ->with('success', 'Workflow créé avec succès.');
    }

    /**
     * Bascule l'état actif/inactif d'un workflow.
     *
     * @param  int $id L'identifiant du workflow.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle($id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->actif = !$workflow->actif;
        $workflow->save();

        return redirect()->route('gel-accountant.workflows')
            ->with('success', 'Statut du workflow mis à jour.');
    }

    /**
     * Supprime un workflow.
     *
     * @param  int $id L'identifiant du workflow à supprimer.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->delete();

        return redirect()->route('gel-accountant.workflows')
            ->with('success', 'Workflow supprimé.');
    }

    // ==========================================
    // MÉTHODES POUR LES APPROBATIONS (Approval Workflows)
    // ==========================================

    /**
     * Affiche les demandes d'approbation en attente pour l'utilisateur connecté.
     */
    public function pending(Request $request)
    {
        $user = Auth::user();
        
        // On récupère toutes les requêtes en attente
        $pendingRequests = ApprovalRequest::where('status', 'pending')
            ->with(['workflow', 'requester'])
            ->latest()
            ->paginate(20);

        // Idéalement on devrait filtrer par approbateur selon les rôles, 
        // mais pour l'instant on affiche toutes les requêtes en attente pour le cabinet.
        
        return view('gel-accountant.workflows.pending', compact('pendingRequests') + ['currentSection' => 'workflows', 'currentPage' => 'workflows']);
    }

    /**
     * Approuve une demande d'approbation.
     */
    public function approve(Request $request, $id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);
        $user = Auth::user();

        // Créer le log de l'étape
        ApprovalStepLog::create([
            'request_id' => $approvalRequest->id,
            'step_number' => $approvalRequest->current_step + 1,
            'approver_id' => $user->id,
            'action' => 'approved',
            'comment' => $request->input('comment'),
        ]);

        // Mettre à jour la requête
        $workflow = $approvalRequest->workflow;
        $steps = json_decode($workflow->steps, true);

        if ($approvalRequest->current_step + 1 >= count($steps)) {
            // Toutes les étapes sont terminées
            $approvalRequest->status = 'approved';
            $approvalRequest->completed_at = now();
        } else {
            // Passe à l'étape suivante
            $approvalRequest->current_step++;
        }
        $approvalRequest->save();

        return redirect()->route('gel-accountant.workflows.pending')
            ->with('success', 'Demande approuvée avec succès.');
    }

    /**
     * Rejette une demande d'approbation.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $approvalRequest = ApprovalRequest::findOrFail($id);
        $user = Auth::user();

        // Créer le log de l'étape
        ApprovalStepLog::create([
            'request_id' => $approvalRequest->id,
            'step_number' => $approvalRequest->current_step + 1,
            'approver_id' => $user->id,
            'action' => 'rejected',
            'comment' => $request->input('comment'),
        ]);

        // Mettre à jour la requête
        $approvalRequest->status = 'rejected';
        $approvalRequest->completed_at = now();
        $approvalRequest->save();

        return redirect()->route('gel-accountant.workflows.pending')
            ->with('error', 'Demande rejetée.');
    }
}
