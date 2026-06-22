# Audit du Module Comptable — Victoire Para

> **Date :** 22 juin 2026
> **Périmètre :** Modèles, Contrôleurs, Routes, Middlewares, Pages Vue, Composants, Migrations, Seeders
> **Standard :** SYSCOHADA / OHADA — Plan comptable des États membres de l'OHADA

---

## Résumé Exécutif

| Domaine | Statut | Problèmes |
|---|---|---|
| Modèles (7+) | ✅ Lu | 1 anomalie interface, 1 duplication |
| Services (5) | ✅ Lu | OK global |
| Contrôleurs GEL (7) | ✅ Lu | 2 CRITICAL, 3 HIGH |
| Contrôleurs Company (4) | ✅ Lu | 2 CRITICAL, 2 HIGH |
| Routes | ✅ Lu | GEL OK, Company OK |
| Pages Vue GEL (9) | ✅ Lu | CSRF manquant sur certains fetch |
| Pages Vue Company (3) | ✅ Lu | OK |
| Composants (6) | ✅ Lu | OK |
| Migrations (19) | ✅ Lu | 3 anomalies |
| Seeders (2+1) | ✅ Lu | 1 fix déjà appliqué (RLS) |

---

## 🔴 CRITIQUES — Corrections immédiates requises

### C1. TVA Compute — Mauvaise période utilisée

**Fichier :** `app/Http/Controllers/Company/CompanyAccountingController.php`
**Méthode :** `computeTva()`

**Problème :** La méthode utilise `$fiscalYear->date_start` et `$fiscalYear->date_end` pour filtrer les écritures, quelle que soit la période mensuelle/trimestrielle sélectionnée par l'utilisateur.

```php
// Actuel — calcule TOUJOURS sur l'exercice complet
->whereBetween('entry_date', [$fiscalYear->date_start, $fiscalYear->date_end])
```

**Impact :** Une déclaration TVA du mois de mars 2025 inclut TOUTES les écritures de l'année 2025 — montants totalement faux, rejet par le fisc, amende pour déclaration erronée.

**Correction :** Utiliser `$request->period` (format `YYYY-MM`) pour calculer la période exacte :

```php
$periodStart = $request->period . '-01';
$periodEnd = date('Y-m-t', strtotime($periodStart));
->whereBetween('entry_date', [$periodStart, $periodEnd])
```

---

### C2. TVA Compute — Même bug dans le contrôleur GEL

**Fichier :** `app/Http/Controllers/Gel/Accounting/TaxDeclarationController.php`
**Méthode :** `compute()` ou méthode de calcul TVA

**Problème :** Même pattern — utilisation des dates fiscales annuelles au lieu de la période déclarative.

**Correction :** Appliquer le même correctif que C1.

---

### C3. CSRF Token absent sur certaines opérations Fetch

**Fichiers :**
- `resources/js/Pages/Gel/Accounting/Budgets/Index.vue` (lignes ~55-68)
- `resources/js/Pages/Gel/Accounting/Closing/Index.vue` (lignes ~46-49, ~64-67)

**Problème :** Les appels `fetch()` pour les opérations POST/PUT/DELETE n'incluent pas le token CSRF dans l'en-tête `X-CSRF-TOKEN`.

**Impact :** Sur les sessions où Sanctum/Session n'injecte pas automatiquement le token (selon la config CORS/Sanctum), ces requêtes échouent avec `419 Page Expired`.

**Correction :** Ajouter `'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')` dans les headers de tous les fetch mutatifs.

---

### C4. RLS Context Persistence — Risque de fuite multi-tenant

**Fichier :** `database/seeders/DaeDemoSeeder.php`

**Problème :** Le seeder pose `SET app.client_id = 'X'` en début d'exécution. Si une exception survient avant le `SET app.client_id = '0'` de fin, le contexte RLS reste sur le mauvais `client_id` pour la suite du processus.

**Fix partiel :** `SyscohadaChartSeeder::createForClient()` a déjà reçu un correctif pour positionner `app.client_id = '0'` avant de lire les comptes de base.

**Correction complète recommandée :** Systématiser le pattern :

```php
DB::statement("SET app.client_id = '{$targetId}'");
try {
    // opérations
} finally {
    DB::statement("SET app.client_id = '0'"); // reset au contexte super-admin
}
```

---

### C5. Aucune vérification d'appartenance client

**Fichier :** `app/Http/Controllers/Company/CompanyAccountingController.php` (et autres contrôleurs Company)

**Problème :** Les contrôleurs Company utilisent `Auth::user()->client_id` sans vérifier que l'utilisateur authentifié a bien le droit d'accéder à ce client. Un utilisateur malveillant pourrait manipuler une requête pour accéder aux données d'un autre client (si une route accepte un paramètre `clientId`).

**Correction :** Ajouter une Policy ou un middleware de vérification sur chaque endpoint :

```php
public function authorizeClient(?int $clientId = null): int
{
    $user = Auth::user();
    $targetClient = $clientId ?? $user->client_id;
    if ($user->client_id !== $targetClient && !$user->isSuperAdmin()) {
        abort(403, 'Accès non autorisé à ce client.');
    }
    return $targetClient;
}
```

---

## 🟠 HAUTE — À corriger avant mise en production

### H1. Absence de transactions ACID sur écritures multi-tables

**Fichier :** Plusieurs contrôleurs

**Problème :** Les opérations qui écrivent dans plusieurs tables (journal + lignes, validation + séquence, clôture + écritures) ne sont pas enveloppées dans `DB::transaction()`.

**Exemples :**
- `JournalController::store()` — crée le journal puis ses lignes
- `JournalController::post()` — valide le journal, met à jour le statut, incrémente la séquence
- `ClosingEntryController::validate()` — crée les écritures, met à jour les comptes

**Impact :** En cas d'erreur à mi-parcours, les données restent dans un état incohérent (lignes sans en-tête, écritures sans référence séquentielle).

---

### H2. Import CSV — Aucune validation des données

**Fichier :** `CompanyAccountingController::importAccounts()`

**Problème :** L'import CSV ne valide pas rigoureusement :
- L'existence des colonnes attendues
- Le format des codes comptables
- Les types autorisés
- Les doublons potentiels

**Correction :** Ajouter une validation via un Form Request dédié avec règles sur le fichier CSV, et valider chaque ligne avant insertion.

---

### H3. Incohérence des types de comptes — Trois systèmes coexistants

**Problème :** Le module utilise trois systèmes de typage différents pour les comptes :

| Système | Utilisé par | Exemple |
|---|---|---|
| Anglais (migration originale) | `accounting_accounts.type` DB | `asset`, `liability`, `equity`, `revenue`, `expense` |
| Français (GEL) | Contrôleurs GEL, pages GEL | `actif`, `passif`, `charge`, `produit`, `tresorerie` |
| Classes SYSCOHADA (Company) | Company SPA | `'1'` à `'9'` |

**Impact :** Confusion, bugs potentiels de filtrage, maintenance complexe.

**Correction recommandée :** Standardiser sur les classes SYSCOHADA (`syscohada_class`) comme type canonique. L'ancienne colonne `type` devient un champ calculé/lu uniquement. Ajouter une migration pour convertir les valeurs.

---

### H4. Pagination absente sur les rapports

**Endpoints concernés :**
- `GET /api/company/accounting/reports/balance`
- `GET /api/company/accounting/reports/grand-livre`
- `GET /api/company/accounting/reports/bilan`
- `GET /api/company/accounting/reports/resultat`

**Problème :** Aucune pagination. Pour un client avec des milliers de comptes/lignes, la réponse JSON peut être très volumineuse (timeout, mémoire).

---

### H5. Journal Types non synchronisés

**Migration 000004 :** Types : `achat`, `vente`, `banque`, `caisse`, `od`
**Migration 133002 :** Types ajoutés : `operations_diverses`, `salaire`, `investissement`, `anouveaux`, `paie`, `hav`
**Company SPA :** Utilise `operations_diverses`, `salaire`, `investissement`
**GEL JournalController :** Peut ne pas gérer tous les nouveaux types

**Correction :** Vérifier et synchroniser la gestion des types dans tous les contrôleurs (validateurs, switches/case).

---

## 🟡 MOYENNE — Améliorations de robustesse

### M1. Duplication des tables analytiques

Deux ensembles de tables coexistent :

| Migration | Tables | Liens |
|---|---|---|
| `133004_add_accounting_integration_columns` | `accounting_cost_centers` | Liée via `cost_center_id` sur `accounting_journal_lines` |
| `100925_create_analytical_accounting_tables` | `cost_centers`, `analytic_lines` | Liée via `analytic_lines.journal_line_id` |

**Impact :** Deux systèmes analytiques parallèles, confusion sur lequel utiliser.

**Correction :** Déprécier un des deux systèmes (recommander de garder `accounting_cost_centers` + colonne directe sur lignes car plus simple).

---

### M2. Dualité TVA — Deux modèles pour la TVA

| Modèle | Table | Utilisé par |
|---|---|---|
| `AccountingTaxDeclaration` | `accounting_tax_declarations` | GEL (TaxDeclarationController) |
| `TvaDeclaration` | `tva_declarations` | Company (CompanyAccountingController) |

**Impact :** Les déclarations TVA ne sont pas partagées entre GEL et Company. Un client voit des données différentes selon l'interface.

---

### M3. Soft Deletes non uniformes

| Table | Soft Delete | 
|---|---|
| `accounting_budgets` | ✅ |
| `accounting_budget_lines` | ✅ |
| `accounting_closing_entries` | ✅ |
| `accounting_tax_declarations` | ✅ |
| `accounting_cost_centers` | ✅ |
| `fiscal_years` | ❌ |
| `accounting_journals` | ❌ |
| `accounting_journal_lines` | ❌ |
| `fixed_assets` | ❌ |
| `tva_declarations` | ❌ |
| `bank_reconciliations` | ❌ |

**Recommandation :** Ajouter `softDeletes()` sur les tables critiques (`fiscal_years`, `accounting_journals`, `fixed_assets`, `bank_reconciliations`).

---

### M4. Messages d'erreur bruts exposés

Plusieurs contrôleurs attrapent des exceptions et renvoient `$e->getMessage()` au client. Cela peut exposer :
- Des informations sur la structure DB
- Des chemins de fichiers
- Des détails d'implémentation

**Correction :** Logger l'exception complète et retourner un message générique.

---

### M5. Index manquants

| Table | Colonne | Raison |
|---|---|---|
| `accounting_journals` | `client_id` | Filtré par client sur chaque requête |
| `accounting_journals` | `entry_date` | Filtré par date dans tous les rapports |
| `accounting_journal_lines` | `account_id` | Jointure sur compte dans balance/bilan |
| `fixed_assets` | `client_id` (sans `status`) | Index existant inclut status, pas seul |
| `bank_reconciliations` | `period` | Recherché par période |

---

## 🟢 BASSE — Cosmétiques / Évolutions

### B1. Préfixes de migration incohérents

- `2025_01_01_00000x` — migrations originales
- `2026_06_14_13300x` — correctifs et nouvelles tables
- `2026_06_18_00000x` — nouveaux modules
- `2026_06_19_100925` — analytique

Les dates ne reflètent pas l'ordre réel d'exécution (les fix migrations 13300x doivent passer après les 00000x correspondantes mais avant les 00000x du 18). Fonctionne car les horodatages sont croissants, mais prête à confusion.

### B2. Pas de rate limiting sur les endpoints API

Aucun middleware `throttle` appliqué. Risque limité en intra-entreprise mais peut être pertinent pour les endpoints d'import/export.

### B3. Typage `unsignedBigInteger` au lieu de `foreignId`

Plusieurs colonnes `client_id` utilisent `unsignedBigInteger('client_id')` sans contrainte de clé étrangère. Devrait être `foreignId('client_id')->constrained('clients')`.

Trouvé dans :
- `accounting_accounts` (migration originale)

### B4. Colonne `source_module` non utilisée

La colonne `source_module` sur `accounting_journals` est ajoutée dans la fix migration mais n'est jamais renseignée par aucun contrôleur ou service.

---

## Architecture Globale

```
┌─────────────────────────────────────────────────────────────┐
│                    GEL (Cabinet)                            │
│  /api/accounting/*                                         │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Controllers:                                           │ │
│  │  - Gel/Accounting/DashboardController                  │ │
│  │  - Gel/Accounting/JournalController                    │ │
│  │  - Gel/Accounting/AccountController                    │ │
│  │  - Gel/Accounting/ReportController                     │ │
│  │  - Gel/Accounting/TaxDeclarationController             │ │
│  │  - Gel/Accounting/BudgetController                     │ │
│  │  - Gel/Accounting/ClosingController                    │ │
│  └────────────────────────────────────────────────────────┘ │
│  Pages Vue: 9 pages séparées                               │
│  Account types: Français (actif, passif...)                 │
├─────────────────────────────────────────────────────────────┤
│                Company (Entreprise)                         │
│  /api/company/accounting/*                                  │
│  /api/company/tva/*                                         │
│  /api/company/fiscal-years/*                                │
│  /api/company/fixed-assets/*                                │
│  /api/company/bank-reconciliations/*                        │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Controllers:                                           │ │
│  │  - Company/CompanyAccountingController                 │ │
│  │  - Company/FiscalYearController                        │ │
│  │  - Company/TvaController (si dédié)                    │ │
│  └────────────────────────────────────────────────────────┘ │
│  Pages Vue: 1 SPA (Accounting.vue) — 11 tabs                │
│  Account types: Classes SYSCOHADA (1-9)                     │
└─────────────────────────────────────────────────────────────┘
```

### Flux de données

```
Client (request) → Middleware Auth → Controller → Service (si appelé)
    → Validation (FormRequest ou validate())
    → Model (Eloquent) → DB (PostgreSQL + RLS)
    → Response JSON (API) → Vue Page
```

---

## Plan de Correction Prioritaire

### Phase 1 — 🔴 CRITIQUE (immédiat, avant déploiement)

| # | Tâche | Effort | Fichier |
|---|---|---|---|
| 1 | Corriger TVA compute période (C1) | 1h | CompanyAccountingController |
| 2 | Corriger TVA compute GEL (C2) | 1h | TaxDeclarationController |
| 3 | Ajouter CSRF tokens sur fetch GEL (C3) | 30min | Budgets/Index.vue, Closing/Index.vue |
| 4 | Try/finally sur RLS context (C4) | 1h | DaeDemoSeeder + seeders |
| 5 | Middleware auth client (C5) | 2h | Company controllers |

### Phase 2 — 🟠 HAUTE (avant mise en production)

| # | Tâche | Effort | Fichier |
|---|---|---|---|
| 6 | Wrapper transactions ACID (H1) | 2h | JournalController + ClosingController |
| 7 | Valider import CSV (H2) | 2h | CompanyAccountingController |
| 8 | Unifier types de comptes (H3) | 4h | Migrations + Contrôleurs GEL |
| 9 | Ajouter pagination rapports (H4) | 2h | ReportController |
| 10 | Synchroniser types journaux (H5) | 1h | JournalController |

### Phase 3 — 🟡 MOYENNE (post-déploiement)

| # | Tâche | Effort |
|---|---|---|
| 11 | Déprécier un système analytique (M1) | 3h |
| 12 | Fusionner TvaDeclaration et AccountingTaxDeclaration (M2) | 4h |
| 13 | Uniformiser softDeletes (M3) | 2h |
| 14 | Cacher messages d'erreur bruts (M4) | 1h |
| 15 | Ajouter indexes manquants (M5) | 1h |

### Phase 4 — 🟢 BASSE (backlog)

| # | Tâche | Effort |
|---|---|---|
| 16 | Uniformiser préfixes migrations (B1) | 2h |
| 17 | Ajouter rate limiting (B2) | 1h |
| 18 | Remplacer unsignedBigInteger par foreignId (B3) | 1h |
| 19 | Implémenter source_module (B4) | 2h |

---

## Statistiques du Module

| Métrique | Valeur |
|---|---|
| Modèles | 10+ (AccountingAccount, AccountingJournal, AccountingJournalLine, FiscalYear, FixedAsset, DepreciationSchedule, TvaDeclaration, BankReconciliation, AccountingBudget, AccountingBudgetLine, AccountingClosingEntry, AccountingTaxDeclaration, AccountingJournalSequence, AccountingCostCenter, AccountingLettrage, CostCenter, AnalyticLine) |
| Contrôleurs | 11 (7 GEL + 4 Company) |
| Routes API | ~50 endpoints |
| Pages Vue | 12 pages |
| Composants | 6 |
| Migrations | 19 |
| Seeders | 3 (SyscohadaChart + AccountingDemo + DaeDemo) |

---

## Conformité SYSCOHADA

| Critère | Statut | Notes |
|---|---|---|
| Classes 1-9 | ✅ Supporté | `syscohada_class` column |
| Plan comptable de référence | ✅ | `is_syscohada=true` pour comptes standards |
| Duplication par client | ✅ | `SyscohadaChartSeeder::createForClient()` |
| Hiérarchie parent-enfant | ✅ | `parent_id` sur `accounting_accounts` |
| TVA par compte | ✅ | `tva_rate` + `has_tva` |
| Bilan SYSCOHADA | ✅ | ReportController::bilan() |
| Résultat SYSCOHADA | ✅ | ReportController::resultat() |
| Grand livre | ✅ | ReportController::grandLivre() |
| Balance | ✅ | ReportController::balance() |
| Lettrage | ✅ | `accounting_lettrages` |
| Comptes 47 (régularisation) | 🔶 Partiel | Utilisé dans seeders, pas d'UI dédiée |
| Amortissements | ✅ | FixedAsset + DepreciationSchedule |

---

*Document généré le 22/06/2026 — Audit complet du module comptable Victoire Para.*
