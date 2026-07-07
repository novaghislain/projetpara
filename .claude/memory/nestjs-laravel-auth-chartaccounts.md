---
name: nestjs-laravel-auth-chartaccounts
description: Conversion NestJS auth + SYSCOHADA to Laravel — Sanctum, 2FA, fiscal periods, tree hierarchy
metadata:
  type: project
---

Session de conversion d'un module NestJS/TypeORM (auth multi-tenant + plan comptable SYSCOHADA) vers Laravel 12/PHP pour le projet GEL Cabinet.

**Résultat**: 25 routes API, 4 contrôleurs (Auth, FiscalYear, FiscalPeriod, ChartAccount), 2 modèles (FiscalPeriod, RefreshToken), 3 seeders, 2 migrations + 1 migration de métadonnées.

**Points clés à retenir:**
- Laravel 12 n'a pas `middleware()` sur le Controller de base — appliquer via routes, pas constructeur
- Le Controller de base dans `app/Http/Controllers/Controller.php` est complètement vide
- Sanctum 4.0 ne publie pas de config (`config/sanctum.php`) — fonctionne sans
- Ne pas utiliser `response()->json($model->token)` pour refresh tokens — `token` est le hash stocké, retourner le `raw_token` non persisté
- SYSCOHADA: 345 comptes (274 existants + 14 regroupements + métadonnées enrichies), 136 racines dans l'arbre (car les comptes à 2+ chiffres sont sous leurs regroupements)

**Problèmes résolus:**
- FiscalPeriodController: paramètres renommés pour correspondre aux routes `{fiscalYear}` / `{period}`
- RefreshToken: `createForUser()` retourne maintenant `raw_token` comme propriété non persistée pour que le client reçoive le token brut
- FiscalYear: relation `periods()` manquante (causait `RelationNotFoundException` sur le `show()`)
- ChartAccountController tree: filtrer par `client_id` pour éviter les doublons; `is_summary` lu du modèle plutôt que recalculé

**Pour tester l'API:** `php artisan serve --port=8080` puis appeler les endpoints `/api/auth/*`, `/api/chart-accounts/*`, `/api/fiscal-years/*`
