# Cahier des Charges — GEL Cabinet

> **Projet :** Plateforme multi-portail de gestion de cabinet
> **Version :** 1.0.0
> **Date :** 15 juin 2026
> **Contexte :** Bénin — conformité fiscale et comptable OHADA

---

## Table des matières

1. [Présentation du projet](#1-présentation-du-projet)
2. [Architecture technique](#2-architecture-technique)
3. [Portails et profils](#3-portails-et-profils)
4. [Modules fonctionnels](#4-modules-fonctionnels)
5. [Système d'authentification et de rôles](#5-système-dauthentification-et-de-rôles)
6. [Base de données](#6-base-de-données)
7. [Seeders et données de démonstration](#7-seeders-et-données-de-démonstration)
8. [Design et interface utilisateur](#8-design-et-interface-utilisateur)
9. [Fonctionnalités transverses](#9-fonctionnalités-transverses)
10. [Structure des fichiers](#10-structure-des-fichiers)
11. [Installation et déploiement](#11-installation-et-déploiement)
12. [Roadmap et évolutions](#12-roadmap-et-évolutions)

---

## 1. Présentation du projet

### 1.1 Contexte

GEL Cabinet est une plateforme SaaS de **gestion multi-pôles** destinée aux cabinets d'expertise comptable, juridique et fiscal au Bénin. Elle centralise l'ensemble des outils nécessaires à la gestion d'un cabinet : CRM, GED, comptabilité, ERP, paie, missions, etc.

### 1.2 Objectifs

- **Centraliser** la gestion du cabinet dans une seule plateforme
- **Digitaliser** les processus comptables et fiscaux
- **Faciliter** la collaboration entre les équipes du cabinet et leurs clients
- **Automatiser** les tâches répétitives (déclarations, relances, rappels)
- **Sécuriser** les données avec un système de rôles et permissions granulaire
- **Proposer** un portail client moderne inspiré des standards internationaux (Crescendo CPA)

### 1.3 Public cible

| Profil | Description |
|--------|-------------|
| **Cabinet d'expertise comptable** | Gestion des clients, missions, déclarations |
| **Entreprises clientes** | Accès à leurs données comptables, RH, déclarations |
| **Particuliers** | Suivi des déclarations fiscales personnelles |
| **Comptables** | Gestion multi-clients, tâches et échéances |

---

## 2. Architecture technique

### 2.1 Stack technologique

| Couche | Technologie | Version |
|--------|-------------|---------|
| **Backend** | Laravel (PHP) | 12 |
| **Frontend** | Vue 3 (Composition API, `<script setup>`) | 3.x |
| **Meta-framework** | Inertia.js | 2.x |
| **Build** | Vite | 7.x |
| **CSS** | Bootstrap 5 + CSS personnalisé | 5.3 |
| **Icônes** | Bootstrap Icons + Lucide | - |
| **Animations** | MotionOne (Motion) | - |
| **Base de données** | MySQL | - |
| **Environnement** | XAMPP / Laragon | - |

### 2.2 Polices

- **Corps de texte :** Inter (300, 400, 500, 600, 700)
- **Titres :** Outfit (400, 600, 700, 800, 900)

### 2.3 Palette de couleurs

| Rôle | Couleur | Code hex |
|------|---------|----------|
| **Primaire (orange)** | Accent principal, CTA, badges | `#FF7900` |
| **Primaire hover** | Survol des éléments orange | `#e06700` |
| **Bleu foncé** | Texte foncé, headers, sidebar | `#163A5E` |
| **Noir carbone** | Titres, texte principal | `#111827` |
| **Fond principal** | Arrière-plan global | `#FFFFFF` |
| **Fond clair** | Sections alternées | `#F8FAFC` |
| **Bordure** | Bordures de cartes | `#E2E8F0` |

### 2.4 Structure MVC

```
Request → Middleware → Controller → Service/Repository → Model → Database
                                                         ↓
                                                    Response ← Inertia (Vue)
```

---

## 3. Portails et profils

La plateforme propose **4 portails distincts** :

### 3.1 Portail Public (Landing)

- **URL :** `/`
- **Fichier :** `resources/views/landing.blade.php`
- **Accès :** Sans authentification
- **Contenu :** Présentation du cabinet, modules, services, témoignages, formulaire de contact, FAQ

### 3.2 Portail GEL Cabinet (Super Admin)

- **URL :** `/dashboard` (après connexion)
- **Rôle requis :** `super_admin`
- **Layout :** `GelLayout.vue`
- **Fonctionnalités :**
  - Tableau de bord global du cabinet
  - Gestion des clients (CRUD complet)
  - Gestion des pôles (départements)
  - Gestion des missions (assignation, suivi)
  - GED (documents, dossiers, indexation)
  - Comptabilité (plan comptable, journaux, balance, bilan)
  - ERP (stocks, factures, trésorerie)
  - RH & Paie (employés, contrats, déclarations)
  - Caisse (encaissements, décaissements)
  - Projets (suivi budgétaire, jalons)
  - Juridique (dossiers, veille)
  - Modules IA (assistant, scraping, prédictions)

### 3.3 Portail Client Entreprise (Company)

- **URL :** `/company/dashboard`
- **Rôle requis :** `company_admin` (avec `client_id`)
- **Layout :** `CompanyLayout.vue`
- **Fonctionnalités :**
  - Tableau de bord financier (CA, dépenses, bénéfices)
  - Gestion des employés / RH
  - Déclarations fiscales et sociales
  - Documents partagés
  - Facturation
  - Accès aux licences et utilisateurs

### 3.4 Portail CPA Client (Client Particulier & Comptable)

- **URL :** `/cpa-dashboard`
- **Rôle requis :** `client`, `comptable`, `super_admin`
- **Layout :** `CpaLayout.vue`
- **Design :** Fond blanc dominant, orange `#FF7900` + bleu `#163A5E`
- **4 vues dans un seul composant :**

#### Vue Administrateur (super_admin)
- Stats globales : clients, dossiers en cours/terminés/en attente, revenus
- Liste des clients récents avec statut
- Graphiques d'activité
- Alertes et notifications

#### Vue Client Particulier (client)
- « Mes Déclarations de revenus »
- Statut du dossier (en cours / complété / en attente)
- Checklist documents à fournir / reçus
- Messagerie avec le comptable
- Historique des années précédentes

#### Vue Client Entreprise (company_admin)
- Tableau de bord financier
- Suivi de paie
- Alertes fiscales (échéances)
- Documents partagés récents
- Accès RH

#### Vue Comptable (comptable)
- Dossiers assignés (liste des clients)
- Tâches à compléter
- Calendrier fiscal (échéances)
- Messagerie avec clients

### 3.5 Portail Public (Catalogue E-commerce)

- **URL :** `/nos-services`
- **Layout :** `resources/js/Layouts/GelLayout.vue`
- **Pages :** Catalogue produits, panier, réservation, commande (wizard)
- **Paiement :** Mobile Money (MoMo)

---

## 4. Modules fonctionnels

### 4.1 CRM Clients (`/clients/*`)

Suivi complet des entreprises clientes et de leurs contacts.

| Fonctionnalité | Description |
|----------------|-------------|
| Listing clients | Tableau avec recherche, filtres, export |
| Fiche client | Coordonnées, contacts, historique, missions associées |
| Contacts | Multi-contacts par client avec rôles |
| Catégorisation | Par secteur, taille, type de mission |
| Export | PDF, CSV |

**Contrôleur :** `App\Http\Controllers\Gel\ClientController`  
**Modèle :** `App\Models\Client`

### 4.2 GED — Gestion Électronique de Documents (`/documents/*`, `/dossiers/*`)

Gestion des arborescences de dossiers et documents avec permissions.

| Fonctionnalité | Description |
|----------------|-------------|
| Arborescence | Dossiers imbriqués (ClientFolder) |
| Upload | Upload par glisser-déposer |
| Indexation | Tags, mots-clés, catégories |
| Recherche | Plein texte sur les métadonnées |
| Permissions | Par dossier, par rôle |
| Prévisualisation | PDF, images |
| Versioning | Historique des versions (DocumentVersion) |

**Modèles :** `ClientFolder`, `Document`, `DocumentVersion`

### 4.3 Pôles & Missions (`/poles/*`, `/missions/*`)

Organisation en départements (pôles) avec suivi des missions.

| Fonctionnalité | Description |
|----------------|-------------|
| Pôles | Création, modification, responsable |
| Missions | Assignation, statut, priorité, échéance |
| Étapes | Sous-tâches avec progression |
| Temps | Suivi du temps passé |

**Modèles :** `Pole`, `Mission`, `MissionStep`

### 4.4 Comptabilité (`/comptabilite/*`)

Chaîne comptable complète conforme au plan comptable OHADA.

| Fonctionnalité | Description |
|----------------|-------------|
| Plan comptable | Comptes généraux et auxiliaires |
| Journaux | Ventes, achats, banque, caisse, OD |
| Balance | Balance générale et auxiliaire |
| Grand livre | Par compte avec cumuls |
| Bilan | Génération automatique |
| Compte de résultat | Génération automatique |
| Exercices | Clôture et ouverture |

**Modèles :** `Account`, `JournalEntry`, `JournalLine`, `Balance`, `FiscalYear`

### 4.5 ERP Intégré (`/erp/*`)

Gestion des stocks, factures et abonnements.

| Fonctionnalité | Description |
|----------------|-------------|
| Catalogue | Produits/services avec prix |
| Stocks | Entrées, sorties, inventaire |
| Factures | Création, envoi, relances |
| Abonnements | Licences logicielles, renouvellements |
| Devis | Génération et suivi |

**Modèles :** `Product`, `StockMovement`, `Invoice`, `InvoiceLine`, `Subscription`

### 4.6 RH & Paie (`/rh/*`, `/paie/*`)

Gestion des employés et de la paie.

| Fonctionnalité | Description |
|----------------|-------------|
| Employés | Contrats, coordonnées, documents |
| Contrats | CDI, CDD, prestation |
| Paie | Bulletins, variables, calculs |
| Déclarations sociales | CNSS, IRPP |
| Congés | Demandes, validation, solde |

**Modèles :** `Employee`, `Contract`, `Payslip`, `LeaveRequest`

### 4.7 Facturation (`/facturation/*`)

Gestion des abonnements, prestations, devis et factures.

| Fonctionnalité | Description |
|----------------|-------------|
| Abonnements | Plans tarifaires, cycles |
| Devis | Création, envoi, signature |
| Factures | Séries, génération récurrente |
| Paiements | Suivi, relances |

**Modèles :** `Abonnement`, `Devis`, `DevisProduit`, `Facture`, `FactureProduit`

### 4.8 Caisse (`/caisse/*`)

Gestion des flux de trésorerie.

| Fonctionnalité | Description |
|----------------|-------------|
| Encaissements | Entrées d'argent |
| Décaissements | Sorties d'argent |
| Rapports | Journaux de caisse |
| Rapprochement | Bancaire |

**Modèles :** `Caisse`, `CaisseTransaction`

### 4.9 Projets (`/projets/*`)

Suivi de projets avec budget et jalons.

| Fonctionnalité | Description |
|----------------|-------------|
| Projets | Création, équipe, budget |
| Tâches | Assignation, priorité, statut |
| Jalons | Dates clés, livrables |
| Budget | Suivi des coûts |

**Modèles :** `Project`, `ProjectTask`, `ProjectMilestone`

### 4.10 Juridique (`/juridique/*`)

Veille et dossiers juridiques.

| Fonctionnalité | Description |
|----------------|-------------|
| Dossiers | Suivi des dossiers juridiques |
| Veille | Actualités juridiques |
| Formalités | Constitution, modifications |

**Modèle :** `LegalCase`

### 4.11 IA & Automatisation (`/ia/*`)

Fonctionnalités basées sur l'intelligence artificielle.

| Fonctionnalité | Description |
|----------------|-------------|
| Assistant IA | Chatbot contextuel |
| Scraping | Collecte automatique de données |
| Prédictions | Scores, tendances |

### 4.12 Blog / Actualités (`/blog/*`)

Gestion de contenu éditorial.

| Fonctionnalité | Description |
|----------------|-------------|
| Articles | Publication, catégories, tags |
| RSS | Export automatique |
| SEO | Meta-descriptions, slugs |

**Modèle :** `Article`

---

## 5. Système d'authentification et de rôles

### 5.1 Middleware disponibles

| Middleware | Rôle | Description |
|------------|------|-------------|
| `admin` | `super_admin` | Accès au portail GEL Cabinet |
| `company` | `company_admin` | Accès au portail entreprise |
| `company.auth` | `company_admin` | Vérifie l'appartenance au client |
| `role:{rôle}` | Variable | Vérifie un rôle spécifique |
| `module:{module}` | Variable | Vérifie l'accès à un module |
| `not_client` | ≠ `client` | Empêche les clients purs |

### 5.2 Définition dans `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => CheckAdmin::class,
        'role' => CheckRole::class,
        'company' => EnsureCompanyAccess::class,
        'company.auth' => EnsureCompanyAuth::class,
        'module' => CheckModuleAccess::class,
        'not_client' => RedirectIfClient::class,
    ]);
    $middleware->append(SetTenantContext::class);
    // ...
});
```

### 5.3 Flux d'authentification

```
POST /login → AuthenticatedSessionController::store
  → LoginRequest::authenticate()
  → Session regenerate
  → last_login_at mis à jour
  → Redirection selon le rôle :
      • session('order_service_id') → commande.step
      • company_admin → company.dashboard
      • client → client.orders.index
      • super_admin / comptable → cpa.dashboard
```

### 5.4 Pages d'authentification

| Page | URL | Fichier |
|------|-----|---------|
| Connexion (standard) | `/login` | `auth/login.blade.php` |
| Connexion (CPA) | `/cpa-login` | `resources/js/Pages/Auth/CpaLogin.vue` |
| Inscription | `/register` | `auth/register.blade.php` |
| Mot de passe oublié | `/forgot-password` | Standard Laravel |

### 5.5 Système de permissions (Vue)

Le store `authStore` expose :

- `authStore.user.id` — ID utilisateur
- `authStore.user.role` — Rôle (`super_admin`, `company_admin`, `client`, `comptable`)
- `authStore.user.client_id` — Client rattaché
- `authStore.hasModule(module)` — Vérifie l'accès à un module
- `authStore.modules` — Liste des modules disponibles

**Modules disponibles :** clients, documents, poles, missions, comptabilite, erp, rh_paie (7 modules)

---

## 6. Base de données

### 6.1 Modèle conceptuel

La base de données `gel_cabinet` contient **67+ tables** organisées par domaine :

#### Tables principales

| Table | Description |
|-------|-------------|
| `users` | Utilisateurs (tous profils) |
| `clients` | Clients (entreprises) du cabinet |
| `contacts` | Personnes contacts chez les clients |
| `licenses` | Licences logicielles par client |
| `client_folder` | Arborescence de dossiers par client |
| `documents` | Fichiers uploadés |
| `document_versions` | Historique des versions |

#### Pôles & Missions

| Table | Description |
|-------|-------------|
| `poles` | Départements du cabinet |
| `missions` | Missions assignées |
| `mission_steps` | Étapes de mission |

#### Comptabilité

| Table | Description |
|-------|-------------|
| `accounts` | Plan comptable |
| `journal_entries` | Écritures comptables |
| `journal_lines` | Lignes d'écritures |
| `balances` | Balances |
| `fiscal_years` | Exercices comptables |

#### ERP

| Table | Description |
|-------|-------------|
| `products` | Produits/services |
| `stock_movements` | Mouvements de stock |
| `invoices` | Factures |
| `invoice_lines` | Lignes de facture |
| `subscriptions` | Abonnements |

#### RH & Paie

| Table | Description |
|-------|-------------|
| `employees` | Employés |
| `contracts` | Contrats de travail |
| `payslips` | Bulletins de paie |
| `leave_requests` | Demandes de congé |

#### Facturation

| Table | Description |
|-------|-------------|
| `abonnements` | Plans tarifaires |
| `devis` | Devis |
| `devis_produits` | Lignes de devis |
| `factures` | Factures |
| `facture_produits` | Lignes de facture |

#### Caisse

| Table | Description |
|-------|-------------|
| `caisses` | Caisses |
| `caisse_transactions` | Transactions |

#### Projets

| Table | Description |
|-------|-------------|
| `projects` | Projets |
| `project_tasks` | Tâches |
| `project_milestones` | Jalons |

#### Juridique & Blog

| Table | Description |
|-------|-------------|
| `legal_cases` | Dossiers juridiques |
| `articles` | Articles de blog |

### 6.2 Migrations

Toutes les migrations suivent le pattern Laravel standard et sont exécutées via `php artisan migrate`. La structure des clés étrangères utilise des contraintes avec `onDelete('cascade')` pour garantir l'intégrité référentielle.

---

## 7. Seeders et données de démonstration

### 7.1 Liste des seeders

| Seeder | Description |
|--------|-------------|
| `DatabaseSeeder` | Orchestrateur principal |
| `AdminSeeder` | Compte super admin principal |
| `ClientSeeder` | Clients de démonstration |
| `CrescendoDemoSeeder` | 4 comptes démo pour portail CPA |
| `PoleSeeder` | Pôles de démonstration |
| `MissionSeeder` | Missions de démonstration |
| Comptabilité | Plan comptable, journaux, écritures |
| ERP | Produits, mouvements, factures |
| RH | Employés, contrats, paie |
| Facturation | Abonnements, devis, factures |
| Caisse | Transactions de caisse |
| Projets | Projets, tâches, jalons |
| Blog | Articles de démonstration |

### 7.2 Comptes de démonstration (CrescendoDemoSeeder)

| Rôle | Email | Mot de passe | Données associées |
|------|-------|-------------|-------------------|
| Super Admin | `admin@monprojet.com` | `Admin2025!` | Accès complet |
| Client particulier | `client1@test.com` | `Client2025!` | 2 dossiers fiscaux |
| Client entreprise | `entreprise@test.com` | `Entreprise2025!` | Licences + 2 utilisateurs |
| Comptable | `comptable@monprojet.com` | `Comptable2025!` | Clients assignés |

### 7.3 Principe d'idempotence

Tous les seeders utilisent `firstOrCreate()` / `updateOrCreate()` pour garantir qu'ils peuvent être exécutés plusieurs fois sans générer d'erreurs de clés dupliquées.

---

## 8. Design et interface utilisateur

### 8.1 Principes généraux

- **Fond blanc** (`#FFFFFF`) dominant sur tous les portails
- **Ombres légères** : `box-shadow: 0 1px 3px rgba(0,0,0,0.06)`
- **Coins arrondis** : `border-radius: 8px` (cartes) / `4px` (boutons)
- **Transitions** : `transition: all 0.2s ease` sur les interactions
- **Micro-interactions** : hover scale(1.02), color shift, underline animations

### 8.2 Landing page

- Navbar glassmorphism (fond blanc 95% + backdrop-filter blur)
- Hero carousel (3 slides avec autoplay 6s)
- Sections : stats animées, valeurs, pourquoi GEL, services, témoignages, fonctionnalités, processus, CTA, contact, footer
- Animations scroll au défilement (Intersection Observer)
- Compteurs animés avec easing cubic-bezier

### 8.3 Portail CPA (CpaLayout)

- Header fixe avec logo + navigation + menu utilisateur
- Sidebar gauche (fixe desktop, rétractable mobile)
- Fond blanc avec dot pattern via pseudo-élément `::before`
- Vague décorative SVG en haut du contenu
- Cartes avec animation staggered (`@keyframes cpa-card-in`)
- Barres de progression animées pour les statuts
- Loader avec spinner double bordure + glow

### 8.4 Responsive

| Breakpoint | Comportement |
|------------|--------------|
| < 768px (Mobile) | Sidebar en drawer overlay, navigation basse, cartes stacked |
| 768-1024px (Tablette) | Sidebar rétractable, grille 2 colonnes |
| > 1024px (Desktop) | Sidebar fixe 240px, grille 3-4 colonnes |

---

## 9. Fonctionnalités transverses

### 9.1 Messagerie interne

- Système de messagerie entre comptables et clients
- Intégré dans le dashboard CPA
- Interface type chat avec historique

### 9.2 Notifications

- Alertes en temps réel
- Rappels d'échéances fiscales
- Notifications de tâches
- Toast system (Vue)

### 9.3 Gestion des fichiers

- Upload par glisser-déposer (Dropzone)
- Prévisualisation des PDF/images
- Versioning automatique
- Permissions par dossier

### 9.4 Export et rapports

- Export PDF des déclarations
- Export CSV des listes (clients, factures, etc.)
- Génération de rapports personnalisés
- Tableaux de bord imprimables

### 9.5 Sécurité

- Authentification Laravel Breeze (session-based)
- CSRF protection via `_token` (form submission)
- Permissions par middleware et store Vue
- Audit des accès (timestamps `last_login_at`)
- Session : SESSION_DRIVER=file, SESSION_LIFETIME=120 min
- Cookies : same_site=lax, http_only=true

---

## 10. Structure des fichiers

### 10.1 Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # Authentification (Login, Register, Password)
│   │   ├── Gel/               # Portail super admin (60+ contrôleurs)
│   │   │   ├── ClientController
│   │   │   ├── PoleController
│   │   │   ├── MissionController
│   │   │   ├── DocumentController
│   │   │   ├── Comptabilite/  # Account, Journal, Balance, etc.
│   │   │   ├── Erp/           # Product, Stock, Invoice
│   │   │   ├── Rh/            # Employee, Contract, Payslip
│   │   │   ├── Facturation/   # Abonnement, Devis, Facture
│   │   │   ├── Caisse/
│   │   │   ├── Projets/
│   │   │   ├── Juridique/
│   │   │   └── Ia/
│   │   ├── Cpa/               # Portail CPA
│   │   │   └── DashboardController
│   │   ├── Company/           # Portail entreprise
│   │   └── Public/            # Catalogue, panier, blog
│   ├── Middleware/            # 7 middlewares personnalisés
│   └── Requests/              # Form requests (Auth, etc.)
├── Models/                    # 67+ modèles Eloquent
└── Providers/                 # Service providers
```

### 10.2 Frontend (Vue 3 + Inertia)

```
resources/
├── views/
│   ├── landing.blade.php      # Page d'accueil statique
│   ├── app.blade.php          # Shell Inertia
│   └── auth/                  # Login, Register (Blade)
├── js/
│   ├── app.js                 # Point d'entrée Vite
│   ├── Layouts/
│   │   ├── GelLayout.vue      # Layout super admin
│   │   ├── CpaLayout.vue      # Layout CPA (635 lignes)
│   │   └── CompanyLayout.vue  # Layout entreprise
│   ├── Pages/
│   │   ├── Auth/
│   │   │   └── CpaLogin.vue   # Login CPA (586 lignes)
│   │   ├── Cpa/
│   │   │   └── Dashboard.vue  # Dashboard CPA (1880 lignes)
│   │   └── Public/            # Catalogue, shop
│   ├── Components/            # Composants réutilisables
│   ├── stores/
│   │   └── auth.js            # Store d'authentification
│   └── css/
│       ├── app.css            # Global
│       └── company.css        # Company portal
```

### 10.3 Routes (`routes/web.php` — ~600 lignes)

```
/                          → Landing page (public)
/nos-modules               → Modules présentations (public)
/nos-services              → Catalogue e-commerce (public)
/services/*                → Pages services (public)
/blog*, /blogue*           → Blog/actualités (public)
/clients/*                 → CRM (auth + admin)
/documents/*               → GED (auth + admin)
/dossiers/*                → Dossiers clients (auth + admin)
/poles/*                   → Pôles (auth + admin)
/missions/*                → Missions (auth + admin)
/comptabilite/*            → Comptabilité (auth + admin)
/erp/*                     → ERP (auth + admin)
/rh/*, /paie/*             → RH & Paie (auth + admin)
/facturation/*             → Facturation (auth + admin)
/caisse/*                  → Caisse (auth + admin)
/projets/*                 → Projets (auth + admin)
/juridique/*               → Juridique (auth + admin)
/ia/*                      → IA (auth + admin)
/company/*                 → Portail entreprise (auth + company)
/cpa-login                 → Login CPA (public)
/cpa-register              → Register CPA (public)
/cpa-dashboard             → Dashboard CPA (auth)
/api/cpa/stats             → API stats CPA (auth)
/commande/*                → Wizard commande (mixed)
require __DIR__.'/auth.php' → Routes auth Laravel
```

---

## 11. Installation et déploiement

### 11.1 Prérequis

- PHP 8.2+
- MySQL
- Composer
- Node.js 20+
- NPM

### 11.2 Installation

```bash
# 1. Cloner le dépôt
git clone <url> Para
cd Para

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances JS
npm install

# 4. Copier et configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
DB_DATABASE=gel_cabinet
DB_USERNAME=root
DB_PASSWORD=

# 6. Migrations et seeders
php artisan migrate --seed

# 7. Build frontend
npm run build

# 8. Lancer le serveur
php artisan serve
```

### 11.3 Commandes utiles

```bash
# Seeder spécifique
php artisan db:seed --class=CrescendoDemoSeeder

# Build en développement
npm run dev

# Build en production
npm run build

# Compilation à chaud (Vite)
npx vite
```

---

## 12. Roadmap et évolutions

### ✅ Version 1.0 (actuelle)

- [x] Landing page complète avec carousel, sections, animations
- [x] Authentification multi-portail (Laravel Breeze)
- [x] Portail GEL Cabinet (Super Admin) — 60+ contrôleurs
- [x] CRM Clients — CRUD, contacts, catégorisation
- [x] GED — Dossiers, documents, upload, versioning
- [x] Pôles & Missions — Organisation et suivi
- [x] Comptabilité — Plan comptable, journaux, balance, bilan
- [x] ERP — Produits, stocks, factures, abonnements
- [x] RH & Paie — Employés, contrats, paie, déclarations
- [x] Facturation — Abonnements, devis, factures, paiements
- [x] Caisse — Encaissements, décaissements, rapports
- [x] Projets — Tâches, jalons, budget
- [x] Juridique — Dossiers, veille
- [x] IA — Assistant, scraping, prédictions
- [x] Blog / Actualités
- [x] Portail Entreprise (Company)
- [x] Portail CPA Client (Dashboard 4 profils)
- [x] Catalogue e-commerce + Panier + Paiement MoMo
- [x] 16 seeders avec données de démonstration
- [x] Design responsive (mobile, tablette, desktop)

### 🔜 Version 1.1

- [ ] Modules IA avancés (analyse prédictive, scoring)
- [ ] PWA (Progressive Web App)
- [ ] Notifications push
- [ ] Mode hors-ligne partiel
- [ ] Import/export Excel automatisé
- [ ] Signature électronique
- [ ] Portail RH pour les employés

### 🔜 Version 2.0

- [ ] Application mobile native (Flutter / React Native)
- [ ] API REST publique
- [ ] Marché d'applications (extensions)
- [ ] Webhooks et intégrations tierces
- [ ] Multilingue (français → anglais)
- [ ] Dashboard analytics avancé (charts temps réel)
- [ ] Automatisation des déclarations fiscales (API Impôts Bénin)

---

## Annexes

### A. Licence

Projet privé — Tous droits réservés GEL Cabinet.

### B. Contact technique

- **Développeur :** Ghislain Jules EDA
- **Stack :** Laravel 12 + Vue 3 + Inertia + Vite + Bootstrap 5 + MySQL
- **Environnement :** XAMPP (Windows)

### C. Dépendances principales

#### PHP (Composer)

```
laravel/framework
inertiajs/inertia-laravel
laravel/breeze (dev)
```

#### JavaScript (NPM)

```
vue (3.x)
@inertiajs/vue3
vite
@vitejs/plugin-vue
bootstrap (5.3)
bootstrap-icons
lucide-vue-next
motion-v (MotionOne)
```

### D. Icônes utilisées

- **Bootstrap Icons** — Interface principale (nav, sidebar, actions, statuts)
- **Lucide** — Icônes supplémentaires pour graphiques et actions spécifiques

---

> **Document généré le 15 juin 2026** — Ce cahier des charges reflète l'état du projet à date et évoluera avec les versions futures.
