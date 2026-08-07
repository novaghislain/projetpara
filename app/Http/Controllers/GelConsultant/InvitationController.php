<?php

namespace App\Http\Controllers\GelConsultant;

use App\Http\Controllers\Controller;
use App\Models\Gel\ConsultantMission;
use App\Models\Gel\ConsultantInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    public function showAccept(string $token)
    {
        $invitation = ConsultantInvitation::with('mission.entreprise')
            ->where('token', $token)
            ->firstOrFail();

        // Vérifier que le token n'est pas expiré
        if ($invitation->expires_at->isPast()) {
            return view('gel-consultant.invitation-expired');
        }

        $userExists = User::where('email', $invitation->email)->exists();

        return view('gel-consultant.invitation-accept', compact('invitation', 'userExists'));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = ConsultantInvitation::where('token', $token)->firstOrFail();

        if ($invitation->expires_at->isPast()) {
            return redirect()->back()->withErrors(['token' => 'Ce lien d\'invitation a expiré.']);
        }

        $mission = $invitation->mission;
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            // Créer le compte consultant
            $request->validate([
                'name'                  => 'required|string|max:255',
                'password'              => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name'         => $request->name,
                'email'        => $invitation->email,
                'password'     => Hash::make($request->password),
                'account_type' => 'consultant',
                'role'         => 'consultant',
                'is_active'    => true,
            ]);
        }

        // Lier le consultant à la mission
        $mission->update([
            'consultant_id' => $user->id,
            'status'        => 'en_cours',
        ]);

        // Supprimer l'invitation (usage unique)
        $invitation->delete();

        // Connecter le consultant directement
        auth()->login($user);

        return redirect()->route('gel-consultant.dashboard')
            ->with('success', 'Bienvenue ! Votre accès au dossier "' . $mission->title . '" est maintenant actif jusqu\'au ' . $mission->end_date->format('d/m/Y') . '.');
    }
}
