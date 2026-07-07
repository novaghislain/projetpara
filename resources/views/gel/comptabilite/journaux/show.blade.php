@extends('layouts.gel')

@section('title', "Journal {$journal->code} — Comptabilité")

@section('styles')
<style>
    .info-label { font-size:0.8rem; font-weight:600; color:var(--gel-text-muted); text-transform:uppercase; letter-spacing:0.5px; }
    .info-value { font-size:1rem; font-weight:600; color:var(--gel-primary); }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">{{ $journal->code }} — {{ $journal->libelle }}</h1>
        <p class="page-subtitle">Journal {{ $journal->type ?? 'général' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gel.comptabilite.journaux.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        @can('comptabilite.creer')
        <a href="{{ route('gel.comptabilite.ecritures.create') }}?journal_id={{ $journal->id }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle écriture
        </a>
        @endcan
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card-dashboard">
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label">Code</div>
                    <div class="info-value" style="font-family:monospace;">{{ $journal->code }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Libellé</div>
                    <div class="info-value">{{ $journal->libelle }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Statut</div>
                    <div class="info-value">
                        @if($journal->actif) <span class="badge bg-success">Actif</span>
                        @else <span class="badge bg-danger">Inactif</span>
                        @endif
                    </div>
                </div>
                <div class="mb-0">
                    <div class="info-label">Nombre d'écritures</div>
                    <div class="info-value">{{ $journal->ecritures_count ?? $ecritures->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card-dashboard">
            <div class="card-header">Écritures du journal</div>
            <div class="card-body p-0">
                @if($ecritures->isEmpty())
                <div class="text-center py-5" style="color:var(--gel-text-muted);">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <p class="mt-2">Aucune écriture dans ce journal.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="px-3 py-2">Date</th>
                                <th class="py-2">Numéro</th>
                                <th class="py-2">Libellé</th>
                                <th class="py-2">Client</th>
                                <th class="py-2 text-end">Débit</th>
                                <th class="py-2 text-end">Crédit</th>
                                <th class="py-2 text-center">Statut</th>
                                <th class="py-2 text-end px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ecritures as $e)
                            <tr>
                                <td class="px-3 py-2">{{ $e->date_ecriture->format('d/m/Y') }}</td>
                                <td class="py-2" style="font-family:monospace;">{{ $e->numero }}</td>
                                <td class="py-2">{{ \Illuminate\Support\Str::limit($e->libelle, 50) }}</td>
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
                                    <a href="{{ route('gel.comptabilite.ecritures.show', $e->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $ecritures->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
