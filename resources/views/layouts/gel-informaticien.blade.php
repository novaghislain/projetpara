<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Pôle Informatique GEL SABINET</title>
    
    @vite('resources/css/app.css')
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --gel-primary: #163A5E;
            --gel-primary-light: #2A547E;
            --gel-accent: #FF7900;
            --gel-sidebar-bg: #1a2938;
            --gel-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gel-bg);
            color: #333;
            display: flex;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }
        
        .sidebar {
            width: 260px;
            background: var(--gel-sidebar-bg);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }
        
        .sidebar-brand {
            font-weight: 700;
            font-size: 18px;
            color: white;
            text-decoration: none;
            line-height: 1.2;
        }
        .sidebar-brand span {
            color: var(--gel-accent);
            display: block;
            font-size: 13px;
            font-weight: 500;
        }
        
        .nav-menu {
            padding: 20px 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .nav-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: 0.2s;
            gap: 12px;
            font-weight: 500;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left: 3px solid var(--gel-accent);
        }
        
        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .main-content {
            flex-grow: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .topbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 900;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gel-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .content-area {
            padding: 30px;
            flex-grow: 1;
        }

        .kpi-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--gel-primary);
        }
        .kpi-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div style="width: 40px; height: 40px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-desktop" style="color: var(--gel-primary); font-size: 20px;"></i>
            </div>
            <a href="{{ route('gel-informaticien.dashboard') }}" class="sidebar-brand">
                GEL SABINET
                <span>Pôle Informatique</span>
            </a>
        </div>
        
        <div class="nav-menu">
            <a href="{{ route('gel-informaticien.dashboard') }}" class="nav-item {{ request()->routeIs('gel-informaticien.dashboard') ? 'active' : '' }}">
                <i class="fas fa-heartbeat"></i> Santé Globale
            </a>
            <a href="{{ route('gel-informaticien.tickets.index') }}" class="nav-item {{ request()->routeIs('gel-informaticien.tickets.*') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i> Centre de Support
            </a>
            <a href="{{ route('gel-informaticien.security.index') }}" class="nav-item {{ request()->routeIs('gel-informaticien.security.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i> Sécurité & Accès
            </a>
            <a href="{{ route('gel-informaticien.maintenance.index') }}" class="nav-item {{ request()->routeIs('gel-informaticien.maintenance.*') ? 'active' : '' }}">
                <i class="fas fa-server"></i> Sauvegardes
            </a>
            <a href="{{ route('gel-informaticien.dev-requests.index') }}" class="nav-item {{ request()->routeIs('gel-informaticien.dev-requests.*') ? 'active' : '' }}">
                <i class="fas fa-code"></i> Projets Digitaux
            </a>
        </div>

        <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm w-100">
                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <h4 class="mb-0">@yield('title')</h4>
            
            <div class="d-flex align-items-center gap-4">
                <div class="user-profile">
                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->prenom, 0, 1) . substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px; line-height: 1.2;">{{ auth()->user()->prenom }} {{ auth()->user()->name }}</div>
                        <div style="font-size: 12px; color: #666;">Informaticien (Admin)</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
