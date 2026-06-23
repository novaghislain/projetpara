{{-- ========================================================================= --}}
{{-- FICHIER : 404.blade.php                                                   --}}
{{-- RÔLE    : Page d'erreur 404 — Page introuvable                           --}}
{{-- ÉQUIPE  : GEL Cabinet — Équipe Dev Frontend                              --}}
{{-- ========================================================================= --}}
{{-- S'affiche quand une route ne correspond à aucune entrée du routeur       --}}
{{-- Laravel, ou quand une ressource (client, facture, document) n'existe     --}}
{{-- pas en base de données.                                                  --}}
{{-- Design neutre, utilisable sans CSS de la plateforme.                     --}}
{{-- ========================================================================= --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page introuvable — GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error-card { text-align: center; background: white; border-radius: 16px; padding: 48px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); max-width: 480px; }
        .error-code { font-size: 96px; font-weight: 800; color: #FF7900; line-height: 1; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">404</div>
        <h2 class="mt-3 fw-bold">Page introuvable</h2>
        <p class="text-muted mt-2">La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <a href="/" class="btn btn-primary mt-3">Retour à l'accueil</a>
    </div>
</body>
</html>
