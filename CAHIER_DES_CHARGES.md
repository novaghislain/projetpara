# Cahier des Charges — GEL Cabinet

> **Projet :** Plateforme multi-portail de gestion de cabinet
> **Version :** 2.1.0
> **Date :** 20 juin 2026
> **Contexte :** Bénin — conformité fiscale et comptable OHADA

---

## Table des matières

1. [Présentation du projet](#1-présentation-du-projet)
2. [Architecture technique](#2-architecture-technique)
   - [Stack technologique](#21-stack-technologique)
   - [Polices](#22-polices)
   - [Palette de couleurs](#23-palette-de-couleurs)
   - [Architecture de rendu](#24-architecture-de-rendu)
   - [Architecture des templates](#25-architecture-des-templates)
   - [Architecture des layouts Vue](#26-architecture-des-layouts-vue)
   - [Architecture des données (auth + permissions)](#27-architecture-des-données-auth--permissions)
   - [Middleware chain](#28-middleware-chain)
3. [Portails et profils](#3-portails-et-profils)
4. [Modules fonctionnels](#4-modules-fonctionnels)
   - [4.1 CRM Clients](#41-crm-clients)
   - [4.2 GED](#42-ged)
   - [4.3 Pôles & Missions](#43-pôles--missions)
   - [4.4 Comptabilité](#44-comptabilité)
   - [4.5 ERP](#45-erp)
   - [4.6 RH & Paie](#46-rh--paie)
   - [4.7 Facturation](#47-facturation)
   - [4.8 Caisse](#48-caisse)
   - [4.9 Projets](#49-projets)
   - [4.10 Juridique](#410-juridique)
   - [4.11 IA & Automatisation](#411-ia--automatisation)
   - [4.12 Blog / Actualités](#412-blog--actualités)
   - [4.13 Catalogue e-commerce](#413-catalogue-e-commerce)
   - [4.14 Secrétariat DAE](#414-secrétariat-dae)
   - [4.15 Comptabilité avancée & Conformité](#415-comptabilité-avancée--conformité-bénin)
   - [4.16 Services Informatiques (IT)](#416-services-informatiques-it)
   - [4.17 Automatisation intelligente](#417-automatisation-intelligente)
   - [4.18 Paiements Mobiles](#418-paiements-mobiles)
   - [4.19 Tontine / Microfinance](#419-tontine--microfinance)
   - [4.20 Signature électronique](#420-signature-électronique)
   - [4.21 Workflows d'approbation](#421-workflows-dapprobation)
5. [Système d'authentification et de rôles](#5-système-dauthentification-et-de-rôles)
6. [Base de données](#6-base-de-données)
7. [Seeders et données de démonstration](#7-seeders-et-données-de-démonstration)
8. [Design et interface utilisateur](#8-design-et-interface-utilisateur)
9. [Fonctionnalités transverses](#9-fonctionnalités-transverses)
10. [Structure des fichiers](#10-structure-des-fichiers)
11. [Installation et déploiement](#11-installation-et-déploiement)
12. [Roadmap et évolutions](#12-roadmap-et-évolutions)
13. [Extension Stratégique v2.0 — Récapitulatif](#13-extension-stratégique-v20--récapitulatif)

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
- **Conquérir le marché béninois** avec des fonctionnalités locales différenciantes (e-MECeF, tontines, Mobile Money, barèmes IRPP/CNSS Bénin)

### 1.4 Avantage concurrentiel — Extension Stratégique v2.0

La version 2.0 ajoute des fonctionnalités qui font de GEL Cabinet **la plateforme la plus complète du marché béninois** :

| Domaine | Fonctionnalité clé | Impact |
|---------|-------------------|--------|
| Conformité | e-MECeF / Sygmef | Obligatoire légal — certification DGI |
| Comptabilité | Télédéclaration, SYSCOHADA, analytique | Gain de temps, conformité OHADA |
| Paie | IRPP/CNSS barèmes Bénin | Calcul automatique, tranches progressives |
| IT | Helpdesk + ITAM + Maintenance | Nouveau pôle de revenus |
| Paiements | Mobile Money (MTN, Moov) | Adoption mobile massive au Bénin |
| Mobile | SMS, WhatsApp, PWA | Accessibilité maximale |
| Finance locale | Tontine / Microfinance | Aucun concurrent ne le propose |
| Sécurité | 2FA, Audit, Permissions granulaires | Confiance grandes entreprises |

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
| **Rendu** | Blade + SPA Vue (composants globaux via `app.component()`) | — |
| **Build** | Vite | 7.x |
| **CSS** | Bootstrap 5 + CSS personnalisé | 5.3 |
| **Icônes** | Bootstrap Icons | — |
| **Base de données** | MySQL (MariaDB) | — |
| **Environnement** | XAMPP / Laragon (Windows) | — |

### 2.2 Polices

- **Corps de texte :** Inter (300, 400, 500, 600, 700)
- **Titres :** Outfit (400, 600, 700, 800, 900)

### 2.3 Palette de couleurs — Dark Premium

| Rôle | Couleur | Code hex |
|------|---------|----------|
| **Primaire (orange)** | Accent principal, CTA, badges, hover | `#FF7900` |
| **Primaire hover** | Survol des éléments orange | `#e06700` |
| **Sidebar bg start** | Dégradé sidebar (haut) | `#0B1120` |
| **Sidebar bg end** | Dégradé sidebar (bas) | `#0F1A2E` |
| **Sidebar texte** | Texte navigation | `rgba(255,255,255,0.55)` |
| **Sidebar texte hover** | Texte navigation survol | `rgba(255,255,255,0.85)` |
| **Sidebar actif** | Item actif (bg + border-left 3px) | `rgba(255,121,0,0.1)` + `#FF7900` |
| **Topbar** | Glass effect (blur 12px) | `rgba(255,255,255,0.9)` |
| **Fond page** | Arrière-plan contenu | `#F5F7FA` |
| **Cartes** | Fond cartes + ombre douce | `#FFFFFF` + `0 1px 3px rgba(0,0,0,0.04)` |
| **Bordure carte** | Bordures subtiles | `rgba(0,0,0,0.06)` |
| **Texte principal** | Corps de texte | `#1F2937` |
| **Texte secondaire** | Labels, métadonnées | `#6B7280` |

### 2.4 Architecture de rendu

Le projet utilise **deux templates Blade** comme points d'entrée. Chaque template charge le même bundle Vue mais avec des props différentes :

| Template | Usage | Rendu assets | CSS/JS |
|----------|-------|-------------|--------|
| `resources/views/app.blade.php` | Portails GEL + CPA + Public | Chemins hardcodés depuis `manifest.json` | `<link>` + `<script>` statiques |
| `resources/views/company.blade.php` | Portail entreprise | `@vite()` directive | Résolution automatique via Vite |

**Flux de rendu complet :**

```
  NAVIGATEUR (URL)
        │
        ▼
  routes/web.php
        │
        ▼
  Middleware globaux + routes (bootstrap/app.php) :
    • SetTenantContext        → SET app.client_id (PostgreSQL RLS only, skip sur MySQL)
    • EncryptCookies / StartSession
    • Authenticate (si route protégée)
    • not_suspended / verified / ensure.company / company.auth / module:*
        │
        ▼
  Controller (ex: Company/UserController)
        │
        ▼
  return view('app', [
    'page' => 'nom-du-composant',   ← clé pour Root.vue
    'props' => ['key' => 'value'],  ← props passées au composant Vue
  ]);
  // OU view('company', ...) pour le portail entreprise
        │
        ▼
  Blade template (app.blade.php / company.blade.php)
    • Injecte #app avec data-page + data-props (JSON)
    • Injecte <script id="auth-data"> avec l'utilisateur connecté
      (id, name, email, role, client_id, active_client_id, is_super_admin,
      is_company_admin, is_comptable, is_client, is_suspended,
      must_change_password, email_verified_at)
    • Injecte window.__CLIENT_ID__
        │
        ▼
  app.js (Vue entry point)
    • Crée l'app Vue
    • Enregistre tous les composants via app.component('nom', Component)
    • Monte sur #app
        │
        ▼
  Root.vue (composant racine unique)
    • inject('page') → lit data-page
    • Résout le composant via une Map<string, Component>
    • Passe les props via inject('props')
    • Rendu : <component :is="pageComponent" v-bind="pageProps" />
        │
        ▼
  Layout (GelLayout / CompanyLayout / CpaLayout)
    • Sidebar + topbar + slot
        │
        ▼
  Page spécifique (Dashboard.vue, Users.vue, etc.)
    • Contenu dans le slot du layout
```

### 2.5 Architecture des templates

#### `app.blade.php` (GEL + CPA + Public)

```
┌──────────────────────────────────────────────────────────────┐
│ <head>                                                       │
│   Bootstrap 5 CSS (CDN)                                      │
│   Google Fonts (Inter + Outfit)                              │
│   Asset CSS (build/assets/app-*.css)                         │
│ </head>                                                      │
│ <body>                                                       │
│   <div id="app" data-page="..." data-props='{...}' />       │
│   Bootstrap JS (CDN)                                         │
│   <script id="auth-data">{"user": {...}}</script>            │
│   Asset JS (build/assets/app-*.js)                           │
│ </body>                                                      │
│                                                              │
│ ← Les chemins CSS/JS sont hardcodés depuis manifest.json     │
│   après chaque npm run build                                 │
└──────────────────────────────────────────────────────────────┘
```

#### `company.blade.php` (Portail Entreprise)

```
┌──────────────────────────────────────────────────────────────┐
│ <head>                                                       │
│   Bootstrap 5 CSS (CDN)                                      │
│   Google Fonts (Inter + Outfit)                              │
│   @vite(['resources/js/app.js'])  ← résolution automatique  │
│ </head>                                                      │
│ <body>                                                       │
│   <div id="app" data-page="..." data-props='{...}' />       │
│   Bootstrap JS (CDN)                                         │
│   <script id="auth-data">{"user": {...}}</script>            │
│   <script>window.__CLIENT_ID__ = ...</script>                │
│ </body>                                                      │
│                                                              │
│ ← @vite() résout les chemins automatiquement via APP_URL    │
│   Aucune mise à jour manuelle après build                    │
└──────────────────────────────────────────────────────────────┘
```

### 2.6 Architecture des layouts Vue

```
Root.vue
  │
  ├── GelLayout.vue          (route: /dashboard, /clients/*, /comptabilite/*, ...)
  │   ├── Topbar vitrée (56px, glass effect : rgba(255,255,255,0.9) + blur(12px))
  │   │   ├── Hamburger toggle + titre de page + divider
  │   │   └── Badge rôle + avatar + dropdown user
  │   ├── Sidebar Dark Premium (260px, #0B1120 → #0F1A2E gradient) :
  │   │   ├── Logo en haut
  │   │   ├── Navigation principale (~28 items filtrés par rôle)
  │   │   │   ├── Items avec icônes 18px, opacité 0.5 → 0.85 hover
  │   │   │   └── Actif : border-left #FF7900 + bg rgba(255,121,0,0.1)
  │   │   ├── ── séparateur avec label ──
  │   │   ├── Navigation contextuelle (sidebarLinks / sidebarBySection)
  │   │   └── Bas de sidebar : avatar + nom + rôle utilisateur
  │   ├── Overlay mobile (semi-transparent, < 992px)
  │   └── <slot /> :
  │       ├── Sous-navigation horizontale (subnav inline)
  │       └── Contenu de la page (padding 24px, fond #F5F7FA)
  │
  ├── CompanyLayout.vue      (route: /company/*)
  │   ├── Topbar : logo "Portail Client" + notifications + badge entreprise + avatar
  │   ├── Sidebar verticale (240px) :
  │   │   ├── Navigation principale filtrée par modules
  │   │   ├── ── séparateur ──
  │   │   └── Paramètres + Déconnexion
  │   ├── Overlay mobile
  │   └── <slot /> :
  │       ├── Sous-navigation horizontale (flatLinks)
  │       └── Contenu de la page
  │
  └── CpaLayout.vue          (route: /cpa-dashboard)
      ├── Header fixe + sidebar gauche
      └── <slot /> : Dashboard CPA
```

### 2.7 Architecture des données (auth + permissions)

```
INIT AUTH (au chargement de la page)
═════════════════════════════════════
Blade template
  │
  ├── <script id="auth-data" type="application/json">
  │   └── {"user": {id, name, email, role, client_id, is_company_admin, ...}}
  │
  └── window.__CLIENT_ID__ = client_id (pour CompanyLayout)
        │
        ▼
resources/js/stores/auth.js :: initAuth()
  │
  ├── Parse auth-data JSON
  ├── authStore.user = data.user
  ├── authStore.isAuthenticated = !!data.user
  └── Lance authStore.initFromApi() via fetch(GET /api/me/profile)
        │
        ▼
  authStore.user = { ... données complètes ... }
  authStore.permissions = ["module:action", ...]
  authStore.modules = ["caisse", "comptabilite", ...]
  authStore.companies = [{id, company_name, rccm, ifu}, ...]
  authStore.activeCompany = { ... }
  authStore.permissionIds = [1, 2, 3, ...]
        │
        ▼
Components reactifs : computed() utilisant authStore.hasModule(),
authStore.can(), authStore.isFieldHidden() pour filtrer
la navigation, les liens rapides, les onglets, les champs

CHANGEMENT DE CONTEXTE ENTREPRISE
═════════════════════════════════
authStore.switchToCompany(clientId)
  │
  ├── POST /api/me/switch-context { client_id }
  │     → MeController::switchContext()
  │     → User::switchToClient(clientId)
  │
  ├── authStore.activeCompany mis à jour
  ├── authStore.refreshPermissions() → permissions rechargées
  └── window.location.reload()

POLLING (toutes les 15s, dans CompanyLayout)
═════════════════════════════════════
fetch(/api/company/events/check)
  │
  ├── {updated: false} → rien ne change
  └── {updated: true, permissions, modules, permission_ids}
        │
        └── authStore.modules = ...
            window.location.reload()  ← recharge la page
```

### 2.8 Middleware chain (détail)

Le noyau HTTP charge les middlewares dans cet ordre :

```
Middlewares globaux (appliqués à toutes les requêtes) :
├── SetTenantContext     → SET app.client_id (PostgreSQL RLS; early-return sur MySQL)

Middlewares d'authentification (sur les groupes protégés) :
├── auth                 → Authentification Laravel standard
├── verified             → EnsureEmailVerified — vérifie email vérifié (sauf super_admin)
├── not_suspended        → CheckNotSuspended — vérifie que le compte n'est pas suspendu

Middlewares de portail (aiguillage selon le profil) :
├── company              → CheckCompanyAccess — redirige company_admin vers /company/*
├── not_client           → EnsureNotClient — bloque les clients purs hors de leur espace
├── redirect.client      → RedirectIfClient — redirige les clients vers /mes-commandes

Middlewares de vérification de rôle :
├── gel.admin            → CheckSuperAdmin — vérifie super_admin
├── gel.comptable        → CheckComptable — vérifie comptable cabinet
├── role                 → CheckRole — vérifie un rôle spécifique (paramétrable)
├── can.action           → CheckActionPermission — vérifie module:action

Middlewares de contexte entreprise :
├── ensure.company       → EnsureCompanyAccess — vérifie active_client_id valide
├── company.auth         → EnsureIsCompanyAdmin — vérifie appartenance au client

Middlewares de module :
├── module               → CheckModuleAccess — vérifie accès module, retourne JSON 403
├── dae.secretaire       → DaeSecretaireAccess — accès spécifique DAE
├── ip.whitelist         → IpWhitelist — filtrage par IP

Middlewares additionnels de l'écosystème :
├── admin                → AdminMiddleware — ancien système, basé sur is_admin

Groupes de routes :
├── web (publiques)          → landing, catalogue, blog
├── auth (authentifié)       → dashboard, profil, commandes /api/me/*
├── gel.admin / gel.admin+company+not_client
│   → /clients/*, /documents/*, /comptabilite/*, /erp/*, /caisse/*,
│     /rh/*, /facturation/*, /juridique/*, /projets/*, /dae/*
├── company (company)        → /company/*
│   ├── module:dae           → /company/dae/*
│   ├── module:rh            → /company/rh/*
│   ├── module:document      → /company/documents/*
│   ├── module:caisse        → /company/caisse/*
│   ├── module:facturation   → /company/facturation/*
│   ├── module:comptabilite  → /company/comptabilite/*
│   ├── module:juridique     → /company/juridique/*
│   ├── module:projets       → /company/projets/*
│   └── api/*                → endpoints JSON API
├── context selection        → /context/* (sélecteur multi-entreprise)
├── api (company API)        → routes JSON protégées par module
└── module:{module}          → routes conditionnelles par module
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
  - Comptabilité (plan comptable, journaux, balance, bilan, SYSCOHADA, analytique)
  - Conformité fiscale (e-MECeF, télédéclaration TVA/IRPP/CNSS/IS)
  - ERP (stocks, factures, trésorerie, prévisionnel)
  - RH & Paie (employés, contrats, déclarations, IRPP/CNSS barèmes Bénin)
  - Services IT (helpdesk, ITAM, maintenance, base de connaissances)
  - Caisse (encaissements, décaissements)
  - Projets (suivi budgétaire, jalons)
  - Juridique (dossiers, veille)
  - Modules IA (assistant, scraping, prédictions)
  - Automatisation (OCR, rapprochement bancaire, relances)
  - Paiements mobiles (MTN MoMo, Moov Money)
  - Signature électronique
  - Workflows d'approbation
  - Tontine / Microfinance

### 3.3 Portail Client Entreprise (Company)

- **URL :** `/company/dashboard`
- **Rôle requis :** `company_admin` ou tout utilisateur avec `client_id`
- **Layout :** `CompanyLayout.vue` (sidebar verticale + topbar)
- **Design :** Sidebar sombre `#1a2938`, topbar bleu `#163A5E`, accent orange `#FF7900`
- **Fonctionnalités :**
  - Tableau de bord avec stats (services actifs, licences, utilisateurs)
  - GED — Documents partagés avec le cabinet
  - Facturation et historique des commandes
  - Comptabilité (liens vers modules)
  - RH (si module activé par le cabinet)
  - Secrétariat / DAE (courriers, contrats, documents, tâches)
  - Profil entreprise et utilisateurs
  - Notifications en temps réel (polling 15s)
- **Navigation latérale :** Section liens rapides contextuelle (`sidebarBySection`)
- **Affichage :** Badge avec nom de l'entreprise + avatar utilisateur

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
- **Composant :** `resources/js/Pages/Public/Catalogue/Index.vue`
- **Pages :** Catalogue produits, panier, réservation, commande (wizard)
- **Paiement :** Mobile Money (MoMo)
- **Design :** Hero sombre avec orbes animés + grille de cartes services par catégorie
- **Filtrage :** Recherche en temps réel par nom ou description
- **Responsive :** Grille adaptative (auto-fill, minmax 300px)

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

### 4.13 Catalogue e-commerce (`/nos-services`, `/commande/*`)

Plateforme de vente de services avec panier et paiement.

| Fonctionnalité | Description |
|----------------|-------------|
| Catalogue | Services classés par catégorie avec recherche |
| Fiche service | Description, tarif, délais, inclus |
| Panier | Ajout / retrait / validation |
| Wizard commande | Étapes de réservation |
| Paiement Mobile Money | Intégration MoMo (MTN, Moov) |

**Contrôleur :** `App\Http\Controllers\Public\*`  
**Pages Vue :** `resources/js/Pages/Public/Catalogue/`

### 4.14 Secrétariat DAE (`/company/dae/*`)

Gestion des courriers, contrats et tâches de secrétariat pour les entreprises clientes.

| Fonctionnalité | Description |
|----------------|-------------|
| Tableau de bord | Synthèse des activités |
| Courriers | Entrants / sortants avec suivi |
| Documents | Centralisation des documents administratifs |
| Contrats | Archivage et suivi des échéances |
| Tâches | Assignation et suivi |

**Contrôleur :** `App\Http\Controllers\Company\CompanyDaeController`  
**Middleware :** `module:dae`

---

### 4.15 Comptabilité avancée & Conformité Bénin

#### 4.15.1 Intégration e-MECeF / Sygmef (OBLIGATOIRE LÉGALEMENT)

La DGI Bénin impose à toutes les entreprises l'utilisation du **Système de Gestion des Machines Électroniques Certifiées de Facturation (Sygmef)**. Toute facture doit être une **Facture Normalisée** transmise et validée par l'API e-MECeF (`sygmef.impots.bj`). GEL Cabinet doit devenir un **SFE (Système de Facturation d'Entreprise) certifié DGI**.

**Package :** `codianselme/lara-sygmef` — package open-source béninois dédié

```php
// config/emecef.php
return [
    'api_token'   => env('EMECEF_API_TOKEN'),
    'nim'         => env('EMECEF_NIM'),           // Numéro d'Identification Machine
    'test_mode'   => env('EMECEF_TEST_MODE', true),
    'api_url'     => env('EMECEF_API_URL', 'https://sygmef.impots.bj/emcf/api'),
];
```

**Service :** `App\Services\EmecefService` — `emettreFactureNormalisee(Invoice $invoice)`
- Construit le payload DGI (IFU, type FV/FA/AV/EA, AIB, client, items, paiement)
- Appelle l'API e-MECeF
- Enregistre NIU, compteur, hash, QR code sur la facture
- Calcule l'AIB (Acompte sur Impôt sur les Bénéfices) : 1% DGE/DME, 5% CSI

**Migrations sur `invoices` :**
```
emecef_nim, emecef_compteur, emecef_hash, emecef_qr (TEXT),
emecef_statut (enum: non_emise/emise/annulee), emecef_datetime
```

**PDF :** QR code e-MECeF vérifiable sur `sygmef.impots.bj/verification` via `chillerlan/php-qrcode`

#### 4.15.2 Module Télédéclaration (Interface e-services DGI)

Pré-remplissage automatique des formulaires de déclarations fiscales à partir des données comptables, avec génération XML/Excel conforme aux formats DGI.

**Types de déclarations :**

| Code | Déclaration | Périodicité | Échéance |
|------|-------------|-------------|----------|
| TVA | Taxe sur la Valeur Ajoutée | Mensuelle | 15 du mois suivant |
| IRPP | Impôt sur le Revenu des Personnes Physiques (salaires) | Mensuelle | 15 du mois suivant |
| CNSS | Cotisations Sécurité Sociale | Mensuelle | 15 du mois suivant |
| AIB | Acompte sur Impôt sur les Bénéfices | Mensuelle | 15 du mois suivant |
| IS | Impôt sur les Sociétés (acomptes) | Trimestrielle | 31/03, 30/06, 30/09, 31/12 |
| IS-ANNUEL | IS solde annuel | Annuelle | 30 avril N+1 |
| TSS | Taxe sur les Salaires et Soldes | Mensuelle | 15 du mois suivant |
| ASDI | Aide Sociale aux Domestiques | Mensuelle | 15 du mois suivant |

**Table :** `tax_declarations` (client_id, type, period, status, base/tax/penalty/total amounts, declaration_data JSON, xml_export_path)

**Contrôleur :** `TeleDeclController` — calcul automatique TVA collectée vs déductible, AIB mensuel, échéance

**Génération Excel :** `TvaDeclarationExport` — format structuré conforme DGID e-services

#### 4.15.3 Calcul Automatique de la Paie (barèmes béninois IRPP 2026)

**Service :** `App\Services\Paie\IrppCalculator`

Barème progressif IRPP Bénin (annuel) :

| Tranche | Taux |
|---------|------|
| 0 — 60 000 FCFA | 0 % |
| 60 001 — 150 000 FCFA | 10 % |
| 150 001 — 250 000 FCFA | 15 % |
| 250 001 — 500 000 FCFA | 19 % |
| 500 001 — 1 000 000 FCFA | 24 % |
| 1 000 001 — 2 000 000 FCFA | 28 % |
| > 2 000 000 FCFA | 32 % |

- Abattement forfaitaire 20 % (min 200 000 FCFA, max 600 000 FCFA/an)
- Réduction pour charges de famille (n/parts) : célibataire=1, marié sans enfants=2, +0.5/enfant

**Service :** `App\Services\Paie\CnssCalculator`
- Taux employeur : 15,40 %
- Taux salarié : 3,36 %
- Plafond mensuel : 450 000 FCFA

#### 4.15.4 Comptabilité Analytique (Centres de Coûts)

**Tables :**
- `cost_centers` (client_id, code, name, type: department/project/product/region, parent_id)
- `analytic_lines` (journal_line_id, cost_center_id, percentage, amount)

Permet l'analyse des résultats **par département, projet ou activité**.

#### 4.15.5 États Financiers SYSCOHADA Complets

Les 4 états obligatoires du Plan Comptable SYSCOHADA révisé :

| État | Description | Fréquence |
|------|-------------|-----------|
| **Bilan** | Actif / Passif (immobilisations 21-28, stocks 31-39, créances 41-49, trésorerie 51-59, capitaux 10-15, dettes 16-48) | Annuel + Intermédiaire |
| **CRP** | Compte de Résultat par Nature (ventes 70, achats 60, charges personnel 66, dotations 68) | Annuel |
| **TAFIRE** | Tableau de Financement (méthode indirecte : CAF, BFR, flux trésorerie) | Annuel |
| **Notes** | Annexes aux états financiers | Annuel |

**Service :** `App\Services\Comptabilite\EtatsFinanciersService`

#### 4.15.6 Trésorerie Prévisionnelle

**Composant :** Vue graphique projetant les flux de trésorerie sur 3 à 6 mois

**Sources de données :**
- Factures émises non payées → recettes prévues
- Fournisseurs à payer → dépenses prévues
- Déclarations fiscales à venir → sorties obligatoires
- Bulletins de paie → sorties fixes

**API :** `GET /api/company/tresorerie/previsionnel?mois=6`

---

### 4.16 Services Informatiques (IT)

#### 4.16.1 Helpdesk & Ticketing IT

Centralisation des tickets de support IT avec SLA et facturation des interventions.

**Tables :**
- `it_tickets` (client_id, ticket_number TKT-2026-XXXXX, title, description, type incident/request/change/problem, priority low/medium/high/critical, status open/assigned/in_progress/pending/resolved/closed, assigned_to, sla_due_at, sla_breached, billable, billed_hours)
- `it_ticket_comments` (ticket_id, user_id, body, is_internal, attachments JSON)
- `it_sla_policies` (name, priority, first_response_hours, resolution_hours)

**Auto-numérotation :** TKT-{year}-{counter 5 digits}

**Indicateur SLA :** Vert (délai OK) / Orange (bientôt expiré) / Rouge (dépassé)

#### 4.16.2 Inventaire du Parc Informatique (ITAM)

Gestion des équipements de chaque client : ordinateurs, serveurs, imprimantes, smartphones, licences, routeurs.

**Tables :**
- `it_assets` (client_id, asset_tag, name, category computer/server/printer/network/mobile/software/other, brand, model, serial_number, status, assigned_to, location, purchase_date, purchase_price, warranty_expires_at, next_maintenance_at, os_version, ip_address, mac_address)
- `it_asset_licenses` (asset_id, client_id, software_name, license_key, seats, expires_at, vendor)
- `it_asset_interventions` (asset_id, ticket_id, technician_id, type, date, duration_minutes, description, cost)

**Alertes automatiques :** Commande hebdomadaire `CheckItAssetAlerts` — garanties et licences expirant dans 30 jours

#### 4.16.3 Contrats de Maintenance IT

**Table :** `it_maintenance_contracts` (client_id, reference, type corrective/preventive/full_service/hotline, status active/expired/suspended, start_date, end_date, monthly_amount, included_hours, response_time_hours, coverage_hours, covered_assets JSON, auto_renew)

#### 4.16.4 Base de Connaissances IT (Knowledge Base)

**Table :** `it_knowledge_base` (title, slug, category, content LONGTEXT, tags JSON, is_public, views)

Suggestions automatiques d'articles KB lors de la rédaction d'un ticket (recherche LIKE sur le titre).

#### 4.16.5 Rapport d'Intervention IT (PDF)

**Service :** `App\Services\ItRapportService` — PDF signable par le client avec technicien, date, durée, équipements, travaux, matériaux, signature.

---

### 4.17 Automatisation intelligente

#### 4.17.1 OCR et Import Automatique de Factures Fournisseurs

Scan ou photo de facture → lecture automatique des informations → pré-remplissage de l'écriture comptable.

**Stack :** `spatie/pdf-to-text` + API Claude (Anthropic) pour OCR sur images

**Service :** `OcrInvoiceService` — extrait fournisseur, numéro, date, montants, TVA, mode de paiement

#### 4.17.2 Rapprochement Bancaire Automatique

Import des relevés bancaires (CSV/OFX/MT940) → correspondances avec écritures non lettrées.

**Service :** `BankReconciliationService`
- Détection automatique de la structure des colonnes
- Matching par montant (±1 FCFA), date (±3 jours), référence/libellé (fuzzy)
- Suggestions triées par score de confiance

Formats supportés : CSV (BOA, Ecobank, BCEAO, UBA), OFX, MT940

#### 4.17.3 Relances Automatiques Clients

Règles de relance configurables par client avec déclenchement J+X après échéance.

**Table :** `relance_rules` (client_id, name, trigger_days, channel email/sms/whatsapp/all, template_subject, template_body)

**Commande :** `ProcessRelances` — quotidienne à 09:00
- Variables : `{client_name}`, `{invoice_number}`, `{amount}`, `{due_date}`, `{days_late}`
- Canaux : Email (Laravel Mail), SMS (Afrique passerelle), WhatsApp (Meta WABA)

#### 4.17.4 SMS & WhatsApp Business

**Service SMS :** `App\Services\SmsService`
- Normalisation téléphone Bénin (+229XXXXXXXX)
- Passerelle : Africa's Talking / OrangeAPI / passerelle locale
- Sender ID : "GEL Cabinet"

**Service WhatsApp :** `App\Services\WhatsAppService`
- API Meta WABA (WhatsApp Business) v18.0
- Templates approuvés (relance_facture, notification, etc.)
- Paramètres dynamiques par template

---

### 4.18 Paiements Mobiles

Intégration des moyens de paiement mobile les plus utilisés au Bénin.

**Service :** `App\Services\MobileMoneyService`
- **MTN MoMo** : API MTN MoMo Collection (requestToPay, vérification statut)
- **Moov Money** : API Moov
- **Webhook :** `POST /api/payments/momo-callback` — mise à jour statut facture

**Flux :** Demande de paiement → référence unique → callback de confirmation → marquage payé

---

### 4.19 Tontine / Microfinance

Module spécifique Bénin pour la gestion des tontines et cotisations collectives.

**Tables :**
- `tontines` (client_id, name, type tournante/epargne/credit, montant_cotisation, periodicite hebdo/quinzaine/mensuel, date_demarrage, statut)
- `tontine_membres` (tontine_id, nom, telephone, email, ordre_tour)
- `tontine_cotisations` (tontine_id, membre_id, periode, montant, statut attendue/payee/retard, date_paiement, mode_paiement)

---

### 4.20 Signature électronique

Signature dessinée (canvas) avec hash SHA256, timestamp et IP.

**Stack :** `setasign/fpdf` pour PDF

**Table :** `document_signatures` (document_id, signer_name, signer_email, signer_phone, signature_data base64, document_hash SHA256, ip_address, signed_at, token unique, expires_at)

**Flux :** Génération document → lien unique envoyé par email/WhatsApp → signature canvas → PDF final + horodatage

---

### 4.21 Workflows d'approbation

Chaînes d'approbation configurables par type d'action et par client.

**Tables :**
- `approval_workflows` (client_id, name, trigger_model, trigger_condition JSON, steps JSON, is_active)
- `approval_requests` (workflow_id, model_type, model_id, current_step, status pending/approved/rejected/cancelled, requested_by)
- `approval_steps_log` (request_id, step_number, approver_id, action approved/rejected/delegated, comment)

**Exemple :** Paiement > 500 000 FCFA → validation manager → validation directeur général

### 4.22 Gestion Commerciale et Point de Vente (POS)

Module destiné aux entreprises de commerce, supermarchés, boutiques, pharmacies, quincailleries et magasins. Il transforme GEL Cabinet en un véritable outil de gestion commerciale avec Point de Vente intégré.

---

#### 4.22.1 Importation massive des produits

L'importation se fait via fichier Excel/CSV avec un template téléchargeable. L'utilisateur structure ses données hors ligne et les importe en un clic.

**Fonctionnalités :**
- Template d'import standard (nom, catégorie, prix achat, prix vente, TVA, stock initial, fournisseur, code-barres)
- Mapping automatique des colonnes + contrôle des doublons par code-barres
- Import différé : l'utilisateur prévisualise ses données avant validation définitive
- Rapport complet : produits importés, erreurs lignes ignorées, doublons détectés
- Historique des imports (fichier source, date, utilisateur, statut)

---

#### 4.22.2 Gestion du catalogue produits

Interface CRUD complète pour administrer les produits de l'entreprise.

**Fonctionnalités :**
- **Catégories** : arborescence hiérarchique (ex : Boissons → Sucrées → Jus), actif/inactif, couleur
- **Produits** : nom, description, catégorie, marque, code-barres (EAN-13 ou interne), SKU, prix de vente HT/TTC, prix d'achat, marges automatiques, TVA (taux multiples), unité (pièce, kg, litre, etc.)
- **Fournisseurs** : nom, contact, téléphone, email, délais de livraison
- **Images produits** : galerie multi-images, image par défaut
- **Stock** : stock initial, stock d'alerte, seuil critique, emplacement (rayon/étagère)
- **Variantes** : taille, couleur, parfum, conditionnement (lot de 6, carton de 12)
- **Bundles / Kits** : produit composé de plusieurs articles avec prix spécial
- **Prix promotionnels** : dates début/fin, prix barré, quantité minimum
- **Étiquettes / Tags** : filtre libre, Nouveauté, Meilleure vente, Rupture
- **Recherche** : par code-barres, nom, SKU, catégorie, fournisseur
- **Export catalogue** : PDF (fiche produit), Excel (catalogue complet)

---

#### 4.22.3 Gestion des utilisateurs commerciaux

Gestion spécifique des profils commerciaux, distincte des utilisateurs classiques du cabinet.

**Rôles spécifiques :**
1. **Admin Entreprise** — Accès complet à la gestion commerciale, paramétrage, utilisateurs, tableaux de bord, validation des opérations sensibles
2. **DG / Directeur** — Lecture seule sur l'ensemble des données, export des rapports, vue consolidée des ventes et stocks
3. **Commercial** — Gestion du catalogue (création/modification produits), suivi des stocks, commandes fournisseurs, devis clients
4. **Stock Manager** — Gestion des entrées/sorties de stock, inventaire, bons de livraison, alertes seuil critique
5. **Caissier / Vendeur** — Accès limité à l'interface de caisse (vente, encaissement, ticket), **uniquement son propre tiroir**
6. **Auditeur** — Consultation seule des ventes, stocks, marges, historique des opérations

**Tables :**
- `business_users` (id, client_id, user_id, role enum, is_active, created_by, permissions JSON)
- L'utilisateur est lié à la table `users` existante (réutilisation du système d'authentification)

---

#### 4.22.4 Interface dédiée aux caissiers

Interface épurée, sécurisée et verrouillée destinée uniquement aux opérations de vente. Aucun accès à la comptabilité, aux prix, aux marges ou à la configuration.

**Règles strictes :**
- Le caissier ne voit **que** l'écran de caisse — pas de dashboard, pas de menu latéral
- Le caissier ne peut **ni modifier les prix** ni appliquer de remise > 5%
- Le caissier ne voit **pas** le prix d'achat, les marges, ni le stock global
- Chaque opération est liée à un `cashier_session_id` avec timestamp d'ouverture/fermeture
- Session de caisse : seul le caissier ouvre et ferme sa caisse (montant d'ouverture, clôture, écart)

**Fonctionnalités :**
- Saisie rapide par code-barres (focus automatique sur le champ de scan)
- Recherche produits par nom avec auto-complétion
- Affichage ticket en temps réel (quantités, sous-total, remise, total TTC)
- Paiement fractionné (espèces + mobile money + carte)
- Impression ticket thermique (format 80mm) via imprimante USB/BT
- Envoi ticket par email/SMS au client
- Passage en mode "hors ligne" avec file d'attente locale (synchro automatique)

---

#### 4.22.5 Point de Vente (POS) complet

Moteur de vente central qui orchestre l'ensemble des opérations d'encaissement.

**Parcours de vente :**
1. **Ouverture de session** → le caissier enregistre son fonds de caisse initial
2. **Scan / Saisie** → ajout rapide des articles (barcode ou recherche)
3. **Ajustements** → quantité, remise (dans limite), note, client (optionnel)
4. **Paiement** → sélection du mode de paiement
5. **Finalisation** → génération ticket + mise à jour stock
6. **Clôture** → total encaissé, écart, versement

**Moyens de paiement :**
- **Espèces** → encaissement direct, calcul rendu monnaie automatique
- **MTN Mobile Money (MoMo)** → API MTN Bénin, validation du paiement, gestion des échecs
- **Moov Money** → API Moov Bénin
- **Carte bancaire** → terminal TPE / API partenaire
- **e-MECeF** → génération de facture fiscalisée via API e-MECeF Bénin

**Facture e-MECeF obligatoire :**
- Chaque vente génère une facture normalisée conforme à la réglementation béninoise
- Transmission en temps réel à l'administration fiscale via l'API e-MECeF
- Impression du QR code fiscal sur le ticket
- Gestion des annulations et avoirs avec motif fiscal obligatoire

**Gestion des remboursements / retours :**
- Retour d'article avec justificatif (numéro de ticket original)
- Calcul de la valeur de remboursement (prix payé, pas le prix courant)
- Avoir ou remboursement selon mode de paiement original
- Motif de retour obligatoire (client, défaut, erreur, périmé)
- Réintégration automatique en stock (ou mise au rebut selon motif)

---

#### 4.22.6 Gestion automatique des stocks

Système de stock intelligent interconnecté avec les ventes, les achats et les mouvements.

**Règles de gestion :**
- **Déduction automatique** : chaque vente confirmée décrémente le stock en temps réel
- **Blocage vente** : si stock ≤ stock d'alerte, le produit peut être vendu mais avec avertissement
- **Blocage critique** : si stock = 0, toute vente est refusée (sauf si vente sur commande activée)

**Mouvements de stock :**
- Entrée : achat fournisseur, retour client, correction inventaire, production/assemblage
- Sortie : vente, retour fournisseur, mise au rebut, perte/casse, don/échantillon
- Transfert : entre dépôts/magasins (suivi par lot)
- **Bon de mouvement** obligatoire pour toute opération

**Alertes automatiques :**
- **Seuil d'alerte** : notification au Stock Manager et au Commercial
- **Seuil critique** : notification à l'Admin Entreprise + email
- **Produit en rupture** : marquage automatique "Rupture de stock" dans le catalogue
- **Produit périmé** : alerte pour les produits avec date de péremption

**Inventaire :**
- Création d'une session d'inventaire (date, dépôt, utilisateur)
- Saisie des quantités réelles par produit (ou scan code-barres)
- Écart automatique entre stock théorique et stock réel
- Validation avec motif d'écart (perte, erreur, casse, vol)
- Ajustement du stock après validation

---

#### 4.22.7 Tableau de bord du dirigeant

Vue consolidée des performances commerciales, accessible à l'Admin Entreprise et au DG.

**Indicateurs en temps réel (ou J-1) :**
- Chiffre d'affaires du jour / mois / année (avec comparaison périodes précédentes)
- Nombre de transactions / ticket moyen
- Produits les plus vendus (Top 10)
- Ventes par catégorie (camembert)
- Ventes par caisse / par caissier
- Ventes par mode de paiement (espèces, MoMo, carte)
- Taux de marge moyen (visible uniquement Admin et DG)
- Évolution CA (graphique linéaire J-30, J-90, J-365)

**Gestion des rapports :**
- Rapport quotidien PDF automatique (email à la clôture)
- Rapport mensuel consolidé
- Export Excel personnalisable

---

#### 4.22.8 Sécurité et traçabilité

Exigences critiques pour un module manipulant des flux financiers et des stocks.

**Traçabilité :**
- Audit trail complet sur chaque table :
  - `product_audits` : CREATE / UPDATE / DELETE → qui, quoi, quand, ancienne valeur, nouvelle valeur
  - `sale_audits` : annulation, remboursement, modification après validation
  - `stock_audits` : tout mouvement de stock (qui, type, quantité, avant/après, motif)
  - `cashier_session_audits` : ouverture, clôture, écart, versement

**Contrôle d'accès :**
- Validation des accès à chaque action (middleware `role:commerce`)
- Champ `field_restrictions` : cache les champs sensibles selon le rôle
- Le caissier ne voit pas les prix d'achat, marges, coûts
- 2FA obligatoire pour Admin Entreprise et DG (paramétrable)

**Sécurité des sessions de caisse :**
- Impossible d'avoir deux sessions ouvertes simultanément pour le même caissier
- Écart de caisse > 5000 FCFA → alerte Admin Entreprise
- Clôture forcée en fin de journée (minuit → session automatiquement fermée)
- Historique des accès à la caisse (login/logout, ouverture/fermeture)

---

#### 4.22.9 Architecture technique

**Nouveaux contrôleurs :**
- `app/Http/Controllers/Commerce/ProductController.php`
- `app/Http/Controllers/Commerce/CategoryController.php`
- `app/Http/Controllers/Commerce/SupplierController.php`
- `app/Http/Controllers/Commerce/PosSaleController.php`
- `app/Http/Controllers/Commerce/StockController.php`
- `app/Http/Controllers/Commerce/InventoryController.php`
- `app/Http/Controllers/Commerce/BusinessUserController.php`
- `app/Http/Controllers/Commerce/DashboardController.php`

**Nouveaux modèles :**
- `Product`, `ProductCategory`, `ProductImage`, `ProductVariant`, `Supplier`
- `PosSession`, `Sale`, `SaleItem`, `Payment`
- `StockMovement`, `InventorySession`, `InventoryLine`
- `BusinessUser`, `BusinessRole`

**Tables principales (migrations) :**
- `product_categories` (id, client_id, name, slug, parent_id, color, is_active)
- `products` (id, client_id, category_id, name, description, brand, barcode, sku, price_ht, price_ttc, price_purchase, tva_rate, unit, stock_qty, stock_alert, stock_critical, location, is_active)
- `product_images` (id, product_id, path, is_primary, sort_order)
- `product_variants` (id, product_id, name, barcode, price_ht, price_ttc, stock_qty)
- `suppliers` (id, client_id, name, contact_name, phone, email, address, delivery_delay)
- `product_suppliers` (id, product_id, supplier_id, reference, price)
- `business_roles` (id, client_id, role enum, permissions JSON, is_active)
- `business_users` (id, client_id, user_id, role_id, is_active, created_by)
- `pos_sessions` (id, client_id, business_user_id, opened_at, closed_at, opening_amount, closing_amount, difference, status)
- `sales` (id, client_id, pos_session_id, business_user_id, customer_name, customer_phone, subtotal, discount, discount_type, total_ht, total_ttc, tax_amount, paid_amount, change_amount, status, emecef_uid, qr_code)
- `sale_items` (id, sale_id, product_id, variant_id, quantity, unit_price_ht, unit_price_ttc, discount, total_ht, total_ttc)
- `payments` (id, sale_id, payment_method, amount, reference, status, gateway_response JSON)
- `stock_movements` (id, client_id, product_id, variant_id, type, quantity, stock_before, stock_after, reference_type, reference_id, motif, created_by)
- `inventory_sessions` (id, client_id, status, created_by, validated_by, validated_at)
- `inventory_lines` (id, inventory_session_id, product_id, theoretical_qty, actual_qty, difference, motif)

**Routes API :**
```
/api/commerce/products           GET    → liste paginée + recherche + filtres
/api/commerce/products           POST   → création
/api/commerce/products/{id}      GET    → détail
/api/commerce/products/{id}      PUT    → mise à jour
/api/commerce/products/{id}      DELETE → suppression
/api/commerce/products/import    POST   → import Excel/CSV
/api/commerce/products/template  GET    → télécharger template
/api/commerce/products/export    GET    → export Excel/PDF
/api/commerce/products/stock     POST   → ajustement stock
/api/commerce/categories         CRUD
/api/commerce/suppliers          CRUD
/api/commerce/business-users     CRUD
/api/commerce/pos/sessions       CRUD
/api/commerce/pos/sell           POST   → vente complète
/api/commerce/pos/return         POST   → retour
/api/commerce/pos/receipt/{id}   GET    → ticket
/api/commerce/stock/movements    GET    → liste mouvements
/api/commerce/stock/inventory    CRUD   → sessions inventaire
/api/commerce/dashboard          GET    → indicateurs
```

**Intégration :**
- Ce module est totalement **indépendant** des modules comptabilité et ERP existants
- Il peut fonctionner seul ou en synchronisation avec la facturation et la comptabilité
- Les ventes POS peuvent être exportées vers la comptabilité en écriture centralisée
- Le module s'intègre avec le **système d'utilisateurs existant** (table `users`)
- La **gérabilité des sociétés** existante (`user_clients`, `active_client_id`) est réutilisée
- Compatible e-MECeF Bénin pour la facture fiscalisée
- Compatible MTN MoMo Bénin et Moov Money pour les paiements mobiles

## 5. Système d'authentification et de rôles

### 5.1 Middleware disponibles

| Middleware | Classe PHP | Usage | Description |
|-----------|------------|-------|-------------|
| `admin` | `AdminMiddleware` | Route | Vérifie `is_admin` (ancien système) |
| `role` | `CheckRole` | Route | Vérifie un rôle spécifique (`role:super_admin`) |
| `company` | `CheckCompanyAccess` | Route | Redirige company_admin vers /company/dashboard |
| `company.auth` | `EnsureIsCompanyAdmin` | Route | Vérifie que l'utilisateur a un `client_id` |
| `module` | `CheckModuleAccess` | Route | Vérifie l'accès à un module (`module:rh`), retourne JSON 403 si refusé |
| `not_client` | `EnsureNotClient` | Route | Bloque les clients purs (role=client) |
| `gel.admin` | `CheckSuperAdmin` | Route | Vérifie que l'utilisateur est super_admin via le système de rôles |
| `gel.comptable` | `CheckComptable` | Route | Vérifie que l'utilisateur est comptable cabinet |
| `ensure.company` | `EnsureCompanyAccess` | Route | Vérifie que l'utilisateur a un `active_client_id` valide dans `user_clients` |
| `verified` | `EnsureEmailVerified` | Route | Vérifie que l'email est confirmé (sauf super_admin) |
| `not_suspended` | `CheckNotSuspended` | Route | Vérifie que le compte n'est pas suspendu |
| `can.action` | `CheckActionPermission` | Route | Vérifie une permission spécifique (`can.action:comptabilite,lire`) |
| `redirect.client` | `RedirectIfClient` | Route | Redirige les clients purs vers `/mes-commandes` |
| `dae.secretaire` | `DaeSecretaireAccess` | Route | Accès restreint au module DAE |
| `ip.whitelist` | `IpWhitelist` | Route | Filtrage par adresse IP |

**SetTenantContext** : Middleware **global** qui exécute `SET app.client_id` pour PostgreSQL RLS. Sur MySQL (environnement actuel), il est neutralisé par un early-return dès la détection du driver.

### 5.2 Définition dans `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin'          => \App\Http\Middleware\AdminMiddleware::class,
        'role'           => \App\Http\Middleware\CheckRole::class,
        'company'        => \App\Http\Middleware\CheckCompanyAccess::class,
        'company.auth'   => \App\Http\Middleware\EnsureIsCompanyAdmin::class,
        'module'         => \App\Http\Middleware\CheckModuleAccess::class,
        'not_client'     => \App\Http\Middleware\EnsureNotClient::class,
        'dae.secretaire' => \App\Http\Middleware\DaeSecretaireAccess::class,
        'ip.whitelist'   => \App\Http\Middleware\IpWhitelist::class,
        // Nouveaux middlewares multi-tenant (session 2026-06-19)
        'gel.admin'      => \App\Http\Middleware\CheckSuperAdmin::class,
        'gel.comptable'  => \App\Http\Middleware\CheckComptable::class,
        'ensure.company' => \App\Http\Middleware\EnsureCompanyAccess::class,
        'verified'       => \App\Http\Middleware\EnsureEmailVerified::class,
        'not_suspended'  => \App\Http\Middleware\CheckNotSuspended::class,
        'can.action'     => \App\Http\Middleware\CheckActionPermission::class,
        'redirect.client'=> \App\Http\Middleware\RedirectIfClient::class,
    ]);
    $middleware->append(\App\Http\Middleware\SetTenantContext::class);
});
```

### 5.3 Flux d'authentification (8 étapes)

```
POST /login → AuthenticatedSessionController::store
  │
  ├── 1. CREDENTIALS : LoginRequest::authenticate()
  │     → Validation email + mot de passe
  │     → Session::regenerate()
  │
  ├── 2. SUSPENSION : Vérification isSuspended()
  │     → Si suspendu : logout + message d'erreur
  │
  ├── 3. EMAIL : Vérification email_verified_at
  │     → Si non vérifié (sauf super_admin) : logout + redirection
  │
  ├── 4. PASSWORD CHANGE : Vérification must_change_password
  │     → Si true : session('must_change_password') = true
  │     → L'utilisateur reste connecté mais sera redirigé
  │
  ├── 5. 2FA : Vérification two_factor_confirmed_at
  │     → Si 2FA activé : logout + redirect vers 2fa.challenge
  │     → Vérification TOTP ou code de secours
  │
  ├── 6. METADATA : Enregistrement de connexion
  │     → last_login_at = now()
  │     → last_login_ip = $request->ip()
  │     → login_count += 1
  │
  ├── 7. AUDIT : AuditTrail::create(event: 'login')
  │
  └── 8. REDIRECTION selon le rôle :
        • session('order_service_id') → commande.step
        • super_admin → dashboard (GEL Cabinet)
        • comptable → cpa.dashboard
        • multi-entreprises (>1 UserClient) sans active_client_id → select.context
        • company_admin / company_manager / company_employee → company.dashboard
        • client → client.orders.index
        • secretaire (role_secretaire) → dae.dashboard
        • fallback → dashboard (GEL)
```

### 5.4 Pages d'authentification

| Page | URL | Fichier |
|------|-----|---------|
| Connexion (standard) | `/login` | `auth/login.blade.php` |
| Connexion (CPA) | `/cpa-login` | `resources/js/Pages/Auth/CpaLogin.vue` |
| Inscription | `/register` | `auth/register.blade.php` |
| Mot de passe oublié | `/forgot-password` | Standard Laravel |

### 5.5 Système de permissions (Vue)

Le store `authStore` (reactive, singleton) expose :

**État :**
| Propriété | Type | Description |
|-----------|------|-------------|
| `authStore.user` | Object | Utilisateur connecté (id, name, email, role, client_id, etc.) |
| `authStore.isAuthenticated` | Boolean | Utilisateur connecté |
| `authStore.modules` | String[] | Liste des modules accessibles (ex: `["caisse", "comptabilite"]`) |
| `authStore.permissions` | String[] | Permissions formatées `module:action` (ex: `"caisse:encaissement"`) |
| `authStore.permissionIds` | Number[] | IDs des permissions directes |
| `authStore.companies` | Object[] | Liste des entreprises disponibles (`[{id, company_name, rccm, ifu}]`) |
| `authStore.activeCompany` | Object | Entreprise actuellement sélectionnée |
| `authStore.fieldRestrictions` | Object | Restrictions de champs par module `{ module: ['field1', 'field2'] }` |

**Getters :**
| Getter | Description |
|--------|-------------|
| `authStore.hasMultipleCompanies` | L'utilisateur a accès à plusieurs entreprises |
| `authStore.activeCompanyName` | Nom de l'entreprise active |
| `authStore.isCompanyUser` | L'utilisateur est un utilisateur d'entreprise |
| `authStore.isCompanyAdmin` | L'utilisateur est admin d'entreprise |
| `authStore.isSuperAdmin` | L'utilisateur est super admin |
| `authStore.isComptable` | L'utilisateur est comptable cabinet |
| `authStore.isClient` | L'utilisateur est un client |
| `authStore.mustChangePassword` | L'utilisateur doit changer son mot de passe |

**Méthodes :**
| Méthode | Description |
|---------|-------------|
| `authStore.init(data)` | Initialisation depuis les données du tag auth-data |
| `authStore.initFromApi()` | Chargement complet depuis `GET /api/me/profile` |
| `authStore.hasModule(module)` | Vérifie l'accès à un module |
| `authStore.can(module, action)` | Vérifie une action spécifique dans un module |
| `authStore.isFieldHidden(module, field)` | Vérifie si un champ est masqué |
| `authStore.loadFieldRestrictions(module)` | Charge les restrictions de champs depuis l'API |
| `authStore.switchToCompany(clientId)` | Bascule le contexte entreprise |
| `authStore.refreshPermissions()` | Recharge les permissions depuis `GET /api/me/permissions` |
| `authStore.checkForUpdates()` | Vérifie les changements (polling) |

**Modules disponibles (12) :**
```
caisse, comptabilite, crm, dae, document, erp,
facturation, it_assets, it_helpdesk, juridique, projets, rh
```

**Polling :**
| Fonction | Description |
|----------|-------------|
| `startPermissionPolling()` | Démarre le polling toutes les 15s via `/api/company/events/check` |
| `stopPermissionPolling()` | Arrête le polling |
| `initAuth()` | Parse `<script id="auth-data">` et appelle `initFromApi()` |

**Fonctionnement :**
1. Au chargement, `initAuth()` parse les données du `<script id="auth-data">` dans le Blade
2. `initFromApi()` est appelée immédiatement pour charger permissions + modules + companies depuis `/api/me/profile`
3. `startPermissionPolling()` (démarré dans CompanyLayout) vérifie `/api/company/events/check` toutes les 15s
4. Si des changements sont détectés, `window.location.reload()` est déclenché automatiquement

### 5.6 Gestion des accès par module

Les routes sont protégées côté serveur par le middleware `module:{module}` :

```php
// Exemple : routes RH
Route::middleware('module:rh')->group(function () {
    Route::get('/company/rh/employees', ...);
    Route::get('/company/rh/leaves', ...);
    // ...
});
```

Côté frontend, la sidebar et les liens rapides sont filtrés par `authStore.hasModule(module)`, avec une redirection vers `/company/dashboard` si l'utilisateur tente d'accéder à une page dont le module est désactivé.

Pour les **company_admin**, tous les modules sont accessibles sauf ceux explicitement désactivés dans `clients.disabled_modules`. Pour les employés non-admin, l'accès dépend de leur rôle et permissions directes.

### 5.7 Permissions granulaires par champ et par action

Extension des permissions existantes pour supporter les **restrictions de champ** :

```sql
CREATE TABLE permission_field_restrictions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module VARCHAR(50) NOT NULL,              -- 'rh', 'comptabilite', etc.
    action VARCHAR(50) NOT NULL,              -- 'lire', 'modifier', etc.
    role_slug VARCHAR(50) DEFAULT NULL,       -- NULL = applicable à tous les rôles
    hidden_fields JSON NOT NULL,              -- ["salary", "bank_account"]
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED DEFAULT NULL,
    timestamps,
    INDEX idx_module_action (module, action),
    INDEX idx_module_role (module, role_slug),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

**API :** `GET /api/me/field-restrictions/{module}` — retourne les champs cachés pour l'utilisateur connecté dans le module donné.

**Côté frontend :** `authStore.loadFieldRestrictions(module)` → `authStore.isFieldHidden(module, field)`

**Matrice de permissions (page d'administration) :**

```
┌──────────────────┬────────┬────────┬────────┬────────┬───────────┐
│ Module           │ Lire   │ Créer  │Modifier│Supprimer│ Exporter │
├──────────────────┼────────┼────────┼────────┼────────┼───────────┤
│ Comptabilité     │ ✅ Dir │ ✅ Dir │ ✅ Dir │ ❌     │ ✅ Dir   │
│ RH (liste)       │ ✅ All │ ✅ DRH │ ✅ DRH │ ❌     │ ✅ DRH   │
│ RH (salaires)    │ ❌ Emp │ ✅ DRH │ ✅ DRH │ ❌     │ ✅ DRH   │
└──────────────────┴────────┴────────┴────────┴────────┴───────────┘
```

### 5.8 Délégation temporaire de permissions

**Table :** `permission_delegations` (delegator_id, delegate_id, permissions JSON, reason, valid_from, valid_until, is_active)

Permet à un utilisateur de déléguer temporairement ses permissions à un collègue (ex : pendant un congé).

---

## 6. Base de données

### 6.1 Modèle conceptuel

La base de données `gel_cabinet` contient **90+ tables** organisées par domaine :

#### Tables principales

| Table | Description |
|-------|-------------|
| `users` | Utilisateurs (tous profils) — enrichi avec `active_client_id`, `is_suspended`, `two_factor_*`, `last_login_*`, `must_change_password` |
| `clients` | Clients (entreprises) du cabinet |
| `contacts` | Personnes contacts chez les clients |
| `licenses` | Licences logicielles par client |
| `client_folder` | Arborescence de dossiers par client |
| `documents` | Fichiers uploadés |
| `document_versions` | Historique des versions |

#### Tables multi-tenant (session 2026-06-19)

| Table | Description |
|-------|-------------|
| `user_clients` | Pivot : liaison utilisateur ↔ entreprise avec rôle, statut, dates |
| `client_modules` | Activation des modules par client (client_id, module, is_active) |
| `comptable_clients` | Assignation des comptables aux clients |
| `permission_field_restrictions` | Restrictions de champs par (module, action, rôle) |
| `permission_delegations` | Délégation temporaire de permissions entre utilisateurs |

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

#### Conformité Fiscale (e-MECeF)

| Table | Description |
|-------|-------------|
| `tax_declarations` | Déclarations fiscales (TVA, IRPP, CNSS, IS, AIB, TSS, ASDI) |
| — | Colonnes e-MECeF sur `invoices` (nim, compteur, hash, qr, statut, datetime) |

#### Comptabilité Analytique

| Table | Description |
|-------|-------------|
| `cost_centers` | Centres de coûts (département, projet, produit, région) |
| `analytic_lines` | Répartition analytique des lignes d'écritures |

#### Services Informatiques (IT)

| Table | Description |
|-------|-------------|
| `it_tickets` | Tickets helpdesk avec SLA |
| `it_ticket_comments` | Fils de discussion par ticket |
| `it_sla_policies` | Politiques de niveau de service |
| `it_assets` | Inventaire du parc informatique |
| `it_asset_licenses` | Licences logicielles |
| `it_asset_interventions` | Historique des interventions par équipement |
| `it_maintenance_contracts` | Contrats de maintenance IT |
| `it_knowledge_base` | Base de connaissances IT |

#### Automatisation & Relances

| Table | Description |
|-------|-------------|
| `relance_rules` | Règles de relances automatiques clients |

#### Approbations

| Table | Description |
|-------|-------------|
| `approval_workflows` | Chaînes d'approbation configurables |
| `approval_requests` | Demandes d'approbation en cours |
| `approval_steps_log` | Journal des étapes d'approbation |

#### Tontine / Microfinance

| Table | Description |
|-------|-------------|
| `tontines` | Groupes de tontine |
| `tontine_membres` | Membres des tontines |
| `tontine_cotisations` | Cotisations et suivi des paiements |

#### Signature électronique

| Table | Description |
|-------|-------------|
| `document_signatures` | Signatures électroniques avec hash SHA256 et timestamp |

#### Sécurité & Audit

| Table | Description |
|-------|-------------|
| `audit_logs` | Journal d'audit complet (toutes les actions sensibles) |
| `permission_field_restrictions` | Restrictions de champs par permission |
| `permission_delegations` | Délégations temporaires de permissions |
| — | Colonnes 2FA sur `users` (two_factor_secret, recovery_codes, confirmed_at) |
| — | Colonnes sécurité sur `clients` (require_2fa, session_timeout_minutes, allowed_ips) |

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

- **GEL Cabinet (Dark Premium)** : Sidebar `#0B1120` ultra-sombre, topbar vitrée, cartes blanches `border-radius: 12px`, ombres légères
- **Portail entreprise** : Sidebar sombre `#1a2938`, topbar `#163A5E`, classes `isup-*`
- **Portail CPA** : Fond blanc dominant, orange `#FF7900` + bleu `#163A5E`
- **Ombres légères** : `box-shadow: 0 1px 3px rgba(0,0,0,0.04)`
- **Coins arrondis** : `border-radius: 12px` (cartes) / `6px` (boutons)
- **Transitions** : `transition: all 0.2s ease` sur les interactions
- **Micro-interactions** : hover scale(1.02), color shift, underline animations

### 8.2 Landing page

- Navbar glassmorphism (fond blanc 95% + backdrop-filter blur)
- Hero carousel (3 slides avec autoplay 6s)
- Sections : stats animées, valeurs, pourquoi GEL, services, témoignages, fonctionnalités, processus, CTA, contact, footer
- Animations scroll au défilement (Intersection Observer)
- Compteurs animés avec easing cubic-bezier

### 8.3 Layout GEL Cabinet (GelLayout.vue) — Super Admin (Dark Premium)

- **Sidebar** : Dégradé vertical `#0B1120` → `#0F1A2E` (260px desktop), rétractable
  - Texte navigation : `rgba(255,255,255,0.55)` → hover `rgba(255,255,255,0.85)`
  - Item actif : border-left 3px `#FF7900` + bg `rgba(255,121,0,0.1)`
  - Icônes : 18px, opacité 0.5 → 0.9 active
  - Avatar utilisateur en bas : photo/initiale + nom + rôle
  - Scrollbar stylisée fine sombre
  - Transitions douces 0.2s sur hover/active
- **Topbar vitrée** : 56px sticky, `rgba(255,255,255,0.9)` + `backdrop-filter: blur(12px)`, border-bottom 1px `rgba(0,0,0,0.06)`
  - Hamburger menu à gauche, titre de page, badge rôle + avatar + notifications à droite
- **Logo** : Déplacé de la topbar vers la sidebar (en haut)
- **Contenu** : `padding: 24px`, fond `#F5F7FA`
- **Cartes** : `border-radius: 12px`, `box-shadow: 0 1px 3px rgba(0,0,0,0.04)`
- **Navigation principale** : Accueil, Clients, Missions, Comptabilité, Commandes, Catalogue, Finance ERP, Caisse, Administration, Admins, Secrétariat, Juridique, RH, IT Support, Tontines, Télédécl., Signatures, Validations, Relances, Centres coûts, e-MECeF, OCR, Paie, Sécurité, Audit, Articles, Commerce (~28 items avec filtrage par rôle)
- **Navigation contextuelle** : Sous-navigation horizontale intégrée au main (subnav inline)
- **Mobile** : Sidebar en drawer overlay + overlay semi-transparent (< 992px)

### 8.4 Layout Entreprise (CompanyLayout.vue)

- Topbar bleu `#163A5E` avec logo "Portail Client — GEL", notifications, badge entreprise, avatar utilisateur
- Sidebar verticale sombre `#1a2938` (240px desktop, rétractable via toggle)
- Items de navigation filtrés par module (visible uniquement si l'utilisateur a accès)
- Liens rapides contextuels (`sidebarBySection`) sous la navigation principale
- Sous-navigation horizontale dans le contenu principal
- Barre de recherche rapide + onglets horizontaux dans le dashboard
- Pastilles statistiques dans l'en-tête du dashboard

### 8.5 Portail CPA (CpaLayout.vue)

- Header fixe avec logo + navigation + menu utilisateur
- Sidebar gauche (fixe desktop, rétractable mobile)
- Fond blanc avec dot pattern via pseudo-élément `::before`
- Vague décorative SVG en haut du contenu
- Cartes avec animation staggered (`@keyframes cpa-card-in`)
- Barres de progression animées pour les statuts
- Loader avec spinner double bordure + glow
- Design : palette bleu `#163A5E` + orange `#FF7900`, fond blanc dominant

### 8.6 Catalogue public (Pages/Public/Catalogue/Index.vue)

- Navbar glassmorphism réutilisée depuis la landing page
- Hero sombre avec gradient animé, orbes floues, particules
- Titre + sous-titre avec animation de fondu au chargement
- Barre de recherche avec focus glow
- Stats : nombre de services, pôles, assistance 24/7
- Grille de cartes services avec :
  - Icône par catégorie (couleur dynamique)
  - Badge de prix (fixe ou sur devis)
  - Description tronquée (3 lignes max)
  - Métadonnées (délai, inclus)
  - Barre de couleur en haut au survol
  - Animation staggered au défilement
- CTA section avec gradient + orbe décorative
- Footer sombre identique à la landing page

### 8.7 Système de classes partagées (isup-*)

Les classes `isup-*` dans `resources/css/company.css` fournissent un design system unifié :

| Classe | Usage |
|--------|-------|
| `.isup-shell` | Conteneur principal avec bordure et ombre |
| `.isup-portal-header` | En-tête bleu avec stats (flex, space-between, wrap) |
| `.isup-portal-logo` | Icône carrée 42px semi-transparente |
| `.isup-portal-company` | Nom entreprise en Outfit 18px blanc |
| `.isup-stat-pill` | Pastille statistique dans le header |
| `.isup-panel` | Carte de contenu avec header bleu |
| `.isup-table` | Tableau stylé avec en-têtes uppercase |
| `.isup-status-*` | Badges de statut (green/red/grey/blue/orange) |
| `.isup-btn-primary` | Bouton orange standard |
| `.isup-input` / `.isup-select` | Champs de formulaire unifiés |
| `.isup-modal-*` | Système de modales |
| `.isup-alert-*` | Messages d'alerte (success/error/warning) |

### 8.8 Responsive

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

#### 9.5.1 Authentification de base
- Authentification Laravel Breeze (session-based)
- CSRF protection via `_token` (form submission)
- Permissions par middleware et store Vue
- Session : `SESSION_DRIVER=file`, `SESSION_LIFETIME=120` min
- Cookies : `same_site=lax`, `http_only=true`

#### 9.5.2 Authentification à Deux Facteurs (2FA)

**Packages :** `pragmarx/google2fa-laravel`, `bacon/bacon-qr-code`

**Flux :**
1. Activation depuis le profil utilisateur → QR code (Google Authenticator / Authy)
2. À chaque connexion, si 2FA activé → formulaire code TOTP (6 chiffres)
3. Codes de secours (recovery codes) générés et stockés hashés

**Migrations sur `users` :**
```
two_factor_secret (TEXT), two_factor_recovery_codes (TEXT), two_factor_confirmed_at (TIMESTAMP)
```

**Politique 2FA par client :** Le super_admin peut imposer le 2FA à tous les utilisateurs d'un client via `clients.require_2fa`.

#### 9.5.3 Journal d'Audit Complet

**Table :** `audit_logs` — trace chaque action sensible : qui, quoi, quand, IP, user-agent

| Colonne | Description |
|---------|-------------|
| `user_id`, `client_id` | Contexte de l'action |
| `action` | create, update, delete, view, export, login, logout |
| `model_type`, `model_id` | Modèle concerné |
| `old_values`, `new_values` | JSON des données avant/après modification |
| `ip_address`, `user_agent`, `url`, `session_id` | Contexte technique |

**Trait `Auditable`** à appliquer sur les modèles sensibles (Invoice, JournalEntry, User, etc.) :
- Log automatique sur `created`, `updated`, `deleted`
- Exclusion des champs sensibles (password, remember_token, two_factor_secret)

**Page d'audit :** `GET /administration/audit` — filtres : utilisateur, action, modèle, période, IP — export Excel

#### 9.5.4 Gestion des Sessions Avancée

**Contrôleur :** `SessionController`
- `activeSessions()` — liste des sessions actives avec IP, navigateur, date
- `revokeSession(id)` — déconnexion à distance d'une session spécifique

**Page "Sécurité de mon compte" :**
- Liste des sessions actives avec IP + navigateur + date
- Bouton "Révoquer toutes les autres sessions"
- Historique des 30 dernières connexions

#### 9.5.5 Politique de Mots de Passe & IP Whitelisting

**Règle :** `App\Rules\StrongPassword` — min 10 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial

**Middleware :** `IpWhitelist` — si le client a défini des IP autorisées, bloquer les autres adresses

**Migrations sur `clients` :**
```
require_2fa (BOOLEAN), session_timeout_minutes (INT, DEFAULT 120), allowed_ips (JSON)
```

### 9.6 Journal d'Audit

Traçabilité complète via `audit_logs` (voir 9.5.3) pour la conformité RGPD et les obligations comptables. Toutes les actions sur les données sensibles sont horodatées avec IP et identité de l'utilisateur.

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
│   ├── Middleware/            # 16 middlewares personnalisés
│   │   ├── AdminMiddleware.php
│   │   ├── CheckActionPermission.php
│   │   ├── CheckCompanyAccess.php
│   │   ├── CheckComptable.php
│   │   ├── CheckModuleAccess.php
│   │   ├── CheckNotSuspended.php
│   │   ├── CheckRole.php
│   │   ├── CheckSuperAdmin.php
│   │   ├── DaeSecretaireAccess.php
│   │   ├── EnsureCompanyAccess.php
│   │   ├── EnsureEmailVerified.php
│   │   ├── EnsureIsCompanyAdmin.php
│   │   ├── EnsureNotClient.php
│   │   ├── IpWhitelist.php
│   │   ├── LogRedirects.php
│   │   ├── RedirectIfClient.php
│   │   └── SetTenantContext.php
│   └── Requests/              # Form requests (Auth, etc.)
├── Models/                    # 80+ modèles Eloquent
│   ├── User.php, Client.php, Contact.php, Role.php, Permission.php
│   ├── Pole.php, Mission.php, MissionStep.php
│   ├── Account.php, JournalEntry.php, JournalLine.php, Balance.php, FiscalYear.php
│   ├── Product.php, StockMovement.php, ErpInvoice.php, Subscription.php
│   ├── Employee.php, Contract.php, Payslip.php, LeaveRequest.php
│   ├── Project.php, ProjectTask.php, ProjectMilestone.php
│   ├── LegalCase.php, Article.php
│   ├── Caisse.php, CaisseTransaction.php
│   ├── ClientFolder.php, Document.php, DocumentVersion.php
│   ├── DossierDae.php, CourrierDae.php, TacheDae.php
│   ├── ItTicket.php, ItAsset.php, ItMaintenanceContract.php, ItKnowledgeBase.php
│   ├── Tontine.php, TontineMembre.php, TontineCotisation.php
│   ├── RelanceRule.php
│   ├── ApprovalWorkflow.php, ApprovalRequest.php
│   ├── DocumentSignature.php, AuditTrail.php
│   ├── TaxDeclaration.php, CostCenter.php, AnalyticLine.php
│   └── Services/ (EmecefService, IrppCalculator, CnssCalculator, ...)
└── Providers/                 # Service providers
```

### 10.2 Frontend (Vue 3 SPA + Blade)

```
resources/
├── views/
│   ├── landing.blade.php          # Page d'accueil statique
│   ├── app.blade.php              # Shell GEL + CPA (CSS/JS compilés)
│   ├── company.blade.php          # Shell entreprise (@vite())
│   └── auth/                      # Login, Register (Blade)
├── js/
│   ├── app.js                     # Point d'entrée Vite + registrations components
│   ├── bootstrap.js               # Axios config + CSRF + base URL + fetch wrapper
│   ├── Root.vue                   # Routeur dynamique (data-page → composant)
│   ├── Layouts/
│   │   ├── GelLayout.vue          # Layout super admin (Dark Premium: sidebar #0B1120)
│   │   ├── CpaLayout.vue          # Layout CPA
│   │   └── CompanyLayout.vue      # Layout entreprise (sidebar verticale + subnav)
│   ├── Pages/
│   │   ├── Auth/
│   │   │   ├── CpaLogin.vue       # Login CPA
│   │   │   └── CpaRegister.vue    # Register CPA
│   │   ├── Cpa/
│   │   │   └── Dashboard.vue      # Dashboard CPA (4 vues: admin/client/entreprise/comptable)
│   │   ├── Company/               # Pages portail entreprise
│   │   │   ├── Dashboard.vue      # Dashboard avec stats + onglets
│   │   │   ├── Accounting.vue, AiAssistant.vue, Caisse.vue
│   │   │   ├── Crm.vue, Ged.vue, HumanResources.vue
│   │   │   ├── Invoices.vue, Legal.vue, Notifications.vue
│   │   │   └── Profile.vue, Projects.vue, Services.vue, Users.vue
│   │   ├── Gel/                   # Pages portail GEL (Super Admin)
│   │   │   ├── Dashboard.vue      # Stats globales, graphiques
│   │   │   ├── Profile.vue        # Profil utilisateur cabinet
│   │   │   ├── Clients/           # CRM complet
│   │   │   │   ├── Index.vue, Create.vue, Show.vue, Edit.vue
│   │   │   │   ├── Modules.vue, Services.vue, Contacts.vue
│   │   │   │   └── Dossiers/      # GED par client
│   │   │   ├── Accounting/        # Comptabilité
│   │   │   │   ├── Accounts.vue, JournalList.vue, JournalForm.vue
│   │   │   │   ├── Balance.vue, GeneralLedger.vue
│   │   │   │   ├── Bilan.vue, Resultat.vue, ExerciseClose.vue
│   │   │   │   └── TeleDeclaration.vue, Configuration.vue
│   │   │   ├── Erp/               # ERP
│   │   │   │   ├── Invoices.vue, InvoiceForm.vue
│   │   │   │   ├── Treasury.vue, TreasuryForecast.vue
│   │   │   │   └── PreBill.vue
│   │   │   ├── Rh/                # RH & Paie
│   │   │   ├── Legal/             # Juridique
│   │   │   ├── Projects/          # Projets
│   │   │   ├── Caisse/            # Caisse
│   │   │   ├── Dae/               # Secrétariat DAE
│   │   │   ├── It/                # IT Support
│   │   │   ├── Tontines/          # Tontine
│   │   │   ├── Signatures/        # Signature électronique
│   │   │   ├── Relances/          # Relances automatiques
│   │   │   ├── CostCenters/       # Centres de coûts
│   │   │   ├── Ocr/               # OCR factures
│   │   │   ├── Paie/              # Calcul paie
│   │   │   └── Admin/             # Administration
│   │   │       ├── Index.vue      # Demande d'activation
│   │   │       ├── Requests/      # Gestion requêtes
│   │   │       ├── Audit.vue      # Journal d'audit
│   │   │       └── Articles.vue   # Articles blog
│   │   ├── Admin/
│   │   │   └── Catalogue/         # Admin catalogue ERP
│   │   │       ├── Services.vue, Orders.vue
│   │   │       └── OrderShow.vue
│   │   ├── Commerce/              # Dashboard commerce/POS
│   │   │   └── Dashboard.vue
│   │   ├── Public/
│   │   │   └── Catalogue/         # Catalogue e-commerce public
│   │   │       ├── Index.vue, Show.vue, OrderWizard.vue
│   │   │       └── OrderConfirmation.vue
│   │   └── Cpa/                   # Pages CPA
│   ├── Components/                # Composants réutilisables
│   ├── stores/
│   │   ├── auth.js                # Store d'authentification (reactive + polling)
│   │   └── cart.js                # Store panier (fetchCart, addToCart, ...)
│   └── css/
│       ├── app.css                # Global (variables CSS, Inter, Outfit)
│       └── company.css            # Classes partagées isup-* (portail entreprise)
```

### 10.3 Routes (`routes/web.php` + `routes/auth.php` — ~1300+ lignes)

```
PUBLIC (sans authentification) :
/                          → Landing page (statique)
/nos-modules               → Modules présentations
/nos-services              → Catalogue e-commerce (public)
/services/{slug}           → Pages services
/blog*, /blogue*           → Blog/actualités
/contact                   → Page contact
/em-cef                    → Page e-MECeF

AUTH (connexion) — routes/auth.php :
/login                     → Login (Blade)
/register                  → Register (Blade)
/forgot-password           → Mot de passe oublié
/cpa-login                 → Login CPA (Vue)
/cpa-register              → Register CPA (Vue)

GEL CABINET — auth + verified + not_suspended + company + not_client :
/dashboard                 → Dashboard GEL
/clients/*                 → CRM clients (module:crm)
/documents/*               → GED (module:document)
/dossiers/*                → Dossiers
/poles/*                   → Pôles
/missions/*                → Missions
/comptabilite/*            → Comptabilité (plan, journaux, balance, bilan, résultat)
/erp/*                     → ERP (factures, trésorerie, prévisionnel)
/rh/*, /paie/*             → RH & Paie
/facturation/*             → Facturation
/caisse/*                  → Caisse
/projets/*                 → Projets
/juridique/*               → Juridique
/ia/*                      → IA Assistant
/administration/*          → Administration (audit, articles)
/commerce/*                → Dashboard commerce
/tele-declarations         → Télédéclaration fiscale
/signatures                → Signature électronique
/approval-workflows        → Workflows d'approbation
/relance-rules             → Règles de relances
/cost-centers              → Centres de coûts
/ocr                       → OCR factures

ENTREPRISE — auth + ensure.company + company.auth :
/company/dashboard          → Dashboard entreprise
/company/users              → Utilisateurs
/company/services           → Services souscrits
/company/accounting         → Comptabilité
/company/invoices           → Factures
/company/caisse             → Caisse
/company/crm                → CRM
/company/ged                → GED
/company/rh/*               → RH (module:rh)
/company/dae/*              → Secrétariat DAE (module:dae)
/company/juridique          → Juridique
/company/projets            → Projets
/company/profile            → Profil entreprise
/company/notifications      → Notifications

API ENDPOINTS :
/api/me/profile                     → Profil complet + permissions
/api/me/permissions                 → Permissions formatées
/api/me/switch-context              → Changer contexte entreprise
/api/me/field-restrictions/{module} → Champs cachés par module
/api/company/events/check           → Polling mises à jour
/api/commerce/dashboard             → Stats commerce
/api/cpa/stats                      → Stats CPA
/api/clients                        → CRUD clients (gel)
/api/clients/{id}/services          → Services client
/api/cart                           → Panier e-commerce
/api/cart/add|update|remove|clear   → Gestion panier
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

### ✅ Version 1.0 (livrée)

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

### ✅ Version 1.1 (achevée)

- [x] **Sidebar verticale** — Remplacement des onglets horizontaux dans GelLayout (12+ items sans débordement)
- [x] **Navigation contextuelle** — Liens rapides par section en bas de sidebar
- [x] **Design system unifié** — Classes isup-* dans company.css (header, panels, tables, statuts, boutons, formulaires, modales)
- [x] **Portail Entreprise amélioré** — Dashboard avec stats, onglets, barre de recherche, sous-navigation
- [x] **Affichage entreprise utilisateur** — Badge avec nom de l'entreprise + avatar au lieu de "Client"
- [x] **Permissions polling** — Vérification automatique des modules toutes les 15s
- [x] **Catalogue public redessiné** — Hero animé, cartes avec barre de couleur, grille responsive
- [x] **Middleware module** — Filtrage des routes RH, DAE, GED avec middleware `module:*`
- [x] **Stores Vue** — authStore (profil, permissions, polling) + cartStore (panier e-commerce)
- [x] **Base URL dynamique** — Meta tag `base-url` + wrapper fetch pour compatibilité XAMPP/artisan
- [x] **CSRF automatique** — Token injecté globalement dans axios depuis `meta[name=csrf-token]`

### ✅ Version 2.0 — Dark Premium (achevée)

- [x] **Design Dark Premium** — Refonte complète de GelLayout.vue
  - [x] Sidebar ultra-sombre `#0B1120` → `#0F1A2E` avec dégradé vertical
  - [x] Topbar vitrée `rgba(255,255,255,0.9)` + `backdrop-filter: blur(12px)`
  - [x] Contenu fond `#F5F7FA`, cartes `border-radius: 12px`, ombres douces
  - [x] Icônes sidebar 18px avec opacité progressive, avatar user en bas
  - [x] Transitions 0.2s, scrollbar stylisée, responsive < 992px overlay

### 🔴 P0 — Priorité absolue (conquête marché)

- [ ] **Intégration e-MECeF / Sygmef** — Obligatoire légalement, bloquant pour les clients
  - Package `codianselme/lara-sygmef`, Service `EmecefService`, QR code sur PDF
- [ ] **Calcul automatique paie IRPP/CNSS** — Barèmes béninois, différenciateur fort
  - `IrppCalculator` (7 tranches progressives), `CnssCalculator` (employeur 15,4%, salarié 3,36%)

### 🟠 P1 — Priorité haute

- [ ] **Télédéclaration TVA/CNSS/IS** — Gain de temps comptable, interface e-services DGI
  - Table `tax_declarations`, calcul auto TVA collectée/déductible, export Excel format DGI
- [ ] **Journal d'audit complet** — Confiance + conformité RGPD
  - Table `audit_logs`, trait `Auditable` sur modèles sensibles
- [ ] **2FA + Sécurité** — Prérequis grandes entreprises
  - Google2FA, codes de secours, politique par client (require_2fa)
- [ ] **Gestion des sessions avancée** — Vue + révocation à distance

### 🟡 P2 — Priorité moyenne

- [ ] **Helpdesk IT** — Nouveau pôle de revenus
  - Tickets, SLA, commentaires, auto-numérotation TKT-2026-XXXXX
- [ ] **Inventaire parc informatique (ITAM)** — Complément helpdesk
  - Équipements, licences, interventions, alertes garanties/licences
- [ ] **SMS & WhatsApp Business** — Canaux mobile (forte adoption Bénin)
  - `SmsService` (Africa's Talking), `WhatsAppService` (Meta WABA v18.0)
- [ ] **Relances automatiques clients** — Règles J+X, canaux multiples
- [ ] **Export PDF avec QR e-MECeF** — Exigé par la DGI
- [ ] **Signature électronique** — Canvas + hash SHA256 + horodatage
- [ ] **Permissions granulaires** — Restrictions de champ, matrice, délégation temporaire
- [ ] **Trésorerie prévisionnelle** — Projection 3-6 mois
- [ ] **PWA (Application Web Progressive)** — Mode dégradé hors ligne

### 🟢 P3 — Priorité future

- [ ] **OCR import factures fournisseurs** — API Claude + spatie/pdf-to-text
- [ ] **Rapprochement bancaire automatique** — CSV/OFX/MT940, fuzzy matching
- [ ] **États financiers SYSCOHADA complets** — Bilan, CRP, TAFIRE, Notes
- [ ] **Comptabilité analytique** — Centres de coûts, répartition
- [ ] **Paiements mobiles intégrés** — MTN MoMo + Moov Money
- [ ] **Module Tontine / Microfinance** — Spécifique Bénin
- [ ] **Workflows d'approbation** — Chaînes configurables
- [ ] **Tableau de bord exécutif** — KPI direction sans jargon comptable
- [ ] **Contrats de maintenance IT** — Gestion des abonnements techniques

### 🔮 Vision v3.0

- [ ] Application mobile native (Flutter / React Native)
- [ ] API REST publique
- [ ] Marché d'applications (extensions)
- [ ] Webhooks et intégrations tierces
- [ ] Multilingue (français → anglais)
- [ ] Dashboard analytics avancé (charts temps réel)
- [ ] IA avancée : analyse prédictive, scoring, scraping automatique

---

## 13. Extension Stratégique v2.0 — Récapitulatif

### 13.1 Nouvelles tables (23 nouvelles)

```
audit_logs                  → Traçabilité complète de toutes les actions sensibles
tax_declarations            → Télédéclarations fiscales calculées (TVA, IRPP, CNSS, IS, AIB, TSS, ASDI)
cost_centers                → Centres de coûts (comptabilité analytique)
analytic_lines              → Lignes analytiques liées aux écritures
it_tickets                  → Tickets helpdesk IT
it_ticket_comments          → Commentaires des tickets
it_sla_policies             → Politiques SLA
it_assets                   → Inventaire parc informatique
it_asset_licenses           → Licences logicielles
it_asset_interventions      → Historique interventions par équipement
it_maintenance_contracts    → Contrats de maintenance IT
it_knowledge_base           → Base de connaissances IT
relance_rules               → Règles de relances automatiques clients
approval_workflows          → Workflows d'approbation configurables
approval_requests           → Demandes d'approbation en cours
approval_steps_log          → Journal des étapes d'approbation
permission_field_restrictions → Restrictions de champs par permission
permission_delegations      → Délégations temporaires de permissions
document_signatures         → Signatures électroniques avec hash SHA256
tontines                    → Groupes tontine
tontine_membres             → Membres des tontines
tontine_cotisations         → Cotisations tontine
+ Colonnes additionnelles sur users, clients, invoices (e-MECeF, 2FA, sécurité IP)
```

### 13.2 Nouveaux packages

| Package | Usage | Partie |
|---------|-------|--------|
| `codianselme/lara-sygmef` | API e-MECeF DGI Bénin | A1 |
| `chillerlan/php-qrcode` | QR code e-MECeF sur PDF factures | A1 |
| `pragmarx/google2fa-laravel` | Authentification 2FA | C1 |
| `bacon/bacon-qr-code` | QR code pour Google Authenticator | C1 |
| `spatie/pdf-to-text` | Extraction texte depuis PDF (OCR) | D1 |
| `setasign/fpdf` | Génération PDF signatures | E6 |
| `vite-plugin-pwa` | PWA (mode dégradé hors ligne) | E3 |

### 13.3 Nouveaux services

```
App\Services\
├── EmecefService              → Émission facture normalisée e-MECeF
├── Paie\
│   ├── IrppCalculator         → Calcul IRPP barèmes Bénin (7 tranches progressives)
│   └── CnssCalculator         → Calcul CNSS (employeur 15,4%, salarié 3,36%)
├── Comptabilite\
│   └── EtatsFinanciersService → Bilan/CRP/TAFIRE conformes SYSCOHADA
├── OcrInvoiceService          → OCR factures fournisseurs (API Claude)
├── BankReconciliationService  → Rapprochement bancaire automatique
├── SmsService                 → SMS Bénin (Africa's Talking / passerelle locale)
├── WhatsAppService            → WhatsApp Business API (Meta WABA v18.0)
├── MobileMoneyService         → MTN MoMo + Moov Money
├── ItRapportService           → Rapports d'intervention IT (PDF signable)
└── IfuVerificationService     → Validation IFU Bénin (clé modulo 97)
```

### 13.4 Nouvelles commandes artisan

| Commande | Fréquence | Description |
|----------|-----------|-------------|
| `CheckItAssetAlerts` | Hebdomadaire (lundi 08:00) | Alertes garanties et licences expirant |
| `ProcessRelances` | Quotidienne (09:00) | Relances automatiques clients impayés |

### 13.5 Nouveaux middlewares

| Middleware | Description |
|------------|-------------|
| `IpWhitelist` | Blocage des IP non autorisées par client |

### 13.6 Schéma de priorisation marché

```
P0 (obligatoire légal, bloquant)
├── e-MECeF / Sygmef
└── Calcul paie IRPP/CNSS

P1 (confiance, grandes entreprises)
├── Télédéclaration
├── Journal d'audit
├── 2FA
└── Sessions avancées

P2 (nouveaux revenus, rétention)
├── Helpdesk IT + ITAM
├── SMS / WhatsApp
├── Relances auto
├── Signature électronique
├── Permissions granulaires
├── Trésorerie prévisionnelle
└── PWA

P3 (différenciation, futur)
├── OCR factures
├── Rapprochement bancaire
├── SYSCOHADA complet
├── Analytique
├── Paiements mobiles
├── Tontine
├── Workflows
├── Dashboard exécutif
└── Contrats maintenance IT
```

---

### A. Licence

Projet privé — Tous droits réservés GEL Cabinet.

### B. Contact technique

- **Développeur :** Ghislain Jules EDA
- **Stack :** Laravel 12 + Vue 3 (SPA, Composition API) + Blade + Vite + Bootstrap 5 + MySQL
- **Environnement :** XAMPP (Windows)

### C. Dépendances principales

#### PHP (Composer)

```
laravel/framework
laravel/breeze (dev — auth scaffolding)
codianselme/lara-sygmef          → API e-MECeF DGI Bénin
chillerlan/php-qrcode            → QR code e-MECeF sur PDF
pragmarx/google2fa-laravel       → Authentification 2FA
bacon/bacon-qr-code              → QR code Google Authenticator
spatie/pdf-to-text               → Extraction texte pour OCR
setasign/fpdf                    → Génération PDF signatures
```

#### JavaScript (NPM)

```
vue (3.x)
vite
@vitejs/plugin-vue
bootstrap (5.3)
bootstrap-icons
vite-plugin-pwa                   → PWA (mode hors ligne)
```

### D. Icônes utilisées

- **Bootstrap Icons** — Interface principale (nav, sidebar, actions, statuts)
- **Lucide** — Icônes supplémentaires pour graphiques et actions spécifiques

---

> **Document généré le 19 juin 2026** — Ce cahier des charges reflète l'état du projet à date et évoluera avec les versions futures.
