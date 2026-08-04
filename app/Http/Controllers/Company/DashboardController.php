<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\License;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du tableau de bord Company.
 *
 * Gère les pages principales de l'interface entreprise :
 * tableau de bord, services, profil entreprise, et les API
 * de consultation/mise à jour des informations de l'entreprise.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord principal de l'entreprise (vue SPA).
     *
     * Vérifie que l'utilisateur a une entreprise associée.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        return view('company', [
            'page' => 'company-dashboard',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * Affiche la page des services souscrits (vue SPA).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function services()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        return view('company', [
            'page' => 'company-services',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * Affiche la page de profil entreprise (vue SPA).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function profile()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        return view('company', [
            'page' => 'company-profile',
            'clientId' => $user->client_id,
        ]);
    }

    // ── API ──

    /**
     * Vérifie que l'utilisateur authentifié a bien accès à ce client.
     */
    /**
     * Vérifie que l'utilisateur authentifié a bien accès à ce client.
     *
     * @param int $clientId Identifiant du client à vérifier
     * @return void
     */
    private function authorizeClientAccess($clientId): void
    {
        $user = Auth::user();
        if ((int) $user->client_id !== (int) $clientId) {
            abort(403, 'Vous n\'avez pas accès aux données de cette entreprise.');
        }
    }

    /**
     * API: Retourne les informations de l'entreprise, licences et statistiques.
     *
     * @param int $clientId Identifiant du client
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyInfo($clientId)
    {
        $this->authorizeClientAccess($clientId);
        $client = Client::with(['services', 'activeLicenses', 'activeLicenses.service'])->findOrFail($clientId);

        $licenses = $client->activeLicenses()->with('service')->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'license_key' => $l->license_key,
                'service_name' => $l->service->name ?? 'N/A',
                'service_id' => $l->service_id,
                'duration_months' => $l->duration_months,
                'start_date' => $l->start_date?->format('d/m/Y'),
                'end_date' => $l->end_date?->format('d/m/Y'),
                'status' => $l->status,
                'valid' => $l->isValid(),
            ];
        });

        // Statistiques pour le dashboard
        $userCount = User::where('client_id', $clientId)->count();
        $activeUserCount = User::where('client_id', $clientId)->where('is_active', true)->count();

        return response()->json([
            'company' => $client,
            'licenses' => $licenses,
            'stats' => [
                'user_count' => $userCount,
                'active_user_count' => $activeUserCount,
            ],
        ]);
    }

    /**
     * API: Met à jour les informations de l'entreprise.
     *
     * @param Request $request Requête HTTP (company_name, email, phone, address)
     * @param int $clientId Identifiant du client
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCompany(Request $request, $clientId)
    {
        $this->authorizeClientAccess($clientId);
        $client = Client::findOrFail($clientId);
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);
        $client->update($data);
        return response()->json(['message' => 'Informations mises à jour.', 'company' => $client]);
    }
}
