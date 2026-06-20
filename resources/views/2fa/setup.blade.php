<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activation 2FA — GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; }
    </style>
</head>
<body>
    <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
        <div style="background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.08);max-width:600px;width:100%;padding:2.5rem;">

            <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:0.5rem;font-family:'Outfit',sans-serif;">
                <i class="bi bi-shield-lock" style="margin-right:8px;color:#FF7900;"></i>Activer 2FA
            </h2>
            <p style="color:#6b7280;font-size:0.9rem;margin-bottom:1.5rem;">
                Scannez ce QR code avec <strong>Google Authenticator</strong> ou <strong>Authy</strong>.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div style="text-align:center;margin-bottom:2rem;">
                <div style="display:inline-block;background:#fff;border:2px solid #e5e7eb;border-radius:12px;padding:1rem;">
                    {!! $qrCodeUrl !!}
                </div>
                <p style="font-size:0.8rem;color:#9ca3af;margin-top:0.5rem;">
                    Ou entrez manuellement : <code style="font-size:0.7rem;word-break:break-all;">{{ $secret }}</code>
                </p>
            </div>

            <h6 style="font-weight:600;margin-bottom:0.75rem;">Codes de secours</h6>
            <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.75rem;">
                Gardez ces codes en lieu sûr. Ils vous permettront de vous connecter si vous perdez l'accès à votre application d'authentification.
            </p>
            <div style="background:#f9fafb;border-radius:8px;padding:1rem;margin-bottom:1.5rem;font-family:monospace;font-size:0.85rem;">
                @foreach($recoveryCodes as $code)
                    <div style="padding:2px 0;">{{ $code }}</div>
                @endforeach
            </div>

            <form method="POST" action="/user/two-factor/confirm">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.85rem;font-weight:500;margin-bottom:0.4rem;">
                        Code de confirmation (6 chiffres)
                    </label>
                    <input type="text" name="code" class="form-control" inputmode="numeric" pattern="[0-9]*" maxlength="6" required
                           placeholder="000000" style="font-size:1.5rem;letter-spacing:8px;text-align:center;font-family:monospace;" />
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-1"></i> Activer et vérifier
                </button>
            </form>
        </div>
    </div>
</body>
</html>
