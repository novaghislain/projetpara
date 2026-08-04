<?php

namespace App\Http\Controllers\Gel\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client as LegacyClient;
use App\Models\Gel\Entreprise;
use App\Models\Gel\Cabinet;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Contrôleur d'inscription pour la plateforme GEL.
 * Gère l'inscription des utilisateurs selon trois profils :
 * entreprise, cabinet comptable ou particulier.
 * Crée les entités associées (Entreprise, Cabinet, LegacyClient)
 * et connecte automatiquement l'utilisateur après inscription.
 */
class RegisterController extends Controller
{
    /**
     * Affiche le formulaire d'inscription avec choix du type de compte.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('gel.auth.register');
    }

    /**
     * Traite l'inscription simplifiée.
     * Les détails complexes seront demandés lors de l'onboarding.
     *
     * @param Request $request La requête HTTP
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'type'     => 'required|in:entreprise,cabinet,particulier',
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'terms'    => 'accepted',
            'wants_accounting' => 'nullable|boolean',
            'wants_secretary'  => 'nullable|boolean',
        ]);

        // Une entreprise doit choisir au moins un espace (comptabilité ou secrétariat)
        if ($validated['type'] === 'entreprise'
            && !($request->boolean('wants_accounting'))
            && !($request->boolean('wants_secretary'))) {
            return back()->withErrors([
                'wants_accounting' => 'Choisissez au moins un espace : Comptabilité ou Secrétariat.',
            ])->withInput();
        }

        return DB::transaction(function () use ($validated, $request) {
            $type = $validated['type'];

            // Les particuliers n'ont pas d'onboarding complexe
            $isOnboardingCompleted = ($type === 'particulier');
            $role = 'client';
            if ($type === 'entreprise') $role = 'company_admin';
            if ($type === 'cabinet') $role = 'comptable';

            $user = User::create([
                'name'                 => $validated['name'],
                'email'                => $validated['email'],
                'password'             => Hash::make($validated['password']),
                'account_type'         => $type,
                'onboarding_completed' => $isOnboardingCompleted,
                'onboarding_token'     => !$isOnboardingCompleted ? Str::random(40) : null,
                'role'                 => $role,
                'is_active'            => true,
                'wants_accounting'     => $type === 'entreprise' ? $request->boolean('wants_accounting') : null,
                'wants_secretary'      => $type === 'entreprise' ? $request->boolean('wants_secretary') : null,
            ]);

            // Pour un particulier, on crée le client legacy immédiatement
            if ($type === 'particulier') {
                $legacyClient = LegacyClient::create([
                    'company_name' => $validated['name'],
                    'email'        => $validated['email'],
                    'status'       => 'actif',
                ]);

                $user->update([
                    'client_id'        => $legacyClient->id,
                    'active_client_id' => $legacyClient->id,
                ]);

                DB::table('user_clients')->insert([
                    'user_id'    => $user->id,
                    'client_id'  => $legacyClient->id,
                    'is_active'  => true,
                    'role'       => 'client',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            event(new Registered($user));
            Auth::login($user);

            // Redirection vers le dashboard
            // Le middleware CheckOnboarding prendra le relai pour entreprise/cabinet
            if ($type === 'entreprise') {
                return redirect('/gel-business/dashboard')
                    ->with('success', 'Bienvenue sur ComptaSaaS !');
            }
            
            return redirect('/gel-accountant/dashboard')
                ->with('success', 'Bienvenue sur ComptaSaaS !');
        });
    }
}
