<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated Remplacé par la version SPA accessible via /company/dashboard.
 * Ne plus développer de nouvelles fonctionnalités ici.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $affectations = $user->affectations()->with('entreprise', 'role')->where('statut', 'active')->get();
        
        // Rôles pris en charge pour la redirection automatique
        $supportedRoles = ['company_admin', 'dirigeant', 'secretary', 'accountant'];

        // 1. Si l'utilisateur est un 'company_admin' ou n'a qu'une seule affectation, on le redirige automatiquement
        // MAIS seulement si son rôle est pris en charge (pour éviter une boucle infinie).
        if ($affectations->count() === 1 || $affectations->where('role.code', 'company_admin')->count() > 0) {
            $primaryAff = $affectations->where('role.code', 'company_admin')->first() ?? $affectations->first();
            $roleCode = $primaryAff->role->code ?? '';

            if (in_array($roleCode, $supportedRoles)) {
                session(['active_entreprise_id' => $primaryAff->entreprise_id]);
                return $this->redirectBasedOnRole($roleCode);
            }
        }

        // 2. Si on revient sur le dashboard, on efface l'entreprise active pour forcer la sélection
        session()->forget('active_entreprise_id');
        
        return view('dashboard', [
            'user' => $user,
            'affectations' => $affectations,
            'activeEntrepriseId' => null,
            'activeEntreprise' => null,
        ]);
    }

    public function switchEntreprise(Request $request)
    {
        $request->validate([
            'entreprise_id' => 'required|uuid'
        ]);

        $user = Auth::user();
        $affectation = $user->affectations()
                          ->with('role')
                          ->where('entreprise_id', $request->entreprise_id)
                          ->where('statut', 'active')
                          ->first();

        if ($affectation) {
            session(['active_entreprise_id' => $request->entreprise_id]);
            return $this->redirectBasedOnRole($affectation->role->code ?? '');
        }

        return back()->with('error', 'Accès refusé.');
    }

    private function redirectBasedOnRole($roleCode)
    {
        // On associe un rôle à son module/design par défaut
        switch ($roleCode) {
            case 'company_admin':
            case 'dirigeant':
                return redirect()->route('gel-direction.dashboard');
            case 'secretary':
                return redirect()->route('gel-secretary.dashboard');
            case 'accountant':
                return redirect()->route('gel-accountant.dashboard');
            default:
                // Fallback si pas de rôle précis, on ne veut pas de boucle infinie.
                session()->forget('active_entreprise_id');
                return redirect()->route('dashboard')->with('error', 'Aucun module assigné pour ce rôle : ' . $roleCode);
        }
    }
}
