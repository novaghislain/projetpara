<?php

namespace App\Http\Controllers\GelRh\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class RhRegisterController extends Controller
{
    public function showChoices()
    {
        return view('gel-rh.auth.register-choices');
    }

    public function showAutonomousForm()
    {
        return view('gel-rh.auth.register-autonomous');
    }

    public function registerAutonomous(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:utilisateurs,email',
            'password'              => 'required|string|min:8|confirmed',
            'personal_company_name' => 'nullable|string|max:255',
            'terms'                 => 'accepted'
        ]);

        return DB::transaction(function () use ($validated) {
            $nomCabinet = $validated['personal_company_name'] ?? 'Cabinet RH â€” ' . $validated['name'];
            $entreprise = \App\Models\Entreprise::create([
                'raison_sociale' => $nomCabinet,
                'pays_code'      => 'BJ',
                'secteur_activite' => 'Ressources Humaines',
                'regime_fiscal'  => 'TPS',
                'modele_usage'   => 'full',
            ]);

            $user = User::create([
                'nom'                   => $validated['name'],
                'email'                 => $validated['email'],
                'mot_de_passe_hash'     => Hash::make($validated['password']),
                'role'                  => 'rh',
                'is_autonomous'         => true,
            ]);

            $role = \App\Models\Role::where('code', 'rh')->first();

            if ($role) {
                \App\Models\Affectation::create([
                    'utilisateur_id' => $user->id,
                    'entreprise_id'  => $entreprise->id,
                    'role_id'        => $role->id,
                    'modele'         => 'full',
                    'statut'         => 'active',
                ]);
            }

            event(new Registered($user));
            Auth::login($user);

            session(['active_entreprise_id' => $entreprise->id]);

            return redirect()->route('gel-rh.dashboard')
                ->with('success', 'Bienvenue dans votre espace RH !');
        });
    }
}