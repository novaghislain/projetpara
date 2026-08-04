<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalDossier;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des dossiers juridiques.
 *
 * Gère le cycle de vie des dossiers : création, suivi, assignation,
 * changement de statut et gestion des documents associés.
 */
class LegalDossiersController extends BaseLegalController
{
    /**
     * Affiche la liste des dossiers juridiques.
     *
     * Filtre par statut et/ou priorité si spécifié dans la requête.
     *
     * @param Request $request La requête HTTP entrante avec filtres optionnels (statut, priorite)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-dossiers']);
        }
        $clientId = $this->getClientId($request);
        $query = LegalDossier::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->priorite) {
            $query->where('priorite', $request->priorite);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Enregistre un nouveau dossier juridique.
     *
     * Valide les données, génère une référence unique et crée le dossier.
     *
     * @param Request $request La requête HTTP avec les données du dossier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-dossiers-create']);
        }
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'priorite' => 'nullable|string',
        ]);

        $data['reference'] = 'DOS-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();
        $data['client_id'] = $this->getClientId($request);

        $dossier = LegalDossier::create($data);

        return response()->json(['success' => true, 'data' => $dossier]);
    }

    /**
     * Affiche les détails d'un dossier juridique.
     *
     * @param int|string $id L'identifiant du dossier
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        if (request()->expectsJson()) {
            return response()->json(LegalDossier::findOrFail($id));
        }
        return view('app', ['page' => 'legal-dossiers-show']);
    }

    /**
     * Met à jour un dossier juridique existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int|string $id L'identifiant du dossier
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $dossier->update($request->all());
        return response()->json(['success' => true, 'data' => $dossier]);
    }

    /**
     * Supprime un dossier juridique.
     *
     * @param int|string $id L'identifiant du dossier à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        LegalDossier::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Assigne un dossier juridique à un utilisateur.
     *
     * @param Request $request La requête HTTP contenant l'identifiant de l'utilisateur
     * @param int|string $id L'identifiant du dossier
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $dossier->update(['assigned_to' => $request->user_id]);
        return response()->json(['success' => true, 'data' => $dossier]);
    }

    /**
     * Change le statut d'un dossier juridique.
     *
     * @param Request $request La requête HTTP contenant le nouveau statut
     * @param int|string $id L'identifiant du dossier
     * @return \Illuminate\Http\JsonResponse
     */
    public function changerStatut(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $dossier->update(['statut' => $request->statut]);
        return response()->json(['success' => true, 'data' => $dossier]);
    }

    /**
     * Ajoute un document au dossier juridique.
     *
     * @param Request $request La requête HTTP contenant le nom et le chemin du document
     * @param int|string $id L'identifiant du dossier
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDocument(Request $request, $id)
    {
        $dossier = LegalDossier::findOrFail($id);
        $documents = $dossier->documents ?? [];
        $documents[] = [
            'nom' => $request->nom,
            'path' => $request->path,
            'date' => now()->format('Y-m-d'),
        ];
        $dossier->update(['documents' => $documents]);

        return response()->json(['success' => true]);
    }
}
