<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vérification 2FA — GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; }
        .card-2fa { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); max-width: 440px; width: 100%; padding: 2.5rem; }
    </style>
</head>
<body>
    <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
        <div class="card-2fa">
            <div style="text-align:center;margin-bottom:1.5rem;">
                <div style="width:56px;height:56px;background:rgba(255,121,0,0.1);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-shield-lock" style="font-size:1.6rem;color:#FF7900;"></i>
                </div>
                <h2 style="font-size:1.2rem;font-weight:700;font-family:'Outfit',sans-serif;margin-bottom:0.25rem;">
                    Authentification à deux facteurs
                </h2>
                <p style="color:#6b7280;font-size:0.85rem;margin-bottom:0;">
                    Ouvrez votre application d'authentification<br/>et saisissez le code à 6 chiffres.
                </p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    @foreach ($errors->all() as $e)
                        <div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i>{{ session('info') }}
                </div>
            @endif

            <form method="POST" action="/2fa/verify">
                @csrf
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.85rem;font-weight:500;margin-bottom:0.5rem;">
                        Code de vérification
                    </label>
                    <input type="text" name="code" class="form-control" inputmode="numeric" pattern="[0-9]*" maxlength="6" required
                           autocomplete="one-time-code" autofocus
                           placeholder="000000"
                           style="font-size:1.5rem;letter-spacing:8px;text-align:center;font-family:monospace;" />
                </div>
                <button type="submit" class="btn btn-primary w-100" style="padding:0.6rem;">
                    <i class="bi bi-shield-check me-1"></i> Vérifier
                </button>
            </form>

            <div style="text-align:center;margin-top:1.25rem;">
                <a href="/login" style="color:#6b7280;font-size:0.8rem;text-decoration:none;">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</body>
</html>
