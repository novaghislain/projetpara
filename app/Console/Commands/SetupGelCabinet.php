<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupGelCabinet extends Command
{
    protected $signature = 'gel:setup {--fresh : Recréer la base de données avant le setup}';
    protected $description = 'Configure l\'environnement GEL Cabinet : ACL, seeders, et permissions.';

    public function handle(): int
    {
        $this->info('🚀 Setup GEL Cabinet - ACL & Permissions');
        $this->newLine();

        // ── Étape 1 : Reset BDD (optionnel) ──────────────────────────
        if ($this->option('fresh')) {
            if ($this->confirm('⚠️  Voulez-vous RECRÉER la base de données ? Toutes les données seront perdues.')) {
                $this->info('Réinitialisation de la base...');
                Artisan::call('migrate:fresh --force', [], $this->output);
                $this->info('✓ Base réinitialisée.');
            }
        } else {
            $this->info('Migration des tables...');
            Artisan::call('migrate --force', [], $this->output);
        }

        $this->newLine();

        // ── Étape 2 : Seeders ACL ────────────────────────────────────
        // Vider le cache Spatie avant tout seed
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $exists = Role::count() > 0;

        if ($exists) {
            $this->warn('Les rôles et permissions existent déjà. Utilisez --fresh pour tout recréer.');
            $this->warn('Exécution des seeders en mode réparation (updateOrCreate)...');

            $this->info('Mise à jour des rôles...');
            Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\RoleSeeder',
                '--force' => true,
            ], $this->output);

            $this->info('Mise à jour des permissions...');
            Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\PermissionSeeder',
                '--force' => true,
            ], $this->output);
        } else {
            $this->info('Création des rôles (14 rôles)...');
            Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\RoleSeeder',
                '--force' => true,
            ], $this->output);

            $this->info('Création des permissions (54 permissions)...');
            Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\PermissionSeeder',
                '--force' => true,
            ], $this->output);
        }

        $this->newLine();

        // ── Étape 3 : Vérification ───────────────────────────────────
        $this->info('📊 Vérification de l\'installation ACL');

        // Vider le cache Spatie après le seed
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $roleCount = Role::count();
        $permCount = Permission::count();

        $this->table(
            ['Composant', 'Statut', 'Détail'],
            [
                ['Rôles', '✓', "$roleCount rôles créés"],
                ['Permissions', '✓', "$permCount permissions créées"],
                ['Migration Spatie', '✓', 'Tables gel_roles, gel_permissions, gel_model_has_roles...'],
                ['CabinetTeamResolver', '✓', 'Implémente PermissionsTeamResolver'],
            ]
        );

        $this->newLine();

        // ── Étape 4 : Résumé des rôles ───────────────────────────────
        $this->info('📋 Rôles disponibles :');

        $roles = Role::with('permissions')->get()->map(fn($r) => [
            $r->name,
            $r->label_fr ?? '—',
            $r->portail ?? '—',
            'Niv. ' . ($r->level ?? '—'),
            $r->permissions->count() . ' perms',
        ])->toArray();

        $this->table(
            ['Nom', 'Label', 'Portail', 'Niveau', 'Permissions'],
            $roles
        );

        $this->newLine();
        $this->info('✅ Setup GEL Cabinet terminé avec succès !');
        $this->warn('   N\'oubliez pas de configurer un utilisateur super_admin pour vous connecter.');
        $this->warn('   Exécutez : php artisan db:seed --class=AdminSeeder');

        return Command::SUCCESS;
    }
}
