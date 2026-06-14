<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Définition des modules et leurs permissions ──────────────

        $modules = [
            'comptabilite' => [
                'lire'         => 'Lire les écritures comptables',
                'creer'        => 'Créer des écritures comptables',
                'modifier'     => 'Modifier les écritures comptables',
                'supprimer'    => 'Supprimer les écritures comptables',
                'valider'      => 'Valider les écritures comptables',
            ],
            'facturation' => [
                'creer_facture'    => 'Créer des factures',
                'modifier_facture' => 'Modifier des factures',
                'annuler_facture'  => 'Annuler des factures',
                'imprimer_facture' => 'Imprimer des factures',
            ],
            'caisse' => [
                'encaissement'  => 'Effectuer un encaissement',
                'decaissement'  => 'Effectuer un décaissement',
                'historique'    => 'Consulter l\'historique des transactions',
                'rapports'      => 'Consulter les rapports de caisse',
            ],
            'juridique' => [
                'consultation'       => 'Consulter les dossiers juridiques',
                'creation_dossiers'  => 'Créer des dossiers juridiques',
                'archivage'          => 'Archiver des dossiers juridiques',
            ],
            'rh' => [
                'lecture'     => 'Consulter les fiches employés',
                'creation'    => 'Créer des fiches employés',
                'modification' => 'Modifier les fiches employés',
                'suppression' => 'Supprimer les fiches employés',
            ],
            'projets' => [
                'lecture'     => 'Consulter les projets',
                'creation'    => 'Créer des projets',
                'modification' => 'Modifier les projets',
                'achevement'  => 'Achever / clôturer des projets',
            ],
            'document' => [
                'lecture'     => 'Consulter les documents',
                'upload'      => 'Téléverser des documents',
                'modification' => 'Modifier les documents',
                'suppression' => 'Supprimer les documents',
            ],
        ];

        // ─── 2. Création des permissions ─────────────────────────────────

        $permissionIds = []; // module => [action => id]

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action => $displayName) {
                $perm = Permission::firstOrCreate(
                    ['module' => $module, 'action' => $action],
                    [
                        'display_name' => $displayName,
                        'description' => "Permission de {$displayName} dans le module {$module}.",
                    ]
                );
                $permissionIds[$module][$action] = $perm->id;
            }
        }

        // ─── 3. Création des rôles ──────────────────────────────────────

        $roles = [
            [
                'name' => 'Super Administrateur',
                'slug' => 'super_admin',
                'description' => 'Contrôle total de la plateforme. Accès à toutes les entreprises et modules.',
                'level' => 100,
                'is_system' => true,
            ],
            [
                'name' => 'Administrateur',
                'slug' => 'company_admin',
                'description' => 'Gère les utilisateurs, les rôles et les modules de son entreprise.',
                'level' => 50,
                'is_system' => true,
            ],
            [
                'name' => 'Comptable',
                'slug' => 'comptable',
                'description' => 'Accès au module de comptabilité (écritures, journaux, rapports).',
                'level' => 10,
                'is_system' => true,
            ],
            [
                'name' => 'Caissier',
                'slug' => 'caissier',
                'description' => 'Accès au module de caisse (encaissements, décaissements).',
                'level' => 10,
                'is_system' => true,
            ],
            [
                'name' => 'Juriste',
                'slug' => 'juriste',
                'description' => 'Accès au module juridique (dossiers, archivage).',
                'level' => 10,
                'is_system' => true,
            ],
            [
                'name' => 'Gestionnaire RH',
                'slug' => 'rh',
                'description' => 'Accès au module RH (fiches employés, paie).',
                'level' => 10,
                'is_system' => true,
            ],
            [
                'name' => 'Gestionnaire de projets',
                'slug' => 'gestionnaire_projet',
                'description' => 'Accès au module de gestion de projets.',
                'level' => 10,
                'is_system' => true,
            ],
        ];

        // ─── 4. Remplir les permissions par rôle ────────────────────────

        // Super Admin → toutes les permissions
        $allPermissionIds = collect($permissionIds)->flatten()->values()->all();

        // Admin entreprise → toutes les permissions (gère son entreprise)
        // (mêmes permissions que Super Admin, mais scope limité à son entreprise)

        // Comptable → comptabilite uniquement
        $comptablePerms = array_values($permissionIds['comptabilite'] ?? []);

        // Caissier → caisse uniquement
        $caissierPerms = array_values($permissionIds['caisse'] ?? []);

        // Juriste → juridique uniquement
        $juristePerms = array_values($permissionIds['juridique'] ?? []);

        // RH → rh uniquement
        $rhPerms = array_values($permissionIds['rh'] ?? []);

        // Gestionnaire projet → projets uniquement
        $projetPerms = array_values($permissionIds['projets'] ?? []);

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Assigner les permissions selon le rôle
            switch ($role->slug) {
                case 'super_admin':
                    $role->permissions()->sync($allPermissionIds);
                    break;
                case 'company_admin':
                    $role->permissions()->sync($allPermissionIds);
                    break;
                case 'comptable':
                    $role->permissions()->sync($comptablePerms);
                    break;
                case 'caissier':
                    $role->permissions()->sync($caissierPerms);
                    break;
                case 'juriste':
                    $role->permissions()->sync($juristePerms);
                    break;
                case 'rh':
                    $role->permissions()->sync($rhPerms);
                    break;
                case 'gestionnaire_projet':
                    $role->permissions()->sync($projetPerms);
                    break;
            }
        }
    }
}
