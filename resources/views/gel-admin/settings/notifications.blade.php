@extends('layouts.gel-admin')

@section('title', 'Paramètres de Notifications')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Paramètres de Notifications</h1>
        <p class="admin-page-sub">Gérez vos préférences d'alertes par email et via l'interface.</p>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Préférences enregistrées.');">
            @csrf
            
            <h5 class="fw-bold mb-4 border-bottom pb-2">Notifications par Email</h5>
            
            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="email_invoices" checked>
                <label class="form-check-label ms-2" for="email_invoices">
                    <strong>Factures & Abonnements</strong><br>
                    <span class="text-muted small">Recevoir une notification avant le renouvellement de l'abonnement ou pour un paiement échoué.</span>
                </label>
            </div>
            
            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="email_security" checked>
                <label class="form-check-label ms-2" for="email_security">
                    <strong>Alertes de Sécurité</strong><br>
                    <span class="text-muted small">Être notifié en cas de connexion depuis un nouvel appareil ou de changement de mot de passe.</span>
                </label>
            </div>

            <div class="mb-4 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="email_team">
                <label class="form-check-label ms-2" for="email_team">
                    <strong>Activité de l'équipe</strong><br>
                    <span class="text-muted small">Recevoir un email quand un nouveau membre accepte une invitation.</span>
                </label>
            </div>

            <h5 class="fw-bold mb-4 border-bottom pb-2">Notifications In-App (GEL Intelligence)</h5>

            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="app_ai" checked>
                <label class="form-check-label ms-2" for="app_ai">
                    <strong>Recommandations de l'Assistant IA</strong><br>
                    <span class="text-muted small">Laisser GEL Intelligence analyser votre activité et suggérer des améliorations ou alertes.</span>
                </label>
            </div>

            <div class="mt-5">
                <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save me-2"></i> Enregistrer les préférences</button>
            </div>
        </form>
    </div>
</div>
@endsection
