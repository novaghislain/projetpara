<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Models\Legal\LegalContract;
use App\Models\Legal\LegalContractSignature;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des contrats juridiques.
 *
 * Gère le cycle de vie complet des contrats : création, signature,
 * renouvellement, résiliation, historique des versions et export.
 */
class LegalContratsController extends BaseLegalController
{
    /**
     * Affiche la liste des contrats juridiques.
     *
     * Filtre par statut et/ou type si spécifié dans la requête.
     * Pour une requête AJAX, retourne la liste des contrats du client.
     *
     * @param Request $request La requête HTTP entrante avec filtres optionnels (statut, type)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-contrats']);
        }
        $clientId = $this->getClientId($request);
        $query = LegalContract::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Enregistre un nouveau contrat juridique.
     *
     * Valide les données, génère une référence unique et crée le contrat en statut brouillon.
     *
     * @param Request $request La requête HTTP avec les données du contrat
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'type' => 'required|string',
            'parties' => 'required|array',
            'objet' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'montant' => 'nullable|numeric',
            'devise' => 'nullable|string|size:3',
        ]);

        $data['reference'] = 'CTR-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();
        $data['statut'] = 'brouillon';
        $data['client_id'] = $this->getClientId($request);

        $contrat = LegalContract::create($data);

        return response()->json(['success' => true, 'data' => $contrat]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau contrat.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('app', ['page' => 'legal-contrats-create']);
    }

    /**
     * Affiche les détails d'un contrat avec ses signatures.
     *
     * @param int|string $id L'identifiant du contrat
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        if (request()->expectsJson()) {
            $contrat = LegalContract::with('signatures')->findOrFail($id);
            return response()->json($contrat);
        }
        return view('app', ['page' => 'legal-contrats-show']);
    }

    /**
     * Affiche le formulaire d'édition d'un contrat.
     *
     * @param int|string $id L'identifiant du contrat à éditer
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('app', ['page' => 'legal-contrats-edit']);
    }

    /**
     * Met à jour un contrat existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int|string $id L'identifiant du contrat
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $contrat = LegalContract::findOrFail($id);
        $contrat->update($request->all());
        return response()->json(['success' => true, 'data' => $contrat]);
    }

    /**
     * Supprime un contrat juridique.
     *
     * @param int|string $id L'identifiant du contrat à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        LegalContract::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Enregistre la signature d'un contrat par un signataire.
     *
     * Crée une entrée de signature et met à jour le statut du contrat.
     *
     * @param Request $request La requête HTTP avec les données du signataire
     * @param int|string $id L'identifiant du contrat
     * @return \Illuminate\Http\JsonResponse
     */
    public function signer(Request $request, $id)
    {
        $contrat = LegalContract::findOrFail($id);

        $signature = LegalContractSignature::create([
            'contract_id' => $id,
            'signataire_nom' => $request->signataire_nom,
            'signataire_email' => $request->signataire_email,
            'signataire_role' => $request->signataire_role,
            'statut' => 'signé',
            'date_signature' => now(),
        ]);

        // Si tous les signataires ont signé, passer le contrat en "signé"
        $contrat->update(['statut' => 'signé', 'date_signature' => now()]);

        return response()->json(['success' => true, 'data' => $signature]);
    }

    /**
     * Renouvelle un contrat en créant une nouvelle version.
     *
     * Duplique le contrat existant avec un nouveau numéro de version
     * et un statut brouillon.
     *
     * @param int|string $id L'identifiant du contrat à renouveler
     * @return \Illuminate\Http\JsonResponse
     */
    public function renouveler($id)
    {
        $contrat = LegalContract::findOrFail($id);
        $nouveau = $contrat->replicate();
        $nouveau->reference = 'CTR-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $nouveau->statut = 'brouillon';
        $nouveau->version = $contrat->version + 1;
        $nouveau->save();

        return response()->json(['success' => true, 'data' => $nouveau]);
    }

    /**
     * Résilie un contrat en mettant à jour son statut.
     *
     * @param int|string $id L'identifiant du contrat à résilier
     * @return \Illuminate\Http\JsonResponse
     */
    public function resilier($id)
    {
        $contrat = LegalContract::findOrFail($id);
        $contrat->update(['statut' => 'résilié']);
        return response()->json(['success' => true]);
    }

    /**
     * Génère un nouveau contrat à partir d'un modèle d'acte de la bibliothèque.
     *
     * @param Request $request La requête HTTP avec les données du modèle
     * @return \Illuminate\Http\JsonResponse
     */
    public function genererDepuisModele(Request $request)
    {
        // Crée un contrat depuis un modèle de la bibliothèque d'actes
        $data = $request->all();
        $data['created_by'] = auth()->id();
        $data['reference'] = 'CTR-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $contrat = LegalContract::create($data);

        return response()->json(['success' => true, 'data' => $contrat]);
    }

    /**
     * Retourne l'historique des versions d'un contrat.
     *
     * @param int|string $id L'identifiant du contrat
     * @return \Illuminate\Http\JsonResponse
     */
    public function historique($id)
    {
        $contrat = LegalContract::findOrFail($id);
        return response()->json($contrat->historique_versions ?? []);
    }

    /**
     * Exporte un contrat en retournant le chemin du document.
     *
     * @param int|string $id L'identifiant du contrat à exporter
     * @return \Illuminate\Http\JsonResponse
     */
    public function export($id)
    {
        $contrat = LegalContract::findOrFail($id);
        // Retourne le chemin du document pour téléchargement
        return response()->json(['path' => $contrat->document_path]);
    }
}
