<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entreprise;
use App\Models\User;
use App\Models\Role;
use App\Models\Affectation;
use App\Models\ModuleEntreprise;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_entreprises' => Entreprise::count(),
            'total_users' => User::count(),
            'active_subscriptions' => Entreprise::where('statut_abonnement', 'actif')->count(),
            'total_mrr' => Entreprise::where('statut_abonnement', 'actif')->count() * 50000, // Dummy MRR
        ];
        
        $recent_entreprises = Entreprise::latest()->take(5)->get();
        
        return view('super-admin.dashboard', compact('stats', 'recent_entreprises'));
    }

    public function entreprises()
    {
        $entreprises = Entreprise::with(['affectations.utilisateur', 'modules'])->get();
        $roles = Role::all();
        $tousModules = ['secretariat' => 'Secrétariat', 'comptabilite' => 'Comptabilité', 'rh' => 'RH'];
        
        return view('super-admin.entreprises.index', compact('entreprises', 'roles', 'tousModules'));
    }

    public function storeEntreprise(Request $request)
    {
        $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'email_admin' => 'required|email',
            'nom_admin' => 'required|string|max:255',
            'mot_de_passe' => 'required|string|min:6',
        ]);

        // Créer l'entreprise
        $entreprise = Entreprise::create([
            'raison_sociale' => $request->raison_sociale,
            'pays_code' => 'FR',
            'regime_fiscal' => 'TVA',
            'modele_usage' => 'standard',
            'statut_abonnement' => 'actif'
        ]);

        // Activer tous les modules par défaut pour la démo
        foreach(['secretariat', 'comptabilite', 'rh'] as $mod) {
            ModuleEntreprise::create(['entreprise_id' => $entreprise->id, 'module_code' => $mod, 'actif' => true]);
        }

        // Créer ou récupérer l'utilisateur admin
        $user = User::firstOrCreate(
            ['email' => $request->email_admin],
            [
                'nom' => $request->nom_admin,
                'mot_de_passe_hash' => Hash::make($request->mot_de_passe),
                'password' => Hash::make($request->mot_de_passe),
                'statut' => 'actif'
            ]
        );

        // Affecter le rôle Admin
        $roleAdmin = Role::where('code', 'company_admin')->first();
        if ($roleAdmin) {
            Affectation::create([
                'utilisateur_id' => $user->id,
                'entreprise_id' => $entreprise->id,
                'role_id' => $roleAdmin->id,
                'modele' => 'standard',
                'statut' => 'active'
            ]);
        }

        return back()->with('success', 'Entreprise et Administrateur créés avec succès.');
    }

    public function updateModules(Request $request, $id)
    {
        $entreprise = Entreprise::findOrFail($id);
        $modules = $request->input('modules', []);

        // Désactiver tous les modules
        ModuleEntreprise::where('entreprise_id', $entreprise->id)->update(['actif' => false]);

        // Activer les modules sélectionnés
        foreach($modules as $modCode => $val) {
            ModuleEntreprise::updateOrCreate(
                ['entreprise_id' => $entreprise->id, 'module_code' => $modCode],
                ['actif' => true]
            );
        }

        return back()->with('success', 'Modules mis à jour.');
    }

    public function storeAffectation(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'nom' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'mot_de_passe' => 'required|string|min:6',
        ]);

        $entreprise = Entreprise::findOrFail($id);

        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'nom' => $request->nom,
                'mot_de_passe_hash' => Hash::make($request->mot_de_passe),
                'password' => Hash::make($request->mot_de_passe),
                'statut' => 'actif'
            ]
        );

        Affectation::updateOrCreate(
            [
                'utilisateur_id' => $user->id,
                'entreprise_id' => $entreprise->id,
            ],
            [
                'role_id' => $request->role_id,
                'modele' => 'standard',
                'statut' => 'active'
            ]
        );

        return back()->with('success', 'Utilisateur affecté avec succès.');
    }

    public function users()
    {
        $users = User::with('affectations.entreprise', 'affectations.role')->paginate(15);
        return view('super-admin.users.index', compact('users'));
    }

    public function settings()
    {
        return view('super-admin.settings');
    }

    public function logs()
    {
        // Dummy logs
        $logs = [
            ['date' => now()->subMinutes(5), 'user' => 'Super Admin', 'action' => 'Création d\'entreprise: Acme Corp'],
            ['date' => now()->subHours(2), 'user' => 'Système', 'action' => 'Sauvegarde automatique réussie'],
            ['date' => now()->subDays(1), 'user' => 'John Doe', 'action' => 'Connexion échouée (IP: 192.168.1.1)'],
        ];
        return view('super-admin.logs', compact('logs'));
    }
}
