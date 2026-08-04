<?php

namespace App\Http\Controllers\Gel\Auth;

use App\Http\Controllers\Controller;
use App\Models\Gel\ClientInvitation;
use App\Models\Gel\Cabinet;
use App\Models\Gel\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Contrôleur d'acceptation des invitations pour les comptables.
 * Gère le processus par lequel un comptable invité accepte de rejoindre
 * un client (entreprise) : validation du token, création de compte si nécessaire,
 * et liaison entre l'utilisateur et le client.
 */
class InvitationAcceptController extends Controller
{
    /**
     * Affiche la page d'acceptation d'invitation.
     * Récupère l'invitation par son token et affiche les détails.
     *
     * @param Request $request La requête HTTP
     * @param string $token Le token unique d'invitation
     * @return \Illuminate\View\View
     */
    public function showAcceptForm(Request $request, string $token)
    {
        $invitation = ClientInvitation::where('token', $token)
            ->with(['client', 'cabinet'])
            ->firstOrFail();

        return view('gel-accountant.invitation-accept-form', compact('invitation'));
    }

    /**
     * Traite l'acceptation d'invitation par un comptable.
     * Gère deux cas : utilisateur déjà connecté (lien direct) ou
     * non connecté (création de compte + lien).
     *
     * @param Request $request La requête HTTP (POST pour soumission, GET pour affichage)
     * @param string $token Le token unique d'invitation
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function accept(Request $request, string $token)
    {
        $invitation = ClientInvitation::where('token', $token)
            ->with(['client', 'cabinet'])
            ->firstOrFail();

        // Vérifier si l'invitation a expiré
        if ($invitation->estExpiree()) {
            return back()->withErrors(['error' => 'Cette invitation a expiré.']);
        }

        // Vérifier si l'invitation a déjà été acceptée
        if ($invitation->estAcceptee()) {
            return back()->withErrors(['error' => 'Cette invitation a déjà été acceptée.']);
        }

        // Cas 1 : l'utilisateur est déjà connecté -> lien direct sans création de compte
        if (Auth::check()) {
            $user = Auth::user();
            $redirectRoute = $this->redirectPathFor($user, $invitation);
            $this->linkUserToClient($user, $invitation);
            return redirect($redirectRoute)
                ->with('success', 'Vous êtes maintenant associé à cette entreprise.');
        }

        // Cas 2 : utilisateur non connecté -> soumission du formulaire de création
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'prenom'   => 'nullable|string|max:255',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Création du compte utilisateur et lien dans une transaction
            $user = DB::transaction(function () use ($validated, $invitation) {
                // Déterminer le rôle et le type de compte à partir de l'invitation.
                // account_type est une ENUM (client/internal/super_admin/particulier/
                // entreprise/cabinet) : une secrétaire rattachée à une entreprise
                // utilise 'client' (même convention que les secrétaires existantes).
                $role = $invitation->role_invite ?? 'comptable';
                $accountType = $role === 'secretaire' ? 'client' : 'cabinet';

                // Créer le compte avec les informations de l'invitation
                $user = User::create([
                    'name'     => $validated['name'],
                    'prenom'   => $validated['prenom'] ?? null,
                    'email'    => $invitation->email,
                    'password' => Hash::make($validated['password']),
                    'account_type' => $accountType,
                    'role'     => $role,
                    'cabinet_id' => $invitation->cabinet_id,
                    'client_id' => $invitation->client_id,
                    'active_client_id' => $invitation->client_id,
                    'email_verified_at' => now(),
                    'onboarding_completed' => true,
                    'is_active'=> true,
                ]);

                $this->linkUserToClient($user, $invitation);
                return $user;
            });

            Auth::login($user);

            $redirectRoute = $this->redirectPathFor($user, $invitation);

            return redirect($redirectRoute)
                ->with('success', 'Compte créé et invitation acceptée ! Bienvenue.');
        }

        // Cas 3 : GET -> afficher le formulaire d'acceptation avec création de compte
        return view('gel-accountant.invitation-accept-form', compact('invitation'));
    }

    /**
     * Détermine la route de redirection après acceptation selon le rôle.
     *
     * Une secrétaire retourne dans son espace dédié ; un comptable invité
     * pour une entreprise (client_id renseigné) atterrit sur l'espace
     * entreprise pour manipuler les données de la société ; un comptable
     * invité sans client rattaché (rejoindre un cabinet) atterrit sur le
     * dashboard du cabinet.
     *
     * @param User $user
     * @param ClientInvitation $invitation
     * @return string
     */
    protected function redirectPathFor(User $user, ClientInvitation $invitation): string
    {
        if ($user->isSecretaire()) {
            return '/gel-secretary/dashboard';
        }
        if ($user->client_id || $invitation->client_id) {
            return '/gel-business/dashboard';
        }
        return '/gel-accountant/dashboard';
    }

    /**
     * Lie un utilisateur comptable à un client (entreprise).
     * Crée l'association dans la table pivot user_clients et
     * marque l'invitation comme acceptée.
     *
     * @param User $user L'utilisateur comptable à lier
     * @param ClientInvitation $invitation L'invitation à accepter
     * @return void
     */
    protected function linkUserToClient(User $user, ClientInvitation $invitation)
    {
        // Déterminer le rôle effectif du collaborateur
        $role = $invitation->role_invite ?? ($user->isSecretaire() ? 'secretaire' : 'comptable');

        DB::table('user_clients')->updateOrInsert(
            ['user_id' => $user->id, 'client_id' => $invitation->client_id],
            [
                'is_active' => true,
                'role' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Rattacher l'utilisateur au client (si le rôle a changé après acceptation)
        $user->client_id = $user->client_id ?? $invitation->client_id;
        $user->active_client_id = $user->active_client_id ?? $invitation->client_id;
        if ($user->isDirty(['client_id', 'active_client_id'])) {
            $user->save();
        }

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
    }
}
