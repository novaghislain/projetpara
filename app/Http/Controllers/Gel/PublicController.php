<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\CompanyRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Contrôleur des fonctionnalités publiques du site.
     * Gère les soumissions de formulaires depuis les pages publiques,
     * notamment les demandes d'inscription des entreprises.
     */

    /**
     * Traite la soumission du formulaire de demande d'inscription entreprise
     * depuis les pages publiques du site. Crée une demande et notifie
     * tous les super-administrateurs.
     *
     * @param Request $request La requête HTTP avec les données du formulaire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDemande(Request $request)
    {
        $validated = $request->validate([
            'company_name'       => 'required|string|max:255',
            'contact_name'       => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'message'            => 'nullable|string',
            'requested_services' => 'nullable|array',
        ]);

        // Création de la demande d'inscription avec statut "en attente"
        $companyRequest = CompanyRequest::create([
            'company_name'       => $validated['company_name'],
            'contact_name'       => $validated['contact_name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'] ?? null,
            'message'            => $validated['message'] ?? null,
            'requested_services' => $validated['requested_services'] ?? null,
            'status'             => 'pending',
        ]);

        // Notification de tous les super-admins de la nouvelle demande
        $superAdmins = User::where('role', 'super_admin')->get();

        foreach ($superAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type'    => 'new_company_request',
                'title'   => 'Nouvelle demande entreprise',
                'message' => "{$companyRequest->company_name} a soumis une demande de services.",
                'data'    => [
                    'request_id'   => $companyRequest->id,
                    'company_name' => $companyRequest->company_name,
                    'contact_name' => $companyRequest->contact_name,
                    'email'        => $companyRequest->email,
                ],
            ]);
        }

        // S17 — Notifier toutes les secrétaires en temps réel (file « Demandes clients »)
        $secretaries = User::all()->filter(fn ($u) => $u->isSecretaire());
        foreach ($secretaries as $secretary) {
            $secretary->notify(new \App\Notifications\RealTimeNotification(
                'Nouvelle demande client',
                $companyRequest->company_name . ' a soumis une demande (' . $companyRequest->contact_name . ')',
                route('gel-secretary.requests.index'),
                'fas fa-inbox'
            ));
        }

        return redirect('/')->with('success', 'Merci ! Nous vous recontacterons dans les plus brefs delais.');
    }
}
