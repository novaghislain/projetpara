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
     * Traite la soumission du formulaire de demande entreprise (page publique).
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

        $companyRequest = CompanyRequest::create([
            'company_name'       => $validated['company_name'],
            'contact_name'       => $validated['contact_name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'] ?? null,
            'message'            => $validated['message'] ?? null,
            'requested_services' => $validated['requested_services'] ?? null,
            'status'             => 'pending',
        ]);

        // Créer une notification pour tous les super admins
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

        return redirect('/')->with('success', 'Merci ! Nous vous recontacterons dans les plus brefs delais.');
    }
}
