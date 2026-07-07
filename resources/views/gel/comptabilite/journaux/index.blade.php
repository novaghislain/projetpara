@extends('layouts.gel')

@section('title', 'Journaux — GEL Cabinet')

@section('styles')
<style>
    .journal-card { background: white; border-radius: 12px; border: 1px solid var(--gel-border); padding: 1.25rem; transition: all 0.2s; }
    .journal-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); transform: translateY(-2px); }
    .journal-code { font-family:monospace; font-weight:800; font-size:1.5rem; color:var(--gel-accent-2); }
    .stat-mini { font-size:0.8rem; color:var(--gel-text-muted); }
    .stat-mini strong { color:var(--gel-primary); }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Journaux comptables</h1>
        <p class="page-subtitle">Gérez les journaux du cabinet.</p>
    </div>
    <div class="d-flex gap-2">
        @can('comptabilite.creer')
        <a href="{{ route('gel.comptabilite.journaux.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau journal
        </a>
        @endcan
    </div>
</div>

<div class="row g-3">
    @forelse($journaux as $journal)
    <div class="col-md-6 col-lg-4">
        <div class="journal-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="journal-code">{{ $journal->code }}</div>
                <span class="badge {{ $journal->actif ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                    {{ $journal->actif ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            <h5 style="font-weight:700;color:var(--gel-primary);">{{ $journal->libelle }}</h5>
            <div class="d-flex gap-3 mt-3">
                <div class="stat-mini">
                    <i class="bi bi-journal-text"></i>
                    <strong>{{ $journal->ecritures_count }}</strong> écritures
                </div>
                <div class="stat-mini">
                    <i class="bi bi-arrow-up-circle text-success"></i>
                    <strong>{{ number_format((float)($journal->total_debit ?? 0), 0, ',', ' ') }}</strong>
                </div>
                <div class="stat-mini">
                    <i class="bi bi-arrow-down-circle text-danger"></i>
                    <strong>{{ number_format((float)($journal->total_credit ?? 0), 0, ',', ' ') }}</strong>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex gap-2">
                <a href="{{ route('gel.comptabilite.journaux.show', $journal->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> Détail
                </a>
                @can('comptabilite.modifier')
                <a href="{{ route('gel.comptabilite.journaux.edit', $journal->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i>
                </a>
                @endcan
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5" style="color:var(--gel-text-muted);">
            <i class="bi bi-inbox" style="font-size:2rem;"></i>
            <p class="mt-2">Aucun journal trouvé. Créez-en un ou générez les journaux standards.</p>
            @can('comptabilite.creer')
            <form action="{{ route('gel.comptabilite.journaux.create-defaults') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Générer les 8 journaux standards SYSCOHADA ?')">
                    <i class="bi bi-magic"></i> Générer les journaux standards
                </button>
            </form>
            @endcan
        </div>
    </div>
    @endforelse
</div>
@endsection
