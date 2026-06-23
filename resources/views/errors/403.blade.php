{{-- ========================================================================= --}}
{{-- FICHIER : 403.blade.php                                                   --}}
{{-- RÔLE    : Page d'erreur 403 — Accès refusé                               --}}
{{-- ÉQUIPE  : GEL Cabinet — Équipe Dev Frontend                              --}}
{{-- ========================================================================= --}}
{{-- S'affiche quand un utilisateur tente d'accéder à une route sans les      --}}
{{-- droits suffisants (middleware, policy, ou périmètre multi-tenant).        --}}
{{-- Design neutre, sans framework — utilisable même si le CSS de la plate-   --}}
{{-- forme n'est pas chargé (exception en amont du SPA).                       --}}
{{-- ========================================================================= --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès refusé — GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error-card { text-align: center; background: white; border-radius: 16px; padding: 48px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); max-width: 480px; }
        .error-code { font-size: 96px; font-weight: 800; color: #cd3c14; line-height: 1; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <h2 class="mt-3 fw-bold">Accès refusé</h2>
        <p class="text-muted mt-2">Vous n'avez pas les droits nécessaires pour accéder à cette ressource.</p>
        <a href="/" class="btn btn-primary mt-3">Retour à l'accueil</a>
    </div>
</body>
</html>
