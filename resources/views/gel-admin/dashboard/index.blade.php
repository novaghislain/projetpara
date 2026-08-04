@extends('layouts.gel-admin')

@section('title', 'Tableau de bord - Admin')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Tableau de bord</h1>
        <p class="admin-page-sub">Vue d'ensemble de votre entreprise et de vos équipes.</p>
    </div>
    <a href="{{ route('gel-admin.team.invitations.index') }}" class="admin-btn admin-btn-primary">
        <i class="fas fa-user-plus"></i> Inviter un membre
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="admin-card text-center py-4">
            <div style="font-size: 32px; color: var(--admin-accent); font-weight: 700;">{{ $teamCount }}</div>
            <div style="font-size: 14px; color: var(--admin-text-muted);">Membres d'équipe</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card text-center py-4">
            <div style="font-size: 32px; color: var(--admin-success); font-weight: 700;">{{ $clientsCount }}</div>
            <div style="font-size: 14px; color: var(--admin-text-muted);">Clients gérés</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card text-center py-4">
            <div style="font-size: 32px; color: var(--admin-warning); font-weight: 700;">2</div>
            <div style="font-size: 14px; color: var(--admin-text-muted);">Portails actifs</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fas fa-building me-2"></i> Identité de l'entreprise</div>
            </div>
            <div class="admin-card-body">
                @if($cabinet)
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 150px;">Raison sociale</td>
                            <td class="fw-bold">{{ $cabinet->nom ?? $cabinet->nom_entreprise }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">IFU</td>
                            <td>{{ $cabinet->ifu ?? 'Non renseigné' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">RCCM</td>
                            <td>{{ $cabinet->rccm ?? 'Non renseigné' }}</td>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <a href="{{ route('gel-admin.profile.index') }}" class="admin-btn admin-btn-secondary btn-sm">Modifier le profil</a>
                    </div>
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i><br>
                        Le profil de votre entreprise n'est pas encore configuré.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div class="admin-card-title"><i class="fas fa-link me-2"></i> Espace Client Public</div>
            </div>
            <div class="admin-card-body text-center">
                @if($cabinet && $cabinet->slug)
                    <div class="mb-3">
                        <p class="text-muted mb-2">Lien d'accès pour vos clients finaux :</p>
                        <div class="p-3 bg-light rounded border font-monospace text-primary">
                            https://client.gelsabinet.com/{{ $cabinet->slug }}
                        </div>
                    </div>
                    <button class="admin-btn admin-btn-secondary" onclick="navigator.clipboard.writeText('https://client.gelsabinet.com/{{ $cabinet->slug }}'); alert('Lien copié !');">
                        <i class="fas fa-copy"></i> Copier le lien
                    </button>
                    <a href="http://client.gelsabinet.com/{{ $cabinet->slug }}" target="_blank" class="admin-btn admin-btn-primary">
                        <i class="fas fa-external-link-alt"></i> Ouvrir le portail
                    </a>
                @else
                    <p class="text-muted">Vous devez configurer votre entreprise pour générer votre portail client.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
