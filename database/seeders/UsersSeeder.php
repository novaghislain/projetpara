<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use App\Models\UserClient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Crée tous les utilisateurs de démonstration pour le site.
     *
     * Mots de passe communs pour faciliter les tests :
     *   - Utilisateurs @gel.cabinet     → Mot de passe: admin123
     *   - Utilisateurs d'entreprise     → Mot de passe: admin123
     *   - Clients                      → Mot de passe: client123
     */
    public function run(): void
    {
        $now = Carbon::now();

        // ─── Récupération des clients (entreprises) existants ──────────
        $techInnov = Client::where('email', 'contact@techinnov.bj')->first();
        $africaLogistics = Client::where('email', 'direction@africa-logistics.com')->first();
        $servicesPro = Client::where('email', 'contact@servicespro.bj')->first();
        $particulier = Client::where('email', 'martin.dupuis@email.com')->first();

        $entrepriseIds = array_filter([
            $techInnov?->id,
            $africaLogistics?->id,
            $servicesPro?->id,
        ]);

        // ─── Récupération des rôles système ────────────────────────────
        $roles = Role::whereIn('slug', [
            'super_admin', 'comptable', 'company_admin', 'company_manager',
            'company_employee', 'client', 'caissier', 'juriste', 'rh',
            'gestionnaire_projet', 'secretaire',
        ])->get()->keyBy('slug');

        // ════════════════════════════════════════════════════════════════
        // 1. SUPER ADMIN – Accès total à la plateforme
        // ════════════════════════════════════════════════════════════════
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@gel.cabinet'],
            [
                'name' => 'Ghislain EDA',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_admin' => true,
                'is_active' => true,
                'is_company_admin' => false,
                'must_change_password' => true,
                'role_id' => $roles['super_admin']?->id,
                'fonction' => 'Super Administrateur - GEL Cabinet',
                'phone' => '+229 97 00 00 01',
                'email_verified_at' => $now,
            ]
        );

        // ════════════════════════════════════════════════════════════════
        // 2. ÉQUIPE CABINET GEL
        // ════════════════════════════════════════════════════════════════
        $cabinetUsers = [
            [
                'name' => 'Alice Comptabilité',
                'email' => 'alice@gel.cabinet',
                'role' => 'comptable',
                'role_model' => 'comptable',
                'fonction' => 'Chef de mission comptable',
                'phone' => '+229 97 00 00 02',
                'clients_assignes' => $entrepriseIds ?: [],
            ],
            [
                'name' => 'Bob Juridique',
                'email' => 'bob@gel.cabinet',
                'role' => 'juriste',
                'role_model' => 'juriste',
                'fonction' => 'Juriste Senior - Droit des Affaires',
                'phone' => '+229 97 00 00 03',
                'clients_assignes' => $entrepriseIds ?: [],
            ],
            [
                'name' => 'Sophie Secrétariat',
                'email' => 'sophie@gel.cabinet',
                'role' => 'secretaire',
                'role_model' => 'secretaire',
                'fonction' => 'Secrétaire Administrative',
                'phone' => '+229 97 00 00 04',
                'role_secretaire' => true,
                'clients_assignes' => $entrepriseIds ?: [],
            ],
            [
                'name' => 'Koffi RH',
                'email' => 'koffi@gel.cabinet',
                'role' => 'rh',
                'role_model' => 'rh',
                'fonction' => 'Gestionnaire RH Externalisé',
                'phone' => '+229 97 00 00 05',
                'clients_assignes' => $entrepriseIds ?: [],
            ],
            [
                'name' => 'Raoul Projets',
                'email' => 'raoul@gel.cabinet',
                'role' => 'gestionnaire_projet',
                'role_model' => 'gestionnaire_projet',
                'fonction' => 'Chef de Projet',
                'phone' => '+229 97 00 00 06',
                'clients_assignes' => $entrepriseIds ?: [],
            ],
        ];

        foreach ($cabinetUsers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('admin123'),
                    'role' => $data['role'],
                    'is_admin' => false,
                    'is_active' => true,
                    'is_company_admin' => false,
                    'role_id' => $roles[$data['role_model']]?->id,
                    'fonction' => $data['fonction'],
                    'phone' => $data['phone'],
                    'role_secretaire' => $data['role_secretaire'] ?? false,
                    'clients_assignes' => $data['clients_assignes'] ?? null,
                    'must_change_password' => true,
                    'email_verified_at' => $now,
                ]
            );

            // Lier aux entreprises pour les utilisateurs du cabinet
            foreach ($entrepriseIds as $cid) {
                UserClient::firstOrCreate(
                    ['user_id' => $user->id, 'client_id' => $cid],
                    ['role' => $data['role'], 'is_active' => true, 'joined_at' => $now]
                );
            }
        }

        // ════════════════════════════════════════════════════════════════
        // 3. ADMINISTRATEURS D'ENTREPRISE
        // ════════════════════════════════════════════════════════════════
        $companyAdmins = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean@techinnov.bj',
                'client' => $techInnov,
                'fonction' => 'Directeur Administratif',
                'phone' => '+229 97 00 01 01',
            ],
            [
                'name' => 'Aminata Diallo',
                'email' => 'aminata@africa-logistics.com',
                'client' => $africaLogistics,
                'fonction' => 'Directrice Générale',
                'phone' => '+229 97 00 01 02',
            ],
            [
                'name' => 'Kossi Agbéko',
                'email' => 'kossi@servicespro.bj',
                'client' => $servicesPro,
                'fonction' => 'Gérant',
                'phone' => '+229 97 00 01 03',
            ],
        ];

        foreach ($companyAdmins as $data) {
            if (!$data['client']) continue;
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('admin123'),
                    'role' => 'company_admin',
                    'is_admin' => false,
                    'is_active' => true,
                    'is_company_admin' => true,
                    'client_id' => $data['client']->id,
                    'active_client_id' => $data['client']->id,
                    'role_id' => $roles['company_admin']?->id,
                    'fonction' => $data['fonction'],
                    'phone' => $data['phone'],
                    'must_change_password' => true,
                    'email_verified_at' => $now,
                ]
            );

            UserClient::firstOrCreate(
                ['user_id' => $user->id, 'client_id' => $data['client']->id],
                ['role' => 'company_admin', 'is_active' => true, 'joined_at' => $now]
            );
        }

        // ════════════════════════════════════════════════════════════════
        // 4. MANAGERS D'ENTREPRISE
        // ════════════════════════════════════════════════════════════════
        $managers = [
            [
                'name' => 'Marie Koné',
                'email' => 'marie@techinnov.bj',
                'client' => $techInnov,
                'fonction' => 'Responsable Comptable',
                'phone' => '+229 97 00 02 01',
            ],
            [
                'name' => 'Issa Traoré',
                'email' => 'issa@africa-logistics.com',
                'client' => $africaLogistics,
                'fonction' => 'Directeur des Opérations',
                'phone' => '+229 97 00 02 02',
            ],
        ];

        foreach ($managers as $data) {
            if (!$data['client']) continue;
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('admin123'),
                    'role' => 'company_manager',
                    'is_admin' => false,
                    'is_active' => true,
                    'is_company_admin' => false,
                    'client_id' => $data['client']->id,
                    'active_client_id' => $data['client']->id,
                    'role_id' => $roles['company_manager']?->id,
                    'fonction' => $data['fonction'],
                    'phone' => $data['phone'],
                    'must_change_password' => true,
                    'email_verified_at' => $now,
                ]
            );

            UserClient::firstOrCreate(
                ['user_id' => $user->id, 'client_id' => $data['client']->id],
                ['role' => 'company_manager', 'is_active' => true, 'joined_at' => $now]
            );
        }

        // ════════════════════════════════════════════════════════════════
        // 5. CAISSIERS / VENDEURS (Commerce/POS)
        // ════════════════════════════════════════════════════════════════
        $cashiers = [
            [
                'name' => 'Paul Adomah',
                'email' => 'paul@techinnov.bj',
                'client' => $techInnov,
                'fonction' => 'Caissier Principal',
                'phone' => '+229 97 00 03 01',
            ],
            [
                'name' => 'Fiacre Hounsa',
                'email' => 'fiacre@techinnov.bj',
                'client' => $techInnov,
                'fonction' => 'Vendeur / Caissier',
                'phone' => '+229 97 00 03 02',
            ],
            [
                'name' => 'Grâce Hounsou',
                'email' => 'grace@africa-logistics.com',
                'client' => $africaLogistics,
                'fonction' => 'Caissière',
                'phone' => '+229 97 00 03 03',
            ],
        ];

        foreach ($cashiers as $data) {
            if (!$data['client']) continue;
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('admin123'),
                    'role' => 'caissier',
                    'is_admin' => false,
                    'is_active' => true,
                    'is_company_admin' => false,
                    'client_id' => $data['client']->id,
                    'active_client_id' => $data['client']->id,
                    'role_id' => $roles['caissier']?->id,
                    'fonction' => $data['fonction'],
                    'phone' => $data['phone'],
                    'must_change_password' => true,
                    'email_verified_at' => $now,
                ]
            );
        }

        // ════════════════════════════════════════════════════════════════
        // 6. EMPLOYÉS D'ENTREPRISE
        // ════════════════════════════════════════════════════════════════
        $employees = [
            [
                'name' => 'David Agossou',
                'email' => 'david@techinnov.bj',
                'client' => $techInnov,
                'fonction' => 'Assistant Comptable',
                'phone' => '+229 97 00 04 01',
            ],
            [
                'name' => 'Sarah Dossou',
                'email' => 'sarah@techinnov.bj',
                'client' => $techInnov,
                'fonction' => 'Assistante RH',
                'phone' => '+229 97 00 04 02',
            ],
            [
                'name' => 'Benoît Soglo',
                'email' => 'benoit@africa-logistics.com',
                'client' => $africaLogistics,
                'fonction' => 'Agent Administratif',
                'phone' => '+229 97 00 04 03',
            ],
        ];

        foreach ($employees as $data) {
            if (!$data['client']) continue;
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('admin123'),
                    'role' => 'company_employee',
                    'is_admin' => false,
                    'is_active' => true,
                    'is_company_admin' => false,
                    'client_id' => $data['client']->id,
                    'active_client_id' => $data['client']->id,
                    'role_id' => $roles['company_employee']?->id,
                    'fonction' => $data['fonction'],
                    'phone' => $data['phone'],
                    'must_change_password' => true,
                    'email_verified_at' => $now,
                ]
            );
        }

        // ════════════════════════════════════════════════════════════════
        // 7. CLIENTS (accès portail)
        // ════════════════════════════════════════════════════════════════
        $clients = [
            [
                'name' => 'Martin Dupuis',
                'email' => 'client1@test.com',
                'client' => $particulier,
                'fonction' => 'Client particulier',
                'phone' => '+229 61 23 45 67',
                'password' => 'Client2025!',
            ],
            [
                'name' => 'Estelle Zinsou',
                'email' => 'estelle@client.bj',
                'client' => $particulier,
                'fonction' => 'Profession libérale',
                'phone' => '+229 97 00 05 01',
                'password' => 'client123',
            ],
            [
                'name' => 'Serge Hounkpatin',
                'email' => 'serge@client.bj',
                'client' => $particulier,
                'fonction' => 'Client portail fiscal',
                'phone' => '+229 97 00 05 02',
                'password' => 'client123',
            ],
        ];

        foreach ($clients as $data) {
            if (!$data['client']) continue;
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'role' => 'client',
                    'is_admin' => false,
                    'is_active' => true,
                    'is_company_admin' => false,
                    'client_id' => $data['client']->id,
                    'active_client_id' => $data['client']->id,
                    'role_id' => $roles['client']?->id,
                    'fonction' => $data['fonction'],
                    'phone' => $data['phone'],
                    'must_change_password' => true,
                    'email_verified_at' => $now,
                ]
            );

            UserClient::firstOrCreate(
                ['user_id' => $user->id, 'client_id' => $data['client']->id],
                ['role' => 'client', 'is_active' => true, 'joined_at' => $now]
            );
        }

        // ════════════════════════════════════════════════════════════════
        // 8. STOCKS / INVENTAIRE (Commerce)
        // ════════════════════════════════════════════════════════════════
        $stockMgrs = [
            [
                'name' => 'Cédric Togbé',
                'email' => 'cedric@techinnov.bj',
                'client' => $techInnov,
                'fonction' => 'Gestionnaire de Stock',
                'phone' => '+229 97 00 06 01',
            ],
        ];

        foreach ($stockMgrs as $data) {
            if (!$data['client']) continue;
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('admin123'),
                    'role' => 'company_employee',
                    'is_admin' => false,
                    'is_active' => true,
                    'is_company_admin' => false,
                    'client_id' => $data['client']->id,
                    'active_client_id' => $data['client']->id,
                    'role_id' => $roles['company_employee']?->id,
                    'fonction' => $data['fonction'],
                    'phone' => $data['phone'],
                    'email_verified_at' => $now,
                ]
            );
        }

        $this->command->info('✅  Comptes utilisateurs créés avec succès !');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  UTILISATEURS DU SYSTÈME');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🔹  ADMINISTRATION');
        $this->command->info('  admin@gel.cabinet        admin123    Super Admin (Ghislain EDA)');
        $this->command->info('');
        $this->command->info('🔹  ÉQUIPE CABINET GEL');
        $this->command->info('  alice@gel.cabinet        admin123    Comptable Chef de mission');
        $this->command->info('  bob@gel.cabinet           admin123    Juriste Senior');
        $this->command->info('  sophie@gel.cabinet        admin123    Secrétaire Administrative');
        $this->command->info('  koffi@gel.cabinet         admin123    Gestionnaire RH');
        $this->command->info('  raoul@gel.cabinet         admin123    Chef de Projet');
        $this->command->info('');
        $this->command->info('🔹  ADMINISTRATEURS D\'ENTREPRISE');
        $this->command->info('  jean@techinnov.bj         admin123    TechInnov - Directeur Admin');
        $this->command->info('  aminata@africa-logistics.com admin123 Africa Logistics - DG');
        $this->command->info('  kossi@servicespro.bj     admin123    Services Pro - Gérant');
        $this->command->info('');
        $this->command->info('🔹  MANAGERS');
        $this->command->info('  marie@techinnov.bj        admin123    TechInnov - Resp. Comptable');
        $this->command->info('  issa@africa-logistics.com admin123 Africa Logistics - DOpérations');
        $this->command->info('');
        $this->command->info('🔹  CAISSIERS / VENDEURS (Commerce/POS)');
        $this->command->info('  paul@techinnov.bj         admin123    TechInnov - Caissier Principal');
        $this->command->info('  fiacre@techinnov.bj       admin123    TechInnov - Vendeur/Caissier');
        $this->command->info('  grace@africa-logistics.com admin123 Africa Logistics - Caissière');
        $this->command->info('');
        $this->command->info('🔹  EMPLOYÉS');
        $this->command->info('  david@techinnov.bj        admin123    TechInnov - Assistant Comptable');
        $this->command->info('  sarah@techinnov.bj        admin123    TechInnov - Assistante RH');
        $this->command->info('  benoit@africa-logistics.com admin123 Africa Logistics - Agent Admin');
        $this->command->info('');
        $this->command->info('🔹  GESTIONNAIRES DE STOCK');
        $this->command->info('  cedric@techinnov.bj       admin123    TechInnov - Gestionnaire Stock');
        $this->command->info('');
        $this->command->info('🔹  CLIENTS (Portail)');
        $this->command->info('  client1@test.com          Client2025! Client particulier (Martin)');
        $this->command->info('  estelle@client.bj         client123   Client particulier (Estelle)');
        $this->command->info('  serge@client.bj           client123   Client particulier (Serge)');
        $this->command->info('');
    }
}
