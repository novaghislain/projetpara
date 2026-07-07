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
     * Crée 7 clients de démonstration couvrant 7 domaines d'activité :
     * - Ets. Koudjo & Fils  → Commerce
     * - Hôtel Beau Rivage   → Hotel
     * - Collège Saint-Michel → Scolaire
     * - Agence Lokossa Immobilier → Location
     * - Transport Rapide Express → Transport
     * - Le Gourmet Restaurant → Restauration
     * - Clinique La Miséricorde → Santé
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
            [
                'company_name'  => 'Agence Lokossa Immobilier',
                'domain_code'   => 'location',
                'legal_form'    => 'SARL',
                'rccm'          => 'RB/MONO/2025/00147',
                'ifu'           => '0202333444555',
                'address'       => '12 Rue des Bailleurs, Centre-Ville',
                'city'          => 'Lokossa',
                'phone'         => '+229 01 10 11 12',
                'email'         => 'contact@lokossaimmo.bj',
                'admin_name'    => 'Admin Lokossa Immo',
                'admin_email'   => 'admin@lokossaimmo.bj',
            ],
            [
                'company_name'  => 'Transport Rapide Express',
                'domain_code'   => 'transport',
                'legal_form'    => 'SA',
                'rccm'          => 'RB/COT/2025/00999',
                'ifu'           => '0202666777888',
                'address'       => 'Gare Routière, Boulevard de l\'Indépendance',
                'city'          => 'Cotonou',
                'phone'         => '+229 01 20 21 22',
                'email'         => 'contact@trexpress.bj',
                'admin_name'    => 'Admin Transport Express',
                'admin_email'   => 'admin@trexpress.bj',
            ],
            [
                'company_name'  => 'Le Gourmet Restaurant',
                'domain_code'   => 'restauration',
                'legal_form'    => 'EURL',
                'rccm'          => 'RB/COT/2025/00150',
                'ifu'           => '0202777888999',
                'address'       => '5 Rue des Saveurs, Quartier Gourmet',
                'city'          => 'Cotonou',
                'phone'         => '+229 01 30 31 32',
                'email'         => 'contact@legourmet.bj',
                'admin_name'    => 'Admin Le Gourmet',
                'admin_email'   => 'admin@legourmet.bj',
            ],
            [
                'company_name'  => 'Clinique La Miséricorde',
                'domain_code'   => 'sante',
                'legal_form'    => 'SARL',
                'rccm'          => 'RB/COT/2025/00177',
                'ifu'           => '0202888999000',
                'address'       => '42 Avenue de la Santé, Quartier Médical',
                'city'          => 'Cotonou',
                'phone'         => '+229 01 40 41 42',
                'email'         => 'contact@lamisericorde.bj',
                'admin_name'    => 'Admin La Miséricorde',
                'admin_email'   => 'admin@lamisericorde.bj',
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
                        'active_client_id'  => $client->id,
                        'email_verified_at' => now(),
                        'is_active'         => true,
                        'must_change_password' => false,
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

        $this->command?->info('┌──────────────────────────────────────────────────────────────────┐');
        $this->command?->info('│  Identifiants de démonstration :                                  │');
        $this->command?->info('│  admin@koudjo.bj / admin123      (Commerce)                       │');
        $this->command?->info('│  admin@beaurivage.bj / admin123  (Hôtel)                          │');
        $this->command?->info('│  admin@saintmichel.bj / admin123 (Scolaire)                       │');
        $this->command?->info('│  admin@lokossaimmo.bj / admin123 (Location)                       │');
        $this->command?->info('│  admin@trexpress.bj / admin123   (Transport)                      │');
        $this->command?->info('│  admin@legourmet.bj / admin123   (Restauration)                   │');
        $this->command?->info('│  admin@lamisericorde.bj / admin123 (Santé)                        │');
        $this->command?->info('└──────────────────────────────────────────────────────────────────┘');
    }
}
