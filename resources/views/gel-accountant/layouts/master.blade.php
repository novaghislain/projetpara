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
      --primary: #2CA01C;
      --primary-hover: #1D7C13;
      --primary-light: #EBF7E9;
      --sidebar-bg: #F4F5F8;
      --sidebar-hover: #EBECEF;
      --sidebar-active: #EBECEF;
      --sidebar-width: 240px;
      --topbar-height: 56px;
      --text-primary: #1F2A44;
      --text-secondary: #6B6C72;
      --text-muted: #9CA3AF;
      --border-color: #D1D5DB;
      --card-bg: #FFFFFF;
      --success: #10B981;
      --danger: #EF4444;
      --warning: #F59E0B;
      --info: #3B82F6;
      --dropdown-shadow: 0 8px 24px rgba(0,0,0,0.15);
      --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    html { font-size: 14px; }
    body {
      font-family: var(--font);
      color: var(--text-primary);
      background: #FFFFFF;
      min-height: 100vh;
    }

    a { text-decoration: none; color: inherit; }
    button { cursor: pointer; font-family: inherit; }

    /* ═══════════════════════════════════════════════════════════════
       TOPBAR
    ═══════════════════════════════════════════════════════════════ */
    .gel-topbar {
      position: fixed; top: 0; left: var(--sidebar-width); right: 0;
      height: var(--topbar-height);
      background: var(--primary);
      border-bottom: 1px solid var(--primary-hover);
      display: flex; align-items: center;
      padding: 0 20px;
      z-index: 1000;
    }

    .topbar-logo {
      font-weight: 700; font-size: 18px;
      color: white; margin-right: 16px;
    }
    .topbar-logo small {
      font-weight: 400; font-size: 12px;
      color: rgba(255,255,255,0.7); margin-left: 6px;
    }

    .btn-go-business {
      display: flex; align-items: center; gap: 6px;
      padding: 6px 12px;
      border: 1px solid rgba(255,255,255,0.4);
      border-radius: 4px; background: transparent; color: white;
      cursor: pointer; font-size: 13px; font-weight: 500;
      position: relative; white-space: nowrap;
      transition: background 120ms;
    }
    .btn-go-business:hover { background: rgba(255,255,255,0.15); }

    .search-bar {
      flex: 1; max-width: 360px; margin: 0 20px; position: relative;
    }
    .search-bar input {
      width: 100%;
      padding: 8px 12px 8px 34px;
      border: 1px solid var(--border-color);
      border-radius: 6px; font-size: 13px;
      background: var(--sidebar-bg);
      transition: all 150ms; outline: none;
    }
    .search-bar input:focus {
      border-color: var(--primary);
      background: white;
      box-shadow: 0 0 0 3px rgba(0,91,172,0.1);
    }
    .search-icon {
      position: absolute; left: 10px; top: 50%;
      transform: translateY(-50%);
      color: var(--text-secondary); font-size: 14px;
      pointer-events: none;
    }
    .search-kbd {
      position: absolute; right: 8px; top: 50%;
      transform: translateY(-50%);
      font-size: 10px; color: var(--text-muted);
      background: var(--sidebar-bg); padding: 1px 5px;
      border-radius: 3px; border: 1px solid var(--border-color);
    }

    .topbar-right {
      display: flex; align-items: center; gap: 4px; margin-left: auto;
    }
    .topbar-btn {
      width: 34px; height: 34px; border: none; background: none;
      border-radius: 50%; cursor: pointer;
      color: white; font-size: 17px;
      display: flex; align-items: center; justify-content: center;
      position: relative; transition: background 120ms;
    }
    .topbar-btn:hover { background: rgba(255,255,255,0.15); }

    .notif-dot::after {
      content: ''; position: absolute; top: 5px; right: 5px;
      width: 7px; height: 7px; background: var(--danger);
      border-radius: 50%; border: 2px solid white;
    }

    .avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: white; color: var(--primary);
      display: flex; align-items: center; justify-content: center;
      font-weight: 600; font-size: 13px; cursor: pointer;
      margin-left: 4px;
    }

    /* ═══════════════════════════════════════════════════════════════
       SIDEBAR
    ═══════════════════════════════════════════════════════════════ */
    .gel-sidebar {
      position: fixed; top: 0; left: 0;
      width: var(--sidebar-width); height: 100vh;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border-color);
      padding-top: var(--topbar-height);
      overflow-y: auto; z-index: 999;
    }

    .btn-nouveau-sidebar {
      width: 100%;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 16px;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: background 150ms, box-shadow 150ms;
    }
    .btn-nouveau-sidebar:hover {
      background: var(--primary-hover);
      box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    }

    .sidebar-menu { list-style: none; padding: 8px; margin: 0; }

    .sidebar-item {
      display: flex; align-items: center;
      padding: 9px 12px; cursor: pointer;
      font-size: 13.5px; color: var(--text-primary);
      border-radius: 4px; margin-bottom: 1px;
      position: relative; user-select: none;
      transition: background 80ms;
    }
    .sidebar-item:hover { background: var(--sidebar-hover); }
    .sidebar-item.active {
      background: var(--primary-light);
      color: var(--sidebar-active); font-weight: 600;
    }
    .sidebar-item .arrow {
      margin-left: auto; font-size: 10px;
      color: var(--text-secondary); transition: none;
    }

    .sidebar-section {
      font-size: 11px; font-weight: 600; color: var(--text-muted);
      text-transform: uppercase; letter-spacing: 0.5px;
      padding: 16px 12px 6px;
    }

    /* ═══════════════════════════════════════════════════════════════
       NESTED DROPDOWN
    ═══════════════════════════════════════════════════════════════ */
    .nested-dropdown {
      position: fixed;
      left: calc(var(--sidebar-width) + 4px);
      top: 100px;
      background: white;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      box-shadow: var(--dropdown-shadow);
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

    .mega-menu {
      position: fixed;
      left: calc(var(--sidebar-width) + 4px);
      top: 60px;
      background: white;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      box-shadow: var(--dropdown-shadow);
      display: flex;
      gap: 32px;
      padding: 24px;
      opacity: 0; visibility: hidden;
      transform: translateY(-4px);
      transition: opacity 120ms ease, visibility 120ms ease, transform 120ms ease;
      z-index: 1100;
      pointer-events: none;
    }
    .mega-menu.open {
      opacity: 1; visibility: visible;
      transform: translateY(0);
      pointer-events: auto;
    }
    .mega-col {
      min-width: 160px;
    }
    .mega-header {
      font-size: 12px; font-weight: 700; color: #1F2A44;
      text-transform: uppercase; letter-spacing: 0.5px;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 12px;
    }
    .mega-item {
      display: flex; align-items: center; gap: 8px;
      padding: 6px 0; cursor: pointer;
      font-size: 13px; color: var(--text-secondary);
      text-decoration: none;
    }
    .mega-item:hover { color: var(--primary); }
    .mega-icon { width: 16px; text-align: center; color: var(--text-muted); font-size: 14px; }

    .dd-header {
      padding: 6px 14px 4px;
      font-size: 11px; font-weight: 600; color: var(--text-muted);
      text-transform: uppercase; letter-spacing: 0.5px;
    }

    .dd-item {
      display: flex; align-items: center;
      padding: 8px 14px; cursor: pointer;
      font-size: 13px; color: var(--text-primary);
      white-space: nowrap; transition: background 80ms;
    }
    .dd-item:hover { background: #F0F4F8; }
    .dd-item.active { background: var(--primary-light); color: var(--primary); font-weight: 600; }
    .dd-item .arrow { margin-left: auto; font-size: 10px; color: var(--text-secondary); }
    .dd-item .dd-icon { width: 20px; text-align: center; margin-right: 8px; font-size: 14px; }

    .dd-divider { height: 1px; background: var(--border-color); margin: 4px 0; }

    /* ═══════════════════════════════════════════════════════════════
       CONTENT
    ═══════════════════════════════════════════════════════════════ */
    .gel-content {
      margin-left: var(--sidebar-width);
      margin-top: var(--topbar-height);
      padding: 28px 32px;
      min-height: calc(100vh - var(--topbar-height));
    }

    .page-header {
      display: flex; align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }
    .page-header h1 {
      font-size: 22px; font-weight: 700; color: var(--text-primary);
    }

    /* ═══════════════════════════════════════════════════════════════
       COMPOSANTS — KPI, CARDS, BOUTONS
    ═══════════════════════════════════════════════════════════════ */
    .kpi-card {
      background: white; border: 1px solid var(--border-color);
      border-radius: 8px; padding: 18px 20px;
    }

    .card {
      background: white; border: 1px solid var(--border-color);
      border-radius: 8px;
    }

    .btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 8px 16px; border-radius: 4px;
      font-size: 13px; font-weight: 600;
      border: none; cursor: pointer;
      transition: all 120ms;
    }
    .btn-sm { padding: 5px 10px; font-size: 12px; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-hover); }
    .btn-secondary {
      background: white; color: var(--text-primary);
      border: 1px solid var(--border-color);
    }
    .btn-secondary:hover { background: var(--sidebar-hover); }

    select {
      padding: 5px 10px; border: 1px solid var(--border-color);
      border-radius: 4px; font-size: 13px;
      background: white; font-family: inherit;
    }

    /* ═══════════════════════════════════════════════════════════════
       TOAST
    ═══════════════════════════════════════════════════════════════ */
    .toast-container {
      position: fixed; top: 68px; right: 20px; z-index: 9999;
      display: flex; flex-direction: column; gap: 8px;
    }
    .toast {
      background: white; border-left: 4px solid var(--success);
      padding: 14px 20px; border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      display: flex; align-items: center; gap: 10px;
      font-size: 13px; min-width: 300px;
      animation: slideInRight 280ms ease forwards;
    }
    .toast.error { border-left-color: var(--danger); }
    .toast.warning { border-left-color: var(--warning); }
    .toast.exit { animation: slideOutRight 200ms ease forwards; }

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
      border-bottom: 1px solid var(--border-color);
    }
    .panel-header h3 { font-size: 17px; font-weight: 600; }
    .panel-close {
      width: 32px; height: 32px; border: none;
      background: none; font-size: 20px;
      cursor: pointer; color: var(--text-secondary);
      border-radius: 50%; display: flex;
      align-items: center; justify-content: center;
    }
    .panel-close:hover { background: var(--sidebar-hover); }
    .panel-body { padding: 24px; }

    /* ═══════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
      :root { --sidebar-width: 0px; }
      .gel-sidebar { display: none; }
      .gel-topbar { left: 0; }
      .gel-content { margin-left: 0; }
      .slide-panel { width: 100%; }
    }
  </style>

  @stack('styles')
</head>
<body>

  {{-- ════════════════════════════════════════════ TOPBAR ═══════════════ --}}
  <header class="gel-topbar">
    <div class="topbar-logo">
      GEL <small>Accountant</small>
    </div>

    {{-- Go To Business --}}
    <div style="position:relative;">
      <button class="btn-go-business" id="btnGoBusiness" onclick="toggleDropdown('dropdownGoBusiness')">
        <i class="fas fa-exchange-alt"></i> Go To GEL Business <i class="fas fa-chevron-down" style="font-size:9px;"></i>
      </button>
      <div class="nested-dropdown" id="dropdownGoBusiness" style="position:absolute;left:0;top:calc(100% + 6px);min-width:300px;">
        <div class="dd-header">Mes clients</div>
        @php
          $cabinetClients = [];
          try { $cabinetClients = \App\Models\Gel\Client::where('cabinet_id', auth()->user()?->cabinet_id)->where('statut', 'actif')->get(); } catch(\Exception $e) {}
        @endphp
        @forelse($cabinetClients as $gc)
        <a href="{{ route('gel-business.dashboard', ['client_id' => $gc->id]) }}" class="dd-item" style="text-decoration:none;">
          <span class="dd-icon">🏢</span> {{ $gc->nom_entreprise }}
        </a>
        @empty
        <div class="dd-item" style="color:var(--text-muted);"><span class="dd-icon">🏢</span> Aucun client actif</div>
        @endforelse
        <div class="dd-divider"></div>
        <a href="{{ route('gel-accountant.clients') }}" class="dd-item" style="text-decoration:none;"><span class="dd-icon">⚙️</span> Gérer les clients</a>
      </div>
    </div>

    {{-- Outils comptables / Flux d'affaires --}}
    <div style="display:flex; gap:8px; margin-left:16px;">
      <button class="btn-go-business">
        <i class="fas fa-briefcase"></i> Outils comptables
      </button>
      <button class="btn-go-business">
        <i class="fas fa-sitemap"></i> Flux d'affaires
      </button>
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
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px 6px;border-bottom:1px solid var(--border-color);">
            <span style="font-weight:600;font-size:14px;">Notifications</span>
            <a href="#" style="font-size:12px;font-weight:500;color:var(--primary);" onclick="showToast('Tout marquer comme lu','success')">Tout marquer</a>
          </div>
          <div class="dd-item" style="gap:10px;white-space:normal;">
            <span>⚠️</span>
            <div><div>Facture impayée - SARL Bénin - 150 000 F</div><div style="font-size:11px;color:var(--text-muted);">Il y a 2 heures</div></div>
          </div>
          <div class="dd-item" style="gap:10px;white-space:normal;">
            <span>✅</span>
            <div><div>Écriture validée - OD-2026-124</div><div style="font-size:11px;color:var(--text-muted);">Il y a 5 heures</div></div>
          </div>
          <div class="dd-item" style="gap:10px;white-space:normal;">
            <span>📅</span>
            <div><div>Échéance TVA dans 5 jours</div><div style="font-size:11px;color:var(--text-muted);">Il y a 1 jour</div></div>
          </div>
          <div class="dd-divider"></div>
          <div class="dd-item" style="justify-content:center;color:var(--primary);font-weight:500;">Voir toutes →</div>
        </div>
      </div>

      <button class="topbar-btn" title="Paramètres"><i class="fas fa-cog"></i></button>

      {{-- User --}}
      @php $authUser = auth()->user(); @endphp
      <div style="position:relative;">
        <div class="avatar" onclick="toggleDropdown('userDropdown')">{{ strtoupper(substr($authUser->name ?? $authUser->email ?? 'U', 0, 2)) }}</div>
        <div class="nested-dropdown" id="userDropdown"
             style="position:absolute;right:0;left:auto;top:calc(100% + 6px);min-width:220px;">
          <div style="padding:12px 14px;border-bottom:1px solid var(--border-color);">
            <div style="font-weight:600;">{{ $authUser->name ?? 'Utilisateur' }}</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $authUser->email }}</div>
            <div style="font-size:11px;color:var(--primary);font-weight:500;margin-top:2px;">Comptable</div>
          </div>
          <a href="{{ route('gel-accountant.profile') }}" class="dd-item" style="text-decoration:none;"><span class="dd-icon">👤</span> Mon profil</a>
          <a href="{{ route('gel-accountant.settings') }}" class="dd-item" style="text-decoration:none;"><span class="dd-icon">⚙️</span> Paramètres</a>
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
    <div style="padding: 16px 16px 8px;">
      <button class="btn-nouveau-sidebar" onclick="toggleDropdown('dropdownNouveauSidebar')">
        <i class="fas fa-plus"></i> Nouveau
      </button>
      <div class="mega-menu" id="dropdownNouveauSidebar">
        <!-- CLIENTS -->
        <div class="mega-col">
          <div class="mega-header">CLIENTS</div>
          <a href="{{ route('gel-accountant.factures.create') }}" class="mega-item"><i class="fas fa-file-invoice mega-icon"></i> Facture</a>
          <a href="{{ route('gel-accountant.payments.create') }}" class="mega-item"><i class="fas fa-hand-holding-usd mega-icon"></i> Recevez le paiement</a>
          <a href="{{ route('gel-accountant.declaration.create') }}" class="mega-item"><i class="fas fa-file-alt mega-icon"></i> Déclaration</a>
          <a href="{{ route('gel-accountant.estimation.create') }}" class="mega-item"><i class="fas fa-calculator mega-icon"></i> Estimation</a>
          <a href="{{ route('gel-accountant.sales-order.create') }}" class="mega-item"><i class="fas fa-shopping-cart mega-icon"></i> Commande de vente</a>
          <a href="{{ route('gel-accountant.credit-note.create') }}" class="mega-item"><i class="fas fa-file-invoice-dollar mega-icon"></i> Note de crédit</a>
          <a href="{{ route('gel-accountant.sales-receipt.create') }}" class="mega-item"><i class="fas fa-receipt mega-icon"></i> Récépissé de vente</a>
          <a href="{{ route('gel-accountant.refund-receipt.create') }}" class="mega-item"><i class="fas fa-undo mega-icon"></i> Remboursement...</a>
          <a href="{{ route('gel-accountant.delayed-credit.create') }}" class="mega-item"><i class="fas fa-clock mega-icon"></i> Crédit retardé</a>
          <a href="{{ route('gel-accountant.delayed-charge.create') }}" class="mega-item"><i class="fas fa-hourglass-half mega-icon"></i> Charge retardée</a>
          <a href="{{ route('gel-accountant.client.create') }}" class="mega-item" style="margin-top: 8px;"><i class="fas fa-user-plus mega-icon"></i> Ajouter un client</a>
        </div>
        
        <!-- FOURNISSEURS -->
        <div class="mega-col">
          <div class="mega-header">FOURNISSEURS</div>
          <a href="{{ route('gel-accountant.expenses.create') }}" class="mega-item"><i class="fas fa-money-bill-wave mega-icon"></i> Dépenses</a>
          <a href="{{ route('gel-accountant.check.create') }}" class="mega-item"><i class="fas fa-money-check mega-icon"></i> Chèque</a>
          <a href="{{ route('gel-accountant.bills.create') }}" class="mega-item"><i class="fas fa-file-invoice mega-icon"></i> Bill</a>
          <a href="{{ route('gel-accountant.pay-bills') }}" class="mega-item"><i class="fas fa-credit-card mega-icon"></i> Payer les factures</a>
          <a href="{{ route('gel-accountant.purchase-orders.create') }}" class="mega-item"><i class="fas fa-shopping-basket mega-icon"></i> Bon de commande</a>
          <a href="{{ route('gel-accountant.receive-item') }}" class="mega-item"><i class="fas fa-box-open mega-icon"></i> Réception de l'article</a>
          <a href="{{ route('gel-accountant.vendor-credit.create') }}" class="mega-item"><i class="fas fa-tags mega-icon"></i> Crédit fournisseur</a>
          <a href="{{ route('gel-accountant.credit-card-credit.create') }}" class="mega-item"><i class="fas fa-credit-card mega-icon"></i> Crédit de carte...</a>
          <a href="{{ route('gel-accountant.vendors.create') }}" class="mega-item" style="margin-top: 8px;"><i class="fas fa-truck-loading mega-icon"></i> Ajouter un fournisseur</a>
        </div>

        <!-- ÉQUIPE -->
        <div class="mega-col">
          <div class="mega-header">ÉQUIPE</div>
          <a href="{{ route('gel-accountant.single-time-activity') }}" class="mega-item"><i class="fas fa-stopwatch mega-icon"></i> Activité à durée unique</a>
          <a href="{{ route('gel-accountant.weekly-timesheet') }}" class="mega-item"><i class="fas fa-calendar-alt mega-icon"></i> Feuille de temps...</a>
          <a href="{{ route('gel-accountant.review-time') }}" class="mega-item"><i class="fas fa-history mega-icon"></i> Temps de révision</a>
        </div>

        <!-- AUTRE -->
        <div class="mega-col">
          <div class="mega-header">AUTRE</div>
          <a href="{{ route('gel-accountant.task.create') }}" class="mega-item"><i class="fas fa-tasks mega-icon"></i> Tâche</a>
          <a href="{{ route('gel-accountant.bank-deposit') }}" class="mega-item"><i class="fas fa-university mega-icon"></i> Dépôt bancaire</a>
          <a href="{{ route('gel-accountant.transfer') }}" class="mega-item"><i class="fas fa-exchange-alt mega-icon"></i> Transfert</a>
          <a href="{{ route('gel-accountant.journal-entry') }}" class="mega-item"><i class="fas fa-book mega-icon"></i> Entrée de journal</a>
          <a href="{{ route('gel-accountant.inventory-adjustment') }}" class="mega-item"><i class="fas fa-boxes mega-icon"></i> Inventaire...</a>
          <a href="{{ route('gel-accountant.pay-credit-card') }}" class="mega-item"><i class="fas fa-credit-card mega-icon"></i> Payer la carte...</a>
          <a href="{{ route('gel-accountant.add-product') }}" class="mega-item" style="margin-top: 8px;"><i class="fas fa-plus-circle mega-icon"></i> Ajouter un produit...</a>
        </div>
      </div>
    </div>

    <ul class="sidebar-menu">
      <div class="sidebar-section">VOTRE PRATIQUE</div>
      <li class="sidebar-item" data-route="clients"><i class="fas fa-users dd-icon"></i> Clients</li>
      <li class="sidebar-item" data-route="travail"><i class="fas fa-list dd-icon"></i> Travail</li>
      <li class="sidebar-item" data-route="equipe"><i class="fas fa-user-friends dd-icon"></i> Équipe</li>
      <li class="sidebar-item" data-route="invitations"><i class="fas fa-envelope-open-text dd-icon"></i> Invitations</li>
      <li class="sidebar-item" data-route="formation">
        <i class="fas fa-graduation-cap dd-icon"></i> Formation 
        <span style="margin-left:auto;background:#EBF7E9;color:#2CA01C;font-size:9px;padding:2px 6px;border-radius:4px;font-weight:700;">NEW</span>
      </li>
      <li class="sidebar-item" data-route="apps"><i class="fas fa-th-large dd-icon"></i> Apps</li>

      <div class="sidebar-section" style="margin-top:16px;">MARQUE-PAGES</div>
      <li class="sidebar-item" data-route="rapports-standards"><i class="fas fa-chart-bar dd-icon"></i> Rapports standards</li>
      <li class="sidebar-item" data-route="transactions"><i class="fas fa-exchange-alt dd-icon"></i> Transactions bancaires</li>
      <li class="sidebar-item" data-route="rapprochement"><i class="fas fa-check dd-icon"></i> Réconcilier</li>
      <li class="sidebar-item" data-route="plan-comptable"><i class="fas fa-sitemap dd-icon"></i> Graphique des comptes</li>
      <li class="sidebar-item" data-route="ecritures"><i class="fas fa-pen dd-icon"></i> Écritures</li>
      <li class="sidebar-item" style="color:var(--primary);"><i class="fas fa-pencil-alt dd-icon"></i> Modifier le signet</li>
    </ul>

    <div style="position: absolute; bottom: 0; left: 0; width: 100%; border-top: 1px solid var(--border-color); padding: 12px 16px; background: var(--sidebar-bg); display: flex; align-items: center; cursor: pointer;">
      <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; margin-right: 12px;">
        {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email ?? 'U', 0, 2)) }}
      </div>
      <div style="font-size: 13px; font-weight: 600; color: var(--text-primary); flex: 1;">
        {{ explode(' ', auth()->user()->name ?? 'Utilisateur')[0] }}
      </div>
      <i class="fas fa-sign-out-alt" style="color: var(--text-muted); font-size: 14px;" onclick="event.preventDefault();document.getElementById('logoutForm').submit();" title="Déconnexion"></i>
    </div>
  </aside>

  {{-- ════════════════════════════════════════════ DROPDOWNS (Niveau 2) ═══ --}}

  {{-- Comptabilité --}}
  <div id="dd-compta" class="nested-dropdown">
    <div class="dd-header">COMPTABILITÉ</div>
    <div class="dd-item" data-route="plan-comptable"><span class="dd-icon">📋</span> Plan comptable</div>
    <div class="dd-item" data-route="ecritures"><span class="dd-icon">📝</span> Saisie d'écritures</div>
    <div class="dd-item" data-route="grand-livre"><span class="dd-icon">📊</span> Grand livre</div>
    <div class="dd-item" data-route="balance"><span class="dd-icon">⚖️</span> Balance</div>
    <div class="dd-item has-children" data-dropdown="dd-etats">
      <span class="dd-icon">📄</span> États financiers <span class="arrow">▸</span>
    </div>
    <div class="dd-divider"></div>
    <div class="dd-item" data-route="journaux"><span class="dd-icon">📋</span> Journaux</div>
    <div class="dd-item" data-route="exercices"><span class="dd-icon">🏠</span> Exercices comptables</div>
  </div>

  {{-- États financiers (Niveau 3) --}}
  <div id="dd-etats" class="nested-dropdown">
    <div class="dd-header">ÉTATS FINANCIERS</div>
    <div class="dd-item" data-route="bilan"><span class="dd-icon">📋</span> Bilan (Actif/Passif)</div>
    <div class="dd-item" data-route="cr"><span class="dd-icon">📊</span> Compte de résultat</div>
    <div class="dd-item" data-route="sig"><span class="dd-icon">📈</span> SIG</div>
    <div class="dd-item" data-route="tafire"><span class="dd-icon">📑</span> TAFIRE</div>
    <div class="dd-item" data-route="tresorerie"><span class="dd-icon">💰</span> Flux de trésorerie</div>
  </div>

  {{-- Facturation --}}
  <div id="dd-fact" class="nested-dropdown">
    <div class="dd-header">FACTURATION</div>
    <div class="dd-item" data-route="factures"><span class="dd-icon">📄</span> Factures</div>
    <div class="dd-item" data-route="devis"><span class="dd-icon">📝</span> Devis</div>
    <div class="dd-item" data-route="clients"><span class="dd-icon">👥</span> Clients</div>
    <div class="dd-item" data-route="produits"><span class="dd-icon">📦</span> Produits & services</div>
    <div class="dd-item has-children" data-dropdown="dd-fact-plus">
      <span class="dd-icon">⚡</span> Plus <span class="arrow">▸</span>
    </div>
  </div>

  {{-- Facturation > Plus (Niveau 3) --}}
  <div id="dd-fact-plus" class="nested-dropdown">
    <div class="dd-item" data-route="paiements"><span class="dd-icon">💳</span> Paiements reçus</div>
    <div class="dd-item" data-route="factures-recurrentes"><span class="dd-icon">🔄</span> Factures récurrentes</div>
    <div class="dd-item" data-route="relances"><span class="dd-icon">📨</span> Relances</div>
  </div>

  {{-- Dépenses --}}
  <div id="dd-dep" class="nested-dropdown">
    <div class="dd-header">DÉPENSES</div>
    <div class="dd-item" data-route="depenses"><span class="dd-icon">💰</span> Dépenses</div>
    <div class="dd-item" data-route="factures-fournisseurs"><span class="dd-icon">📄</span> Factures fournisseurs</div>
    <div class="dd-item" data-route="bons-commande"><span class="dd-icon">📋</span> Bons de commande</div>
    <div class="dd-item" data-route="fournisseurs"><span class="dd-icon">🏭</span> Fournisseurs</div>
    <div class="dd-divider"></div>
    <div class="dd-item" data-route="notes-frais"><span class="dd-icon">🧾</span> Notes de frais</div>
  </div>

  {{-- Banque --}}
  <div id="dd-banque" class="nested-dropdown">
    <div class="dd-header">BANQUE</div>
    <div class="dd-item" data-route="transactions"><span class="dd-icon">🏦</span> Transactions bancaires</div>
    <div class="dd-item" data-route="rapprochement"><span class="dd-icon">🤝</span> Rapprochement</div>
    <div class="dd-item has-children" data-dropdown="dd-banque-plus">
      <span class="dd-icon">⚙️</span> Réglages <span class="arrow">▸</span>
    </div>
  </div>

  {{-- Banque > Réglages (Niveau 3) --}}
  <div id="dd-banque-plus" class="nested-dropdown">
    <div class="dd-item" data-route="regles-bancaires"><span class="dd-icon">📋</span> Règles bancaires</div>
    <div class="dd-item" data-route="comptes-bancaires"><span class="dd-icon">🏦</span> Comptes bancaires</div>
    <div class="dd-item" data-route="virements"><span class="dd-icon">↔️</span> Virements</div>
  </div>

  {{-- Rapports --}}
  <div id="dd-rapports" class="nested-dropdown">
    <div class="dd-header">RAPPORTS</div>
    <div class="dd-item" data-route="rapports-standards"><span class="dd-icon">📊</span> Rapports standards</div>
    <div class="dd-item" data-route="centre-performance"><span class="dd-icon">🎯</span> Centre de performance</div>
    <div class="dd-item" data-route="rapports-personnalises"><span class="dd-icon">📐</span> Rapports personnalisés</div>
    <div class="dd-divider"></div>
    <div class="dd-item" data-route="rapports-sauvegardes"><span class="dd-icon">💾</span> Rapports sauvegardés</div>
  </div>

  {{-- ════════════════════════════════════════════ CONTENT ═══════════════ --}}
  <main class="gel-content" id="content-area">

    {{-- Page Header --}}
    <div class="page-header">
      <h1>📊 Tableau de bord</h1>
      <div>
        <span style="color:var(--text-secondary);font-size:13px;">Aujourd'hui : {{ date('d/m/Y') }}</span>
      </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:12px;margin-bottom:24px;align-items:center;flex-wrap:wrap;">
      <span style="font-weight:500;font-size:13px;">Entreprise :</span>
      <select>
        <option>Toutes les entreprises</option>
        <option>SARL Bénin Tech</option>
        <option>Ets Afrique Express</option>
        <option>Global SARL</option>
      </select>
      <span style="font-weight:500;font-size:13px;margin-left:8px;">Période :</span>
      <select>
        <option>Ce mois</option>
        <option>Ce trimestre</option>
        <option>Cette année</option>
      </select>
    </div>

    {{-- KPIs --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
      <div class="kpi-card">
        <div style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;">REVENUS</div>
        <div style="font-size:24px;font-weight:700;margin:8px 0 4px;">CFA 2 500 000</div>
        <div style="font-size:13px;color:var(--success);">+15% vs N-1</div>
      </div>
      <div class="kpi-card">
        <div style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;">DÉPENSES</div>
        <div style="font-size:24px;font-weight:700;margin:8px 0 4px;">CFA 1 800 000</div>
        <div style="font-size:13px;color:var(--danger);">+5% vs N-1</div>
      </div>
      <div class="kpi-card">
        <div style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;">BÉNÉFICE</div>
        <div style="font-size:24px;font-weight:700;margin:8px 0 4px;">CFA 700 000</div>
        <div style="font-size:13px;color:var(--success);">+25% vs N-1</div>
      </div>
      <div class="kpi-card">
        <div style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;">CLIENTS</div>
        <div style="font-size:24px;font-weight:700;margin:8px 0 4px;">12</div>
        <div style="font-size:13px;color:var(--success);">+3 ce mois</div>
      </div>
    </div>

    {{-- Graphiques --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
      <div class="card" style="padding:20px;">
        <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">📈 Évolution des revenus</h3>
        <div style="height:180px;background:#F0F4F8;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);">
          Graphique courbe (12 mois glissants)
        </div>
      </div>
      <div class="card" style="padding:20px;">
        <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">🥧 Dépenses par catégorie</h3>
        <div style="height:180px;background:#F0F4F8;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);">
          Graphique camembert
        </div>
      </div>
    </div>

    {{-- Activités + Alertes --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div class="card" style="padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <h3 style="font-size:15px;font-weight:600;">⚡ Activités récentes</h3>
          <a href="#" style="font-size:12px;color:var(--primary);font-weight:500;">Voir tout →</a>
        </div>
        <div style="display:flex;flex-direction:column;">
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #F0F4F8;">
            <span>📄 Facture émise - Client A</span>
            <span style="font-weight:500;">CFA 150 000</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #F0F4F8;">
            <span>💳 Paiement reçu - Client B</span>
            <span style="font-weight:500;color:var(--success);">CFA 300 000</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #F0F4F8;">
            <span>📦 Facture fournisseur Énerco</span>
            <span style="font-weight:500;color:var(--danger);">CFA 45 000</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0;">
            <span>💰 OD-2026-001 - Apport capital</span>
            <span style="font-weight:500;">CFA 500 000</span>
          </div>
        </div>
      </div>
      <div class="card" style="padding:20px;">
        <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">⚠️ Alertes</h3>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#FFFBEB;border-radius:6px;border-left:3px solid var(--warning);">
            <span>⚠️</span>
            <span style="font-size:13px;">3 factures clients en retard de paiement</span>
          </div>
          <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#FEF2F2;border-radius:6px;border-left:3px solid var(--danger);">
            <span>🚨</span>
            <span style="font-size:13px;">1 écriture en brouillon en attente de validation</span>
          </div>
          <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#ECFDF5;border-radius:6px;border-left:3px solid var(--success);">
            <span>✅</span>
            <span style="font-size:13px;">Tous les comptes bancaires sont rapprochés</span>
          </div>
        </div>
      </div>
    </div>
  </main>

  {{-- ════════════════════════════════════════════ TOAST ════════════════ --}}
  <div class="toast-container" id="toastContainer"></div>

  {{-- ════════════════════════════════════════════ OVERLAY + SLIDE PANEL ═══ --}}
  <div class="panel-overlay" id="panelOverlay" onclick="closeSlidePanel()"></div>
  <div class="slide-panel" id="slidePanel">
    <div class="panel-header">
      <h3 id="panelTitle">Panel</h3>
      <button class="panel-close" onclick="closeSlidePanel()">✕</button>
    </div>
    <div class="panel-body" id="panelBody">
      <p style="color:var(--text-secondary);">Contenu du panel...</p>
    </div>
  </div>

  {{-- Logout form --}}
  <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

  {{-- ════════════════════════════════════════════ JAVASCRIPT ════════════ --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      'use strict';

      let activeDropdown = null;
      let closeTimer = null;

      // ═══════════════════════════════════════════════
      // POSITIONNEMENT DYNAMIQUE DES DROPDOWNS
      // ═══════════════════════════════════════════════

      function positionDropdown(ddId, triggerEl) {
        const dd = document.getElementById(ddId);
        const trigger = triggerEl;
        if (!dd || !trigger) return;

        const triggerRect = trigger.getBoundingClientRect();
        const ddWidth = dd.offsetWidth || 230;

        // Position verticale : alignée sur le trigger
        let top = triggerRect.top;
        // Si le dropdown dépasse en bas, le remonter
        const ddHeight = dd.offsetHeight || 300;
        if (top + ddHeight > window.innerHeight - 20) {
          top = Math.max(10, window.innerHeight - ddHeight - 20);
        }

        // Vérifier si c'est un niveau 3 (le trigger est dans un dropdown)
        const parentDropdown = trigger.closest('.nested-dropdown');
        if (parentDropdown) {
          // Niveau 3 : à droite de l'élément dans le dropdown
          dd.style.left = (triggerRect.right - 4) + 'px';
        } else {
          // Niveau 2 : à droite de la sidebar
          dd.style.left = (triggerRect.right - 4) + 'px';
        }

        dd.style.top = top + 'px';
      }

      // ═══════════════════════════════════════════════
      // OUVERTURE / FERMETURE
      // ═══════════════════════════════════════════════

      function openDropdown(id, trigger) {
        const dd = document.getElementById(id);
        if (!dd) return;

        // Fermer les autres dropdowns ouverts (sauf celui-ci et ses parents)
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
        const dd = document.getElementById(id);
        if (dd) {
          dd.classList.remove('open');
          // Fermer aussi les enfants
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

      var sidebarItems = document.querySelectorAll('.sidebar-item.has-children');
      sidebarItems.forEach(function(item) {
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
            // Ne fermer que si la souris n'est pas sur un parent
            self.classList.remove('open');
            // Fermer aussi les enfants
            self.querySelectorAll('.nested-dropdown.open').forEach(function(child) {
              child.classList.remove('open');
            });
          }, 200);
        });
      });

      // ═══════════════════════════════════════════════
      // SURVOL ITEM AVEC ENFANTS DANS UN DROPDOWN (NIVEAU 3)
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
      // CLIC SUR ÉLÉMENT AVEC DATA-ROUTE → NAVIGATION
      // ═══════════════════════════════════════════════

      document.querySelectorAll('.sidebar-item[data-route], .dd-item[data-route]').forEach(function(item) {
        item.addEventListener('click', function(e) {
          e.stopPropagation();
          var route = this.dataset.route;

          // Retirer les classes actives
          document.querySelectorAll('.sidebar-item.active, .dd-item.active').forEach(function(el) {
            el.classList.remove('active');
          });

          // Activer l'élément cliqué
          this.classList.add('active');

          // Si c'est un dd-item, activer aussi le parent sidebar-item
          var parentSidebar = this.closest('.sidebar-item');
          if (parentSidebar && parentSidebar.matches('.sidebar-item')) {
            document.querySelectorAll('.sidebar-item.active').forEach(function(el) {
              if (el !== parentSidebar) el.classList.remove('active');
            });
            parentSidebar.classList.add('active');
          }

          // Fermer tous les dropdowns
          closeAllDropdowns();

          // Mettre à jour le contenu
          navigateTo(route);
        });
      });

      // ═══════════════════════════════════════════════
      // FONCTION DE NAVIGATION
      // ═══════════════════════════════════════════════

      window.navigateTo = function(route) {
        var content = document.getElementById('content-area');
        if (!content) return;

        var titles = {
          'dashboard': '📊 Tableau de bord',
          'clients': '📋 Mes clients',
          'plan-comptable': '📋 Plan comptable SYSCOHADA',
          'ecritures': '📝 Saisie d\'écritures',
          'grand-livre': '📊 Grand livre',
          'balance': '⚖️ Balance',
          'bilan': '📋 Bilan (Actif/Passif)',
          'cr': '📊 Compte de résultat',
          'sig': '📈 SIG - Soldes Intermédiaires de Gestion',
          'tafire': '📑 TAFIRE',
          'tresorerie': '💰 Flux de trésorerie',
          'exercices': '🏠 Exercices comptables',
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
          'parametres': '⚙️ Paramètres',
          'client-1': '🏢 SARL Bénin Tech',
          'client-2': '🏢 Ets Afrique Express',
          'client-3': '🏢 Global SARL',
          'gestion-clients': '⚙️ Gérer les clients',
          'invitations': '✉️ Invitations'
        };

        var routeMap = {
          'dashboard': '{{ route("gel-accountant.dashboard") }}',
          'clients': '{{ route("gel-accountant.clients") }}',
          'gestion-clients': '{{ route("gel-accountant.clients") }}',
          'plan-comptable': '{{ route("gel-accountant.comptabilite.plan-comptable") }}',
          'ecritures': '{{ route("gel-accountant.comptabilite.ecritures") }}',
          'grand-livre': '{{ route("gel-accountant.comptabilite.grand-livre") }}',
          'balance': '{{ route("gel-accountant.comptabilite.balance") }}',
          'journaux': '{{ route("gel-accountant.comptabilite.journaux") }}',
          'etats-financiers': '{{ route("gel-accountant.comptabilite.etats-financiers") }}',
          'taches': '{{ route("gel-accountant.tasks.index") }}',
          'workflows': '{{ route("gel-accountant.workflows.index") }}',
          'equipe': '{{ route("gel-accountant.team") }}',
          'invitations': '{{ route("gel-accountant.invitations") }}',
          'parametres': '{{ route("gel-accountant.settings") }}',
          'profile': '{{ route("gel-accountant.profile") }}',
        };

        if (routeMap[route]) {
          window.location.href = routeMap[route];
          return;
        }

        var title = titles[route] || '📄 ' + route.charAt(0).toUpperCase() + route.slice(1);

        content.innerHTML = `
          <div class="page-header">
            <h1>${title}</h1>
            <div>
              <button class="btn btn-secondary btn-sm" onclick="showToast('Action test réussie', 'success')">
                <i class="fas fa-play"></i> Action test
              </button>
              <button class="btn btn-primary btn-sm" style="margin-left:8px;" onclick="openSlidePanel()">
                <i class="fas fa-plus"></i> Nouveau
              </button>
            </div>
          </div>
          <div style="padding:60px 40px;text-align:center;color:var(--text-secondary);background:var(--sidebar-bg);border-radius:8px;border:2px dashed var(--border-color);">
            <div style="font-size:56px;margin-bottom:16px;">
              ${title.includes('📊') ? '📊' : title.includes('📋') ? '📋' : title.includes('📝') ? '📝' : title.includes('📄') ? '📄' : title.includes('💰') ? '💰' : title.includes('🏦') ? '🏦' : '📄'}
            </div>
            <h2 style="color:var(--text-primary);margin-bottom:8px;">${title}</h2>
            <p>Contenu chargé dans la même fenêtre.</p>
            <p style="font-size:12px;margin-top:12px;color:var(--text-muted);">
              <code style="background:white;padding:2px 8px;border-radius:3px;border:1px solid var(--border-color);">/gel-accountant/${route}</code>
            </p>
            <button class="btn btn-secondary btn-sm" style="margin-top:16px;" onclick="showToast('${title} chargé avec succès', 'success')">
              <i class="fas fa-check-circle"></i> Tester notification
            </button>
          </div>
          <div style="margin-top:20px;">
            <div class="card" style="padding:20px;">
              <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;">Aide-mémoire</h3>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;font-size:13px;color:var(--text-secondary);">
                <div><kbd style="background:#F0F4F8;padding:2px 6px;border-radius:3px;border:1px solid var(--border-color);">Ctrl+K</kbd> Recherche rapide</div>
                <div><kbd style="background:#F0F4F8;padding:2px 6px;border-radius:3px;border:1px solid var(--border-color);">Échap</kbd> Fermer les menus</div>
                <div><kbd style="background:#F0F4F8;padding:2px 6px;border-radius:3px;border:1px solid var(--border-color);">Survol</kbd> Menus déroulants</div>
              </div>
            </div>
          </div>
        `;
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
        var container = document.getElementById('toastContainer');
        if (!container) return;

        var icons = { 'success': '✅', 'error': '❌', 'warning': '⚠️', 'info': 'ℹ️' };
        var toast = document.createElement('div');
        toast.className = 'toast' + (type === 'error' ? ' error' : type === 'warning' ? ' warning' : '');
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
      // TOGGLE DROPDOWN (pour topbar: clic)
      // ═══════════════════════════════════════════════

      window.toggleDropdown = function(id) {
        var dd = document.getElementById(id);
        if (!dd) return;

        var isOpen = dd.classList.contains('open');

        // Fermer les autres dropdowns ouverts (sauf les nested-dropdown classiques)
        document.querySelectorAll('.nested-dropdown.open').forEach(function(el) {
          if (el.id !== id && el.style.position !== 'fixed') {
            el.classList.remove('open');
          }
        });

        if (isOpen) {
          dd.classList.remove('open');
        } else {
          // Positionner le dropdown
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

      var flashMsg = document.getElementById('gelFlashMessage');
      if (flashMsg) {
        var message = flashMsg.dataset.message || '';
        var type = flashMsg.dataset.type || 'success';
        if (message) showToast(message, type);
      }

      console.log('✅ GEL Accountant ready — nested dropdown menu activé');
    });
  </script>

  @stack('scripts')
</body>
</html>
