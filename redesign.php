<?php
$blade = <<<HTML
@extends('layouts.gel-secretary')
@section('title', 'Tableau de bord — Secrétariat')

@section('content')
<style>
  /* ANIMATIONS */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.2s; }
  .delay-3 { animation-delay: 0.3s; }

  /* HERO SECTION */
  .dash-hero {
    background: linear-gradient(135deg, var(--sec-primary-dark) 0%, var(--sec-primary) 100%);
    border-radius: 20px;
    padding: 35px 40px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(13, 148, 136, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .dash-hero::before {
    content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%; pointer-events: none;
  }
  .hero-greeting { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px; }
  .hero-date { font-size: 14px; opacity: 0.8; font-weight: 500; }
  
  .hero-actions { display: flex; gap: 12px; }
  .hero-btn {
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
    color: white; padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 13px;
    backdrop-filter: blur(10px); transition: all 0.3s ease; text-decoration: none;
    display: flex; align-items: center; gap: 8px;
  }
  .hero-btn:hover { background: white; color: var(--sec-primary-dark); transform: translateY(-2px); }

  /* KPI CARDS PREMIUM */
  .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
  .kpi-card {
    background: white; border-radius: 16px; padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05);
    display: flex; align-items: center; gap: 20px;
    transition: all 0.3s ease; position: relative; overflow: hidden;
  }
  .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.06); }
  .kpi-icon-wrap {
    width: 56px; height: 56px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center; font-size: 24px;
    background: var(--bg-color); color: var(--icon-color);
  }
  .kpi-info { flex: 1; }
  .kpi-val { font-size: 26px; font-weight: 800; color: var(--sec-text); line-height: 1.1; margin-bottom: 4px; }
  .kpi-label { font-size: 13px; color: var(--sec-text-muted); font-weight: 600; }

  /* LISTS & TABLES */
  .premium-panel {
    background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid rgba(0,0,0,0.05); overflow: hidden; height: 100%; display: flex; flex-direction: column;
  }
  .panel-header {
    padding: 20px 24px; border-bottom: 1px solid #F3F4F6;
    display: flex; justify-content: space-between; align-items: center;
  }
  .panel-title { font-size: 16px; font-weight: 700; color: var(--sec-text); display: flex; align-items: center; gap: 10px; }
  .panel-title i { color: var(--sec-primary); background: var(--sec-primary-light); padding: 8px; border-radius: 8px; font-size: 14px; }
  .panel-body { padding: 0; flex: 1; overflow-y: auto; }

  /* List Items */
  .list-item {
    display: flex; align-items: center; gap: 16px; padding: 16px 24px;
    border-bottom: 1px solid #F3F4F6; transition: background 0.2s; text-decoration: none; color: inherit;
  }
  .list-item:hover { background: #F8FAFC; }
  .list-item:last-child { border-bottom: none; }
  
  .avatar-md {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 15px; background: var(--sec-primary-light); color: var(--sec-primary);
  }
  
  /* Custom Checkbox */
  .task-checkbox {
    width: 20px; height: 20px; border-radius: 6px; border: 2px solid #CBD5E1;
    appearance: none; outline: none; cursor: pointer; transition: all 0.2s;
    position: relative;
  }
  .task-checkbox:checked { background: var(--sec-primary); border-color: var(--sec-primary); }
  .task-checkbox:checked::after {
    content: '\\f00c'; font-family: "Font Awesome 6 Free"; font-weight: 900;
    color: white; position: absolute; font-size: 12px;
    top: 50%; left: 50%; transform: translate(-50%, -50%);
  }

  /* Status Badges */
  .status-badge {
    padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;
  }
  .badge-urgent { background: #FEE2E2; color: #DC2626; }
  .badge-normal { background: #F1F5F9; color: #64748B; }
  
  /* Timeline */
  .timeline-item {
    padding: 16px 24px; display: flex; gap: 16px; position: relative;
  }
  .timeline-item::before {
    content: ''; position: absolute; left: 43px; top: 40px; bottom: 0; width: 2px; background: #E2E8F0;
  }
  .timeline-item:last-child::before { display: none; }
  .timeline-icon {
    width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    background: white; border: 2px solid #E2E8F0; font-size: 14px; position: relative; z-index: 2;
  }
  
</style>

<!-- HERO SECTION -->
<div class="dash-hero fade-in-up">
  <div>
    @php
      \$greeting = (date('H') < 18) ? 'Bonjour' : 'Bonsoir';
      \$userName = auth()->user()->name ?? 'Secrétaire';
    @endphp
    <div class="hero-greeting">{{ \$greeting }}, {{ \$userName }} \u{1F44B}</div>
    <div class="hero-date">Aujourd'hui, nous sommes le {{ now()->translatedFormat('l d F Y') }}</div>
    
    @if(\$activeClient)
      <div style="margin-top: 16px; background: rgba(0,0,0,0.15); display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px;">
        Entreprise active : <strong style="color:#FFF;">{{ \$activeClient->company_name }}</strong>
      </div>
    @endif
  </div>
  
  <div class="hero-actions">
    @if(\$activeClient)
      <a href="{{ route('gel-secretary.documents.index') }}" class="hero-btn">
        <i class="fas fa-folder-open"></i> Gérer les Documents
      </a>
      <a href="#" class="hero-btn" style="background: white; color: var(--sec-primary-dark);">
        <i class="fas fa-plus"></i> Nouvelle Action
      </a>
    @else
      <a href="{{ route('gel-secretary.clients.index') }}" class="hero-btn">
        <i class="fas fa-building"></i> Vos Entreprises
      </a>
    @endif
  </div>
</div>

<!-- KPIs -->
<div class="kpi-grid fade-in-up delay-1">
  <div class="kpi-card">
    <div class="kpi-icon-wrap" style="--bg-color: #F0FDFA; --icon-color: #0D9488;">
      <i class="fas fa-building"></i>
    </div>
    <div class="kpi-info">
      <div class="kpi-val">{{ \$stats['total_clients'] }}</div>
      <div class="kpi-label">Entreprises Clientes</div>
    </div>
  </div>
  
  <div class="kpi-card">
    <div class="kpi-icon-wrap" style="--bg-color: #FFF7ED; --icon-color: #EA580C;">
      <i class="fas fa-tasks"></i>
    </div>
    <div class="kpi-info">
      <div class="kpi-val">{{ \$stats['tasks_pending'] }}</div>
      <div class="kpi-label">Tâches en Attente</div>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon-wrap" style="--bg-color: #EFF6FF; --icon-color: #2563EB;">
      <i class="fas fa-comments"></i>
    </div>
    <div class="kpi-info">
      <div class="kpi-val">{{ \$stats['messages_unread'] }}</div>
      <div class="kpi-label">Messages Non Lus</div>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon-wrap" style="--bg-color: #F5F3FF; --icon-color: #7C3AED;">
      <i class="fas fa-file-alt"></i>
    </div>
    <div class="kpi-info">
      <div class="kpi-val">{{ \$stats['documents_recent'] }}</div>
      <div class="kpi-label">Documents Récents</div>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; margin-bottom: 40px;">
  
  <!-- ENTREPRISES CLIENTES -->
  <div class="premium-panel fade-in-up delay-2">
    <div class="panel-header">
      <div class="panel-title"><i class="fas fa-building"></i> Mes Entreprises</div>
      <a href="{{ route('gel-secretary.clients.index') }}" class="sec-btn sec-btn-secondary" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Voir l'annuaire</a>
    </div>
    <div class="panel-body">
      @forelse(\$clients->take(6) as \$c)
      <a href="{{ route('gel-secretary.clients.show', \$c->id) }}" class="list-item">
        <div class="avatar-md">
          {{ strtoupper(substr(\$c->company_name ?? 'E', 0, 2)) }}
        </div>
        <div style="flex: 1;">
          <div style="font-size: 14px; font-weight: 600; margin-bottom: 2px;">{{ \$c->company_name }}</div>
          <div style="font-size: 12px; color: var(--sec-text-muted);">{{ \$c->email ?? 'Aucun email' }}</div>
        </div>
        @if(\$activeClient?->id == \$c->id)
          <span class="status-badge" style="background: #DCFCE7; color: #166534;"><i class="fas fa-check-circle"></i> Actif</span>
        @else
          <i class="fas fa-chevron-right" style="color: #CBD5E1;"></i>
        @endif
      </a>
      @empty
      <div style="padding: 40px; text-align: center; color: var(--sec-text-muted);">
        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" style="width:64px; opacity:0.5; margin-bottom:16px;">
        <p>Aucune entreprise enregistrée dans votre portefeuille.</p>
      </div>
      @endforelse
    </div>
  </div>

  <!-- TACHES & ACTIVITE -->
  <div style="display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Tâches -->
    <div class="premium-panel fade-in-up delay-3" style="flex: auto;">
      <div class="panel-header">
        <div class="panel-title"><i class="fas fa-tasks" style="color: #EA580C; background: #FFF7ED;"></i> Tâches Prioritaires</div>
      </div>
      <div class="panel-body">
        @foreach([
          ['Préparer relevé bancaire', 'SARL TechInnov', 'urgent'],
          ['Envoyer documents TVA', 'SAS AgroPro', 'normal'],
          ['Rappel paiement facture #48', 'EURL Diallo', 'normal'],
          ['Classer courriers entrants', null, 'normal']
        ] as \$task)
        <div class="list-item" style="cursor: pointer;" onclick="this.querySelector('input').click()">
          <input type="checkbox" class="task-checkbox" onclick="event.stopPropagation()">
          <div style="flex: 1;">
            <div style="font-size: 14px; font-weight: 500; color: var(--sec-text);">{{ \$task[0] }}</div>
            @if(\$task[1])
            <div style="font-size: 12px; color: var(--sec-text-muted); margin-top: 2px;"><i class="fas fa-building me-1"></i> {{ \$task[1] }}</div>
            @endif
          </div>
          @if(\$task[2] === 'urgent')
            <span class="status-badge badge-urgent">Urgent</span>
          @else
            <span class="status-badge badge-normal">Normal</span>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    
  </div>

</div>
@endsection
HTML;

file_put_contents('resources/views/gel-secretary/dashboard.blade.php', $blade);
echo "Redesigned secretary dashboard successfully.\n";
?>
