<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EntrepriseInvitation;
use App\Models\Affectation;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EntrepriseInvitationAcceptController extends Controller
{
    public function index($token)
    {
        $invitation = EntrepriseInvitation::where('token', $token)->firstOrFail();

        if ($invitation->statut !== 'en_attente' || $invitation->estExpiree()) {
            return redirect('/')->with('error', 'Cette invitation est expirée ou a déjà été traitée.');
        }

        return view('auth.entreprise-invitation-accept', compact('invitation'));
    }

    public function accept(Request $request, $token)
    {
        $invitation = EntrepriseInvitation::where('token', $token)->firstOrFail();

        if ($invitation->statut !== 'en_attente' || $invitation->estExpiree()) {
            return redirect('/')->with('error', 'Cette invitation est expirée ou a déjà été traitée.');
        }

        // L'utilisateur doit être connecté pour accepter (s'il vient de créer un compte, il est connecté)
        if (!Auth::check()) {
            // On peut rediriger vers le login en passant le token, 
            // mais l'UI d'acceptation de la page `index` devrait forcer l'inscription/connexion d'abord.
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour accepter l\'invitation.');
        }

        $user = Auth::user();

        DB::transaction(function() use ($invitation, $user) {
            // Assigner le rôle (recherche par code: accountant pour comptable, secretary pour secretaire)
            $roleCode = $invitation->role_invite === 'comptable' ? 'accountant' : 'secretary';
            $role = Role::where('code', $roleCode)->first();

            // Créer l'affectation
            Affectation::updateOrCreate(
                [
                    'utilisateur_id' => $user->id,
                    'entreprise_id' => $invitation->entreprise_id,
                ],
                [
                    'role_id' => $role ? $role->id : null,
                    'modele' => $roleCode, // 'accountant' ou 'secretary'
                    'statut' => 'active',
                ]
            );

            // Mettre à jour l'invitation
            $invitation->update([
                'statut' => 'acceptee',
                'acceptee_at' => now(),
            ]);
            
            // Activer le contexte pour cette entreprise
            session(['active_entreprise_id' => $invitation->entreprise_id]);
        });

        return redirect()->route('dashboard')->with('success', 'Invitation acceptée. Vous avez maintenant accès à cet espace.');
    }
}
