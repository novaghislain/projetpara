<?php

namespace App\Http\Controllers;

use App\Models\UserClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySwitcherController extends Controller
{
    /**
     * Affiche la page de sélection du contexte entreprise.
     */
    public function showSelector()
    {
        $user = Auth::user();

        $companies = UserClient::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('client')
            ->get()
            ->map(function ($uc) {
                return $uc->client;
            })
            ->filter();

        if ($companies->isEmpty()) {
            // Éviter la boucle infinie : si l'utilisateur est company_admin,
            // le middleware CheckCompanyAccess (GEL) le renverrait vers company.dashboard.
            // On le redirige vers l'accueil avec un message d'erreur clair.
            if ($user->isCompanyAdmin() || $user->isCompanyManager()) {
                Auth::logout();
                return redirect()->route('home')
                    ->withErrors(['Votre compte entreprise n\'est pas correctement configuré. Contactez l\'administrateur.']);
            }
            return redirect()->route('dashboard')
                ->withErrors(['Aucune entreprise associée à votre compte.']);
        }

        // Si une seule entreprise, sélectionner directement
        if ($companies->count() === 1) {
            $user->switchToClient($companies->first()->id);
            $redirect = $this->getRedirectForRole($user);
            return redirect()->route($redirect);
        }

        return view('app', [
            'page' => 'select-context',
            'props' => [
                'companies' => $companies->values()->toArray(),
            ],
        ]);
    }

    /**
     * Sélectionner un contexte d'entreprise.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        $user = Auth::user();
        $clientId = (int) $request->client_id;

        $success = $user->switchToClient($clientId);

        if (!$success) {
            return back()->withErrors(['Cette entreprise ne vous est pas accessible.']);
        }

        $redirect = $this->getRedirectForRole($user);
        return redirect()->route($redirect)->with('success', 'Contexte basculé vers l\'entreprise sélectionnée.');
    }

    /**
     * Détermine la route de redirection selon le rôle.
     */
    private function getRedirectForRole($user): string
    {
        if ($user->isSuperAdmin()) return 'dashboard';
        if ($user->isComptable()) return 'cpa.dashboard';
        if ($user->isCompanyAdmin() || $user->isCompanyManager()) return 'company.dashboard';
        if ($user->roleModel?->slug === 'company_employee') return 'company.dashboard';
        return 'dashboard';
    }
}
