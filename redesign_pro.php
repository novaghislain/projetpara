<?php
$blade = <<<HTML
@extends('layouts.gel-secretary')
@section('title', 'Tableau de bord — Secrétariat')

@section('content')
<style>
  /* ANIMATIONS SUBTILES */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade { animation: fadeUp 0.4s ease-out forwards; opacity: 0; }
  .delay-1 { animation-delay: 0.05s; }
  .delay-2 { animation-delay: 0.1s; }
  .delay-3 { animation-delay: 0.15s; }

  /* EN-TETE PROFESSIONNEL */
  .pro-header {
    display: flex; justify-content: space-between; align-items: flex-end;
    margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--sec-border);
  }
  .pro-title { font-size: 18px; font-weight: 700; color: var(--sec-text); margin-bottom: 4px; }
  .pro-subtitle { font-size: 12px; color: var(--sec-text-muted); }

  .pro-actions { display: flex; gap: 8px; }
  .pro-btn {
    background: white; border: 1px solid var(--sec-border); color: var(--sec-text);
    padding: 7px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
  }
  .pro-btn:hover { background: #F8FAFC; border-color: #CBD5E1; }
  .pro-btn-primary {
    background: var(--sec-primary); border-color: var(--sec-primary); color: white;
  }
  .pro-btn-primary:hover { background: var(--sec-primary-dark); color: white; border-color: var(--sec-primary-dark); }

  /* KPI COMPACTS */
  .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .kpi-card {
    background: white; border-radius: 8px; padding: 16px;
    border: 1px solid var(--sec-border);
    display: flex; align-items: center; gap: 14px;
    transition: border-color 0.15s; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }
  .kpi-card:hover { border-color: #CBD5E1; }
  .kpi-icon {
    width: 40px; height: 40px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
    background: var(--bg-color); color: var(--icon-color);
  }
  .kpi-val { font-size: 20px; font-weight: 700; color: var(--sec-text); line-height: 1.1; margin-bottom: 2px; }
  .kpi-label { font-size: 11px; color: var(--sec-text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }

  /* PANNEAUX / CARTES */
  .pro-panel {
    background: white; border-radius: 8px; border: 1px solid var(--sec-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;
  }
  .panel-header {
    padding: 14px 16px; border-bottom: 1px solid var(--sec-border);
    display: flex; justify-content: space-between; align-items: center;
    background: #FAFAFA; border-radius: 8px 8px 0 0;
  }
  .panel-title { font-size: 13px; font-weight: 700; color: var(--sec-text); }
  .panel-body { padding: 0; flex: 1; }

  /* LISTES COMPACTES */
  .list-row {
    display: flex; align-items: center; gap: 12px; padding: 10px 16px;
    border-bottom: 1px solid var(--sec-border); text-decoration: none; color: inherit;
  }
  .list-row:last-child { border-bottom: none; }
  .list-row:hover { background: #F8FAFC; }
  
  .avatar-sm {
    width: 32px; height: 32px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 12px; background: #E2E8F0; color: #475569;
  }
  
  /* BADGES */
  .badge-sm {
    padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;
  }
  .b-active { background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; }
  .b-urgent { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
  .b-normal { background: #F8FAFC; color: #64748B; border: 1px solid #E2E8F0; }

  /* CUSTOM CHECKBOX */
  .chk {
    width: 16px; height: 16px; border-radius: 4px; border: 1px solid #CBD5E1;
    appearance: none; outline: none; cursor: pointer; position: relative;
  }
  .chk:checked { background: var(--sec-primary); border-color: var(--sec-primary); }
  .chk:checked::after {
    content: '\\f00c'; font-family: "Font Awesome 6 Free"; font-weight: 900;
    color: white; position: absolute; font-size: 9px;
    top: 50%; left: 50%; transform: translate(-50%, -50%);
  }
</style>

<div class="pro-header animate-fade">
  <div>
    <div class="pro-title">Aperçu de l'activité</div>
    <div class="pro-subtitle">Tableau de bord • {{ now()->translatedFormat('d F Y') }}</div>
  </div>
  <div class="pro-actions">
    @if(\$activeClient)
      <a href="{{ route('gel-secretary.documents.index') }}" class="pro-btn">
        <i class="fas fa-folder"></i> Documents (\{\{ \$activeClient->company_name \}\})
      </a>
      <a href="#" class="pro-btn pro-btn-primary">
        <i class="fas fa-plus"></i> Nouvelle action
      </a>
    @else
      <a href="{{ route('gel-secretary.clients.index') }}" class="pro-btn">
        <i class="fas fa-building"></i> Annuaire clients
      </a>
    @endif
  </div>
</div>

<!-- KPIs -->
<div class="kpi-grid animate-fade delay-1">
  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #F0FDFA; --icon-color: #0D9488;">
      <i class="fas fa-building"></i>
    </div>
    <div>
      <div class="kpi-val">{{ \$stats['total_clients'] }}</div>
      <div class="kpi-label">Total entreprises</div>
    </div>
  </div>
  
  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #FFF7ED; --icon-color: #EA580C;">
      <i class="fas fa-tasks"></i>
    </div>
    <div>
      <div class="kpi-val">{{ \$stats['tasks_pending'] }}</div>
      <div class="kpi-label">Tâches en cours</div>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #EFF6FF; --icon-color: #2563EB;">
      <i class="fas fa-inbox"></i>
    </div>
    <div>
      <div class="kpi-val">{{ \$stats['messages_unread'] }}</div>
      <div class="kpi-label">Messages non lus</div>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #F5F3FF; --icon-color: #7C3AED;">
      <i class="fas fa-file-alt"></i>
    </div>
    <div>
      <div class="kpi-val">{{ \$stats['documents_recent'] }}</div>
      <div class="kpi-label">Nouveaux documents</div>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 40px;">
  
  <!-- ENTREPRISES CLIENTES -->
  <div class="pro-panel animate-fade delay-2">
    <div class="panel-header">
      <div class="panel-title">Portefeuille client</div>
      <a href="{{ route('gel-secretary.clients.index') }}" class="pro-btn" style="padding: 4px 8px; font-size: 11px;">Voir tout</a>
    </div>
    <div class="panel-body">
      @forelse(\$clients->take(6) as \$c)
      <a href="{{ route('gel-secretary.clients.show', \$c->id) }}" class="list-row">
        <div class="avatar-sm">
          {{ strtoupper(substr(\$c->company_name ?? 'E', 0, 2)) }}
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-size: 13px; font-weight: 600; color: var(--sec-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ \$c->company_name }}</div>
          <div style="font-size: 11px; color: var(--sec-text-muted);">{{ \$c->email ?? 'Non renseigné' }}</div>
        </div>
        @if(\$activeClient?->id == \$c->id)
          <span class="badge-sm b-active">Actif</span>
        @endif
      </a>
      @empty
      <div style="padding: 32px; text-align: center; color: var(--sec-text-muted); font-size: 12px;">
        Aucun client enregistré.
      </div>
      @endforelse
    </div>
  </div>

  <!-- TACHES A TRAITER -->
  <div class="pro-panel animate-fade delay-3">
    <div class="panel-header">
      <div class="panel-title">Tâches à traiter</div>
      <span style="font-size: 11px; color: var(--sec-text-muted);">{{ \$stats['tasks_pending'] }} en attente</span>
    </div>
    <div class="panel-body">
      @foreach([
        ['Préparation relevés bancaires', 'SARL TechInnov', 'urgent'],
        ['Traitement de la TVA', 'SAS AgroPro', 'normal'],
        ['Relance facture impayée', 'EURL Diallo', 'normal'],
        ['Classement courrier entrant', null, 'normal'],
        ['Vérification dossiers annuels', 'SARL BuildCo', 'normal']
      ] as \$task)
      <label class="list-row" style="cursor: pointer; margin: 0;">
        <input type="checkbox" class="chk" onclick="event.stopPropagation()">
        <div style="flex: 1; min-width: 0;">
          <div style="font-size: 13px; font-weight: 500; color: var(--sec-text);">{{ \$task[0] }}</div>
          @if(\$task[1])
          <div style="font-size: 11px; color: var(--sec-text-muted);"><i class="fas fa-building" style="margin-right: 4px; opacity: 0.6;"></i>{{ \$task[1] }}</div>
          @endif
        </div>
        @if(\$task[2] === 'urgent')
          <span class="badge-sm b-urgent">Urgent</span>
        @else
          <span class="badge-sm b-normal">Standard</span>
        @endif
      </label>
      @endforeach
    </div>
  </div>

</div>
@endsection
HTML;

file_put_contents('resources/views/gel-secretary/dashboard.blade.php', $blade);
echo "Redesigned professional dashboard successfully.\n";
?>
