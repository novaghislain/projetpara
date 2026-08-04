<?php

namespace App\Http\Controllers\GelSecretary\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\ClientInvitation;
use App\Models\UserClient;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $invitations = ClientInvitation::with('client')
            ->where('email', $user->email)
            ->where('statut', 'en_attente')
            ->get();
            
        return view('gel-secretary.invitations.index', compact('invitations'));
    }

    public function accept($id)
    {
        $user = Auth::user();
        $invitation = ClientInvitation::where('email', $user->email)->findOrFail($id);

        if ($invitation->statut !== 'en_attente') {
            return back()->with('error', 'Cette invitation n\'est plus valide.');
        }

        // Add client to user_clients
        UserClient::firstOrCreate([
            'user_id' => $user->id,
            'client_id' => $invitation->client_id,
        ], [
            'role' => $invitation->role_invite ?? 'secretaire',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $invitation->update([
            'statut' => 'acceptee',
            'acceptee_at' => now()
        ]);

        // Log acceptance
        \App\Services\AuditLogService::log('invitation.accepted', $invitation, null, ['statut' => 'acceptee', 'acceptee_at' => now()]);

        // Set as active client if they have none
        if (!$user->active_client_id) {
            $user->update(['active_client_id' => $invitation->client_id]);
            session(['active_client_id' => $invitation->client_id]);
        }

        return redirect()->route('gel-secretary.dashboard')->with('success', 'Invitation acceptée avec succès.');
    }

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $invitation = ClientInvitation::where('email', $user->email)->findOrFail($id);
        $motif = $request->input('motif_rejet');
        $invitation->update([
            'statut' => 'rejetee',
            'motif_rejet' => $motif
        ]);

        // Log rejection
        \App\Services\AuditLogService::log('invitation.rejected', $invitation, null, ['statut' => 'rejetee', 'motif' => $motif]);

        return back()->with('success', 'L\'invitation a été refusée.');
    }
}
