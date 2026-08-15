<?php

namespace App\Http\Controllers\Gel\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DaeDossier;
use Illuminate\Support\Str;

class DaeController extends Controller
{
    /**
     * Liste des dossiers du Guichet Unique (Module DAE)
     */
    public function index(Request $request)
    {
        // En mode multi-tenant, on récupère l'entreprise active via le header/session
        // Ici on suppose que le middleware 'tenant' a injecté l'entreprise dans le request
        $entrepriseId = $request->header('X-Tenant-Id', $request->entreprise_id);

        if (!$entrepriseId) {
            return response()->json(['message' => 'Tenant ID manquant'], 400);
        }

        $dossiers = DaeDossier::where('entreprise_id', $entrepriseId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $dossiers
        ]);
    }

    /**
     * Soumettre un nouveau dossier administratif (CNSS, DGI, Douanes)
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_demarche' => 'required|string|in:DGI_QUITUS,CNSS_IMMATRICULATION,DOUANE_DECLARATION,IFU_CREATION',
            'documents_joints' => 'nullable|array',
            'meta_data' => 'nullable|array',
        ]);

        $entrepriseId = $request->header('X-Tenant-Id', $request->entreprise_id);

        if (!$entrepriseId) {
            return response()->json(['message' => 'Tenant ID manquant'], 400);
        }

        $dossier = DaeDossier::create([
            'entreprise_id' => $entrepriseId,
            'type_demarche' => $request->type_demarche,
            'statut' => 'soumis',
            'documents_joints' => $request->documents_joints ?? [],
            'meta_data' => $request->meta_data ?? [],
            'soumis_le' => now(),
            'reference_institution' => 'PENDING-' . strtoupper(Str::random(6)), // Simulé en attendant le vrai retour
        ]);

        // Ici, on pourrait appeler l'API de l'institution concernée (DGI/CNSS) de manière asynchrone
        // Exemple: dispatch(new SubmitDaeDossierJob($dossier));

        return response()->json([
            'status' => 'success',
            'message' => 'Dossier soumis avec succès au guichet unique.',
            'data' => $dossier
        ], 201);
    }

    /**
     * Vérifier le statut du dossier (Simulation d'appel API de l'institution)
     */
    public function checkStatus($id)
    {
        $dossier = DaeDossier::findOrFail($id);

        if ($dossier->statut === 'valide' || $dossier->statut === 'rejete') {
            return response()->json([
                'status' => 'success',
                'message' => 'Le dossier est déjà clôturé.',
                'data' => $dossier
            ]);
        }

        // Simulation : une chance sur deux que l'administration l'ait validé
        $isApproved = rand(0, 1);
        
        $dossier->statut = $isApproved ? 'valide' : 'en_attente_institution';
        
        if ($isApproved) {
            $dossier->cloture_le = now();
            $dossier->notes_institution = "Dossier vérifié et validé par l'administration.";
            $dossier->reference_institution = strtoupper(explode('_', $dossier->type_demarche)[0]) . '-' . rand(10000, 99999);
        } else {
            $dossier->notes_institution = "Le dossier est toujours en cours de traitement par nos services.";
        }

        $dossier->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Statut mis à jour.',
            'data' => $dossier
        ]);
    }
}
