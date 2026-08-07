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
            <span style="font-weight:600; color:var(--gel-text-primary);">{{ $stats['bilans_en_attente'] ?? 0 }}</span> bilans en attente de clôture
          </li>
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-envelope-open-text" style="color:#8B5CF6;"></i> 
            <span style="font-weight:600; color:var(--gel-text-primary);">{{ $stats['messages_non_lus'] ?? 0 }}</span> messages clients non lus
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

{{-- Sélecteur entreprise + période --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
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

<!-- GRID PRINCIPALE : 2 COLONNES -->
<div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px; align-items: start;">
    
    <!-- COLONNE GAUCHE (Contenu Principal) -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        {{-- LIGNE 1 — KPIs Financiers --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div class="gel-kpi-card" style="margin-bottom:0;">
                <div class="gel-kpi-label">Revenus</div>
                <div class="gel-kpi-value">{{ number_format($stats['revenus'] ?? 0, 0, ',', ' ') }} CFA</div>
                <div class="gel-kpi-change {{ ($stats['evolution_revenus'] ?? 0) >= 0 ? 'up' : 'down' }}"><i class="fas fa-arrow-{{ ($stats['evolution_revenus'] ?? 0) >= 0 ? 'up' : 'down' }}"></i> {{ $stats['evolution_revenus'] ?? 0 }}% vs N-1</div>
            </div>
            <div class="gel-kpi-card" style="margin-bottom:0;">
                <div class="gel-kpi-label">Dépenses</div>
                <div class="gel-kpi-value">{{ number_format($stats['depenses'] ?? 0, 0, ',', ' ') }} CFA</div>
            </div>
            <div class="gel-kpi-card" style="margin-bottom:0;">
                <div class="gel-kpi-label">Bénéfice</div>
                <div class="gel-kpi-value" style="color:{{ ($stats['benefice'] ?? 0) >= 0 ? 'var(--gel-success)' : 'var(--gel-danger)' }};">{{ number_format($stats['benefice'] ?? 0, 0, ',', ' ') }} CFA</div>
            </div>
            <div class="gel-kpi-card" style="margin-bottom:0;">
                <div class="gel-kpi-label">Clients Actifs</div>
                <div class="gel-kpi-value">{{ $stats['clients_actifs'] ?? 0 }}</div>
                <div class="gel-kpi-change up"><i class="fas fa-arrow-up"></i> +0 ce mois</div>
            </div>
        </div>

        {{-- LIGNE 2 — Graphiques --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:16px;">
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

        {{-- LIGNE 3 — Activités récentes --}}
        <div class="gel-card p-4">
            <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Activités récentes</span></div>
            <div class="gel-card-body p-4">
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

        {{-- LIGNE 4 — Derniers clients --}}
        @if(isset($recentClients) && count($recentClients) > 0)
        <div class="gel-card p-4">
            <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Derniers clients</span></div>
            <div class="gel-card-body p-4">
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

    </div>

    <!-- COLONNE DROITE (Sidebar contextuelle) -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        {{-- CARTE CAISSES (Mise en valeur) --}}
        <div class="gel-card" onclick="window.location.href='{{ route('gel-accountant.commerce.pos.index') }}'" style="cursor:pointer; transition: transform 0.2s, box-shadow 0.2s; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 15px rgba(16, 185, 129, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(16, 185, 129, 0.25)'">
            <div class="gel-card-body" style="padding: 24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">
                        <i class="fas fa-cash-register me-2"></i> Caisses (POS)
                    </div>
                    <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 32px; height: 32px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 800; margin-bottom: 8px;">1 / 2 <span style="font-size: 16px; font-weight: 500; opacity: 0.9;">Actives</span></div>
                <div style="font-size:14px; opacity: 0.9; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-chart-line"></i> 1 250,50 CFA Encaissés
                </div>
            </div>
        </div>

        {{-- ALERTES --}}
        <div class="gel-card p-4">
            <div class="gel-card-header p-4 mb-3" style="border-bottom:none; padding-bottom:0;"><span style="font-weight:700;">Alertes & Notifications</span></div>
            <div class="gel-card-body p-4 pt-0">
                <div class="gel-alertes" style="display:flex; flex-direction:column; gap:12px;">
                    @if(($stats['en_attente'] ?? 0) > 0)
                    <div class="gel-alerte-item alerte-rouge" style="margin:0; width:100%;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $stats['en_attente'] }} écriture(s) en attente</span>
                    </div>
                    @endif
                    @if(($stats['declarations_tva_imminentes'] ?? 0) > 0)
                    <div class="gel-alerte-item alerte-jaune" style="margin:0; width:100%;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ $stats['declarations_tva_imminentes'] }} décla. TVA imminente(s)</span>
                    </div>
                    @endif
                    @if(($stats['bilans_en_attente'] ?? 0) > 0)
                    <div class="gel-alerte-item alerte-jaune" style="margin:0; width:100%;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ $stats['bilans_en_attente'] }} bilan(s) en attente</span>
                    </div>
                    @endif
                    @if(($stats['en_attente'] ?? 0) == 0 && ($stats['declarations_tva_imminentes'] ?? 0) == 0 && ($stats['bilans_en_attente'] ?? 0) == 0)
                    <div class="gel-alerte-item alerte-verte" style="margin:0; width:100%;">
                        <i class="fas fa-check-circle"></i>
                        <span>Tout est à jour !</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ECHEANCES --}}
        <div class="gel-card p-4">
            <div class="gel-card-header p-4 mb-3" style="border-bottom:none; padding-bottom:0;"><span style="font-weight:700;">Prochaines échéances</span></div>
            <div class="gel-card-body p-4 pt-0">
                <div class="gel-echeances" style="margin:0; display:flex; flex-direction:column; gap:12px;">
                    <div class="gel-echeance-item" style="border-bottom:1px solid var(--gel-border); padding-bottom:12px;">
                        <span class="gel-echeance-label"><i class="fas fa-file-invoice" style="color:var(--gel-warning);"></i> Déclaration TVA</span>
                        <span class="gel-echeance-date">J+5</span>
                    </div>
                    <div class="gel-echeance-item" style="border-bottom:1px solid var(--gel-border); padding-bottom:12px;">
                        <span class="gel-echeance-label"><i class="fas fa-building" style="color:var(--gel-info);"></i> CNSS</span>
                        <span class="gel-echeance-date">J+12</span>
                    </div>
                    <div class="gel-echeance-item" style="border-bottom:1px solid var(--gel-border); padding-bottom:12px;">
                        <span class="gel-echeance-label"><i class="fas fa-lock" style="color:var(--gel-danger);"></i> Clôture mens.</span>
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
</div>
@endsection
