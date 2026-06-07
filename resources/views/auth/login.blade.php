<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion | Victoire Para</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bs-primary: #1b5e20; --bs-primary-rgb: 27,94,32; }
        .btn-primary { background:#1b5e20!important; border-color:#1b5e20!important; }
        .text-primary { color:#1b5e20!important; }
        .shadow-premium { box-shadow:0 25px 50px -12px rgba(0,0,0,0.1); }
        body { background: #f8f9fa!important; }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5 col-lg-4">
                    <div class="text-center mb-4">
                        <a href="{{ url('/') }}">
                            <img src="/images/products/official_logo_victoire.png" alt="Victoire" style="height:50px;">
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4 py-2 small mb-3">
                            @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success rounded-4 py-2 small mb-3">{{ session('status') }}</div>
                    @endif

                    <div class="card border-0 shadow-premium rounded-5 p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold mb-1">Connexion</h4>
                            <p class="text-muted small">Connectez-vous à votre espace client</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Email</label>
                                <input type="email" name="email" class="form-control rounded-pill border-2 px-4 py-2"
                                    value="{{ old('email') }}" required autofocus placeholder="votre@email.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Mot de passe</label>
                                <input type="password" name="password" class="form-control rounded-pill border-2 px-4 py-2"
                                    required placeholder="Votre mot de passe">
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label small" for="remember">Se souvenir de moi</label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary fw-semibold">Mot de passe oublié ?</a>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">Se connecter</button>
                            <p class="text-center mt-4 small text-muted mb-0">
                                Pas encore de compte ?
                                <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Créer un compte</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
