@extends('layouts.gel-admin')

@section('title', 'Sécurité du Compte')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Sécurité du Compte</h1>
        <p class="admin-page-sub">Gérez votre mot de passe et l'authentification à deux facteurs.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fas fa-key me-2"></i> Changer le mot de passe</div>
            </div>
            <div class="admin-card-body">
                <form action="{{ route('gel-admin.security.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nouveau mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="admin-btn admin-btn-primary w-100">Mettre à jour le mot de passe</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fas fa-shield-alt me-2"></i> Authentification à deux facteurs (2FA)</div>
            </div>
            <div class="admin-card-body">
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-mobile-alt fa-3x text-muted"></i>
                    </div>
                    <h5 class="fw-bold">Sécurisez davantage votre compte</h5>
                    <p class="text-muted small">Ajoutez une couche de sécurité supplémentaire en exigeant un code de vérification généré par votre application d'authentification lors de la connexion.</p>
                    @if(auth()->user()->is_company_admin)
                        <div class="alert alert-warning text-start" style="font-size: 13px;">
                            <i class="fas fa-exclamation-triangle me-2"></i> En tant que <strong>Propriétaire du Cabinet</strong>, l'activation de la 2FA est fortement recommandée (voire obligatoire selon votre plan).
                        </div>
                    @endif
                    <button class="admin-btn admin-btn-secondary" onclick="alert('Module 2FA en cours de développement.')">Activer la 2FA</button>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-body">
                <a href="{{ route('gel-admin.security.sessions') }}" class="d-flex justify-content-between align-items-center text-decoration-none">
                    <div>
                        <div class="fw-bold text-dark"><i class="fas fa-laptop me-2"></i> Gérer les sessions actives</div>
                        <div class="text-muted small mt-1">Déconnectez-vous des autres appareils.</div>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
