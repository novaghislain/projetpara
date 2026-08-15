<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Role;
use App\Models\Affectation;
use App\Models\ModuleEntreprise;
use Carbon\Carbon;

class GelDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création des Rôles
        $roleAdmin = Role::firstOrCreate(['code' => 'company_admin'], ['libelle' => 'Administrateur Entreprise']);
        $roleSecretaire = Role::firstOrCreate(['code' => 'secretary'], ['libelle' => 'Secrétaire']);
        $roleComptable = Role::firstOrCreate(['code' => 'accountant'], ['libelle' => 'Comptable']);

        // 2. Création de l'Entreprise de Démo
        $entreprise = Entreprise::create([
            'raison_sociale' => 'Entreprise de Démo GEL',
            'pays_code' => 'BJ',
            'regime_fiscal' => 'TPS',
            'modele_usage' => 'standard',
            'statut_abonnement' => 'actif'
        ]);

        // 3. Activation des modules pour cette entreprise
        ModuleEntreprise::create(['entreprise_id' => $entreprise->id, 'module_code' => 'secretariat', 'actif' => true]);
        ModuleEntreprise::create(['entreprise_id' => $entreprise->id, 'module_code' => 'comptabilite', 'actif' => true]);
        ModuleEntreprise::create(['entreprise_id' => $entreprise->id, 'module_code' => 'rh', 'actif' => true]);

        // 4. Création des utilisateurs
        $users = [
            [
                'email' => 'admin@demo.com',
                'nom' => 'Admin Démo',
                'role' => $roleAdmin->id,
            ],
            [
                'email' => 'secretaire@demo.com',
                'nom' => 'Secrétaire Démo',
                'role' => $roleSecretaire->id,
            ],
            [
                'email' => 'comptable@demo.com',
                'nom' => 'Comptable Démo',
                'role' => $roleComptable->id,
            ]
        ];

        foreach ($users as $u) {
            $user = User::create([
                'email' => $u['email'],
                'mot_de_passe_hash' => Hash::make('password123'),
                'password' => Hash::make('password123'),
                'nom' => $u['nom'],
                'statut' => 'actif'
            ]);

            // Affectation à l'entreprise
            Affectation::create([
                'utilisateur_id' => $user->id,
                'entreprise_id' => $entreprise->id,
                'role_id' => $u['role'],
                'modele' => 'standard',
                'statut' => 'active'
            ]);
        }
    }
}
