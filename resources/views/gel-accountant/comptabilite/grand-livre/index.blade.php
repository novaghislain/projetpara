@extends('layouts.gel-accountant')

@section('title', 'Grand Livre')

@push('styles')
<style>
/* ==========================================================================
   GRAND LIVRE - BENTO GRID & REPORT DESIGN
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

.account-header {
    background: #F1F5F9;
    padding: 12px 20px;
    border-bottom: 1px solid #E2E8F0;
    font-weight: 700;
    font-size: 14px;
    color: #1E293B;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.gel-table {
    width: 100%; border-collapse: collapse;
}
.gel-table th {
    background: white; padding: 10px 20px; font-size: 11px;
    font-weight: 700; color: #64748B; text-transform: uppercase;
    letter-spacing: 0.5px; border-bottom: 2px solid #E2E8F0;
}
.gel-table td {
    padding: 10px 20px; border-bottom: 1px solid #F1F5F9;
    font-size: 13px; color: #334155; vertical-align: middle;
}
.gel-table tr:hover { background: #F8FAFC; }

.amount-col { font-family: monospace; text-align: right; }
.debit-val { color: #1E293B; }
.credit-val { color: #1E293B; }
.solde-val { font-weight: 700; color: var(--gel-primary); }

.account-totals {
    background: #F8FAFC;
    font-weight: 700;
    border-top: 2px solid #E2E8F0;
    border-bottom: 4px solid #E2E8F0;
}

.btn-export {
    background: white; color: #475569; border: 1px solid #E2E8F0;
    padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    transition: all 0.2s; cursor: pointer;
}
.btn-export:hover { background: #F8FAFC; color: var(--gel-primary); border-color: #CBD5E1; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Grand Livre</h1>
        <div class="gel-page-subtitle">Consultez l'historique des mouvements par compte comptable (écritures validées).</div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger d-flex align-items-center" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; font-size:14px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-exclamation-circle me-2" style="font-size:18px;"></i>
    {{ session('error') }}
</div>
@endif

<div class="report-container">
    <form action="{{ route('gel-accountant.comptabilite.grand-livre') }}" method="GET" class="filters-bar" id="glFilters">
        <div style="font-weight:600; font-size:12px; color:#64748B; text-transform:uppercase;">Filtres :</div>
        
        <select name="client_id" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les dossiers</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
            @endforeach
        </select>
        
        <select name="compte_id" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les comptes</option>
            @foreach($comptes as $c)
                <option value="{{ $c->id }}" {{ request('compte_id') == $c->id ? 'selected' : '' }}>{{ $c->code }} - {{ $c->intitule }}</option>
            @endforeach
        </select>

        <input type="date" name="date_from" class="form-control-sm" value="{{ request('date_from') }}" onchange="this.form.submit()" placeholder="Du">
        <input type="date" name="date_to" class="form-control-sm" value="{{ request('date_to') }}" onchange="this.form.submit()" placeholder="Au">

        @if(request()->anyFilled(['client_id', 'compte_id', 'date_from', 'date_to']))
            <a href="{{ route('gel-accountant.comptabilite.grand-livre') }}" class="text-danger" style="font-size:12px; text-decoration:none; margin-left:auto;"><i class="fas fa-times"></i> Réinitialiser</a>
        @endif
    </form>

    @if($lignesGroupees->isEmpty())
        <div class="text-center" style="padding: 60px 20px; color: #94A3B8;">
            <i class="fas fa-book-open" style="font-size: 40px; margin-bottom: 16px; opacity:0.5;"></i>
            <div style="font-size:15px; font-weight:600; color:#475569;">Aucun mouvement validé trouvé</div>
            <div style="font-size:13px; margin-top:4px;">Modifiez vos filtres ou validez vos écritures pour les voir apparaître ici.</div>
        </div>
    @else
        <div style="overflow-x: auto;">
            @foreach($lignesGroupees as $compteId => $lignes)
                @php 
                    $compte = $lignes->first()->compte; 
                    $solde = 0;
                    $totalD = 0;
                    $totalC = 0;
                @endphp
                <div class="account-header">
                    <div>
                        <i class="fas fa-folder-open me-2 text-primary"></i> 
                        Compte {{ $compte->code }} — {{ $compte->intitule }}
                    </div>
                    <form action="{{ route('gel-accountant.comptabilite.grand-livre.export') }}" method="GET" style="margin:0;">
                        <input type="hidden" name="compte_id" value="{{ $compte->id }}">
                        <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        <button type="submit" class="btn-export" title="Exporter ce compte en CSV"><i class="fas fa-file-export"></i> Exporter CSV</button>
                    </form>
                </div>
                <table class="gel-table mb-4">
                    <thead>
                        <tr>
                            <th style="width:10%;">Date</th>
                            <th style="width:12%;">N° Pièce</th>
                            <th style="width:38%;">Libellé</th>
                            <th class="text-end" style="width:12%;">Débit (F)</th>
                            <th class="text-end" style="width:12%;">Crédit (F)</th>
                            <th class="text-end" style="width:16%;">Solde (F)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lignes as $ligne)
                            @php
                                $montant = (float) $ligne->montant;
                                if($ligne->sens === 'debit') {
                                    $solde += $montant;
                                    $totalD += $montant;
                                } else {
                                    $solde -= $montant;
                                    $totalC += $montant;
                                }
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ligne->ecriture->date_ecriture)->format('d/m/Y') }}</td>
                                <td style="font-weight:600;">
                                    <a href="{{ route('gel-accountant.comptabilite.ecritures.show', $ligne->ecriture_id) }}" style="color:var(--gel-primary); text-decoration:none;">
                                        {{ $ligne->ecriture->numero }}
                                    </a>
                                </td>
                                <td>{{ $ligne->libelle_ligne ?? $ligne->ecriture->libelle }}</td>
                                <td class="amount-col debit-val">{{ $ligne->sens === 'debit' ? number_format($montant, 0, ',', ' ') : '' }}</td>
                                <td class="amount-col credit-val">{{ $ligne->sens === 'credit' ? number_format($montant, 0, ',', ' ') : '' }}</td>
                                <td class="amount-col solde-val">{{ number_format($solde, 0, ',', ' ') }} {{ $solde >= 0 ? 'D' : 'C' }}</td>
                            </tr>
                        @endforeach
                        <tr class="account-totals">
                            <td colspan="3" class="text-end">TOTAUX COMPTE {{ $compte->code }} :</td>
                            <td class="amount-col text-primary" style="font-size:14px;">{{ number_format($totalD, 0, ',', ' ') }}</td>
                            <td class="amount-col text-primary" style="font-size:14px;">{{ number_format($totalC, 0, ',', ' ') }}</td>
                            <td class="amount-col solde-val" style="font-size:14px;">
                                Solde : {{ number_format(abs($solde), 0, ',', ' ') }} {{ $solde >= 0 ? 'Débiteur' : 'Créditeur' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        </div>
    @endif
</div>
@endsection
