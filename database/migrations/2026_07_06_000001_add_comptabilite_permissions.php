<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'comptabilite.voir',
            'comptabilite.creer',
            'comptabilite.modifier',
            'comptabilite.supprimer',
            'comptabilite.valider',
            'comptabilite.exporter',
            'comptabilite.importer',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = Role::whereIn('name', ['super-admin', 'expert-comptable'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        $permissions = [
            'comptabilite.voir',
            'comptabilite.creer',
            'comptabilite.modifier',
            'comptabilite.supprimer',
            'comptabilite.valider',
            'comptabilite.exporter',
            'comptabilite.importer',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
