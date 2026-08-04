<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin Entreprise') — GEL CABINET</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    :root {
      /* Palette Admin (Plus sombre et Corporate) */
      --admin-primary: #1E293B; /* Slate 800 */
      --admin-primary-dark: #0F172A; /* Slate 900 */
      --admin-primary-light: #F8FAFC;
      --admin-accent: #3B82F6; /* Blue 500 */
      
      --admin-sidebar-bg: var(--admin-primary-dark);
      --admin-sidebar-w: 240px;
      --admin-topbar-h: 60px;
      
      --admin-text: #1F2A44;
      --admin-text-muted: #6B7280;
      --admin-border: #E5E7EB;
      --admin-bg: #F1F5F9;
      --admin-success: #10B981;
      --admin-danger: #EF4444;
      --admin-warning: #F59E0B;
      --admin-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }
    html { font-size:14px; }
    body { font-family:'Inter',sans-serif; color:var(--admin-text); background:var(--admin-bg); min-height:100vh; }
    a { text-decoration:none; color:inherit; }

    /* ─── TOPBAR ─── */
    .admin-topbar {
      position:fixed; top:0; left:var(--admin-sidebar-w); right:0;
      height:var(--admin-topbar-h);
      background:white;
      display:flex; align-items:center; padding:0 24px; gap:16px;
      z-index:1000; border-bottom:1px solid var(--admin-border);
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .admin-topbar-title {
        font-size: 16px; font-weight: 600; color: var(--admin-primary);
        flex: 1;
    }

    .admin-topbar-btn {
      width:36px; height:36px; border:none; background:var(--admin-primary-light);
      border-radius:50%; color:var(--admin-text-muted); font-size:16px;
      display:flex; align-items:center; justify-content:center; cursor:pointer;
      transition:all 150ms;
    }
    .admin-topbar-btn:hover { background:var(--admin-border); color:var(--admin-primary); }
    
    .admin-avatar {
      width:36px; height:36px; border-radius:50%;
      background:var(--admin-accent); color:white;
      display:flex; align-items:center; justify-content:center;
      font-weight:700; font-size:13px; cursor:pointer; flex-shrink:0;
    }

    /* ─── SIDEBAR ─── */
    .admin-sidebar {
      position:fixed; top:0; left:0; bottom:0;
      width:var(--admin-sidebar-w);
      background:var(--admin-sidebar-bg);
      display:flex; flex-direction:column;
      z-index:999;
    }
    .admin-sidebar-brand {
      height: var(--admin-topbar-h);
      padding:0 20px; border-bottom:1px solid rgba(255,255,255,0.05);
      display:flex; align-items:center; gap:12px;
    }
    .admin-sidebar-icon {
      width:32px; height:32px; border-radius:8px;
      background:var(--admin-accent);
      color:white; display:flex; align-items:center; justify-content:center;
      font-size:14px; flex-shrink:0;
    }
    .admin-sidebar-title { font-size:15px; font-weight:700; color:white; letter-spacing: 0.5px; }

    .admin-nav { list-style:none; padding:12px; flex:1; overflow-y:auto; }
    .admin-nav-section { font-size:11px; font-weight:600; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px; padding:16px 12px 8px; }
    
    .admin-nav-item {
      display:flex; align-items:center; gap:12px;
      padding:10px 14px; margin-bottom: 4px; border-radius:8px;
      font-size:14px; color:rgba(255,255,255,0.7); cursor:pointer;
      transition:all 150ms;
    }
    .admin-nav-item:hover { background:rgba(255,255,255,0.05); color:white; }
    .admin-nav-item.active { background:var(--admin-accent); color:white; font-weight:500; }
    .admin-nav-item i { width:18px; text-align:center; font-size:15px; }

    /* ─── CONTENT ─── */
    .admin-content {
      margin-left:var(--admin-sidebar-w);
      margin-top:var(--admin-topbar-h);
      padding:32px;
      min-height:calc(100vh - var(--admin-topbar-h));
    }

    /* ─── COMPONENTS ─── */
    .admin-card { background:white; border:1px solid var(--admin-border); border-radius:12px; box-shadow: var(--admin-shadow); margin-bottom: 24px; }
    .admin-card-header { padding:20px 24px; border-bottom:1px solid var(--admin-border); display:flex; align-items:center; justify-content:space-between; }
    .admin-card-title { font-size:16px; font-weight:600; color:var(--admin-primary); }
    .admin-card-body { padding:24px; }

    .admin-btn {
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 16px; border-radius:6px; font-size:14px; font-weight:500;
      border:1px solid transparent; cursor:pointer; transition:all 150ms;
    }
    .admin-btn-primary { background:var(--admin-accent); color:white; }
    .admin-btn-primary:hover { background:#2563EB; color:white; }
    .admin-btn-secondary { background:white; color:var(--admin-primary); border-color:var(--admin-border); }
    .admin-btn-secondary:hover { background:var(--admin-primary-light); }

    .admin-page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
    .admin-page-title { font-size:24px; font-weight:700; color:var(--admin-primary); }
    .admin-page-sub { font-size:14px; color:var(--admin-text-muted); margin-top:4px; }

    /* ─── OMNISEARCH ─── */
    .admin-omnisearch {
      background: var(--admin-bg);
      border: 1px solid var(--admin-border);
      border-radius: 20px;
      padding: 6px 16px;
      color: var(--admin-text);
      font-size: 13px;
      width: 300px;
      outline: none;
      transition: all 0.2s;
    }
    .admin-omnisearch:focus { border-color: var(--admin-accent); width: 350px; }

    /* ─── DROPDOWNS ─── */
    .admin-dropdown {
      position:absolute; background:white; border:1px solid var(--admin-border);
      border-radius:8px; box-shadow:var(--admin-shadow); padding:6px 0;
      min-width:200px; z-index:1100;
      display:none;
    }
    .admin-dropdown.open { display:block; }
  </style>
  @stack('styles')
</head>
<body>

<header class="admin-topbar">
  <div class="admin-topbar-title">Espace Administrateur @if(isset($cabinet)) — {{ $cabinet->nom ?? $cabinet->nom_entreprise ?? 'Cabinet' }} @endif</div>
  
  <div style="position: relative; flex:1; max-width:400px; margin-right: 16px;">
    <i class="fas fa-magic" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--admin-accent);"></i>
    <input type="text" id="adminOmnisearchInput" class="admin-omnisearch" placeholder="Demandez à l'IA ou cherchez..." style="padding-left: 36px; width:100%;">
    
    <div class="admin-dropdown" id="adminSearchDd" style="left:0; right:auto; top:calc(100% + 8px); width:100%; padding:0;">
      <div id="adminSearchResults" style="max-height:400px; overflow-y:auto; padding:8px 0;">
        <div style="padding:16px; text-align:center; color:var(--admin-text-muted); font-size:12px;">Tapez une commande ou posez une question à GEL Intelligence.</div>
      </div>
    </div>
  </div>

  <div style="position:relative;">
    <button class="admin-topbar-btn" title="Notifications" onclick="toggleAdminDropdown('notifDd')">
      <i class="fas fa-bell"></i>
      <span style="position:absolute; top:-2px; right:-2px; background:var(--admin-danger); color:white; font-size:9px; font-weight:bold; padding:2px 5px; border-radius:10px; border:2px solid white;">2</span>
    </button>
    <div class="admin-dropdown" id="notifDd" style="right:0;top:calc(100% + 10px); width:320px; padding:0;">
      <div style="padding:12px 14px; border-bottom:1px solid var(--admin-border); font-weight:700; font-size:13px; background:var(--admin-bg); border-radius:8px 8px 0 0; display:flex; justify-content:space-between; align-items:center;">
        <span>Notifications</span>
        <a href="{{ route('gel-admin.settings.notifications') }}" style="color:var(--admin-text-muted);" title="Paramètres"><i class="fas fa-cog"></i></a>
      </div>
      <div style="max-height:300px; overflow-y:auto; padding:0;">
        <div style="padding:12px; border-bottom:1px solid #f1f5f9; display:flex; gap:12px; align-items:flex-start; cursor:pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
          <div style="width:32px; height:32px; border-radius:50%; background:#EFF6FF; color:#3B82F6; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fas fa-user-plus"></i></div>
          <div>
            <div style="font-size:12px; font-weight:600; color:var(--admin-text);">Nouveau membre ajouté</div>
            <div style="font-size:11px; color:var(--admin-text-muted);">David a rejoint l'équipe en tant que Comptable.</div>
          </div>
        </div>
        <div style="padding:12px; display:flex; gap:12px; align-items:flex-start; cursor:pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
          <div style="width:32px; height:32px; border-radius:50%; background:#FEF2F2; color:#EF4444; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fas fa-exclamation-triangle"></i></div>
          <div>
            <div style="font-size:12px; font-weight:600; color:var(--admin-text);">Alerte de sécurité</div>
            <div style="font-size:11px; color:var(--admin-text-muted);">Nouvelle connexion détectée depuis un appareil inconnu.</div>
          </div>
        </div>
      </div>
      <div style="padding:8px; border-top:1px solid var(--admin-border); text-align:center;">
        <a href="#" style="font-size:12px; color:var(--admin-accent); font-weight:500;">Marquer tout comme lu</a>
      </div>
    </div>
  </div>
  
  <div style="display:flex; align-items:center; gap:12px; padding-left:16px; border-left:1px solid var(--admin-border);">
    <div style="text-align:right;">
        <div style="font-size:13px; font-weight:600; color:var(--admin-primary);">{{ auth()->user()->name }}</div>
        <div style="font-size:11px; color:var(--admin-text-muted);">Propriétaire</div>
    </div>
    <div class="admin-avatar" onclick="document.getElementById('logoutForm').submit();" title="Déconnexion">
      {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
    </div>
  </div>
</header>

<aside class="admin-sidebar">
  <div class="admin-sidebar-brand">
    <div class="admin-sidebar-icon"><i class="fas fa-shield-alt"></i></div>
    <div class="admin-sidebar-title">GEL ADMIN</div>
  </div>

  <ul class="admin-nav">
    <li>
      <a href="{{ route('gel-admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-pie"></i> Vue d'ensemble
      </a>
    </li>

    <li class="admin-nav-section">Entreprise</li>
    <li>
      <a href="{{ route('gel-admin.profile.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.profile.index') ? 'active' : '' }}">
        <i class="fas fa-building"></i> Profil & Identité
      </a>
    </li>
    <li>
      <a href="{{ route('gel-admin.profile.documents.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.profile.documents.*') ? 'active' : '' }}">
        <i class="fas fa-file-contract"></i> Documents légaux
      </a>
    </li>

    <li class="admin-nav-section">Équipe & Rôles</li>
    <li>
      <a href="{{ route('gel-admin.team.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.team.index') ? 'active' : '' }}">
        <i class="fas fa-users"></i> Membres de l'équipe
      </a>
    </li>
    <li>
      <a href="{{ route('gel-admin.team.roles.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.team.roles.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag"></i> Matrice des rôles
      </a>
    </li>
    <li>
      <a href="{{ route('gel-admin.team.invitations.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.team.invitations.*') ? 'active' : '' }}">
        <i class="fas fa-envelope-open"></i> Invitations
      </a>
    </li>

    <li class="admin-nav-section">Portails</li>
    <li>
      <a href="{{ route('gel-admin.dashboard') }}" class="admin-nav-item">
        <i class="fas fa-laptop-house"></i> Espace Client
      </a>
    </li>

    <li class="admin-nav-section">Abonnement</li>
    <li>
      <a href="{{ route('gel-admin.subscription.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.subscription.index') ? 'active' : '' }}">
        <i class="fas fa-gem"></i> Mon Plan
      </a>
    </li>
    <li>
      <a href="{{ route('gel-admin.subscription.invoices') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.subscription.invoices') ? 'active' : '' }}">
        <i class="fas fa-file-invoice-dollar"></i> Facturation
      </a>
    </li>

    <li class="admin-nav-section">Paramètres Système</li>
    <li>
      <a href="{{ route('gel-admin.security.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.security.*') ? 'active' : '' }}">
        <i class="fas fa-lock"></i> Sécurité & Sessions
      </a>
    </li>
    <li>
      <a href="{{ route('gel-admin.audit-logs.index') }}" class="admin-nav-item {{ request()->routeIs('gel-admin.audit-logs.*') ? 'active' : '' }}">
        <i class="fas fa-history"></i> Historique d'audit
      </a>
    </li>
  </ul>
</aside>

<main class="admin-content">
  @if(session('success'))
    <div class="alert alert-success border-0" style="background:#ECFDF5; color:#065F46; border-radius:8px;">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger border-0" style="background:#FEF2F2; color:#991B1B; border-radius:8px;">
      <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    </div>
  @endif

  @yield('content')
</main>

<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.toggleAdminDropdown = function(id) {
    var el = document.getElementById(id);
    if (!el) return;
    var open = el.classList.contains('open');
    document.querySelectorAll('.admin-dropdown.open').forEach(function(d){ d.classList.remove('open'); });
    if (!open) el.classList.add('open');
  };

  document.addEventListener('click', function(e) {
    if(!e.target.closest('.admin-avatar') && !e.target.closest('.admin-topbar-btn') && !e.target.closest('.admin-dropdown') && !e.target.closest('.admin-omnisearch')) {
      document.querySelectorAll('.admin-dropdown').forEach(d => d.classList.remove('open'));
    }
  });

  var omnisearch = document.getElementById('adminOmnisearchInput');
  if(omnisearch) {
    omnisearch.addEventListener('focus', function() {
      toggleAdminDropdown('adminSearchDd');
    });
  }
</script>
@stack('scripts')
</body>
</html>
