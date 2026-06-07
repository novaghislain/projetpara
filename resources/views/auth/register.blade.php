<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscription | Victoire Para</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --bs-primary: #1b5e20; --bs-primary-rgb: 27,94,32; }
        .btn-primary { background:#1b5e20!important; border-color:#1b5e20!important; }
        .text-primary { color:#1b5e20!important; }
        .shadow-premium { box-shadow:0 25px 50px -12px rgba(0,0,0,0.1); }
    </style>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
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

                    <div class="card border-0 shadow-premium rounded-5 p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold mb-1">Inscription</h4>
                            <p class="text-muted small">Créez votre espace client Victoire</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Nom complet</label>
                                <input type="text" name="name" class="form-control rounded-pill border-2 px-4 py-2"
                                    value="{{ old('name') }}" required autofocus placeholder="Votre nom">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Email</label>
                                <input type="email" name="email" class="form-control rounded-pill border-2 px-4 py-2"
                                    value="{{ old('email') }}" required placeholder="votre@email.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Mot de passe</label>
                                <input type="password" name="password" class="form-control rounded-pill border-2 px-4 py-2"
                                    required placeholder="Minimum 8 caractères">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold small">Confirmer le mot de passe</label>
                                <input type="password" name="password_confirmation" class="form-control rounded-pill border-2 px-4 py-2"
                                    required placeholder="Retapez le mot de passe">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">Créer mon compte</button>
                            <p class="text-center mt-4 small text-muted mb-0">
                                Déjà un compte ? <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Se connecter</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
