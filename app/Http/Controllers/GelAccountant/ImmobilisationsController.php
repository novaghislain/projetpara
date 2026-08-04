<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des immobilisations (actifs fixes).
 *
 * Permet de lister, créer, modifier et supprimer les immobilisations
 * d'un cabinet comptable. Chaque actif est associé à un client et
 * fait l'objet d'un suivi d'amortissement (linéaire ou dégressif)
 * avec calcul automatique du taux et de la valeur nette comptable (VNC).
 */
class ImmobilisationsController extends Controller
{
    /**
     * Affiche la liste paginée des immobilisations.
     *
     * Les immobilisations peuvent être filtrées par client et par
     * statut (actif, cédé, mis au rebut).
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

        $query = FixedAsset::where('cabinet_id', $cabinetId);

        // Filtre par client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filtre par statut (actif, cede, mis_au_rebut)
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $assets = $query->with('client:id,nom_entreprise')
            ->orderBy('date_acquisition', 'desc')
            ->paginate(20);

        return view('gel-accountant.comptabilite.immobilisations.index', compact('assets') + ['currentSection' => 'comptabilite', 'currentPage' => 'immobilisations']);
    }

    /**
     * Crée une nouvelle immobilisation.
     *
     * Calcule automatiquement le taux d'amortissement en fonction
     * de la méthode choisie (linéaire : 100/durée, dégressif :
     * taux linéaire x 1,75). La valeur nette comptable initiale
     * est égale au coût d'acquisition.
     *
     * @param  Request $request La requête contenant les données de l'actif.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'client_id' => 'nullable|exists:gel_clients,id',
            'compte_id' => 'nullable|exists:gel_plan_comptable,id',
            'date_acquisition' => 'required|date',
            'cout_acquisition' => 'required|numeric|min:0',
            'valeur_residuelle' => 'nullable|numeric|min:0',
            'duree_vie' => 'required|integer|min:1',
            'methode_amort' => 'required|in:lineaire,degressif',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['statut'] = 'actif';

        // Calcul du taux d'amortissement selon la méthode choisie
        if ($validated['methode_amort'] === 'lineaire') {
            $validated['taux_amort'] = round(100 / $validated['duree_vie'], 2);
        } else {
            // Amortissement dégressif : taux linéaire majoré par le coefficient 1,75
            $validated['taux_amort'] = round((100 / $validated['duree_vie']) * 1.75, 2);
        }

        // Valeur nette comptable initiale = coût d'acquisition (pas encore d'amortissement)
        $validated['vnc'] = $validated['cout_acquisition'];
        $validated['amort_cumule'] = 0;

        FixedAsset::create($validated);

        return redirect()->route('gel-accountant.comptabilite.immobilisations')
            ->with('success', 'Actif créé avec succès.');
    }

    /**
     * Met à jour une immobilisation existante.
     *
     * Permet de modifier le nom, la valeur résiduelle ou le statut
     * (actif, cédé, mis au rebut).
     *
     * @param  Request $request La requête contenant les champs modifiés.
     * @param  int     $id      L'identifiant de l'actif à modifier.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $asset = FixedAsset::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'valeur_residuelle' => 'nullable|numeric|min:0',
            'statut' => 'required|in:actif,cede,mis_au_rebut',
        ]);

        $asset->update($validated);

        return redirect()->route('gel-accountant.comptabilite.immobilisations')
            ->with('success', 'Actif mis à jour.');
    }

    /**
     * Supprime une immobilisation.
     *
     * @param  int $id L'identifiant de l'actif à supprimer.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $asset = FixedAsset::findOrFail($id);
        $asset->delete();

        return redirect()->route('gel-accountant.comptabilite.immobilisations')
            ->with('success', 'Actif supprimé.');
    }
}
