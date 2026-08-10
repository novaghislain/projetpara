@extends('layouts.gel')

@section('title', 'Grand Livre — GEL Cabinet')

@section('styles')
<style>
    /* QuickBooks Online Style Overrides */
    .qbo-toolbar {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    
    .qbo-grid-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    
    .qbo-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .qbo-table th {
        text-align: left;
        padding: 0.75rem 1rem;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .qbo-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        vertical-align: middle;
    }
    .qbo-table tbody tr { transition: background-color 0.1s; }
    .qbo-table tbody tr:hover { background-color: #f9fafb; }
    
    .filter-select, .filter-input {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.4rem 0.75rem;
        font-size: 0.85rem;
        color: #374151;
        outline: none;
        background-color: white;
    }
    .filter-select:focus, .filter-input:focus { border-color: #2ca01c; }
    
    .action-btn {
        background: none;
        border: 1px solid #d1d5db;
        color: #374151;
        cursor: pointer;
        padding: 0.4rem 1rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: background 0.15s;
    }
    .action-btn:hover { background: #f3f4f6; }

    .summary-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        flex: 1;
    }
    .summary-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    
    .solde-debiteur { color: #03543f; } /* Dark Green */
    .solde-crediteur { color: #9b1c1c; } /* Dark Red */
    .solde-nul { color: #6b7280; }
    
    .val-debit { color: #374151; }
    .val-credit { color: #374151; }

    .report-header {
        text-align: center;
        padding: 2rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .report-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
    }
    .report-subtitle {
        font-size: 1rem;
        color: #4b5563;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1" style="font-weight: 700; color: #111827;">Grand Livre</h1>
        <p class="text-muted" style="font-size: 0.9rem;">Détail des mouvements par compte comptable.</p>
    </div>
</div>

<div class="qbo-grid-container">
    <form method="GET" id="filter-form">
        <div class="qbo-toolbar flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <select name="compte_id" class="filter-select" style="min-width: 250px;" required>
                    <option value="">Sélectionnez un compte...</option>
                    @foreach($comptes as $c)
                    <option value="{{ $c->id }}" {{ request('compte_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->code }} — {{ $c->intitule }}
                    </option>
                    @endforeach
                </select>

                <select name="exercice_id" class="filter-select">
                    <option value="">Tous les exercices</option>
                    @foreach($exercices as $ex)
                    <option value="{{ $ex->id }}" {{ request('exercice_id') == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                    @endforeach
                </select>

                <input type="date" name="date_debut" class="filter-input" value="{{ request('date_debut') }}" title="Date de début">
                <span class="text-muted">au</span>
                <input type="date" name="date_fin" class="filter-input" value="{{ request('date_fin') }}" title="Date de fin">
                
                <button type="submit" class="action-btn" style="background:#2ca01c; color:white; border-color:#2ca01c;">
                    Exécuter
                </button>
                
                @if(request()->anyFilled(['compte_id', 'date_debut', 'date_fin', 'exercice_id']))
                    <a href="{{ route('gel.comptabilite.grand-livre.index') }}" class="text-decoration-none" style="color: #6b7280; font-size: 0.8rem; margin-left:10px;">Réinitialiser</a>
                @endif
            </div>
            
            <div class="d-flex gap-2">
                @if($compte)
                <button type="button" class="action-btn" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimer
                </button>
                <a href="{{ route('gel.comptabilite.grand-livre.export', request()->query()) }}" class="action-btn text-decoration-none">
                    <i class="bi bi-download me-1"></i> Exporter
                </a>
                @endif
            </div>
        </div>
    </form>

    @if($compte)
    <div class="report-header">
        <div class="report-title">GRAND LIVRE — {{ $compte->code }}</div>
        <div class="report-subtitle">{{ $compte->intitule }} (Classe {{ $compte->classe }})</div>
        @if(request('date_debut') || request('date_fin'))
        <div class="text-muted mt-2" style="font-size:0.85rem;">
            Période du {{ request('date_debut') ? \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') : 'Début' }} 
            au {{ request('date_fin') ? \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') : 'Fin' }}
        </div>
        @endif
    </div>

    <div class="d-flex gap-3 p-4 bg-white border-bottom">
        <div class="summary-card">
            <div class="summary-label">Solde Initial</div>
            <div class="summary-value {{ $soldeInitial > 0 ? 'solde-debiteur' : ($soldeInitial < 0 ? 'solde-crediteur' : 'solde-nul') }}">
                {{ number_format(abs((float) $soldeInitial), 0, ',', ' ') }} <span style="font-size:1rem;">{{ $soldeInitial > 0 ? 'D' : ($soldeInitial < 0 ? 'C' : '') }}</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Mouvements Débit</div>
            <div class="summary-value" style="color:#374151;">
                {{ number_format((float) $totalDebit, 0, ',', ' ') }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Mouvements Crédit</div>
            <div class="summary-value" style="color:#374151;">
                {{ number_format((float) $totalCredit, 0, ',', ' ') }}
            </div>
        </div>
        <div class="summary-card" style="border: 1px solid #2ca01c; background: #f8fdf8;">
            <div class="summary-label" style="color:#2ca01c;">Solde Final</div>
            <div class="summary-value {{ $soldeFinal > 0 ? 'solde-debiteur' : ($soldeFinal < 0 ? 'solde-crediteur' : 'solde-nul') }}">
                {{ number_format(abs((float) $soldeFinal), 0, ',', ' ') }} <span style="font-size:1rem;">{{ $soldeFinal > 0 ? 'D' : ($soldeFinal < 0 ? 'C' : '') }}</span>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="qbo-table">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>N° PIÈCE</th>
                    <th>JOURNAL</th>
                    <th>LIBELLÉ</th>
                    <th class="text-end">DÉBIT</th>
                    <th class="text-end">CRÉDIT</th>
                    <th class="text-end">SOLDE COURANT</th>
                </tr>
            </thead>
            <tbody>
                @php $courant = $soldeInitial; @endphp
                @forelse($lignes as $ligne)
                    @php
                        if($ligne->sens === 'debit') {
                            $courant += $ligne->montant;
                        } else {
                            $courant -= $ligne->montant;
                        }
                    @endphp
                    <tr onclick="window.location='{{ route('gel.comptabilite.ecritures.show', $ligne->ecriture->id) }}'">
                        <td>{{ $ligne->ecriture->date_ecriture->format('d/m/Y') }}</td>
                        <td style="font-family: monospace;">{{ $ligne->ecriture->numero ?? '—' }}</td>
                        <td>{{ $ligne->ecriture->journal?->code ?? '—' }}</td>
                        <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $ligne->libelle_ligne ?? $ligne->ecriture->libelle }}
                        </td>
                        <td class="text-end val-debit" style="font-variant-numeric: tabular-nums;">
                            {{ $ligne->sens === 'debit' ? number_format((float) $ligne->montant, 0, ',', ' ') : '' }}
                        </td>
                        <td class="text-end val-credit" style="font-variant-numeric: tabular-nums;">
                            {{ $ligne->sens === 'credit' ? number_format((float) $ligne->montant, 0, ',', ' ') : '' }}
                        </td>
                        <td class="text-end fw-bold {{ $courant > 0 ? 'solde-debiteur' : ($courant < 0 ? 'solde-crediteur' : 'solde-nul') }}" style="font-variant-numeric: tabular-nums;">
                            {{ number_format(abs((float) $courant), 0, ',', ' ') }}
                            <span style="font-size:0.75rem; font-weight:normal; margin-left:2px;">{{ $courant > 0 ? 'D' : ($courant < 0 ? 'C' : '') }}</span>
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        Aucun mouvement pour ce compte sur la période sélectionnée.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot style="background: #f9fafb; border-top: 2px solid #e5e7eb;">
                <tr>
                    <td colspan="4" class="text-end fw-bold" style="padding: 1rem;">TOTAUX PÉRIODE</td>
                    <td class="text-end fw-bold" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $totalDebit, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $totalCredit, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="text-center py-5">
        <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
        <p class="mt-3 text-muted" style="font-size: 1.1rem;">Sélectionnez un compte pour afficher le Grand Livre.</p>
    </div>
    @endif
</div>
@endsection
