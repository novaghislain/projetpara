# Architecture GEL Cabinet — Plateforme SaaS Multi-Portail

> **Version :** 2.1.1 | **Date :** 21 juin 2026 | **Stack :** Laravel 12 + Vue 3 + Vite + Bootstrap 5 + MySQL

## 1. Vue d'ensemble

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        CLIENTS WEB (Vue 3 SPA + Blade)                       │
│  ┌────────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │  GEL Cabinet   │  │   Portail    │  │ CPA Client   │  │   Public     │  │
│  │ (super_admin)   │  │  Entreprise  │  │ (comptable/  │  │ (catalogue/  │  │
│  │  Dark Premium   │  │ (company_*)  │  │  client)     │  │  landing)    │  │
│  │ GelLayout.vue   │  │CompanyLayout│  │ CpaLayout.vue│  │ Landing.blade│  │
│  └───────┬─────────┘  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘  │
│          └───────────────────┴──────────────────┴─────────────────┘         │
│            Root.vue → Map (data-page → composant Vue)                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
┌────────────────────────────────────┴──────────────────────────────────────┐
│                         API REST (Laravel 12)                              │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │  Middleware : auth | verified | not_suspended | ensure.company     │  │
│  │  company.auth | module:{module} | can.action | gel.admin          │  │
│  │  Guest (redirectIfAuthenticated) | SetTenantContext                │  │
│  │  Rôles : super_admin(100) → comptable(50) → company_admin(40)     │  │
│  │  → company_manager(30) → caissier(20) → employé(20) → client(10)  │  │
│  │  16 middlewares personnalisés                                      │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │  Contrôleurs : Gel/* | Company/* | Public/* | Auth/* | Commerce/*  │  │
│  │  | Cpa/* | Api/* | MeController | SwaggerController               │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
┌────────────────────────────────────┴──────────────────────────────────────┐
│                       BASE DE DONNÉES (MySQL)                              │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │  Tables système : users | clients | roles | permissions            │  │
│  │  Tables multi-tenant : user_clients | client_modules |             │  │
│  │    comptable_clients | permission_field_restrictions               │  │
│  │  80+ tables : comptabilité, ERP, RH, IT, tontines, audit, etc.    │  │
│  │  Isolation : TenantScope (Eloquent Global Scope) sur client_id     │  │
│  │  + SetTenantContext (PostgreSQL RLS ready, skip on MySQL)          │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 2. Architecture Multi-Portail (4 portails)

### 2.1 Portail GEL Cabinet (Super Admin) — Dark Premium
- **URL :** `/dashboard`
- **Layout :** `GelLayout.vue` — Dark Premium : sidebar `#0B1120` gradient + topbar vitrée
- **Rôle :** `super_admin`, `director`
- **Périmètre :** Gestion cabinet, clients, comptabilité, ERP, RH, IT, tontines, signatures, etc.

### 2.2 Portail Entreprise (Client)
- **URL :** `/company/dashboard`
- **Layout :** `CompanyLayout.vue` — sidebar verticale entreprise
- **Rôles :** `company_admin`, `company_manager`, `company_employee`
- **Périmètre :** Données propres à l'entreprise, GED, RH, facturation

### 2.3 Portail CPA (Client Particulier & Comptable)
- **URL :** `/cpa-dashboard`
- **Layout :** `CpaLayout.vue`
- **Rôles :** `client`, `comptable`, `super_admin`
- **Périmètre :** Déclarations, dossiers, messagerie

### 2.4 Portail Public (Catalogue E-commerce)
- **URL :** `/nos-services`
- **Layout :** Aucun (Bootstrap direct) — pages standalone
- **Accès :** Sans authentification
- **Périmètre :** Catalogue, panier, commande, Mobile Money

## 3. Rendu (Blade → Vue SPA)

### 3.1 Entrées Blade
| Template | Usage | Rendu |
|----------|-------|-------|
| `app.blade.php` | Portails GEL + CPA + Public | Manifest JSON hardcodé |
| `company.blade.php` | Portail entreprise | `@vite()` directive |
| `landing.blade.php` | Page d'accueil publique | HTML statique |

### 3.2 Flux complet
```
URL → routes/web.php → Middleware → Controller → return view('app', [
    'page' => 'gel-dashboard',     // clé pour Root.vue
    'props' => ['key' => 'value'], // props Vue
])
  → Blade template
    → <div id="app" data-page="gel-dashboard" data-props='{...}'>
    → <script id="auth-data">{"user": {id, name, email, role, active_client_id, ...}}</script>
    → window.__CLIENT_ID__
  → app.js (Vue entry)
    → Root.vue (Map: data-page → composant Vue)
      → Layout (GelLayout / CompanyLayout / CpaLayout)
        → Page spécifique
```

## 4. Système Multi-Tenant

### 4.1 Isolation
- **Stage 1 — PHP** : `TenantScope` (Eloquent Global Scope) applique `client_id = X` sur toutes les requêtes Eloquent
- **Stage 2 — Base de données** : `SetTenantContext` middleware exécute `SET app.client_id` pour PostgreSQL RLS (early-return sur MySQL)
- **Stage 3 — Application** : `EnsureCompanyAccess` middleware valide que l'utilisateur a un `active_client_id` valide

### 4.2 Tables multi-tenant

| Table | Rôle |
|-------|------|
| `user_clients` | Pivot utilisateur ↔ entreprise avec rôle, statut, dates d'accès |
| `client_modules` | Activation/désactivation des modules par client |
| `comptable_clients` | Assignation des comptables aux clients |
| `permission_field_restrictions` | Restrictions de champs par (module, action, role_slug) |
| `permission_delegations` | Délégation temporaire de permissions |

### 4.3 Changement de contexte (multi-entreprise)
```
Utilisateur lié à plusieurs entreprises
  → CompanySwitcherController::showSelector → page SelectContext.vue
  → Sélection d'une entreprise → switchToClient(clientId)
  → active_client_id mis à jour → window.location.reload()
  → Les permissions sont rechargées pour le nouveau contexte
```

## 5. Middleware Chain

### 5.1 Middleware globaux (bootstrap/app.php)
```
SetTenantContext → SET app.client_id (PostgreSQL RLS, skip on MySQL)
LogRedirects     → Log des redirections HTTP
```

### 5.2 Middleware d'authentification
```
auth              → Authentification Laravel standard
verified          → EnsureEmailVerified (passe-système, ne bloque pas)
not_suspended     → CheckNotSuspended (vérifie suspension, abort 401 si non auth)
guest             → RedirectIfAuthenticated (Laravel core, redirige vers dashboard)
```

### 5.3 Middleware de portail
```
company           → CheckCompanyAccess (redirige company_admin vers /company/dashboard)
not_client        → EnsureNotClient (bloque clients purs, redirige vers /mes-commandes)
redirect.client   → RedirectIfClient (redirige clients vers /mes-commandes)
```

### 5.4 Middleware de rôle et permissions
```
gel.admin         → CheckSuperAdmin (vérifie super_admin via système de rôles)
gel.comptable     → CheckComptable (vérifie comptable cabinet)
role              → CheckRole (rôle paramétrable: role:super_admin)
can.action        → CheckActionPermission (module:action: can.action:comptabilite,lire)
module            → CheckModuleAccess (accès module: module:rh, retourne JSON 403 si refusé)
```

### 5.5 Middleware de contexte entreprise
```
ensure.company    → EnsureCompanyAccess (active_client_id valide dans user_clients)
company.auth      → EnsureIsCompanyAdmin (client_id non nul)
```

### 5.6 Middleware additionnels
```
dae.secretaire    → DaeSecretaireAccess (accès restreint au module DAE)
ip.whitelist      → IpWhitelist (filtrage par IP si défini sur le client)
admin             → AdminMiddleware (ancien système, basé sur is_admin)
```

### 5.7 Alias définitifs dans bootstrap/app.php
```php
'admin'          => \App\Http\Middleware\AdminMiddleware::class,
'role'           => \App\Http\Middleware\CheckRole::class,
'company'        => \App\Http\Middleware\CheckCompanyAccess::class,
'company.auth'   => \App\Http\Middleware\EnsureIsCompanyAdmin::class,
'module'         => \App\Http\Middleware\CheckModuleAccess::class,
'not_client'     => \App\Http\Middleware\EnsureNotClient::class,
'dae.secretaire' => \App\Http\Middleware\DaeSecretaireAccess::class,
'ip.whitelist'   => \App\Http\Middleware\IpWhitelist::class,
'gel.admin'      => \App\Http\Middleware\CheckSuperAdmin::class,
'gel.comptable'  => \App\Http\Middleware\CheckComptable::class,
'ensure.company' => \App\Http\Middleware\EnsureCompanyAccess::class,
'verified'       => \App\Http\Middleware\EnsureEmailVerified::class,
'not_suspended'  => \App\Http\Middleware\CheckNotSuspended::class,
'can.action'     => \App\Http\Middleware\CheckActionPermission::class,
'redirect.client'=> \App\Http\Middleware\RedirectIfClient::class,
```

## 6. Authentification (8 étapes)

```
POST /login → AuthenticatedSessionController::store

  1. CREDENTIALS   → LoginRequest::authenticate() + Session::regenerate()
  2. SUSPENSION    → isSuspended() ? logout + erreur
  3. EMAIL         → email_verified_at ? (sauf super_admin) logout + erreur
  4. PASSWORD      → must_change_password ? session flag
  5. 2FA           → two_factor_confirmed_at ? redirect → 2fa.challenge
  6. METADATA      → last_login_at, last_login_ip, login_count
  7. AUDIT         → AuditTrail::create(event: 'login')
  8. REDIRECT      → selon le rôle (dashboard / company.dashboard / cpa.dashboard / etc.)
```

## 7. Système de Rôles et Permissions

### 7.1 Rôles (11, avec hiérarchie)

| Rôle | Level | Description |
|------|-------|-------------|
| `super_admin` | 100 | Super Administrateur — accès total |
| `director` | 90 | Directeur du cabinet |
| `pole_responsible` | 60 | Responsable de pôle |
| `comptable` | 50 | Comptable Cabinet |
| `collaborator` | 45 | Collaborateur cabinet |
| `company_admin` | 40 | Administrateur Entreprise |
| `company_manager` | 30 | Manager Entreprise |
| `company_employee` | 20 | Employé Entreprise |
| `secretaire` | 20 | Secrétaire |
| `caissier` | 20 | Caissier |
| `juriste` | 20 | Juriste |
| `rh` | 20 | Gestionnaire RH |
| `gestionnaire_projet` | 20 | Gestionnaire de projets |
| `client` | 10 | Client |

### 7.2 Modules de permissions (12)

| Module | Actions |
|--------|---------|
| `caisse` | encaissement, decaissement, cloture, historique, lire, rapports |
| `comptabilite` | creer, exporter, lire, modifier, rapports, saisir, supprimer, valider |
| `crm` | campagnes, creer, devis, lire, modifier, rapports, relances, supprimer |
| `dae` | archiver, assigner, creer, exporter, lire, modifier, programmer, supprimer, traiter, valider |
| `document` | lecture, lire, modification, partage, suppression, upload |
| `erp` | commandes, creer, inventaire, lire, modifier, rapports, stock_entree, stock_sortie, supprimer |
| `facturation` | annuler_facture, creer_facture, imprimer_facture, lire, modifier_facture, parametres, regler |
| `it_assets` | assigner, creer, lire, maintenance, modifier, supprimer |
| `it_helpdesk` | assigner, creer, lire, rapports, repondre, resoudre |
| `juridique` | archivage, consultation, creation_dossiers, lire, modification, suppression |
| `projets` | achevement, creation, lecture, lire, modification, suppression, taches |
| `rh` | approuver_conge, approuver_frais, creation, lecture, lire, modification, paie, recrutement, suppression, valider_paie |

Total : **89 permissions** réparties dans 12 modules.

## 8. Architecture du Module Juridique

### 8.1 Modèle de base — LegalBaseModel

```php
abstract class LegalBaseModel extends Model
{
    // Scope multi-tenant : super admin bypass, sinon filtre par client_id
    public function scopeByClient($query, int $clientId)
    {
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $query; // Super admin voit toutes les données
        }
        return $query->where('client_id', $clientId);
    }
}
```

Tous les modèles juridiques héritent de cette classe abstraite, garantissant que :
- Les **super admins** voient l'intégralité des données (tous les clients)
- Les **comptables / company_admin** voient uniquement `client_id = X`
- Le filtre est appliqué automatiquement via `->byClient($clientId)` dans les contrôleurs

### 8.2 Sous-modules (8)

| Sous-module | Tables | Pages Vue |
|-------------|--------|-----------|
| Fiche Société | `legal_company_infos` | Societe/Index |
| Assemblées | `legal_assemblies` | Assemblees/Index, Show, Form |
| Contrats | `legal_contracts`, `legal_contract_signatures` | Contrats/Index, Show, Form |
| Contentieux | `legal_litigations` | Contentieux/Index, Show, Form |
| Conformité | `legal_compliance` | Conformite/Index, Form, Calendrier |
| Bibliothèque | `legal_acts_library` | Bibliotheque/Index, Form, Generer |
| Dossiers | `legal_dossiers` | Dossiers/Index, Show, Form |
| Registres | `legal_registres` | Registres/Show |

### 8.3 Générateur d'actes

`ActeGeneratorService` : remplace les variables `{nom_client}`, `{date}`, `{capital}` dans les modèles de la bibliothèque par les valeurs dynamiques du client. Utilisé par `LegalActsLibraryController::generer()`.

## 9. Flux Facture (création → normalisation e-MECeF → portail client)

### 9.1 Architecture à deux systèmes de facturation

Le système repose sur **deux modèles distincts et indépendants** :

| Aspect | `ErpInvoice` (GEL) | `CompanyInvoice` (Entreprise) |
|--------|-------------------|------------------------------|
| **Namespace** | `App\Models\ErpInvoice` | `App\Models\CompanyInvoice` |
| **Portail** | GEL Cabinet (backoffice) | Portail Entreprise |
| **Créée par** | Comptable cabinet | Admin/manager entreprise |
| **e-MECeF** | ✅ Oui (champs dédiés) | ❌ Non |
| **Paiements** | Non géré | ✅ CompanyPayment |
| **Items** | ErpInvoiceItem | CompanyInvoiceItem |
| **Scope** | client_id (manuel) | byClient() scope |

### 9.2 Flux d'émission e-MECeF (détaillé)

```
Étape 1 : Facture créée par le comptable (GEL)
  POST /erp/invoices → ErpInvoiceController::store()
  → Création ErpInvoice + ErpInvoiceItems
  → Statut initial : "brouillon"
  → Aucun champ e-MECeF rempli

Étape 2 : Clic sur le bouton DGI
  POST /emecef/emit/{invoice} → EmecefController::emitInvoice()
  → Vérifie que la facture n'est pas déjà émise (422 si déjà 'emise')
  → Appelle EmecefService::emettreFactureNormalisee($invoice)

Étape 3 : EmecefService::emettreFactureNormalisee()
  1. Vérifie la configuration e-MECeF du client :
     - client->emecef_is_active (bool) 
     - client->emecef_nim (string)
     - client->emecef_password (encrypted)
  2. Construit le payload DGI :
     - IFU émetteur, IFU récepteur
     - Type de facture (FV/FA/AV/EA)
     - Montants HT, TVA, TTC
     - Calcul AIB (1% DGE/DME, 5% CSI)
     - Liste des articles
  3. Appelle l'API Sygmef (ou simule si EMECEF_TEST_MODE=true)
  4. Sur succès :
     - Stocke emecef_nim, emecef_compteur, emecef_hash, emecef_qr
     - Passe emecef_statut = 'emise'
     - Enregistre emecef_datetime
  5. Retourne ['success' => true, 'data' => [...]]

Étape 4 : Création des notifications (si émission réussie)
  EmecefController::createEmissionNotifications($invoice)
  → Notification 1 : admin de l'entreprise (via $client->companyAdmins())
  → Notification 2 : comptable qui a émis (via $invoice->created_by)

Étape 5 : Portail entreprise — affichage de la facture
  GET /api/company/invoices → CompanyInvoiceController::listAll()
  → Récupère CompanyInvoice byClient($clientId)
  → Récupère ErpInvoice where client_id = $clientId AND emecef_statut = 'emise'
  → Fusionne les deux collections, tri par date descendante
  → Chaque entrée ErpInvoice a un flag source: 'erp' + emecef data

Étape 6 : Affichage dans Invoices.vue (Company)
  → Badge "ERP" (fond bleu foncé #163A5E)
  → Badge "DGI ✓" (vert, pour les factures certifiées)
  → QR code dans le détail (NIM, compteur, datetime, statut)
  → Distinction visuelle (isup-row-erp: fond alterné)
```

### 9.3 EmecefService — API complète

```php
class EmecefService
{
    public function __construct(
        private readonly string $apiUrl,
        private readonly string $apiToken,
        private readonly string $defaultNim,
        private readonly bool $testMode,
    ) {}

    // Émet une facture normalisée à la DGI
    public function emettreFactureNormalisee(ErpInvoice $invoice): array
    {
        $client = $invoice->client;
        if (!$client || !$client->emecef_is_active || !$client->emecef_nim) {
            return ['success' => false, 'error' => 'Client non configuré pour e-MECeF.'];
        }

        // Construction du payload...
        // Appel API ou simulation...
        // Mise à jour des champs emecef_* sur l'invoice...
    }

    // Annule une facture auprès de la DGI
    public function annulerFacture(ErpInvoice $invoice): array {}

    // Vérifie le statut d'une facture
    public function verifierFacture(string $nim, string $compteur): array {}

    // Certifie pour PDF (statique, sans API)
    public static function certifyInvoice(ErpInvoice $invoice): ErpInvoice {}
}
```

### 9.4 EmecefController — Endpoints

| Méthode | Endpoint | Action | Préconditions |
|---------|----------|--------|---------------|
| POST | `/emecef/emit/{invoice}` | Émettre à la DGI | Facture non déjà émise |
| POST | `/emecef/cancel/{invoice}` | Annuler à la DGI | Facture doit avoir emecef_nim |
| GET | `/emecef/verify/{invoice}` | Vérifier statut | Facture doit avoir nim + compteur |

---

## 10. Flux des Permissions (Frontend)

### 8.1 Store authStore (reactive, singleton)

```
INIT (au chargement de la page) :
  initAuth()
    → Parse <script id="auth-data">
    → authStore.user = data.user
    → authStore.initFromApi()
        → GET /api/me/profile
        → permissions, modules, companies, activeCompany

VÉRIFICATIONS :
  hasModule(module)    → authStore.modules.includes(module)
  can(module, action)  → authStore.permissions.includes("module:action")
  isFieldHidden(module, field) → authStore.fieldRestrictions[module]

POLLING (toutes les 15s) :
  startPermissionPolling()
    → GET /api/company/events/check
    → {updated: true} ? window.location.reload()

CHANGEMENT DE CONTEXTE :
  switchToCompany(clientId)
    → POST /api/me/switch-context { client_id }
    → refreshPermissions() + window.location.reload()
```

## 11. Structure des fichiers

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                → Login, Register, 2FA, Password
│   │   ├── Gel/                 → Super Admin (60+ contrôleurs)
│   │   │   ├── ClientController, PoleController, MissionController
│   │   │   ├── Comptabilite/    → Account, Journal, Balance, Bilan, Resultat
│   │   │   ├── Erp/             → Invoice, Treasury, PreBill
│   │   │   ├── Rh/              → Employee, Contract, Payslip
│   │   │   ├── Facturation/
│   │   │   ├── Caisse/
│   │   │   ├── Projets/
│   │   │   ├── Juridique/
│   │   │   ├── Ia/
│   │   │   ├── Dae/
│   │   │   ├── It/              → Tickets, Assets, KnowledgeBase
│   │   │   ├── Tontines/
│   │   │   ├── Signatures/
│   │   │   ├── Relances/
│   │   │   ├── CostCenters/
│   │   │   ├── Ocr/
│   │   │   ├── Paie/
│   │   │   └── Admin/           → Audit, Articles
│   │   ├── Modules/             → Modules spécialisés
│   │   │   └── Legal/           → Module juridique (9 contrôleurs)
│   │   │       ├── LegalDashboardController
│   │   │       ├── LegalCompanyInfoController
│   │   │       ├── LegalAssembliesController
│   │   │       ├── LegalContratsController
│   │   │       ├── LegalLitigationsController
│   │   │       ├── LegalComplianceController
│   │   │       ├── LegalActsLibraryController
│   │   │       ├── LegalDossiersController
│   │   │       └── LegalRegistresController
│   │   ├── Company/             → Portail entreprise (Users, Events, Caisse, DAE...)
│   │   ├── Public/              → Catalogue e-commerce, Commande, Panier
│   │   ├── Commerce/            → Dashboard commerce/POS
│   │   ├── Api/                 → Profile, Password...
│   │   ├── Cpa/                 → CPA Dashboard
│   │   ├── MeController.php     → /api/me/profile, permissions, switch-context
│   │   └── CompanySwitcherController.php → /context/ selector, switch
│   ├── Middleware/              → 16 middlewares personnalisés
│   │   ├── CheckRole, CheckModuleAccess, CheckSuperAdmin, CheckComptable
│   │   ├── CheckNotSuspended, CheckCompanyAccess, CheckActionPermission
│   │   ├── EnsureCompanyAccess, EnsureEmailVerified, EnsureIsCompanyAdmin
│   │   ├── EnsureNotClient, RedirectIfClient, AdminMiddleware
│   │   ├── SetTenantContext, DaeSecretaireAccess, IpWhitelist, LogRedirects
│   └── Requests/
├── Models/                      → 80+ modèles Eloquent
│   ├── User.php                 → isSuperAdmin(), isCompanyAdmin(), canModule(),
│   │                              switchToClient(), hiddenFields(), recordLogin()
│   ├── Client.php, UserClient.php, ClientModule.php, Role.php, Permission.php
│   ├── Pole.php, Mission.php, MissionStep.php
│   ├── Account.php, JournalEntry.php, JournalLine.php, Balance.php, FiscalYear.php
│   ├── ErpInvoice.php, Product.php, StockMovement.php, Subscription.php
│   ├── Employee.php, Contract.php, Payslip.php, LeaveRequest.php
│   ├── Project.php, ProjectTask.php, ProjectMilestone.php
│   ├── LegalCase.php, Article.php, Caisse.php, CaisseTransaction.php
│   ├── Legal/                    → Module juridique (11 modèles, base abstraite)
│   │   ├── LegalBaseModel.php   → Abstract, scopeByClient() (super_admin bypass)
│   │   ├── LegalCompanyInfo.php, LegalAssembly.php, LegalContract.php
│   │   ├── LegalContractSignature.php, LegalLitigation.php, LegalCompliance.php
│   │   ├── LegalActsLibrary.php, LegalRegistre.php, LegalDossier.php
│   │   ├── LegalVeille.php, LegalAuditLog.php
│   ├── ClientFolder.php, Document.php, DocumentVersion.php
│   ├── DossierDae.php, CourrierDae.php, TacheDae.php
│   ├── ItTicket.php, ItAsset.php, ItMaintenanceContract.php
│   ├── Tontine.php, TontineMembre.php, TontineCotisation.php
│   ├── RelanceRule.php, ApprovalWorkflow.php, ApprovalRequest.php
│   ├── DocumentSignature.php, AuditTrail.php
│   ├── TaxDeclaration.php, CostCenter.php, AnalyticLine.php
│   └── Scopes/TenantScope.php  → Eloquent Global Scope (client_id)

resources/
├── views/
│   ├── app.blade.php           → Portails GEL + CPA + Public (assets hardcodés)
│   ├── company.blade.php       → Portail entreprise (@vite())
│   ├── landing.blade.php       → Page d'accueil statique
│   │   auth/                   → login, register, 2FA (Blade)
│   │   2fa/                    → setup, challenge (Blade)
│   │   accounting/pdf/         → PDF factures, bilan, balance (Blade + DejaVu Sans)
├── js/
│   ├── app.js                  → Entry point Vue + registrations components
│   ├── bootstrap.js            → Axios config + CSRF + base URL + fetch wrapper
│   ├── Root.vue                → Routeur dynamique (data-page → composant Vue)
│   ├── Layouts/                → GelLayout (Dark Premium), CompanyLayout, CpaLayout
│   ├── Pages/
│   │   ├── Gel/                → 25+ pages Super Admin
│   │   │   ├── Dashboard.vue, Profile.vue
│   │   │   ├── Clients/        → Index, Create, Show, Edit, Modules, Services
│   │   │   ├── Accounting/     → Accounts, Journal*, Balance, Bilan, Resultat...
│   │   │   ├── Erp/            → Invoices, Treasury, PreBill
│   │   │   ├── Rh/             → Employees, Contracts, Payslips
│   │   │   ├── Caisse/, Projets/, Dae/, It/, Legal/
│   │   │   ├── Tontines/, Signatures/, Relances/, CostCenters/
│   │   │   ├── Ocr/, Paie/
│   │   │   └── Admin/          → Requests, Audit, Articles
│   │   ├── Modules/            → Modules spécialisés
│   │   │   └── Legal/          → Module juridique (21 pages)
│   │   │       ├── Dashboard.vue, Societe/, Assemblees/
│   │   │       ├── Contrats/, Contentieux/, Conformite/
│   │   │       ├── Bibliotheque/, Dossiers/, Registres/
│   │   ├── Company/            → 15+ pages portail entreprise
│   │   ├── Public/             → Catalogue, Panier, Commande
│   │   └── Auth/               → CpaLogin, CpaRegister
│   ├── Components/             → Composants réutilisables
│   ├── stores/
│   │   ├── auth.js             → authStore (réactif, permissions, polling)
│   │   └── cart.js             → cartStore (panier e-commerce)
│   └── css/
│       ├── app.css             → Global (variables CSS, Inter, Outfit)
│       └── company.css         → Classes isup-* (portail entreprise)

routes/
├── web.php                     → Routes principales (~1300+ lignes)
├── auth.php                    → Login, Register, 2FA, Password
├── console.php                 → Commandes artisan (relances, IT alerts)
└── debug.php                   → Routes de debug

database/
├── migrations/                 → 100+ migrations
│   ├── multi-tenant (2026_06_19_*)
│   ├── comptabilité, ERP, RH, IT, tontines, audit...
└── seeders/
    ├── DatabaseSeeder          → Orchestrateur (15+ seeders)
    ├── AdminSeeder             → Comptes cabinet GEL
    ├── UsersSeeder             → 30+ utilisateurs démo
    ├── RoleAndPermissionSeeder → 11 rôles, 89 permissions, 12 modules
    ├── CrescendoDemoSeeder     → 4 profils démo CPA
    ├── DemoCompanySeeder       → Données entreprise démo
    ├── AccountingDemoSeeder    → Écritures comptables démo
    ├── DaeDemoSeeder           → Secrétariat DAE démo
    ├── LegalDemoSeeder         → Données juridiques démo (contrats, contentieux, conformité, actes, AG, registres)
    └── ...

bootstrap/app.php               → Aliases middleware + SetTenantContext global
```

## 12. API Endpoints

### 12.1 Company — Factures
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/company/invoices` | GET | Page facturation (SPA) |
| `/api/company/invoices` | GET | Liste toutes les factures (CompanyInvoice + ErpInvoice émises) |
| `/api/company/invoices/stats` | GET | Statistiques (total TTC, payé, dû, par statut) |
| `/api/company/invoices` | POST | Créer facture |
| `/api/company/invoices/{id}` | GET | Détail facture avec lignes + paiements |
| `/api/company/invoices/{id}` | PUT | Mettre à jour facture (brouillon seulement) |
| `/api/company/invoices/{id}` | DELETE | Supprimer facture (brouillon seulement) |
| `/api/company/invoices/{id}/status` | PATCH | Changer statut (draft/sent/paid/cancelled/overdue) |
| `/api/company/invoices/{id}/payments` | POST | Enregistrer un paiement |

### 12.2 CompanyInvoice — Modèle de données
```sql
company_invoices (
    id, client_id, number, type (invoice/credit_note/devis),
    status (draft/sent/paid/cancelled/overdue),
    recipient_name, recipient_address,
    issue_date, due_date,
    total_ht DECIMAL(15,2), total_tva DECIMAL(15,2), total_ttc DECIMAL(15,2),
    paid_amount DECIMAL(15,2) DEFAULT 0,
    notes TEXT, created_by FK→users,
    created_at, updated_at, deleted_at
)
```
- **Attribut calculé** : `computedStatus` = 'paid' si `paid_amount >= total_ttc`
- **Relations** : items(), payments(), createdBy()
- **Scope** : byClient(int $clientId)

### 12.3 ErpInvoice — Modèle de données (e-MECeF)
```sql
erp_invoices (
    id, invoice_number, type, client_id FK→clients, client_name,
    invoice_date, due_date,
    total_ht DECIMAL(15,2), tax_amount DECIMAL(15,2), total_ttc DECIMAL(15,2),
    status, created_by FK→users,
    emecef_nim VARCHAR(50),        -- NIM du client (per-client)
    emecef_compteur INT,           -- Compteur retourné par la DGI
    emecef_hash VARCHAR(255),       -- Hash de la facture
    emecef_qr TEXT,                 -- QR code (URL de vérification DGI)
    emecef_statut VARCHAR(20),      -- non_emise / emise / annulee
    emecef_datetime DATETIME,       -- Date d'émission DGI
    created_at, updated_at, deleted_at
)
```
- **Relations** : client(), lineItems(), creator()

### 12.5 Profil et permissions
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/me/profile` | GET | Profil complet + permissions + entreprises |
| `/api/me/permissions` | GET | Permissions formatées (pour polling/refresh) |
| `/api/me/field-restrictions/{module}` | GET | Champs cachés pour un module |
| `/api/me/switch-context` | POST | Basculer le contexte entreprise |

### 12.6 Contexte entreprise
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/context` | GET | Sélecteur de contexte (multi-entreprise) |
| `/context/switch` | POST | Changer d'entreprise active |

### 12.7 Polling
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/company/events/check` | GET | Vérification des mises à jour permissions |

### 12.9 CRM Clients (API GEL)
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/clients` | GET | Liste des clients |
| `/api/clients/{id}` | GET | Détail client |
| `/api/clients/{id}/services` | GET | Services d'un client |
| `/api/clients/{clientId}/services/attach` | POST | Attacher un service |
| `/api/clients/{clientId}/services/{serviceId}` | DELETE | Détacher un service |
| `/api/clients/{clientId}/services/{serviceId}/status` | PUT | Statut d'un service |
| `/api/clients/{id}/modules` | PUT | Modules d'un client |

### 12.10 Commerce / Catalogue
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/commerce/dashboard` | GET | Stats dashboard commerce |
| `/api/cart` | GET | Contenu du panier |
| `/api/cart/add` | POST | Ajouter au panier |
| `/api/cart/update` | PUT | Mettre à jour quantité |
| `/api/cart/remove/{id}` | DELETE | Retirer du panier |
| `/api/cart/clear` | POST | Vider le panier |

## 13. Architecture Frontend (Vue 3)

### 13.1 bootstrap.js — Configuration réseau
```
Axios defaults :
  baseURL       ← <meta name="base-url"> (request()->root() côté serveur)
  X-CSRF-TOKEN  ← <meta name="csrf-token"> (pour les requêtes POST/PUT/DELETE)
  X-Requested-With: XMLHttpRequest

fetch() wrapper :
  Toute requête fetch('/api/...') interceptée
  → Préfixe automatique avec base-url (ex: /Para/public/api/...)
  → Évite les 404 dus au décalage entre APP_URL et le chemin XAMPP
```

### 13.2 Stores Vue (réactifs)

**authStore** (stores/auth.js) — Singleton réactif :
```
État :
  user, isAuthenticated, permissions[], modules[], permissionIds[]
  companies[], activeCompany{}, fieldRestrictions{}

Getters :
  hasModule(module), can(module, action), isFieldHidden(module, field)
  isCompanyUser, isCompanyAdmin, isSuperAdmin, isComptable, isClient

Cycle de vie :
  initAuth()  → Parse <script id="auth-data"> dans le Blade
  initFromApi() → GET /api/me/profile → permissions + modules + companies
  Polling toutes les 15s → GET /api/company/events/check
  switchToCompany(clientId) → POST /api/me/switch-context → reload
```

**cartStore** (stores/cart.js) — Panier e-commerce :
```
État : items[], count, total, isLoading
Méthodes : fetchCart(), addToCart(), updateCartItem(), removeFromCart(), clearCart()
```

### 13.3 Root.vue — Routeur de composants
```
Lecture de data-page sur #app
Map<pageKey, Component> résout le composant Vue correspondant
Props passées via data-props (JSON dans l'attribut data-props du div #app)
Rendu : <component :is="pageComponent" v-bind="pageProps" />
```

## 14. Modèle de Données (tables multi-tenant)

### user_clients
```sql
user_id (FK→users) + client_id (FK→clients) [PRIMARY]
role (enum: super_admin/company_admin/.../client)
is_active, invited_by, joined_at, last_accessed_at
```

### client_modules
```sql
client_id (FK→clients) + module [PRIMARY]
is_active, activated_at, activated_by (FK→users)
```

### comptable_clients
```sql
comptable_id (FK→users) + client_id (FK→clients) [PRIMARY]
assigned_at, assigned_by (FK→users)
```

### permission_field_restrictions
```sql
id (PK), module, action, role_slug (nullable), hidden_fields (JSON),
is_active, created_by (FK→users)
INDEX (module, action), INDEX (module, role_slug)
```

## 15. Plan de Développement

### Phase 0 — Fondation ✅
- [x] Système de rôles et permissions (11 rôles, 89 permissions, 12 modules)
- [x] Auth + redirection multi-rôle (8 étapes, 4 portails)
- [x] Middleware chain (15 middlewares)
- [x] Multi-tenant (TenantScope, user_clients, client_modules)
- [x] API profile/permissions/switch-context
- [x] Auth Store Vue avec polling 15s
- [x] Components UI : SelectContext, Modules, UserPermissions

### Phase 1 — Modules fonctionnels ✅ (achevée)
- [x] GED complète (dossiers, upload, versioning)
- [x] Comptabilité (plan, journaux, balance, bilan, résultat)
- [x] Facturation (abonnements, devis, factures, PDF)
- [x] RH (employés, contrats, paie, déclarations)
- [x] ERP (produits, stocks, factures, trésorerie, prévisionnel)
- [x] IT Helpdesk (tickets, SLA, base de connaissances)
- [x] ITAM (inventaire, licences, interventions)
- [x] Caisse (encaissements, décaissements, rapports)
- [x] Juridique (8 sous-modules : société, assemblées, contrats, contentieux, conformité, bibliothèque d'actes, dossiers, registres)
- [x] Projets (tâches, jalons, budget)
- [x] CRM Clients (CRUD, contacts, catégorisation)
- [x] Tontine / Microfinance
- [x] Signature électronique (canvas + hash SHA256)
- [x] Relances automatiques (règles J+X)
- [x] Workflows d'approbation
- [x] Secrétariat DAE (courriers, contrats, tâches)
- [x] OCR import factures fournisseurs
- [x] Calcul paie IRPP/CNSS (barèmes Bénin)

### Phase 2 — Design & UX (achevée)
- [x] Dark Premium redesign (sidebar #0B1120, glass topbar)
- [x] Dashboard responsive (mobile/tablette/desktop)
- [x] Navigation contextuelle dynamique
- [x] Base URL dynamique (XAMPP + artisan serve)
- [x] Stores Vue auth + cart
- [x] CSRF automatique + fetch wrapper

---

## 16. Configuration post-installation

### 16.1 Activation des modules par client

Chaque entreprise cliente doit avoir ses modules activés via la table `client_modules` (accessible dans l'UI super_admin → Client → Modules) :

```sql
INSERT INTO client_modules (client_id, module, is_active, activated_at, activated_by)
VALUES (2, 'rh', true, NOW(), 1);
```

Les modules non activés sont masqués côté serveur (middleware `module:*`) et côté client (sidebar filtrée par `authStore.hasModule()`).

**Modules disponibles :** caisse, comptabilite, crm, dae, document, erp, facturation, it_assets, it_helpdesk, juridique, projets, rh

### 16.2 Configuration e-MECeF par client

Pour activer l'envoi des factures à la DGI pour un client :

1. **IFU** (Identifiant Fiscal Unique) — obligatoire, renseigné sur la fiche client
2. **NIM** (Numéro d'Identification Machine) — fourni par la DGI, stocké dans `clients.emecef_nim`
3. **Mot de passe e-MECeF** — stocké chiffré dans `clients.emecef_password`
4. **Toggle** — `clients.emecef_is_active = true`

Ces champs sont gérés dans `resources/js/Pages/Gel/Clients/Show.vue` (section paramètres/modifiables par super_admin et comptables).

### 16.3 Configuration .env

```env
EMECEF_API_TOKEN=token_dgi
EMECEF_NIM=XX-XXXXXXX           # NIM global (déprécié — utiliser per-client)
EMECEF_TEST_MODE=true            # true = pas d'appel API réel
EMECEF_API_URL=https://sygmef.impots.bj/emcf/api
SESSION_DRIVER=file
SESSION_LIFETIME=120
MAIL_MAILER=smtp                # Pour notifications et envoi factures
MOMO_API_KEY=                   # MTN MoMo (sandbox → production)
```

### 16.4 Création des utilisateurs et rattachement

```php
// Créer un utilisateur entreprise
$user = User::create([...]);
UserClient::create([
    'user_id' => $user->id,
    'client_id' => $client->id,
    'role' => 'company_admin',
    'is_active' => true,
    'joined_at' => now(),
]);
$user->switchToClient($client->id); // active_client_id = client->id
```

**Comptes démo pré-existants (via UsersSeeder) :**
- `jean@techinnov.bj` / admin123 — Admin TechInnov (client_id = 2)
- `aminata@africa-logistics.com` / admin123 — Admin Africa Logistics (client_id = 4)
- `kossi@servicespro.bj` / admin123 — Admin Services Pro (client_id = 3)
- `marie@techinnov.bj` / admin123 — Manager TechInnov
- `david@techinnov.bj` / admin123 — Employé TechInnov

### 16.5 Vérification du bon fonctionnement

```bash
# 1. Migrations OK
php artisan migrate:status

# 2. Seeders chargés
php artisan db:seed --class=DatabaseSeeder

# 3. Build frontend
npm run build

# 4. Connexion test (super_admin)
#    admin@gel.cabinet / admin123 → /dashboard
#    Vérifier que la sidebar affiche tous les modules

# 5. Connexion test (entreprise)
#    jean@techinnov.bj / admin123 → /company/dashboard
#    Vérifier que les modules activés sont visibles

# 6. Test e-MECeF (mode test)
#    EMECEF_TEST_MODE=true → l'émission simule une réponse DGI
#    → emecef_statut passe à 'emise'
#    → notification créée pour l'admin entreprise
#    → facture visible côté entreprise avec badge DGI ✓
```
