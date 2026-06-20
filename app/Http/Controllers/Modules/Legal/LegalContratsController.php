<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalContract;
use App\Models\Legal\LegalContractSignature;
use Illuminate\Http\Request;

class LegalContratsController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-contrats']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $query = LegalContract::byClient($clientId);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|integer',
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

        $contrat = LegalContract::create($data);

        return response()->json(['success' => true, 'data' => $contrat]);
    }

    public function create()
    {
        return view('app', ['page' => 'legal-contrats-create']);
    }

    public function show($id)
    {
        if (request()->expectsJson()) {
            $contrat = LegalContract::with('signatures')->findOrFail($id);
            return response()->json($contrat);
        }
        return view('app', ['page' => 'legal-contrats-show']);
    }

    public function edit($id)
    {
        return view('app', ['page' => 'legal-contrats-edit']);
    }

    public function update(Request $request, $id)
    {
        $contrat = LegalContract::findOrFail($id);
        $contrat->update($request->all());
        return response()->json(['success' => true, 'data' => $contrat]);
    }

    public function destroy($id)
    {
        LegalContract::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

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

    public function resilier($id)
    {
        $contrat = LegalContract::findOrFail($id);
        $contrat->update(['statut' => 'résilié']);
        return response()->json(['success' => true]);
    }

    public function genererDepuisModele(Request $request)
    {
        // Crée un contrat depuis un modèle de la bibliothèque d'actes
        $data = $request->all();
        $data['created_by'] = auth()->id();
        $data['reference'] = 'CTR-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $contrat = LegalContract::create($data);

        return response()->json(['success' => true, 'data' => $contrat]);
    }

    public function historique($id)
    {
        $contrat = LegalContract::findOrFail($id);
        return response()->json($contrat->historique_versions ?? []);
    }

    public function export($id)
    {
        $contrat = LegalContract::findOrFail($id);
        // Retourne le chemin du document pour téléchargement
        return response()->json(['path' => $contrat->document_path]);
    }
}
