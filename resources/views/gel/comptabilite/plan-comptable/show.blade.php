@extends('layouts.gel')

@section('title', "Compte {$compte->code} — Plan comptable")

@section('styles')
<style>
    .info-label { font-size:0.8rem; font-weight:600; color:var(--gel-text-muted); text-transform:uppercase; letter-spacing:0.5px; }
    .info-value { font-size:1rem; font-weight:600; color:var(--gel-primary); }
    .solde-positif { color: #0ca678; font-weight:700; }
    .solde-negatif { color: #e03131; font-weight:700; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">{{ $compte->code }} — {{ $compte->intitule }}</h1>
        <p class="page-subtitle">Détail du compte comptable.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gel.comptabilite.plan-comptable.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        @can('comptabilite.modifier')
        <a href="{{ route('gel.comptabilite.plan-comptable.edit', $compte->id) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        @endcan
    </div>
</div>

<div class="row g-4">
    {{-- Infos compte --}}
    <div class="col-lg-4">
        <div class="card-dashboard">
            <div class="card-header">Informations</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label">Code</div>
                    <div class="info-value" style="font-family:monospace;">{{ $compte->code }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Intitulé</div>
                    <div class="info-value">{{ $compte->intitule }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Classe</div>
                    <div class="info-value">{{ $compte->classe }} — {{ $compte->classe_libelle }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Niveau</div>
                    <div class="info-value">{{ $compte->niveau }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Nature</div>
                    <div class="info-value">{{ $compte->solde ? 'Débiteur' : 'Créditeur' }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Statut</div>
                    <div class="info-value">
                        @if($compte->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </div>
                </div>
                @if($compte->parent)
                <div class="mb-3">
                    <div class="info-label">Compte parent</div>
                    <div class="info-value">
                        <a href="{{ route('gel.comptabilite.plan-comptable.show', $compte->parent->id) }}" class="text-decoration-none">
                            {{ $compte->parent->code }} — {{ $compte->parent->intitule }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Solde --}}
        <div class="card-dashboard mt-3">
            <div class="card-header">Solde</div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="info-label">Total Débit</div>
                    <div class="info-value solde-positif">{{ number_format($totalDebit ?? 0, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="mb-2">
                    <div class="info-label">Total Crédit</div>
                    <div class="info-value" style="color:var(--gel-text-muted);">{{ number_format($totalCredit ?? 0, 0, ',', ' ') }} FCFA</div>
                </div>
                <hr>
                <div class="mb-2">
                    <div class="info-label">Solde</div>
                    <div class="info-value {{ ($solde ?? 0) >= 0 ? 'solde-positif' : 'solde-negatif' }}">
                        {{ number_format(abs($solde ?? 0), 0, ',', ' ') }} FCFA
                        <small style="font-weight:400;">{{ ($solde ?? 0) >= 0 ? '(débiteur)' : '(créditeur)' }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sous-comptes --}}
        @if($compte->enfants->isNotEmpty())
        <div class="card-dashboard mt-3">
            <div class="card-header">Sous-comptes ({{ $compte->enfants->count() }})</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($compte->enfants as $enfant)
                    <li class="list-group-item" style="font-size:0.85rem;">
                        <a href="{{ route('gel.comptabilite.plan-comptable.show', $enfant->id) }}" class="text-decoration-none">
                            {{ $enfant->code }} — {{ $enfant->intitule }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    {{-- Écritures --}}
    <div class="col-lg-8">
        <div class="card-dashboard">
            <div class="card-header">Écritures comptables</div>
            <div class="card-body p-0">
                @if($compte->lignesEcriture->isEmpty())
                <div class="text-center py-5" style="color:var(--gel-text-muted);">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <p class="mt-2">Aucune écriture pour ce compte.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="px-3 py-2">Date</th>
                                <th class="py-2">Numéro</th>
                                <th class="py-2">Libellé</th>
                                <th class="py-2 text-end">Débit</th>
                                <th class="py-2 text-end">Crédit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compte->lignesEcriture->sortByDesc('ecriture.date_ecriture') as $ligne)
                            <tr>
                                <td class="px-3 py-2">{{ $ligne->ecriture?->date_ecriture?->format('d/m/Y') }}</td>
                                <td class="py-2" style="font-family:monospace;">{{ $ligne->ecriture?->numero }}</td>
                                <td class="py-2">{{ $ligne->libelle_ligne ?? $ligne->ecriture?->libelle }}</td>
                                <td class="py-2 text-end">
                                    @if($ligne->sens === 'debit')
                                    {{ number_format((float) $ligne->montant, 0, ',', ' ') }}
                                    @endif
                                </td>
                                <td class="py-2 text-end">
                                    @if($ligne->sens === 'credit')
                                    {{ number_format((float) $ligne->montant, 0, ',', ' ') }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
