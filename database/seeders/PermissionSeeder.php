<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * 40+ permissions réparties en 10 modules.
     *
     * Convention : module.action
     * Portails : gel (interne), entreprise (client), cpa (particulier)
     */
    public function run(): void
    {
        // Vider le cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // ─────────────────────────────────────────────────────────────
        // 1. COMPTABILITÉ (11 permissions)
        // ─────────────────────────────────────────────────────────────
        $comptabilite = [
            ['name' => 'comptabilite.consulter', 'module' => 'comptabilite', 'label_fr' => 'Consulter la comptabilité', 'description' => 'Voir les écritures, journaux, balances et états financiers', 'portail' => 'gel'],
            ['name' => 'comptabilite.ecrire',    'module' => 'comptabilite', 'label_fr' => 'Saisir des écritures',  'description' => 'Créer et modifier des écritures comptables', 'portail' => 'gel'],
            ['name' => 'comptabilite.valider',   'module' => 'comptabilite', 'label_fr' => 'Valider des écritures', 'description' => 'Valider/Approuver les écritures saisies', 'portail' => 'gel'],
            ['name' => 'comptabilite.exporter',  'module' => 'comptabilite', 'label_fr' => 'Exporter la comptabilité', 'description' => 'Exporter en CSV, PDF, SYSCOHADA, etc.', 'portail' => 'gel'],
            ['name' => 'comptabilite.cloturer',  'module' => 'comptabilite', 'label_fr' => 'Clôturer', 'description' => 'Clôturer les exercices comptables', 'portail' => 'gel'],
            ['name' => 'comptabilite.journaux',  'module' => 'comptabilite', 'label_fr' => 'Gérer les journaux', 'description' => 'Configurer et gérer les journaux comptables', 'portail' => 'gel'],
            ['name' => 'comptabilite.balance',   'module' => 'comptabilite', 'label_fr' => 'Balance', 'description' => 'Générer et consulter la balance', 'portail' => 'gel'],
            ['name' => 'comptabilite.bilan',     'module' => 'comptabilite', 'label_fr' => 'Bilan comptable', 'description' => 'Générer et consulter le bilan', 'portail' => 'gel'],
            ['name' => 'comptabilite.resultat',  'module' => 'comptabilite', 'label_fr' => 'Compte de résultat', 'description' => 'Générer et consulter le compte de résultat', 'portail' => 'gel'],
            ['name' => 'comptabilite.analyse',   'module' => 'comptabilite', 'label_fr' => 'Analyse financière', 'description' => 'Analyser les ratios et indicateurs financiers', 'portail' => 'gel'],
            ['name' => 'comptabilite.param',     'module' => 'comptabilite', 'label_fr' => 'Paramètres comptables', 'description' => 'Configurer le plan comptable, les exercices, les devises', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 2. PAIE (6 permissions)
        // ─────────────────────────────────────────────────────────────
        $paie = [
            ['name' => 'paie.consulter',   'module' => 'paie', 'label_fr' => 'Consulter la paie',         'description' => 'Voir les bulletins et données salariales', 'portail' => 'gel'],
            ['name' => 'paie.bulletin',    'module' => 'paie', 'label_fr' => 'Générer les bulletins',     'description' => 'Calculer et éditer les bulletins de paie', 'portail' => 'gel'],
            ['name' => 'paie.decla_cnss',  'module' => 'paie', 'label_fr' => 'Déclarations CNSS',        'description' => 'Générer et soumettre les déclarations CNSS', 'portail' => 'gel'],
            ['name' => 'paie.decla_irpp',  'module' => 'paie', 'label_fr' => 'Déclarations IRPP',        'description' => 'Générer et soumettre les déclarations IRPP', 'portail' => 'gel'],
            ['name' => 'paie.contrat',     'module' => 'paie', 'label_fr' => 'Gérer les contrats',       'description' => 'Créer et modifier les contrats de travail', 'portail' => 'gel'],
            ['name' => 'paie.editer',      'module' => 'paie', 'label_fr' => 'Éditer la paie',           'description' => 'Modifier les éléments de paie avant validation', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 3. FISCALITÉ (6 permissions)
        // ─────────────────────────────────────────────────────────────
        $fiscalite = [
            ['name' => 'fiscalite.consulter',  'module' => 'fiscalite', 'label_fr' => 'Consulter la fiscalité',     'description' => 'Voir les déclarations et obligations fiscales', 'portail' => 'gel'],
            ['name' => 'fiscalite.tva',        'module' => 'fiscalite', 'label_fr' => 'Gérer la TVA',              'description' => 'Calculer et déclarer la TVA', 'portail' => 'gel'],
            ['name' => 'fiscalite.declarer',   'module' => 'fiscalite', 'label_fr' => 'Déclarer les impôts',       'description' => 'Soumettre les déclarations fiscales', 'portail' => 'gel'],
            ['name' => 'fiscalite.suivi',      'module' => 'fiscalite', 'label_fr' => 'Suivi fiscal',             'description' => 'Suivre les échéances et obligations fiscales', 'portail' => 'gel'],
            ['name' => 'fiscalite.e_mecef',    'module' => 'fiscalite', 'label_fr' => 'e-MECeF',                 'description' => 'Gérer les factures normalisées et e-MECeF', 'portail' => 'gel'],
            ['name' => 'fiscalite.optimiser',  'module' => 'fiscalite', 'label_fr' => 'Optimisation fiscale',     'description' => 'Proposer des stratégies d\'optimisation fiscale', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 4. CRM (5 permissions)
        // ─────────────────────────────────────────────────────────────
        $crm = [
            ['name' => 'crm.consulter',  'module' => 'crm', 'label_fr' => 'Consulter le CRM',         'description' => 'Voir les fiches clients et prospects', 'portail' => 'gel'],
            ['name' => 'crm.assigner',   'module' => 'crm', 'label_fr' => 'Assigner des dossiers',   'description' => 'Assigner des clients/dossiers à des agents', 'portail' => 'gel'],
            ['name' => 'crm.relancer',   'module' => 'crm', 'label_fr' => 'Relancer les clients',    'description' => 'Envoyer des relances et communications', 'portail' => 'gel'],
            ['name' => 'crm.documents',  'module' => 'crm', 'label_fr' => 'Gérer les documents',     'description' => 'Ajouter/retirer des documents clients', 'portail' => 'gel'],
            ['name' => 'crm.onboarding', 'module' => 'crm', 'label_fr' => 'Onboarding client',       'description' => 'Gérer le processus d\'intégration des nouveaux clients', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 5. CLIENT (4 permissions)
        // ─────────────────────────────────────────────────────────────
        $client = [
            ['name' => 'client.consulter',  'module' => 'client', 'label_fr' => 'Consulter les clients',     'description' => 'Voir la liste et les fiches clients', 'portail' => 'gel'],
            ['name' => 'client.creer',      'module' => 'client', 'label_fr' => 'Créer des clients',        'description' => 'Ajouter de nouveaux clients au cabinet', 'portail' => 'gel'],
            ['name' => 'client.modifier',   'module' => 'client', 'label_fr' => 'Modifier les clients',     'description' => 'Éditer les informations des clients existants', 'portail' => 'gel'],
            ['name' => 'client.suspendre',  'module' => 'client', 'label_fr' => 'Suspendre des clients',    'description' => 'Suspendre/réactiver un abonnement client', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 6. GED (4 permissions)
        // ─────────────────────────────────────────────────────────────
        $ged = [
            ['name' => 'ged.consulter',  'module' => 'ged', 'label_fr' => 'Consulter la GED',       'description' => 'Parcourir l\'arborescence des documents', 'portail' => 'gel'],
            ['name' => 'ged.telecharger', 'module' => 'ged', 'label_fr' => 'Télécharger',           'description' => 'Télécharger des documents depuis la GED', 'portail' => 'gel'],
            ['name' => 'ged.importer',    'module' => 'ged', 'label_fr' => 'Importer des documents', 'description' => 'Ajouter des fichiers dans la GED', 'portail' => 'gel'],
            ['name' => 'ged.supprimer',   'module' => 'ged', 'label_fr' => 'Supprimer',             'description' => 'Supprimer des documents de la GED', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 7. IA (6 permissions)
        // ─────────────────────────────────────────────────────────────
        $ia = [
            ['name' => 'ia.consulter',      'module' => 'ia', 'label_fr' => 'Consulter l\'IA',              'description' => 'Voir les suggestions et analyses IA', 'portail' => 'gel'],
            ['name' => 'ia.suggestion',     'module' => 'ia', 'label_fr' => 'Recevoir des suggestions',     'description' => 'Activer les suggestions automatiques de l\'IA', 'portail' => 'gel'],
            ['name' => 'ia.analyse',        'module' => 'ia', 'label_fr' => 'Analyse prédictive',           'description' => 'Utiliser les analyses prédictives (cashflow, risques)', 'portail' => 'gel'],
            ['name' => 'ia.rapprochement',  'module' => 'ia', 'label_fr' => 'Rapprochement bancaire IA',    'description' => 'Utiliser le rapprochement bancaire automatique', 'portail' => 'gel'],
            ['name' => 'ia.ocr',            'module' => 'ia', 'label_fr' => 'OCR intelligent',              'description' => 'Utiliser la reconnaissance de documents (OCR)', 'portail' => 'gel'],
            ['name' => 'ia.entrainer',      'module' => 'ia', 'label_fr' => 'Entraîner les modèles',        'description' => 'Configurer et entraîner les modèles IA', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 8. ADMIN (5 permissions)
        // ─────────────────────────────────────────────────────────────
        $admin = [
            ['name' => 'admin.acces',       'module' => 'admin', 'label_fr' => 'Gérer les accès',           'description' => 'Gérer les utilisateurs, rôles et permissions', 'portail' => 'gel'],
            ['name' => 'admin.config',      'module' => 'admin', 'label_fr' => 'Configuration',             'description' => 'Configurer les paramètres généraux du cabinet', 'portail' => 'gel'],
            ['name' => 'admin.logs',        'module' => 'admin', 'label_fr' => 'Consulter les logs',        'description' => 'Voir les journaux d\'audit et d\'activité', 'portail' => 'gel'],
            ['name' => 'admin.securite',    'module' => 'admin', 'label_fr' => 'Sécurité',                 'description' => 'Gérer la sécurité, 2FA, sessions', 'portail' => 'gel'],
            ['name' => 'admin.support',     'module' => 'admin', 'label_fr' => 'Support technique',         'description' => 'Accéder au panneau de support et assistance', 'portail' => 'gel'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 9. ENTREPRISE (4 permissions)
        // ─────────────────────────────────────────────────────────────
        $entreprise = [
            ['name' => 'entreprise.tableau_bord',  'module' => 'entreprise', 'label_fr' => 'Tableau de bord',       'description' => 'Accéder au tableau de bord entreprise', 'portail' => 'entreprise'],
            ['name' => 'entreprise.comptabilite',   'module' => 'entreprise', 'label_fr' => 'Comptabilité',         'description' => 'Consulter la comptabilité de l\'entreprise', 'portail' => 'entreprise'],
            ['name' => 'entreprise.paie',           'module' => 'entreprise', 'label_fr' => 'Paie',                'description' => 'Consulter la paie et les déclarations', 'portail' => 'entreprise'],
            ['name' => 'entreprise.documents',      'module' => 'entreprise', 'label_fr' => 'Documents',           'description' => 'Accéder aux documents de l\'entreprise', 'portail' => 'entreprise'],
        ];

        // ─────────────────────────────────────────────────────────────
        // 10. CPA (3 permissions)
        // ─────────────────────────────────────────────────────────────
        $cpa = [
            ['name' => 'cpa.documents',     'module' => 'cpa', 'label_fr' => 'Mes documents',         'description' => 'Accéder à ses documents personnels', 'portail' => 'cpa'],
            ['name' => 'cpa.declarations',  'module' => 'cpa', 'label_fr' => 'Mes déclarations',      'description' => 'Consulter ses déclarations fiscales', 'portail' => 'cpa'],
            ['name' => 'cpa.profil',        'module' => 'cpa', 'label_fr' => 'Mon profil',            'description' => 'Gérer son profil et ses préférences', 'portail' => 'cpa'],
        ];

        // ─── On fusionne tout ────────────────────────────────────────
        $allPermissions = array_merge(
            $comptabilite, $paie, $fiscalite, $crm,
            $client, $ged, $ia, $admin,
            $entreprise, $cpa
        );

        $createdCount = 0;
        foreach ($allPermissions as $permData) {
            Permission::firstOrCreate(
                ['name' => $permData['name'], 'guard_name' => 'web'],
                [
                    'module'      => $permData['module'],
                    'label_fr'    => $permData['label_fr'],
                    'description' => $permData['description'],
                    'portail'     => $permData['portail'],
                ]
            );
            $createdCount++;
        }

        $this->command->info('✓ ' . $createdCount . ' permissions créées/mises à jour avec succès.');

        // ─── Attribution des permissions aux rôles ───────────────────

        // super_admin : TOUTES les permissions GEL
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $gelPerms = Permission::whereIn('portail', ['gel', null])->pluck('name')->toArray();
            $superAdmin->givePermissionTo($gelPerms);
            $this->command->info('  → super_admin : ' . count($gelPerms) . ' permissions');
        }

        // gestionnaire_cabinet : admin + client + crm (consultation)
        $this->assignToRole('gestionnaire_cabinet', [
            'client.consulter', 'client.creer', 'client.modifier', 'client.suspendre',
            'crm.consulter', 'crm.assigner', 'crm.relancer', 'crm.onboarding',
            'admin.config', 'admin.logs', 'admin.support',
        ]);

        // comptable_senior : TOUTE la compta + fiscalité + export
        $this->assignToRole('comptable_senior', [
            'comptabilite.consulter', 'comptabilite.ecrire', 'comptabilite.valider',
            'comptabilite.exporter', 'comptabilite.cloturer', 'comptabilite.journaux',
            'comptabilite.balance', 'comptabilite.bilan', 'comptabilite.resultat',
            'comptabilite.analyse', 'comptabilite.param',
            'fiscalite.consulter', 'fiscalite.tva', 'fiscalite.declarer',
            'fiscalite.suivi', 'fiscalite.e_mecef',
        ]);

        // chef_comptable : compta sans clôture + validation
        $this->assignToRole('chef_comptable', [
            'comptabilite.consulter', 'comptabilite.ecrire', 'comptabilite.valider',
            'comptabilite.exporter', 'comptabilite.journaux',
            'comptabilite.balance', 'comptabilite.bilan', 'comptabilite.resultat',
            'comptabilite.analyse',
            'fiscalite.consulter', 'fiscalite.tva', 'fiscalite.declarer',
            'fiscalite.suivi',
        ]);

        // comptable_junior : saisie uniquement
        $this->assignToRole('comptable_junior', [
            'comptabilite.consulter', 'comptabilite.ecrire',
            'comptabilite.journaux', 'comptabilite.balance',
        ]);

        // agent_paie : paie complète
        $this->assignToRole('agent_paie', [
            'paie.consulter', 'paie.bulletin', 'paie.decla_cnss',
            'paie.decla_irpp', 'paie.contrat', 'paie.editer',
        ]);

        // agent_client : CRM + client (lecture)
        $this->assignToRole('agent_client', [
            'crm.consulter', 'crm.assigner', 'crm.relancer',
            'crm.documents', 'crm.onboarding',
            'client.consulter', 'client.modifier',
        ]);

        // chef_it : admin technique
        $this->assignToRole('chef_it', [
            'admin.acces', 'admin.config', 'admin.logs',
            'admin.securite', 'admin.support',
            'ged.consulter', 'ged.telecharger',
        ]);

        // stagiaire : lecture seule
        $this->assignToRole('stagiaire', [
            'comptabilite.consulter', 'paie.consulter',
            'fiscalite.consulter', 'crm.consulter',
            'client.consulter', 'ged.consulter',
            'ia.consulter',
        ]);

        // auditeur : consultation compta + fiscal
        $this->assignToRole('auditeur', [
            'comptabilite.consulter', 'comptabilite.balance',
            'comptabilite.bilan', 'comptabilite.resultat',
            'fiscalite.consulter', 'fiscalite.suivi',
            'admin.logs',
        ]);

        // entreprise_admin : toutes les permissions entreprise
        $this->assignToRole('entreprise_admin', [
            'entreprise.tableau_bord', 'entreprise.comptabilite',
            'entreprise.paie', 'entreprise.documents',
        ]);

        // entreprise_comptable : compta entreprise seulement
        $this->assignToRole('entreprise_comptable', [
            'entreprise.tableau_bord', 'entreprise.comptabilite',
            'entreprise.documents',
        ]);

        // entreprise_rh : paie entreprise seulement
        $this->assignToRole('entreprise_rh', [
            'entreprise.tableau_bord', 'entreprise.paie',
            'entreprise.documents',
        ]);

        // cpa_particulier : tout CPA
        $this->assignToRole('cpa_particulier', [
            'cpa.documents', 'cpa.declarations', 'cpa.profil',
        ]);

        $this->command->info('✓ Attributions de permissions terminées.');
    }

    /**
     * Assigne une liste de permissions à un rôle.
     */
    private function assignToRole(string $roleName, array $permissionNames): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $role->givePermissionTo($permissionNames);
            $this->command->info("  → {$roleName} : " . count($permissionNames) . ' permissions');
        }
    }
}
