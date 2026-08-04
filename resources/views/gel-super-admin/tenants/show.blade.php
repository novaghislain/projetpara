@extends('layouts.gel-super-admin')

@section('title', 'Détails Entreprise')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('gel-super-admin.tenants.index') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left me-1"></i> Retour à la liste</a>
        <h1 class="page-title">{{ $entreprise->nom }}</h1>
        <p class="page-subtitle">Détails, équipe, et historique de l'entreprise.</p>
    </div>
    <div>
        @php
            $proprietaire = $entreprise->proprietaires->first();
            $isSuspended = $proprietaire ? $proprietaire->is_suspended : false;
        @endphp

        @if($proprietaire && !$isSuspended)
        <form action="{{ route('gel-super-admin.tenants.impersonate', $entreprise->id) }}" method="POST" class="d-inline" target="_blank">
            @csrf
            <button type="submit" class="btn btn-outline-info">
                <i class="fas fa-user-secret me-2"></i> Mode Support
            </button>
        </form>
        @endif
        
        <form action="{{ route('gel-super-admin.tenants.destroy', $entreprise->id) }}" method="POST" class="d-inline" onsubmit="return confirm('La suppression d\'une entreprise est irréversible après la période de rétention. Continuer ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-2"></i> Supprimer
            </button>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Infos Générales -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4 border-bottom border-light pb-2">Informations Générales</h5>
            
            <ul class="list-unstyled mb-0">
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Statut</span>
                    @if($isSuspended)
                        <span class="badge bg-danger">Suspendu le {{ $proprietaire->suspended_at }}</span>
                        <div class="text-danger mt-1" style="font-size: 0.8rem;"><i class="fas fa-info-circle"></i> {{ $proprietaire->suspended_reason }}</div>
                    @else
                        <span class="badge bg-success">Actif</span>
                    @endif
                </li>
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Date d'inscription</span>
                    <div class="fw-bold">{{ $entreprise->created_at->format('d/m/Y H:i') }}</div>
                </li>
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Numéro IFU</span>
                    <div class="fw-bold">{{ $entreprise->ifu ?? 'Non renseigné' }}</div>
                </li>
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Numéro RCCM</span>
                    <div class="fw-bold">{{ $entreprise->rc ?? 'Non renseigné' }}</div>
                </li>
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Adresse</span>
                    <div class="fw-bold">{{ $entreprise->adresse ?? '-' }}<br>{{ $entreprise->ville ?? '-' }}, {{ $entreprise->pays ?? '-' }}</div>
                </li>
                <li>
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Secteur d'activité</span>
                    <div class="fw-bold">{{ $entreprise->secteur ?? 'Non renseigné' }}</div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Contact & Forfait -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4 border-bottom border-light pb-2">Contact & Abonnement</h5>
            
            <ul class="list-unstyled mb-4">
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Email principal</span>
                    <div class="fw-bold">{{ $entreprise->email ?? ($proprietaire ? $proprietaire->email : 'N/A') }}</div>
                </li>
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Téléphone</span>
                    <div class="fw-bold">{{ $entreprise->telephone ?? 'Non renseigné' }}</div>
                </li>
                <li class="mb-3">
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Forfait Actuel</span>
                    <div class="fw-bold text-primary">Prestige (Mock)</div>
                </li>
                <li>
                    <span class="text-muted d-block" style="font-size: 0.8rem;">Prochaine facturation</span>
                    <div class="fw-bold">15/{{ now()->addMonth()->format('m/Y') }}</div>
                </li>
            </ul>
            
            <button class="btn btn-sm btn-outline-warning w-100"><i class="fas fa-edit me-2"></i> Modifier le plan</button>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4 border-bottom border-light pb-2">Statistiques de l'entreprise</h5>
            
            <div class="row g-3 text-center">
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--gel-border);">
                        <div class="fs-3 fw-bold text-primary">{{ $equipe->count() }}</div>
                        <div class="text-muted" style="font-size: 0.8rem;">Membres d'équipe</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--gel-border);">
                        <div class="fs-3 fw-bold text-success">{{ $contacts->count() }}</div>
                        <div class="text-muted" style="font-size: 0.8rem;">Clients finaux</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded" style="background: var(--gel-border);">
                        <div class="fs-3 fw-bold text-warning">{{ $entreprise->cabinets->count() }}</div>
                        <div class="text-muted" style="font-size: 0.8rem;">Cabinets connectés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Equipe List -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4">Équipe de l'entreprise</h5>
            
            <div class="table-responsive">
                <table class="table table table-hover align-middle table-borderless table-sm">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipe as $membre)
                        <tr>
                            <td>{{ $membre->name }}</td>
                            <td>{{ $membre->email }}</td>
                            <td>
                                @if($membre->is_company_admin || $membre->entreprise_id == current(array_filter([$entreprise->id])))
                                    <span class="badge bg-primary opacity-75">Admin</span>
                                @else
                                    <span class="badge bg-secondary opacity-75">Membre</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Aucun membre d'équipe.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4">Dernières actions de l'entreprise</h5>
            
            @forelse($activite as $log)
            <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom border-light" style="border-color: var(--gel-border) !important;">
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-dark mt-1" style="width:32px; height:32px; flex-shrink:0;">
                    <i class="fas fa-history" style="font-size: 0.8rem;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.9rem;">{{ $log->event }}</div>
                    <div class="text-muted" style="font-size: 0.85rem;">{{ $log->description }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                        {{ $log->user->name ?? 'Système' }} &bull; {{ $log->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                Aucune activité récente enregistrée pour cette entreprise.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
