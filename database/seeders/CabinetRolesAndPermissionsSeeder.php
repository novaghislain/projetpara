<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CabinetRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Définir les permissions (pour un cabinet)
        $permissions = [
            // Tableau de bord
            'view dashboard',
            
            // Clients
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',

            // Comptabilité
            'view ecritures',
            'create ecritures',
            'edit ecritures',
            'validate ecritures',
            'delete ecritures',

            // Rapports (Grand Livre, Balance, etc.)
            'view reports',

            // Paramètres Cabinet & Equipe
            'manage team',
            'manage cabinet settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Définir les rôles et associer les permissions

        // Expert-Comptable / Administrateur
        $roleAdmin = Role::firstOrCreate(['name' => 'Expert-Comptable', 'guard_name' => 'web']);
        $roleAdmin->givePermissionTo(Permission::all());

        // Comptable Senior (peut valider)
        $roleSenior = Role::firstOrCreate(['name' => 'Comptable Senior', 'guard_name' => 'web']);
        $roleSenior->givePermissionTo([
            'view dashboard',
            'view clients',
            'create clients',
            'edit clients',
            'view ecritures',
            'create ecritures',
            'edit ecritures',
            'validate ecritures',
            'view reports',
        ]);

        // Comptable Junior (saisie uniquement)
        $roleJunior = Role::firstOrCreate(['name' => 'Comptable Junior', 'guard_name' => 'web']);
        $roleJunior->givePermissionTo([
            'view dashboard',
            'view clients',
            'view ecritures',
            'create ecritures',
            'edit ecritures', // Peut-être limité à ses propres écritures plus tard
            'view reports',
        ]);

        // Secrétaire / Assistant administratif
        $roleSecretary = Role::firstOrCreate(['name' => 'Secrétaire', 'guard_name' => 'web']);
        $roleSecretary->givePermissionTo([
            'view dashboard',
            'view clients',
            'create clients',
        ]);
    }
}
