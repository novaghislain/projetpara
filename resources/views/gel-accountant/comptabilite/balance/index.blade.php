@extends('layouts.gel-accountant')

@section('title', 'Balance - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Balance</h1>
        <p class="gel-page-subtitle">Balance des comptes — {{ $comptes->count() ?? 0 }} comptes</p>
    </div>
</div>

{{-- Filtres --}}
<div class="gel-card" style="margin-bottom:16px;">
    <div class="gel-card-body">
        <form method="GET" action="{{ route('gel-accountant.comptabilite.balance') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="gel-filter-group">
                <label>Client</label>
                <select name="client_id" class="gel-filter-select">
                    <option value="">Tous</option>
                    @if(isset($clients) && count($clients) > 0)
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Classe</label>
                <select name="classe" class="gel-filter-select">
                    <option value="">Toutes</option>
                    @foreach(range(1,8) as $c)
                        <option value="{{ $c }}" {{ request('classe') == $c ? 'selected' : '' }}>Classe {{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="gel-filter-select" value="{{ request('date_fin', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm">Afficher</button>
            <a href="{{ route('gel-accountant.comptabilite.balance') }}" class="gel-btn gel-btn-secondary gel-btn-sm">Réinitialiser</a>
        </form>
    </div>
</div>

{{-- Totaux --}}
@if(isset($totalDebit) && isset($totalCredit))
    <div style="display:flex;gap:16px;margin-bottom:16px;">
        <div class="gel-stat-card" style="flex:1;">
            <div class="gel-stat-label">Total Débit</div>
            <div class="gel-stat-value" style="font-size:20px;">{{ number_format($totalDebit, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="gel-stat-card" style="flex:1;">
            <div class="gel-stat-label">Total Crédit</div>
            <div class="gel-stat-value" style="font-size:20px;">{{ number_format($totalCredit, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="gel-stat-card" style="flex:1;">
            <div class="gel-stat-label">Équilibre</div>
            <div class="gel-stat-value" style="font-size:16px;">
                @if($totalDebit === $totalCredit)
                    <span style="color:var(--gel-success);"><i class="bi bi-check-circle-fill"></i> Balance équilibrée</span>
                @else
                    <span style="color:var(--gel-danger);"><i class="bi bi-exclamation-circle-fill"></i> Différence: {{ number_format(abs($totalDebit - $totalCredit), 0, ',', ' ') }} FCFA</span>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Tableau de balance --}}
<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        @if(isset($balanceData) && count($balanceData) > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th style="width:80px;">Code</th>
                        <th>Compte</th>
                        <th class="gel-text-right" style="width:120px;">Débit</th>
                        <th class="gel-text-right" style="width:120px;">Crédit</th>
                        <th class="gel-text-right" style="width:120px;">Solde Déb.</th>
                        <th class="gel-text-right" style="width:120px;">Solde Créd.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($balanceData as $row)
                        <tr>
                            <td><strong>{{ $row['code'] }}</strong></td>
                            <td>{{ $row['intitule'] }}</td>
                            <td class="gel-text-right">{{ $row['total_debit'] > 0 ? number_format($row['total_debit'], 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right">{{ $row['total_credit'] > 0 ? number_format($row['total_credit'], 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right">{{ $row['solde_debit'] > 0 ? number_format($row['solde_debit'], 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right">{{ $row['solde_credit'] > 0 ? number_format($row['solde_credit'], 0, ',', ' ') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight:700;background:var(--gel-body-bg);">
                        <td colspan="2">TOTAUX</td>
                        <td class="gel-text-right">{{ number_format($totalDebit ?? 0, 0, ',', ' ') }}</td>
                        <td class="gel-text-right">{{ number_format($totalCredit ?? 0, 0, ',', ' ') }}</td>
                        <td class="gel-text-right">{{ number_format($totalSoldeDebit ?? 0, 0, ',', ' ') }}</td>
                        <td class="gel-text-right">{{ number_format($totalSoldeCredit ?? 0, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <div class="gel-empty">
                <i class="bi bi-table"></i>
                <h3>Aucune donnée</h3>
                <p>La balance est vide pour la période sélectionnée.</p>
            </div>
        @endif
    </div>
</div>
@endsection
