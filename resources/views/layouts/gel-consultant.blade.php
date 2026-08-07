<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portail Consultant — GEL SABINET</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --bg: #0f1117;
      --surface: #1a1d27;
      --surface2: #22263a;
      --border: rgba(255,255,255,0.07);
      --accent: #7c5cfc;
      --accent-light: #a78bfa;
      --text: #e2e8f0;
      --muted: #64748b;
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #ef4444;
      --info: #3b82f6;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    /* ── TOPBAR ── */
    .c-topbar {
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      height: 60px;
      display: flex;
      align-items: center;
      padding: 0 28px;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .c-brand { font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px; }
    .c-brand-badge {
      background: linear-gradient(135deg, var(--accent), var(--accent-light));
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 20px;
      letter-spacing: 0.5px;
    }
    .c-topbar-right { display: flex; align-items: center; gap: 16px; }
    .c-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--accent-light));
      color: #fff; font-weight: 700; font-size: 13px;
      display: flex; align-items: center; justify-content: center;
    }

    /* ── LAYOUT ── */
    .c-layout { display: flex; }
    .c-sidebar {
      width: 240px;
      min-height: calc(100vh - 60px);
      background: var(--surface);
      border-right: 1px solid var(--border);
      padding: 24px 16px;
      position: sticky;
      top: 60px;
      height: calc(100vh - 60px);
      overflow-y: auto;
    }
    .c-main { flex: 1; padding: 32px; }

    /* ── SIDEBAR NAV ── */
    .c-nav-section {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1px;
      color: var(--muted);
      text-transform: uppercase;
      padding: 16px 8px 6px;
    }
    .c-nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: 8px; cursor: pointer;
      font-size: 13px; font-weight: 500; color: var(--muted);
      text-decoration: none; transition: all 140ms;
      margin-bottom: 2px;
    }
    .c-nav-item:hover, .c-nav-item.active {
      background: rgba(124, 92, 252, 0.12);
      color: var(--accent-light);
    }
    .c-nav-item i { width: 16px; text-align: center; font-size: 13px; }

    /* ── CARDS ── */
    .c-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 20px;
    }
    .c-card-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .c-card-title { font-size: 14px; font-weight: 600; color: var(--text); }
    .c-card-body { padding: 20px; }

    /* ── MISSION CARD ── */
    .mission-card {
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 16px;
      text-decoration: none;
      display: block;
      transition: all 200ms;
    }
    .mission-card:hover { border-color: var(--accent); transform: translateY(-1px); }
    .mission-card.expired { opacity: 0.5; pointer-events: none; }
    .mission-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px; }
    .mission-title { font-size: 15px; font-weight: 600; color: var(--text); }
    .mission-company { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .mission-desc { font-size: 13px; color: var(--muted); line-height: 1.5; margin-top: 8px; }
    .mission-footer { display: flex; align-items: center; gap: 12px; margin-top: 14px; }

    /* ── BADGE ── */
    .badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 600;
    }
    .badge-success { background: rgba(16,185,129,.15); color: var(--success); }
    .badge-warning { background: rgba(245,158,11,.15); color: var(--warning); }
    .badge-danger  { background: rgba(239,68,68,.15); color: var(--danger); }
    .badge-info    { background: rgba(59,130,246,.15); color: var(--info); }
    .badge-purple  { background: rgba(124,92,252,.15); color: var(--accent-light); }
    .badge-gray    { background: rgba(100,116,139,.15); color: var(--muted); }

    /* ── COUNTDOWN ── */
    .countdown-pill {
      font-size: 11px; font-weight: 700;
      padding: 4px 10px; border-radius: 20px;
      background: rgba(245,158,11,.12);
      color: var(--warning);
    }
    .countdown-pill.urgent { background: rgba(239,68,68,.12); color: var(--danger); }

    /* ── ALERT ── */
    .c-alert {
      border-radius: 10px; padding: 14px 18px;
      font-size: 13px; margin-bottom: 16px;
      display: flex; align-items: flex-start; gap: 12px;
    }
    .c-alert-success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.2); color: #6ee7b7; }
    .c-alert-danger  { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); color: #fca5a5; }

    /* ── UTIL ── */
    .page-title { font-size: 22px; font-weight: 700; color: #fff; }
    .page-sub   { font-size: 13px; color: var(--muted); margin-top: 4px; }
    .page-header { margin-bottom: 28px; }
    .text-muted { color: var(--muted); }
    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
      text-decoration: none; border: none; cursor: pointer; transition: all 150ms;
    }
    .btn-primary  { background: linear-gradient(135deg, var(--accent), var(--accent-light)); color: #fff; }
    .btn-primary:hover  { opacity: .85; }
    .btn-light { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
    .btn-light:hover { background: var(--surface); }
  </style>
  @stack('styles')
</head>
<body>
  <!-- TOPBAR -->
  <header class="c-topbar">
    <div class="c-brand">
      <i class="fas fa-user-tie" style="color:var(--accent-light)"></i>
      GEL SABINET <span class="c-brand-badge">CONSULTANT</span>
    </div>
    <div class="c-topbar-right">
      <div class="c-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}</div>
      <span style="font-size:13px;font-weight:500">{{ auth()->user()->name }}</span>
      <form method="POST" action="{{ route('logout') }}" style="display:inline">
        @csrf
        <button type="submit" style="background:none;border:none;color:var(--muted);font-size:13px;cursor:pointer;">
          <i class="fas fa-sign-out-alt"></i>
        </button>
      </form>
    </div>
  </header>

  <div class="c-layout">
    <!-- SIDEBAR -->
    <aside class="c-sidebar">
      <div class="c-nav-section">Navigation</div>
      <a href="{{ route('gel-consultant.dashboard') }}"
         class="c-nav-item {{ request()->routeIs('gel-consultant.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> Mes Dossiers
      </a>

      <div class="c-nav-section" style="margin-top:20px;">Informations</div>
      <div style="padding: 10px 12px; font-size: 12px; color: var(--muted); line-height: 1.6;">
        <div style="font-weight:600; color:var(--text); margin-bottom:4px;">Accès limité</div>
        Vous n'avez accès qu'aux dossiers explicitement confiés par l'administrateur.
      </div>
    </aside>

    <!-- MAIN -->
    <main class="c-main">
      @if(session('success'))
        <div class="c-alert c-alert-success">
          <i class="fas fa-check-circle"></i>
          {{ session('success') }}
        </div>
      @endif
      @if($errors->any())
        <div class="c-alert c-alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          {{ $errors->first() }}
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</body>
</html>
