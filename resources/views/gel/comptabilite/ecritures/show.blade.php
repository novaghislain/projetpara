@extends('layouts.gel')

@section('title', "Écriture {$ecriture->numero} — Comptabilité")

@section('styles')
<style>
    .info-label { font-size:0.8rem; font-weight:600; color:var(--gel-text-muted); text-transform:uppercase; letter-spacing:0.5px; }
    .info-value { font-size:1rem; font-weight:600; color:var(--gel-primary); }
    .ligne-debit { border-left: 3px solid #0ca678; }
    .ligne-credit { border-left: 3px solid #e03131; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Écriture {{ $ecriture->numero }}</h1>
        <p class="page-subtitle">{{ $ecriture->libelle }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        @if($ecriture->peutEtreModifiee())
            @can('comptabilite.modifier')
            <a href="{{ route('gel.comptabilite.ecritures.edit', $ecriture->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            @endcan
            @can('comptabilite.valider')
            <form action="{{ route('gel.comptabilite.ecritures.valider', $ecriture->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Valider cette écriture ? Cette action est irréversible.');">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Valider
                </button>
            </form>
            @endcan
        @endif
        <a href="{{ route('gel.comptabilite.ecritures.pdf', $ecriture->id) }}" class="btn btn-outline-danger" target="_blank">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-dashboard">
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label">Numéro</div>
                    <div class="info-value" style="font-family:monospace;">{{ $ecriture->numero }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Journal</div>
                    <div class="info-value">{{ $ecriture->journal?->code }} — {{ $ecriture->journal?->libelle }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Exercice</div>
                    <div class="info-value">{{ $ecriture->exercice?->libelle }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Client</div>
                    <div class="info-value">{{ $ecriture->client?->company_name ?? '—' }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Date écriture</div>
                    <div class="info-value">{{ $ecriture->date_ecriture->format('d/m/Y') }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Date pièce</div>
                    <div class="info-value">{{ $ecriture->date_piece->format('d/m/Y') }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Référence</div>
                    <div class="info-value">{{ $ecriture->reference_piece ?? '—' }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Créée par</div>
                    <div class="info-value">{{ $ecriture->createur?->name }}</div>
                </div>
                <div class="mb-0">
                    <div class="info-label">Statut</div>
                    <div class="info-value">
                        @if($ecriture->valide)
                            <span class="badge bg-success">Validée</span>
                            <small class="d-block mt-1 text-muted">
                                par {{ $ecriture->validateur?->name ?? '—' }}
                                le {{ $ecriture->date_validation?->format('d/m/Y H:i') }}
                            </small>
                        @else
                            <span class="badge bg-warning text-dark">Brouillon</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-dashboard">
            <div class="card-header">Lignes d'écriture</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="px-3 py-2">Compte</th>
                                <th class="py-2">Intitulé</th>
                                <th class="py-2">Libellé ligne</th>
                                <th class="py-2 text-end">Débit</th>
                                <th class="py-2 text-end">Crédit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ecriture->lignes as $ligne)
                            <tr class="{{ $ligne->sens === 'debit' ? 'ligne-debit' : 'ligne-credit' }}">
                                <td class="px-3 py-2" style="font-family:monospace;font-weight:600;">
                                    {{ $ligne->compte?->code }}
                                </td>
                                <td class="py-2">{{ $ligne->compte?->intitule }}</td>
                                <td class="py-2" style="color:var(--gel-text-muted);">{{ $ligne->libelle_ligne ?? '—' }}</td>
                                <td class="py-2 text-end fw-bold">
                                    @if($ligne->sens === 'debit')
                                    {{ number_format((float) $ligne->montant, 0, ',', ' ') }}
                                    @endif
                                </td>
                                <td class="py-2 text-end fw-bold">
                                    @if($ligne->sens === 'credit')
                                    {{ number_format((float) $ligne->montant, 0, ',', ' ') }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8f9fa;font-weight:700;">
                                <td colspan="3" class="px-3 py-2 text-end">Totaux</td>
                                <td class="py-2 text-end" style="color:#0ca678;">{{ number_format((float) $ecriture->total_debit, 0, ',', ' ') }}</td>
                                <td class="py-2 text-end" style="color:#e03131;">{{ number_format((float) $ecriture->total_credit, 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
