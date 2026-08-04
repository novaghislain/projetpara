@php $currentSection = 'dashboard'; @endphp
@extends('layouts.gel-accountant')

@section('title', 'Tableau de bord - GEL Accountant')

@section('content')
{{-- Page Header --}}
<style>
  /* ANIMATIONS SUBTILES */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade { animation: fadeUp 0.4s ease-out forwards; opacity: 0; }
  .delay-1 { animation-delay: 0.05s; }
  
  .pro-panel {
    background: white; border-radius: 8px; border: 1px solid var(--gel-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;
  }
  .panel-body { padding: 0; flex: 1; }
</style>

<div class="gel-page-header animate-fade" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 class="gel-page-title" style="font-size: 18px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">Tableau de bord Expert</h1>
        <p class="gel-page-subtitle" style="font-size: 12px; color: var(--gel-text-secondary);">Aperçu de l'activité du cabinet • {{ now()->locale('fr')->translatedFormat('l d F Y') }}</p>
    </div>
    <div style="display:flex;gap:8px;position:relative;">
        <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="toggleDropdown('dd-nouveau-dashboard')" style="background:var(--gel-primary);color:white;border:none;padding:7px 12px;border-radius:6px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-plus"></i> Nouveau <i class="fas fa-chevron-down" style="font-size:10px;"></i>
        </button>
        <div id="dd-nouveau-dashboard" class="nested-dropdown" style="top:calc(100% + 4px);left:auto;right:0;width:220px;position:absolute;">
            <div class="dd-header">CRÉER</div>
            <a href="{{ route('gel-accountant.client.create') ?? '#' }}" class="dd-item" style="text-decoration:none;"><span class="dd-icon">🏢</span> Nouveau dossier client</a>
            <a href="{{ route('gel-accountant.comptabilite.ecritures.create') ?? '#' }}" class="dd-item" style="text-decoration:none;"><span class="dd-icon">📝</span> Saisie d'écriture</a>
            <a href="#" class="dd-item" style="text-decoration:none;"><span class="dd-icon">📄</span> Nouvelle déclaration</a>
        </div>
    </div>
</div>

<!-- MA JOURNÉE (GREETING PREMIUM) -->
<div class="pro-panel animate-fade delay-1" style="margin-bottom: 24px; background: linear-gradient(to right, var(--gel-primary-light), #ffffff); border-left: 4px solid var(--gel-primary);">
  <div class="panel-body" style="padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h2 style="font-size:22px; font-weight:700; color:var(--gel-text-primary); margin-bottom:12px;">Bonjour {{ Auth::user()->name ?? 'Expert' }} 👋</h2>
        <div style="font-size:14px; color:var(--gel-text-secondary); margin-bottom:16px;">Priorités du jour pour le cabinet :</div>
        
        <ul style="list-style:none; padding:0; margin:0; display:flex; gap:24px; flex-wrap:wrap;">
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-landmark" style="color:var(--gel-primary);"></i> 
            <span style="font-weight:600; color:var(--gel-text-primary);">{{ $stats['declarations_tva_imminentes'] ?? 0 }}</span> déclarations TVA imminentes
          </li>
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-file-invoice" style="color:var(--gel-warning);"></i> 
            <span style="font-weight:600; color:var(--gel-text-primary);">{{ $stats['en_attente'] ?? 0 }}</span> écritures à réviser
          </li>
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-chart-pie" style="color:var(--gel-info);"></i> 
            <span style="font-weight:600; color:var(--gel-text-primary);">2</span> bilans en attente de clôture
          </li>
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-envelope-open-text" style="color:#8B5CF6;"></i> 
            <span style="font-weight:600; color:var(--gel-text-primary);">3</span> messages clients urgents
          </li>
        </ul>
      </div>
    </div>
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
        <div class="gel-kpi-value">{{ number_format($stats['revenus'] ?? 0, 0, ',', ' ') }} CFA</div>
        <div class="gel-kpi-change {{ ($stats['evolution_revenus'] ?? 0) >= 0 ? 'up' : 'down' }}"><i class="fas fa-arrow-{{ ($stats['evolution_revenus'] ?? 0) >= 0 ? 'up' : 'down' }}"></i> {{ $stats['evolution_revenus'] ?? 0 }}% vs N-1</div>
    </div>
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Dépenses</div>
        <div class="gel-kpi-value">{{ number_format($stats['depenses'] ?? 0, 0, ',', ' ') }} CFA</div>
    </div>
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Bénéfice</div>
        <div class="gel-kpi-value" style="color:{{ ($stats['benefice'] ?? 0) >= 0 ? 'var(--gel-success)' : 'var(--gel-danger)' }};">{{ number_format($stats['benefice'] ?? 0, 0, ',', ' ') }} CFA</div>
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
    <div class="gel-card p-4 mb-4">
        <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Activités récentes</span></div>
        <div class="gel-card-body p-4 mb-4">
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
    <div class="gel-card p-4 mb-4">
        <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Prochaines échéances</span></div>
        <div class="gel-card-body p-4 mb-4">
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
<div class="gel-card p-4 mb-4">
    <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Derniers clients</span></div>
    <div class="gel-card-body p-4 mb-4">
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
