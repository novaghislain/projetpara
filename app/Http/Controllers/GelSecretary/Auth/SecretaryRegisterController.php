<?php

namespace App\Http\Controllers\GelSecretary\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gel\Client;
use App\Models\Gel\Task;
use App\Models\Dae\DaeAgendaEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecretaryRegisterController extends Controller
{
    /**
     * Affiche l'écran de choix (Invitation vs Autonome).
     */
    public function showChoices()
    {
        return view('gel-secretary.auth.register-choices');
    }

    /**
     * Affiche le formulaire d'inscription autonome.
     */
    public function showAutonomousForm()
    {
        return view('gel-secretary.auth.register-autonomous');
    }

    /**
     * Traite l'inscription du secrétaire autonome.
     */
    public function registerAutonomous(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:utilisateurs,email',
            'password'              => 'required|string|min:8|confirmed',
            'personal_company_name' => 'nullable|string|max:255',
            'personal_industry'     => 'nullable|string|max:255',
            'primary_intent'        => 'required|in:tasks,agenda,contacts,documents',
            'terms'                 => 'accepted'
        ]);

        return DB::transaction(function () use ($validated) {
            // Création de l'entreprise "Cabinet" du secrétaire
            $nomCabinet = $validated['personal_company_name'] ?? 'Secrétariat Indépendant — ' . $validated['name'];
            $entreprise = \App\Models\Entreprise::create([
                'raison_sociale' => $nomCabinet,
                'pays_code'      => 'BJ', // Par défaut
                'secteur_activite' => 'Services Administratifs',
                'regime_fiscal'  => 'TPS', // Par défaut
                'modele_usage'   => 'full',
            ]);

            // Création de l'utilisateur
            $user = User::create([
                'nom'                   => $validated['name'],
                'email'                 => $validated['email'],
                'mot_de_passe_hash'     => Hash::make($validated['password']),
                'role'                  => 'secretaire',
                'is_autonomous'         => true,
            ]);

            // Récupérer le rôle secretary
            $role = \App\Models\Role::where('code', 'secretary')->first();

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

            // Insertion d'un exemple selon l'intention
            $this->seedIntentExample($user, $validated['primary_intent']);

            event(new Registered($user));
            Auth::login($user);

            // Définir l'entreprise active en session
            session(['active_entreprise_id' => $entreprise->id]);

            return redirect()->route('gel-secretary.dashboard')
                ->with('success', 'Bienvenue dans votre espace personnel ! Votre essai de 30 jours commence aujourd\'hui.');
        });
    }

    /**
     * Crée une donnée fictive pédagogique basée sur le premier choix.
     */
    private function seedIntentExample(User $user, string $intent)
    {
        // On ne crée pas de "Client", on l'associe uniquement au user.
        if ($intent === 'tasks') {
            Task::create([
                'titre'       => '🚀 Explorer GEL Secrétariat',
                'description' => 'Bienvenue ! Ceci est votre première tâche d\'exemple. Vous pouvez la marquer comme terminée.',
                'statut'      => 'a_faire',
                'priorite'    => 'moyenne',
                'created_by'  => $user->id,
                // On associe la tâche directement au user via le created_by et sans client_id/cabinet_id
            ]);
        } elseif ($intent === 'agenda') {
            DaeAgendaEvent::create([
                'title'       => '🚀 Rendez-vous de prise en main',
                'description' => 'Explorez votre nouvel agenda personnel autonome.',
                'start_at'  => Carbon::now()->addHours(1),
                'end_at'    => Carbon::now()->addHours(2),
                'type'        => 'autre',
                'created_by'  => $user->id,
            ]);
        }
    }
}


