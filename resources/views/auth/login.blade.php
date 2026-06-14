<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion | GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bs-primary: #FF7900; --bs-primary-rgb: 255,121,0; }
        .btn-primary { background:#FF7900!important; border-color:#FF7900!important; color:#000000!important; border-radius:0px!important; }
        .btn-primary:hover { background:#000000!important; border-color:#000000!important; color:#ffffff!important; }
        .text-primary { color:#FF7900!important; }
        .shadow-premium { box-shadow:0 25px 50px -12px rgba(0,0,0,0.1); }
        body { background: #f5f7fa!important; color:#000000; }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5 col-lg-4">
                    <div class="text-center mb-4">
                        <div class="bg-black text-white rounded-0 d-inline-flex align-items-center justify-content-center mb-3"
                             style="width: 64px; height: 64px; border-bottom: 3px solid #FF7900;">
                            <i class="bi-gem text-primary" style="font-size: 32px;"></i>
                        </div>
                        <h3 class="fw-bold mb-1 font-heading">GEL Cabinet</h3>
                        <p class="text-muted small">Plateforme de gestion de cabinet</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-0 py-2 small mb-3">
                            @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success rounded-0 py-2 small mb-3">{{ session('status') }}</div>
                    @endif

                    <div class="card border-0 shadow-premium rounded-0 p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold mb-1">Connexion</h4>
                            <p class="text-muted small">Connectez-vous à votre espace client</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Email</label>
                                <input type="email" name="email" class="form-control rounded-0 border-2 px-4 py-2"
                                    value="{{ old('email') }}" required autofocus placeholder="votre@email.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Mot de passe</label>
                                <input type="password" name="password" class="form-control rounded-0 border-2 px-4 py-2"
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
                            <button type="submit" class="btn btn-primary w-100 rounded-0 py-3 fw-bold shadow-sm">Se connecter</button>
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
