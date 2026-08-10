@extends('layouts.gel')

@section('title', 'Balance Générale — GEL Cabinet')

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
        border-bottom: 2px solid #e5e7eb;
        white-space: nowrap;
        background: #f9fafb;
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

    .report-header {
        text-align: center;
        padding: 2.5rem 1rem 1.5rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .report-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .report-subtitle {
        font-size: 1rem;
        color: #4b5563;
    }
    
    .val-debit { color: #03543f; } /* Subtle Green */
    .val-credit { color: #9b1c1c; } /* Subtle Red */
    .val-null { color: #9ca3af; }

    .total-row td {
        background: #f3f4f6;
        font-weight: 700;
        border-top: 2px solid #d1d5db;
        color: #111827;
    }
    
    .table-group-header {
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #f9fafb;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1" style="font-weight: 700; color: #111827;">Balance Générale</h1>
        <p class="text-muted" style="font-size: 0.9rem;">Vue d'ensemble des soldes de tous les comptes.</p>
    </div>
</div>

<div class="qbo-grid-container">
    <form method="GET" id="filter-form">
        <div class="qbo-toolbar flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
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
                    Actualiser
                </button>
                
                @if(request()->anyFilled(['date_debut', 'date_fin', 'exercice_id']))
                    <a href="{{ route('gel.comptabilite.balance.index') }}" class="text-decoration-none" style="color: #6b7280; font-size: 0.8rem; margin-left:10px;">Réinitialiser</a>
                @endif
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="action-btn" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimer
                </button>
            </div>
        </div>
    </form>

    <div class="report-header">
        <div class="report-title">BALANCE GÉNÉRALE DES COMPTES</div>
        @if(request('date_debut') || request('date_fin'))
        <div class="report-subtitle mt-2">
            Période du {{ request('date_debut') ? \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') : 'Début' }} 
            au {{ request('date_fin') ? \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') : 'Fin' }}
        </div>
        @else
        <div class="report-subtitle mt-2">Toutes les périodes</div>
        @endif
    </div>

    <div class="table-responsive">
        <table class="qbo-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 100px; vertical-align: bottom;">COMPTE</th>
                    <th rowspan="2" style="vertical-align: bottom;">INTITULÉ</th>
                    <th colspan="2" class="table-group-header">MOUVEMENTS DE LA PÉRIODE</th>
                    <th colspan="2" class="table-group-header border-start">SOLDES FINAUX</th>
                </tr>
                <tr>
                    <th class="text-end">DÉBIT</th>
                    <th class="text-end">CRÉDIT</th>
                    <th class="text-end border-start">DÉBITEUR</th>
                    <th class="text-end">CRÉDITEUR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balances as $b)
                <tr>
                    <td style="font-family: monospace; font-weight: 600;">{{ $b['code'] }}</td>
                    <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $b['intitule'] }}
                    </td>
                    
                    <!-- Mouvements -->
                    <td class="text-end {{ $b['total_debit'] > 0 ? 'val-debit' : 'val-null' }}" style="font-variant-numeric: tabular-nums;">
                        {{ $b['total_debit'] > 0 ? number_format((float) $b['total_debit'], 0, ',', ' ') : '—' }}
                    </td>
                    <td class="text-end {{ $b['total_credit'] > 0 ? 'val-credit' : 'val-null' }}" style="font-variant-numeric: tabular-nums;">
                        {{ $b['total_credit'] > 0 ? number_format((float) $b['total_credit'], 0, ',', ' ') : '—' }}
                    </td>
                    
                    <!-- Soldes -->
                    <td class="text-end border-start fw-bold {{ $b['solde_debiteur'] > 0 ? 'val-debit' : 'val-null' }}" style="font-variant-numeric: tabular-nums;">
                        {{ $b['solde_debiteur'] > 0 ? number_format((float) $b['solde_debiteur'], 0, ',', ' ') : '—' }}
                    </td>
                    <td class="text-end fw-bold {{ $b['solde_crediteur'] > 0 ? 'val-credit' : 'val-null' }}" style="font-variant-numeric: tabular-nums;">
                        {{ $b['solde_crediteur'] > 0 ? number_format((float) $b['solde_crediteur'], 0, ',', ' ') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-calculator mb-2 d-block" style="font-size: 2rem; color: #9ca3af;"></i>
                        Aucune donnée de balance trouvée pour cette sélection.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-end pe-4">TOTAUX GÉNÉRAUX</td>
                    <td class="text-end" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $totalGeneralDebit, 0, ',', ' ') }}</td>
                    <td class="text-end" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $totalGeneralCredit, 0, ',', ' ') }}</td>
                    
                    <td class="text-end border-start" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $totalGeneralDebit, 0, ',', ' ') }}</td>
                    <td class="text-end" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $totalGeneralCredit, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="text-center py-3" style="background: #ffffff; border-bottom: none;">
                        @if(abs($totalGeneralDebit - $totalGeneralCredit) < 0.01)
                            <div class="d-inline-flex align-items-center px-3 py-2" style="background: #def7ec; color: #03543f; border-radius: 9999px; font-size: 0.85rem; font-weight: 600;">
                                <i class="bi bi-check-circle-fill me-2"></i> Balance parfaitement équilibrée
                            </div>
                        @else
                            <div class="d-inline-flex align-items-center px-3 py-2" style="background: #fde8e8; color: #9b1c1c; border-radius: 9999px; font-size: 0.85rem; font-weight: 600;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Déséquilibre détecté 
                                (Écart: {{ number_format(abs($totalGeneralDebit - $totalGeneralCredit), 0, ',', ' ') }})
                            </div>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
