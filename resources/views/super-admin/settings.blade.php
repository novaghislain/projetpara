@extends('layouts.gel-super-admin')

@section('title', 'Paramètres Globaux')

@section('content')
<div class="mb-4">
    <h1 style="font-size: 24px; font-weight: 700; color: #111827;">Paramètres Système</h1>
    <div style="color: #6B7280; font-size: 14px;">Configuration globale de la plateforme SaaS</div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="sa-card mb-4">
            <div class="sa-card-header">
                <div class="sa-card-title"><i class="fas fa-server text-muted me-2"></i> Mode Maintenance</div>
            </div>
            <div class="sa-card-body">
                <p class="text-muted" style="font-size: 14px;">Activer ce mode empêchera tous les utilisateurs (sauf Super Admin) de se connecter.</p>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="maintenanceMode">
                    <label class="form-check-label" for="maintenanceMode">Activer le mode maintenance</label>
                </div>
                <button class="btn btn-sm btn-light border">Enregistrer</button>
            </div>
        </div>
        
        <div class="sa-card">
            <div class="sa-card-header">
                <div class="sa-card-title"><i class="fas fa-lock text-muted me-2"></i> Sécurité</div>
            </div>
            <div class="sa-card-body">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="force2FA" checked>
                    <label class="form-check-label" for="force2FA">Forcer le 2FA pour les nouveaux Company Admins</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="passwordExpiry">
                    <label class="form-check-label" for="passwordExpiry">Expiration des mots de passe tous les 90 jours</label>
                </div>
                <button class="btn btn-sm btn-light border">Enregistrer</button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <div class="sa-card-title"><i class="fas fa-cogs text-muted me-2"></i> Variables d'environnement</div>
            </div>
            <div class="sa-card-body">
                <div class="mb-3">
                    <label class="form-label" style="font-size: 13px; font-weight: 500;">APP_URL</label>
                    <input type="text" class="form-control form-control-sm" value="{{ env('APP_URL') }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 13px; font-weight: 500;">APP_ENV</label>
                    <input type="text" class="form-control form-control-sm" value="{{ env('APP_ENV') }}" readonly>
                </div>
                <div class="alert alert-warning" style="font-size: 13px; padding: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> La modification directe du fichier .env via l'interface est désactivée par sécurité.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
