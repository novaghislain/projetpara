# Archive — Fichiers inutiles / dépréciés

Ces fichiers ont été archivés depuis la racine du projet Para car ils ne sont plus nécessaires.

## Structure

```
_archive/
├── README.md
├── root/               → Fichiers artifacts à la racine du projet
│   ├── breeze_help.txt       — Aide Laravel Breeze
│   ├── build_error.txt       — Log d'erreur de build
│   ├── build_log.txt         — Log de build
│   ├── cequejefais.md        — Journal de développement personnel
│   ├── cookie.txt            — Cookie de debug
│   ├── cookies.txt           — Cookie de debug
│   ├── edits_restore.json    — Sauvegarde IDE (extension)
│   └── excel_analysis.txt    — Analyse de données
│
├── controllers/         → Contrôleurs Company/Compta (dépréciés)
│   └── Company/Compta/
│       ├── DashboardController.php  — Tableau de bord comptable
│       ├── CompteController.php     — Plan comptable SYSCOHADA
│       ├── EcritureController.php   — Écritures (partie double)
│       ├── JournalController.php    — Journaux comptables
│       ├── FactureController.php    — Facturation clients
│       ├── BanqueController.php     — Banque & rapprochement
│       ├── TvaController.php        — TVA & déclarations fiscales
│       ├── RapportController.php    — États financiers SYSCOHADA
│       └── Modules/
│           ├── StockController.php          — Gestion de stock
│           ├── HotelChambreController.php   — Hôtel (chambres)
│           ├── ScolaireController.php       — Scolaire (élèves/classes)
│           ├── LocationController.php       — Location immobilière
│           ├── TontineController.php        — Tontines
│           ├── PressingController.php       — Pressing
│           ├── TransportController.php      — Transport/transit
│           ├── MorgueController.php         — Morgue
│           ├── RestaurationController.php   — Restauration
│           └── IndustrieController.php      — Industrie
│
└── spa-pages/           → Pages Vue SPA Company/Compta (dépréciées)
    └── Company/Compta/
        ├── Dashboard.vue
        ├── Balance.vue
        ├── CreateEntry.vue
        ├── JournalEntries.vue
        ├── Banque/Index.vue
        ├── Comptes/Index.vue
        ├── Ecritures/Index.vue
        ├── Exercices/Index.vue
        ├── Factures/Index.vue
        ├── Immobilisations/Index.vue
        ├── Journaux/Index.vue
        ├── Tva/Index.vue
        └── Rapports/
            ├── Aging.vue, Bilan.vue, CashFlow.vue
            ├── GrandLivre.vue, Resultat.vue, TrialBalance.vue
```

## Pourquoi ces fichiers ont été archivés

### Fichiers racine
Simples artifacts de développement (logs, cookies, notes personnelles) qui encombraient la racine du projet.

### Company/Compta
Ces contrôleurs et pages Vue étaient dépréciés (`@deprecated`). La comptabilité est désormais assurée par :
- **GelBusiness/Comptabilite/** — Portail entreprise (Blade)
- **GelAccountant/Comptabilite/** — Portail comptable (Blade)
- **Gel/Comptabilite/** — API pour le SPA Cabinet

## Routes désactivées

Les routes suivantes ont été commentées dans `routes/web.php` :
- `prefix('comptabilite')->name('compta.')` — toutes les routes Company/Compta

Les références dans `resources/js/Root.vue` (lignes 112-128) ont aussi été commentées.

## Restauration

Pour restaurer un fichier :
```bash
# Exemple : restaurer un contrôleur
mv _archive/controllers/Company/Compta/DashboardController.php app/Http/Controllers/Company/Compta/
# Puis réactiver les routes dans routes/web.php
```
