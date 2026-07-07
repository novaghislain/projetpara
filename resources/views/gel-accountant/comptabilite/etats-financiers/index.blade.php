@extends('layouts.gel-accountant')

@section('title', 'États financiers - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">États financiers</h1>
        <p class="gel-page-subtitle">Bilan, Compte de résultat, SIG</p>
    </div>
</div>

{{-- Filtres --}}
<div class="gel-card" style="margin-bottom:16px;">
    <div class="gel-card-body">
        <form method="GET" action="{{ route('gel-accountant.comptabilite.etats-financiers') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="gel-filter-group">
                <label>Client</label>
                <select name="client_id" class="gel-filter-select" onchange="this.form.submit()">
                    <option value="">Sélectionner un client...</option>
                    @if(isset($clients) && count($clients) > 0)
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Type</label>
                <select name="type" class="gel-filter-select" onchange="this.form.submit()">
                    <option value="bilan" {{ request('type', 'bilan') === 'bilan' ? 'selected' : '' }}>Bilan</option>
                    <option value="resultat" {{ request('type') === 'resultat' ? 'selected' : '' }}>Compte de résultat</option>
                    <option value="sig" {{ request('type') === 'sig' ? 'selected' : '' }}>SIG</option>
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

{{-- Contenu --}}
<div class="gel-card">
    <div class="gel-card-header">
        <strong>
            @if(request('type', 'bilan') === 'bilan')
                Bilan comptable
            @elseif(request('type') === 'resultat')
                Compte de résultat
            @else
                Soldes Intermédiaires de Gestion (SIG)
            @endif
        </strong>
    </div>
    <div class="gel-card-body">
        @if(!request('client_id'))
            <div class="gel-empty">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <h3>Sélectionnez un client</h3>
                <p>Choisissez un client pour générer ses états financiers.</p>
            </div>
        @elseif(request('type', 'bilan') === 'bilan')
            {{-- BILAN --}}
            @if(isset($actif) && count($actif) > 0)
                <h5 style="margin-bottom:12px;color:var(--gel-primary);">ACTIF</h5>
                <table class="gel-table" style="margin-bottom:24px;">
                    <thead>
                        <tr>
                            <th>Compte</th>
                            <th>Intitulé</th>
                            <th class="gel-text-right">Montant brut</th>
                            <th class="gel-text-right">Amort./Prov.</th>
                            <th class="gel-text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($actif as $ligne)
                            <tr>
                                <td>{{ $ligne['code'] ?? '—' }}</td>
                                <td>{{ $ligne['intitule'] ?? '—' }}</td>
                                <td class="gel-text-right">{{ number_format($ligne['brut'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="gel-text-right">{{ number_format($ligne['amortissement'] ?? 0, 0, ',', ' ') }}</td>
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
                        <tr>
                            <th>Compte</th>
                            <th>Intitulé</th>
                            <th class="gel-text-right">Montant</th>
                        </tr>
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
                    <p>Les données du bilan ne sont pas encore disponibles pour ce client.</p>
                </div>
            @endif

        @elseif(request('type') === 'resultat')
            {{-- COMPTE DE RÉSULTAT --}}
            @if(isset($produits) && count($produits) > 0)
                <h5 style="margin-bottom:12px;color:var(--gel-success);">PRODUITS (Classe 7)</h5>
                <table class="gel-table" style="margin-bottom:24px;">
                    <thead>
                        <tr><th>Compte</th><th>Intitulé</th><th class="gel-text-right">Montant</th></tr>
                    </thead>
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
                <h5 style="margin-bottom:12px;color:var(--gel-danger);">CHARGES (Classe 6)</h5>
                <table class="gel-table" style="margin-bottom:24px;">
                    <thead>
                        <tr><th>Compte</th><th>Intitulé</th><th class="gel-text-right">Montant</th></tr>
                    </thead>
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

            @if(isset($totalProduits) && isset($totalCharges))
                <div style="display:flex;gap:16px;justify-content:flex-end;padding:16px;border-top:2px solid var(--gel-border);">
                    <div style="text-align:right;">
                        <div style="font-size:12px;color:var(--gel-text-muted);">Total Produits</div>
                        <div style="font-size:18px;font-weight:700;color:var(--gel-success);">{{ number_format($totalProduits, 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:12px;color:var(--gel-text-muted);">Total Charges</div>
                        <div style="font-size:18px;font-weight:700;color:var(--gel-danger);">{{ number_format($totalCharges, 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div style="text-align:right;padding-left:16px;border-left:2px solid var(--gel-border);">
                        <div style="font-size:12px;color:var(--gel-text-muted);">Résultat Net</div>
                        <div style="font-size:20px;font-weight:800;color:var(--gel-primary);">
                            {{ number_format($totalProduits - $totalCharges, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                </div>
            @endif

            @if(!isset($produits) || count($produits) === 0)
                <div class="gel-empty">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <h3>Résultat non disponible</h3>
                    <p>Les données du compte de résultat ne sont pas encore disponibles.</p>
                </div>
            @endif

        @elseif(request('type') === 'sig')
            {{-- SIG --}}
            <div class="gel-empty">
                <i class="bi bi-bar-chart-steps"></i>
                <h3>SIG</h3>
                <p>Les Soldes Intermédiaires de Gestion seront bientôt disponibles.</p>
            </div>
        @endif
    </div>
</div>
@endsection
