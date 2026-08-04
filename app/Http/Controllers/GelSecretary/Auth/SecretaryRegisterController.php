<?php

namespace App\Http\Controllers\GelSecretary\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
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
            'email'                 => 'required|string|email|max:255|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'personal_company_name' => 'nullable|string|max:255',
            'personal_industry'     => 'nullable|string|max:255',
            'primary_intent'        => 'required|in:tasks,agenda,contacts,documents',
            'terms'                 => 'accepted'
        ]);

        return DB::transaction(function () use ($validated) {
            // Création de l'utilisateur avec 30 jours d'essai
            $user = User::create([
                'name'                  => $validated['name'],
                'email'                 => $validated['email'],
                'password'              => Hash::make($validated['password']),
                'account_type'          => 'secretary',
                'role'                  => 'secretaire',
                'workspace_type'        => 'individuel',
                'trial_ends_at'         => Carbon::now()->addDays(30),
                'subscription_status'   => 'trial',
                'personal_company_name' => $validated['personal_company_name'],
                'personal_industry'     => $validated['personal_industry'],
                'onboarding_completed'  => true,
                'is_active'             => true,
                'role_secretaire'       => true,
            ]);

            // Insertion d'un exemple selon l'intention
            $this->seedIntentExample($user, $validated['primary_intent']);

            event(new Registered($user));
            Auth::login($user);

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
