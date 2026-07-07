@php $currentSection = 'dashboard'; @endphp
@extends('layouts.gel-accountant')

@section('title', 'Tableau de bord - GEL Accountant')

@section('content')
{{-- Page Header --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Tableau de bord</h1>
        <p class="gel-page-subtitle">Bienvenue, {{ Auth::user()->name ?? 'Utilisateur' }} — {{ now()->format('d/m/Y') }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="gel-btn gel-btn-primary gel-btn-sm">
            <i class="fas fa-plus"></i> Nouvelle écriture
        </a>
    </div>
</div>

{{-- Sélecteur entreprise + période --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:6px;">
        <label style="font-size:13px;font-weight:600;color:var(--gel-text-secondary);">Entreprise :</label>
        <select class="gel-filter-select" onchange="showToast('Filtre changé','info')">
            <option value="">Toutes les entreprises</option>
            @if(isset($clients) && count($clients) > 0)
                @foreach($clients as $c)
                <option value="{{ $c->id }}">{{ $c->nom_entreprise }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div style="display:flex;align-items:center;gap:6px;">
        <label style="font-size:13px;font-weight:600;color:var(--gel-text-secondary);">Période :</label>
        <select class="gel-filter-select">
            <option>Ce mois</option>
            <option>Ce trimestre</option>
            <option>Cette année</option>
            <option>Personnalisée</option>
        </select>
    </div>
</div>

{{-- LIGNE 1 — KPIs --}}
<div class="gel-kpi-grid">
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Revenus</div>
        <div class="gel-kpi-value">{{ number_format($stats['clients_actifs'] ?? 0) }} CFA</div>
        <div class="gel-kpi-change up"><i class="fas fa-arrow-up"></i> +0% vs N-1</div>
    </div>
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Dépenses</div>
        <div class="gel-kpi-value">{{ number_format($stats['ecritures_mois'] ?? 0) }} CFA</div>
        <div class="gel-kpi-change down"><i class="fas fa-arrow-down"></i> +0% vs N-1</div>
    </div>
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Bénéfice</div>
        <div class="gel-kpi-value" style="color:var(--gel-success);">CFA 0</div>
        <div class="gel-kpi-change up"><i class="fas fa-arrow-up"></i> +0% vs N-1</div>
    </div>
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Clients</div>
        <div class="gel-kpi-value">{{ $stats['clients_actifs'] ?? 0 }}</div>
        <div class="gel-kpi-change up"><i class="fas fa-arrow-up"></i> +0 ce mois</div>
    </div>
</div>

{{-- LIGNE 2 — Graphiques --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
    <div class="gel-chart-container">
        <div class="gel-chart-header">
            <div class="gel-chart-title">Évolution des revenus</div>
            <select class="gel-filter-select"><option>12 mois</option></select>
        </div>
        <div style="text-align:center;padding:40px;color:var(--gel-text-muted);">
            <i class="fas fa-chart-line" style="font-size:48px;margin-bottom:12px;display:block;"></i>
            Transactions insuffisantes
        </div>
    </div>
    <div class="gel-chart-container">
        <div class="gel-chart-header">
            <div class="gel-chart-title">Dépenses par catégorie</div>
        </div>
        <div style="text-align:center;padding:40px;color:var(--gel-text-muted);">
            <i class="fas fa-chart-pie" style="font-size:48px;margin-bottom:12px;display:block;"></i>
            Pas assez de données
        </div>
    </div>
</div>

{{-- LIGNE 3 — Activités récentes + Échéances --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
    <div class="gel-card">
        <div class="gel-card-header"><span style="font-weight:700;">Activités récentes</span></div>
        <div class="gel-card-body" style="padding:0;">
            @if(isset($recentEcritures) && count($recentEcritures) > 0)
            <table class="gel-table">
                <tr><th>Date</th><th>Libellé</th><th>Statut</th></tr>
                @foreach($recentEcritures as $e)
                <tr>
                    <td>{{ $e->date_ecriture->format('d/m/Y') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($e->libelle, 35) }}</td>
                    <td>
                        @if($e->valide)
                        <span class="gel-badge gel-badge-success">Validée</span>
                        @else
                        <span class="gel-badge gel-badge-warning">Brouillon</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
            @else
            <div class="gel-empty" style="padding:30px;">
                <i class="fas fa-pen"></i>
                <h3>Aucune activité récente</h3>
                <p>Les écritures apparaîtront ici.</p>
            </div>
            @endif
        </div>
    </div>
    <div class="gel-card">
        <div class="gel-card-header"><span style="font-weight:700;">Prochaines échéances</span></div>
        <div class="gel-card-body" style="padding:0;">
            <div class="gel-echeances" style="margin:0;">
                <div class="gel-echeance-item">
                    <span class="gel-echeance-label"><i class="fas fa-file-invoice" style="color:var(--gel-warning);"></i> Déclaration TVA</span>
                    <span class="gel-echeance-date">J+5</span>
                </div>
                <div class="gel-echeance-item">
                    <span class="gel-echeance-label"><i class="fas fa-building" style="color:var(--gel-info);"></i> CNSS trimestrielle</span>
                    <span class="gel-echeance-date">J+12</span>
                </div>
                <div class="gel-echeance-item">
                    <span class="gel-echeance-label"><i class="fas fa-lock" style="color:var(--gel-danger);"></i> Clôture mensuelle</span>
                    <span class="gel-echeance-date">J+30</span>
                </div>
                <div class="gel-echeance-item">
                    <span class="gel-echeance-label"><i class="fas fa-users" style="color:var(--gel-text-muted);"></i> IRPP</span>
                    <span class="gel-echeance-date">15/08</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LIGNE 4 — Alertes --}}
<div class="gel-alertes">
    <div class="gel-alerte-item alerte-jaune">
        <i class="fas fa-exclamation-triangle"></i>
        <span>3 factures clients en retard de paiement</span>
    </div>
    <div class="gel-alerte-item alerte-rouge">
        <i class="fas fa-exclamation-circle"></i>
        <span>1 écriture en brouillon en attente de validation</span>
    </div>
    <div class="gel-alerte-item alerte-verte">
        <i class="fas fa-check-circle"></i>
        <span>Tous les comptes bancaires sont rapprochés</span>
    </div>
</div>

{{-- LIGNE 5 — Derniers clients --}}
@if(isset($recentClients) && count($recentClients) > 0)
<div class="gel-card">
    <div class="gel-card-header"><span style="font-weight:700;">Derniers clients</span></div>
    <div class="gel-card-body" style="padding:0;">
        <table class="gel-table">
            <tr><th>Client</th><th>Contact</th><th>Écritures</th><th>Statut</th></tr>
            @foreach($recentClients as $c)
            <tr>
                <td><strong>{{ $c->nom_entreprise }}</strong></td>
                <td>{{ $c->email ?? '—' }}</td>
                <td>{{ $loop->index * 10 + 10 }}</td>
                <td><span class="gel-badge gel-badge-success">{{ $c->statut ?? 'actif' }}</span></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endif
@endsection
