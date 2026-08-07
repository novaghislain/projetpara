<?php
// =============================================================================
// FICHIER : DatabaseSeeder.php
// RÔLE    : Seeder principal — Initialise les données de démonstration
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// ⛑  SÉCURITÉ : Ce seeder est BLOQUÉ en environnement de production.
//     Il crée des comptes de démonstration avec des mots de passe faibles
//     et ne doit JAMAIS être exécuté sur un serveur de production.
//
// Ordre d'exécution :
//   1. Rôles & permissions (indispensable pour les utilisateurs)
//   2. Référentiels (services, domaines, pôles, plan comptable)
//   3. Données ERP (catégories, articles, entrepôts, employés)
//   4. Données démo (entreprises, dossiers, documents)
//   5. Modules métier (DAE, juridique, RH, compta, IT, tontines)
//   6. Utilisateurs finaux
// =============================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ⛔  SÉCURITÉ : ne JAMAIS exécuter les seeders de démo en production
        if (app()->environment('production')) {
            $this->command?->error('⛔ Les seeders ne doivent PAS être exécutés en environnement de production.');
            $this->command?->error('   Ils créent des comptes de démonstration avec des mots de passe faibles.');
            return;
        }
        // Le seeder de rôles et permissions DOIT être appelé avant AdminSeeder
        // car les utilisateurs Admin et Company Admin ont besoin des rôles.
        $this->call([
            // ─── ACL : Rôles & Permissions (Spatie) ────────────────────
            RoleSeeder::class,
            PermissionSeeder::class,

            // ─── Référentiels ──────────────────────────────────────────
            PoleSeeder::class,
            AdminSeeder::class,
            ServiceSeeder::class,
            BusinessDomainSeeder::class,
            SyscohadaChartSeeder::class,
            AccountingAccountSeeder::class,
            ErpCategorySeeder::class,
            ErpItemSeeder::class,
            ErpWarehouseSeeder::class,
            ErpEmployeeSeeder::class,
            ErpBankAccountSeeder::class,
            PlanComptableSyscohadaSeeder::class,
            UsersSeeder::class,

            // ─── GEL Cabinet ─────────────────────────────────────────
            \Database\Seeders\Gel\AccountTypesSeeder::class,
            \Database\Seeders\Gel\PlanComptableSyscohadaSeeder::class,
            \Database\Seeders\Gel\GelDemoSeeder::class,
        ]);
    }
}
