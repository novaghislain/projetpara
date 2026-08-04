<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalLitigation;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des contentieux et litiges juridiques.
 *
 * Gère le suivi des litiges : création, historique des actions,
 * documents associés et changement de statut procédural.
 */
class LegalLitigationsController extends BaseLegalController
{
    /**
     * Affiche la liste des contentieux et litiges.
     *
     * Filtre par statut et/ou type si spécifié dans la requête.
     *
     * @param Request $request La requête HTTP entrante avec filtres optionnels (statut, type)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-contentieux']);
        }
        $clientId = $this->getClientId($request);
        $query = LegalLitigation::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Enregistre un nouveau litige ou contentieux.
     *
     * Valide les données et génère une référence unique.
     *
     * @param Request $request La requête HTTP avec les données du litige
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-contentieux-create']);
        }
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'type' => 'required|string',
            'nature' => 'required|string',
            'partie_adverse' => 'required|string',
            'tribunal' => 'required|string',
        ]);

        $data['reference'] = 'LIT-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();
        $data['client_id'] = $this->getClientId($request);

        $litige = LegalLitigation::create($data);

        return response()->json(['success' => true, 'data' => $litige]);
    }

    /**
     * Affiche les détails d'un litige.
     *
     * @param int|string $id L'identifiant du litige
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalLitigation::findOrFail($id));
        }
        return view('app', ['page' => 'legal-contentieux-show']);
    }

    /**
     * Met à jour un litige existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int|string $id L'identifiant du litige
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $litige->update($request->all());
        return response()->json(['success' => true, 'data' => $litige]);
    }

    /**
     * Supprime un litige.
     *
     * @param int|string $id L'identifiant du litige à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        LegalLitigation::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Ajoute une entrée à l'historique d'un litige.
     *
     * Enregistre une action et des notes dans le journal du litige.
     *
     * @param Request $request La requête HTTP contenant l'action et les notes
     * @param int|string $id L'identifiant du litige
     * @return \Illuminate\Http\JsonResponse
     */
    public function addHistorique(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $historique = $litige->historique ?? [];
        $historique[] = [
            'date' => now()->format('Y-m-d H:i:s'),
            'action' => $request->action,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
        ];
        $litige->update(['historique' => $historique]);

        return response()->json(['success' => true, 'data' => $litige]);
    }

    /**
     * Ajoute un document à un litige.
     *
     * @param Request $request La requête HTTP contenant le nom et le chemin du document
     * @param int|string $id L'identifiant du litige
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDocument(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $documents = $litige->documents ?? [];
        $documents[] = [
            'nom' => $request->nom,
            'path' => $request->path,
            'date' => now()->format('Y-m-d'),
        ];
        $litige->update(['documents' => $documents]);

        return response()->json(['success' => true]);
    }

    /**
     * Change le statut d'un litige et enregistre l'action dans l'historique.
     *
     * @param Request $request La requête HTTP contenant le nouveau statut
     * @param int|string $id L'identifiant du litige
     * @return \Illuminate\Http\JsonResponse
     */
    public function changerStatut(Request $request, $id)
    {
        $litige = LegalLitigation::findOrFail($id);
        $litige->update(['statut' => $request->statut]);

        // Ajouter au journal
        $historique = $litige->historique ?? [];
        $historique[] = [
            'date' => now()->format('Y-m-d H:i:s'),
            'action' => 'Changement de statut → ' . $request->statut,
            'user_id' => auth()->id(),
        ];
        $litige->update(['historique' => $historique]);

        return response()->json(['success' => true, 'data' => $litige]);
    }
}
