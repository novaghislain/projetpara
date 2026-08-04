<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Double Authentification | GEL Cabinet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #1F2A44; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .auth-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 400px; padding: 40px 30px; text-align: center; }
        .auth-icon { font-size: 48px; color: #0D9488; margin-bottom: 20px; }
        .auth-title { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .auth-desc { font-size: 14px; color: #6B7280; margin-bottom: 30px; }
        .form-control { padding: 12px 16px; border-radius: 8px; border: 1px solid #E5E7EB; text-align: center; font-size: 20px; letter-spacing: 5px; font-weight: 600; }
        .form-control:focus { border-color: #0D9488; box-shadow: 0 0 0 4px rgba(13,148,136,0.1); }
        .btn-primary { background-color: #0D9488; border: none; padding: 12px; border-radius: 8px; font-weight: 600; width: 100%; font-size: 15px; margin-top: 20px; }
        .btn-primary:hover { background-color: #0F766E; }
    </style>
</head>
<body>
    <div class="auth-card">
        <i class="bi bi-shield-lock-fill auth-icon"></i>
        <h1 class="auth-title">Authentification Requise</h1>
        <p class="auth-desc">Veuillez entrer le code à 6 chiffres généré par votre application d'authentification.</p>

        @if($errors->any())
            <div class="alert alert-danger" style="font-size: 13px; text-align: left; border-radius: 8px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/2fa') }}">
            @csrf
            <div class="mb-3">
                <input type="text" name="one_time_password" class="form-control" placeholder="000000" maxlength="6" required autofocus autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary">Vérifier le code</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 13px;">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: #6B7280; text-decoration: none;"><i class="bi bi-box-arrow-left"></i> Retour à la connexion</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </div>
</body>
</html>
