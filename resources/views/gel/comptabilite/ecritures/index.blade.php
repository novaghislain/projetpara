@extends('layouts.gel')

@section('title', 'Écritures comptables — GEL Cabinet')

@section('styles')
<style>
    .filter-card { background: white; border-radius: 12px; border: 1px solid var(--gel-border); padding: 1.25rem; margin-bottom: 1.5rem; }
    .ecriture-row { transition: background 0.15s ease; }
    .ecriture-row:hover { background: #f8f9ff; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Écritures comptables</h1>
        <p class="page-subtitle">Saisie, modification et validation des écritures.</p>
    </div>
    <div>
        @can('comptabilite.creer')
        <a href="{{ route('gel.comptabilite.ecritures.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle écriture
        </a>
        @endcan
    </div>
</div>

<div class="filter-card">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Recherche</label>
            <input type="text" name="search" class="form-control" placeholder="Numéro, libellé..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Journal</label>
            <select name="journal_id" class="form-select">
                <option value="">Tous</option>
                @foreach($journaux as $j)
                <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>{{ $j->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Exercice</label>
            <select name="exercice_id" class="form-select">
                <option value="">Tous</option>
                @foreach($exercices as $ex)
                <option value="{{ $ex->id }}" {{ request('exercice_id') == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Statut</label>
            <select name="valide" class="form-select">
                <option value="">Tous</option>
                <option value="0" {{ request('valide') === '0' ? 'selected' : '' }}>Brouillons</option>
                <option value="1" {{ request('valide') === '1' ? 'selected' : '' }}>Validées</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Du</label>
            <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
        </div>
        <div class="col-md-1">
            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Au</label>
            <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
        </div>
    </form>
    <div class="mt-2 d-flex gap-2">
        <button type="submit" form="filter-form" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i> Filtrer</button>
        <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Réinitialiser</a>
        @can('comptabilite.exporter')
        <a href="{{ route('gel.comptabilite.ecritures.export', request()->query()) }}" class="btn btn-sm btn-outline-success ms-auto">
            <i class="bi bi-download"></i> Export CSV
        </a>
        @endcan
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:0.85rem;">
                <thead>
                    <tr style="background:#f8f9fa;border-bottom:2px solid var(--gel-border);">
                        <th class="px-3 py-3" style="font-weight:600;">Date</th>
                        <th class="py-3" style="font-weight:600;">Numéro</th>
                        <th class="py-3" style="font-weight:600;">Journal</th>
                        <th class="py-3" style="font-weight:600;">Libellé</th>
                        <th class="py-3" style="font-weight:600;">Client</th>
                        <th class="py-3 text-end" style="font-weight:600;">Débit</th>
                        <th class="py-3 text-end" style="font-weight:600;">Crédit</th>
                        <th class="py-3 text-center" style="font-weight:600;">Statut</th>
                        <th class="py-3 text-end px-3" style="font-weight:600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ecritures as $e)
                    <tr class="ecriture-row">
                        <td class="px-3 py-2">{{ $e->date_ecriture->format('d/m/Y') }}</td>
                        <td class="py-2 fw-bold" style="font-family:monospace;color:var(--gel-accent-2);">{{ $e->numero }}</td>
                        <td class="py-2"><span class="badge bg-light text-dark">{{ $e->journal?->code }}</span></td>
                        <td class="py-2" style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $e->libelle }}</td>
                        <td class="py-2">{{ $e->client?->company_name ?? '—' }}</td>
                        <td class="py-2 text-end">{{ number_format((float) $e->total_debit, 0, ',', ' ') }}</td>
                        <td class="py-2 text-end">{{ number_format((float) $e->total_credit, 0, ',', ' ') }}</td>
                        <td class="py-2 text-center">
                            @if($e->valide)
                                <span class="badge bg-success-subtle text-success" style="font-size:0.7rem;">Validée</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning" style="font-size:0.7rem;">Brouillon</span>
                            @endif
                        </td>
                        <td class="py-2 text-end px-3">
                            <a href="{{ route('gel.comptabilite.ecritures.show', $e->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(!$e->valide && Auth::user()->can('comptabilite.modifier'))
                            <a href="{{ route('gel.comptabilite.ecritures.edit', $e->id) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5" style="color:var(--gel-text-muted);">
                            <i class="bi bi-inbox" style="font-size:2rem;"></i>
                            <p class="mt-2">Aucune écriture trouvée.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $ecritures->links() }}</div>
@endsection
