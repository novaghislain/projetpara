{{-- ========================================================================= --}}
{{-- FICHIER : 500.blade.php                                                   --}}
{{-- RÔLE    : Page d'erreur 500 — Erreur interne serveur                     --}}
{{-- ÉQUIPE  : GEL Cabinet — Équipe Dev Backend                              --}}
{{-- ========================================================================= --}}
{{-- S'affiche en cas d'exception non rattrapée : erreur PHP, requête BDD    --}}
{{-- qui échoue, timeout, ou tout plantage critique.                         --}}
{{-- En production, le message est volontairement vague pour ne pas exposer  --}}
{{-- de détails internes à l'utilisateur.                                    --}}
{{-- ========================================================================= --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur serveur — GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error-card { text-align: center; background: white; border-radius: 16px; padding: 48px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); max-width: 480px; }
        .error-code { font-size: 96px; font-weight: 800; color: #cd3c14; line-height: 1; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">500</div>
        <h2 class="mt-3 fw-bold">Erreur interne</h2>
        <p class="text-muted mt-2">Une erreur est survenue. L'équipe technique a été informée.</p>
        <a href="/" class="btn btn-primary mt-3">Retour à l'accueil</a>
    </div>
</body>
</html>
