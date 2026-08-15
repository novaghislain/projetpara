<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Contrôleur d'inscription publique des utilisateurs.
 *
 * Gère la création d'un nouveau compte utilisateur avec le rôle 'client'
 * et la connexion automatique après inscription.
 */
class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function create(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    /**
     * Enregistre un nouvel utilisateur avec le rôle 'client'.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = null;
        $entreprise = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, &$user, &$entreprise) {
            // 1. Créer l'utilisateur
            $user = User::create([
                'nom'               => $request->name,
                'email'             => $request->email,
                'mot_de_passe_hash' => Hash::make($request->password),
                'role'              => 'client', // Rôle global = client
            ]);

            // 2. Créer l'entreprise (Tenant)
            $entreprise = \App\Models\Entreprise::create([
                'raison_sociale'    => $request->raison_sociale,
                'pays_code'         => 'BJ', // Code pays par défaut (Bénin) pour l'espace OHADA
                'regime_fiscal'     => 'non_defini', // Par défaut
                'modele_usage'      => 'saas',       // Par défaut
                'statut_abonnement' => 'essai', // Abonnement d'essai gratuit par défaut
            ]);

            // 3. Lier l'utilisateur à l'entreprise avec le rôle "company_admin"
            $roleAdmin = \App\Models\Role::where('code', 'company_admin')->first();
            if ($roleAdmin) {
                \App\Models\Affectation::create([
                    'utilisateur_id' => $user->id,
                    'entreprise_id'  => $entreprise->id,
                    'role_id'        => $roleAdmin->id,
                    'modele'         => 'standard', // Par défaut
                    'statut'         => 'active'
                ]);
            }

            // 4. Activer les modules de base (Secrétariat, Comptabilité, RH)
            $modules = ['secretariat', 'comptabilite', 'rh'];
            foreach ($modules as $mod) {
                \App\Models\ModuleEntreprise::create([
                    'entreprise_id' => $entreprise->id,
                    'module_code'   => $mod,
                    'actif'         => true,
                ]);
            }
        });

        event(new Registered($user));
        Auth::login($user);

        // Définir l'entreprise active en session pour qu'il soit directement loggué dessus
        if ($entreprise) {
            session(['active_entreprise_id' => $entreprise->id]);
        }

        // Si le client venait du catalogue (service en session), on le renvoie commander
        if (session('order_service_id')) {
            return redirect()->route('commande.step');
        }

        // Sinon, vers le tableau de bord de l'application (SaaS)
        return redirect()->route('dashboard');
    }
}
