<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vuedotjs" alt="Vue 3">
  <img src="https://img.shields.io/badge/Bootstrap_5-7952B3?style=for-the-badge&logo=bootstrap" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite" alt="Vite">
</p>

# 🏢 GEL Cabinet — Plateforme SaaS Multi-Portail

> **Plateforme SaaS de gestion multi-pôles** destinée aux cabinets d'expertise comptable, juridique et fiscal au Bénin. Conforme OHADA et SYSCOHADA, avec intégration e-MECeF DGI Bénin.

---

## 📋 Table des matières

1. [Présentation](#-présentation)
2. [Portails](#-portails)
3. [Modules fonctionnels](#-modules-fonctionnels)
4. [Stack technique](#-stack-technique)
5. [Installation](#-installation)
6. [Comptes de démonstration](#-comptes-de-démonstration)
7. [Architecture clé](#-architecture-clé)
8. [Documentation](#-documentation)
9. [Licence](#-licence)

---

## 🎯 Présentation

GEL Cabinet est une plateforme SaaS complète qui centralise **l'ensemble des outils** nécessaires à la gestion d'un cabinet d'expertise comptable :

- **👨‍💼 Portail GEL** — Super admin : gestion complète du cabinet (CRM, compta, ERP, RH, juridique, IT, etc.)
- **🏢 Portail Entreprise** — Company admin : données propres à l'entreprise, GED, RH, factures
- **📊 Portail CPA** — Comptable/client : déclarations, dossiers, messagerie
- **🛒 Portail Public** — Catalogue e-commerce, panier, paiement Mobile Money

### Contexte Bénin

- Conformité **OHADA / SYSCOHADA** (Plan comptable, Bilan, CRP, TAFIRE)
- Calcul automatique **IRPP/CNSS** (barèmes Bénin 2026)
- Intégration **e-MECeF / Sygmef** (facture normalisée DGI)
- Paiements **Mobile Money** (MTN MoMo, Moov Money)
- Modules spécifiques : **Tontine/Microfinance**, **Signature électronique**

---

## 🚪 Portails

| Portail | URL | Rôles | Layout |
|---------|-----|-------|--------|
| **GEL Cabinet** 🎩 | `/dashboard` | `super_admin` | `GelLayout.vue` — Dark Premium |
| **Entreprise** 🏢 | `/company/dashboard` | `company_admin`, `company_manager`, `company_employee` | `CompanyLayout.vue` |
| **CPA Client** 📊 | `/cpa-dashboard` | `comptable`, `client`, `super_admin` | `CpaLayout.vue` |
| **Public** 🌐 | `/nos-services` | Aucune authentification | Landings statiques |

---

## 🧩 Modules fonctionnels

### 🔷 Comptabilité & Conformité
| Module | Description |
|--------|-------------|
| 📒 **Comptabilité** | Plan comptable OHADA, journaux, balance, grand livre, bilan, compte de résultat, clôture d'exercice |
| 📑 **Télédéclaration** | TVA, IRPP, CNSS, IS, AIB — calcul automatique, export XML/Excel format DGI |
| ✅ **Conformité** | Obligations réglementaires, échéances, statuts, alertes |
| 📎 **e-MECeF** | Facture normalisée DGI, QR code, transmission API Sygmef |

### 🔷 Gestion d'entreprise
| Module | Description |
|--------|-------------|
| 👥 **CRM Clients** | Fiches clients, contacts, catégorisation, historique missions |
| 📁 **GED** | Arborescence dossiers, upload, versioning, permissions |
| 📦 **ERP** | Produits, stocks, factures, trésorerie, prévisionnel |
| 💳 **Facturation** | Abonnements, devis, factures récurrentes, paiements, relances |
| 🏗️ **Pôles & Missions** | Départements, missions, étapes, suivi du temps |
| 📋 **Projets** | Tâches, jalons, budget, équipe |

### 🔷 Ressources Humaines
| Module | Description |
|--------|-------------|
| 👷 **RH & Paie** | Employés, contrats, bulletins, déclarations CNSS/IRPP |
| 🏖️ **Congés** | Demandes, validation, solde |
| 💰 **Calcul paie** | Barèmes IRPP Bénin 2026 (7 tranches), CNSS employeur 15,4% / salarié 3,36% |

### 🔷 Juridique
| Module | Description |
|--------|-------------|
| ⚖️ **Contentieux** | Litiges avec référence, tribunal, montant, audience |
| 🛡️ **Conformité** | Obligations réglementaires, échéances, statuts |
| 📚 **Bibliothèque d'Actes** | Modèles avec variables dynamiques, générateur |
| 🤝 **Contrats** | Gestion complète avec parties, dates, signatures |
| 🏛️ **Assemblées** | Planification AG, PV, résolutions |
| 📋 **Dossiers** | Dossiers juridiques transverses |
| 📰 **Veille** | Actualités juridiques |

### 🔷 Services IT
| Module | Description |
|--------|-------------|
| 🎫 **Helpdesk** | Tickets, SLA, auto-numérotation TKT-2026-XXXXX |
| 💻 **ITAM** | Inventaire parc (ordinateurs, serveurs, imprimantes, licences) |
| 🔧 **Maintenance** | Contrats de maintenance, interventions |
| 📚 **Base de connaissances** | Articles KB, suggestions automatiques |

### 🔷 Finance & Mobile
| Module | Description |
|--------|-------------|
| 🏧 **Caisse** | Encaissements, décaissements, rapports, rapprochement |
| 📱 **Paiements mobiles** | MTN MoMo, Moov Money — demande → callback → confirmation |
| 🤝 **Tontine** | Tournante/épargne/crédit, cotisations, suivi |
| ✍️ **Signature électronique** | Canvas + hash SHA256 + PDF signé |
| 🔄 **Workflows d'approbation** | Chaînes configurables, escalade |
| 🤖 **OCR Factures** | Scan → extraction automatique → écriture comptable |
| 🏦 **Rapprochement bancaire** | CSV/OFX/MT940, fuzzy matching |

---

## 🛠️ Stack technique

| Couche | Technologie |
|--------|-------------|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Vue 3 (Composition API, `<script setup>`) |
| **Rendu** | Blade + SPA Vue (Root.vue → data-page) |
| **Build** | Vite 7.x |
| **CSS** | Bootstrap 5.3 + Bootstrap Icons + CSS personnalisé |
| **Base de données** | MySQL / MariaDB |
| **Middleware** | 16 middlewares personnalisés (rôles, modules, multi-tenant, 2FA) |
| **Stockage sessions** | Fichier (`SESSION_DRIVER=file`) |

### Dépendances clés

| Package | Usage |
|---------|-------|
| `codianselme/lara-sygmef` | API e-MECeF DGI Bénin |
| `chillerlan/php-qrcode` | QR code e-MECeF sur PDF |
| `pragmarx/google2fa-laravel` | Authentification 2FA |
| `setasign/fpdf` | Génération PDF signatures |
| `spatie/pdf-to-text` | Extraction texte (OCR) |
| `vite-plugin-pwa` | PWA (mode hors ligne) |

---

## ⚙️ Installation

### Prérequis
- PHP 8.2+
- MySQL 8+
- Composer 2+
- Node.js 20+
- NPM

### Procédure

```bash
# 1. Cloner le dépôt
git clone <url> Para
cd Para

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances JS
npm install

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données (fichier .env)
#    DB_DATABASE=gel_cabinet
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Migrations + seeders
php artisan migrate --seed

# 7. Build frontend
npm run build

# 8. Lancer le serveur
php artisan serve
```

### Commandes utiles

```bash
# Seeders spécifiques
php artisan db:seed --class=CrescendoDemoSeeder
php artisan db:seed --class=LegalDemoSeeder

# Build développement
npm run dev

# Build production
npm run build
```

---

## 👥 Comptes de démonstration

### Super Admin (cabinet GEL)
| Email | Mot de passe | Rôle |
|-------|-------------|------|
| `admin@gel.cabinet` | `admin123` | Super administrateur — accès complet |

### Administrateurs Entreprise
| Email | Mot de passe | Entreprise |
|-------|-------------|------------|
| `nova@gmail.com` | `admin123` | Nova Tech Vision (client_id=2) |
| `nsia@gmail.com` | `admin123` | Nsia (client_id=4) |

### Autres profils
| Email | Rôle | Description |
|-------|------|-------------|
| `m@gmail.com` | Comptable | Comptable cabinet |
| `oui@gmail.com` | Client | Client particulier |

> Tous les comptes démo utilisent le mot de passe `admin123` sauf indication contraire.

---

## 🏗️ Architecture clé

### Rendu (Blade → Vue SPA)

```
URL → routes/web.php → Middleware → Controller
  → return view('app', ['page' => 'gel-dashboard', 'props' => [...]])
  → Blade template (#app data-page + data-props + auth-data)
  → app.js → Root.vue (Map: data-page → composant)
  → Layout (GelLayout / CompanyLayout / CpaLayout)
  → Page spécifique
```

### Authentification (8 étapes)

```
POST /login → AuthenticatedSessionController::store
  1. Credentials  (LoginRequest::authenticate)
  2. Suspension   (isSuspended?)
  3. Email        (email_verified_at — pass-through)
  4. Password     (must_change_password?)
  5. 2FA          (two_factor_confirmed_at?)
  6. Metadata     (last_login_at, ip, login_count)
  7. Audit        (AuditTrail)
  8. Redirect     (selon rôle)
```

### Middleware chain (16 middlewares)

```
auth → verified (pass-through) → not_suspended → ensure.company
→ company.auth → gel.admin|gel.comptable → module:{module}
→ can.action → role → company → not_client → redirect.client
→ dae.secretaire → ip.whitelist → admin (legacy)
```

### Multi-tenant

- **Isolation** : `TenantScope` (Eloquent Global Scope) sur `client_id`
- **Tables pivot** : `user_clients`, `client_modules`, `comptable_clients`
- **Changement contexte** : `switchToClient(clientId)` → `active_client_id` mis à jour
- **Permissions polling** : Toutes les 15s via `/api/company/events/check`

### Base de données

90+ tables organisées par domaine : comptabilité (15), ERP (10), RH (8), juridique (11), IT (8), facturation (6), caisse (2), projets (3), sécurité/audit (5), multi-tenant (5), tontines (3), etc.

---

## 🔄 Flux Facture (création → normalisation e-MECeF → portail client)

### Architecture à deux systèmes
Le système de facturation repose sur **deux modèles distincts** qui communiquent via le processus e-MECeF :

| Modèle | Système | Usage | e-MECeF |
|--------|---------|-------|----------|
| `ErpInvoice` | GEL Cabinet (backoffice) | Facture créée par le comptable | ✅ Oui |
| `CompanyInvoice` | Portail Entreprise | Facture créée par l'entreprise | ❌ Non |

### Flux complet
```
1. COMPTABLE (GEL) : Crée une facture → /erp/invoices → statut "brouillon"
2. COMPTABLE (GEL) : Clic "DGI" → POST /emecef/emit/{id}
3. EmecefService  : Vérifie client (emecef_nim, emecef_is_active)
4. EmecefService  : Appelle API Sygmef (ou simule en test mode)
5. EmecefService  : Stocke emecef_* (nim, compteur, hash, qr, statut='emise')
6. Notifications  : Créées pour l'admin entreprise + le comptable
7. ENTREPRISE     : Facture visible dans /company/invoices avec badge DGI ✓
8. ENTREPRISE     : QR code e-MECeF consultable dans le détail
```

### Contrôleurs impliqués
| Contrôleur | Rôle |
|-----------|------|
| `Gel/Erp/InvoiceController` | Création des factures backoffice |
| `Gel/EmecefController` | Émission, annulation, vérification e-MECeF |
| `Company/InvoiceController` | Liste, CRUD, paiements, stats portail entreprise |
| `CompanySwitcherController` | Sélection et bascule de contexte multi-entreprise |

### Service e-MECeF (`app/Services/EmecefService.php`)
- `emettreFactureNormalisee(ErpInvoice $invoice)` — Émet la facture à la DGI (payload IFU, montants, AIB, items)
- `annulerFacture(ErpInvoice $invoice)` — Annule une facture émise
- `verifierFacture(string $nim, string $compteur)` — Vérifie le statut auprès de la DGI
- `certifyInvoice(ErpInvoice $invoice)` — Certifie pour PDF (statique, sans API)

### Configuration e-MECeF par client
Les identifiants e-MECeF sont stockés **sur le Client** (pas dans `.env`) :
- `clients.emecef_nim` — NIM attribué par la DGI
- `clients.emecef_password` — Mot de passe (chiffré en base)
- `clients.emecef_is_active` — Active l'envoi DGI
- `clients.ifu` — IFU obligatoire pour la facture normalisée

---

## ⚙️ Comportement serveur

### Double rendu des contrôleurs
Les contrôleurs suivent un pattern de **double rendu** basé sur `$request->expectsJson()` :
- **Navigation** → `view('app', ['page' => ..., 'props' => [...]])` → SPA Vue
- **Appels API** (fetch) → `response()->json($data)`

### Middleware chain (ordre d'exécution)
```
1. SetTenantContext        → Contexte multi-tenant (PostgreSQL RLS)
2. EncryptCookies          → Standard Laravel
3. StartSession            → Standard Laravel
4. Authenticate            → Vérifie session valide
5. EnsureEmailVerified     → Vérifie email (pass-through super_admin)
6. CheckNotSuspended       → Compte non suspendu
7. CheckCompanyAccess      → Redirection company_admin
8. EnsureCompanyAccess     → active_client_id valide dans user_clients
9. CheckModuleAccess       → Accès au module (module:rh, etc.)
10. Controller              → Logique métier
```

### Services injectés (constructeur)
Les services sont injectés par constructeur (auto-resolve Laravel) :
```php
class EmecefController extends Controller
{
    public function __construct(
        private readonly EmecefService $emecef
    ) {}
}
```
Services clés : `EmecefService`, `MobileMoneyService`, `SmsService`, `WhatsAppService`, `IrppCalculator`, `CnssCalculator`, `ActeGeneratorService`, `BankReconciliationService`

### Notifications (App\Models\Notification)
- Champs : `user_id`, `type`, `title`, `message`, `data` (array JSON), `read_at`
- Types : `emecef_emise`, etc.
- Scope : `unread()` — filtre les notifications non lues
- Marqueur lecture : `markAsRead()`

---

## 🖥️ Comportement client (Vue SPA)

### Initialisation
```
1. Blade template → <div id="app" data-page="company-invoices" data-props='{...}'>
2. app.js → Crée l'app Vue, injecte page + pageProps
3. Root.vue → Map résout le composant (company-invoices → Company/Invoices.vue)
4. Layout (CompanyLayout) → Sidebar + Topbar + Slot
5. authStore.initFromApi() → GET /api/me/profile
```

### authStore (stores/auth.js)
**État réactif** : `user`, `permissions[]` (module:action), `modules[]`, `companies[]`, `activeCompany`, `fieldRestrictions`

**Vérifications d'accès** :
```javascript
authStore.hasModule('facturation')       // true/false
authStore.can('facturation', 'lire')     // true/false
authStore.isFieldHidden('rh', 'salary')  // true/false
// SuperAdmin et CompanyAdmin bypassent toujours
```

**Polling permissions** : Toutes les 15s, `GET /api/company/events/check`. Si `updated === true` → `window.location.reload()`. Démarre dans `CompanyLayout.vue`, arrêté au démontage.

**Changement contexte** : `authStore.switchToCompany(clientId)` → `POST /api/me/switch-context` → recharge permissions → reload.

### Pages factures
- **GEL** (`Gel/Erp/Invoice.vue`) : Liste ErpInvoice + bouton DGI (shield) → `POST /emecef/emit/{id}`
- **Company** (`Company/Invoices.vue`) : Liste fusionnée CompanyInvoice + ErpInvoice émises. Colonne DGI avec badge vert `[DGI ✓]`. Détail avec QR code, NIM, compteur, statut. Modal paiement (cash, transfer, momo, cheque).

---

## 🔐 Droits d'accès et restrictions

### Hiérarchie des rôles (11 rôles)

| Niveau | Rôle | Slug | Périmètre |
|--------|------|------|-----------|
| 100 | Super Administrateur | `super_admin` | Total — toutes les entreprises, toutes les données |
| 50 | Comptable Cabinet | `comptable` | Comptabilité + facturation des clients assignés |
| 40 | Administrateur Entreprise | `company_admin` | Son entreprise — tous les modules |
| 30 | Manager Entreprise | `company_manager` | Opérations courantes (sauf validation/paramètres) |
| 20 | Caissier | `caissier` | Module caisse uniquement |
| 20 | Juriste | `juriste` | Module juridique uniquement |
| 20 | Gestionnaire RH | `rh` | Module RH uniquement |
| 20 | Secrétaire | `secretaire` | Module DAE uniquement |
| 20 | Gestionnaire Projet | `gestionnaire_projet` | Module projets uniquement |
| 20 | Employé | `company_employee` | Lecture seule sur les modules assignés |
| 10 | Client | `client` | Portail CPA — consultation documents et factures |

### Permissions (89 permissions, 12 modules)
Format `module:action`. Exemples : `comptabilite:lire`, `facturation:creer_facture`, `caisse:encaissement`

**Restrictions de champs** : La table `permission_field_restrictions` permet de cacher des champs sensibles par (module, action, rôle). Exemple : un employé ne voit pas le salaire dans la fiche RH.
- Côté client : `authStore.loadFieldRestrictions(module)` puis `authStore.isFieldHidden(module, field)`
- Côté serveur : `GET /api/me/field-restrictions/{module}`

### Activation des modules
- **Table** `client_modules(client_id, module, is_active)` — active/désactive un module pour un client
- **Colonne** `clients.disabled_modules` (JSON array) — alternative pour désactiver des modules
- **Middleware** `module:{module}` — bloque les routes si le module n'est pas activé
- **Côté client** — sidebars filtrées par `authStore.hasModule(module)`

---

## 🔧 Configuration post-installation et activation

### 1. Activation des modules par client
Dans l'interface super_admin → Client → Modules, activer les modules souhaités :
```sql
INSERT INTO client_modules (client_id, module, is_active, activated_at, activated_by)
VALUES (2, 'rh', true, NOW(), 1);
```
Modules disponibles : caisse, comptabilite, crm, dae, document, erp, facturation, it_assets, it_helpdesk, juridique, projets, rh

### 2. Configuration e-MECeF d'un client
1. Renseigner l'**IFU** du client (obligatoire pour la DGI)
2. Saisir le **NIM** fourni par la DGI pour ce client
3. Définir le **mot de passe** e-MECeF du client (chiffré en base)
4. Activer le **toggle** `emecef_is_active`

### 3. Création des utilisateurs entreprise
```php
UserClient::create([
    'user_id' => $user->id,
    'client_id' => $client->id,
    'role' => 'company_admin',
    'is_active' => true,
]);
$user->switchToClient($client->id);
```

### 4. Vérification (après installation)
```bash
php artisan migrate --seed    # Migrations + données démo
npm run build                 # Build frontend
```
- Connexion : `admin@gel.cabinet` / `admin123` → Dashboard GEL
- Connexion : `jean@techinnov.bj` / `admin123` → Dashboard entreprise
- Test e-MECeF : activer `EMECEF_TEST_MODE=true` dans `.env` pour simulation

### 5. Configuration .env additionnelle
```env
EMECEF_API_TOKEN=token_dgi     # Token API DGI
EMECEF_TEST_MODE=true           # true = simulation
MOMO_API_KEY=                   # MTN MoMo
MAIL_MAILER=smtp                # Notifications et envois
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## 📚 Documentation

- **[Cahier des charges](CAHIER_DES_CHARGES.md)** — Description fonctionnelle complète, modules, base de données, seeders, roadmap
- **[Architecture technique](ARCHITECTURE.md)** — Diagrammes, flux, middleware, permissions, structure des fichiers

---

## 📄 Licence

Projet privé — Tous droits réservés GEL Cabinet.

**Contact technique :** Ghislain Jules EDA
**Stack :** Laravel 12 + Vue 3 + Vite + Bootstrap 5 + MySQL
**Environnement :** XAMPP / Laragon (Windows)

---

<p align="center">
  <sub>Conçu pour le marché béninois — Conforme OHADA, SYSCOHADA et DGI e-MECeF</sub>
</p>
