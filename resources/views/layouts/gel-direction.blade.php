<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'GEL Direction') — Portail Dirigeant</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --sec-primary: #0D9488;
      --sec-primary-dark: #0F766E;
      --sec-primary-light: #F0FDFA;
      --sec-sidebar-bg: var(--sec-primary-dark);
      --sec-sidebar-hover: #F0FDFA;
      --sec-sidebar-active-bg: #CCFBF1;
      --sec-sidebar-w: 230px;
      --sec-topbar-h: 54px;
      --sec-text: #1F2A44;
      --sec-text-muted: #6B7280;
      --sec-border: #E5E7EB;
      --sec-bg: #F1F3F6;
      --sec-success: #10B981;
      --sec-danger: #EF4444;
      --sec-warning: #F59E0B;
      --sec-info: #3B82F6;
      --sec-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
    }

    html {
      font-size: 14px;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      color: var(--sec-text);
      background: var(--sec-bg);
      min-height: 100vh;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* ─── TOPBAR ─── */
    .sec-topbar {
      position: fixed;
      top: 0;
      left: var(--sec-sidebar-w);
      right: 0;
      height: var(--sec-topbar-h);
      background: var(--sec-primary);
      display: flex;
      align-items: center;
      padding: 0 20px;
      gap: 12px;
      z-index: 1000;
      border-bottom: 2px solid var(--sec-primary-dark);
    }

    .sec-topbar-brand {
      font-weight: 700;
      font-size: 16px;
      color: white;
      white-space: nowrap;
    }

    .sec-topbar-brand small {
      font-size: 11px;
      font-weight: 400;
      opacity: .75;
      margin-left: 6px;
    }

    /* Client switcher / Company display */
    .sec-client-switcher {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 6px;
      padding: 5px 12px;
      font-size: 13px;
      color: white;
      max-width: 240px;
    }

    .sec-client-switcher .name {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      flex: 1;
    }

    /* Omnisearch */
    .sec-omnisearch {
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 20px;
      padding: 5px 14px;
      color: white;
      font-size: 13px;
      width: 250px;
      outline: none;
      transition: all 0.2s;
    }

    .sec-omnisearch::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .sec-omnisearch:focus {
      background: rgba(255, 255, 255, 0.25);
      width: 300px;
    }

    .sec-topbar-right {
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .sec-topbar-btn {
      width: 32px;
      height: 32px;
      border: none;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 50%;
      color: white;
      font-size: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background 120ms;
    }

    .sec-topbar-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .sec-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: white;
      color: var(--sec-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 12px;
      cursor: pointer;
      flex-shrink: 0;
      overflow: hidden;
    }

    /* ─── DROPDOWN (topbar) ─── */
    .sec-dropdown {
      position: absolute;
      background: white;
      border: 1px solid var(--sec-border);
      border-radius: 8px;
      box-shadow: var(--sec-shadow);
      padding: 6px 0;
      min-width: 200px;
      z-index: 1100;
      display: none;
    }

    .sec-dropdown.open {
      display: block;
    }

    .sec-dd-item {
      display: block;
      padding: 8px 14px;
      font-size: 13px;
      color: var(--sec-text);
      text-decoration: none;
      transition: all 0.2s;
      white-space: nowrap;
    }

    .sec-dd-item:hover {
      background: var(--sec-bg-hover);
      color: var(--sec-primary);
    }

    /* ─── APP SWITCHER ─── */
    .app-switcher-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 6px;
      padding: 10px;
    }
    .app-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 14px 10px;
      border-radius: 8px;
      text-decoration: none;
      color: var(--sec-text);
      transition: background 0.2s;
      text-align: center;
    }
    .app-item:hover {
      background: var(--sec-bg-hover);
      text-decoration: none;
      color: var(--sec-primary);
    }
    .app-icon {
      font-size: 24px;
      margin-bottom: 8px;
    }
    .app-name {
      font-size: 11px;
      font-weight: 600;
    }

    /* ─── SIDEBAR ─── */
    .sec-sidebar::-webkit-scrollbar {
      display: none;
    }

    .sec-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: var(--sec-sidebar-w);
      background: var(--sec-sidebar-bg);
      display: flex;
      flex-direction: column;
      z-index: 999;
      overflow-y: auto;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .sec-sidebar-brand {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sec-sidebar-icon {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      background: linear-gradient(135deg, #0D9488, #14B8A6);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      flex-shrink: 0;
    }

    .sec-sidebar-title {
      font-size: 14px;
      font-weight: 700;
      color: white;
    }

    .sec-sidebar-sub {
      font-size: 11px;
      color: rgba(255, 255, 255, 0.7);
    }

    .sec-nav {
      list-style: none;
      padding: 8px;
      flex: 1;
    }

    .sec-nav-section {
      font-size: 10px;
      font-weight: 700;
      color: rgba(255, 255, 255, 0.5);
      text-transform: uppercase;
      letter-spacing: .6px;
      padding: 14px 10px 4px;
    }

    .sec-nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      margin: 0 0 4px 12px;
      border-radius: 20px 0 0 20px;
      position: relative;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.75);
      cursor: pointer;
      transition: all 100ms;
      text-decoration: none;
    }

    .sec-nav-item:hover {
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    .sec-nav-item.active {
      background: var(--sec-bg);
      color: var(--sec-primary);
      font-weight: 600;
    }

    .sec-nav-item.active::before,
    .sec-nav-item.active::after {
      content: '';
      position: absolute;
      right: 0;
      width: 20px;
      height: 20px;
      background: transparent;
      pointer-events: none;
    }

    .sec-nav-item.active::before {
      bottom: 100%;
      border-bottom-right-radius: 20px;
      box-shadow: 10px 10px 0 11px var(--sec-bg);
    }

    .sec-nav-item.active::after {
      top: 100%;
      border-top-right-radius: 20px;
      box-shadow: 10px -10px 0 11px var(--sec-bg);
    }

    .sec-nav-item i {
      width: 16px;
      text-align: center;
      font-size: 14px;
    }

    .sec-nav-badge {
      margin-left: auto;
      background: var(--sec-danger);
      color: white;
      font-size: 10px;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: 10px;
    }

    /* Sidebar footer */
    .sec-sidebar-footer {
      padding: 12px 16px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sec-sidebar-footer-avatar {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      overflow: hidden;
      background: var(--sec-primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
      flex-shrink: 0;
    }

    .sec-sidebar-footer-name {
      font-size: 12px;
      font-weight: 600;
      color: white;
    }

    .sec-sidebar-footer-role {
      font-size: 10px;
      color: rgba(255, 255, 255, 0.7);
    }

    /* ─── CONTENT ─── */
    .sec-content {
      margin-left: var(--sec-sidebar-w);
      margin-top: var(--sec-topbar-h);
      padding: 24px 28px;
      min-height: calc(100vh - var(--sec-topbar-h));
    }

    /* ─── KPI CARDS ─── */
    .sec-kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }

    .sec-kpi {
      background: white;
      border: 1px solid var(--sec-border);
      border-radius: 8px;
      padding: 18px 20px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .sec-kpi-icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }

    .sec-kpi-icon.teal {
      background: #F0FDFA;
      color: #0D9488;
    }

    .sec-kpi-icon.blue {
      background: #EFF6FF;
      color: #3B82F6;
    }

    .sec-kpi-icon.amber {
      background: #FFFBEB;
      color: #F59E0B;
    }

    .sec-kpi-icon.violet {
      background: #F5F3FF;
      color: #7C3AED;
    }

    .sec-kpi-icon.rose {
      background: #FFF1F2;
      color: #E11D48;
    }

    .sec-kpi-val {
      font-size: 26px;
      font-weight: 700;
      color: var(--sec-text);
      line-height: 1;
    }

    .sec-kpi-label {
      font-size: 12px;
      color: var(--sec-text-muted);
      margin-top: 2px;
    }

    /* ─── CARD ─── */
    .sec-card {
      background: white;
      border: 1px solid var(--sec-border);
      border-radius: 8px;
    }

    .sec-card-header {
      padding: 14px 18px;
      border-bottom: 1px solid var(--sec-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .sec-card-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--sec-text);
    }

    .sec-card-body {
      padding: 18px;
    }

    /* ─── TABLE ─── */
    .sec-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .sec-table th {
      text-align: left;
      padding: 10px 14px;
      border-bottom: 1px solid var(--sec-border);
      color: var(--sec-text-muted);
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .5px;
    }

    .sec-table td {
      padding: 11px 14px;
      border-bottom: 1px solid #F3F4F6;
    }

    .sec-table tr:last-child td {
      border-bottom: none;
    }

    .sec-table tr:hover td {
      background: #FAFAFA;
    }

    /* ─── BADGE ─── */
    .sec-badge {
      display: inline-block;
      padding: 3px 9px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    .sec-badge-success {
      background: #ECFDF5;
      color: #10B981;
    }

    .sec-badge-warning {
      background: #FFFBEB;
      color: #F59E0B;
    }

    .sec-badge-info {
      background: #EFF6FF;
      color: #3B82F6;
    }

    .sec-badge-danger {
      background: #FEF2F2;
      color: #EF4444;
    }

    .sec-badge-muted {
      background: #F3F4F6;
      color: #6B7280;
    }

    /* ─── BUTTONS ─── */
    .sec-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      border-radius: 5px;
      font-size: 13px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 100ms;
      text-decoration: none;
    }

    .sec-btn-sm {
      padding: 5px 10px;
      font-size: 12px;
    }

    .sec-btn-primary {
      background: var(--sec-primary);
      color: white;
    }

    .sec-btn-primary:hover {
      background: var(--sec-primary-dark);
      color: white;
    }

    .sec-btn-secondary {
      background: white;
      color: var(--sec-text);
      border: 1px solid var(--sec-border);
    }

    .sec-btn-secondary:hover {
      background: #F8FAFC;
    }

    /* ─── MODAL OVERLAY (PROFIL) ─── */
    .sec-overlay {
      position: fixed;
      top: 0; left: 0; width: 100vw; height: 100vh;
      background: rgba(31, 42, 68, 0.85);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .sec-modal {
      background: white;
      border-radius: 12px;
      padding: 32px;
      width: 100%;
      max-width: 450px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .sec-modal-title {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 8px;
      color: var(--sec-text);
    }
    .sec-modal-desc {
      font-size: 13px;
      color: var(--sec-text-muted);
      margin-bottom: 24px;
    }
    .sec-modal input, .sec-modal select {
      padding: 10px 14px;
      border: 1px solid var(--sec-border);
      border-radius: 6px;
      font-size: 13px;
      width: 100%;
      margin-bottom: 14px;
      font-family: inherit;
    }
    .sec-modal input:focus, .sec-modal select:focus {
      outline: none;
      border-color: var(--sec-primary);
      box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }
    
    /* ─── PAGE HEADER ─── */
    .sec-page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .sec-page-title {
      font-size: 20px;
      font-weight: 700;
      color: var(--sec-text);
    }
  </style>

  <script>
    function toggleSecDropdown(id) {
      document.querySelectorAll('.sec-dropdown').forEach(dd => {
        if (dd.id !== id) dd.classList.remove('open');
      });
      document.getElementById(id).classList.toggle('open');
    }

    document.addEventListener('click', function(e) {
      if (!e.target.closest('.sec-topbar-btn') && !e.target.closest('.sec-avatar') && !e.target.closest('.sec-dropdown')) {
        document.querySelectorAll('.sec-dropdown').forEach(dd => dd.classList.remove('open'));
      }
    });
  </script>
</head>

<body>

  @php
      $entreprise = \App\Models\Entreprise::find(session('active_entreprise_id'));
      $profilIncomplet = $entreprise && (
          empty($entreprise->ifu) || 
          $entreprise->regime_fiscal === 'non_defini' || 
          empty($entreprise->rccm)
      );
  @endphp

  @if($profilIncomplet)
  <!-- PROFIL INCOMPLET MODAL -->
  <div class="sec-overlay">
    <div class="sec-modal">
        <div class="text-center mb-4">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; background:#F0FDFA; color:#0D9488; border-radius:50%; margin-bottom:12px;">
                <i class="fas fa-building" style="font-size:24px;"></i>
            </div>
            <h3 class="sec-modal-title">Bienvenue sur GEL Cabinet !</h3>
            <p class="sec-modal-desc">Avant d'accéder à votre tableau de bord direction, veuillez compléter les informations légales de votre entreprise <strong>{{ $entreprise->raison_sociale }}</strong>.</p>
        </div>
        
        <form action="{{ route('gel-direction.profile.complete') }}" method="POST">
            @csrf
            
            <select name="pays_code" required>
                <option value="" disabled selected>Sélectionnez votre pays</option>
                <option value="BJ" {{ $entreprise->pays_code == 'BJ' ? 'selected' : '' }}>Bénin (BJ)</option>
                <option value="TG">Togo (TG)</option>
                <option value="CI">Côte d'Ivoire (CI)</option>
                <option value="SN">Sénégal (SN)</option>
            </select>

            <select name="regime_fiscal" required>
                <option value="" disabled selected>Votre Régime Fiscal</option>
                <option value="TPS">Taxe Professionnelle Synthétique (TPS)</option>
                <option value="RSI">Régime du Réel Simplifié (RSI)</option>
                <option value="RNI">Régime du Réel Normal (RNI)</option>
                <option value="Exonéré">Structure Exonérée</option>
            </select>

            <input type="text" name="ifu" placeholder="Numéro IFU (Identifiant Fiscal Unique)" required>
            <input type="text" name="rccm" placeholder="Numéro RCCM" required>
            <input type="text" name="secteur_activite" placeholder="Secteur d'activité (Ex: Informatique, BTP...)" required>
            
            <button type="submit" class="sec-btn sec-btn-primary w-100 justify-content-center" style="padding:10px; font-size:14px; margin-top:10px;">
                Enregistrer & Accéder
            </button>
        </form>
    </div>
  </div>
  @endif

  {{-- TOPBAR --}}
  <header class="sec-topbar">
    <div class="sec-topbar-brand">GEL <small>Direction</small></div>

    <div class="sec-client-switcher">
        <i class="fas fa-building" style="opacity:.8;font-size:13px;"></i>
        <span class="name">{{ $entreprise?->raison_sociale ?? 'Mon Entreprise' }}</span>
    </div>

    <div style="margin-left: 20px; position: relative; flex:1; max-width:400px;">
      <i class="fas fa-search"
        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: rgba(255,255,255,0.7);"></i>
      <input type="text" class="sec-omnisearch"
        placeholder="Rechercher..."
        style="padding-left: 32px; width:100%; max-width:100%;">
    </div>

    <div class="sec-topbar-right">
      <!-- Actions Rapides -->
      <div style="position:relative;">
        <button class="sec-topbar-btn" title="Actions Rapides" onclick="toggleSecDropdown('quickActionsDd')">
          <i class="fas fa-plus"></i>
        </button>
        <div class="sec-dropdown" id="quickActionsDd" style="right:0;top:calc(100% + 6px); width:200px; padding:8px 0;">
          <a href="{{ route('gel-direction.clients.index') }}" class="sec-dd-item"><i class="fas fa-user-plus" style="color:#0D9488;width:20px;"></i> Nouveau Client</a>
          <a href="{{ route('gel-direction.finance.index') }}" class="sec-dd-item"><i class="fas fa-file-invoice" style="color:#3B82F6;width:20px;"></i> Nouvelle Facture</a>
          <a href="{{ route('gel-direction.team.index') }}" class="sec-dd-item"><i class="fas fa-user-tie" style="color:#F59E0B;width:20px;"></i> Nouveau Salarié</a>
        </div>
      </div>

      <!-- APP SWITCHER -->
      <div style="position:relative;">
        <button class="sec-topbar-btn" title="Applications GEL" onclick="toggleSecDropdown('appSwitcherDd')">
          <i class="fas fa-th"></i>
        </button>
        <div class="sec-dropdown" id="appSwitcherDd" style="right:0;top:calc(100% + 6px); width:320px; padding:0;">
          <div style="padding:14px; border-bottom:1px solid var(--sec-border); font-weight:600; font-size:13px; color:var(--sec-text);">
            Applications
          </div>
          <div class="app-switcher-grid">
            @if(auth()->user()->hasRoleForActiveEntreprise('company_admin'))
            <a href="{{ route('gel-direction.dashboard') }}" class="app-item">
              <i class="fas fa-chart-line app-icon" style="color:#0D9488;"></i>
              <span class="app-name">Direction</span>
            </a>
            @endif
            @if(auth()->user()->hasRoleForActiveEntreprise('secretary'))
            <a href="{{ route('gel-secretary.dashboard') }}" class="app-item">
              <i class="fas fa-tachometer-alt app-icon" style="color:#3B82F6;"></i>
              <span class="app-name">Secrétariat</span>
            </a>
            @endif
            @if(auth()->user()->hasRoleForActiveEntreprise('accountant'))
            <a href="{{ route('gel-accountant.dashboard') }}" class="app-item">
              <i class="fas fa-chart-pie app-icon" style="color:#F59E0B;"></i>
              <span class="app-name">Comptabilité</span>
            </a>
            @endif
            @if(auth()->user()->hasRoleForActiveEntreprise('rh'))
            <a href="{{ route('gel-rh.dashboard') }}" class="app-item">
              <i class="fas fa-user-friends app-icon" style="color:#7C3AED;"></i>
              <span class="app-name">Ressources Humaines</span>
            </a>
            @endif
            @if(auth()->user()->hasRoleForActiveEntreprise('legal'))
            <a href="{{ route('gel-legal.dashboard') }}" class="app-item">
              <i class="fas fa-balance-scale app-icon" style="color:#E11D48;"></i>
              <span class="app-name">Juridique</span>
            </a>
            @endif
          </div>
        </div>
      </div>

      <button class="sec-topbar-btn" title="Aide"><i class="fas fa-question-circle"></i></button>

      <div style="position:relative;">
        <div class="sec-avatar" onclick="toggleSecDropdown('userDd')" title="Mon compte">
          {{ strtoupper(substr(auth()->user()?->nom ?? 'A', 0, 1)) }}
        </div>
        <div class="sec-dropdown" id="userDd" style="right:0;top:calc(100% + 6px);">
          <div style="padding:10px 14px;border-bottom:1px solid var(--sec-border);">
            <div style="font-weight:600;font-size:13px;">{{ auth()->user()?->nom ?? 'Admin' }}</div>
            <div style="font-size:11px;color:var(--sec-text-muted);">{{ auth()->user()?->email }}</div>
            <div style="font-size:11px;color:var(--sec-primary);font-weight:600;margin-top:2px;">Dirigeant</div>
          </div>
          <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="sec-dd-item w-100 text-start" style="background:none; border:none; color:#EF4444;">
              <i class="fas fa-sign-out-alt" style="width:20px;"></i> Déconnexion
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  {{-- SIDEBAR --}}
  <aside class="sec-sidebar">
    <div class="sec-sidebar-brand">
      <div class="sec-sidebar-icon">
        <i class="fas fa-chart-line"></i>
      </div>
      <div>
        <div class="sec-sidebar-title">GEL Direction</div>
        <div class="sec-sidebar-sub">Espace Administrateur</div>
      </div>
    </div>

    <ul class="sec-nav">
      <div class="sec-nav-section">GÉNÉRAL</div>
      <a href="{{ route('gel-direction.dashboard') }}" class="sec-nav-item {{ request()->routeIs('gel-direction.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> Vue d'ensemble
      </a>

      <li class="sec-nav-section">SUPERVISION</li>
      <li>
        <a href="{{ route('gel-direction.clients.index') }}"
          class="sec-nav-item {{ request()->routeIs('gel-direction.clients.*') ? 'active' : '' }}">
          <i class="fas fa-building"></i> Supervision Clients
        </a>
      </li>
      <li>
        <a href="{{ route('gel-direction.finance.index') }}"
          class="sec-nav-item {{ request()->routeIs('gel-direction.finance.*') ? 'active' : '' }}">
          <i class="fas fa-chart-line"></i> Rapport Financier
        </a>
      </li>
      <li>
        <a href="{{ route('gel-direction.team.index') }}"
          class="sec-nav-item {{ request()->routeIs('gel-direction.team.*') ? 'active' : '' }}">
          <i class="fas fa-users-cog"></i> Équipe & Collaborateurs
        </a>
      </li>

      <li class="sec-nav-section">VALIDATIONS</li>
      <li>
        <a href="{{ route('gel-direction.validations.index') }}"
          class="sec-nav-item {{ request()->routeIs('gel-direction.validations.*') ? 'active' : '' }}">
          <i class="fas fa-check-double"></i> Approbations
          @php
            $countValidations = \DB::table('rh_leave_requests')->where('statut', 'pending')->count() + \DB::table('gel_tasks')->where('statut', 'en_attente_validation')->count();
          @endphp
          @if($countValidations > 0)
            <span class="sec-nav-badge" style="background:#ef4444;">{{ $countValidations }}</span>
          @endif
        </a>
      </li>

      <li class="sec-nav-section">PARAMÈTRES</li>
      <li>
        <a href="#"
          class="sec-nav-item">
          <i class="fas fa-cog"></i> Configuration
        </a>
      </li>
    </ul>

    <div class="sec-sidebar-footer">
      <div class="sec-sidebar-footer-avatar">
        {{ strtoupper(substr(auth()->user()?->nom ?? 'A', 0, 1)) }}
      </div>
      <div>
        <div class="sec-sidebar-footer-name">{{ auth()->user()?->nom ?? 'Admin' }}</div>
        <div class="sec-sidebar-footer-role">Dirigeant</div>
      </div>
    </div>
  </aside>

  {{-- MAIN CONTENT --}}
  <main class="sec-content">
    @if(session('success'))
        <div style="background:#ECFDF5; border:1px solid #10B981; color:#065F46; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px; font-weight:500;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#FEF2F2; border:1px solid #EF4444; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px; font-weight:500;">
            <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i> {{ session('error') }}
        </div>
    @endif
    
    <div class="sec-page-header">
      <div>
        <h1 class="sec-page-title">@yield('page_title', 'Tableau de bord')</h1>
        <div class="sec-page-sub">Vue d'ensemble de votre cabinet</div>
      </div>
      <div>
        @yield('page_actions')
      </div>
    </div>

    @yield('content')
  </main>

</body>
</html>
