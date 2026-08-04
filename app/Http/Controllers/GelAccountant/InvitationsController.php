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
        $invitation = ClientInvitation::findOrFail($id);
        
        if ($invitation->email !== auth()->user()->email) {
            abort(403);
        }

        DB::transaction(function() use ($invitation) {
            $user = auth()->user();
            
            // Lier l'utilisateur au client
            DB::table('user_clients')->updateOrInsert(
                ['user_id' => $user->id, 'client_id' => $invitation->client_id],
                [
                    'is_active' => true,
                    'role' => $invitation->role_invite ?? 'comptable',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Associer l'entreprise cliente au cabinet du comptable qui accepte
            if ($user->cabinet_id) {
                $client = Client::find($invitation->client_id);
                if ($client && !$client->cabinet_id) {
                    $client->update(['cabinet_id' => $user->cabinet_id]);
                }
                if (!$invitation->cabinet_id) {
                    $invitation->update(['cabinet_id' => $user->cabinet_id]);
                }
            }

            $invitation->update([
                'statut' => 'acceptee',
                'acceptee_at' => now(),
            ]);

            // Envoi de notification (optionnel pour l'instant)
            // L'utilisateur (administrateur qui a invité) pourrait être notifié ici.
        });

        return back()->with('success', 'Invitation acceptée avec succès.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'motif' => 'required|string|max:1000'
        ]);

        $invitation = ClientInvitation::findOrFail($id);
        
        if ($invitation->email !== auth()->user()->email) {
            abort(403);
        }

        $invitation->update([
            'statut' => 'rejetee',
            'motif_rejet' => $request->motif,
        ]);

        // Envoi de notification (optionnel pour l'instant)
        // L'utilisateur (administrateur qui a invité) pourrait être notifié ici.

        return back()->with('success', 'L\'invitation a été rejetée.');
    }
}
