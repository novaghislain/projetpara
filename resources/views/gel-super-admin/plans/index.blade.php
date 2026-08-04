@extends('layouts.gel-super-admin')

@section('title', 'Forfaits & Tarifs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Gestion des Forfaits (Plans)</h1>
        <p class="page-subtitle">Configurez les formules d'abonnement proposées aux entreprises clientes.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlanModal">
        <i class="fas fa-plus me-2"></i>Nouveau Forfait
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success bg-success text-dark border-0">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger bg-danger text-dark border-0">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    @forelse($plans as $plan)
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 p-4 text-center position-relative h-100 d-flex flex-column" @if(!$plan->is_active) style="opacity: 0.7;" @endif>
            @if(!$plan->is_active)
                <div class="position-absolute top-0 end-0 m-3 badge bg-danger rounded-pill">Inactif</div>
            @endif
            <h4 class="fw-bold mb-3">{{ $plan->name }}</h4>
            <div class="display-6 fw-bold text-primary mb-3">{{ number_format($plan->price, 0, ',', ' ') }} <small class="fs-6">FCFA/mois</small></div>
            <p class="text-muted" style="font-size: 0.85rem; min-height: 40px;">{{ $plan->description }}</p>
            
            <ul class="list-unstyled text-start mb-4 mx-auto flex-grow-1" style="max-width: 250px; color: var(--gel-text-muted);">
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ $plan->max_users ? $plan->max_users . ' Utilisateurs max' : 'Utilisateurs illimités' }}</li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ $plan->ia_quota }} requêtes IA / mois</li>
            </ul>
            
            <div class="d-flex gap-2 mt-auto">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan->id }}">Modifier</button>
                <form action="{{ route('gel-super-admin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce forfait ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Plan Modal -->
    <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog bg-white">
            <div class="modal-content" style="background-color: var(--gel-bg); border: 1px solid var(--gel-border);">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">Modifier {{ $plan->name }}</h5>
                    <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('gel-super-admin.plans.update', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom du forfait</label>
                            <input type="text" name="name" class="form-control border-light" value="{{ $plan->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control border-light" rows="2">{{ $plan->description }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prix mensuel (FCFA)</label>
                            <input type="number" name="price" class="form-control border-light" value="{{ $plan->price }}" required min="0">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Utilisateurs Max</label>
                                <input type="number" name="max_users" class="form-control border-light" value="{{ $plan->max_users }}" min="1">
                                <small class="text-muted">Vide pour illimité</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quota IA</label>
                                <input type="number" name="ia_quota" class="form-control border-light" value="{{ $plan->ia_quota }}" required min="0">
                            </div>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive{{ $plan->id }}" {{ $plan->is_active ? 'checked' : '' }} value="1">
                            <label class="form-check-label" for="isActive{{ $plan->id }}">Forfait Actif (visible pour les clients)</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card shadow-sm border-0 h-100 p-4 text-center py-5">
            <i class="fas fa-box-open fs-1 text-muted mb-3 opacity-50"></i>
            <h5 class="text-muted">Aucun forfait configuré.</h5>
            <button type="button" class="btn btn-outline-secondary mt-3" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                Créer le premier forfait
            </button>
        </div>
    </div>
    @endforelse
</div>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog bg-white">
        <div class="modal-content" style="background-color: var(--gel-bg); border: 1px solid var(--gel-border);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">Nouveau Forfait</h5>
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('gel-super-admin.plans.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du forfait</label>
                        <input type="text" name="name" class="form-control border-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control border-light" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prix mensuel (FCFA)</label>
                        <input type="number" name="price" class="form-control border-light" required min="0">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Utilisateurs Max</label>
                            <input type="number" name="max_users" class="form-control border-light" min="1">
                            <small class="text-muted">Vide pour illimité</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quota IA</label>
                            <input type="number" name="ia_quota" class="form-control border-light" value="0" required min="0">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveNew" checked value="1">
                        <label class="form-check-label" for="isActiveNew">Forfait Actif</label>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
