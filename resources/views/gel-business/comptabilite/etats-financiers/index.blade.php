@php $currentSection = 'etats-financiers'; @endphp
@extends('layouts.gel-business')

@section('title', 'États financiers - Mon Entreprise')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">États financiers</h1>
        <p class="gel-page-subtitle">Bilan et compte de résultat de votre entreprise</p>
    </div>
</div>

<div class="gel-card" style="margin-bottom:16px;">
    <div class="gel-card-body">
        <form method="GET" action="{{ route('gel-business.comptabilite.etats-financiers') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="gel-filter-group">
                <label>Type</label>
                <select name="type" class="gel-filter-select" onchange="this.form.submit()">
                    <option value="bilan" {{ request('type', 'bilan') === 'bilan' ? 'selected' : '' }}>Bilan</option>
                    <option value="resultat" {{ request('type') === 'resultat' ? 'selected' : '' }}>Compte de résultat</option>
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Date arrêtée</label>
                <input type="date" name="date_fin" class="gel-filter-select" value="{{ request('date_fin', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm">Générer</button>
        </form>
    </div>
</div>

<div class="gel-card">
    <div class="gel-card-header">
        <strong>
            @if(request('type', 'bilan') === 'bilan')
                Bilan comptable
            @else
                Compte de résultat
            @endif
        </strong>
    </div>
    <div class="gel-card-body">
        @if(request('type', 'bilan') === 'bilan')
            @if(isset($actif) && count($actif) > 0)
                <h5 style="margin-bottom:12px;color:var(--gel-primary);">ACTIF</h5>
                <table class="gel-table" style="margin-bottom:24px;">
                    <thead>
                        <tr><th>Compte</th><th>Intitulé</th><th class="gel-text-right">Net</th></tr>
                    </thead>
                    <tbody>
                        @foreach($actif as $ligne)
                            <tr>
                                <td>{{ $ligne['code'] ?? '—' }}</td>
                                <td>{{ $ligne['intitule'] ?? '—' }}</td>
                                <td class="gel-text-right"><strong>{{ number_format($ligne['net'] ?? 0, 0, ',', ' ') }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if(isset($passif) && count($passif) > 0)
                <h5 style="margin-bottom:12px;color:var(--gel-success);">PASSIF</h5>
                <table class="gel-table">
                    <thead>
                        <tr><th>Compte</th><th>Intitulé</th><th class="gel-text-right">Montant</th></tr>
                    </thead>
                    <tbody>
                        @foreach($passif as $ligne)
                            <tr>
                                <td>{{ $ligne['code'] ?? '—' }}</td>
                                <td>{{ $ligne['intitule'] ?? '—' }}</td>
                                <td class="gel-text-right"><strong>{{ number_format($ligne['montant'] ?? 0, 0, ',', ' ') }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if(!isset($actif) || count($actif) === 0)
                <div class="gel-empty">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <h3>Bilan non disponible</h3>
                    <p>Les données ne sont pas encore disponibles.</p>
                </div>
            @endif
        @else
            @if(isset($produits) && count($produits) > 0)
                <h5 style="margin-bottom:12px;color:var(--gel-success);">PRODUITS</h5>
                <table class="gel-table" style="margin-bottom:24px;">
                    <thead><tr><th>Compte</th><th>Intitulé</th><th class="gel-text-right">Montant</th></tr></thead>
                    <tbody>
                        @foreach($produits as $ligne)
                            <tr>
                                <td>{{ $ligne['code'] ?? '—' }}</td>
                                <td>{{ $ligne['intitule'] ?? '—' }}</td>
                                <td class="gel-text-right">{{ number_format($ligne['montant'] ?? 0, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if(isset($charges) && count($charges) > 0)
                <h5 style="margin-bottom:12px;color:var(--gel-danger);">CHARGES</h5>
                <table class="gel-table" style="margin-bottom:24px;">
                    <thead><tr><th>Compte</th><th>Intitulé</th><th class="gel-text-right">Montant</th></tr></thead>
                    <tbody>
                        @foreach($charges as $ligne)
                            <tr>
                                <td>{{ $ligne['code'] ?? '—' }}</td>
                                <td>{{ $ligne['intitule'] ?? '—' }}</td>
                                <td class="gel-text-right">{{ number_format($ligne['montant'] ?? 0, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if(!isset($produits) || count($produits) === 0)
                <div class="gel-empty">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <h3>Résultat non disponible</h3>
                    <p>Les données ne sont pas encore disponibles.</p>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
