<?php

namespace Database\Seeders;

use App\Models\BusinessDomain;
use App\Models\Client;
use App\Models\User;
use App\Models\UserClient;
use App\Services\TenantDomainService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DomainDemoSeeder extends Seeder
{
    /**
     * Crée 3 clients de démonstration avec des domaines différents :
     * - Ets. Koudjo & Fils  → Commerce
     * - Hôtel Beau Rivage   → Hotel
     * - Collège Saint-Michel → Scolaire
     */
    public function run(): void
    {
        $demoPassword = 'admin123';

        $demos = [
            [
                'company_name'  => 'Ets. Koudjo & Fils',
                'domain_code'   => 'commerce',
                'legal_form'    => 'SARL',
                'rccm'          => 'RB/COT/2025/00123',
                'ifu'           => '0202123456789',
                'address'       => '15 Rue du Commerce, Zone Industrielle',
                'city'          => 'Cotonou',
                'phone'         => '+229 01 01 01 01',
                'email'         => 'contact@koudjo.bj',
                'admin_name'    => 'Admin Koudjo',
                'admin_email'   => 'admin@koudjo.bj',
            ],
            [
                'company_name'  => 'Hôtel Beau Rivage',
                'domain_code'   => 'hotel',
                'legal_form'    => 'SA',
                'rccm'          => 'RB/COT/2025/00456',
                'ifu'           => '0202987654321',
                'address'       => 'Boulevard Maritime, Plage',
                'city'          => 'Cotonou',
                'phone'         => '+229 01 02 03 04',
                'email'         => 'contact@beaurivage.bj',
                'admin_name'    => 'Admin Beau Rivage',
                'admin_email'   => 'admin@beaurivage.bj',
            ],
            [
                'company_name'  => 'Collège Saint-Michel',
                'domain_code'   => 'scolaire',
                'legal_form'    => 'Association',
                'rccm'          => 'RB/COT/2025/00789',
                'ifu'           => '0202555666777',
                'address'       => 'Avenue de l\'Éducation, Quartier Nord',
                'city'          => 'Cotonou',
                'phone'         => '+229 01 04 05 06',
                'email'         => 'contact@saintmichel.bj',
                'admin_name'    => 'Admin Saint-Michel',
                'admin_email'   => 'admin@saintmichel.bj',
            ],
        ];

        foreach ($demos as $demo) {
            try {
                DB::transaction(function () use ($demo, $demoPassword) {
                    // Récupérer le domaine
                    $domain = BusinessDomain::where('code', $demo['domain_code'])->first();
                    if (!$domain) {
                        Log::warning('Domaine introuvable pour le seeder de démo', [
                            'code' => $demo['domain_code'],
                        ]);
                        return;
                    }

                    // Éviter les doublons
                    $existingClient = Client::where('company_name', $demo['company_name'])->first();
                    if ($existingClient) {
                        Log::info('Client démo déjà existant, ignoré', [
                            'company' => $demo['company_name'],
                        ]);
                        return;
                    }

                    $existingUser = User::where('email', $demo['admin_email'])->first();
                    if ($existingUser) {
                        Log::info('Admin démo déjà existant, ignoré', [
                            'email' => $demo['admin_email'],
                        ]);
                        return;
                    }

                    // 1. Créer le client
                    $client = Client::create([
                        'company_name'       => $demo['company_name'],
                        'legal_form'         => $demo['legal_form'],
                        'rccm'               => $demo['rccm'],
                        'ifu'                => $demo['ifu'],
                        'address'            => $demo['address'],
                        'city'               => $demo['city'],
                        'phone'              => $demo['phone'],
                        'email'              => $demo['email'],
                        'country'            => 'Bénin',
                        'status'             => 'actif',
                        'contract_type'      => 'annuel',
                        'contract_start'     => now()->subMonth(),
                        'domain_id'          => $domain->id,
                        'domain_code'        => $demo['domain_code'],
                        'domain_confirmed'   => true,
                        'domain_confirmed_at'=> now(),
                    ]);

                    // 2. Créer l'utilisateur admin
                    $admin = User::create([
                        'name'              => $demo['admin_name'],
                        'email'             => $demo['admin_email'],
                        'password'          => Hash::make($demoPassword),
                        'role'              => 'company_admin',
                        'is_company_admin'  => true,
                        'client_id'         => $client->id,
                        'is_active'         => true,
                        'must_change_password' => true,
                    ]);

                    // 3. Associer l'admin au client
                    UserClient::create([
                        'user_id'    => $admin->id,
                        'client_id'  => $client->id,
                        'role'       => 'company_admin',
                        'is_active'  => true,
                        'joined_at'  => now(),
                    ]);

                    // 4. Activer les modules comptables du domaine
                    try {
                        app(TenantDomainService::class)->activerModulesDomaine($client);
                    } catch (\Exception $e) {
                        Log::warning('Échec activation modules domaine pour démo', [
                            'client_id' => $client->id,
                            'error'     => $e->getMessage(),
                        ]);
                    }

                    $this->command?->info("✓ Client démo créé : {$demo['company_name']} ({$demo['domain_code']})");
                });
            } catch (\Exception $e) {
                Log::error('Erreur création client démo', [
                    'company' => $demo['company_name'],
                    'error'   => $e->getMessage(),
                ]);
                $this->command?->error("✗ Erreur : {$demo['company_name']} — {$e->getMessage()}");
            }
        }

        $this->command?->info('┌──────────────────────────────────────────────┐');
        $this->command?->info('│  Identifiants de démonstration :              │');
        $this->command?->info('│  admin@koudjo.bj / admin123 (Commerce)        │');
        $this->command?->info('│  admin@beaurivage.bj / admin123 (Hôtel)       │');
        $this->command?->info('│  admin@saintmichel.bj / admin123 (Scolaire)   │');
        $this->command?->info('└──────────────────────────────────────────────┘');
    }
}
