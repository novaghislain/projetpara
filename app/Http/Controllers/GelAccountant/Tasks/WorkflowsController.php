<?php

namespace App\Http\Controllers\GelAccountant\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Gel\Workflow;
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
}
