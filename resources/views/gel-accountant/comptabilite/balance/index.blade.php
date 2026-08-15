@extends('layouts.gel-accountant')

@section('title', 'Balance Générale')

@push('styles')
<style>
/* ==========================================================================
   BALANCE GENERALE - BENTO GRID & REPORT DESIGN
   ========================================================================== */
.report-container {
    background: white;
    border: 1px solid var(--gel-border);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    margin-bottom: 24px;
}

.filters-bar {
    padding: 16px 20px;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    gap: 16px;
    align-items: center;
    background: #F8FAFC;
    flex-wrap: wrap;
}

.form-select-sm, .form-control-sm {
    border: 1px solid #E2E8F0;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 13px;
    color: #475569;
    outline: none;
    min-width: 140px;
}
.form-select-sm:focus, .form-control-sm:focus { border-color: var(--gel-primary); }

.gel-table {
    width: 100%; border-collapse: collapse;
}
.gel-table th {
    background: white; padding: 12px 20px; font-size: 11px;
    font-weight: 700; color: #64748B; text-transform: uppercase;
    letter-spacing: 0.5px; border-bottom: 2px solid #E2E8F0;
    text-align: center;
}
.gel-table th.left-align { text-align: left; }
.gel-table td {
    padding: 10px 20px; border-bottom: 1px solid #F1F5F9;
    font-size: 13px; color: #334155; vertical-align: middle;
}
.gel-table tr:hover { background: #F8FAFC; }

.header-group th {
    border-bottom: 1px solid #E2E8F0;
    background: #F8FAFC;
    padding: 8px 20px;
}

.amount-col { font-family: monospace; text-align: right; }
.total-val { font-weight: 600; color: #1E293B; }
.solde-val { font-weight: 700; color: var(--gel-primary); }

.table-totals {
    background: #F8FAFC;
    font-weight: 700;
    border-top: 2px solid #E2E8F0;
    border-bottom: 4px solid #E2E8F0;
}

.btn-export {
    background: white; color: #475569; border: 1px solid #E2E8F0;
    padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.2s; cursor: pointer;
}
.btn-export:hover { background: #F8FAFC; color: var(--gel-primary); border-color: #CBD5E1; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Balance Générale</h1>
        <div class="gel-page-subtitle">Visualisez les totaux (débit/crédit) et les soldes de chaque compte.</div>
    </div>
    <div style="display:flex; gap:12px;">
        <button class="btn-export" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
    </div>
</div>

<div class="report-container">
    <form action="{{ route('gel-accountant.comptabilite.balance') }}" method="GET" class="filters-bar" id="balanceFilters">
        <div style="font-weight:600; font-size:12px; color:#64748B; text-transform:uppercase;">Filtres :</div>
        
        <select name="client_id" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les dossiers</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
            @endforeach
        </select>
        
        <select name="classe" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Toutes les classes</option>
            @for($i = 1; $i <= 8; $i++)
                <option value="{{ $i }}" {{ request('classe') == $i ? 'selected' : '' }}>Classe {{ $i }}</option>
            @endfor
        </select>

        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:12px; color:#64748B; font-weight:600;">Arrêté au :</span>
            <input type="date" name="date_fin" class="form-control-sm" value="{{ request('date_fin', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
        </div>

        @if(request()->anyFilled(['client_id', 'classe']))
            <a href="{{ route('gel-accountant.comptabilite.balance') }}" class="text-danger" style="font-size:12px; text-decoration:none; margin-left:auto;"><i class="fas fa-times"></i> Réinitialiser</a>
        @endif
    </form>

    @if(empty($balanceData))
        <div class="text-center" style="padding: 60px 20px; color: #94A3B8;">
            <i class="fas fa-balance-scale" style="font-size: 40px; margin-bottom: 16px; opacity:0.5;"></i>
            <div style="font-size:15px; font-weight:600; color:#475569;">Aucune donnée pour cette balance</div>
            <div style="font-size:13px; margin-top:4px;">Modifiez vos filtres ou validez vos écritures.</div>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="gel-table">
                <thead>
                    <tr class="header-group">
                        <th colspan="2">Compte</th>
                        <th colspan="2" style="border-left: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0;">Mouvements</th>
                        <th colspan="2">Soldes</th>
                    </tr>
                    <tr>
                        <th class="left-align" style="width:10%;">N° Compte</th>
                        <th class="left-align" style="width:30%;">Intitulé</th>
                        <th class="text-end" style="width:15%; border-left: 1px solid #E2E8F0;">Débit (F)</th>
                        <th class="text-end" style="width:15%; border-right: 1px solid #E2E8F0;">Crédit (F)</th>
                        <th class="text-end" style="width:15%;">Débiteur (F)</th>
                        <th class="text-end" style="width:15%;">Créditeur (F)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($balanceData as $row)
                        <tr>
                            <td style="font-weight:600; color:var(--gel-primary);">{{ $row['code'] }}</td>
                            <td>{{ $row['intitule'] }}</td>
                            <td class="amount-col total-val" style="border-left: 1px solid #F1F5F9;">{{ $row['total_debit'] > 0 ? number_format($row['total_debit'], 0, ',', ' ') : '-' }}</td>
                            <td class="amount-col total-val" style="border-right: 1px solid #F1F5F9;">{{ $row['total_credit'] > 0 ? number_format($row['total_credit'], 0, ',', ' ') : '-' }}</td>
                            <td class="amount-col solde-val">{{ $row['solde_debit'] > 0 ? number_format($row['solde_debit'], 0, ',', ' ') : '-' }}</td>
                            <td class="amount-col solde-val">{{ $row['solde_credit'] > 0 ? number_format($row['solde_credit'], 0, ',', ' ') : '-' }}</td>
                        </tr>
                    @endforeach
                    
                    <tr class="table-totals">
                        <td colspan="2" class="text-end" style="padding:16px 20px;">TOTAUX GÉNÉRAUX :</td>
                        <td class="amount-col total-val" style="border-left: 1px solid #E2E8F0; font-size:15px; color:#1E293B;">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                        <td class="amount-col total-val" style="border-right: 1px solid #E2E8F0; font-size:15px; color:#1E293B;">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                        <td class="amount-col solde-val" style="font-size:15px;">{{ number_format($totalSoldeDebit, 0, ',', ' ') }}</td>
                        <td class="amount-col solde-val" style="font-size:15px;">{{ number_format($totalSoldeCredit, 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
