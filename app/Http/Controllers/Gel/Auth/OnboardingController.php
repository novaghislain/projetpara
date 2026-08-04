<?php

namespace App\Http\Controllers\Gel\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client as LegacyClient;
use App\Models\Gel\Entreprise;
use App\Models\Gel\Cabinet;
use App\Models\Gel\Client as GelClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur du processus d'onboarding (parcours de bienvenue).
 * Gère la complétion du profil après la première connexion, selon
 * le type de compte : entreprise, cabinet comptable ou particulier.
 * Crée les entités nécessaires (Entreprise, Cabinet, GelClient, LegacyClient)
 * et établit les relations entre l'utilisateur et ces entités.
 */
class OnboardingController extends Controller
{
    /**
     * Affiche le formulaire de completion de profil après inscription.
     * Utilise un token d'onboarding pour identifier l'utilisateur.
     *
     * @param Request $request La requête HTTP
     * @param string $token Le token d'onboarding unique de l'utilisateur
     * @return \Illuminate\View\View
     */
    public function showProfilForm(Request $request, string $token)
    {
        $user = User::where('onboarding_token', $token)->firstOrFail();

        return view('gel.onboarding.profil', [
            'user'             => $user,
            'account_type'     => $user->account_type,
            'token'            => $token,
            'wants_accounting' => $user->wants_accounting,
            'wants_secretary'  => $user->wants_secretary,
        ]);
    }

    /**
     * Traite la completion de profil (étape 2).
     * Redirige vers la méthode appropriée selon le type de compte.
     *
     * @param Request $request La requête HTTP contenant les données du profil
     * @param string $token Le token d'onboarding unique de l'utilisateur
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeProfil(Request $request, string $token)
    {
        $user = User::where('onboarding_token', $token)->firstOrFail();

        if ($user->account_type === 'entreprise') {
            return $this->completeEntrepriseProfil($request, $user);
        }

        if ($user->account_type === 'cabinet') {
            return $this->completeCabinetProfil($request, $user);
        }

        return back()->withErrors(['account_type' => 'Type de compte invalide.']);
    }

    /**
     * Complète le profil pour un compte entreprise.
     * Crée l'entreprise, le gel_client, le legacy_client et lie l'utilisateur
     * à toutes ces entités dans une seule transaction.
     *
     * @param Request $request La requête HTTP avec les données entreprise
     * @param User $user L'utilisateur dont le profil est complété
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function completeEntrepriseProfil(Request $request, User $user)
    {
        $validated = $request->validate([
            'nom'             => 'required|string|max:255',
            'ifu'             => 'nullable|string|max:50',
            'rc'              => 'nullable|string|max:50',
            'telephone'       => 'nullable|string|max:30',
            'adresse'         => 'nullable|string',
            'ville'           => 'nullable|string|max:100',
            'secteur'         => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'wants_accounting' => 'nullable|boolean',
            'wants_secretary'  => 'nullable|boolean',
        ]);

        // Une entreprise doit choisir au moins un espace (comptabilité ou secrétariat)
        if (!$request->boolean('wants_accounting') && !$request->boolean('wants_secretary')) {
            return back()->withErrors([
                'wants_accounting' => 'Choisissez au moins un espace : Comptabilité ou Secrétariat.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $request, $user) {
            // 1. Entreprise (gel_entreprises) — réutiliser si déjà liée
            $entreprise = $user->entreprise_id ? Entreprise::find($user->entreprise_id) : null;
            $entrepriseData = [
                'nom'       => $validated['nom'],
                'ifu'       => $validated['ifu'] ?? null,
                'rc'        => $validated['rc'] ?? null,
                'telephone' => $validated['telephone'] ?? null,
                'adresse'   => $validated['adresse'] ?? null,
                'ville'     => $validated['ville'] ?? null,
                'secteur'   => $validated['secteur'] ?? null,
                'email'     => $validated['email'] ?? $user->email,
            ];
            if ($entreprise) {
                $entreprise->update($entrepriseData);
            } else {
                $entreprise = Entreprise::create($entrepriseData);
            }

            // Lier l'utilisateur à son entreprise
            $user->entreprise_id = $entreprise->id;

            // 2. Client comptable (gel_clients) — réutiliser si déjà présent
            $gelClient = $user->entreprise_id
                ? GelClient::where('email', $validated['email'] ?? $user->email)->orWhere('nom_entreprise', $validated['nom'])->first()
                : null;
            $gelClientData = [
                'cabinet_id'    => $user->cabinet_id, // nullable (entreprise auto-inscrite sans cabinet)
                'nom_entreprise' => $validated['nom'],
                'email'         => $validated['email'] ?? $user->email,
                'telephone'     => $validated['telephone'] ?? null,
                'adresse'       => $validated['adresse'] ?? null,
                'ville'         => $validated['ville'] ?? null,
                'ifu'           => $validated['ifu'] ?? null,
                'rc'            => $validated['rc'] ?? null,
                'secteur'       => $validated['secteur'] ?? null,
                'statut'        => 'actif',
            ];
            if ($gelClient) {
                $gelClient->update($gelClientData);
            } else {
                GelClient::create($gelClientData);
            }

            // 3. Client legacy (clients) — réutiliser si déjà lié, sinon créer
            $legacyClient = $user->client_id ? LegacyClient::find($user->client_id) : null;
            $legacyData = [
                'company_name'     => $validated['nom'],
                'email'            => $validated['email'] ?? $user->email,
                'phone'            => $validated['telephone'] ?? null,
                'address'          => $validated['adresse'] ?? null,
                'city'             => $validated['ville'] ?? null,
                'ifu'              => $validated['ifu'] ?? null,
                'rccm'             => $validated['rc'] ?? null,
                'secteur'          => $validated['secteur'] ?? null,
                'status'           => 'actif',
                'wants_accounting' => $request->boolean('wants_accounting'),
                'wants_secretary'  => $request->boolean('wants_secretary'),
            ];
            if ($legacyClient) {
                $legacyClient->update($legacyData);
            } else {
                $legacyClient = LegacyClient::create($legacyData);
            }

            // Lier l'utilisateur aux deux clients pour compatibilité
            $user->client_id = $legacyClient->id;
            $user->active_client_id = $legacyClient->id;

            // Lier le user au client dans la table pivot (user_clients) — anti-doublon
            \DB::table('user_clients')->updateOrInsert(
                ['user_id' => $user->id, 'client_id' => $legacyClient->id],
                [
                    'is_active'  => true,
                    'role'       => 'company_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Mémoriser le choix des espaces et terminer l'onboarding
            $user->wants_accounting = $request->boolean('wants_accounting');
            $user->wants_secretary  = $request->boolean('wants_secretary');
            $user->onboarding_completed = true;
            $user->onboarding_token = null;
            $user->save();
        });

        return redirect('/gel-business/dashboard')
            ->with('success', 'Bienvenue ! Votre entreprise a été créée avec succès.');
    }

    /**
     * Complète le profil pour un compte cabinet comptable.
     * Crée le cabinet et lie l'utilisateur dans une transaction.
     *
     * @param Request $request La requête HTTP avec les données du cabinet
     * @param User $user L'utilisateur dont le profil est complété
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function completeCabinetProfil(Request $request, User $user)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'adresse'   => 'nullable|string',
            'ville'     => 'nullable|string|max:100',
            'ifu'       => 'nullable|string|max:50',
            'rc'        => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['nom']);
            $cabinet = Cabinet::create($validated);

            // Lier l'utilisateur au cabinet et marquer l'onboarding comme terminé
            $user->cabinet_id = $cabinet->id;
            $user->onboarding_completed = true;
            $user->onboarding_token = null;
            $user->save();
        });

        return redirect('/gel-accountant/dashboard')
            ->with('success', 'Bienvenue ! Votre cabinet comptable a été créé avec succès.');
    }
}
