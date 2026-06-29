<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\License;
use App\Models\User;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        // Créer une entreprise cliente de démo
        $client = Client::create([
            'company_name' => 'TechInnov SARL',
            'legal_form' => 'SARL',
            'rccm' => 'RB-2025-012345',
            'ifu' => '4202501234567',
            'address' => '12 Boulevard de la République',
            'city' => 'Cotonou',
            'country' => 'Bénin',
            'phone' => '+229 01 23 45 67',
            'email' => 'contact@techinnov.bj',
            'status' => 'actif',
            'contract_type' => 'annual',
            'contract_start' => Carbon::now()->subMonths(2),
            'contract_end' => Carbon::now()->addMonths(10),
            'created_by' => 1, // Super Admin
        ]);

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SET app.client_id = '{$client->id}'");
        }
        try {
            // Lier les services (Comptabilité, Fiscal, Social)
        $services = Service::whereIn('id', [1, 3, 4])->get();
        foreach ($services as $service) {
            $client->services()->attach($service->id, [
                'status' => 'active',
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->addMonths(10),
            ]);
        }

        // Créer des licences pour ces services
        foreach ($services as $service) {
            License::create([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'duration_months' => 12,
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->addMonths(10),
                'price' => $service->id === 1 ? 150000 : 100000,
                'status' => 'active',
            ]);
        }

        // Créer l'administrateur de l'entreprise
        $companyAdminRole = \App\Models\Role::where('slug', 'company_admin')->first();
        User::create([
            'name' => 'Jean Dupont',
            'email' => 'jean@techinnov.bj',
            'password' => Hash::make('admin123'),
            'role' => 'company_admin',
            'is_company_admin' => true,
            'client_id' => $client->id,
            'role_id' => $companyAdminRole?->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Créer des utilisateurs de démonstration
        $comptableRole = \App\Models\Role::where('slug', 'comptable')->first();
        $caissierRole = \App\Models\Role::where('slug', 'caissier')->first();
        $juristeRole = \App\Models\Role::where('slug', 'juriste')->first();

        $demoUsers = [
            [
                'name' => 'Marie Koné',
                'email' => 'marie@techinnov.bj',
                'role' => 'comptable',
                'role_id' => $comptableRole?->id,
                'fonction' => 'Comptable senior',
            ],
            [
                'name' => 'Paul Adomah',
                'email' => 'paul@techinnov.bj',
                'role' => 'caissier',
                'role_id' => $caissierRole?->id,
                'fonction' => 'Caissier principal',
            ],
            [
                'name' => 'Safia Diallo',
                'email' => 'safia@techinnov.bj',
                'role' => 'juriste',
                'role_id' => $juristeRole?->id,
                'fonction' => 'Juriste d\'entreprise',
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('admin123'),
                'role' => $userData['role'],
                'is_company_admin' => false,
                'client_id' => $client->id,
                'role_id' => $userData['role_id'],
                'fonction' => $userData['fonction'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    } finally {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SET app.client_id = '0'");
        }
    }
}
}
