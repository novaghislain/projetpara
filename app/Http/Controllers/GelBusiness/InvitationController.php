<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Gel\ClientInvitation;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Notifications\RealTimeNotification;
use App\Models\User;
/**
 * Contrôleur de gestion des invitations de comptables.
 *
 * Permet à une entreprise (client) d'inviter un cabinet comptable
 * à rejoindre sa structure via un lien d'invitation par email.
 * Les invitations ont une durée de validité limitée et un suivi de statut.
 */
class InvitationController extends Controller
{
    /**
     * Affiche la liste des invitations envoyées par l'entreprise.
     *
     * Récupère toutes les invitations liées au client connecté,
     * triées de la plus récente à la plus ancienne.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Déterminer l'ID client à partir de l'utilisateur connecté
        $clientId = $user->client_id ?? $user->active_client_id;

        // Récupérer les invitations uniquement si un client est associé
        $invitations = collect();
        if ($clientId) {
            $invitations = ClientInvitation::where('client_id', $clientId)
                ->latest()
                ->get();
        }

        return view('gel-business.inviter-comptable', compact('invitations') + ['currentSection' => 'inviter']);
    }

    /**
     * Envoie une nouvelle invitation à un cabinet comptable.
     *
     * Valide l'email et le message facultatif, crée l'invitation avec
     * un token unique et une expiration à 7 jours, puis redirige
     * vers la liste des invitations.
     *
     * @param  \Illuminate\Http\Request  $request  Contient 'email' et optionnellement 'message'
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Déterminer l'ID client à partir de l'utilisateur connecté
        $clientId = $user->client_id ?? $user->active_client_id;

        // Vérifier que l'utilisateur est bien rattaché à une entreprise
        if (!$clientId) {
            return back()->withErrors(['error' => 'Vous devez être rattaché à une entreprise.']);
        }

        // Validation des champs du formulaire
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'message' => 'nullable|string|max:1000',
            'role_invite' => 'required|string|in:comptable,secretaire',
        ]);

        // Récupérer le client pour obtenir le cabinet associé
        $client = Client::find($clientId);

        // Création de l'invitation avec un token aléatoire et une expiration à 7 jours
        $invitation = ClientInvitation::create([
            'cabinet_id' => $client?->cabinet_id,
            'client_id' => $clientId,
            'email' => $validated['email'],
            'token' => Str::random(40),
            'statut' => 'en_attente',
            'expire_at' => now()->addDays(7),
            'message' => $validated['message'] ?? null,
            'role_invite' => $validated['role_invite'],
            'invited_by_user_id' => Auth::id(),
        ]);

        // Envoyer la notification par email au collaborateur invité
        Mail::to($validated['email'])->send(new InvitationMail($invitation));

        // Si l'utilisateur existe déjà, on lui envoie une notification temps réel
        $invitedUser = User::where('email', $validated['email'])->first();
        if ($invitedUser) {
            $invitedUser->notify(new RealTimeNotification(
                'Nouvelle invitation',
                ($client->nom_entreprise ?? 'Une entreprise') . ' souhaite vous ajouter.',
                route('gel-secretary.invitations.index'),
                'fas fa-envelope-open-text text-warning'
            ));
        }

        return redirect()->route('gel-business.inviter-comptable')
            ->with('success', 'Invitation envoyée à ' . $validated['email'] . '.');
    }
}
