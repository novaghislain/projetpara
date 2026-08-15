<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * 15 rôles répartis sur 3 portails.
     *
     * Portail GEL (cabinet)     : super_admin, gestionnaire_cabinet, comptable_senior,
     *                             chef_comptable, comptable_junior, agent_paie,
     *                             agent_client, chef_it, stagiaire, auditeur, fiscaliste
     * Portail Entreprise (client): entreprise_admin, entreprise_comptable, entreprise_rh
     * Portail CPA (particulier)  : cpa_particulier
     */
    public function run(): void
    {
        // Désactiver le cache du temps d'exécution
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            // ── Portail Plateforme (GEL) ──────────────────────────────
            [
                'name'        => 'super_admin',
                'guard_name'  => 'web',
                'module'      => null,
                'label_fr'    => 'Super Administrateur',
                'description' => 'Contrôle total de la plateforme.',
                'level'       => 100,
                'portail'     => 'gel',
            ],
            // ── Portail Cabinet ───────────────────────────────────────
            [
                'name'        => 'director',
                'guard_name'  => 'web',
                'module'      => 'admin',
                'label_fr'    => 'Directeur de Cabinet',
                'description' => 'Dirige le cabinet, affecte les responsables de pôles et les fiscalistes.',
                'level'       => 90,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'pole_responsible',
                'guard_name'  => 'web',
                'module'      => 'admin',
                'label_fr'    => 'Responsable de Pôle',
                'description' => 'Supervise un pôle et affecte/supervise les comptables.',
                'level'       => 60,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'fiscaliste',
                'guard_name'  => 'web',
                'module'      => 'fiscalite',
                'label_fr'    => 'Fiscaliste',
                'description' => 'Valide les règles fiscales du cabinet.',
                'level'       => 55,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'comptable',
                'guard_name'  => 'web',
                'module'      => 'comptabilite',
                'label_fr'    => 'Comptable',
                'description' => 'En charge de dossiers clients, affecte les collaborateurs et secrétaires.',
                'level'       => 50,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'collaborator',
                'guard_name'  => 'web',
                'module'      => 'comptabilite',
                'label_fr'    => 'Collaborateur',
                'description' => 'Saisie et gestion comptable sous la supervision du comptable.',
                'level'       => 45,
                'portail'     => 'gel',
            ],
            [
                'name'        => 'secretaire',
                'guard_name'  => 'web',
                'module'      => 'crm',
                'label_fr'    => 'Secrétaire',
                'description' => 'Peut être affectée par un comptable ou invitée par une entreprise.',
                'level'       => 20,
                'portail'     => 'gel',
            ],

            // ── Portail Entreprise ────────────────────────────────────
            [
                'name'        => 'company_admin',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Administrateur Entreprise',
                'description' => 'Gère l\'entreprise et invite le personnel.',
                'level'       => 40,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'company_manager',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Manager Entreprise',
                'description' => 'Poste de direction invité.',
                'level'       => 30,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'company_employee',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Employé d\'Entreprise',
                'description' => 'Employé générique invité.',
                'level'       => 20,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'caissier',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Caissier',
                'description' => 'Gestion de la caisse invité.',
                'level'       => 20,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'juriste',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Juriste',
                'description' => 'Rôle juridique invité.',
                'level'       => 20,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'rh',
                'guard_name'  => 'web',
                'module'      => 'paie',
                'label_fr'    => 'Ressources Humaines',
                'description' => 'Gestion RH invité.',
                'level'       => 20,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'gestionnaire_projet',
                'guard_name'  => 'web',
                'module'      => 'entreprise',
                'label_fr'    => 'Gestionnaire de Projet',
                'description' => 'Gère les projets invité.',
                'level'       => 20,
                'portail'     => 'entreprise',
            ],
            [
                'name'        => 'auditeur',
                'guard_name'  => 'web',
                'module'      => 'fiscalite',
                'label_fr'    => 'Auditeur',
                'description' => 'Lecture seule, accès temporaire.',
                'level'       => 15,
                'portail'     => 'entreprise',
            ],

            // ── Portail Public / CPA ──────────────────────────────────
            [
                'name'        => 'client',
                'guard_name'  => 'web',
                'module'      => 'cpa',
                'label_fr'    => 'Client (CPA)',
                'description' => 'Accès au portail public et suivi de dossier.',
                'level'       => 10,
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
