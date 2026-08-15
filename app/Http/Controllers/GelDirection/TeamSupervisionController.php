<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EntrepriseInvitation;
use App\Models\Affectation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamSupervisionController extends Controller
{
    public function index()
    {
        $entrepriseId = session('active_entreprise_id');

        // Récupérer les affectations actives (l'équipe)
        $team = Affectation::where('entreprise_id', $entrepriseId)
            ->whereIn('statut', ['actif', 'active'])
            ->with('utilisateur', 'role')
            ->get();

        // Récupérer les invitations en attente
        $invitations = EntrepriseInvitation::where('entreprise_id', $entrepriseId)
            ->where('statut', 'en_attente')
            ->get();

        return view('gel-direction.team.index', compact('team', 'invitations'));
    }
    
    public function invite(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role_invite' => 'required|in:comptable,secretaire',
        ]);

        $entrepriseId = session('active_entreprise_id');

        // Vérifier si une invitation en attente existe déjà
        $exists = EntrepriseInvitation::where('entreprise_id', $entrepriseId)
            ->where('email', $request->email)
            ->where('statut', 'en_attente')
            ->exists();

        if ($exists) {
            return back()->with('error', 'Une invitation est déjà en attente pour cet email.');
        }

        $invitation = EntrepriseInvitation::create([
            'entreprise_id' => $entrepriseId,
            'invited_by_user_id' => Auth::id(),
            'email' => $request->email,
            'role_invite' => $request->role_invite,
        ]);

        // URL d'acceptation
        $acceptUrl = route('invitation.entreprise.accept', ['token' => $invitation->token]);

        // Simuler l'envoi d'e-mail pour le moment
        // Mail::to($invitation->email)->send(new \App\Mail\EntrepriseInvitationMail($invitation, $acceptUrl));
        \Log::info("Invitation link: " . $acceptUrl);

        return back()->with('success', 'Invitation envoyée avec succès à ' . $request->email);
    }

    public function cancelInvitation($id)
    {
        $entrepriseId = session('active_entreprise_id');
        $invitation = EntrepriseInvitation::where('entreprise_id', $entrepriseId)
            ->findOrFail($id);
            
        $invitation->update(['statut' => 'rejetee']);
        
        return back()->with('success', 'Invitation annulée.');
    }

    public function show($user)
    {
        return view('gel-direction.team.show', compact('user'));
    }
}
