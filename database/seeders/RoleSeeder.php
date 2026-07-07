<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * 14 rôles répartis sur 3 portails.
     *
     * Portail GEL (cabinet)     : super_admin, gestionnaire_cabinet, comptable_senior,
     *                             chef_comptable, comptable_junior, agent_paie,
     *                             agent_client, chef_it, stagiaire, auditeur
     * Portail Entreprise (client): entreprise_admin, entreprise_comptable, entreprise_rh
     * Portail CPA (particulier)  : cpa_particulier
     */
    public function run(): void
    {
        // Désactiver le cache du temps d'exécution
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            // ── Portail GEL (cabinet) ──────────────────────────────
            [
                'name'        => 'super_admin',
                'guard_name'  => 'web',
                'module'      => null,
                'label_fr'    => 'Super Administrateur',
                'description' => 'Accès total à toutes les fonctionnalités du cabinet. Gère les utilisateurs, les rôles, et la configuration globale.',
                'level'       => 0,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'gestionnaire_cabinet',
                'guard_name'  => 'web',
                'module'      => 'admin',
                'label_fr'    => 'Gestionnaire de Cabinet',
                'description' => 'Gère les clients, les abonnements, la facturation et les contrats du cabinet.',
                'level'       => 1,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'comptable_senior',
                'guard_name'  => 'web',
                'module'      => 'comptabilite',
                'label_fr'    => 'Comptable Senior',
                'description' => 'Peut tout faire en comptabilité : écrire, valider, exporter, clôturer. Accès aux données de tous les clients.',
                'level'       => 2,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'chef_comptable',
                'guard_name'  => 'web',
                'module'      => 'comptabilite',
                'label_fr'    => 'Chef Comptable',
                'description' => 'Valide les écritures, gère les exercices comptables et supervise les comptables juniors.',
                'level'       => 2,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'comptable_junior',
                'guard_name'  => 'web',
                'module'      => 'comptabilite',
                'label_fr'    => 'Comptable Junior',
                'description' => 'Saisie des écritures, consultation des comptes et journaux. Ne peut pas valider ni exporter.',
                'level'       => 3,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'agent_paie',
                'guard_name'  => 'web',
                'module'      => 'paie',
                'label_fr'    => 'Agent Paie',
                'description' => 'Gère la paie, les déclarations sociales (CNSS, IRPP) et les contrats de travail.',
                'level'       => 3,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'agent_client',
                'guard_name'  => 'web',
                'module'      => 'crm',
                'label_fr'    => 'Agent Client',
                'description' => 'Gère la relation client : suivi des dossiers, onboarding, assistance au quotidien.',
                'level'       => 3,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'chef_it',
                'guard_name'  => 'web',
                'module'      => 'admin',
                'label_fr'    => 'Chef IT',
                'description' => 'Gère l\'infrastructure technique, les accès, la sécurité et le support.',
                'level'       => 3,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'stagiaire',
                'guard_name'  => 'web',
                'module'      => null,
                'label_fr'    => 'Stagiaire',
                'description' => 'Accès en lecture seule aux données. Peut explorer mais pas modifier. Session tracée.',
                'level'       => 4,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'auditeur',
                'guard_name'  => 'web',
                'module'      => null,
                'label_fr'    => 'Auditeur',
                'description' => 'Accès consultation uniquement. Utilisé pour les audits internes et externes.',
                'level'       => 5,
                'portail'     => 'gel',
            ],

            // ── Portail Entreprise (client) ─────────────────────────
            [
                'name'        => 'entreprise_admin',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Administrateur Entreprise',
                'description' => 'Super-utilisateur côté entreprise. Gère les accès des employés de l\'entreprise et consulte toute la compta.',
                'level'       => 1,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'entreprise_comptable',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Comptable Entreprise',
                'description' => 'Consulte la comptabilité de son entreprise, télécharge les rapports et suit les déclarations.',
                'level'       => 2,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'entreprise_rh',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'RH Entreprise',
                'description' => 'Gère les employés, consulte la paie et les déclarations sociales de son entreprise.',
                'level'       => 2,
                'portail'     => 'entreprise',
            ],

            // ── Portail CPA (particulier) ────────────────────────────
            [
                'name'        => 'cpa_particulier',
                'guard_name'  => 'web',
                'module'      => 'cpa',
                'label_fr'    => 'Particulier CPA',
                'description' => 'Accès à son espace personnel : documents fiscaux, déclarations et suivi.',
                'level'       => 1,
                'portail'     => 'cpa',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => 'web'],
                $roleData
            );
        }

        $count = Role::count();
        $this->command->info("✓ {$count} rôles créés/mis à jour avec succès.");
    }
}
