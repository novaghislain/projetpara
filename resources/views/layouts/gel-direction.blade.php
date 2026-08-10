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
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    :root {
      /* Theme Direction (Bleu exécutif sombre) */
      --dir-primary: #1E3A8A; /* Blue 900 */
      --dir-primary-light: #EFF6FF;
      --dir-sidebar-bg: #0F172A; /* Slate 900 */
      --dir-sidebar-w: 250px;
      --dir-topbar-h: 60px;
      --dir-text: #1E293B;
      --dir-text-muted: #64748B;
      --dir-bg: #F8FAFC;
      --dir-border: #E2E8F0;
    }
    body { font-family:'Inter',sans-serif; color:var(--dir-text); background:var(--dir-bg); min-height:100vh; }
    a { text-decoration:none; color:inherit; }

    /* TOPBAR */
    .dir-topbar {
      position:fixed; top:0; left:var(--dir-sidebar-w); right:0; height:var(--dir-topbar-h);
      background:white; border-bottom:1px solid var(--dir-border);
      display:flex; align-items:center; padding:0 24px; z-index:1000;
    }
    .dir-topbar-title { font-size:18px; font-weight:600; color:var(--dir-primary); }

    /* SIDEBAR */
    .dir-sidebar {
      position:fixed; top:0; left:0; bottom:0; width:var(--dir-sidebar-w);
      background:var(--dir-sidebar-bg); color:white;
      display:flex; flex-direction:column; z-index:999;
    }
    .dir-sidebar-brand {
      height:var(--dir-topbar-h); display:flex; align-items:center; padding:0 24px;
      font-size:18px; font-weight:700; border-bottom:1px solid rgba(255,255,255,0.1);
    }
    .dir-nav { list-style:none; padding:16px; flex:1; }
    .dir-nav-item {
      display:flex; align-items:center; gap:12px; padding:12px 16px; margin-bottom:8px;
      border-radius:8px; color:rgba(255,255,255,0.7); cursor:pointer; font-size:14px;
      transition:all 0.2s;
    }
    .dir-nav-item:hover, .dir-nav-item.active { background:rgba(255,255,255,0.1); color:white; }
    
    /* CONTENT */
    .dir-content {
      margin-left:var(--dir-sidebar-w); margin-top:var(--dir-topbar-h);
      padding:32px; min-height:calc(100vh - var(--dir-topbar-h));
    }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="dir-sidebar">
    <div class="dir-sidebar-brand">
      <i class="fas fa-chart-line" style="margin-right:10px; color:#3B82F6;"></i> GEL Direction
    </div>
    <ul class="dir-nav">
      <a href="{{ route('gel-direction.dashboard') }}" class="dir-nav-item {{ request()->routeIs('gel-direction.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home" style="width:20px;"></i> Vue d'ensemble
      </a>
      <a href="{{ route('gel-direction.clients.index') }}" class="dir-nav-item {{ request()->routeIs('gel-direction.clients.*') ? 'active' : '' }}">
        <i class="fas fa-building" style="width:20px;"></i> Clients & CA
      </a>
      <a href="{{ route('gel-direction.team.index') }}" class="dir-nav-item {{ request()->routeIs('gel-direction.team.*') ? 'active' : '' }}">
        <i class="fas fa-users" style="width:20px;"></i> Supervision RH
      </a>
      <a href="{{ route('gel-direction.finance.index') }}" class="dir-nav-item {{ request()->routeIs('gel-direction.finance.*') ? 'active' : '' }}">
        <i class="fas fa-chart-pie" style="width:20px;"></i> Finance
      </a>
      <a href="{{ route('gel-direction.validations.index') }}" class="dir-nav-item {{ request()->routeIs('gel-direction.validations.*') ? 'active' : '' }}">
        <i class="fas fa-file-signature" style="width:20px;"></i> Validations 
        <span class="badge ms-auto" style="background:#EF4444; color:white; font-size:10px; border-radius:10px; padding:2px 6px;">3</span>
      </a>
    </ul>
    <div style="padding:16px; border-top:1px solid rgba(255,255,255,0.1); font-size:12px; color:rgba(255,255,255,0.5);">
      <i class="fas fa-user-tie" style="margin-right:6px;"></i> Dirigeant : {{ Auth::user()->name ?? 'Admin' }}
      <div style="margin-top:10px;">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" style="background:none; border:none; color:inherit; font-size:12px; cursor:pointer;">
             <i class="fas fa-sign-out-alt"></i> Déconnexion
          </button>
        </form>
      </div>
    </div>
  </aside>

  <!-- TOPBAR -->
  <header class="dir-topbar">
    <div class="dir-topbar-title">@yield('page_title', 'Tableau de bord Exécutif')</div>
    <div class="ms-auto d-flex align-items-center gap-3">
        <div style="font-size:13px; color:var(--dir-text-muted); margin-right:10px;">
            <i class="fas fa-calendar-day"></i> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
        </div>
        <a href="{{ route('gel-secretary.dashboard') }}" class="btn btn-sm" style="background:#F1F5F9; color:#475569; border:1px solid #E2E8F0; font-weight:500;">
            <i class="fas fa-arrow-left"></i> Retour Secrétariat
        </a>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="dir-content">
    @yield('content')
  </main>

</body>
</html>
