@extends('layouts.gel-accountant')

@section('title', 'Grand Livre - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Grand Livre</h1>
        <p class="gel-page-subtitle">Détail des mouvements par compte</p>
    </div>
    <div style="display:flex;gap:8px;">
        @if(request('compte_id') || request('client_id'))
            <a href="{{ route('gel-accountant.comptabilite.grand-livre.export', request()->query()) }}" class="gel-btn gel-btn-secondary">
                <i class="bi bi-download"></i> Exporter
            </a>
        @endif
    </div>
</div>

{{-- Filtres --}}
<div class="gel-card p-4 mb-4">
    <div class="gel-card-body p-4 mb-4">
        <form method="GET" action="{{ route('gel-accountant.comptabilite.grand-livre') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
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
                <label>Compte</label>
                <select name="compte_id" class="gel-filter-select">
                    <option value="">Tous les comptes</option>
                    @if(isset($comptes) && count($comptes) > 0)
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" {{ request('compte_id') == $compte->id ? 'selected' : '' }}>{{ $compte->code }} — {{ $compte->intitule }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Du</label>
                <input type="date" name="date_from" class="gel-filter-select" value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}">
            </div>
            <div class="gel-filter-group">
                <label>Au</label>
                <input type="date" name="date_to" class="gel-filter-select" value="{{ request('date_to', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm">Afficher</button>
            <a href="{{ route('gel-accountant.comptabilite.grand-livre') }}" class="gel-btn gel-btn-secondary gel-btn-sm">Réinitialiser</a>
        </form>
    </div>
</div>

{{-- Résultats --}}
@if(isset($lignes) && $lignes->count() > 0)
    <div class="gel-card p-4 mb-4">
        <div class="gel-card-body p-4 mb-4">
            <table class="gel-table">
                <thead>
                    <tr>
                        <th style="width:90px;">Date</th>
                        <th style="width:80px;">N°</th>
                        <th>Compte</th>
                        <th>Libellé</th>
                        <th class="gel-text-right" style="width:120px;">Débit</th>
                        <th class="gel-text-right" style="width:120px;">Crédit</th>
                        <th class="gel-text-right" style="width:120px;">Solde</th>
                    </tr>
                </thead>
                <tbody>
                    @php $solde = 0; @endphp
                    @foreach($lignes as $ligne)
                        @php
                            $montant = $ligne->montant;
                            if ($ligne->sens === 'debit') { $solde += $montant; } else { $solde -= $montant; }
                        @endphp
                        <tr>
                            <td>{{ $ligne->ecriture->date_ecriture->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $ligne->ecriture->numero ?? '—' }}</td>
                            <td>
                                <strong>{{ $ligne->compte->code ?? '—' }}</strong>
                                <br><small style="color:var(--gel-text-muted);">{{ $ligne->compte->intitule ?? '' }}</small>
                            </td>
                            <td>{{ $ligne->libelle_ligne ?? $ligne->ecriture->libelle ?? '—' }}</td>
                            <td class="gel-text-right">{{ $ligne->sens === 'debit' ? number_format($montant, 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right">{{ $ligne->sens === 'credit' ? number_format($montant, 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right"><strong>{{ number_format($solde, 0, ',', ' ') }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="gel-card p-4 mb-4">
        <div class="gel-empty">
            <i class="bi bi-book"></i>
            <h3>Aucun mouvement</h3>
            <p>Sélectionnez un compte et une période pour afficher le Grand Livre.</p>
        </div>
    </div>
@endif
@endsection
