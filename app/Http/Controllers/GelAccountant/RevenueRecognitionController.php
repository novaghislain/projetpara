<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\RevenueRecognition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion de la reconnaissance de revenus.
 *
 * Permet de gérer les plans d'étalement de revenus selon différents
 * modèles (service sur intervalle, abonnement, étapes). Chaque plan
 * suit le montant total, le montant différé et le montant déjà
 * reconnu au fil du temps.
 */
class RevenueRecognitionController extends Controller
{
    /**
     * Affiche la liste paginée des plans de reconnaissance de revenus.
     *
     * Les plans peuvent être filtrés par client et par statut.
     *
     * @param  Request $request La requête avec les filtres optionnels
     *                          `client_id` et `statut`.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = RevenueRecognition::where('cabinet_id', $cabinetId);

        // Filtre par client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filtre par statut (actif, termine, etc.)
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $plans = $query->with(['client:id,nom_entreprise', 'compteProduit:id,numero,libelle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('gel-accountant.comptabilite.revenus.index', compact('plans') + ['currentSection' => 'comptabilite', 'currentPage' => 'revenus']);
    }

    /**
     * Crée un nouveau plan de reconnaissance de revenus.
     *
     * Initialise le plan avec le statut "actif". Le montant total est
     * entièrement différé au départ (montant_reconnu = 0).
     *
     * @param  Request $request La requête contenant les données du plan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'client_id' => 'nullable|exists:gel_clients,id',
            'compte_produit_id' => 'nullable|exists:gel_plan_comptable,id',
            'modele' => 'required|in:service_interval,abonnement,etapes',
            'montant_total' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['statut'] = 'actif';
        // Au départ, la totalité du montant est différée
        $validated['montant_differe'] = $validated['montant_total'];
        $validated['montant_reconnu'] = 0;

        RevenueRecognition::create($validated);

        return redirect()->route('gel-accountant.comptabilite.revenus')
            ->with('success', 'Plan de reconnaissance créé.');
    }

    /**
     * Supprime un plan de reconnaissance de revenus.
     *
     * @param  int $id L'identifiant du plan à supprimer.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $plan = RevenueRecognition::findOrFail($id);
        $plan->delete();

        return redirect()->route('gel-accountant.comptabilite.revenus')
            ->with('success', 'Plan supprimé.');
    }
}
