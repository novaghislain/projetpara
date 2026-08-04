<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaePersonnelDossier;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion du personnel du module DAE.
 *
 * Permet la gestion des dossiers du personnel avec suivi
 * des effectifs, départements et statuts.
 */
class DaePersonnelController extends BaseDaeController
{
    /**
     * Liste paginée du personnel avec filtres.
     *
     * Filtres disponibles : statut, département, recherche
     * (nom, prénom, email, poste).
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = DaePersonnelDossier::orderBy('nom')->orderBy('prenom');

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('departement')) $query->where('departement', $request->departement);
        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                  ->orWhere('prenom', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('poste', 'like', "%{$s}%");
            });
        }

        $personnel = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($personnel);
        return view('app', ['page' => 'dae-personnel']);
    }

    /**
     * Crée un nouveau dossier personnel.
     *
     * @param Request $request La requête HTTP avec les données du personnel
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'          => 'required|string|max:255',
            'prenom'       => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'telephone'    => 'nullable|string|max:50',
            'poste'        => 'nullable|string|max:255',
            'departement'  => 'nullable|string|max:255',
            'date_embauche'=> 'nullable|date',
            'date_depart'  => 'nullable|date|after:date_embauche',
            'statut'       => 'nullable|in:actif,conge,suspendu,sorti',
            'type_contrat' => 'nullable|string|max:100',
            'salaire'      => 'nullable|numeric|min:0',
            'numero_securite_sociale' => 'nullable|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $validated['statut'] ??= 'actif';
        $validated['client_id'] = $this->getClientId($request);
        $personne = DaePersonnelDossier::create($validated);

        if ($request->expectsJson()) return response()->json($personne, 201);
        return redirect()->route('dae.personnel.index')->with('success', 'Membre ajouté.');
    }

    /**
     * Affiche un dossier personnel spécifique.
     *
     * @param int $id L'identifiant du membre du personnel
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        $personne = DaePersonnelDossier::with('client')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($personne);
        return view('app', ['page' => 'dae-personnel-show']);
    }

    /**
     * Met à jour un dossier personnel existant.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant du membre du personnel
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $personne = DaePersonnelDossier::findOrFail($id);

        $validated = $request->validate([
            'nom'          => 'sometimes|string|max:255',
            'prenom'       => 'sometimes|string|max:255',
            'email'        => 'nullable|email|max:255',
            'telephone'    => 'nullable|string|max:50',
            'poste'        => 'nullable|string|max:255',
            'departement'  => 'nullable|string|max:255',
            'date_embauche'=> 'nullable|date',
            'date_depart'  => 'nullable|date|after:date_embauche',
            'statut'       => 'sometimes|in:actif,conge,suspendu,sorti',
            'type_contrat' => 'nullable|string|max:100',
            'salaire'      => 'nullable|numeric|min:0',
            'numero_securite_sociale' => 'nullable|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $personne->update($validated);

        if ($request->expectsJson()) return response()->json($personne);
        return redirect()->route('dae.personnel.index')->with('success', 'Membre mis à jour.');
    }

    /**
     * Supprime un dossier personnel.
     *
     * @param int $id L'identifiant du membre à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $personne = DaePersonnelDossier::findOrFail($id);
        $personne->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Membre supprimé.']);
        return redirect()->route('dae.personnel.index')->with('success', 'Membre supprimé.');
    }

    /**
     * Retourne les changements récents et les statistiques du personnel.
     *
     * Fournit l'effectif actif, les effectifs par département
     * et les 5 derniers membres ajoutés.
     *
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\JsonResponse
     */
    public function changementsRecent(Request $request)
    {
        $query = DaePersonnelDossier::whereIn('statut', ['actif', 'conge'])->where('client_id', $this->getClientId($request));

        return response()->json([
            'effectif' => $query->count(),
            'par_departement' => DaePersonnelDossier::selectRaw('departement, count(*) as total')
                ->where('statut', 'actif')
                ->groupBy('departement')
                ->get(),
            'recents' => DaePersonnelDossier::orderBy('created_at', 'desc')->take(5)->get(),
        ]);
    }
}
