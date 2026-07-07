@extends('layouts.gel')

@section('title', 'Plan comptable — GEL Cabinet')

@section('styles')
<style>
    .filter-card { background: white; border-radius: 12px; border: 1px solid var(--gel-border); padding: 1.25rem; margin-bottom: 1.5rem; }
    .compte-row { transition: background 0.15s ease; }
    .compte-row:hover { background: #f8f9ff; }
    .badge-classe { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 20px; }
    .classe-1 { background: #e8f4ff; color: #1c7ed6; }
    .classe-2 { background: #f3e8ff; color: #7048e8; }
    .classe-3 { background: #fff3e0; color: #e67700; }
    .classe-4 { background: #e6fcf5; color: #0ca678; }
    .classe-5 { background: #fff0f6; color: #e64980; }
    .classe-6 { background: #ffe0e0; color: #e03131; }
    .classe-7 { background: #e0fce0; color: #2b8a3e; }
    .classe-8 { background: #f1f3f5; color: #495057; }
    .solde-badge { font-size: 0.7rem; padding: 0.1rem 0.4rem; border-radius: 4px; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Plan comptable SYSCOHADA</h1>
        <p class="page-subtitle">Gérez les comptes comptables du cabinet et de vos clients.</p>
    </div>
    <div>
        @can('comptabilite.creer')
        <a href="{{ route('gel.comptabilite.plan-comptable.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau compte
        </a>
        @endcan
    </div>
</div>

{{-- Filtres --}}
<div class="filter-card">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Recherche</label>
            <input type="text" name="search" class="form-control" placeholder="Code ou intitulé..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Classe</label>
            <select name="classe" class="form-select">
                <option value="">Toutes</option>
                <option value="1" {{ request('classe') == '1' ? 'selected' : '' }}>1 — Capitaux</option>
                <option value="2" {{ request('classe') == '2' ? 'selected' : '' }}>2 — Immobilisations</option>
                <option value="3" {{ request('classe') == '3' ? 'selected' : '' }}>3 — Stocks</option>
                <option value="4" {{ request('classe') == '4' ? 'selected' : '' }}>4 — Tiers</option>
                <option value="5" {{ request('classe') == '5' ? 'selected' : '' }}>5 — Trésorerie</option>
                <option value="6" {{ request('classe') == '6' ? 'selected' : '' }}>6 — Charges</option>
                <option value="7" {{ request('classe') == '7' ? 'selected' : '' }}>7 — Produits</option>
                <option value="8" {{ request('classe') == '8' ? 'selected' : '' }}>8 — Résultats</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Statut</label>
            <select name="actif" class="form-select">
                <option value="">Tous</option>
                <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actifs</option>
                <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactifs</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-search"></i> Filtrer
            </button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('gel.comptabilite.plan-comptable.index') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-x-circle"></i> Réinitialiser
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card-dashboard">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:0.85rem;">
                <thead>
                    <tr style="background:#f8f9fa;border-bottom:2px solid var(--gel-border);">
                        <th class="px-3 py-3" style="font-weight:600;">Code</th>
                        <th class="py-3" style="font-weight:600;">Intitulé</th>
                        <th class="py-3" style="font-weight:600;">Classe</th>
                        <th class="py-3" style="font-weight:600;">Niveau</th>
                        <th class="py-3" style="font-weight:600;">Nature</th>
                        <th class="py-3 text-center" style="font-weight:600;">Statut</th>
                        <th class="py-3 text-center" style="font-weight:600;">Écritures</th>
                        <th class="py-3 text-end px-3" style="font-weight:600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comptes as $compte)
                    <tr class="compte-row">
                        <td class="px-3 py-2 fw-bold" style="font-family:monospace;color:var(--gel-accent-2);">
                            {{ $compte->code }}
                        </td>
                        <td class="py-2">
                            <a href="{{ route('gel.comptabilite.plan-comptable.show', $compte->id) }}" class="text-decoration-none" style="color:var(--gel-primary);">
                                {{ $compte->intitule }}
                            </a>
                            @if($compte->parent)
                            <br><small style="color:var(--gel-text-muted);">Parent : {{ $compte->parent->code }} — {{ $compte->parent->intitule }}</small>
                            @endif
                        </td>
                        <td class="py-2">
                            <span class="badge-classe classe-{{ $compte->classe }}">{{ $compte->classe }}</span>
                        </td>
                        <td class="py-2">{{ $compte->niveau }}</td>
                        <td class="py-2">
                            <span class="solde-badge {{ $compte->solde_debiteur ? 'bg-light text-dark' : 'bg-light text-dark' }}">
                                {{ $compte->solde_debiteur ? 'Débiteur' : 'Créditeur' }}
                            </span>
                        </td>
                        <td class="py-2 text-center">
                            @if($compte->actif)
                                <span class="badge bg-success-subtle text-success" style="font-size:0.7rem;">Actif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger" style="font-size:0.7rem;">Inactif</span>
                            @endif
                        </td>
                        <td class="py-2 text-center">
                            <span class="badge bg-secondary-subtle text-secondary">{{ $compte->lignes_ecriture_count ?? 0 }}</span>
                        </td>
                        <td class="py-2 text-end px-3">
                            <a href="{{ route('gel.comptabilite.plan-comptable.show', $compte->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('comptabilite.modifier')
                            <a href="{{ route('gel.comptabilite.plan-comptable.edit', $compte->id) }}" class="btn btn-sm btn-outline-secondary me-1" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('comptabilite.supprimer')
                            <form action="{{ route('gel.comptabilite.plan-comptable.destroy', $compte->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Désactiver ce compte ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Désactiver">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color:var(--gel-text-muted);">
                            <i class="bi bi-inbox" style="font-size:2rem;"></i>
                            <p class="mt-2">Aucun compte trouvé.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-3">
    {{ $comptes->links() }}
</div>
@endsection
