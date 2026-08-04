@php $currentSection = 'balance'; @endphp
@extends('layouts.gel-business')

@section('title', 'Balance - Mon Entreprise')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Balance des comptes</h1>
        <p class="gel-page-subtitle">Balance comptable de votre entreprise</p>
    </div>
</div>

<div class="gel-card" style="margin-bottom:16px;">
    <div class="gel-card-body">
        <form method="GET" action="{{ route('gel-business.comptabilite.balance') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="gel-filter-group">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="gel-filter-select" value="{{ request('date_fin', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm">Actualiser</button>
        </form>
    </div>
</div>

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
    </div>
@endif

<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        @if(isset($balanceData) && count($balanceData) > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Compte</th>
                        <th class="gel-text-right">Débit</th>
                        <th class="gel-text-right">Crédit</th>
                        <th class="gel-text-right">Solde</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($balanceData as $row)
                        <tr>
                            <td><strong>{{ $row['code'] }}</strong></td>
                            <td>{{ $row['intitule'] }}</td>
                            <td class="gel-text-right">{{ $row['total_debit'] > 0 ? number_format($row['total_debit'], 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right">{{ $row['total_credit'] > 0 ? number_format($row['total_credit'], 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right"><strong>{{ number_format($row['solde_debit'] - $row['solde_credit'], 0, ',', ' ') }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
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
