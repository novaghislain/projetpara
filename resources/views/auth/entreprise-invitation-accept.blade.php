<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceptation d'invitation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold mb-1">Invitation</h3>
                            <p class="text-muted">Vous avez été invité(e) par l'entreprise <strong>{{ $invitation->entreprise->nom ?? 'Entreprise' }}</strong>.</p>
                        </div>

                        <div class="alert alert-info border-0 bg-light text-center">
                            <p class="mb-1">Rôle proposé : <strong>{{ ucfirst($invitation->role_invite) }}</strong></p>
                            <p class="mb-0 text-muted small">Invitation envoyée à : {{ $invitation->email }}</p>
                        </div>

                        @if(!Auth::check())
                            <div class="alert alert-warning border-0 text-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Veuillez vous connecter avec l'adresse <strong>{{ $invitation->email }}</strong> pour accepter l'invitation.
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary">Créer un compte</a>
                            </div>
                        @else
                            @if(Auth::user()->email !== $invitation->email)
                                <div class="alert alert-danger border-0 text-center">
                                    <i class="fas fa-times-circle me-2"></i>
                                    Vous êtes connecté(e) en tant que <strong>{{ Auth::user()->email }}</strong>.<br>
                                    L'invitation est destinée à <strong>{{ $invitation->email }}</strong>.
                                </div>
                                <div class="text-center">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-danger">Déconnexion</button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('invitation.entreprise.accept.submit', $invitation->token) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 py-2">
                                        <i class="fas fa-check-circle me-2"></i> Accepter l'invitation
                                    </button>
                                </form>
                                <form action="{{ route('dashboard') }}" method="GET" class="mt-2 text-center">
                                    <button type="submit" class="btn btn-link text-muted">Ignorer pour le moment</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
