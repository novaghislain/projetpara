<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On s'assure que le cache des permissions est vidé
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Créer le rôle legacy si utilisé
        $legacyRole = \App\Models\Role::firstOrCreate(
            ['slug' => 'informaticien'],
            ['name' => 'Informaticien', 'description' => 'Informaticien (Pôle Informatique)']
        );

        // 2. Créer le rôle Spatie
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'informaticien', 'guard_name' => 'web']);

        // 3. Créer les permissions IT
        $modules = [
            'it.tickets' => 'Tickets Techniques',
            'it.security' => 'Sécurité et accès',
            'it.health' => 'Santé technique',
            'it.backups' => 'Sauvegardes et maintenance',
            'it.dev_requests' => 'Demandes de développement client',
        ];

        $actions = ['view', 'create', 'update', 'delete', 'export', 'validate'];

        foreach ($modules as $moduleSlug => $moduleName) {
            foreach ($actions as $action) {
                // Certains modules n'ont pas forcément toutes les actions, 
                // mais pour la flexibilité (matrice), on les crée tous.
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => "{$moduleSlug}.{$action}",
                    'guard_name' => 'web',
                ], [
                    'module' => $moduleSlug,
                    'action' => $action,
                    'description' => "Permet de {$action} sur le module {$moduleName}",
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En cas de rollback, on supprime le rôle informaticien
        \Spatie\Permission\Models\Role::where('name', 'informaticien')->delete();
        \App\Models\Role::where('slug', 'informaticien')->delete();

        // Et les permissions IT
        \Spatie\Permission\Models\Permission::where('name', 'like', 'it.%')->delete();
    }
};
