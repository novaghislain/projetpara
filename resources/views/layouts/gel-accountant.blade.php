<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'GEL Accountant') — Cabinet Comptable</title>

  {{-- Fonts + Icons --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* ═══════════════════════════════════════════════════════════════
       RESET & VARIABLES
    ═══════════════════════════════════════════════════════════════ */
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --gel-primary: #005BAC;
      --gel-primary-hover: #004080;
      --gel-primary-light: #E8F0FE;
      --gel-sidebar-bg: #F7F8F9;
      --gel-sidebar-hover: #E5E7EB;
      --gel-sidebar-active: #005BAC;
      --gel-sidebar-width: 240px;
      --gel-topbar-height: 56px;
      --gel-text-primary: #1F2A44;
      --gel-text-secondary: #6B6C72;
      --gel-text-muted: #9CA3AF;
      --gel-border: #D1D5DB;
      --gel-card-bg: #FFFFFF;
      --gel-success: #10B981;
      --gel-danger: #EF4444;
      --gel-warning: #F59E0B;
      --gel-info: #3B82F6;
      --gel-dropdown-shadow: 0 8px 24px rgba(0,0,0,0.15);
      --gel-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    html { font-size: 14px; }
    body {
      font-family: var(--gel-font);
      color: var(--gel-text-primary);
      background: #FFFFFF;
      min-height: 100vh;
    }

    a { text-decoration: none; color: inherit; }
    button { cursor: pointer; font-family: inherit; }
    img { max-width: 100%; }

    /* ═══════════════════════════════════════════════════════════════
       TOPBAR
    ═══════════════════════════════════════════════════════════════ */
    .gel-topbar {
      position: fixed; top: 0; left: var(--gel-sidebar-width); right: 0;
      height: var(--gel-topbar-height);
      background: white;
      border-bottom: 1px solid var(--gel-border);
      display: flex; align-items: center;
      padding: 0 20px;
      z-index: 1000;
    }

    .topbar-logo {
      font-weight: 700; font-size: 18px;
      color: var(--gel-text-primary); margin-right: 12px;
      display: flex; align-items: center;
    }
    .topbar-logo .gel-logo-mark {
      width: 28px; height: 28px;
      background: var(--gel-primary); color: white;
      border-radius: 4px;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 15px;
      margin-right: 8px;
    }
    .topbar-logo small {
      font-weight: 400; font-size: 12px;
      color: var(--gel-text-muted); margin-left: 4px;
    }

    .btn-go-business {
      display: flex; align-items: center; gap: 6px;
      padding: 6px 12px;
      border: 1px solid var(--gel-border);
      border-radius: 4px; background: white;
      cursor: pointer; font-size: 13px; font-weight: 500;
      position: relative; white-space: nowrap;
      transition: background 120ms; color: var(--gel-text-primary);
    }
    .btn-go-business:hover { background: var(--gel-sidebar-hover); }

    .search-bar {
      flex: 1; max-width: 360px; margin: 0 20px; position: relative;
    }
    .search-bar input {
      width: 100%;
      padding: 8px 12px 8px 34px;
      border: 1px solid var(--gel-border);
      border-radius: 6px; font-size: 13px;
      background: var(--gel-sidebar-bg);
      transition: all 150ms; outline: none;
      color: var(--gel-text-primary);
    }
    .search-bar input:focus {
      border-color: var(--gel-primary);
      background: white;
      box-shadow: 0 0 0 3px rgba(0,91,172,0.1);
    }
    .search-icon {
      position: absolute; left: 10px; top: 50%;
      transform: translateY(-50%);
      color: var(--gel-text-secondary); font-size: 14px;
      pointer-events: none;
    }
    .search-kbd {
      position: absolute; right: 8px; top: 50%;
      transform: translateY(-50%);
      font-size: 10px; color: var(--gel-text-muted);
      background: var(--gel-sidebar-bg); padding: 1px 5px;
      border-radius: 3px; border: 1px solid var(--gel-border);
    }

    .topbar-right {
      display: flex; align-items: center; gap: 2px; margin-left: auto;
    }
    .topbar-btn {
      width: 34px; height: 34px; border: none; background: none;
      border-radius: 50%; cursor: pointer;
      color: var(--gel-text-secondary); font-size: 17px;
      display: flex; align-items: center; justify-content: center;
      position: relative; transition: background 120ms;
    }
    .topbar-btn:hover { background: var(--gel-sidebar-hover); }

    .notif-dot::after {
      content: ''; position: absolute; top: 5px; right: 5px;
      width: 7px; height: 7px; background: var(--gel-danger);
      border-radius: 50%; border: 2px solid white;
    }

    .avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: var(--gel-primary); color: white;
      display: flex; align-items: center; justify-content: center;
      font-weight: 600; font-size: 13px; cursor: pointer;
      margin-left: 4px;
      flex-shrink: 0;
    }

    /* ═══════════════════════════════════════════════════════════════
       SIDEBAR
    ═══════════════════════════════════════════════════════════════ */
    .gel-sidebar {
      position: fixed; top: 0; left: 0;
      width: var(--gel-sidebar-width); height: 100vh;
      background: var(--gel-sidebar-bg);
      border-right: 1px solid var(--gel-border);
      padding-top: var(--gel-topbar-height);
      overflow-y: auto; z-index: 999;
      display: flex; flex-direction: column;
    }

    .sidebar-menu { list-style: none; padding: 8px; margin: 0; flex: 1; }

    .sidebar-item {
      display: flex; align-items: center;
      padding: 9px 12px; cursor: pointer;
      font-size: 13.5px; color: var(--gel-text-primary);
      border-radius: 4px; margin-bottom: 1px;
      position: relative; user-select: none;
      transition: background 80ms;
    }
    .sidebar-item:hover { background: var(--gel-sidebar-hover); }
    .sidebar-item.active {
      background: var(--gel-primary-light);
      color: var(--gel-sidebar-active); font-weight: 600;
    }
    .sidebar-item .arrow {
      margin-left: auto; font-size: 10px;
      color: var(--gel-text-secondary); transition: none;
    }

    .sidebar-section {
      font-size: 11px; font-weight: 600; color: var(--gel-text-muted);
      text-transform: uppercase; letter-spacing: 0.5px;
      padding: 16px 12px 6px;
    }

    /* ═══════════════════════════════════════════════════════════════
       NESTED DROPDOWN
    ═══════════════════════════════════════════════════════════════ */
    .nested-dropdown {
      position: fixed;
      left: calc(var(--gel-sidebar-width) + 4px);
      top: 100px;
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      box-shadow: var(--gel-dropdown-shadow);
      min-width: 230px;
      padding: 6px 0;
      opacity: 0; visibility: hidden;
      transform: translateY(-4px);
      transition: opacity 120ms ease, visibility 120ms ease, transform 120ms ease;
      z-index: 1100;
      pointer-events: none;
    }
    .nested-dropdown.open {
      opacity: 1; visibility: visible;
      transform: translateY(0);
      pointer-events: auto;
    }

    .dd-header {
      padding: 6px 14px 4px;
      font-size: 11px; font-weight: 600; color: var(--gel-text-muted);
      text-transform: uppercase; letter-spacing: 0.5px;
    }

    .dd-item {
      display: flex; align-items: center;
      padding: 8px 14px; cursor: pointer;
      font-size: 13px; color: var(--gel-text-primary);
      white-space: nowrap; transition: background 80ms;
    }
    .dd-item:hover { background: #F0F4F8; }
    .dd-item.active { background: var(--gel-primary-light); color: var(--gel-primary); font-weight: 600; }
    .dd-item .arrow { margin-left: auto; font-size: 10px; color: var(--gel-text-secondary); }
    .dd-item .dd-icon { width: 20px; text-align: center; margin-right: 8px; font-size: 14px; }

    .dd-divider { height: 1px; background: var(--gel-border); margin: 4px 0; }

    /* ═══════════════════════════════════════════════════════════════
       CONTENT
    ═══════════════════════════════════════════════════════════════ */
    .gel-content {
      margin-left: var(--gel-sidebar-width);
      margin-top: var(--gel-topbar-height);
      padding: 28px 32px;
      min-height: calc(100vh - var(--gel-topbar-height));
    }

    /* ═══════════════════════════════════════════════════════════════
       COMPOSANTS — KPI, CARDS, BOUTONS
    ═══════════════════════════════════════════════════════════════ */

    /* KPI Grid */
    .gel-kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }

    .gel-kpi-card {
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      padding: 18px 20px;
    }

    .gel-kpi-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--gel-text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .gel-kpi-value {
      font-size: 24px;
      font-weight: 700;
      margin: 8px 0 4px;
      color: var(--gel-text-primary);
    }

    .gel-kpi-change {
      font-size: 13px;
    }
    .gel-kpi-change.up { color: var(--gel-success); }
    .gel-kpi-change.down { color: var(--gel-danger); }

    /* Card générique */
    .gel-card {
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
    }
    .gel-card-header {
      padding: 14px 18px;
      border-bottom: 1px solid var(--gel-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .gel-card-body { padding: 18px; }

    /* Table */
    .gel-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .gel-table th {
      text-align: left;
      padding: 10px 14px;
      border-bottom: 1px solid var(--gel-border);
      color: var(--gel-text-secondary);
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .gel-table td {
      padding: 10px 14px;
      border-bottom: 1px solid #F0F4F8;
    }

    /* Badges */
    .gel-badge {
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }
    .gel-badge-success {
      background: #ECFDF5;
      color: var(--gel-success);
    }
    .gel-badge-warning {
      background: #FFFBEB;
      color: var(--gel-warning);
    }

    /* Boutons */
    .gel-btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 8px 16px; border-radius: 4px;
      font-size: 13px; font-weight: 600;
      border: none; cursor: pointer;
      transition: all 120ms;
    }
    .gel-btn-sm { padding: 5px 10px; font-size: 12px; }
    .gel-btn-primary { background: var(--gel-primary); color: white; }
    .gel-btn-primary:hover { background: var(--gel-primary-hover); }
    .gel-btn-secondary {
      background: white; color: var(--gel-text-primary);
      border: 1px solid var(--gel-border);
    }
    .gel-btn-secondary:hover { background: var(--gel-sidebar-hover); }

    .gel-filter-select {
      padding: 5px 10px; border: 1px solid var(--gel-border);
      border-radius: 4px; font-size: 13px;
      background: white; font-family: inherit;
      color: var(--gel-text-primary);
    }

    /* Chart containers */
    .gel-chart-container {
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      padding: 18px;
    }
    .gel-chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }
    .gel-chart-title {
      font-size: 15px;
      font-weight: 600;
    }

    /* Alertes */
    .gel-alertes {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 24px;
    }
    .gel-alerte-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      border-radius: 6px;
      font-size: 13px;
      border-left: 3px solid;
    }
    .gel-alerte-item.alerte-jaune {
      background: #FFFBEB;
      border-left-color: var(--gel-warning);
    }
    .gel-alerte-item.alerte-rouge {
      background: #FEF2F2;
      border-left-color: var(--gel-danger);
    }
    .gel-alerte-item.alerte-verte {
      background: #ECFDF5;
      border-left-color: var(--gel-success);
    }

    /* Échéances */
    .gel-echeances { margin: 0; }
    .gel-echeance-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 18px;
      border-bottom: 1px solid #F0F4F8;
    }
    .gel-echeance-item:last-child { border-bottom: none; }
    .gel-echeance-label { font-size: 13px; display: flex; align-items: center; gap: 8px; }
    .gel-echeance-date {
      font-size: 12px;
      font-weight: 600;
      color: var(--gel-text-secondary);
    }

    /* Empty state */
    .gel-empty {
      text-align: center;
      padding: 40px;
      color: var(--gel-text-muted);
    }
    .gel-empty i {
      font-size: 40px;
      margin-bottom: 12px;
      display: block;
    }
    .gel-empty h3 {
      font-size: 16px;
      font-weight: 600;
      color: var(--gel-text-primary);
    }
    .gel-empty p {
      font-size: 13px;
    }

    /* Page header */
    .gel-page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .gel-page-title {
      font-size: 22px;
      font-weight: 700;
      color: var(--gel-text-primary);
    }
    .gel-page-subtitle {
      font-size: 13px;
      color: var(--gel-text-secondary);
      margin-top: 2px;
    }

    /* ═══════════════════════════════════════════════════════════════
       TOAST
    ═══════════════════════════════════════════════════════════════ */
    .gel-toast-container {
      position: fixed; top: 68px; right: 20px; z-index: 9999;
      display: flex; flex-direction: column; gap: 8px;
    }
    .gel-toast {
      background: white; border-left: 4px solid var(--gel-success);
      padding: 14px 20px; border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      display: flex; align-items: center; gap: 10px;
      font-size: 13px; min-width: 300px;
      animation: slideInRight 280ms ease forwards;
    }
    .gel-toast-error { border-left-color: var(--gel-danger); }
    .gel-toast-warning { border-left-color: var(--gel-warning); }
    .gel-toast-info { border-left-color: var(--gel-info); }
    .gel-toast.exit { animation: slideOutRight 200ms ease forwards; }

    @keyframes slideInRight {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(100%); opacity: 0; }
    }

    /* ═══════════════════════════════════════════════════════════════
       OVERLAY + SLIDE PANEL
    ═══════════════════════════════════════════════════════════════ */
    .panel-overlay {
      position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.3);
      z-index: 2000; opacity: 0; visibility: hidden;
      transition: all 250ms;
    }
    .panel-overlay.open { opacity: 1; visibility: visible; }

    .slide-panel {
      position: fixed; top: 0; right: 0;
      width: 480px; height: 100vh;
      background: white; z-index: 2001;
      box-shadow: -4px 0 24px rgba(0,0,0,0.15);
      transform: translateX(100%);
      transition: transform 250ms ease;
      overflow-y: auto;
    }
    .slide-panel.open { transform: translateX(0); }

    .panel-header {
      display: flex; align-items: center;
      justify-content: space-between;
      padding: 18px 24px;
      border-bottom: 1px solid var(--gel-border);
    }
    .panel-header h3 { font-size: 17px; font-weight: 600; }
    .panel-close {
      width: 32px; height: 32px; border: none;
      background: none; font-size: 20px;
      cursor: pointer; color: var(--gel-text-secondary);
      border-radius: 50%; display: flex;
      align-items: center; justify-content: center;
    }
    .panel-close:hover { background: var(--gel-sidebar-hover); }
    .panel-body { padding: 24px; }

    /* User footer dans la sidebar */
    .sidebar-footer {
      padding: 12px;
      border-top: 1px solid var(--gel-border);
      display: flex;
      align-items: center;
      gap: 10px;
      flex-shrink: 0;
    }

    /* ═══════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
      :root { --gel-sidebar-width: 0px; }
      .gel-sidebar { display: none; }
      .gel-topbar { left: 0; }
      .gel-content { margin-left: 0; }
      .slide-panel { width: 100%; }
      .gel-kpi-grid { grid-template-columns: 1fr 1fr; }
    }
  </style>

  @stack('styles')
</head>
<body>

  {{-- ════════════════════════════════════════════ TOPBAR ═══════════════ --}}
  <header class="gel-topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <div class="topbar-logo">
        <div class="gel-logo-mark">G</div>
        <span>GEL <small>Accountant</small></span>
      </div>

      {{-- Go To Business --}}
      <div style="position:relative;">
        <button class="btn-go-business" id="btnGoBusiness" onclick="toggleDropdown('dropdownGoBusiness')">
          <i class="fas fa-exchange-alt"></i> Go To GEL Business <i class="fas fa-chevron-down" style="font-size:9px;"></i>
        </button>
        <div class="nested-dropdown" id="dropdownGoBusiness" style="position:absolute;left:0;top:calc(100% + 6px);min-width:300px;">
          <div class="dd-header">Mes clients</div>
          @php $cabinetClients = []; try { $cabinetClients = \App\Models\Gel\Client::where('cabinet_id', auth()->user()?->cabinet_id)->where('statut', 'actif')->get(); } catch(\Exception $e) {} @endphp
          @forelse($cabinetClients as $c)
          <div class="dd-item" data-route="client-{{ $c->id }}"><span class="dd-icon">🏢</span> {{ $c->nom_entreprise }}</div>
          @empty
          <div class="dd-item" style="color:var(--gel-text-muted);"><span class="dd-icon">🏢</span> Aucun client actif</div>
          @endforelse
          <div class="dd-divider"></div>
          <div class="dd-item" data-route="gestion-clients"><span class="dd-icon">⚙️</span> Gérer les clients</div>
        </div>
      </div>
    </div>

    {{-- Search --}}
    <div class="search-bar">
      <i class="fas fa-search search-icon"></i>
      <input type="text" placeholder="Rechercher... Ctrl+K" id="globalSearch"
             onfocus="openSearchResults()" onblur="setTimeout(closeSearchResults, 200)">
      <span class="search-kbd">Ctrl+K</span>
      <div class="nested-dropdown" id="searchResults"
           style="position:absolute;left:0;top:calc(100% + 6px);width:100%;min-width:320px;">
        <div class="dd-header">RÉCEMMENTS</div>
        <div class="dd-item" onclick="navigateTo('ecritures')"><span class="dd-icon">📝</span> OD-2026-124 - Achat fournitures</div>
        <div class="dd-divider"></div>
        <div class="dd-header">RACCOURCIS</div>
        <div class="dd-item" onclick="navigateTo('dashboard')"><span class="dd-icon">📊</span> Tableau de bord</div>
        <div class="dd-item" onclick="navigateTo('clients')"><span class="dd-icon">👥</span> Liste des clients</div>
        <div class="dd-item" onclick="navigateTo('ecritures')"><span class="dd-icon">📝</span> Saisir une écriture</div>
      </div>
    </div>

    {{-- Right --}}
    <div class="topbar-right">
      <button class="topbar-btn" title="Fil d'actualité"><i class="fas fa-rss"></i></button>
      <button class="topbar-btn" title="Aide"><i class="fas fa-question-circle"></i></button>

      {{-- Notifications --}}
      <div style="position:relative;">
        <button class="topbar-btn notif-dot" onclick="toggleDropdown('notifDropdown')" title="Notifications">
          <i class="fas fa-bell"></i>
        </button>
        <div class="nested-dropdown" id="notifDropdown"
             style="position:absolute;right:0;left:auto;top:calc(100% + 6px);min-width:360px;">
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px 6px;border-bottom:1px solid var(--gel-border);">
            <span style="font-weight:600;font-size:14px;">Notifications</span>
            <a href="#" style="font-size:12px;font-weight:500;color:var(--gel-primary);" onclick="showToast('Tout marquer comme lu','success')">Tout marquer</a>
          </div>
          <div class="dd-item" style="gap:10px;white-space:normal;">
            <span>⚠️</span>
            <div><div>Facture impayée - SARL Bénin - 150 000 F</div><div style="font-size:11px;color:var(--gel-text-muted);">Il y a 2 heures</div></div>
          </div>
          <div class="dd-item" style="gap:10px;white-space:normal;">
            <span>✅</span>
            <div><div>Écriture validée - OD-2026-124</div><div style="font-size:11px;color:var(--gel-text-muted);">Il y a 5 heures</div></div>
          </div>
          <div class="dd-item" style="gap:10px;white-space:normal;">
            <span>📅</span>
            <div><div>Échéance TVA dans 5 jours</div><div style="font-size:11px;color:var(--gel-text-muted);">Il y a 1 jour</div></div>
          </div>
          <div class="dd-divider"></div>
          <div class="dd-item" style="justify-content:center;color:var(--gel-primary);font-weight:500;">Voir toutes →</div>
        </div>
      </div>

      <button class="topbar-btn" title="Paramètres"><i class="fas fa-cog"></i></button>

      {{-- User --}}
      <div style="position:relative;">
        <div class="avatar" onclick="toggleDropdown('userDropdown')">{{ strtoupper(substr(auth()->user()?->name ?? auth()->user()?->email ?? 'U', 0, 2)) }}</div>
        <div class="nested-dropdown" id="userDropdown"
             style="position:absolute;right:0;left:auto;top:calc(100% + 6px);min-width:220px;">
          <div style="padding:12px 14px;border-bottom:1px solid var(--gel-border);">
            <div style="font-weight:600;">{{ auth()->user()?->name ?? 'Utilisateur' }}</div>
            <div style="font-size:12px;color:var(--gel-text-muted);">{{ auth()->user()?->email }}</div>
            <div style="font-size:11px;color:var(--gel-primary);font-weight:500;margin-top:2px;">Comptable</div>
          </div>
          <div class="dd-item" data-route="profile"><span class="dd-icon">👤</span> Mon profil</div>
          <div class="dd-item" data-route="settings"><span class="dd-icon">⚙️</span> Paramètres</div>
          <div class="dd-divider"></div>
          <div class="dd-item" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
            <span class="dd-icon">🚪</span> Déconnexion
          </div>
        </div>
      </div>
    </div>
  </header>

  {{-- ════════════════════════════════════════════ SIDEBAR ═══════════════ --}}
  <aside class="gel-sidebar">
    <ul class="sidebar-menu">
      <div class="sidebar-section">Général</div>
      <li class="sidebar-item" data-page="clients">📋 Mes clients</li>
      <li class="sidebar-item {{ request()->routeIs('gel-accountant.dashboard') || request()->routeIs('gel-accountant.home') ? 'active' : '' }}" data-page="dashboard">📊 Tableau de bord</li>

      <div class="sidebar-section">Comptabilité</div>
      <li class="sidebar-item has-children" data-dropdown="dd-compta">📒 Comptabilité <span class="arrow">▸</span></li>
      <li class="sidebar-item has-children" data-dropdown="dd-fact">📄 Facturation <span class="arrow">▸</span></li>
      <li class="sidebar-item has-children" data-dropdown="dd-dep">💰 Dépenses <span class="arrow">▸</span></li>
      <li class="sidebar-item has-children" data-dropdown="dd-banque">🏦 Banque <span class="arrow">▸</span></li>

      <div class="sidebar-section">Analyse</div>
      <li class="sidebar-item has-children" data-dropdown="dd-rapports">📊 Rapports <span class="arrow">▸</span></li>

      <div class="sidebar-section">Administration</div>
      <li class="sidebar-item" data-page="equipe">👥 Équipe</li>
      <li class="sidebar-item" data-page="settings">⚙️ Paramètres</li>
    </ul>

    {{-- User Footer --}}
    @php $u = auth()->user(); @endphp
    @if($u)
    <div class="sidebar-footer">
      <div class="avatar" style="width:32px;height:32px;font-size:11px;">{{ strtoupper(substr($u->name ?? $u->email, 0, 2)) }}</div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:600;color:var(--gel-text-primary);">{{ $u->name ?? $u->email }}</div>
        <div style="font-size:11px;color:var(--gel-text-muted);">Cabinet comptable</div>
      </div>
      <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logoutForm').submit();" style="color:var(--gel-text-muted);font-size:16px;">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
    @endif
  </aside>

  {{-- ════════════════════════════════════════════ DROPDOWNS (Niveau 2) ═══ --}}

  {{-- Comptabilité --}}
  <div id="dd-compta" class="nested-dropdown">
    <div class="dd-header">COMPTABILITÉ</div>
    <div class="dd-item" data-page="plan-comptable"><span class="dd-icon">📋</span> Plan comptable</div>
    <div class="dd-item" data-page="ecritures"><span class="dd-icon">📝</span> Saisie d'écritures</div>
    <div class="dd-item" data-page="grand-livre"><span class="dd-icon">📊</span> Grand livre</div>
    <div class="dd-item" data-page="balance"><span class="dd-icon">⚖️</span> Balance</div>
    <div class="dd-item has-children" data-dropdown="dd-etats">
      <span class="dd-icon">📄</span> États financiers <span class="arrow">▸</span>
    </div>
    <div class="dd-divider"></div>
    <div class="dd-item" data-page="journaux"><span class="dd-icon">📋</span> Journaux</div>
    <div class="dd-item" data-page="taches"><span class="dd-icon">✅</span> Tâches</div>
    <div class="dd-item" data-page="workflows"><span class="dd-icon">🤖</span> Workflows</div>
  </div>

  {{-- États financiers (Niveau 3) --}}
  <div id="dd-etats" class="nested-dropdown">
    <div class="dd-header">ÉTATS FINANCIERS</div>
    <div class="dd-item" data-page="bilan"><span class="dd-icon">📋</span> Bilan (Actif/Passif)</div>
    <div class="dd-item" data-page="cr"><span class="dd-icon">📊</span> Compte de résultat</div>
    <div class="dd-item" data-page="sig"><span class="dd-icon">📈</span> SIG</div>
    <div class="dd-item" data-page="tafire"><span class="dd-icon">📑</span> TAFIRE</div>
    <div class="dd-item" data-page="tresorerie"><span class="dd-icon">💰</span> Flux de trésorerie</div>
  </div>

  {{-- Facturation --}}
  <div id="dd-fact" class="nested-dropdown">
    <div class="dd-header">FACTURATION</div>
    <div class="dd-item" data-page="factures"><span class="dd-icon">📄</span> Factures</div>
    <div class="dd-item" data-page="devis"><span class="dd-icon">📝</span> Devis</div>
    <div class="dd-item" data-page="clients"><span class="dd-icon">👥</span> Clients</div>
    <div class="dd-item" data-page="produits"><span class="dd-icon">📦</span> Produits & services</div>
    <div class="dd-item has-children" data-dropdown="dd-fact-plus">
      <span class="dd-icon">⚡</span> Plus <span class="arrow">▸</span>
    </div>
  </div>

  {{-- Facturation > Plus (Niveau 3) --}}
  <div id="dd-fact-plus" class="nested-dropdown">
    <div class="dd-item" data-page="paiements"><span class="dd-icon">💳</span> Paiements reçus</div>
    <div class="dd-item" data-page="factures-recurrentes"><span class="dd-icon">🔄</span> Factures récurrentes</div>
    <div class="dd-item" data-page="relances"><span class="dd-icon">📨</span> Relances</div>
  </div>

  {{-- Dépenses --}}
  <div id="dd-dep" class="nested-dropdown">
    <div class="dd-header">DÉPENSES</div>
    <div class="dd-item" data-page="depenses"><span class="dd-icon">💰</span> Dépenses</div>
    <div class="dd-item" data-page="factures-fournisseurs"><span class="dd-icon">📄</span> Factures fournisseurs</div>
    <div class="dd-item" data-page="bons-commande"><span class="dd-icon">📋</span> Bons de commande</div>
    <div class="dd-item" data-page="fournisseurs"><span class="dd-icon">🏭</span> Fournisseurs</div>
    <div class="dd-divider"></div>
    <div class="dd-item" data-page="notes-frais"><span class="dd-icon">🧾</span> Notes de frais</div>
  </div>

  {{-- Banque --}}
  <div id="dd-banque" class="nested-dropdown">
    <div class="dd-header">BANQUE</div>
    <div class="dd-item" data-page="transactions"><span class="dd-icon">🏦</span> Transactions bancaires</div>
    <div class="dd-item" data-page="rapprochement"><span class="dd-icon">🤝</span> Rapprochement</div>
    <div class="dd-item has-children" data-dropdown="dd-banque-plus">
      <span class="dd-icon">⚙️</span> Réglages <span class="arrow">▸</span>
    </div>
  </div>

  {{-- Banque > Réglages (Niveau 3) --}}
  <div id="dd-banque-plus" class="nested-dropdown">
    <div class="dd-item" data-page="regles-bancaires"><span class="dd-icon">📋</span> Règles bancaires</div>
    <div class="dd-item" data-page="comptes-bancaires"><span class="dd-icon">🏦</span> Comptes bancaires</div>
    <div class="dd-item" data-page="virements"><span class="dd-icon">↔️</span> Virements</div>
  </div>

  {{-- Rapports --}}
  <div id="dd-rapports" class="nested-dropdown">
    <div class="dd-header">RAPPORTS</div>
    <div class="dd-item" data-page="rapports-standards"><span class="dd-icon">📊</span> Rapports standards</div>
    <div class="dd-item" data-page="centre-performance"><span class="dd-icon">🎯</span> Centre de performance</div>
    <div class="dd-item" data-page="rapports-personnalises"><span class="dd-icon">📐</span> Rapports personnalisés</div>
    <div class="dd-divider"></div>
    <div class="dd-item" data-page="rapports-sauvegardes"><span class="dd-icon">💾</span> Rapports sauvegardés</div>
  </div>

  {{-- ════════════════════════════════════════════ CONTENT ═══════════════ --}}
  <main class="gel-content">

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="display:none;" id="gel-flash-message" data-message="{{ session('success') }}" data-type="success"></div>
    @endif
    @if(session('error'))
    <div style="display:none;" id="gel-flash-message" data-message="{{ session('error') }}" data-type="error"></div>
    @endif

    @yield('content')
  </main>

  {{-- ════════════════════════════════════════════ TOAST CONTAINER ═══════ --}}
  <div class="gel-toast-container" id="gelToastContainer"></div>

  {{-- ════════════════════════════════════════════ OVERLAY + SLIDE PANEL ═══ --}}
  <div class="panel-overlay" id="panelOverlay" onclick="closeSlidePanel()"></div>
  <div class="slide-panel" id="slidePanel">
    <div class="panel-header">
      <h3 id="panelTitle">Panel</h3>
      <button class="panel-close" onclick="closeSlidePanel()">✕</button>
    </div>
    <div class="panel-body" id="panelBody">
      <p style="color:var(--gel-text-secondary);">Contenu du panel...</p>
    </div>
  </div>

  {{-- Logout form --}}
  <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

  {{-- ════════════════════════════════════════════ JAVASCRIPT ════════════ --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      'use strict';

      var activeDropdown = null;
      var closeTimer = null;

      // ═══════════════════════════════════════════════
      // POSITIONNEMENT DYNAMIQUE DES DROPDOWNS
      // ═══════════════════════════════════════════════

      function positionDropdown(ddId, triggerEl) {
        var dd = document.getElementById(ddId);
        var trigger = triggerEl;
        if (!dd || !trigger) return;

        var triggerRect = trigger.getBoundingClientRect();
        var ddWidth = dd.offsetWidth || 230;

        var top = triggerRect.top;
        var ddHeight = dd.offsetHeight || 300;
        if (top + ddHeight > window.innerHeight - 20) {
          top = Math.max(10, window.innerHeight - ddHeight - 20);
        }

        var parentDropdown = trigger.closest('.nested-dropdown');
        if (parentDropdown) {
          dd.style.left = (triggerRect.right - 4) + 'px';
        } else {
          dd.style.left = (triggerRect.right - 4) + 'px';
        }

        dd.style.top = top + 'px';
      }

      // ═══════════════════════════════════════════════
      // OUVERTURE / FERMETURE
      // ═══════════════════════════════════════════════

      function openDropdown(id, trigger) {
        var dd = document.getElementById(id);
        if (!dd) return;

        document.querySelectorAll('.nested-dropdown.open').forEach(function(el) {
          if (el.id !== id && !el.contains(trigger)) {
            el.classList.remove('open');
          }
        });

        positionDropdown(id, trigger);
        dd.classList.add('open');
        activeDropdown = id;
      }

      function closeDropdown(id) {
        var dd = document.getElementById(id);
        if (dd) {
          dd.classList.remove('open');
          dd.querySelectorAll('.nested-dropdown.open').forEach(function(child) {
            child.classList.remove('open');
          });
        }
        if (activeDropdown === id) activeDropdown = null;
      }

      window.closeAllDropdowns = function() {
        document.querySelectorAll('.nested-dropdown.open').forEach(function(el) {
          el.classList.remove('open');
        });
        activeDropdown = null;
      };

      // ═══════════════════════════════════════════════
      // SURVOL SIDEBAR → OUVERTURE DROPDOWN
      // ═══════════════════════════════════════════════

      document.querySelectorAll('.sidebar-item.has-children').forEach(function(item) {
        var ddId = item.dataset.dropdown;
        var dd = document.getElementById(ddId);
        if (!dd) return;

        item.addEventListener('mouseenter', function() {
          clearTimeout(closeTimer);
          openDropdown(ddId, this);
        });

        item.addEventListener('mouseleave', function() {
          var ddEl = document.getElementById(ddId);
          closeTimer = setTimeout(function() {
            if (ddEl && !ddEl.matches(':hover') && !ddEl.querySelector(':hover')) {
              closeDropdown(ddId);
            }
          }, 200);
        });
      });

      // ═══════════════════════════════════════════════
      // SURVOL DROPDOWN → MAINTIEN OUVERT
      // ═══════════════════════════════════════════════

      document.querySelectorAll('.nested-dropdown').forEach(function(dd) {
        dd.addEventListener('mouseenter', function() {
          clearTimeout(closeTimer);
        });

        dd.addEventListener('mouseleave', function() {
          var self = this;
          closeTimer = setTimeout(function() {
            self.classList.remove('open');
            self.querySelectorAll('.nested-dropdown.open').forEach(function(child) {
              child.classList.remove('open');
            });
          }, 200);
        });
      });

      // ═══════════════════════════════════════════════
      // SURVOL ITEM AVEC ENFANTS (NIVEAU 3)
      // ═══════════════════════════════════════════════

      document.querySelectorAll('.dd-item.has-children').forEach(function(item) {
        var ddId = item.dataset.dropdown;
        var dd = document.getElementById(ddId);
        if (!dd) return;

        item.addEventListener('mouseenter', function(e) {
          e.stopPropagation();
          clearTimeout(closeTimer);
          positionDropdown(ddId, this);
          dd.classList.add('open');
        });

        item.addEventListener('mouseleave', function() {
          var ddEl = document.getElementById(ddId);
          closeTimer = setTimeout(function() {
            if (ddEl && !ddEl.matches(':hover') && !ddEl.querySelector(':hover')) {
              ddEl.classList.remove('open');
            }
          }, 200);
        });
      });

      // ═══════════════════════════════════════════════
      // CLIC SUR ÉLÉMENT AVEC DATA-PAGE
      // ═══════════════════════════════════════════════

      document.querySelectorAll('[data-page]').forEach(function(item) {
        item.addEventListener('click', function(e) {
          e.stopPropagation();
          var page = this.dataset.page;

          // Activer l'élément cliqué
          document.querySelectorAll('.dd-item.active, .sidebar-item.active').forEach(function(el) {
            el.classList.remove('active');
          });
          this.classList.add('active');

          // Si c'est un dd-item dans un dropdown, activer aussi le parent sidebar
          var parentSidebar = this.closest('.sidebar-item');
          if (parentSidebar) {
            parentSidebar.classList.add('active');
          }

          closeAllDropdowns();
          navigateTo(page);
        });
      });

      // ═══════════════════════════════════════════════
      // FONCTION DE NAVIGATION
      // ═══════════════════════════════════════════════

      window.navigateTo = function(page) {
        var titles = {
          'dashboard': '📊 Tableau de bord',
          'clients': '📋 Mes clients',
          'plan-comptable': '📋 Plan comptable SYSCOHADA',
          'ecritures': '📝 Saisie d\'écritures',
          'grand-livre': '📊 Grand livre',
          'balance': '⚖️ Balance',
          'bilan': '📋 Bilan (Actif/Passif)',
          'cr': '📊 Compte de résultat',
          'sig': '📈 SIG',
          'tafire': '📑 TAFIRE',
          'tresorerie': '💰 Flux de trésorerie',
          'taches': '✅ Tâches',
          'workflows': '🤖 Workflows',
          'journaux': '📋 Journaux',
          'factures': '📄 Factures',
          'devis': '📝 Devis',
          'produits': '📦 Produits & services',
          'paiements': '💳 Paiements reçus',
          'factures-recurrentes': '🔄 Factures récurrentes',
          'relances': '📨 Relances',
          'depenses': '💰 Dépenses',
          'factures-fournisseurs': '📄 Factures fournisseurs',
          'bons-commande': '📋 Bons de commande',
          'fournisseurs': '🏭 Fournisseurs',
          'notes-frais': '🧾 Notes de frais',
          'transactions': '🏦 Transactions bancaires',
          'rapprochement': '🤝 Rapprochement bancaire',
          'regles-bancaires': '📋 Règles bancaires',
          'comptes-bancaires': '🏦 Comptes bancaires',
          'virements': '↔️ Virements',
          'rapports-standards': '📊 Rapports standards',
          'centre-performance': '🎯 Centre de performance',
          'rapports-personnalises': '📐 Rapports personnalisés',
          'rapports-sauvegardes': '💾 Rapports sauvegardés',
          'equipe': '👥 Équipe',
          'settings': '⚙️ Paramètres',
          'profile': '👤 Mon profil'
        };

        var title = titles[page] || '📄 ' + page;

        // Redirection vers la vraie route si elle existe
        var routeMap = {
          'dashboard': '{{ route("gel-accountant.dashboard") }}',
          'clients': '{{ route("gel-accountant.clients") }}',
          'plan-comptable': '{{ route("gel-accountant.comptabilite.plan-comptable") }}',
          'ecritures': '{{ route("gel-accountant.comptabilite.ecritures") }}',
          'grand-livre': '{{ route("gel-accountant.comptabilite.grand-livre") }}',
          'balance': '{{ route("gel-accountant.comptabilite.balance") }}',
          'journaux': '{{ route("gel-accountant.comptabilite.journaux") }}',
          'etats-financiers': '{{ route("gel-accountant.comptabilite.etats-financiers") }}',
          'taches': '{{ route("gel-accountant.tasks.index") }}',
          'workflows': '{{ route("gel-accountant.workflows.index") }}',
          'equipe': '{{ route("gel-accountant.team") }}',
          'settings': '{{ route("gel-accountant.settings") }}',
          'profile': '{{ route("gel-accountant.profile") }}',
        };

        if (routeMap[page]) {
          window.location.href = routeMap[page];
          return;
        }

        // Fallback: contenu simulé
        var content = document.getElementById('content-area');
        if (content) {
          content.innerHTML = `
            <div class="gel-page-header">
              <div>
                <h1 class="gel-page-title">${title}</h1>
              </div>
              <div>
                <button class="gel-btn gel-btn-secondary gel-btn-sm" onclick="showToast('Action test réussie', 'success')">
                  <i class="fas fa-play"></i> Action test
                </button>
                <button class="gel-btn gel-btn-primary gel-btn-sm" style="margin-left:8px;" onclick="openSlidePanel()">
                  <i class="fas fa-plus"></i> Nouveau
                </button>
              </div>
            </div>
            <div style="padding:60px 40px;text-align:center;color:var(--gel-text-secondary);background:var(--gel-sidebar-bg);border-radius:8px;border:2px dashed var(--gel-border);">
              <div style="font-size:56px;margin-bottom:16px;">📄</div>
              <h2 style="color:var(--gel-text-primary);margin-bottom:8px;">${title}</h2>
              <p>Contenu chargé dans la même fenêtre.</p>
              <p style="font-size:12px;margin-top:12px;color:var(--gel-text-muted);">
                <code style="background:white;padding:2px 8px;border-radius:3px;border:1px solid var(--gel-border);">/gel-accountant/${page}</code>
              </p>
            </div>
          `;
        }
      };

      // ═══════════════════════════════════════════════
      // CLIC EN DEHORS → FERME TOUT
      // ═══════════════════════════════════════════════

      document.addEventListener('click', function(e) {
        if (!e.target.closest('.sidebar-menu') &&
            !e.target.closest('.nested-dropdown') &&
            !e.target.closest('.topbar-btn') &&
            !e.target.closest('.btn-go-business') &&
            !e.target.closest('.avatar') &&
            !e.target.closest('.search-bar')) {
          closeAllDropdowns();
        }
      });

      // ═══════════════════════════════════════════════
      // ÉCHAP → FERME TOUT
      // ═══════════════════════════════════════════════

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeAllDropdowns();
          closeSlidePanel();
        }
        if (e.key === 'k' && (e.ctrlKey || e.metaKey)) {
          e.preventDefault();
          var searchInput = document.getElementById('globalSearch');
          if (searchInput) searchInput.focus();
        }
      });

      // ═══════════════════════════════════════════════
      // TOAST SYSTEM
      // ═══════════════════════════════════════════════

      window.showToast = function(message, type) {
        type = type || 'success';
        var container = document.getElementById('gelToastContainer');
        if (!container) return;

        var icons = { 'success': '✅', 'error': '❌', 'warning': '⚠️', 'info': 'ℹ️' };
        var toast = document.createElement('div');
        toast.className = 'gel-toast' + (type === 'error' ? ' gel-toast-error' : type === 'warning' ? ' gel-toast-warning' : type === 'info' ? ' gel-toast-info' : '');
        toast.innerHTML = '<span>' + (icons[type] || '✅') + '</span> ' + message;
        container.appendChild(toast);

        setTimeout(function() {
          toast.classList.add('exit');
          setTimeout(function() { toast.remove(); }, 250);
        }, 4000);
      };

      // ═══════════════════════════════════════════════
      // SLIDE PANEL
      // ═══════════════════════════════════════════════

      window.openSlidePanel = function(title) {
        var panel = document.getElementById('slidePanel');
        var overlay = document.getElementById('panelOverlay');
        var titleEl = document.getElementById('panelTitle');
        if (titleEl && title) titleEl.textContent = title;
        if (panel) panel.classList.add('open');
        if (overlay) overlay.classList.add('open');
      };

      window.closeSlidePanel = function() {
        var panel = document.getElementById('slidePanel');
        var overlay = document.getElementById('panelOverlay');
        if (panel) panel.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
      };

      // ═══════════════════════════════════════════════
      // TOGGLE DROPDOWN (topbar: clic)
      // ═══════════════════════════════════════════════

      window.toggleDropdown = function(id) {
        var dd = document.getElementById(id);
        if (!dd) return;

        var isOpen = dd.classList.contains('open');
        document.querySelectorAll('.nested-dropdown.open').forEach(function(el) {
          if (el.id !== id) {
            el.classList.remove('open');
          }
        });

        if (isOpen) {
          dd.classList.remove('open');
        } else {
          dd.classList.add('open');
          activeDropdown = id;
        }
      };

      // ═══════════════════════════════════════════════
      // RECHERCHE
      // ═══════════════════════════════════════════════

      window.openSearchResults = function() {
        var dd = document.getElementById('searchResults');
        if (dd) dd.classList.add('open');
      };

      window.closeSearchResults = function() {
        var dd = document.getElementById('searchResults');
        if (dd) dd.classList.remove('open');
      };

      // ═══════════════════════════════════════════════
      // FLASH MESSAGE → AUTO TOAST
      // ═══════════════════════════════════════════════

      var flashMsg = document.getElementById('gel-flash-message');
      if (flashMsg) {
        showToast(flashMsg.dataset.message, flashMsg.dataset.type || 'success');
      }
    });
  </script>

  @stack('scripts')
</body>
</html>
