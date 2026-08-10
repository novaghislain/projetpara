<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\UserClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutonomousEnterpriseController extends Controller
{
    /**
     * Affiche le formulaire de création de la première entreprise pour le secrétaire indépendant.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user->isAutonomousSecretary()) {
            abort(403, 'Accès non autorisé.');
        }

        // Si l'utilisateur a déjà une entreprise, on redirige vers le dashboard
        if ($user->userClients()->count() > 0) {
            return redirect()->route('gel-secretary.dashboard');
        }

        return view('gel-secretary.autonomous.create-enterprise');
    }

    /**
     * Enregistre l'entreprise et la définit comme contexte actif.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAutonomousSecretary()) {
            abort(403, 'Accès non autorisé.');
        }

        if ($user->userClients()->count() > 0) {
            return redirect()->route('gel-secretary.dashboard');
        }

        $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'telephone'      => 'nullable|string|max:30',
        ]);

        // Création du client (qui représente l'entreprise du secrétaire)
        // Utilise App\Models\Client qui mappe vers la table 'clients'
        // référencée par user_clients.client_id
        $client = Client::create([
            'company_name'   => $request->nom_entreprise,
            'email'          => $request->email ?? $user->email,
            'phone'          => $request->telephone,
            'created_by'     => $user->id,
            'status'         => 'actif',
            'wants_secretary' => true,
        ]);

        // Lier l'utilisateur à cette entreprise
        // Le rôle doit être dans l'enum: 'super_admin','company_admin','company_manager',
        // 'company_employee','client','comptable','caissier','juriste','rh','gestionnaire_projet','secretaire'
        UserClient::create([
            'user_id'   => $user->id,
            'client_id' => $client->id,
            'role'      => 'company_admin',
            'is_active' => true,
        ]);

        // Définir l'entreprise comme contexte actif
        session(['active_client_id' => $client->id]);
        $user->update(['active_client_id' => $client->id]);

        return redirect()->route('gel-secretary.dashboard')->with('success', 'Votre espace entreprise a été créé avec succès.');
    }
}
