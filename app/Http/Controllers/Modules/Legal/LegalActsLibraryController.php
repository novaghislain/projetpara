<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalActsLibrary;
use App\Services\Legal\ActeGeneratorService;
use Illuminate\Http\Request;

/**
 * Contrôleur de la bibliothèque d'actes juridiques.
 *
 * Gère le CRUD des modèles d'actes ainsi que la génération
 * et la prévisualisation des documents juridiques.
 */
class LegalActsLibraryController extends BaseLegalController
{
    protected ActeGeneratorService $acteGenerator;

    /**
     * Constructeur du contrôleur de bibliothèque d'actes.
     *
     * @param ActeGeneratorService $acteGenerator Service de génération d'actes juridiques
     */
    public function __construct(ActeGeneratorService $acteGenerator)
    {
        $this->acteGenerator = $acteGenerator;
    }

    /**
     * Affiche la liste des modèles d'actes juridiques.
     *
     * Pour une requête AJAX, retourne la liste filtrée par client (ou tous si super admin).
     * Sinon, retourne la vue principale de la bibliothèque.
     *
     * @param Request $request La requête HTTP entrante
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-bibliotheque']);
        }
        // Super admin voit tous les modèles, sinon filtrer par client
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            $modeles = LegalActsLibrary::orderBy('categorie')->get();
        } else {
            $clientId = $this->getClientId($request);
            $modeles = LegalActsLibrary::whereNull('client_id')
                ->orWhere('client_id', $clientId)
                ->orderBy('categorie')
                ->get();
        }

        return response()->json($modeles);
    }

    /**
     * Enregistre un nouveau modèle d'acte juridique.
     *
     * Valide les données entrantes et persiste le modèle en base.
     *
     * @param Request $request La requête HTTP entrante avec les données du modèle
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-bibliotheque-create']);
        }
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'categorie' => 'required|string',
            'contenu' => 'required|string',
            'variables' => 'nullable|array',
            'is_public' => 'nullable|boolean',
        ]);

        $data['created_by'] = auth()->id();
        $modele = LegalActsLibrary::create($data);

        return response()->json(['success' => true, 'data' => $modele]);
    }

    /**
     * Affiche les détails d'un modèle d'acte spécifique.
     *
     * @param int|string $id L'identifiant du modèle
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalActsLibrary::findOrFail($id));
        }
        return view('app', ['page' => 'legal-bibliotheque-show']);
    }

    /**
     * Affiche le formulaire d'édition d'un modèle d'acte.
     *
     * @param int|string $id L'identifiant du modèle à éditer
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('app', ['page' => 'legal-bibliotheque-edit']);
    }

    /**
     * Met à jour un modèle d'acte juridique existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int|string $id L'identifiant du modèle à mettre à jour
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $modele = LegalActsLibrary::findOrFail($id);
        $modele->update($request->all());
        return response()->json(['success' => true, 'data' => $modele]);
    }

    /**
     * Supprime un modèle d'acte juridique.
     *
     * @param int|string $id L'identifiant du modèle à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        LegalActsLibrary::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Génère un document juridique à partir d'un modèle d'acte.
     *
     * Utilise le service ActeGeneratorService pour produire le document
     * avec les variables fournies et l'identifiant client.
     *
     * @param Request $request La requête HTTP contenant les variables de génération
     * @param int|string $id L'identifiant du modèle d'acte
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function generer(Request $request, $id)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-bibliotheque-generer']);
        }
        $html = $this->acteGenerator->generer(
            $id,
            $request->variables ?? [],
            $this->getClientId($request)
        );

        return response()->json(['html' => $html]);
    }

    /**
     * Prévisualise un modèle d'acte avec ses variables requises.
     *
     * Retourne le modèle d'acte ainsi que la liste des variables
     * nécessaires à sa génération.
     *
     * @param int|string $id L'identifiant du modèle d'acte
     * @return \Illuminate\Http\JsonResponse
     */
    public function preview($id)
    {
        $modele = LegalActsLibrary::findOrFail($id);
        $variables = $this->acteGenerator->getVariablesRequises($id);

        return response()->json([
            'modele' => $modele,
            'variables' => $variables,
        ]);
    }
}
