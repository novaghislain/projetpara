@extends('layouts.gel')

@section('title', 'Grand Livre — GEL Cabinet')

@section('styles')
<style>
    .solde-debiteur { color: #0ca678; font-weight: 600; }
    .solde-crediteur { color: #e03131; font-weight: 600; }
    .solde-nul { color: var(--gel-text-muted); }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Grand Livre</h1>
        <p class="page-subtitle">Consultation détaillée des mouvements par compte.</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Compte</label>
                <select name="compte_id" class="form-select">
                    <option value="">Tous les comptes</option>
                    @foreach($comptes as $c)
                    <option value="{{ $c->id }}" {{ request('compte_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->code }} — {{ $c->intitule }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Exercice</label>
                <select name="exercice_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($exercices as $ex)
                    <option value="{{ $ex->id }}" {{ request('exercice_id') == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Du</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Au</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label fw-semibold">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

@if(request('compte_id'))
    <div class="card-dashboard">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <strong>{{ $compteSelectionne->code }}</strong>
                — {{ $compteSelectionne->intitule }}
            </span>
            <span class="badge bg-secondary">Classe {{ $compteSelectionne->classeNumero }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:0.85rem;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th class="px-3 py-2">Date</th>
                            <th class="py-2">N° Écriture</th>
                            <th class="py-2">Libellé</th>
                            <th class="py-2">Journal</th>
                            <th class="py-2 text-end">Débit</th>
                            <th class="py-2 text-end">Crédit</th>
                            <th class="py-2 text-end">Solde cumulé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mouvements as $mvt)
                        <tr>
                            <td class="px-3 py-1">{{ $mvt->date_ecriture->format('d/m/Y') }}</td>
                            <td class="py-1" style="font-family:monospace;">{{ $mvt->numero }}</td>
                            <td class="py-1">{{ $mvt->libelle_ligne ?? $mvt->libelle }}</td>
                            <td class="py-1">{{ $mvt->journal?->code ?? '—' }}</td>
                            <td class="py-1 text-end">{{ $mvt->sens === 'debit' ? number_format((float) $mvt->montant, 0, ',', ' ') : '—' }}</td>
                            <td class="py-1 text-end">{{ $mvt->sens === 'credit' ? number_format((float) $mvt->montant, 0, ',', ' ') : '—' }}</td>
                            <td class="py-1 text-end fw-bold {{ $mvt->solde_cumule > 0 ? 'solde-debiteur' : ($mvt->solde_cumule < 0 ? 'solde-crediteur' : 'solde-nul') }}">
                                {{ number_format(abs((float) $mvt->solde_cumule), 0, ',', ' ') }}
                                {{ $mvt->solde_cumule > 0 ? 'D' : ($mvt->solde_cumule < 0 ? 'C' : '') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucun mouvement pour cette période.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8f9fa;font-weight:700;">
                            <td colspan="4" class="px-3 py-2 text-end">Totaux</td>
                            <td class="py-2 text-end" style="color:#0ca678;">{{ number_format((float) $totalDebit, 0, ',', ' ') }}</td>
                            <td class="py-2 text-end" style="color:#e03131;">{{ number_format((float) $totalCredit, 0, ',', ' ') }}</td>
                            <td class="py-2 text-end">
                                <span class="{{ $soldeFinal > 0 ? 'solde-debiteur' : ($soldeFinal < 0 ? 'solde-crediteur' : 'solde-nul') }}">
                                    {{ number_format(abs((float) $soldeFinal), 0, ',', ' ') }}
                                    {{ $soldeFinal > 0 ? 'D' : ($soldeFinal < 0 ? 'C' : '') }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <div class="card-dashboard text-center p-3">
                <div class="text-muted small">Solde initial</div>
                <div class="fw-bold fs-5 {{ $soldeInitial > 0 ? 'solde-debiteur' : ($soldeInitial < 0 ? 'solde-crediteur' : '') }}">
                    {{ number_format(abs((float) $soldeInitial), 0, ',', ' ') }}
                    {{ $soldeInitial > 0 ? 'Débiteur' : ($soldeInitial < 0 ? 'Créditeur' : 'Nul') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-dashboard text-center p-3">
                <div class="text-muted small">Solde final</div>
                <div class="fw-bold fs-5 {{ $soldeFinal > 0 ? 'solde-debiteur' : ($soldeFinal < 0 ? 'solde-crediteur' : '') }}">
                    {{ number_format(abs((float) $soldeFinal), 0, ',', ' ') }}
                    {{ $soldeFinal > 0 ? 'Débiteur' : ($soldeFinal < 0 ? 'Créditeur' : 'Nul') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-dashboard text-center p-3">
                <div class="text-muted small">Nombre de mouvements</div>
                <div class="fw-bold fs-5">{{ $mouvements->count() }}</div>
            </div>
        </div>
    </div>
@else
    <div class="card-dashboard">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-book" style="font-size:3rem;"></i>
            <p class="mt-3 fs-5">Sélectionnez un compte pour afficher le Grand Livre.</p>
        </div>
    </div>
@endif
@endsection
