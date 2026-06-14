# Architecture GEL Cabinet — Plateforme SaaS Multi-Tenant

## 1. Vue d'ensemble

```
┌─────────────────────────────────────────────────────────────────┐
│                      CLIENTS WEB (Vue 3)                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │ Super    │  │ Admin    │  │Employé   │  │ Invité   │        │
│  │ Admin    │  │Client    │  │          │  │          │        │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘        │
│       └──────────────┴─────────────┴──────────────┘             │
│                         API REST (Laravel)                      │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │  Middleware multi-tenant | Auth JWT | RBAC | Audit      │    │
│  │  Modules: GED | Compta | Facturation | RH | ...        │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                           │
┌──────────────────────────┴──────────────────────────────────────┐
│                     POSTGRESQL (RLS Multi-Tenant)               │
│  ┌─────────┐ ┌──────────┐ ┌────────┐ ┌────────┐ ┌──────────┐  │
│  │ Tenants │ │  Users   │ │Modules │ │ Data   │ │  Audit   │  │
│  │(clients)│ │          │ │Services│ │(GED,   │ │  Logs    │  │
│  │         │ │          │ │        │ │Compta) │ │          │  │
│  └─────────┘ └──────────┘ └────────┘ └────────┘ └──────────┘  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │  Row-Level Security : client_id = current_setting(...)  │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                           │
┌──────────────────────────┴──────────────────────────────────────┐
│              SERVICES ANNEXES                                    │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │  Redis   │  │  MinIO   │  │  Queue   │  │  WebSocket│      │
│  │ (Cache)  │  │(Fichiers)│  │ (Jobs)   │  │ (Notifs) │       │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘       │
└─────────────────────────────────────────────────────────────────┘
```

## 2. Architecture Multi-Tenant

### Stratégie : Row-Level Security (RLS) PostgreSQL

Chaque table de données métier possède une colonne `client_id`.
PostgreSQL RLS applique automatiquement `client_id = current_setting('app.client_id')::int`.

```sql
-- Activation RLS sur une table
ALTER TABLE documents ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON documents
    USING (client_id = current_setting('app.client_id')::int);
```

### Isolation stricte
- **Au niveau base de données** : RLS PostgreSQL garantit qu'aucune requête ne traverse les tenants
- **Au niveau application** : Middleware Laravel injecte `client_id` dans la session PostgreSQL
- **Au niveau API** : Validation explicite dans les contrôleurs (double sécurité)

## 3. Modèle de Données

### Tables système (cross-tenant)

```
users                  roles                  permissions
├── id (PK)            ├── id (PK)            ├── id (PK)
├── client_id (FK)     ├── name               ├── module
├── name               ├── slug               ├── action
├── email              ├── level              ├── display_name
├── password           ├── is_system          ├── description
├── role_id (FK)       └── ...                └── ...
├── is_company_admin
├── fonction               role_permission
├── photo                  ├── role_id (FK)
├── is_active              └── permission_id (FK)
└── ...
     licenses              client_settings
     ├── id (PK)           ├── id (PK)
     ├── client_id (FK)    ├── client_id (FK)
     ├── service_id (FK)   ├── key
     ├── license_key       ├── value
     ├── start_date        └── ...
     ├── end_date
     ├── status
     └── ...
```

### Module GED (Gestion Électronique de Documents)

```
document_folders                    documents
├── id (PK)                         ├── id (PK)
├── client_id (FK) *RLS*            ├── client_id (FK) *RLS*
├── parent_id (FK) → self           ├── folder_id (FK)
├── name                            ├── name
├── slug                            ├── original_name
├── path (materialized)             ├── mime_type
├── level (1-4)                     ├── size (bytes)
├── sort_order                      ├── path (storage path)
├── metadata (JSON)                 ├── version
└── ...                             ├── file_hash (SHA256)
                                    ├── tags (JSON)
                                    ├── is_deleted (soft delete)
                                    └── ...

document_versions                   document_audit_log
├── id (PK)                         ├── id (PK)
├── document_id (FK)                ├── document_id (FK)
├── version_number                  ├── user_id (FK)
├── path (storage)                  ├── action (view/edit/delete/restore)
├── size                            ├── metadata (JSON)
├── created_by                      └── created_at
└── created_at
```

### Module Comptabilité

```
accounting_accounts                 accounting_entries
├── id (PK)                         ├── id (PK)
├── client_id (FK) *RLS*            ├── client_id (FK) *RLS*
├── account_number                  ├── journal_id (FK)
├── name                            ├── date
├── type (asset/liability/...)      ├── reference
├── parent_id (FK)                  ├── description
├── is_active                       ├── debit
└── ...                             ├── credit
                                    ├── account_id (FK)
    accounting_journals             ├── is_balanced
    ├── id (PK)                     ├── is_validated
    ├── client_id (FK) *RLS*        └── validated_by
    ├── code (ACH/VTE/BNQ/PAIE)
    ├── label                       accounting_bank_transactions
    ├── period (YYYY-MM)            ├── id (PK)
    ├── status (open/closed)        ├── client_id (FK) *RLS*
    └── ...                         ├── account_id (FK)
                                    ├── date
    accounting_reconciliations      ├── description
    ├── id (PK)                     ├── amount
    ├── client_id (FK) *RLS*        ├── type (debit/credit)
    ├── account_id (FK)             ├── reconciled (boolean)
    ├── period                      ├── reconciled_at
    ├── status                      └── ...
    └── ...
```

### Module Facturation

```
invoices                           invoice_items
├── id (PK)                        ├── id (PK)
├── client_id (FK) *RLS*           ├── invoice_id (FK)
├── number (auto)                  ├── description
├── type (devis/facture/avoir)     ├── quantity
├── status (brouillon/émise/...)   ├── unit_price
├── issue_date                     ├── tax_rate
├── due_date                       ├── total_ht
├── recipient (JSON)               └── total_ttc
├── conditions
├── notes                          invoice_payments
├── total_ht                       ├── id (PK)
├── total_tva                      ├── invoice_id (FK)
├── total_ttc                      ├── date
├── paid_amount                    ├── amount
├── paid (boolean)                 ├── method (cash/transfer/momo/...)
├── sent_at                        ├── reference
├── paid_at                        └── ...
├── pdf_path
└── ...
```

### Module RH

```
hr_employees                       hr_contracts
├── id (PK)                        ├── id (PK)
├── client_id (FK) *RLS*           ├── employee_id (FK)
├── user_id (FK) (optionnel)       ├── type (CDI/CDD/INTERIM)
├── employee_number                 ├── start_date
├── last_name                      ├── end_date
├── first_name                     ├── position
├── email                          ├── salary
├── phone                          ├── status (active/ended)
├── birth_date                     └── ...
├── address
├── social_security_number         hr_payrolls
├── position                       ├── id (PK)
├── department                     ├── client_id (FK) *RLS*
├── hire_date                      ├── employee_id (FK)
├── status (active/suspended/...)  ├── period (YYYY-MM)
├── photo                          ├── gross_salary
└── ...                            ├── deductions (JSON)
                                   ├── net_salary
    hr_leaves                      ├── pdf_path
    ├── id (PK)                    └── ...
    ├── employee_id (FK)
    ├── type (congé/maladie/...)
    ├── start_date
    ├── end_date
    ├── status (pending/approved/refused)
    ├── approved_by
    └── ...
```

### Module Juridique

```
legal_contracts                    legal_litigations
├── id (PK)                        ├── id (PK)
├── client_id (FK) *RLS*           ├── client_id (FK) *RLS*
├── title                          ├── case_number
├── type (contrat/statut/PV/...)   ├── title
├── status (draft/active/expired)  ├── status
├── parties (JSON)                 ├── court
├── start_date                     ├── start_date
├── end_date                       ├── end_date
├── content (PDF/text)             ├── documents (JSON)
├── signed_by_client               ├── assigned_to
├── signed_by_us                   └── ...
├── auto_renewal
├── reminder_days                  legal_compliance
└── ...                            ├── id (PK)
                                   ├── client_id (FK) *RLS*
                                   ├── obligation
                                   ├── deadline
                                   ├── status
                                   ├── assigned_to
                                   └── ...
```

### Module Projets

```
projects                           project_tasks
├── id (PK)                        ├── id (PK)
├── client_id (FK) *RLS*           ├── project_id (FK)
├── name                           ├── parent_id (FK)
├── description                    ├── title
├── status (planning/active/...)   ├── description
├── priority                       ├── assigned_to (FK)
├── start_date                     ├── status (todo/in_progress/...)
├── end_date                       ├── priority
├── budget                         ├── start_date
├── progress (%)                   ├── due_date
├── project_manager (FK)           ├── estimated_hours
└── ...                            ├── actual_hours
                                   ├── sort_order
    project_members                └── ...
    ├── project_id (FK)
    ├── user_id (FK)               project_time_entries
    └── role                       ├── id (PK)
                                   ├── task_id (FK)
                                   ├── user_id (FK)
                                   ├── date
                                   ├── hours
                                   ├── description
                                   └── ...
```

### Module CRM

```
crm_contacts                       crm_deals
├── id (PK)                        ├── id (PK)
├── client_id (FK) *RLS*           ├── client_id (FK) *RLS*
├── company_name                   ├── contact_id (FK)
├── contact_name                   ├── title
├── email                          ├── value
├── phone                          ├── stage (prospection/nego/...)
├── address                        ├── probability (%)
├── website                        ├── expected_close_date
├── sector                         ├── assigned_to
├── source                         └── ...
├── status (lead/prospect/client)
├── assigned_to                    crm_interactions
├── notes                          ├── id (PK)
└── ...                            ├── contact_id (FK)
                                   ├── type (email/call/meeting/...)
                                   ├── subject
                                   ├── content
                                   ├── date
                                   ├── user_id (FK)
                                   └── ...
```

## 4. Structure des Modules

Chaque module est indépendant et activable par licence :

```
app/Http/Controllers/
├── GEL/           (Super Admin - Cabinet)
├── Company/       (Admin Client)
└── Modules/
    ├── Ged/
    ├── Comptabilite/
    ├── Facturation/
    ├── Rh/
    ├── Juridique/
    ├── Projets/
    ├── Crm/
    ├── Communication/
    └── ...

resources/js/Pages/
├── Gel/           (Super Admin)
├── Company/       (Admin Client)
└── Modules/
    ├── Ged/
    ├── Comptabilite/
    ├── Facturation/
    ├── Rh/
    ├── Juridique/
    ├── Projets/
    ├── Crm/
    ├── Communication/
    └── ...
```

## 5. API REST Design

```
/api/v1/
├── auth/              (login, logout, refresh, 2FA)
├── users/             (CRUD utilisateurs)
├── profile/           (profil, photo, préférences)
├── notifications/     (notifications temps réel)
│
├── ged/
│   ├── folders/       (arborescence dossiers)
│   ├── documents/     (upload, download, versioning)
│   └── search/        (recherche full-text)
│
├── accounting/
│   ├── accounts/      (plan comptable)
│   ├── journals/      (journaux)
│   ├── entries/       (écritures)
│   ├── balance/       (balance)
│   ├── ledger/        (grand livre)
│   ├── reports/       (états financiers)
│   └── reconcile/     (conciliation bancaire)
│
├── invoicing/
│   ├── invoices/      (factures, devis, avoirs)
│   ├── payments/      (paiements)
│   └── templates/     (modèles)
│
├── hr/
│   ├── employees/     (employés)
│   ├── contracts/     (contrats)
│   ├── leaves/        (congés)
│   ├── payroll/       (paie)
│   └── expenses/      (notes de frais)
│
├── legal/
│   ├── contracts/     (contrats juridiques)
│   ├── litigations/   (contentieux)
│   └── compliance/    (conformité)
│
├── projects/          (projets, tâches, temps)
├── crm/               (contacts, deals)
├── communication/     (messagerie)
├── admin/             (super admin : tenants, stats)
└── settings/          (paramètres entreprise)
```

## 6. Plan de Développement par Phases

### Phase 0 — Fondation (1-2 semaines)
- [x] Système de rôles et permissions
- [x] Auth + redirection multi-tenant
- [x] Middleware multi-tenant
- [ ] **Migration PostgreSQL** (installation + configuration)
- [ ] Structure de base du frontend (CompanyLayout)

### Phase 1 — Module GED / Secrétariat (2-3 semaines)
- [ ] Arborescence complète des dossiers clients (modèle Eden Store)
- [ ] Upload/Download de fichiers
- [ ] Prévisualisation navigateur
- [ ] Versioning + Corbeille
- [ ] Recherche full-text
- [ ] Partage sécurisé
- [ ] Audit trail

### Phase 2 — Module Comptabilité (3-4 semaines)
- [ ] Plan comptable
- [ ] Saisie d'écritures (journaux)
- [ ] Balance + Grand Livre
- [ ] États financiers (Bilan, CRP, Flux)
- [ ] Conciliation bancaire
- [ ] Clôture mensuelle/annuelle

### Phase 3 — Module Facturation (2 semaines)
- [ ] Devis + Factures + Avoirs
- [ ] PDF haute qualité
- [ ] Envoi email
- [ ] Suivi paiements + relances
- [ ] Paiements en ligne (MTN MoMo, etc.)

### Phase 4 — Module RH (2-3 semaines)
- [ ] Dossiers employés
- [ ] Contrats
- [ ] Congés / Absences
- [ ] Paie
- [ ] Notes de frais

### Phase 5 — Modules Avancés (4-6 semaines)
- [ ] Juridique (contrats, conformité)
- [ ] Projets (Kanban, Gantt)
- [ ] CRM
- [ ] Communication (messagerie)
- [ ] Dashboard BI

### Phase 6 — IA & Optimisations (2-3 semaines)
- [ ] Assistant IA
- [ ] OCR
- [ ] Catégorisation auto
- [ ] Anomalies comptables
