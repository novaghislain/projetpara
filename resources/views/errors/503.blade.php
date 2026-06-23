{{-- ========================================================================= --}}
{{-- FICHIER : 503.blade.php                                                   --}}
{{-- RÔLE    : Page d'erreur 503 — Service indisponible (maintenance)         --}}
{{-- ÉQUIPE  : GEL Cabinet — Équipe Dev Ops                                  --}}
{{-- ========================================================================= --}}
{{-- S'affiche quand l'application est en mode maintenance (php artisan down) --}}
{{-- ou quand le serveur est temporairement incapable de répondre.            --}}
{{-- Les utilisateurs voient cette page au lieu d'une erreur blanche.         --}}
{{-- ========================================================================= --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance — GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error-card { text-align: center; background: white; border-radius: 16px; padding: 48px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); max-width: 480px; }
        .error-code { font-size: 96px; font-weight: 800; color: #ffcc00; line-height: 1; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">503</div>
        <h2 class="mt-3 fw-bold">Service indisponible</h2>
        <p class="text-muted mt-2">L'application est momentanément indisponible pour maintenance.</p>
        <a href="/" class="btn btn-primary mt-3">Réessayer</a>
    </div>
</body>
</html>
