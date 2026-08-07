<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'GEL Communication') — Pôle Communication</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- PWA Manifest & Theme Color -->
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#163A5E">
  <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --sec-primary: #7c3aed;
      --sec-primary-dark: #5b21b6;
      --sec-primary-light: #f5f3ff;
      --sec-sidebar-bg: #4c1d95;
      --sec-sidebar-hover: rgba(255,255,255,0.1);
      --sec-sidebar-active-bg: rgba(124,58,237,0.2);
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

    /* Client switcher */
    .sec-client-switcher {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 6px;
      padding: 5px 12px;
      cursor: pointer;
      font-size: 13px;
      color: white;
      max-width: 240px;
      position: relative;
    }

    .sec-client-switcher:hover {
      background: rgba(255, 255, 255, 0.25);
    }

    .sec-client-switcher .name {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      flex: 1;
    }

    .client-dropdown {
      position: absolute;
      top: calc(100% + 6px);
      left: 0;
      min-width: 260px;
      background: white;
      border: 1px solid var(--sec-border);
      border-radius: 8px;
      box-shadow: var(--sec-shadow);
      z-index: 200;
      display: none;
      padding: 6px 0;
      max-height: 320px;
      overflow-y: auto;
    }

    .client-dropdown.open {
      display: block;
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

    /* Notification Badge */
    .sec-topbar-btn {
      position: relative;
    }

    .notif-badge {
      position: absolute;
      top: -2px;
      right: -2px;
      background: #EF4444;
      color: white;
      font-size: 9px;
      font-weight: 700;
      padding: 2px 5px;
      border-radius: 10px;
      border: 2px solid var(--sec-primary);
      display: none;
    }

    .client-dd-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 14px;
      cursor: pointer;
      font-size: 13px;
      color: var(--sec-text);
    }

    .client-dd-item:hover {
      background: #F8FAFC;
    }

    .client-dd-item.active {
      background: var(--sec-primary-light);
      color: var(--sec-primary);
      font-weight: 600;
    }

    .client-dd-avatar {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--sec-primary);
      color: white;
      font-size: 11px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
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
      background: linear-gradient(135deg, #0a2540, #1e3a8a);
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
      color: #0a2540;
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

    .sec-page-sub {
      font-size: 12px;
      color: var(--sec-text-muted);
      margin-top: 2px;
    }

    /* ─── AVATAR INITIALS ─── */
    .sec-initials {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--sec-primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
      flex-shrink: 0;
    }

    /* ─── FORM ─── */
    .sec-form-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 14px;
    }

    .sec-form-group label {
      font-size: 12px;
      font-weight: 600;
      color: var(--sec-text);
      margin-bottom: 5px;
    }

    .sec-form-control,
    .sec-form-select {
      padding: 8px 12px;
      border: 1px solid var(--sec-border);
      border-radius: 5px;
      font-size: 13px;
      font-family: inherit;
      transition: border-color 120ms;
      width: 100%;
      background: white;
    }

    .sec-form-control:focus,
    .sec-form-select:focus {
      outline: none;
      border-color: var(--sec-primary);
      box-shadow: 0 0 0 3px rgba(13, 148, 136, .1);
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
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      cursor: pointer;
      font-size: 13px;
      color: var(--sec-text);
    }

    .sec-dd-item:hover {
      background: #F8FAFC;
    }

    .sec-dd-divider {
      height: 1px;
      background: var(--sec-border);
      margin: 4px 0;
    }

    /* ─── TOAST (IA Dynamic Toast — Positioned at Bottom Right) ─── */
    .sec-toast-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 99999;
      display: flex;
      flex-direction: column;
      gap: 10px;
      pointer-events: none;
    }

    .sec-toast {
      pointer-events: auto;
      position: relative;
      background: var(--sec-primary);
      color: #ffffff;
      padding: 12px 16px;
      border-radius: 8px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 13px;
      font-weight: 500;
      min-width: 250px;
      max-width: 350px;
      overflow: hidden;
      animation: secToastSlideUp 300ms cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .sec-toast-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 3px;
      background: rgba(255, 255, 255, 0.3);
      width: 100%;
      animation: secToastProgress 1.5s linear forwards;
    }

    .sec-toast-icon-wrapper {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      flex-shrink: 0;
    }

    .sec-toast-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .sec-toast-message {
      color: #ffffff;
      line-height: 1.4;
    }

    .sec-toast.err,
    .sec-toast.error {
      background: #EF4444;
    }

    .sec-toast.warn,
    .sec-toast.warning {
      background: #F59E0B;
    }

    .sec-toast.info {
      background: #3B82F6;
    }

    .sec-toast.exit {
      animation: secToastSlideDown 250ms cubic-bezier(0.4, 0, 1, 1) forwards;
    }

    @keyframes secToastSlideUp {
      from {
        transform: translateY(40px) scale(0.95);
        opacity: 0;
      }

      to {
        transform: translateY(0) scale(1);
        opacity: 1;
      }
    }

    @keyframes secToastSlideDown {
      from {
        transform: translateY(0) scale(1);
        opacity: 1;
      }

      to {
        transform: translateY(20px) scale(0.95);
        opacity: 0;
      }
    }

    @keyframes secToastProgress {
      from {
        width: 100%;
      }

      to {
        width: 0%;
      }
    }

    /* Fix pour les icônes SVG de la pagination Tailwind par défaut de Laravel */
    nav[role="navigation"] svg {
      width: 1.25rem;
      height: 1.25rem;
    }

    nav[role="navigation"] a,
    nav[role="navigation"] span {
      text-decoration: none;
    }
  </style>
  @stack('styles')
  @vite(['resources/js/echo.js'])
  <script>
    window.userId = {{ auth()->id() ?? 'null' }};
    window.cabinetId = {{ auth()->user()->cabinet_id ?? 'null' }};
  </script>
</head>

<body>

  {{-- ════════════════════════════════════ TOPBAR ════════════════════════════════════ --}}
  <header class="sec-topbar">
    <div class="sec-topbar-brand">GEL <small>Informatique</small></div>


    <div style="margin-left: 20px; position: relative; flex:1; max-width:400px;">
      <i class="fas fa-magic"
        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: rgba(255,255,255,0.7);"></i>
      <input type="text" id="omnisearchInput" class="sec-omnisearch"
        placeholder="Chercher, ou demandez à l'IA (Ex: Prépare une lettre...)"
        style="padding-left: 32px; width:100%; max-width:100%;">

      <div class="sec-dropdown" id="searchDd" style="left:0; right:auto; top:calc(100% + 6px); width:450px; padding:0;">
        <div id="searchResults" style="max-height:400px; overflow-y:auto; padding:8px 0;">
          <div style="padding:16px; text-align:center; color:var(--sec-text-muted); font-size:12px;">Tapez au moins 2
            caractères ou donnez un ordre à GEL Intelligence...</div>
        </div>
      </div>
    </div>

    <div class="sec-topbar-right">
      <!-- Barre d'actions rapides -->
      <div style="position:relative;">
        <button class="sec-topbar-btn" title="Actions Rapides" onclick="toggleSecDropdown('quickActionsDd')"
          style="background:var(--sec-success); box-shadow:0 0 10px rgba(16, 185, 129, 0.4);">
          <i class="fas fa-plus"></i>
        </button>
        <div class="sec-dropdown" id="quickActionsDd" style="right:0;top:calc(100% + 6px); width:200px; padding:8px 0;">
          <a href="{{ route('gel-informaticien.tickets.create') }}" class="sec-dd-item text-decoration-none"><i
              class="fas fa-ticket-alt" style="color:#FF7900;width:20px;"></i> Nouveau Ticket</a>
          <a href="{{ route('gel-informaticien.dev-requests.create') }}" class="sec-dd-item text-decoration-none"><i
              class="fas fa-code" style="color:#3B82F6;width:20px;"></i> Nouvelle Demande</a>
        </div>
      </div>

      <!-- Centre de notifications intelligent -->
      <div style="position:relative;">
        <button class="sec-topbar-btn" title="Notifications" onclick="toggleSecDropdown('notifDd')">
          <i class="fas fa-bell"></i>
          <span class="notif-badge" id="notifBadge"></span>
        </button>
        <div class="sec-dropdown" id="notifDd" style="right:0;top:calc(100% + 6px); width:320px; padding:0;">
          <div
            style="padding:12px 14px; border-bottom:1px solid var(--sec-border); font-weight:700; font-size:13px; background:#F8FAFC; border-radius:8px 8px 0 0; display:flex; justify-content:space-between; align-items:center;">
            <span>GEL Intelligence</span>
            <i class="fas fa-magic" style="color:var(--sec-primary);"></i>
          </div>
          <div id="notifList" style="max-height:300px; overflow-y:auto; padding:0;">
            <div style="padding:16px; text-align:center; color:var(--sec-text-muted); font-size:12px;">
              <i class="fas fa-circle-notch fa-spin"></i> Chargement des notifications…
            </div>
          </div>
          <div style="padding:8px; border-top:1px solid var(--sec-border); text-align:center;">
            <form method="POST" action="{{ route('gel-secretary.notifications.read-all') }}" style="margin:0;"
              onsubmit="event.preventDefault(); fetch(this.action,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(()=>{ fetchNotifications(); secToast('Notifications marquées comme lues','success'); }).catch(()=>secToast('Erreur lors de la mise à jour','err'));">
              @csrf
              <button type="submit" style="background:none;border:none;font-size:12px;color:var(--sec-primary);font-weight:500;cursor:pointer;">Marquer tout comme lu</button>
            </form>
          </div>
        </div>
      </div>

      <button class="sec-topbar-btn" title="Aide"><i class="fas fa-question-circle"></i></button>

      <div style="position:relative;">
        <div class="sec-avatar" onclick="toggleSecDropdown('userDd')" title="Mon compte">
          @if(auth()->user()?->photo)
            <img src="{{ asset('storage/' . auth()->user()->photo) }}" style="width:100%;height:100%;object-fit:cover;">
          @else
            {{ strtoupper(substr(auth()->user()?->name ?? auth()->user()?->email ?? 'S', 0, 2)) }}
          @endif
        </div>
        <div class="sec-dropdown" id="userDd" style="right:0;top:calc(100% + 6px);">
          <div style="padding:10px 14px;border-bottom:1px solid var(--sec-border);">
            <div style="font-weight:600;font-size:13px;">{{ auth()->user()?->prenom ?? 'Informaticien' }}</div>
            <div style="font-size:11px;color:var(--sec-text-muted);">{{ auth()->user()?->email }}</div>
            <div style="font-size:11px;color:var(--sec-primary);font-weight:600;margin-top:2px;">Pôle Informatique
            </div>
          </div>
          @if(in_array(auth()->user()->role ?? '', ['admin','super_admin','director','dirigeant']))
            <a href="{{ route('gel-secretary.dashboard') }}" class="sec-dd-item" style="color:var(--sec-info); font-weight:600; background:#EFF6FF;">
              <i class="fas fa-user-tie"></i> Mode Direction
            </a>
          @endif
          <div class="sec-dd-item" onclick="document.getElementById('logoutForm').submit();">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </div>
        </div>
      </div>
    </div>
  </header>

  {{-- ════════════════════════════════════ SIDEBAR ════════════════════════════════════ --}}
  <aside class="sec-sidebar">
    <div class="sec-sidebar-brand">
      <div class="sec-sidebar-icon">          <i class="fas fa-bullhorn"></i>
        </div>
        <div>
          <div class="sec-sidebar-title">GEL Communication</div>
          <div class="sec-sidebar-sub">Marketing & Création</div>
        </div>
      </div>

    <ul class="sec-nav">
      <li class="sec-nav-section">GÉNÉRAL</li>
      <li>
        <a href="{{ route('gel-communication.dashboard') }}"
          class="sec-nav-item {{ request()->routeIs('gel-communication.dashboard') ? 'active' : '' }}">
          <i class="fas fa-chart-line"></i> Tableau de bord
        </a>
      </li>
      <li>
        <a href="{{ route('gel-communication.campaigns.index') }}"
          class="sec-nav-item {{ request()->routeIs('gel-communication.campaigns.*') ? 'active' : '' }}">
          <i class="fas fa-bullhorn"></i> Campagnes Actives
        </a>
      </li>
      <li>
        <a href="{{ route('gel-communication.calendar.index') }}" class="sec-nav-item {{ request()->routeIs('gel-communication.calendar.*') ? 'active' : '' }}">
          <i class="fas fa-calendar-alt"></i> Calendrier Éditorial
        </a>
      </li>

      <li class="sec-nav-section">RELATION CLIENT</li>
      <li>
        <a href="{{ route('gel-communication.briefs.index') }}"
          class="sec-nav-item {{ request()->routeIs('gel-communication.briefs.*') ? 'active' : '' }}">
          <i class="fas fa-envelope-open-text"></i> Briefs Clients
          @php
            $pendingBriefs = \App\Models\Gel\MarketingBrief::where('status', 'pending')->count();
          @endphp
          @if($pendingBriefs > 0)
            <span class="sec-nav-badge" style="background:#f59e0b;color:#000;">{{ $pendingBriefs }}</span>
          @endif
        </a>
      </li>
      <li>
        <a href="{{ route('gel-communication.contents.index') }}"
          class="sec-nav-item {{ request()->routeIs('gel-communication.contents.*') ? 'active' : '' }}">
          <i class="fas fa-paint-brush"></i> Visuels & Validations
          @php
            $pendingValidations = \App\Models\Gel\MarketingContent::where('status', 'pending_client')->count();
          @endphp
          @if($pendingValidations > 0)
            <span class="sec-nav-badge">{{ $pendingValidations }}</span>
          @endif
        </a>
      </li>
    </ul>

    @php $u = auth()->user(); @endphp
    @if($u)
      <div class="sec-sidebar-footer">
        <div class="sec-sidebar-footer-avatar">
          @if($u->photo)
            <img src="{{ asset('storage/' . $u->photo) }}" style="width:100%;height:100%;object-fit:cover;">
          @else
            {{ strtoupper(substr($u->name ?? $u->email, 0, 2)) }}
          @endif
        </div>
        <div style="flex:1;min-width:0;">
          <div class="sec-sidebar-footer-name">{{ $u->name ?? $u->email }}</div>
          <div class="sec-sidebar-footer-role">Pôle Communication</div>
        </div>
        <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logoutForm').submit();"
          style="color:var(--sec-text-muted);font-size:15px;"><i class="fas fa-sign-out-alt"></i></a>
      </div>
    @endif
  </aside>

  {{-- ════════════════════════════════════ CONTENT ════════════════════════════════════ --}}
  <main class="sec-content">
    @if(session('success'))
      <div style="display:none;" id="sec-flash" data-msg="{{ session('success') }}" data-type="success"></div>
    @endif
    @if(session('error'))
      <div style="display:none;" id="sec-flash" data-msg="{{ session('error') }}" data-type="err"></div>
    @endif

    @yield('content')
  </main>

  <div class="sec-toast-container" id="secToastContainer"></div>

  <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

  {{-- ════════════════════════════════════ SCRIPTS ════════════════════════════════════ --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {

      // ─── Toast ─────────────────────────────────────────────────────────
      window.secToast = function (msg, type) {
        type = type || 'success';
        var c = document.getElementById('secToastContainer');
        if (!c) return;

        var icons = {
          'success': '<i class="fas fa-check-circle"></i>',
          'err': '<i class="fas fa-times-circle"></i>',
          'warn': '<i class="fas fa-exclamation-triangle"></i>',
          'info': '<i class="fas fa-info-circle"></i>'
        };

        var t = document.createElement('div');
        t.className = 'sec-toast ' + type;

        t.innerHTML = `
        <div class="sec-toast-icon-wrapper">
          ${icons[type] || icons['success']}
        </div>
        <div class="sec-toast-content">
          <div class="sec-toast-message">${msg}</div>
        </div>
        <div class="sec-toast-progress"></div>
      `;

        c.appendChild(t);

        setTimeout(function () {
          if (t && t.parentNode) {
            t.classList.add('exit');
            setTimeout(function () { if (t && t.parentNode) t.remove(); }, 250);
          }
        }, 1500);
      };

      // Flash auto
      var flash = document.getElementById('sec-flash');
      if (flash) secToast(flash.dataset.msg, flash.dataset.type);

      // ─── User dropdown ─────────────────────────────────────────────────
      window.toggleSecDropdown = function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        var open = el.classList.contains('open');
        document.querySelectorAll('.sec-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
        if (!open) el.classList.add('open');
      };

      // ─── Client dropdown ───────────────────────────────────────────────
      window.toggleClientDropdown = function (e) {
        var dd = document.getElementById('clientDropdown');
        if (dd) dd.classList.toggle('open');
      };

      function toggleSecDropdown(id) {
        document.querySelectorAll('.sec-dropdown').forEach(d => {
          if (d.id !== id) d.classList.remove('open');
        });
        const d = document.getElementById(id);
        if (d) d.classList.toggle('open');
      }

      document.addEventListener('click', (e) => {
        if (!e.target.closest('.sec-avatar') && !e.target.closest('#userDd') && !e.target.closest('.sec-topbar-btn') && !e.target.closest('#notifDd') && !e.target.closest('.sec-omnisearch') && !e.target.closest('#searchDd')) {
          document.querySelectorAll('.sec-dropdown').forEach(d => d.classList.remove('open'));
        }
      });

      // Fetch Notifications via AJAX
      function fetchNotifications() {
        fetch('{{ route("gel-secretary.dashboard.notifications") }}')
          .then(response => response.json())
          .then(data => {
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');

            if (data.count > 0) {
              badge.style.display = 'block';
              badge.innerText = data.count > 99 ? '99+' : data.count;
            } else {
              badge.style.display = 'none';
            }

            // S17/S19 — Resynchroniser le badge latéral « Demandes clients »
            const demBadge = document.getElementById('navDemandesBadge');
            if (demBadge) {
              const dc = parseInt(data.demandes_count || 0);
              if (dc > 0) {
                demBadge.style.display = 'inline-flex';
                demBadge.innerText = dc;
              } else {
                demBadge.style.display = 'none';
              }
            }

            if (data.items.length === 0) {
              list.innerHTML = '<div style="padding:16px; text-align:center; color:var(--sec-text-muted); font-size:12px;">Aucune notification.</div>';
            } else {
              list.innerHTML = data.items.map(item => `
            <a href="${item.link}" style="display:flex; gap:12px; padding:10px 14px; text-decoration:none; border-bottom:1px solid #F1F5F9; color:inherit; align-items:flex-start;">
              <div style="width:32px; height:32px; border-radius:50%; background:#F8FAFC; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="${item.icon}"></i>
              </div>
              <div>
                <div style="font-size:13px; font-weight:600; color:var(--sec-text);">${item.title}</div>
                <div style="font-size:12px; color:var(--sec-text-muted); margin-top:2px;">${item.description}</div>
                <div style="font-size:10px; color:#94A3B8; margin-top:4px;">${item.time}</div>
              </div>
            </a>
          `).join('');
            }
          })
          .catch(error => {
            document.getElementById('notifList').innerHTML = '<div style="padding:16px; text-align:center; color:#EF4444; font-size:12px;">Erreur de chargement.</div>';
          });
      }

      // Call on load
      fetchNotifications();

      // Enregistrement PWA Service Worker
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/sw.js').then(reg => {
            console.log('PWA Service Worker enregistré', reg.scope);
          }).catch(err => {
            console.warn('Erreur Service Worker', err);
          });
        });
      }

      // Listen to Reverb Websockets
      if (window.userId && typeof window.Echo !== 'undefined') {
        window.Echo.private('user.' + window.userId)
          .listen('.NotificationRecuEvent', (e) => {
            const item = e.notificationData;

            // Jouer le son
            try {
              let audio = new Audio('/audio/notification.wav');
              audio.play().catch(err => console.warn("L'autoplay a été bloqué par le navigateur", err));
            } catch (e) { }

            // Mettre à jour le badge (optimistic UI)
            const badge = document.getElementById('notifBadge');
            if (badge) {
              let current = parseInt(badge.innerText) || 0;
              current++;
              badge.style.display = 'block';
              badge.innerText = current > 99 ? '99+' : current;
            }

            // Mettre à jour la liste
            const list = document.getElementById('notifList');
            const emptyMsg = list.querySelector('div[style*="Aucune notification"]');
            if (emptyMsg) emptyMsg.remove();

            const notifHtml = `
            <a href="${item.link}" style="display:flex; gap:12px; padding:10px 14px; text-decoration:none; border-bottom:1px solid #F1F5F9; color:inherit; align-items:flex-start; background:#F0FDFA; transition:background 0.3s;" onmouseover="this.style.background='transparent'">
              <div style="width:32px; height:32px; border-radius:50%; background:#FF7900; color:white; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="${item.icon}"></i>
              </div>
              <div>
                <div style="font-size:13px; font-weight:700; color:var(--sec-text);">${item.title}</div>
                <div style="font-size:12px; color:var(--sec-text-muted); margin-top:2px;">${item.description}</div>
                <div style="font-size:10px; color:#94A3B8; margin-top:4px;">${item.time}</div>
              </div>
            </a>
        `;
            list.insertAdjacentHTML('afterbegin', notifHtml);

            // Afficher un toast
            secToast(item.title + ' : ' + item.description, 'info');

            // S19 — Resynchroniser tous les badges depuis le serveur (source de vérité)
            fetchNotifications();
          });

        // P1 — Un nouveau message interne (comptable/admin) doit faire bouger
        // le badge + la liste des notifications immédiatement, sur n'importe quelle page.
        // Le contenu de la conversation est ajouté en direct dans la vue Messagerie.
        window.Echo.private('chat.interne.' + window.cabinetId + '.' + window.userId)
          .listen('.MessageEnvoyeEvent', (e) => {
            // Resynchronise badge notifications + compteurs depuis le serveur
            fetchNotifications();
            try {
              let audio = new Audio('/audio/notification.wav');
              audio.play().catch(err => console.warn("L'autoplay a été bloqué par le navigateur", err));
            } catch (e) { }
          });
      }

      // Omnisearch AJAX
      const searchInput = document.getElementById('omnisearchInput');
      const searchDd = document.getElementById('searchDd');
      const searchResults = document.getElementById('searchResults');
      let searchTimeout;

      if (searchInput) {
        searchInput.addEventListener('input', function (e) {
          const q = e.target.value;
          if (q.length < 2) {
            searchDd.classList.remove('open');
            return;
          }

          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            searchDd.classList.add('open');
            searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:var(--sec-text-muted); font-size:12px;"><i class="fas fa-circle-notch fa-spin"></i> Recherche en cours...</div>';

            fetch('{{ route("gel-secretary.search") }}?q=' + encodeURIComponent(q))
              .then(res => res.json())
              .then(data => {
                if (Object.keys(data).length === 0) {
                  searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:var(--sec-text-muted); font-size:12px;">Aucun résultat trouvé pour "' + q + '".</div>';
                  return;
                }

                let html = '';
                for (const [category, items] of Object.entries(data)) {
                  html += `<div style="padding:6px 14px; font-size:11px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase; background:#F8FAFC;">${category}</div>`;
                  items.forEach(item => {
                    html += `
                  <a href="${item.url}" style="display:flex; gap:12px; padding:10px 14px; text-decoration:none; border-bottom:1px solid #F1F5F9; color:inherit; align-items:center;">
                    <div style="width:32px; height:32px; border-radius:50%; background:#F0FDFA; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                      <i class="${item.icon}"></i>
                    </div>
                    <div>
                      <div style="font-size:13px; font-weight:600; color:var(--sec-text);">${item.title}</div>
                      <div style="font-size:11px; color:var(--sec-text-muted); margin-top:2px;">${item.subtitle}</div>
                    </div>
                  </a>
                `;
                  });
                }
                searchResults.innerHTML = html;
              })
              .catch(err => {
                searchResults.innerHTML = '<div style="padding:16px; text-align:center; color:#EF4444; font-size:12px;">Erreur lors de la recherche.</div>';
              });
          }, 400);
        });

        searchInput.addEventListener('focus', function (e) {
          if (e.target.value.length >= 2) {
            searchDd.classList.add('open');
          }
        });
      }

      // Escape closes
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          document.querySelectorAll('.sec-dropdown.open, .client-dropdown.open')
            .forEach(function (el) { el.classList.remove('open'); });
        }
      });

      // Scroll sidebar to active item
      const activeNavItem = document.querySelector('.sec-nav-item.active');
      const sidebar = document.querySelector('.sec-sidebar');
      if (activeNavItem && sidebar) {
        // Calculer la position pour centrer l'élément actif dans la sidebar
        const offset = activeNavItem.offsetTop - (sidebar.clientHeight / 2) + (activeNavItem.clientHeight / 2);
        if (offset > 0) {
          sidebar.scrollTop = offset;
        }
      }
    });
  </script>

  <!-- ─── WIDGET IA TRANSVERSAL ─── -->
  <div id="aiChatWidget" class="ai-widget-container">
    <button id="aiWidgetToggle" class="ai-widget-btn shadow-lg" title="Assistant IA">
      <i class="fas fa-magic"></i>
    </button>

    <div id="aiWidgetPanel" class="ai-widget-panel shadow-lg">
      <div class="ai-widget-header">
        <div class="d-flex align-items-center gap-2">
          <div class="ai-avatar"><i class="fas fa-robot"></i></div>
          <div>
            <h6 class="mb-0" style="font-size:14px; font-weight:700;">Assistant IA</h6>
            <small style="font-size:11px; opacity:0.8;">GEL Secrétariat</small>
          </div>
        </div>
        <button id="aiWidgetClose" class="btn-close btn-close-white" style="font-size:10px;"></button>
      </div>

      <div id="aiWidgetBody" class="ai-widget-body">
        <div class="ai-msg received">
          Bonjour ! Je suis votre assistant IA. Avez-vous une question concernant
          {{ $activeClient?->nom_entreprise ?? 'vos dossiers' }} ?
        </div>
      </div>

      <div class="ai-widget-footer">
        <form id="aiWidgetForm" class="d-flex gap-2">
          <input type="text" id="aiWidgetInput" class="form-control form-control-sm"
            placeholder="Posez votre question..." required autocomplete="off"
            style="font-size:13px; border-radius:20px; padding-left:14px;">
          <button type="submit" class="btn btn-sm"
            style="background:var(--sec-primary); color:white; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>
    </div>
  </div>

  <style>
    .ai-widget-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 1050;
    }

    .ai-widget-btn {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--sec-primary) 0%, var(--sec-primary-dark) 100%);
      color: white;
      border: none;
      font-size: 24px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .ai-widget-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(13, 148, 136, 0.3) !important;
    }

    .ai-widget-panel {
      position: absolute;
      bottom: 70px;
      right: 0;
      width: 340px;
      height: 450px;
      background: white;
      border-radius: 16px;
      display: none;
      flex-direction: column;
      overflow: hidden;
      transform-origin: bottom right;
      animation: aiPanelPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: 1px solid var(--sec-border);
    }

    @keyframes aiPanelPop {
      0% {
        opacity: 0;
        transform: scale(0.8) translateY(20px);
      }

      100% {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    .ai-widget-panel.show {
      display: flex;
    }

    .ai-widget-header {
      background: linear-gradient(135deg, var(--sec-primary) 0%, var(--sec-primary-dark) 100%);
      color: white;
      padding: 12px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .ai-avatar {
      width: 32px;
      height: 32px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .ai-widget-body {
      flex: 1;
      padding: 16px;
      overflow-y: auto;
      background: #F8FAFC;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .ai-widget-footer {
      padding: 12px;
      background: white;
      border-top: 1px solid var(--sec-border);
    }

    .ai-msg {
      max-width: 85%;
      padding: 10px 14px;
      border-radius: 12px;
      font-size: 13px;
      line-height: 1.4;
      word-wrap: break-word;
    }

    .ai-msg.received {
      background: white;
      color: var(--sec-text);
      border: 1px solid var(--sec-border);
      align-self: flex-start;
      border-bottom-left-radius: 4px;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .ai-msg.sent {
      background: var(--sec-primary-light);
      color: var(--sec-primary-dark);
      border: 1px solid #CCFBF1;
      align-self: flex-end;
      border-bottom-right-radius: 4px;
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggle = document.getElementById('aiWidgetToggle');
      const panel = document.getElementById('aiWidgetPanel');
      const close = document.getElementById('aiWidgetClose');
      const form = document.getElementById('aiWidgetForm');
      const input = document.getElementById('aiWidgetInput');
      const body = document.getElementById('aiWidgetBody');

      toggle.addEventListener('click', () => {
        panel.classList.toggle('show');
        if (panel.classList.contains('show')) input.focus();
      });

      close.addEventListener('click', () => panel.classList.remove('show'));

      function appendMsg(text, type) {
        const div = document.createElement('div');
        div.className = 'ai-msg ' + type;
        div.innerText = text;
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
        return div;
      }

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;

        appendMsg(msg, 'sent');
        input.value = '';
        const loadingDiv = appendMsg('...', 'received');

        fetch('{{ route("gel-secretary.ai-chat") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ message: msg })
        })
          .then(res => res.json())
          .then(data => {
            loadingDiv.remove();
            if (data.reply) {
              appendMsg(data.reply, 'received');
            } else {
              appendMsg('Désolé, une erreur est survenue.', 'received');
            }
          })
          .catch(err => {
            loadingDiv.remove();
            appendMsg('Erreur de connexion à l\'IA.', 'received');
          });
      });
    });
  </script>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>

</html>