<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\ClientInvitation;
use Illuminate\Support\Facades\DB;
use App\Models\Gel\Client;

class InvitationsController extends Controller
{
    public function accept(Request $request, $id)
    {
        $invitation = \App\Models\EntrepriseInvitation::findOrFail($id);
        
        if ($invitation->email !== auth()->user()->email) {
            abort(403);
        }

        DB::transaction(function() use ($invitation) {
            $user = auth()->user();
            
            // Assigner le rôle (recherche par code: accountant pour comptable, secretary pour secretaire)
            $roleCode = $invitation->role_invite === 'comptable' ? 'accountant' : 'secretary';
            $role = \App\Models\Role::where('code', $roleCode)->first();

            // Créer l'affectation
            \App\Models\Affectation::updateOrCreate(
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

            $invitation->update([
                'statut' => 'acceptee',
                'acceptee_at' => now(),
            ]);
        });

        return back()->with('success', 'Invitation acceptée avec succès.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'motif' => 'required|string|max:1000'
        ]);

        $invitation = \App\Models\EntrepriseInvitation::findOrFail($id);
        
        if ($invitation->email !== auth()->user()->email) {
            abort(403);
        }

        // On peut ajouter un champ motif_rejet si on l'ajoute à la migration, sinon on met juste en 'rejetee'
        $invitation->update([
            'statut' => 'rejetee',
            // 'motif_rejet' => $request->motif, // Commented out as it's not in the migration by default
        ]);

        return back()->with('success', 'L\'invitation a été rejetée.');
    }
}
