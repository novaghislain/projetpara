<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Super Admin') — GEL Cabinet</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    :root {
      /* Theme Super Admin (Dark Premium) */
      --sa-primary: #FF7900;
      --sa-primary-light: rgba(255,121,0,0.1);
      --sa-sidebar-bg-start: #0B1120;
      --sa-sidebar-bg-end: #0F1A2E;
      --sa-sidebar-w: 260px;
      --sa-topbar-h: 60px;
      --sa-text: #1F2937;
      --sa-text-muted: #6B7280;
      --sa-bg: #F5F7FA;
      --sa-border: rgba(0,0,0,0.06);
    }
    body { font-family:'Inter',sans-serif; color:var(--sa-text); background:var(--sa-bg); min-height:100vh; }
    a { text-decoration:none; color:inherit; }

    /* TOPBAR */
    .sa-topbar {
      position:fixed; top:0; left:var(--sa-sidebar-w); right:0; height:var(--sa-topbar-h);
      background:rgba(255,255,255,0.9);
      backdrop-filter: blur(12px);
      border-bottom:1px solid var(--sa-border);
      display:flex; align-items:center; padding:0 24px; z-index:1000;
    }
    .sa-topbar-title { font-size:18px; font-weight:600; color:#111827; }

    /* SIDEBAR */
    .sa-sidebar {
      position:fixed; top:0; left:0; bottom:0; width:var(--sa-sidebar-w);
      background: linear-gradient(180deg, var(--sa-sidebar-bg-start) 0%, var(--sa-sidebar-bg-end) 100%);
      color: white;
      display:flex; flex-direction:column; z-index:999;
      box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    }
    .sa-sidebar-brand {
      height:var(--sa-topbar-h); display:flex; align-items:center; padding:0 24px;
      font-size:18px; font-weight:700; border-bottom:1px solid rgba(255,255,255,0.05);
      color: #fff;
    }
    .sa-sidebar-brand span {
      color: var(--sa-primary);
    }
    .sa-nav { list-style:none; padding:16px 0; flex:1; overflow-y:auto; }
    .sa-nav-label {
      padding: 0 24px; margin-top: 16px; margin-bottom: 8px;
      font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
      color: rgba(255,255,255,0.4); font-weight: 600;
    }
    .sa-nav-item {
      display:flex; align-items:center; gap:12px; padding:12px 24px;
      color:rgba(255,255,255,0.55); cursor:pointer; font-size:14px; font-weight:500;
      transition:all 0.2s; border-left: 3px solid transparent;
    }
    .sa-nav-item i { width: 18px; text-align: center; font-size:16px; }
    .sa-nav-item:hover { color:rgba(255,255,255,0.85); }
    .sa-nav-item.active { 
      background: var(--sa-primary-light); 
      color: white; 
      border-left-color: var(--sa-primary);
    }
    
    /* CONTENT */
    .sa-content {
      margin-left:var(--sa-sidebar-w); margin-top:var(--sa-topbar-h);
      padding:32px; min-height:calc(100vh - var(--sa-topbar-h));
    }
    
    /* CARDS */
    .sa-card {
      background: #FFFFFF; border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
      border: 1px solid var(--sa-border);
      margin-bottom: 24px; overflow: hidden;
    }
    .sa-card-header {
      padding: 16px 20px; border-bottom: 1px solid var(--sa-border);
      display: flex; justify-content: space-between; align-items: center;
      background: #fff;
    }
    .sa-card-title { font-weight: 600; font-size: 15px; color: #111827; }
    .sa-card-body { padding: 20px; }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sa-sidebar">
    <div class="sa-sidebar-brand">
      <i class="fas fa-cube" style="margin-right:10px; font-size:22px; color:var(--sa-primary);"></i> 
      <div>GEL <span>Cabinet</span></div>
    </div>
    
    <ul class="sa-nav">
      <div class="sa-nav-label">Général</div>
      <a href="{{ route('super-admin.dashboard') }}" class="sa-nav-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> Dashboard
      </a>
      
      <div class="sa-nav-label">Tenants (SaaS)</div>
      <a href="{{ route('super-admin.entreprises.index') }}" class="sa-nav-item {{ request()->routeIs('super-admin.entreprises.*') ? 'active' : '' }}">
        <i class="fas fa-building"></i> Entreprises
      </a>
      
      <div class="sa-nav-label">Administration</div>
      <a href="{{ route('super-admin.users.index') }}" class="sa-nav-item {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> Utilisateurs
      </a>
      <a href="{{ route('super-admin.settings') }}" class="sa-nav-item {{ request()->routeIs('super-admin.settings') ? 'active' : '' }}">
        <i class="fas fa-cog"></i> Paramètres
      </a>
      <a href="{{ route('super-admin.logs') }}" class="sa-nav-item {{ request()->routeIs('super-admin.logs') ? 'active' : '' }}">
        <i class="fas fa-shield-alt"></i> Audit Logs
      </a>
    </ul>
    
    <div style="padding:20px 24px; border-top:1px solid rgba(255,255,255,0.05);">
      <div style="font-size:12px; color:rgba(255,255,255,0.4); margin-bottom:4px;">Connecté en tant que</div>
      <div style="font-size:14px; font-weight:600; color:white; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-user-shield text-warning"></i> {{ Auth::user()->nom ?? 'Super Admin' }}
      </div>
      <form method="POST" action="{{ route('logout') }}" style="margin-top:15px;">
        @csrf
        <button type="submit" style="background:rgba(255,255,255,0.1); border:none; color:white; font-size:12px; cursor:pointer; padding:8px 12px; border-radius:6px; width:100%; transition:all 0.2s;">
           <i class="fas fa-sign-out-alt"></i> Déconnexion
        </button>
      </form>
    </div>
  </aside>

  <!-- TOPBAR -->
  <header class="sa-topbar">
    <div class="sa-topbar-title">@yield('page_title', 'Administration Plateforme')</div>
    <div class="ms-auto d-flex align-items-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-sm" style="background:#F3F4F6; color:#4B5563; border:1px solid #E5E7EB; font-weight:500;">
            <i class="fas fa-arrow-left"></i> Quitter Super Admin
        </a>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="sa-content">
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
