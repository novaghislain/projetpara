# Cahier des Charges — GEL Cabinet

> **Projet :** Plateforme multi-portail de gestion de cabinet
> **Version :** 2.1.1
> **Date :** 21 juin 2026
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
   - [4.22 Gestion Commerciale et POS](#422-gestion-commerciale-et-point-de-vente-pos)
   - [4.23 GEL Intelligence — Système Multi-Agents IA](#423-gel-intelligence--système-multi-agents-ia)
   - [4.24 Fil d'Activité IA — Activity Feed](#424-fil-dactivité-ia--activity-feed)
   - [4.25 Omnisearch — Recherche Globale](#425-omnisearch--recherche-globale)
   - [4.26 Workpapers — Dossiers de Travail](#426-workpapers--dossiers-de-travail)
   - [4.27 Magic Links — Demandes de documents sans login](#427-magic-links--demandes-de-documents-sans-login)
   - [4.28 Transactions Récurrentes](#428-transactions-récurrentes)
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
  - Juridique (société, assemblées, contrats, contentieux, conformité, bibliothèque d'actes, dossiers, registres, veille)
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

#### Outils comptables en masse

| Outil | Description |
|-------|-------------|
| **Reclassification en masse** | Sélection multiple de transactions → changement de compte comptable en un clic. Filtre par période, compte source, montant. Prévisualisation avant validation. Log d'audit automatique. |
| **Annulation de réconciliation** | Défaire un rapprochement bancaire erroné. Remet les transactions à l'état "non rapproché". Accessible uniquement aux super_admin et comptables. |
| **Verrouillage de période** | Après clôture, verrouillage de la période comptable. Aucune écriture ne peut être ajoutée/modifiée dans une période verrouillée. Seul le super_admin peut déverrouiller (motif obligatoire + audit log). Champ sur `fiscal_years` : `locked_at TIMESTAMP NULL`, `locked_by BIGINT NULL`. |

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

Module complet de gestion juridique avec 8 sous-modules opérationnels, un tableau de bord central et un système de filtrage multi-tenant via `LegalBaseModel::scopeByClient()`.

| Sous-module | URL | Description |
|-------------|-----|-------------|
| **Dashboard** | `/juridique` | Tableau de bord avec KPI (contrats actifs, contentieux, conformité, AG, dossiers) |
| **Fiche Société** | `/juridique/societe` | Informations légales de l'entreprise (RCCM, IFU, capital, siège social, etc.) |
| **Assemblées** | `/juridique/assemblees` | Planification et suivi des AG (ordinaire, extraordinaire, conseil d'administration) |
| **Contrats** | `/juridique/contrats` | Gestion complète des contrats avec signature, expiration, parties, renouvellement |
| **Contentieux** | `/juridique/contentieux` | Suivi des litiges avec référence, tribunal, montant, prochaine audience, statut |
| **Conformité** | `/juridique/conformite` | Obligations réglementaires avec échéances, organismes, statuts (conforme/en retard/à venir) |
| **Bibliothèque d'Actes** | `/juridique/bibliotheque` | Modèles d'actes juridiques avec variables dynamiques, génération et versioning |
| **Registres** | `/juridique/registres` | Registres obligatoires (registre des délibérations, registre de présence, etc.) |
| **Dossiers** | `/juridique/dossiers` | Dossiers juridiques transverses par client |

**Fonctionnalités clés :**
- **Génération d'actes** : Modèles avec variables (`{nom_client}`, `{date}`, `{capital}`) → remplacement automatique → HTML prêt à imprimer
- **Validateur de conformité** : Calcul automatique des échéances, statuts et alertes par obligation
- **Contentieux filtré** : Filtres par statut (en_cours/clos) avec compteurs, montant formaté en FCFA
- **Filtrage multi-tenant** : Les super admins voient toutes les données ; les comptables voient uniquement les données de leurs clients
- **Scoped queries** : `byClient()`, `enCours()`, `planifiees()`, `expireBientot()`, `echeantes()`

**Modèles (11 tables) :**
- `LegalBaseModel` (abstract) — Base avec `scopeByClient()` pour le filtrage multi-tenant
- `LegalCompanyInfo` — Informations légales de l'entreprise cliente
- `LegalAssembly` — Assemblées générales (type, date, lieu, statut, résolutions)
- `LegalContract` — Contrats (titre, parties, date_début, date_fin, statut, montant)
- `LegalContractSignature` — Signatures électroniques liées aux contrats
- `LegalLitigation` — Contentieux (référence, titre, type, nature, tribunal, montant_litige, prochaine_audience)
- `LegalCompliance` — Obligations de conformité (intitulé, organisme, type, date_échéance, statut)
- `LegalActsLibrary` — Bibliothèque d'actes (titre, catégorie, contenu avec variables, version)
- `LegalRegistre` — Registres obligatoires (type, contenu, période)
- `LegalDossier` — Dossiers juridiques (titre, description, client_id, statut)
- `LegalVeille` — Veille juridique (actualités, textes de loi, jurisprudence)
- `LegalAuditLog` — Journal d'audit des actions juridiques

**Contrôleurs (9) :**
- `LegalDashboardController` — Stats pour le dashboard juridique
- `LegalCompanyInfoController` — Gestion fiche société
- `LegalAssembliesController` — CRUD assemblées
- `LegalContratsController` — Gestion contrats
- `LegalLitigationsController` — Gestion contentieux
- `LegalComplianceController` — Gestion conformité
- `LegalActsLibraryController` — Bibliothèque d'actes + génération via `ActeGeneratorService`
- `LegalDossiersController` — Gestion dossiers
- `LegalRegistresController` — Gestion registres

**Pages Vue (21 pages) :**
- `Dashboard.vue` — KPI, listes récentes, accès rapides
- `Societe/Index.vue` — Fiche d'identification
- `Assemblees/Index.vue`, `Show.vue`, `Form.vue` — CRUD assemblées
- `Contrats/Index.vue`, `Show.vue`, `Form.vue` — CRUD contrats
- `Contentieux/Index.vue`, `Show.vue`, `Form.vue` — CRUD contentieux
- `Conformite/Index.vue`, `Form.vue`, `Calendrier.vue` — Conformité + calendrier des échéances
- `Bibliotheque/Index.vue`, `Form.vue`, `Generer.vue` — Actes + générateur
- `Dossiers/Index.vue`, `Show.vue`, `Form.vue` — Dossiers
- `Registres/Show.vue` — Registres

**Architecture technique :**
- `LegalBaseModel` gère le scope multi-tenant : les super admins voient toutes les données, les comptables voient uniquement `client_id = X`
- `ActeGeneratorService` : remplace les variables `{...}` dans les modèles d'actes par les valeurs dynamiques
- Contrôleurs avec double rendu : `expectsJson()` → JSON API / sinon → `view('app', ['page' => ...])`
- Seeders : `LegalDemoSeeder` peuple 50+ enregistrements démo pour tous les sous-modules

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

### 4.7.1 Flux détaillé — Création de facture → Normalisation e-MECeF → Portail client

Le système de facturation repose sur **deux modèles distincts** qui communiquent via le processus e-MECeF :

| Modèle | Portail | Usage | e-MECeF |
|--------|---------|-------|----------|
| `ErpInvoice` | GEL Cabinet (backoffice) | Facture créée par le comptable pour le client | Oui — émission DGI |
| `CompanyInvoice` | Portail Entreprise | Facture créée par l'entreprise elle-même | Non (hors scope) |

**Flux complet :**

```
┌─────────────────────────────────────────────────────────────────────────┐
│  PORTAL GEL CABINET (Comptable)                                         │
│                                                                         │
│  1. Création facture                                                    │
│     POST /erp/invoices                                                  │
│     → ErpInvoiceController::store()                                     │
│     → Création ErpInvoice + ErpInvoiceItems                             │
│     → Status: "brouillon"                                               │
│                                                                         │
│  2. Clic "DGI" (bouton shield dans la liste)                           │
│     POST /emecef/emit/{invoice}                                         │
│     → EmecefController::emitInvoice(ErpInvoice $invoice)                │
│                                                                         │
│  3. EmecefService::emettreFactureNormalisee()                           │
│     ├── Vérifie : client->emecef_is_active && client->emecef_nim       │
│     ├── Construit le payload DGI (IFU, type, montants, items)          │
│     ├── Appelle l'API Sygmef (ou simule en test mode)                  │
│     ├── Stocke la réponse : emecef_nim, emecef_compteur,               │
│     │   emecef_hash, emecef_qr, emecef_statut='emise'                  │
│     └── Retourne le résultat au contrôleur                             │
│                                                                         │
│  4. EmecefController::createEmissionNotifications()                     │
│     ├── Notification → Admin entreprise (type: emecef_emise)           │
│     │   title: "Facture émise à la DGI"                                │
│     │   message: "La facture {num} a été transmise à la DGI."          │
│     │   data: { invoice_id, url: '/company/invoices' }                 │
│     └── Notification → Comptable émetteur (copie)                      │
│         title: "Facture transmise — copie disponible"                   │
│         message: "La facture {num} a été transmise à la DGI..."        │
│         data: { invoice_id, url: '/gel/erp/invoices' }                 │
│                                                                         │
│  5. La facture est maintenant visible sur le portail entreprise         │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│  PORTAL ENTREPRISE (Company Admin)                                      │
│                                                                         │
│  6. API /api/company/invoices (GET)                                     │
│     → CompanyInvoiceController::listAll()                               │
│     ├── Récupère CompanyInvoice où client_id = X                        │
│     └── Récupère ErpInvoice où client_id = X ET emecef_statut = 'emise'│
│         └── Fusionne + tri par date (les plus récents d'abord)         │
│                                                                         │
│  7. Affichage dans Company/Invoices.vue                                 │
│     ├── Badge "ERP" pour les factures du comptable                      │
│     ├── Badge vert "DGI ✓" pour les factures certifiées                │
│     ├── Ligne de fond alternée (isup-row-erp)                           │
│     └── Détail avec QR code e-MECeF (NIM, compteur, date, statut)      │
│                                                                         │
│  8. Notification reçue → clic → redirection vers /company/invoices      │
└─────────────────────────────────────────────────────────────────────────┘
```

**Contrôleurs impliqués :**
- `app/Http/Controllers/Gel/Erp/InvoiceController.php` — Création facture backoffice
- `app/Http/Controllers/Gel/EmecefController.php` — Émission, annulation, vérification e-MECeF
- `app/Http/Controllers/Company/InvoiceController.php` — Liste, CRUD, stats portail entreprise

**Service :** `app/Services/EmecefService.php`
- `emettreFactureNormalisee(ErpInvoice $invoice): array` — Émission DGI
- `annulerFacture(ErpInvoice $invoice): array` — Annulation DGI
- `verifierFacture(string $nim, string $compteur): array` — Vérification statut
- `certifyInvoice(ErpInvoice $invoice): ErpInvoice` — Certification PDF (statique, sans API)

**Modèle ErpInvoice — Champs e-MECeF :**
```
emecef_nim, emecef_compteur (int), emecef_hash, emecef_qr (TEXT),
emecef_statut (enum: non_emise/emise/annulee), emecef_datetime (datetime)
```

**QR Code :** Format URL de vérification DGI :
```
https://portail.impots.bj/portail/facture/?nim={nim}&compteur={compteur}&dateHeure={date}&signature={signature}
```

**Notifications créées (table `notifications`) :**
| Type | Destinataire | Contenu |
|------|-------------|---------|
| `emecef_emise` | Admin entreprise | Facture émise à la DGI + lien vers /company/invoices |
| `emecef_emise` | Comptable émetteur | Copie disponible + lien vers /gel/erp/invoices |

---

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

**Service :** `App\Services\EmecefService`
- `emettreFactureNormalisee(ErpInvoice $invoice): array` — Émet la facture à la DGI
  - Vérifie la configuration du client : `$client->emecef_is_active`, `$client->emecef_nim`, `$client->emecef_password`
  - Construit le payload DGI (IFU emetteur/récepteur, type FV/FA/AV/EA, AIB, client, items, paiement)
  - En test mode : simulation sans appel API réel
  - Appelle l'API Sygmef
  - Enregistre NIU, compteur, hash, QR code sur la facture
  - Calcule l'AIB (Acompte sur Impôt sur les Bénéfices) : 1% DGE/DME, 5% CSI
- `annulerFacture(ErpInvoice $invoice): array` — Annule une facture émise
- `verifierFacture(string $nim, string $compteur): array` — Vérifie le statut auprès de la DGI
- `certifyInvoice(ErpInvoice $invoice): ErpInvoice` — Certifie pour PDF (statique, sans API)

**Per-client NIM (pas de NIM global) :**
Le NIM et le mot de passe e-MECeF sont stockés **sur le modèle Client**, pas dans `.env` :
```
clients.emecef_nim (string, nullable)       — NIM fourni par la DGI pour ce client
clients.emecef_is_active (boolean, false)   — Active/désactive e-MECeF pour ce client
clients.emecef_password (encrypted, nullable) — Mot de passe e-MECeF du client
```

**Configuration minimale du client pour e-MECeF :**
| Champ | Description | Obligatoire |
|-------|-------------|-------------|
| `ifu` | IFU de l'entreprise (9 chiffres) | Oui |
| `emecef_nim` | NIM attribué par la DGI | Oui |
| `emecef_password` | Mot de passe e-MECeF | Oui |
| `emecef_is_active` | Active l'envoi DGI | Oui (true) |
| `regime_fiscal` | Régime fiscal (réel_simplifié/réel_normal) | Recommandé |

**Notifications à l'émission :**
Après une émission réussie, `EmecefController::createEmissionNotifications()` crée deux notifications :
1. **Admin entreprise** — "La facture X a été transmise à la DGI avec succès." (url: /company/invoices)
2. **Comptable émetteur** — "La facture X a été transmise à la DGI. Copie disponible dans votre tableau de bord."

**Champs e-MECeF sur ErpInvoice :**
```
emecef_nim, emecef_compteur (int), emecef_hash, emecef_qr (TEXT),
emecef_statut (enum: non_emise/emise/annulee), emecef_datetime (datetime)
```

**QR Code :**
```
https://portail.impots.bj/portail/facture/?nim={nim}&compteur={compteur}&dateHeure={date}&signature={signature}
```
Généré via `chillerlan/php-qrcode`. Affiché dans le détail de la facture côté entreprise (modale avec QR + infos).

**Portail entreprise — Détail e-MECeF dans la modale :**
```
┌──────────────────────────────────────┐
│  Certification DGI (e-MECeF)         │
│  ┌──────────┐  NIM:    XX-XXXXXXX   │
│  │ QR Code  │  Compteur: 12345       │
│  │          │  Émis le: 21/06/2026   │
│  │          │  Statut: Certifiée ✓   │
│  └──────────┘  QR URL: https://...   │
└──────────────────────────────────────┘
```

**Configuration .env :**
```env
EMECEF_API_TOKEN=token_dgi
EMECEF_NIM=XX-XXXXXXX           # NIM global par défaut (déprécié — utiliser per-client)
EMECEF_TEST_MODE=true            # true = simulation sans API réelle
EMECEF_API_URL=https://sygmef.impots.bj/emcf/api
```

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

**Stack :** `spatie/pdf-to-text` + API IA (Gemini) pour OCR sur images

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

### 4.23 GEL Intelligence — Système Multi-Agents IA

Système multi-agents IA dédié à l'automatisation des tâches comptables, fiscales et de gestion. Chaque agent est spécialisé dans un domaine métier. Toutes les suggestions et actions sont centralisées dans le **Fil d'Activité IA** (Activity Feed) où le comptable est décisionnaire final : il examine, approuve ou rejette chaque suggestion.

**Architecture globale :**
- 6 agents spécialisés communiquant via une base de suggestions centralisée (`ai_suggestions`)
- Apprentissage continu via `ai_learning_log` (les corrections manuelles améliorent les futures suggestions)
- Score de confiance (0–100%) sur chaque suggestion
- Actions exécutables en un clic depuis le fil d'activité
- Notifications push pour les suggestions critiques

#### 4.23.1 Agent OHADA — Comptabilité Intelligente

| Fonctionnalité | Description |
|----------------|-------------|
| Catégorisation automatique | Classification des transactions selon le plan comptable SYSCOHADA basée sur le libellé et l'historique (ML) |
| Détection d'anomalies | Flag des transactions inhabituelles (>200% moyenne, catégorie incohérente, doublons, écarts de réconciliation) |
| Suggestions de régularisation | Propositions d'écritures de fin de mois basées sur les patterns historiques |
| Cohérence SYSCOHADA | Vérification des comptes autorisés par journal et par classe |
| Balance | Alerte automatique en cas de déséquilibre |
| Apprentissage continu | Les corrections du comptable améliorent les suggestions futures (feedback loop) |

#### 4.23.2 Agent Fiscal Bénin

| Fonctionnalité | Description |
|----------------|-------------|
| Calcul TVA automatique | TVA collectée vs TVA déductible depuis les journaux comptables |
| Pré-remplissage déclarations | Génération automatique des déclarations TVA, IRPP, CNSS, IS, AIB |
| Alertes d'échéances | Notifications J-15, J-7, J-3, J-1 avant chaque deadline fiscale |
| Détection risques fiscaux | TVA non déclarée, dépassement de seuils, incohérences |
| Simulation e-MECeF | Prévisualisation de la facture normalisée avant émission DGI (dry-run) |
| Calendrier fiscal interactif | Toutes les échéances par client avec compteurs et alertes |

#### 4.23.3 Agent Rapprochement Bancaire

| Fonctionnalité | Description |
|----------------|-------------|
| Import multi-format | CSV (BOA, Ecobank, UBA, BCEAO), OFX, MT940, PDF (OCR) |
| Matching intelligent | Correspondance par montant (±1 FCFA), date (±3 jours), libellé (fuzzy Levenshtein) |
| Score de confiance | Chaque correspondance suggérée a un score de 0 à 100% |
| Matching complexe | One-to-many et many-to-one |
| Résolution guidée | Suggestions d'action pour les non-matchés (créer écriture, demander justificatif) |
| Ready-to-Post | Transactions pré-matchées haute confiance → validation en masse en 1 clic |
| Reconcile Companion | Upload PDF → extraction IA → comparaison auto → écarts en évidence |

#### 4.23.4 Agent Relance Intelligente

| Fonctionnalité | Description |
|----------------|-------------|
| Canal optimal | Choix automatique du meilleur canal (SMS > email) basé sur les stats par client |
| Moment optimal | Envoi au meilleur horaire/jour basé sur l'analyse comportementale |
| Escalade progressive | Ton progressif : rappel amical (J+7) → formel (J+14) → mise en demeure (J+30) |
| Prédiction de paiement | Probabilité de paiement dans X jours (ML régression) |
| Rapport d'efficacité | Taux de recouvrement par canal, par type de client |
| Templates d'escalade | Modèles par niveau avec variables dynamiques |

#### 4.23.5 Agent OCR Factures

| Fonctionnalité | Description |
|----------------|-------------|
| Capture multi-source | Photo mobile, upload web, email forwarding |
| Extraction IA | Fournisseur, date, numéro, montants HT/TVA/TTC (API IA (Gemini) + spatie/pdf-to-text) |
| Pré-comptabilisation | Écriture SYSCOHADA pré-remplie prête à valider (mapping compte ← fournisseur) |
| Matching auto | Correspondance avec commandes/bons de livraison existants |
| Archive légale | Stockage avec horodatage et hash SHA256 (valeur probante) |
| Précision cible | 90%+ sur les champs clés |

#### 4.23.6 Agent Prédiction Trésorerie

| Fonctionnalité | Description |
|----------------|-------------|
| Projection cash flow | 3 à 6 mois basée sur factures impayées, fournisseurs, paie, historique bancaire, échéances fiscales |
| Alertes proactives | "Attention, tension de trésorerie prévue semaine 28" |
| Scénarios what-if | Simulation paramétrique ("Et si le client X ne paie pas ?") |
| Détection saisonnalité | Identification des patterns récurrents par mois |
| Graphique interactif | Zones critiques colorées (vert/orange/rouge) |

#### 4.23.7 Tables du système IA

```sql
CREATE TABLE ai_suggestions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    agent VARCHAR(50) NOT NULL,            -- 'ohada', 'fiscal', 'rapprochement', 'relance', 'ocr', 'tresorerie'
    type VARCHAR(50) NOT NULL,             -- 'anomalie', 'suggestion', 'alerte', 'action_auto', 'prevision'
    priority ENUM('critical','high','normal','low') DEFAULT 'normal',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,                             -- Données structurées spécifiques à l'agent
    confidence DECIMAL(5,2),               -- Score de confiance 0.00 → 100.00
    status ENUM('pending','approved','rejected','expired') DEFAULT 'pending',
    action_type VARCHAR(100) NULL,         -- 'categorize_transaction', 'create_entry', 'send_relance'...
    action_payload JSON NULL,              -- Données pour exécuter l'action si approuvée
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_client_status (client_id, status),
    INDEX idx_agent_priority (agent, priority),
    INDEX idx_created (created_at DESC),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE ai_learning_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,           -- 'categorize', 'match', 'flag', 'predict'
    input_data JSON NOT NULL,
    suggested_output JSON NOT NULL,
    actual_output JSON NULL,               -- Ce que l'humain a choisi
    was_correct BOOLEAN NULL,
    client_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_agent_action (agent, action),
    INDEX idx_correct (was_correct),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Service :** `App\Services\IA\GelIntelligenceService` — Orchestrateur des 6 agents
**Contrôleur :** `App\Http\Controllers\Gel\Ia\AiFeedController` — API du fil d'activité
**Composant Vue :** `resources/js/Pages/Gel/Ia/AiFeed.vue` — Fil d'activité IA

**Routes API :**
```
GET    /api/ia/feed                    → Liste paginée des suggestions (filtrable par agent, priority, status)
GET    /api/ia/feed/stats              → Compteurs par agent et par statut
POST   /api/ia/suggestions/{id}/approve → Approuver une suggestion et exécuter l'action
POST   /api/ia/suggestions/{id}/reject  → Rejeter une suggestion
POST   /api/ia/suggestions/{id}/modify  → Modifier puis approuver
DELETE /api/ia/suggestions/{id}         → Supprimer une suggestion expirée
```

---

### 4.24 Fil d'Activité IA — Activity Feed

Hub central de GEL Intelligence. Toutes les suggestions/actions des 6 agents IA sont affichées ici dans un fil chronologique. Le comptable consulte ce fil et approuve ou rejette chaque suggestion.

**Fonctionnalités :**
- Affichage chronologique inversé (plus récent en haut)
- Filtrable par : agent (6 filtres), priorité (critical/high/normal/low), statut (pending/approved/rejected), client, période
- Compteurs en haut : suggestions en attente par priorité (🔴 critiques, 🟡 hautes, 🟢 normales)
- Actions par suggestion : ✅ Approuver (exécute l'action), ❌ Rejeter, ✏️ Modifier puis approuver
- Notifications push pour les suggestions critiques
- Badge dans la sidebar GelLayout avec le nombre de suggestions en attente
- Design : Cards empilées avec code couleur par priorité

---

### 4.25 Omnisearch — Recherche Globale

Barre de recherche globale accessible depuis tous les portails via Ctrl+K (Cmd+K).

**Fonctionnalités :**
- Recherche unifiée sur : clients, transactions comptables, contacts, factures, employés, documents, pages de navigation, aide/documentation
- Résultats groupés par catégorie avec icônes
- Navigation au clavier (↑↓ pour sélectionner, Enter pour ouvrir)
- Résultats en temps réel (debounce 300ms)
- Historique des recherches récentes (5 dernières)
- Affichage en modal overlay (type Command Palette)

**Composant Vue :** `resources/js/Components/Omnisearch.vue`
**Contrôleur :** `App\Http\Controllers\Api\SearchController`
**Route :** `GET /api/search?q={query}&type={type}` → résultats groupés JSON

**Intégration :** Ajouté dans GelLayout.vue, CompanyLayout.vue et CpaLayout.vue (topbar). Event listener global Ctrl+K.

---

### 4.26 Workpapers — Dossiers de Travail

Système de révision structurée des comptes avant clôture comptable.

**Fonctionnalités :**
- Vue "balance de vérification" avec tous les comptes du plan comptable SYSCOHADA
- Statut de révision par compte : ✅ Révisé / ⏳ En cours / ❌ Non révisé
- Création d'écritures d'ajustement (OD) directement depuis les workpapers
- Comparaison côte à côte : N vs N-1 (2 colonnes)
- Notes internes par compte (visibles uniquement par l'équipe cabinet)
- Attachement de pièces justificatives par compte
- Progression globale : "72% des comptes révisés" avec barre de progression
- Verrouillage : possibilité de verrouiller la période une fois tous les comptes révisés

**Table :**
```sql
CREATE TABLE workpapers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    fiscal_year_id BIGINT UNSIGNED NOT NULL,
    period VARCHAR(20) NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    status ENUM('not_reviewed','in_progress','reviewed') DEFAULT 'not_reviewed',
    reviewer_id BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    notes TEXT NULL,
    adjustments JSON NULL,
    attachments JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_client_year_account (client_id, fiscal_year_id, account_id),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Contrôleur :** `App\Http\Controllers\Gel\Comptabilite\WorkpaperController`
**Composant Vue :** `resources/js/Pages/Gel/Accounting/Workpapers.vue`
**Route :** `/comptabilite/workpapers`

---

### 4.27 Magic Links — Demandes de documents sans login

Permet au comptable d'envoyer un lien sécurisé à un client pour uploader un document sans connexion.

**Fonctionnalités :**
- Création d'une "demande de document" : titre, description, client, deadline, documents attendus
- Lien unique signé (token UUID + expiration) envoyé par email, SMS ou WhatsApp
- Page publique sécurisée pour uploader sans authentification
- Rattachement automatique des documents au dossier client dans la GED
- Notification au comptable quand le client répond
- Suivi : envoyé / lu / répondu / expiré

**Table :**
```sql
CREATE TABLE magic_link_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    requested_documents JSON NULL,
    channel ENUM('email','sms','whatsapp','all') DEFAULT 'email',
    status ENUM('sent','viewed','responded','expired') DEFAULT 'sent',
    viewed_at TIMESTAMP NULL,
    responded_at TIMESTAMP NULL,
    expires_at TIMESTAMP NOT NULL,
    response_files JSON NULL,
    response_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_client_status (client_id, status),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

**Route publique :** `GET /documents/request/{token}` → page upload publique
**Contrôleur :** `App\Http\Controllers\Public\MagicLinkController`
**Composant Vue :** `resources/js/Pages/Public/MagicLinkUpload.vue`

---

### 4.28 Transactions Récurrentes

Automatisation des écritures comptables récurrentes avec 3 types :

| Type | Description |
|------|-------------|
| **Programmée (scheduled)** | Écriture créée automatiquement à la date prévue (ex: loyer le 1er du mois) |
| **Rappel (reminder)** | Notification au comptable pour créer l'écriture manuellement (ex: facture variable) |
| **Template** | Modèle sauvegardé pour usage ponctuel (ex: écriture exceptionnelle) |

Applicable à : Écritures de journal, factures, dépenses, notes de crédit

**Table :**
```sql
CREATE TABLE recurring_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    type ENUM('scheduled','reminder','template') NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    template_data JSON NOT NULL,
    frequency ENUM('daily','weekly','biweekly','monthly','quarterly','yearly') NULL,
    next_occurrence DATE NULL,
    last_occurrence DATE NULL,
    end_date DATE NULL,
    occurrences_count INT DEFAULT 0,
    max_occurrences INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_client_type (client_id, type),
    INDEX idx_next (next_occurrence, is_active),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

**Commande artisan :** `ProcessRecurringTransactions` — quotidienne à 06:00 — crée les écritures programmées et envoie les rappels

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

#### Hiérarchie des rôles (par niveau)

| Niveau | Rôle | Slug | Accès |
|--------|------|------|-------|
| **100** | Super Administrateur | `super_admin` | Total — voit toutes les entreprises, toutes les données |
| **50** | Comptable Cabinet | `comptable` | Comptabilité + facturation des clients assignés |
| **40** | Administrateur Entreprise | `company_admin` | Son entreprise uniquement — tous les modules |
| **30** | Manager Entreprise | `company_manager` | Son entreprise — opérations courantes (pas validation) |
| **20** | Employé Entreprise | `company_employee` | Son entreprise — lecture seule |
| **20** | Caissier | `caissier` | Module caisse uniquement |
| **20** | Juriste | `juriste` | Module juridique uniquement |
| **20** | Gestionnaire RH | `rh` | Module RH uniquement |
| **20** | Gestionnaire Projet | `gestionnaire_projet` | Module projets uniquement |
| **20** | Secrétaire | `secretaire` | Module DAE uniquement |
| **10** | Client | `client` | Portail CPA — consultation documents et factures |

#### Matrice des permissions par rôle

| Module | Action | super_admin | company_admin | company_manager | company_employee | Roles spécialisés |
|--------|--------|-------------|---------------|-----------------|------------------|-------------------|
| **comptabilite** | lire | ✅ | ✅ | ✅ | ✅ | — |
| | creer | ✅ | ✅ | ✅ | — | — |
| | modifier | ✅ | ✅ | ✅ | — | — |
| | supprimer | ✅ | ✅ | — | — | — |
| | valider | ✅ | ✅ | — | — | — |
| | saisir | ✅ | ✅ | ✅ | — | — |
| | exporter | ✅ | ✅ | ✅ | — | — |
| | rapports | ✅ | ✅ | ✅ | ✅ (lecture) | — |
| **facturation** | lire | ✅ | ✅ | ✅ | ✅ | — |
| | creer_facture | ✅ | ✅ | ✅ | — | — |
| | modifier_facture | ✅ | ✅ | ✅ | — | — |
| | annuler_facture | ✅ | ✅ | — | — | — |
| | imprimer_facture | ✅ | ✅ | ✅ | — | — |
| | regler | ✅ | ✅ | ✅ | — | — |
| | parametres | ✅ | ✅ | — | — | — |
| **caisse** | toutes (6) | ✅ | ✅ | toutes sauf clôture | lecture/historique | ✅ (caissier) |
| **juridique** | toutes (5) | ✅ | ✅ | toutes sauf archivage/suppression | consultation | ✅ (juriste) |
| **rh** | toutes (10) | ✅ | ✅ | toutes sauf paie/validation | lecture | ✅ (rh) |
| **projets** | toutes (6) | ✅ | ✅ | toutes sauf achèvement | lecture | ✅ (gestionnaire_projet) |
| **document** | toutes (6) | ✅ | ✅ | toutes sauf suppression | lecture/upload | — |
| **dae** | toutes (10) | ✅ | ✅ | toutes sauf validation | lecture | ✅ (secretaire) |
| **erp** | toutes (9) | ✅ | ✅ | toutes sauf paramètres | — | — |
| **crm** | toutes (8) | ✅ | ✅ | toutes | — | — |
| **it_helpdesk** | toutes (6) | ✅ | ✅ | toutes | — | — |
| **it_assets** | toutes (6) | ✅ | ✅ | toutes | — | — |
| **commerce** | toutes (17) | ✅ | ✅ | — | — | — |

#### 5.5.1 Permissions directes (user_permissions)

En complément des permissions de rôle, des permissions directes peuvent être attribuées à des utilisateurs spécifiques via `user_permissions` :

```sql
user_permissions (user_id, permission_id, granted_by, granted_at, client_id, expires_at)
```

Priorité : **permissions directes > permissions de rôle**.
Si un utilisateur a des permissions directes, les permissions de son rôle sont ignorées pour les modules concernés.

#### 5.5.2 Restrictions de champs (PermissionFieldRestriction)

```sql
permission_field_restrictions (module, action, role_slug, hidden_fields JSON, is_active)
```

**Exemple :**
| module | action | role_slug | hidden_fields |
|--------|--------|-----------|---------------|
| rh | lire | company_employee | `["salary", "bank_account"]` |
| commerce | voir | caissier | `["price_purchase", "margin"]` |

**Side client :** `authStore.isFieldHidden(module, field)` → masque le champ dans l'UI.

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
| `legal_company_infos` | Informations légales entreprise (RCCM, IFU, capital) |
| `legal_assemblies` | Assemblées générales et conseils |
| `legal_contracts` | Contrats avec parties, dates, montant |
| `legal_contract_signatures` | Signatures électroniques liées aux contrats |
| `legal_litigations` | Contentieux et litiges (référence, tribunal, montant) |
| `legal_compliance` | Obligations réglementaires (échéances, statuts) |
| `legal_acts_library` | Bibliothèque d'actes juridiques avec variables |
| `legal_registres` | Registres obligatoires |
| `legal_dossiers` | Dossiers juridiques transverses |
| `legal_veille` | Veille juridique et actualités |
| `legal_audit_log` | Journal d'audit des actions juridiques |
| `legal_cases` | (Legacy) Dossiers juridiques — remplacé par `legal_dossiers` |
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
| `UsersSeeder` | 30+ utilisateurs multi-rôles (admin GEL, company_admin, comptables, managers, employés, clients) |
| `ClientSeeder` | Clients de démonstration |
| `CrescendoDemoSeeder` | 4 comptes démo pour portail CPA |
| `RoleAndPermissionSeeder` | 11 rôles, 89 permissions, 12 modules |
| `LegalDemoSeeder` | 50+ enregistrements juridiques (contrats, contentieux, conformité, actes, assemblées, registres) |
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

### 8.9 Sidebar avec sous-menus dépliants

Regrouper les ~30 items de navigation en catégories repliables avec chevrons :

| Catégorie | Items inclus |
|-----------|-------------|
| 🏠 Accueil | Dashboard |
| 👥 CRM | Clients, Missions, Pôles |
| 📂 Documents | GED, Dossiers |
| 🧮 Comptabilité | Plan comptable, Journaux, Balance, Bilan, Résultat, Télédécl., Workpapers |
| 💼 Gestion | ERP, Facturation, Caisse, Commerce |
| 👔 RH & Paie | Employés, Contrats, Paie, Congés |
| ⚖️ Juridique | Tous les sous-modules juridiques |
| 🔧 IT Support | Helpdesk, ITAM, Maintenance |
| 📊 Projets | Tâches, jalons, budget |
| 🤖 IA & Automatisation | Fil IA, OCR, Relances, Rapprochement |
| ⚙️ Administration | Audit, Sécurité, Utilisateurs, Articles |

- Animation 0.2s sur le déploiement/repli
- État mémorisé dans localStorage
- Badge de notification par catégorie (ex: "CRM (3)")

### 8.10 Bouton "+ Nouveau" rapide

Bouton flottant en haut de la sidebar (sous le logo) avec dropdown d'actions rapides :
- Nouveau client, Nouvelle facture, Nouvelle écriture, Nouveau document
- Nouveau ticket IT, Nouvelle mission, Nouveau contrat juridique
- Raccourci clavier : `N` (quand pas dans un input)

### 8.11 Filtres en chips supprimables

Dans TOUS les tableaux du projet, afficher les filtres actifs sous forme de chips/tags avec bouton × pour les retirer.

**Composant réutilisable :** `resources/js/Components/FilterChips.vue`
**Props :** `filters` (array d'objets `{label, value, key}`)
**Emit :** `@remove(key)` pour retirer un filtre

### 8.12 Signets / Favoris personnalisables

Section "SIGNETS" dans la sidebar avec bouton ✏️ pour éditer. L'utilisateur peut ajouter/retirer des pages en favoris.

**Table :** `user_bookmarks` (user_id, label, url, icon, sort_order) — max 10 signets par utilisateur

### 8.13 Dashboard widgetisable

Le dashboard principal (GEL et Company) affiche des widgets réarrangeables par drag & drop.

**Widgets disponibles :** Stats clients, Échéances fiscales, Suggestions IA en attente, Factures impayées, Alertes connexions, CA du mois, Tâches à compléter, Anomalies détectées

**Table :** `user_dashboard_config` (user_id, widget_order JSON, hidden_widgets JSON)

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

### 9.7 Requêtes Client In-App

Le comptable crée une "requête" depuis n'importe quelle page (bouton "Demander au client") :
- La requête apparaît dans l'espace du client (portail Company) dans un onglet "Demandes"
- Le client peut répondre, uploader des documents, poser des questions
- Suivi : envoyée / lue / répondue / complétée
- Vue centralisée "Mes requêtes" côté comptable

### 9.8 Notes Internes vs Partagées

Sur chaque transaction, document, facture : possibilité d'ajouter des notes.
- **Toggle** "Interne" (visible uniquement par l'équipe cabinet) vs "Partagée" (visible aussi par le client)
- Icône 🔒 pour les notes internes, 👁️ pour les notes partagées

**Table :** `entity_notes` (notable_type, notable_id, user_id, content, is_internal BOOLEAN, created_at)

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
│   │   ├── Modules/            # Modules spécialisés
│   │   │   └── Legal/          # Module juridique (9 contrôleurs)
│   │   │       ├── LegalDashboardController
│   │   │       ├── LegalCompanyInfoController
│   │   │       ├── LegalAssembliesController
│   │   │       ├── LegalContratsController
│   │   │       ├── LegalLitigationsController
│   │   │       ├── LegalComplianceController
│   │   │       ├── LegalActsLibraryController
│   │   │       ├── LegalDossiersController
│   │   │       └── LegalRegistresController
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
│   ├── Legal/                            # Module juridique (11 modèles)
│   │   ├── LegalBaseModel.php            # Abstract avec scopeByClient() multi-tenant
│   │   ├── LegalCompanyInfo.php          # Fiche société
│   │   ├── LegalAssembly.php             # Assemblées
│   │   ├── LegalContract.php             # Contrats
│   │   ├── LegalContractSignature.php    # Signatures de contrats
│   │   ├── LegalLitigation.php           # Contentieux
│   │   ├── LegalCompliance.php           # Conformité
│   │   ├── LegalActsLibrary.php          # Bibliothèque d'actes
│   │   ├── LegalRegistre.php             # Registres
│   │   ├── LegalDossier.php              # Dossiers
│   │   ├── LegalVeille.php               # Veille juridique
│   │   └── LegalAuditLog.php             # Journal d'audit juridique
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

### 10.1.1 Comportement serveur détaillé

#### Double rendu Controllers (SPA + JSON)

Les contrôleurs suivent un **pattern de double rendu** basé sur `$request->expectsJson()` :

```php
// Pattern standard
public function index(Request $request)
{
    $data = Model::where(...)->get();

    if ($request->expectsJson()) {
        return response()->json($data);  // Appel API → JSON
    }

    return view('app', [                 // Navigation → SPA
        'page' => 'ma-page',
        'props' => ['data' => $data],
    ]);
}
```

Deux patterns existent :
1. **Rendu mixte** (view pour page + JSON pour API) — utilisé dans les contrôleurs GEL existants
2. **Pure API** (toujours JSON) — utilisé dans les contrôleurs Company pour `fetch()` côté client

#### Middleware chain complète

L'ordre d'exécution des middlewares sur une route protégée typique :

```
1. SetTenantContext (global)     → SET app.client_id (PostgreSQL RLS only)
2. EncryptCookies                → Standard Laravel
3. StartSession                  → Standard Laravel
4. Authenticate                  → Vérifie session valide
5. EnsureEmailVerified           → Vérifie email (pass-through pour super_admin)
6. CheckNotSuspended             → Vérifie que le compte n'est pas suspendu
7. CheckCompanyAccess            → Redirige company_admin vers /company/*
8. EnsureCompanyAccess           → Vérifie active_client_id valide dans user_clients
9. CheckModuleAccess             → Vérifie accès au module (module:rh, module:dae, etc.)
10. Controller                   → Exécute la logique métier
```

#### Services pattern

Les services sont des classes PHP dédiées, injectées par constructeur (Laravel auto-resolve) :

```php
class EmecefController extends Controller
{
    public function __construct(
        private readonly EmecefService $emecef
    ) {}
}
```

Services clés :
| Service | Localisation | Responsabilité |
|---------|-------------|----------------|
| `EmecefService` | `app/Services/EmecefService.php` | API e-MECeF DGI (émettre, annuler, vérifier, certifier) |
| `MobileMoneyService` | `app/Services/MobileMoneyService.php` | Paiements MTN MoMo, Moov Money |
| `SmsService` | `app/Services/SmsService.php` | Envoi SMS (Africa's Talking) |
| `WhatsAppService` | `app/Services/WhatsAppService.php` | Meta WABA v18.0 |
| `IrppCalculator` | `app/Services/Paie/IrppCalculator.php` | Calcul IRPP Bénin (7 tranches) |
| `CnssCalculator` | `app/Services/Paie/CnssCalculator.php` | Calcul CNSS (employeur 15,4%, salarié 3,36%) |
| `OcrInvoiceService` | `app/Services/OcrInvoiceService.php` | OCR factures fournisseurs |
| `BankReconciliationService` | `app/Services/BankReconciliationService.php` | Rapprochement bancaire |
| `ActeGeneratorService` | `app/Services/ActeGeneratorService.php` | Génération d'actes juridiques |

#### Scopes Eloquent

Les modèles utilisent des **scopes Eloquent** pour le filtrage multi-tenant :

```php
// Client scope (User, CompanyInvoice)
scopeByClient($query, int $clientId)

// LegalBaseModel abstract — scope multi-tenant pour le module juridique
scopeByClient() {
    if (auth()->user()?->isSuperAdmin()) return;  // Super admin → tout voir
    $query->where('client_id', clientId());        // Comptable → filtré
}

// Autres scopes fréquents
scopeActive()       // is_active = true
scopeNotSuspended() // is_suspended = false | null
scopeUnread()       // read_at IS NULL
```

#### Notifications

**Modèle :** `App\Models\Notification`
- `user_id` → destinataire
- `type` → catégorie (ex: `emecef_emise`)
- `title`, `message` → contenu affiché
- `data` → payload JSON (`{invoice_id, url}`)
- `read_at` → nullable, marquée lue quand non-null
- **Scope :** `unread()` → `WHERE read_at IS NULL`

---

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
│   │   │   ├── Legal/             # (Lien vers Modules/Legal)
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
│   │   ├── Modules/               # Modules spécialisés
│   │   │   └── Legal/             # Module juridique (21 pages)
│   │   │       ├── Dashboard.vue                          # KPI + listes récentes
│   │   │       ├── Societe/
│   │   │       │   └── Index.vue                          # Fiche société
│   │   │       ├── Assemblees/
│   │   │       │   ├── Index.vue, Show.vue, Form.vue      # CRUD assemblées
│   │   │       ├── Contrats/
│   │   │       │   ├── Index.vue, Show.vue, Form.vue      # CRUD contrats
│   │   │       ├── Contentieux/
│   │   │       │   ├── Index.vue, Show.vue, Form.vue      # CRUD contentieux
│   │   │       ├── Conformite/
│   │   │       │   ├── Index.vue, Form.vue, Calendrier.vue # Conformité
│   │   │       ├── Bibliotheque/
│   │   │       │   ├── Index.vue, Form.vue, Generer.vue   # Bibliothèque d'actes
│   │   │       ├── Dossiers/
│   │   │       │   ├── Index.vue, Show.vue, Form.vue      # Dossiers juridiques
│   │   │       └── Registres/
│   │   │           └── Show.vue                           # Registres
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

### 10.3 Comportement client (Vue SPA)

#### Initialisation de l'application

```
1. Blade template (app.blade.php / company.blade.php)
   ├── <div id="app" data-page="company-invoices" data-props='{...}'>
   └── <script id="auth-data">{"user": {...}}</script>

2. app.js (Vue entry point)
   ├── Crée l'app Vue (createApp)
   ├── Injecte 'page' et 'pageProps' via provide()
   ├── Enregistre tous les composants (app.component('nom', Component))
   └── Monte sur #app

3. Root.vue
   ├── inject('page') → 'company-invoices'
   ├── inject('pageProps') → { clientId: 2 }
   ├── Map() résout le composant → 'company-invoices' → Company/Invoices.vue
   └── <component :is="pageComponent" v-bind="pageProps" />

4. Layout wrapper (CompanyLayout.vue)
   ├── Topbar + Sidebar + Slot
   ├── AuthStore initialisation
   ├── Permission polling démarre
   └── Composant page rendu dans le slot
```

#### AuthStore (stores/auth.js)

**Initialisation :**
1. `initAuth()` parse `<script id="auth-data">` au chargement
2. `authStore.initFromApi()` appelle `GET /api/me/profile` pour charger :
   - `user` (profil complet)
   - `permissions` (format `module:action`)
   - `modules` (liste déduite des permissions)
   - `companies` (entreprises accessibles)
   - `activeCompany` (entreprise sélectionnée)

**Vérifications d'accès côté client :**
```javascript
// Vérifier l'accès à un module
authStore.hasModule('facturation')  // → true/false

// Vérifier une action spécifique
authStore.can('facturation', 'lire')  // → true/false

// Super Admin et Company Admin bypassent toujours
authStore.isSuperAdmin → true → tout accès autorisé
authStore.isCompanyAdmin → true → tout accès autorisé dans son scope
```

**Polling des permissions (toutes les 15 secondes) :**
```javascript
// startPermissionPolling() → setInterval toutes les 15s
// Appelle GET /api/company/events/check
// Si updated === true → window.location.reload()
// Utilisé dans CompanyLayout.vue

// Mécanisme :
// 1. CompanyLayout monte → startPermissionPolling()
// 2. Toutes les 15s : GET /api/company/events/check
// 3. Compare permissions avec le cache
// 4. Si changées → reload pour appliquer les nouveaux droits
// 5. Composant démonté → stopPermissionPolling()
```

**Restrictions de champs :**
```javascript
// Chargement des champs cachés pour un module
await authStore.loadFieldRestrictions('rh')
// Vérification si un champ est caché
if (authStore.isFieldHidden('rh', 'salary')) {
    // Masquer le champ salaire
}
```

**Changement de contexte entreprise :**
```javascript
// Bascule vers une autre entreprise
await authStore.switchToCompany(clientId)
// → POST /api/me/switch-context
// → Met à jour active_client_id
// → Recharge les permissions
// → window.location.reload()
```

#### Composants Vue — Pages Factures

**Page GEL (Gel/Erp/Invoice.vue) :**
- Affiche la liste des `ErpInvoice`
- Bouton DGI (`emitEmecef(invoiceId)`) :
  ```javascript
  const emitEmecef = async (invoiceId) => {
      const res = await fetch(`/emecef/emit/${invoiceId}`, { method: 'POST' })
      const data = await res.json()
      if (data.success) {
          alert('Facture émise à la DGI avec succès.')
          await fetchData() // Recharge la liste
      }
  }
  ```
- Badge "DGI" vert sur les factures déjà émises
- Statuts : brouillon, émise, envoyée, payée, impayée, annulée

**Page Company (Company/Invoices.vue) :**
- Affiche les `CompanyInvoice` + les `ErpInvoice` émises (fusionnées)
- Colonne "DGI" avec badge `[DGI ✓]` pour les factures certifiées
- Badge "ERP" pour les factures créées par le comptable
- Détail avec QR code e-MECeF (NIM, compteur, date, statut, QR URL)
- Modal paiement avec 4 méthodes (cash, transfer, momo, cheque)

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

### 11.4 Configuration post-installation

Après `php artisan migrate --seed`, plusieurs étapes de configuration sont nécessaires :

#### 11.4.1 Fichier .env

```env
# Base de données
DB_DATABASE=gel_cabinet
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# e-MECeF / Sygmef (DGI Bénin)
EMECEF_API_TOKEN=token_dgi
EMECEF_NIM=XX-XXXXXXX           # NIM global par défaut
EMECEF_TEST_MODE=true            # true = simulation sans API réelle
EMECEF_API_URL=https://sygmef.impots.bj/emcf/api

# 2FA
GOOGLE2FA_VIEW_PACKAGE= Blade  # ou "tailwind" selon le thème

# Mail (pour notifications, relances, envoi factures)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=mdp_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@gel.cabinet
MAIL_FROM_NAME="GEL Cabinet"

# Mobile Money
MOMO_API_KEY=
MOMO_API_SECRET=
MOMO_ENVIRONMENT= sandbox     # sandbox → production
```

#### 11.4.2 Activation des modules par client

Chaque entreprise cliente doit avoir ses modules activés :

```sql
-- Via la table client_modules (créée par le super_admin dans l'UI)
INSERT INTO client_modules (client_id, module, is_active, activated_at, activated_by)
VALUES (2, 'rh', true, NOW(), 1),
       (2, 'dae', true, NOW(), 1),
       (2, 'facturation', true, NOW(), 1);

-- Ou via la colonne disabled_modules sur clients
UPDATE clients SET disabled_modules = '["commerce"]' WHERE id = 2;
-- Les modules non listés dans disabled_modules sont accessibles par défaut
```

**Modules disponibles (12) :**
```
caisse, comptabilite, crm, dae, document, erp,
facturation, it_assets, it_helpdesk, juridique, projets, rh
```

#### 11.4.3 Configuration e-MECeF par client

Pour activer e-MECeF sur un client :

1. **Renseigner l'IFU** du client sur sa fiche (obligatoire pour la DGI)
2. **Renseigner le NIM** fourni par la DGI pour ce client
3. **Renseigner le mot de passe** e-MECeF du client (chiffré en base)
4. **Activer le toggle** `emecef_is_active`
5. **Configurer le régime fiscal** (réel_simplifié / réel_normal)

Ces champs sont modifiables uniquement par les super_admin / comptables, dans la fiche client (section paramètres).

#### 11.4.4 Création des utilisateurs entreprise

Les utilisateurs du portail entreprise sont créés via la table `user_clients` :

```php
// Liaison d'un utilisateur à une entreprise
UserClient::create([
    'user_id' => $user->id,
    'client_id' => $client->id,
    'role' => 'company_admin',  // ou company_manager, company_employee, client
    'is_active' => true,
    'joined_at' => now(),
]);

// Sélection du contexte actif
$user->switchToClient($clientId);
// → Met à jour active_client_id
// → Met à jour last_accessed_at dans user_clients
```

#### 11.4.5 Configuration des pôles et missions

Les pôles (départements) et missions sont créés par le super_admin :

```bash
# Seeders disponibles
php artisan db:seed --class=PoleSeeder        # Pôles de démonstration
php artisan db:seed --class=MissionSeeder      # Missions de démonstration
php artisan db:seed --class=LegalDemoSeeder    # Données juridiques (50+ enregistrements)
```

Chaque pôle peut être assigné à un ou plusieurs clients via `client_pole`.

#### 11.4.6 Vérification après installation

```bash
# 1. Vérifier les migrations
php artisan migrate:status

# 2. Vérifier les seeders
php artisan db:seed --class=DatabaseSeeder

# 3. Tester la connexion
#    admin@gel.cabinet / admin123 → Dashboard GEL
#    jean@techinnov.bj / admin123 → Dashboard entreprise TechInnov

# 4. Tester e-MECeF (en test mode)
#    EMECEF_TEST_MODE=true → pas d'appel API réel
#    → La facture reçoit de vraies données simulées

# 5. Vérifier npm build
npm run build
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

### ✅ Version 2.1 — Services & Navigation (achevée)

- [x] **7 sections de services** — Réorganisation de la navigation en 7 catégories (Administration, Consultation, Fiscal, IT, Social & Paie, Juridique, Logiciel Comptabilité)
- [x] **Pages dédiées** — Pages publiques pour chaque service avec contenu détaillé
- [x] **Sidebar GEL dynamique** — Navigation par groupes avec icônes et badges
- [x] **Module Commerce/POS** — Gestion commerciale complète avec point de vente, stocks et catalogue

### 🔥 Version 2.2 — GEL Intelligence (en cours)

- [ ] **Omnisearch (Ctrl+K)** — Recherche globale type Command Palette
- [ ] **Filtres en chips** — Composant FilterChips.vue pour tous les tableaux
- [ ] **Sidebar dépliante** — Regroupement en catégories repliables + état mémorisé
- [ ] **Bouton "+ Nouveau"** — Actions rapides dans la sidebar
- [ ] **Système Multi-Agents IA** — 6 agents spécialisés (OHADA, Fiscal, Rapprochement, Relance, OCR, Trésorerie)
- [ ] **Fil d'Activité IA** — AiFeed.vue avec approbation/rejet des suggestions
- [ ] **Agent Fiscal Bénin** — Alertes échéances + pré-remplissage TVA + calendrier fiscal
- [ ] **Agent Rapprochement Bancaire** — Import multi-format + matching intelligent + ready-to-post
- [ ] **Magic Links** — Demandes de documents sans login par lien sécurisé
- [ ] **Workpapers** — Dossiers de travail avec révision structurée des comptes
- [ ] **Agent Relance Intelligente** — Canal optimal + escalade progressive + prédiction
- [ ] **Agent OCR Factures** — Extraction IA + pré-comptabilisation automatique
- [ ] **Transactions Récurrentes** — 3 types (programmée, rappel, template)
- [ ] **Signets / Favoris** — Section SIGNETS personnalisable dans la sidebar
- [ ] **Dashboard widgetisable** — Widgets réarrangeables par drag & drop
- [ ] **Notes Internes vs Partagées** — Toggle 🔒 Interne / 👁️ Partagée
- [ ] **Reclassification en masse** — Changement de compte en un clic
- [ ] **Verrouillage de période** — Protection des périodes clôturées
- [ ] **Requêtes Client In-App** — Communication structurée comptable → client

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

- [ ] **OCR import factures fournisseurs** — API IA (Gemini) + spatie/pdf-to-text
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

### 13.1 Nouvelles tables (23 tables v2.0 + 8 tables v2.2)

**Tables Version 2.0 :**

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

**Tables Version 2.2 (GEL Intelligence) :**

```
ai_suggestions               → Suggestions/actions des 6 agents IA (fil d'activité)
ai_learning_log              → Journal d'apprentissage (corrections manuelles → amélioration)
workpapers                   → Dossiers de travail avec statut de révision par compte
magic_link_requests          → Demandes de documents sans login avec token signé
recurring_transactions       → Transactions récurrentes (3 types : scheduled/reminder/template)
user_bookmarks               → Signets personnalisables (favoris dans la sidebar)
user_dashboard_config        → Configuration des widgets du dashboard
entity_notes                 → Notes internes et partagées sur toutes les entités
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
├── OcrInvoiceService          → OCR factures fournisseurs (API IA (Gemini))
├── BankReconciliationService  → Rapprochement bancaire automatique
├── SmsService                 → SMS Bénin (Africa's Talking / passerelle locale)
├── WhatsAppService            → WhatsApp Business API (Meta WABA v18.0)
├── MobileMoneyService         → MTN MoMo + Moov Money
├── ItRapportService           → Rapports d'intervention IT (PDF signable)
└── IfuVerificationService     → Validation IFU Bénin (clé modulo 97)
├── IA\
│   └── GelIntelligenceService → Orchestrateur des 6 agents IA (suggestions, actions, apprentissage)
├── Comptabilite\
│   ├── WorkpaperService       → Révision structurée des comptes (workpapers)
│   └── RecurringTransactionService → Gestion des transactions récurrentes
├── RelanceIntelligenteService → Agent relance (canal optimal, escalade, prédiction paiement)
├── ReconciliationService      → Agent rapprochement bancaire (matching, scores, ready-to-post)
└── FiscalBeninService         → Agent fiscal (TVA, déclarations, échéances, simulation e-MECeF)
```

### 13.4 Nouvelles commandes artisan

| Commande | Fréquence | Description |
|----------|-----------|-------------|
| `CheckItAssetAlerts` | Hebdomadaire (lundi 08:00) | Alertes garanties et licences expirant |
| `ProcessRelances` | Quotidienne (09:00) | Relances automatiques clients impayés |
| `ProcessRecurringTransactions` | Quotidienne (06:00) | Création des écritures programmées et envoi des rappels |
| `ProcessAiAgentOhada` | Horaire | Analyse et suggestions Agent OHADA |
| `ProcessAiAgentFiscal` | Quotidienne (07:00) | Alertes échéances et pré-remplissage déclarations |
| `ProcessAiAgentRelance` | Quotidienne (08:00) | Analyse des impayés et suggestions de relance |

### 13.5 Nouveaux middlewares

| Middleware | Description |
|------------|-------------|
| `IpWhitelist` | Blocage des IP non autorisées par client |
| `CheckAiAgentAccess` | Vérification des droits d'accès aux agents IA |

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

P0 v2.2 (fondation GEL Intelligence)
├── Mise à jour CAHIER_DES_CHARGES.md
├── Omnisearch (Ctrl+K)
├── Filtres en chips (FilterChips.vue)
└── Sidebar dépliante + Bouton "+ Nouveau"

P1 v2.2 (core IA)
├── Tables IA + API CRUD
├── AiFeed.vue + badge sidebar
├── Agent Fiscal Bénin
└── Agent Rapprochement Bancaire

P2 v2.2 (automatisation)
├── Magic Links
├── Workpapers
├── Agent Relance Intelligente
├── Agent OCR Factures
├── Transactions Récurrentes
├── Signets
├── Dashboard widgetisable
└── Notes internes/partagées

P3 v2.2 (optimisation)
├── Agent OHADA complet
├── Agent Prédiction Trésorerie
├── Outils comptables en masse
└── Requêtes client in-app
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
