<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\CompanyRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion des demandes d'inscription entreprise.
 * Permet de lister, consulter, mettre à jour le statut et supprimer
 * les demandes de création d'entreprise soumises via le formulaire public.
 * Notifie les super admins lors de la validation d'une demande.
 */
class CompanyRequestController extends Controller
{
    /**
     * Affiche la page de gestion des demandes entreprises.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'gel-requests']);
    }

    /**
     * API : Retourne toutes les demandes entreprises, de la plus récente à la plus ancienne.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAll()
    {
        $requests = CompanyRequest::latest()->get();

        return response()->json($requests);
    }

    /**
     * API : Retourne une demande entreprise spécifique.
     *
     * @param int $id L'identifiant de la demande
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $request = CompanyRequest::findOrFail($id);

        return response()->json($request);
    }

    /**
     * API : Met à jour le statut d'une demande entreprise.
     * Si le statut passe à 'validated', crée une notification pour tous les super admins.
     *
     * @param Request $request La requête HTTP avec status et admin_notes optionnels
     * @param int $id L'identifiant de la demande
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status'      => 'required|in:pending,contacted,validated,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $companyRequest = CompanyRequest::findOrFail($id);
        $companyRequest->update($validated);

        // Si le statut passe à 'validated', notifier tous les super admins
        if ($validated['status'] === 'validated') {
            $superAdmins = User::where('role', 'super_admin')->get();

            foreach ($superAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type'    => 'company_request_validated',
                    'title'   => 'Demande entreprise validee',
                    'message' => "La demande de {$companyRequest->company_name} a ete validee.",
                    'data'    => [
                        'request_id' => $companyRequest->id,
                        'company_name' => $companyRequest->company_name,
                    ],
                ]);
            }
        }

        return response()->json($companyRequest);
    }

    /**
     * API : Supprime une demande entreprise.
     *
     * @param int $id L'identifiant de la demande à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $companyRequest = CompanyRequest::findOrFail($id);
        $companyRequest->delete();

        return response()->json(['message' => 'Demande entreprise supprimee avec succes.'], 200);
    }
}
