<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalCompliance;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion de la conformité légale et réglementaire.
 *
 * Gère les obligations déclaratives, les échéances de conformité,
 * le suivi des statuts et le calendrier des obligations.
 */
class LegalComplianceController extends BaseLegalController
{
    /**
     * Affiche la liste des obligations de conformité.
     *
     * Filtre par statut et/ou type si spécifié dans la requête.
     *
     * @param Request $request La requête HTTP entrante avec filtres optionnels (statut, type)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-conformite']);
        }
        $clientId = $this->getClientId($request);
        $query = LegalCompliance::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('date_echeance')->get());
    }

    /**
     * Enregistre une nouvelle obligation de conformité.
     *
     * @param Request $request La requête HTTP avec les données de l'obligation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-conformite-create']);
        }
        $data = $request->validate([
            'intitule' => 'required|string|max:255',
            'type' => 'required|string',
            'organisme' => 'required|string',
            'periodicite' => 'required|string',
            'date_echeance' => 'required|date',
        ]);

        $data['created_by'] = auth()->id();
        $data['client_id'] = $this->getClientId($request);
        $item = LegalCompliance::create($data);

        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * Affiche les détails d'une obligation de conformité.
     *
     * @param int|string $id L'identifiant de l'obligation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalCompliance::findOrFail($id));
        }
        return view('app', ['page' => 'legal-conformite-show']);
    }

    /**
     * Met à jour une obligation de conformité existante.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int|string $id L'identifiant de l'obligation
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $item = LegalCompliance::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * Supprime une obligation de conformité.
     *
     * @param int|string $id L'identifiant de l'obligation à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        LegalCompliance::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Marque une obligation de conformité comme vérifiée et conforme.
     *
     * Met à jour le statut et enregistre la date de dernière conformité.
     *
     * @param int|string $id L'identifiant de l'obligation
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifier($id)
    {
        $item = LegalCompliance::findOrFail($id);
        $item->update([
            'statut' => 'conforme',
            'date_derniere_conformite' => now(),
        ]);
        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * Affiche le calendrier des échéances de conformité pour une année donnée.
     *
     * @param Request $request La requête HTTP contenant l'année optionnelle
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function calendrier(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-conformite-calendrier']);
        }
        $clientId = $this->getClientId($request);
        $items = LegalCompliance::byClient($clientId)
            ->whereYear('date_echeance', $request->annee ?? date('Y'))
            ->orderBy('date_echeance')
            ->get();

        return response()->json($items);
    }
}
