@extends('layouts.gel-super-admin')

@section('title', 'Gestion des Entreprises')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Entreprises Clientes (Tenants)</h1>
        <p class="page-subtitle">Gérez l'ensemble des cabinets et entreprises inscrits sur GEL SABINET.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success bg-success text-dark border-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger bg-danger text-dark border-0">{{ session('error') }}</div>
@endif

<div class="card shadow-sm border-0 h-100 p-4 mb-4">
    <form action="{{ route('gel-super-admin.tenants.index') }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control border-light" placeholder="Rechercher par nom, email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select border-light">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendus</option>
            </select>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <input type="date" name="date_from" class="form-control border-light" value="{{ request('date_from') }}">
                <span class="input-group-text bg-secondary text-dark border-light">à</span>
                <input type="date" name="date_to" class="form-control border-light" value="{{ request('date_to') }}">
            </div>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn" style="background-color: var(--gel-primary); color: white;">Filtrer</button>
        </div>
    </form>
</div>

<div class="card shadow-sm border-0 h-100 p-4">
    <div class="table-responsive">
        <table class="table table table-hover align-middle table-borderless align-middle">
            <thead>
                <tr>
                    <th>Entreprise</th>
                    <th>Email Contact</th>
                    <th>Téléphone</th>
                    <th>Date d'inscription</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entreprises as $entreprise)
                @php
                    $proprietaire = $entreprise->proprietaires->first();
                    $isSuspended = $proprietaire ? $proprietaire->is_suspended : false;
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold">{{ $entreprise->nom }}</div>
                        <div class="text-muted" style="font-size: 0.8rem;">IFU: {{ $entreprise->ifu ?? 'N/A' }}</div>
                    </td>
                    <td>{{ $entreprise->email ?? ($proprietaire ? $proprietaire->email : 'N/A') }}</td>
                    <td>{{ $entreprise->telephone ?? 'N/A' }}</td>
                    <td>{{ $entreprise->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if($isSuspended)
                            <span class="badge bg-danger">Suspendu</span>
                        @else
                            <span class="badge bg-success">Actif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <a href="{{ route('gel-super-admin.tenants.show', $entreprise->id) }}" class="btn btn-sm btn-outline-secondary" title="Voir les détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if($isSuspended)
                                <form action="{{ route('gel-super-admin.tenants.activate', $entreprise->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Réactiver">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#suspendModal{{ $entreprise->id }}" title="Suspendre">
                                    <i class="fas fa-pause"></i>
                                </button>
                            @endif
                            
                            @if($proprietaire && !$isSuspended)
                            <form action="{{ route('gel-super-admin.tenants.impersonate', $entreprise->id) }}" method="POST" class="d-inline" target="_blank">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info" title="Mode Support (Se connecter en tant que)">
                                    <i class="fas fa-user-secret"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Suspend Modal -->
                @if(!$isSuspended)
                <div class="modal fade" id="suspendModal{{ $entreprise->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog bg-white">
                        <div class="modal-content" style="background-color: var(--gel-bg); border: 1px solid var(--gel-border);">
                            <div class="modal-header border-bottom-0">
                                <h5 class="modal-title">Suspendre {{ $entreprise->nom }}</h5>
                                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('gel-super-admin.tenants.suspend', $entreprise->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Cette action bloquera l'accès à la plateforme pour tous les utilisateurs de cette entreprise.</p>
                                    <div class="mb-3">
                                        <label class="form-label">Motif de la suspension (Obligatoire)</label>
                                        <textarea name="reason" class="form-control border-light" required rows="3" placeholder="Ex: Défaut de paiement, Violation des CGU..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-danger">Confirmer la suspension</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        Aucune entreprise ne correspond à vos critères.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $entreprises->links() }}
    </div>
</div>
@endsection
