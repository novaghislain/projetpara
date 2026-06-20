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
                'saisir'       => 'Saisir des écritures comptables',
                'exporter'     => 'Exporter les écritures comptables',
                'rapports'     => 'Consulter les rapports comptables',
            ],
            'facturation' => [
                'lire'           => 'Lire les factures',
                'creer_facture'  => 'Créer des factures',
                'modifier_facture' => 'Modifier des factures',
                'annuler_facture' => 'Annuler des factures',
                'imprimer_facture' => 'Imprimer des factures',
                'regler'         => 'Enregistrer un règlement',
                'parametres'     => 'Configurer les paramètres de facturation',
            ],
            'caisse' => [
                'lire'          => 'Consulter la caisse',
                'encaissement'  => 'Effectuer un encaissement',
                'decaissement'  => 'Effectuer un décaissement',
                'historique'    => "Consulter l'historique des transactions",
                'rapports'      => 'Consulter les rapports de caisse',
                'cloture'       => 'Clôturer la caisse',
            ],
            'juridique' => [
                'lire'             => 'Consulter les dossiers juridiques',
                'consultation'     => 'Consulter les dossiers juridiques',
                'creation_dossiers' => 'Créer des dossiers juridiques',
                'modification'     => 'Modifier les dossiers juridiques',
                'archivage'        => 'Archiver des dossiers juridiques',
                'suppression'      => 'Supprimer des dossiers juridiques',
            ],
            'rh' => [
                'lire'            => 'Consulter les fiches employés',
                'lecture'         => 'Consulter les fiches employés',
                'creation'        => 'Créer des fiches employés',
                'modification'    => 'Modifier les fiches employés',
                'suppression'     => 'Supprimer les fiches employés',
                'valider_paie'    => 'Valider les fiches de paie',
                'approuver_conge' => 'Approuver les demandes de congé',
                'approuver_frais' => 'Approuver les notes de frais',
                'paie'            => 'Gérer la paie',
                'recrutement'     => 'Gérer les recrutements',
            ],
            'projets' => [
                'lire'         => 'Consulter les projets',
                'lecture'      => 'Consulter les projets',
                'creation'     => 'Créer des projets',
                'modification' => 'Modifier les projets',
                'suppression'  => 'Supprimer les projets',
                'achevement'   => 'Achever / clôturer des projets',
                'taches'       => 'Gérer les tâches',
            ],
            'document' => [
                'lire'         => 'Consulter les documents',
                'lecture'      => 'Consulter les documents',
                'upload'       => 'Téléverser des documents',
                'modification' => 'Modifier les documents',
                'suppression'  => 'Supprimer les documents',
                'partage'      => 'Partager des documents',
            ],
            'dae' => [
                'lire'       => 'Lire les données du module DAE',
                'creer'      => 'Créer des enregistrements DAE',
                'modifier'   => 'Modifier les enregistrements DAE',
                'supprimer'  => 'Supprimer des enregistrements DAE',
                'traiter'    => 'Traiter les courriers et emails',
                'archiver'   => 'Archiver des documents DAE',
                'exporter'   => 'Exporter les données DAE',
                'assigner'   => 'Assigner des tâches DAE',
                'valider'    => "Valider des éléments de conformité",
                'programmer' => 'Programmer des événements agenda',
            ],
            'erp' => [
                'lire'          => 'Consulter le stock ERP',
                'creer'         => 'Créer des articles',
                'modifier'      => 'Modifier des articles',
                'supprimer'     => 'Supprimer des articles',
                'stock_entree'  => 'Enregistrer une entrée de stock',
                'stock_sortie'  => 'Enregistrer une sortie de stock',
                'inventaire'    => 'Effectuer un inventaire',
                'rapports'      => 'Consulter les rapports ERP',
                'commandes'     => 'Gérer les commandes fournisseurs',
            ],
            'crm' => [
                'lire'           => 'Consulter les contacts CRM',
                'creer'          => 'Créer des contacts',
                'modifier'       => 'Modifier des contacts',
                'supprimer'      => 'Supprimer des contacts',
                'devis'          => 'Gérer les devis',
                'relances'       => 'Gérer les relances',
                'campagnes'      => 'Gérer les campagnes',
                'rapports'       => 'Consulter les rapports CRM',
            ],
            'it_helpdesk' => [
                'lire'         => 'Consulter les tickets',
                'creer'        => 'Créer des tickets',
                'repondre'     => 'Répondre aux tickets',
                'resoudre'     => 'Résoudre les tickets',
                'assigner'     => 'Assigner les tickets',
                'rapports'     => 'Consulter les rapports Helpdesk',
            ],
            'it_assets' => [
                'lire'         => 'Consulter les actifs IT',
                'creer'        => 'Créer des actifs IT',
                'modifier'     => 'Modifier des actifs IT',
                'supprimer'    => 'Supprimer des actifs IT',
                'assigner'     => 'Assigner des actifs',
                'maintenance'  => 'Gérer la maintenance',
            ],
            'commerce' => [
                'lire'             => 'Consulter le module Commerce',
                'consulter'        => 'Consulter le module Commerce',
                'creer_produit'    => 'Créer des produits',
                'modifier_produit' => 'Modifier des produits',
                'supprimer_produit'=> 'Supprimer des produits',
                'gerer_stock'      => 'Gérer les stocks',
                'gerer_categories' => 'Gérer les catégories',
                'gerer_fournisseurs'=> 'Gérer les fournisseurs',
                'vendre'           => 'Effectuer des ventes',
                'rembourser'       => 'Effectuer des remboursements',
                'voir_prix_achat'  => 'Voir les prix d\'achat',
                'voir_marges'      => 'Voir les marges',
                'gerer_inventaire' => 'Gérer les inventaires',
                'ouvrir_caisse'    => 'Ouvrir/fermer la caisse',
                'voir_rapports'    => 'Consulter les rapports',
                'exporter'         => 'Exporter les données',
                'gerer_utilisateurs'=> 'Gérer les utilisateurs commerciaux',
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
                'name' => 'Comptable Cabinet',
                'slug' => 'comptable',
                'description' => 'Accès à la comptabilité, facturation, trésorerie des clients qui lui sont assignés.',
                'level' => 50,
                'is_system' => true,
            ],
            [
                'name' => 'Administrateur Entreprise',
                'slug' => 'company_admin',
                'description' => 'Gère les utilisateurs, les rôles et les modules de son entreprise.',
                'level' => 40,
                'is_system' => true,
            ],
            [
                'name' => 'Manager Entreprise',
                'slug' => 'company_manager',
                'description' => 'Gère les opérations quotidiennes. Accès à la plupart des modules en lecture/écriture.',
                'level' => 30,
                'is_system' => true,
            ],
            [
                'name' => 'Employé Entreprise',
                'slug' => 'company_employee',
                'description' => 'Accès limité aux modules assignés en lecture seule ou actions spécifiques.',
                'level' => 20,
                'is_system' => true,
            ],
            [
                'name' => 'Client',
                'slug' => 'client',
                'description' => 'Accès au portail CPA : consultation des documents et factures le concernant.',
                'level' => 10,
                'is_system' => true,
            ],
            // Rôles spécialisés existants
            [
                'name' => 'Caissier',
                'slug' => 'caissier',
                'description' => 'Accès au module de caisse (encaissements, décaissements).',
                'level' => 20,
                'is_system' => true,
            ],
            [
                'name' => 'Juriste',
                'slug' => 'juriste',
                'description' => 'Accès au module juridique (dossiers, archivage).',
                'level' => 20,
                'is_system' => true,
            ],
            [
                'name' => 'Gestionnaire RH',
                'slug' => 'rh',
                'description' => 'Accès au module RH (fiches employés, paie).',
                'level' => 20,
                'is_system' => true,
            ],
            [
                'name' => 'Gestionnaire de projets',
                'slug' => 'gestionnaire_projet',
                'description' => 'Accès au module de gestion de projets.',
                'level' => 20,
                'is_system' => true,
            ],
            [
                'name' => 'Secrétaire',
                'slug' => 'secretaire',
                'description' => 'Accès au module DAE (Direction Administrative Externalisée).',
                'level' => 20,
                'is_system' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // ─── 4. Remplir les permissions par rôle ────────────────────────

        // Helper pour récupérer les IDs par module
        $allPerms = collect($permissionIds)->flatten()->values()->all();

        // Super Admin → toutes les permissions
        $this->syncPermissions('super_admin', $allPerms);

        // Comptable Cabinet → comptabilité + facturation + document
        $comptablePerms = array_merge(
            array_values($permissionIds['comptabilite'] ?? []),
            array_values($permissionIds['facturation'] ?? []),
            array_values($permissionIds['caisse'] ?? ['lire' => null, 'historique' => null, 'rapports' => null]),
            [$permissionIds['comptabilite']['lire'] ?? null, $permissionIds['comptabilite']['saisir'] ?? null,
             $permissionIds['comptabilite']['exporter'] ?? null, $permissionIds['comptabilite']['rapports'] ?? null,
             $permissionIds['facturation']['lire'] ?? null, $permissionIds['facturation']['creer_facture'] ?? null,
             $permissionIds['facturation']['imprimer_facture'] ?? null,
             $permissionIds['caisse']['lire'] ?? null, $permissionIds['caisse']['historique'] ?? null,
             $permissionIds['caisse']['rapports'] ?? null,
             $permissionIds['document']['lire'] ?? null]
        );
        $this->syncPermissions('comptable', $comptablePerms);

        // Admin entreprise → toutes les permissions (scope limité à son entreprise)
        $this->syncPermissions('company_admin', $allPerms);

        // Manager entreprise → presque tout sauf valider/paramètres critiques
        $managerPerms = [];
        foreach (['comptabilite', 'facturation', 'caisse', 'juridique', 'rh', 'projets', 'document', 'dae', 'erp', 'crm', 'it_helpdesk', 'it_assets'] as $mod) {
            foreach ($permissionIds[$mod] ?? [] as $action => $id) {
                // Exclure valider_comptable + parametres facturation + paie
                if (in_array($action, ['valider', 'parametres', 'paie'])) continue;
                $managerPerms[] = $id;
            }
        }
        $this->syncPermissions('company_manager', $managerPerms);

        // Employé entreprise → lecture surtout
        $employeePerms = [];
        $readActions = ['lire', 'lecture', 'consultation', 'historique'];
        foreach (['comptabilite', 'facturation', 'caisse', 'juridique', 'rh', 'projets', 'document', 'dae'] as $mod) {
            foreach ($permissionIds[$mod] ?? [] as $action => $id) {
                if (in_array($action, $readActions)) {
                    $employeePerms[] = $id;
                }
            }
        }
        $this->syncPermissions('company_employee', $employeePerms);

        // Client → seulement consultation des documents et factures le concernant
        $clientPerms = array_merge(
            [$permissionIds['document']['lire'] ?? null],
            [$permissionIds['facturation']['lire'] ?? null],
        );
        $this->syncPermissions('client', array_filter($clientPerms));

        // Caissier → caisse uniquement
        $caissierPerms = array_values($permissionIds['caisse'] ?? []);
        $this->syncPermissions('caissier', $caissierPerms);

        // Juriste → juridique uniquement
        $juristePerms = array_values($permissionIds['juridique'] ?? []);
        $this->syncPermissions('juriste', $juristePerms);

        // RH → rh uniquement
        $rhPerms = array_values($permissionIds['rh'] ?? []);
        $this->syncPermissions('rh', $rhPerms);

        // Gestionnaire projet → projets uniquement
        $projetPerms = array_values($permissionIds['projets'] ?? []);
        $this->syncPermissions('gestionnaire_projet', $projetPerms);

        // Secrétaire → DAE uniquement
        $daePerms = array_values($permissionIds['dae'] ?? []);
        $this->syncPermissions('secretaire', $daePerms);
    }

    /**
     * Synchroniser les permissions d'un rôle.
     */
    private function syncPermissions(string $slug, array $permissionIds): void
    {
        $role = Role::where('slug', $slug)->first();
        if (!$role) return;

        $validIds = array_filter($permissionIds);
        if (!empty($validIds)) {
            $role->permissions()->syncWithoutDetaching($validIds);
        }
    }
}
