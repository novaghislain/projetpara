@extends('layouts.gel-business')
@section('title', "Choix de l'espace")

@section('content')
<div class="gel-workspace-selector">
    <div class="gel-workspace-header">
        <h2 class="fw-bold" style="color:var(--gel-text-primary); margin:0 0 6px;">Sélectionnez votre espace de travail</h2>
        <p class="text-muted" style="margin:0 0 28px;">Vous avez accès à plusieurs services. Choisissez l'espace auquel vous souhaitez accéder.</p>
    </div>

    <div class="gel-grid-3">
        <!-- Carte Comptabilité -->
        <a href="{{ route('gel-business.workspace.set', 'comptabilite') }}" class="gel-card gel-workspace-card">
            <div class="gel-workspace-icon" style="background:var(--gel-primary-light); color:#FF7900;">
                <i class="bi bi-calculator"></i>
            </div>
            <h4 class="fw-bold" style="color:var(--gel-text-primary); margin:0 0 8px;">Comptabilité</h4>
            <p class="text-muted small" style="margin:0;">Ventes, achats, banque, états financiers et messagerie avec le comptable.</p>
        </a>

        <!-- Carte Secrétariat -->
        <a href="{{ route('gel-business.workspace.set', 'secretariat') }}" class="gel-card gel-workspace-card">
            <div class="gel-workspace-icon" style="background:var(--gel-info-bg); color:#009FE3;">
                <i class="bi bi-briefcase"></i>
            </div>
            <h4 class="fw-bold" style="color:var(--gel-text-primary); margin:0 0 8px;">Secrétariat</h4>
            <p class="text-muted small" style="margin:0;">Gestion de l'agenda, suivi des relances, documents et appels.</p>
        </a>

        <!-- Carte Gestion -->
        <a href="{{ route('gel-business.workspace.set', 'gestion') }}" class="gel-card gel-workspace-card">
            <div class="gel-workspace-icon" style="background:var(--gel-success-bg); color:#00A86B;">
                <i class="bi bi-kanban"></i>
            </div>
            <h4 class="fw-bold" style="color:var(--gel-text-primary); margin:0 0 8px;">Gestion</h4>
            <p class="text-muted small" style="margin:0;">Ventes, dépenses, banque : pilotez votre activité au quotidien.</p>
        </a>
    </div>
</div>
@endsection
