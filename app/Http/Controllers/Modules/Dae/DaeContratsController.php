<?php

namespace App\Http\Controllers\Modules\Dae;

use App\Models\Dae\DaeContrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Contrôleur de gestion des contrats du module DAE.
 *
 * Permet la gestion complète des contrats avec création, suivi,
 * renouvellement, téléchargement des fichiers et recherche avancée.
 */
class DaeContratsController extends BaseDaeController
{
    /**
     * Liste paginée des contrats avec filtres.
     *
     * Filtres disponibles : statut, type_contrat, recherche (titre, référence, partie_adverse).
     *
     * @param Request $request La requête HTTP avec les paramètres de filtre
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = DaeContrat::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('type_contrat')) $query->where('type_contrat', $request->type_contrat);
        $query->where('client_id', $this->getClientId($request));
        if ($request->filled('recherche')) {
            $s = $request->recherche;
            $query->where(function ($q) use ($s) {
                $q->where('titre', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('partie_adverse', 'like', "%{$s}%");
            });
        }

        $contrats = $query->paginate(20);
        if ($request->expectsJson()) return response()->json($contrats);
        return view('app', ['page' => 'dae-contrats']);
    }

    /**
     * Affiche le formulaire de création d'un contrat.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('app', ['page' => 'dae-contrats-create']);
    }

    /**
     * Crée un nouveau contrat avec gestion du fichier joint.
     *
     * @param Request $request La requête HTTP avec les données du contrat
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference'     => 'nullable|string|max:100',
            'titre'         => 'required|string|max:500',
            'type_contrat'  => 'required|string|max:200',
            'partie_adverse'=> 'nullable|string|max:255',
            'date_debut'    => 'required|date',
            'date_fin'      => 'nullable|date|after:date_debut',
            'date_preavis'  => 'nullable|date',
            'duree_mois'    => 'nullable|integer|min:1',
            'montant'       => 'nullable|numeric|min:0',
            'devise'        => 'nullable|string|max:3',
            'statut'        => 'nullable|in:brouillon,actif,expire,resilie,renouvele',
            'fichier'       => 'nullable|file|max:10240',
            'renouvelable'  => 'nullable|boolean',
            'tags'          => 'nullable|json',
        ]);

        $validated['reference'] ??= 'CT-' . strtoupper(uniqid());
        $validated['statut'] ??= 'brouillon';
        $validated['client_id'] = $this->getClientId($request);

        if ($request->hasFile('fichier')) {
            $validated['fichier'] = $request->file('fichier')->store('dae/contrats', 'public');
        }

        $contrat = DaeContrat::create($validated);

        if ($request->expectsJson()) return response()->json($contrat, 201);
        return redirect()->route('dae.contrats.index')->with('success', 'Contrat créé.');
    }

    /**
     * Affiche un contrat spécifique.
     *
     * @param int $id L'identifiant du contrat
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        $contrat = DaeContrat::with('client')->findOrFail($id);
        if (request()->expectsJson()) return response()->json($contrat);
        return view('app', ['page' => 'dae-contrats-show']);
    }

    /**
     * Affiche le formulaire d'édition d'un contrat.
     *
     * @param int $id L'identifiant du contrat
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('app', ['page' => 'dae-contrats-edit']);
    }

    /**
     * Met à jour un contrat existant avec gestion du fichier joint.
     *
     * @param Request $request La requête HTTP avec les données de mise à jour
     * @param int $id L'identifiant du contrat
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $contrat = DaeContrat::findOrFail($id);

        $validated = $request->validate([
            'titre'         => 'sometimes|string|max:500',
            'type_contrat'  => 'sometimes|string|max:200',
            'partie_adverse'=> 'nullable|string|max:255',
            'date_debut'    => 'sometimes|date',
            'date_fin'      => 'nullable|date|after:date_debut',
            'date_preavis'  => 'nullable|date',
            'duree_mois'    => 'nullable|integer|min:1',
            'montant'       => 'nullable|numeric|min:0',
            'devise'        => 'nullable|string|max:3',
            'statut'        => 'sometimes|in:brouillon,actif,expire,resilie,renouvele',
            'fichier'       => 'nullable|file|max:10240',
            'renouvelable'  => 'nullable|boolean',
            'tags'          => 'nullable|json',
        ]);

        if ($request->hasFile('fichier')) {
            if ($contrat->fichier) Storage::disk('public')->delete($contrat->fichier);
            $validated['fichier'] = $request->file('fichier')->store('dae/contrats', 'public');
        }

        $contrat->update($validated);

        if ($request->expectsJson()) return response()->json($contrat);
        return redirect()->route('dae.contrats.index')->with('success', 'Contrat mis à jour.');
    }

    /**
     * Supprime un contrat et son fichier associé.
     *
     * @param int $id L'identifiant du contrat à supprimer
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $contrat = DaeContrat::findOrFail($id);
        if ($contrat->fichier) Storage::disk('public')->delete($contrat->fichier);
        $contrat->delete();

        if (request()->expectsJson()) return response()->json(['message' => 'Contrat supprimé.']);
        return redirect()->route('dae.contrats.index')->with('success', 'Contrat supprimé.');
    }

    /**
     * Renouvelle un contrat en créant une copie avec les mêmes paramètres.
     *
     * L'ancien contrat est marqué comme "renouvele" et un nouveau contrat
     * est créé par réplication avec une nouvelle référence et date de début.
     *
     * @param Request $request La requête HTTP avec la nouvelle date de fin et le montant
     * @param int $id L'identifiant du contrat à renouveler
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function renouveler(Request $request, $id)
    {
        $contrat = DaeContrat::findOrFail($id);
        $contrat->update(['statut' => 'renouvele']);

        $newValidated = $request->validate([
            'date_fin' => 'nullable|date|after:today',
            'montant'  => 'nullable|numeric|min:0',
        ]);

        $newContrat = $contrat->replicate(['reference', 'statut']);
        $newContrat->reference = 'CT-' . strtoupper(uniqid());
        $newContrat->statut = 'actif';
        $newContrat->date_debut = now();
        $newContrat->date_fin = $newValidated['date_fin'] ?? $contrat->date_fin?->addYear();
        $newContrat->montant = $newValidated['montant'] ?? $contrat->montant;
        $newContrat->save();

        if ($request->expectsJson()) return response()->json($newContrat, 201);
        return redirect()->route('dae.contrats.show', $newContrat->id)
            ->with('success', 'Contrat renouvelé.');
    }

    /**
     * Télécharge le fichier d'un contrat.
     *
     * @param int $id L'identifiant du contrat
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function telecharger($id)
    {
        $contrat = DaeContrat::findOrFail($id);
        if (!$contrat->fichier) abort(404);
        return Storage::disk('public')->download($contrat->fichier);
    }
}
