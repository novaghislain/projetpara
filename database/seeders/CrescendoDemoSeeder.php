<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientFolder;
use App\Models\ClientModule;
use App\Models\Document;
use App\Models\License;
use App\Models\Role;
use App\Models\User;
use App\Models\UserClient;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CrescendoDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 0. Super Admin ─────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@monprojet.com'],
            [
                'name' => 'Admin GEL',
                'password' => Hash::make('Admin2025!'),
                'role' => 'super_admin',
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'must_change_password' => true,
            ]
        );

        // ─── 1. Client particulier ───────────────────────────────────────
        $particulier = Client::firstOrCreate(
            ['email' => 'martin.dupuis@email.com'],
            [
                'company_name' => 'Martin Dupuis',
                'legal_form' => 'Particulier',
                'address' => '45 Rue des Lilas',
                'city' => 'Cotonou',
                'country' => 'Bénin',
                'phone' => '+229 61 23 45 67',
                'status' => 'actif',
                'contract_type' => 'monthly',
                'contract_start' => Carbon::now()->subMonths(6),
                'contract_end' => Carbon::now()->addMonths(6),
                'created_by' => 1,
            ]
        );

        $particulierRole = Role::firstOrCreate(
            ['slug' => 'client'],
            [
                'name' => 'Client',
                'description' => 'Client particulier',
                'level' => 1,
                'is_system' => true,
            ]
        );

        $clientUser = User::firstOrCreate(
            ['email' => 'client1@test.com'],
            [
                'name' => 'Martin Dupuis',
                'password' => Hash::make('Client2025!'),
                'role' => 'client',
                'client_id' => $particulier->id,
                'role_id' => $particulierRole->id,
                'is_company_admin' => false,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'must_change_password' => true,
                'fonction' => 'Client particulier',
                'phone' => '+229 61 23 45 67',
            ]
        );

        // Dossiers fiscaux
        $foldersData = [
            ['name' => 'Déclarations 2025', 'slug' => 'declarations-2025'],
            ['name' => 'Déclarations 2024', 'slug' => 'declarations-2024'],
            ['name' => 'Documents justificatifs', 'slug' => 'justificatifs'],
            ['name' => 'Correspondance', 'slug' => 'correspondance'],
        ];
        $folderIds = [];
        foreach ($foldersData as $i => $fd) {
            $folder = ClientFolder::firstOrCreate(
                ['client_id' => $particulier->id, 'slug' => $fd['slug']],
                [
                    'name' => $fd['name'],
                    'path' => $fd['name'],
                    'level' => 1,
                    'parent_id' => null,
                    'sort_order' => $i,
                    'is_system' => false,
                ]
            );
            $folderIds[] = $folder->id;
        }

        // Documents fictifs
        $docs = [
            ['folder_idx' => 0, 'name' => 'Avis d\'imposition 2024.pdf', 'size' => 245000],
            ['folder_idx' => 0, 'name' => 'Déclaration fédérale 2025.pdf', 'size' => 312000],
            ['folder_idx' => 0, 'name' => 'Déclaration provinciale 2025.pdf', 'size' => 289000],
            ['folder_idx' => 1, 'name' => 'Avis d\'imposition 2023.pdf', 'size' => 198000],
            ['folder_idx' => 2, 'name' => 'Relevé bancaire 2025.pdf', 'size' => 156000],
            ['folder_idx' => 2, 'name' => 'Justificatif frais médicaux.pdf', 'size' => 89000],
        ];
        foreach ($docs as $d) {
            Document::firstOrCreate(
                ['client_id' => $particulier->id, 'name' => $d['name']],
                [
                    'folder_id' => $folderIds[$d['folder_idx']],
                    'original_name' => $d['name'],
                    'file_path' => 'demo/' . strtolower(str_replace(' ', '_', $d['name'])),
                    'file_hash' => md5($d['name']),
                    'file_type' => 'application/pdf',
                    'file_size' => $d['size'],
                    'mime_type' => 'application/pdf',
                    'description' => 'Document de démonstration',
                    'version' => 1,
                    'uploaded_by' => $clientUser->id,
                    'is_archived' => false,
                ]
            );
        }

        // ─── 2. Client entreprise ────────────────────────────────────────
        $entreprise = Client::firstOrCreate(
            ['email' => 'contact@servicespro.bj'],
            [
                'company_name' => 'Services Pro Bénin SARL',
                'legal_form' => 'SARL',
                'rccm' => 'RB-2026-078945',
                'ifu' => '4202607890123',
                'address' => '88 Avenue des Affaires',
                'city' => 'Cotonou',
                'country' => 'Bénin',
                'phone' => '+229 51 23 45 78',
                'website' => 'https://servicespro.bj',
                'status' => 'actif',
                'contract_type' => 'annual',
                'contract_start' => Carbon::now()->subMonths(3),
                'contract_end' => Carbon::now()->addMonths(9),
                'created_by' => 1,
            ]
        );

        // Licences
        $svcIds = Service::whereIn('id', [1, 2, 3, 4])->pluck('id')->toArray();
        foreach ($svcIds as $svcId) {
            License::firstOrCreate(
                ['client_id' => $entreprise->id, 'service_id' => $svcId],
                [
                    'duration_months' => 12,
                    'start_date' => Carbon::now()->subMonths(3),
                    'end_date' => Carbon::now()->addMonths(9),
                    'price' => match ($svcId) { 1 => 200000, 2 => 150000, 3 => 120000, 4 => 100000, default => 100000 },
                    'status' => 'active',
                ]
            );
        }

        // Admin entreprise
        $companyAdminRole = Role::where('slug', 'company_admin')->first();
        $entrepriseUser = User::firstOrCreate(
            ['email' => 'entreprise@test.com'],
            [
                'name' => 'Aminata Diallo',
                'password' => Hash::make('Entreprise2025!'),
                'role' => 'company_admin',
                'client_id' => $entreprise->id,
                'role_id' => $companyAdminRole?->id,
                'is_company_admin' => true,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'must_change_password' => true,
                'fonction' => 'Directrice Administrative',
            ]
        );

        // ─── 3. Comptable ────────────────────────────────────────────────
        $comptableRole = Role::firstOrCreate(
            ['slug' => 'comptable'],
            [
                'name' => 'Comptable',
                'description' => 'Accès au module de comptabilité',
                'level' => 10,
                'is_system' => true,
            ]
        );

        $comptableUser = User::firstOrCreate(
            ['email' => 'comptable@monprojet.com'],
            [
                'name' => 'Fatoumata Koné',
                'password' => Hash::make('Comptable2025!'),
                'role' => 'comptable',
                'client_id' => $entreprise->id,
                'role_id' => $comptableRole->id,
                'is_company_admin' => false,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'must_change_password' => true,
                'fonction' => 'Comptable Senior',
            ]
        );

        // ─── Multi-tenant: UserClient records ───────────────────────────
        $now = Carbon::now();
        $multiTenantAssignments = [
            [$clientUser->id, $particulier->id, 'client'],
            [$entrepriseUser->id ?? null, $entreprise->id, 'company_admin'],
            [$comptableUser->id, $entreprise->id, 'comptable'],
        ];
        foreach ($multiTenantAssignments as [$uid, $cid, $role]) {
            if (!$uid) continue;
            UserClient::firstOrCreate(
                ['user_id' => $uid, 'client_id' => $cid],
                ['role' => $role, 'is_active' => true, 'joined_at' => $now]
            );
        }

        // ─── ClientModule records (activer tous les modules par défaut) ──
        $allModules = ['comptabilite','facturation','caisse','juridique','rh','projets','document','dae','erp','crm','it_helpdesk','it_assets'];
        foreach ([$particulier, $entreprise] as $client) {
            foreach ($allModules as $module) {
                ClientModule::firstOrCreate(
                    ['client_id' => $client->id, 'module' => $module],
                    ['is_active' => true, 'activated_at' => $now, 'activated_by' => 1]
                );
            }
        }

        $this->command->info('✅ Comptes démo Crescendo CPA créés avec succès !');
        $this->command->info('   admin@monprojet.com    / Admin2025!       (Super Admin)');
        $this->command->info('   client1@test.com       / Client2025!      (Client particulier)');
        $this->command->info('   entreprise@test.com    / Entreprise2025!  (Client entreprise)');
        $this->command->info('   comptable@monprojet.com / Comptable2025!  (Comptable)');
    }
}
