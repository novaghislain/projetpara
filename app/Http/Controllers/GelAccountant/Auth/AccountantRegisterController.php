<?php

namespace App\Http\Controllers\GelAccountant\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gel\Cabinet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\GelSuperAdmin\SubscriptionPlan;

/**
 * Contrôleur d'inscription autonome pour comptable indépendant (Modèle 3B).
 *
 * Miroir exact de SecretaryRegisterController (Modèle 3A), adapté au profil comptable.
 * Différences clés vs secrétaire :
 * - workspace_type = 'individuel_comptable'
 * - account_context = ['model3_comptable']
 * - Validation que le plan sélectionné est de profile_type = 'comptable_independant'
 * - Le comptable indépendant peut gérer plusieurs clients (via IndependantClientController)
 */
class AccountantRegisterController extends Controller
{
    /**
     * Affiche l'écran de choix (Invitation d'une entreprise vs Autonome).
     */
    public function showChoices()
    {
        return view('gel-accountant.auth.register-choices');
    }

    /**
     * Affiche le formulaire d'inscription autonome pour comptable indépendant.
     */
    public function showAutonomousForm()
    {
        // Récupérer uniquement les plans destinés aux comptables indépendants
        $plans = SubscriptionPlan::forComptablesIndependants()->actifs()->orderBy('price')->get();

        return view('gel-accountant.auth.register-autonomous', compact('plans'));
    }

    /**
     * Traite l'inscription du comptable indépendant.
     */
    public function registerAutonomous(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:utilisateurs,email',
            'password'              => 'required|string|min:8|confirmed',
            'personal_company_name' => 'nullable|string|max:255',
            'specialite'            => 'nullable|string|max:255',
            'terms'                 => 'accepted',
            'plan_id'               => 'nullable|exists:subscription_plans,id',
        ]);

        // Vérification de sécurité : si un plan est soumis, s'assurer qu'il est bien pour comptable indépendant
        if (!empty($validated['plan_id'])) {
            $plan = SubscriptionPlan::find($validated['plan_id']);
            if (!$plan || $plan->profile_type !== 'comptable_independant') {
                return back()->withErrors(['plan_id' => 'Ce forfait n\'est pas disponible pour un comptable indépendant.']);
            }
        }

        return DB::transaction(function () use ($validated) {
            // Créer un cabinet GEL personnel (solo) pour ce comptable indépendant via la table Entreprise
            $nomCabinet = $validated['personal_company_name'] ?? 'Cabinet Solo — ' . $validated['name'];
            
            $entreprise = \App\Models\Entreprise::create([
                'raison_sociale' => $nomCabinet,
                'pays_code'      => 'BJ', // Par défaut
                'secteur_activite' => 'Services Financiers',
                'regime_fiscal'  => 'TPS', // Par défaut
                'modele_usage'   => 'full',
            ]);

            $user = User::create([
                'nom'                   => $validated['name'],
                'email'                 => $validated['email'],
                'mot_de_passe_hash'     => Hash::make($validated['password']),
                'role'                  => 'comptable', // Gardé pour compatibilité éventuelle
                'is_autonomous'         => true,
            ]);

            // Récupérer le rôle accountant
            $role = \App\Models\Role::where('code', 'accountant')->first();

            // Créer l'affectation RBAC
            if ($role) {
                \App\Models\Affectation::create([
                    'utilisateur_id' => $user->id,
                    'entreprise_id'  => $entreprise->id,
                    'role_id'        => $role->id,
                    'modele'         => 'full', // par défaut
                    'statut'         => 'active',
                ]);
            }

            event(new Registered($user));
            Auth::login($user);

            // Définir l'entreprise active en session
            session(['active_entreprise_id' => $entreprise->id]);

            return redirect()->route('gel-accountant.dashboard')
                ->with('success', 'Bienvenue dans votre espace comptable personnel ! Votre essai gratuit de 30 jours commence aujourd\'hui. Aucun paiement requis pour l\'instant.');
        });
    }
}
