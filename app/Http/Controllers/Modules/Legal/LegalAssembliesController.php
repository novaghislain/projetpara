<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalAssembly;
use App\Services\Legal\AGService;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des assemblées générales.
 *
 * Gère le cycle de vie complet des assemblées : préparation,
 * convocation, tenue, feuille de présence, résolutions, PV et approbation.
 */
class LegalAssembliesController extends BaseLegalController
{
    protected AGService $agService;

    /**
     * Constructeur du contrôleur des assemblées générales.
     *
     * @param AGService $agService Service de gestion des assemblées générales
     */
    public function __construct(AGService $agService)
    {
        $this->agService = $agService;
    }

    /**
     * Affiche la liste des assemblées générales.
     *
     * Pour une requête AJAX, retourne la liste des AG filtrées par client,
     * triées par date de tenue décroissante.
     *
     * @param Request $request La requête HTTP entrante
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-assemblees']);
        }
        $clientId = $this->getClientId($request);
        return response()->json(
            LegalAssembly::byClient($clientId)->orderBy('date_tenue', 'desc')->get()
        );
    }

    /**
     * Affiche le formulaire de création d'une nouvelle assemblée générale.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('app', ['page' => 'legal-assemblees-create']);
    }

    /**
     * Enregistre une nouvelle assemblée générale après validation.
     *
     * Utilise le service AGService pour préparer l'assemblée.
     *
     * @param Request $request La requête HTTP avec les données de l'AG
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'annee' => 'required|integer',
            'date_tenue' => 'required|date',
            'lieu' => 'required|string',
            'ordre_du_jour' => 'required|array',
        ]);

        $data['created_by'] = auth()->id();
        $data['client_id'] = $this->getClientId($request);
        $ag = $this->agService->preparerAG($data);

        return response()->json(['success' => true, 'data' => $ag]);
    }

    /**
     * Affiche les détails d'une assemblée générale.
     *
     * @param int|string $id L'identifiant de l'assemblée
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalAssembly::findOrFail($id));
        }
        return view('app', ['page' => 'legal-assemblees-show']);
    }

    /**
     * Affiche le formulaire d'édition d'une assemblée générale.
     *
     * @param int|string $id L'identifiant de l'assemblée à éditer
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('app', ['page' => 'legal-assemblees-edit']);
    }

    /**
     * Met à jour une assemblée générale existante.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int|string $id L'identifiant de l'assemblée à mettre à jour
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update($request->all());
        return response()->json(['success' => true, 'data' => $ag]);
    }

    /**
     * Supprime une assemblée générale.
     *
     * @param int|string $id L'identifiant de l'assemblée à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        LegalAssembly::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Génère le document de convocation pour une assemblée générale.
     *
     * Utilise le service AGService pour produire le contenu de la convocation.
     *
     * @param int|string $id L'identifiant de l'assemblée
     * @return \Illuminate\Http\JsonResponse
     */
    public function genererConvocation($id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $contenu = $this->agService->acteGenerator->genererConvocationAG($ag);
        return response()->json(['html' => $contenu]);
    }

    /**
     * Enregistre la feuille de présence et le quorum d'une assemblée.
     *
     * @param Request $request La requête HTTP contenant les participants et l'état du quorum
     * @param int|string $id L'identifiant de l'assemblée
     * @return \Illuminate\Http\JsonResponse
     */
    public function enregistrerPresences(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update([
            'participants' => $request->participants,
            'quorum_atteint' => $request->quorum_atteint,
        ]);
        return response()->json(['success' => true, 'data' => $ag]);
    }

    /**
     * Enregistre les résolutions adoptées lors de l'assemblée générale.
     *
     * @param Request $request La requête HTTP contenant les résolutions
     * @param int|string $id L'identifiant de l'assemblée
     * @return \Illuminate\Http\JsonResponse
     */
    public function saisirResolutions(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update(['resolutions' => $request->resolutions]);
        return response()->json(['success' => true]);
    }

    /**
     * Génère le procès-verbal de l'assemblée générale.
     *
     * Produit le document PV, l'enregistre dans les documents
     * et met à jour le registre légal correspondant.
     *
     * @param int|string $id L'identifiant de l'assemblée
     * @return \Illuminate\Http\JsonResponse
     */
    public function genererPV($id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $path = $this->agService->genererPV($ag, $ag->resolutions ?? []);
        $this->agService->enregistrerAuRegistre($ag);
        return response()->json(['success' => true, 'pv_path' => $path]);
    }

    /**
     * Approuve le procès-verbal d'une assemblée générale.
     *
     * Marque le PV comme approuvé dans la base de données.
     *
     * @param Request $request La requête HTTP entrante
     * @param int|string $id L'identifiant de l'assemblée
     * @return \Illuminate\Http\JsonResponse
     */
    public function approuverPV(Request $request, $id)
    {
        $ag = LegalAssembly::findOrFail($id);
        $ag->update(['pv_approuve' => true]);
        return response()->json(['success' => true]);
    }
}
