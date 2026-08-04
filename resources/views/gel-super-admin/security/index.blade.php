@extends('layouts.gel-super-admin')

@section('title', 'Sécurité Globale')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Sécurité & Accès</h1>
        <p class="page-subtitle">Gérez les accès Super Admin et auditez les actions critiques de la plateforme.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success bg-success text-dark border-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger bg-danger text-dark border-0">{{ session('error') }}</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Comptes Super Administrateurs</h5>
                <button class="btn btn-sm btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="fas fa-plus me-1"></i> Nouveau
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table table-hover align-middle table-borderless table-sm">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($superAdmins as $admin)
                        <tr>
                            <td class="align-middle">
                                <div class="fw-bold">{{ $admin->name }}</div>
                            </td>
                            <td class="align-middle">{{ $admin->email }}</td>
                            <td class="align-middle">
                                @if($admin->is_suspended)
                                    <span class="badge bg-danger">Révoqué</span>
                                @else
                                    <span class="badge bg-success">Actif</span>
                                @endif
                            </td>
                            <td class="align-middle text-end">
                                @if(auth()->id() !== $admin->id && !$admin->is_suspended)
                                <form action="{{ route('gel-super-admin.security.revoke', $admin->id) }}" method="POST" onsubmit="return confirm('Révoquer cet administrateur ?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Révoquer l'accès"><i class="fas fa-ban"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4">Configurations 2FA globales</h5>
            <div class="p-3 mb-3" style="background: rgba(245, 158, 11, 0.1); border-left: 3px solid var(--gel-accent); border-radius: 4px;">
                <div class="fw-bold text-warning mb-1"><i class="fas fa-shield-alt me-2"></i>Double Facteur (2FA) Obligatoire</div>
                <div class="text-muted" style="font-size: 0.85rem;">Force tous les administrateurs d'entreprises à utiliser le 2FA.</div>
                <div class="mt-2 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="force2fa" checked disabled>
                    <label class="form-check-label text-dark" for="force2fa">Activé pour les cabinets</label>
                </div>
            </div>
            
            <div class="p-3" style="background: rgba(59, 130, 246, 0.1); border-left: 3px solid #3B82F6; border-radius: 4px;">
                <div class="fw-bold text-info mb-1"><i class="fas fa-lock me-2"></i>Règles de mot de passe</div>
                <div class="text-muted" style="font-size: 0.85rem;">Politique stricte appliquée à tous les utilisateurs.</div>
                <ul class="mt-2 mb-0 text-muted ps-3" style="font-size: 0.8rem;">
                    <li>Minimum 8 caractères</li>
                    <li>Au moins une majuscule et un chiffre</li>
                    <li>Renouvellement tous les 90 jours (Désactivé)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4 text-warning">Dernières Actions Critiques (Audit)</h5>
            @forelse($criticalActions as $log)
            <div class="mb-3 pb-3 border-bottom border-light" style="border-color: var(--gel-border) !important;">
                <div class="fw-bold text-warning" style="font-size: 0.85rem;">{{ $log->event }}</div>
                <div class="text-dark" style="font-size: 0.9rem;">{{ $log->description }}</div>
                <div class="text-muted mt-1" style="font-size: 0.75rem;">
                    Par {{ $log->user->name ?? 'Système' }} &bull; {{ $log->created_at->format('d/m/Y H:i:s') }} &bull; IP: {{ $log->ip_address }}
                </div>
            </div>
            @empty
            <div class="text-muted text-center py-4">Aucune action critique récente.</div>
            @endforelse
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4 text-danger">Tentatives de connexion échouées</h5>
            @forelse($failedLogins as $log)
            <div class="mb-3 pb-3 border-bottom border-light" style="border-color: var(--gel-border) !important;">
                <div class="text-dark" style="font-size: 0.9rem;">Tentative sur : <span class="text-danger">{{ $log->description }}</span></div>
                <div class="text-muted mt-1" style="font-size: 0.75rem;">
                    {{ $log->created_at->format('d/m/Y H:i:s') }} &bull; IP: {{ $log->ip_address }}
                </div>
            </div>
            @empty
            <div class="text-muted text-center py-4">Aucun échec de connexion récent enregistré.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Add Admin -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog bg-white">
        <div class="modal-content" style="background-color: var(--gel-bg); border: 1px solid var(--gel-border);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">Nouveau Super Administrateur</h5>
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('gel-super-admin.security.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="name" class="form-control border-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control border-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control border-light" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-control border-light" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer l'administrateur</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
