# ⚠️ Company/Compta — Contrôleurs obsolètes

> **Ces contrôleurs sont dépréciés et ne doivent plus être utilisés.**

## Pourquoi ?

Ces contrôleurs ont été écrits pour une architecture `Inertia.js` qui n'est plus en service. Ils n'ont **aucune route active** qui les référence.

## Architecture actuelle

| Interface | Framework | Emplacement |
|-----------|-----------|-------------|
| **GEL Cabinet** (cabinet comptable) | SPA Vue + `view('app')` | `app/Http/Controllers/Gel/` |
| **GEL Business** (portail entreprise) | Blade | `app/Http/Controllers/GelBusiness/` |
| **GEL Accountant** (espace comptable) | Blade | `app/Http/Controllers/GelAccountant/` |

## Ce qui remplace ces contrôleurs

- Comptabilité d'entreprise → `app/Http/Controllers/Company/AccountingController.php`
- Les modules métier (stock, hôtel, scolaire, etc.) n'ont pas été réécrits.
  La fonctionnalité équivalente se trouve dans `app/Http/Controllers/Gel/` (SPA cabinet)
  ou `app/Http/Controllers/GelBusiness/` (portail entreprise).

## Calendrier de suppression

Ces fichiers seront **supprimés dans une version future** du projet.

Ne pas utiliser ces fichiers dans du nouveau code.
