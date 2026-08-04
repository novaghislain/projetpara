<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GEL Cabinet')</title>

    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --gel-primary: #0a2540;
            --gel-accent: #00d4aa;
            --gel-accent-2: #635bff;
            --gel-warning: #ffb800;
            --gel-danger: #ff4d6d;
            --gel-bg: #f6f9fc;
            --gel-sidebar-bg: #0f1b2d;
            --gel-sidebar-hover: #1a2d4a;
            --gel-sidebar-active: #635bff;
            --gel-text-muted: #697386;
            --gel-border: #e3e8ee;
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
            --header-height: 64px;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--gel-bg);
            color: #1a1f36;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ═══════ SIDEBAR ═══════ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--gel-sidebar-bg);
            z-index: 1030;
            transition: width 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .sidebar::-webkit-scrollbar { display: none; }
        
        

        .sidebar-brand {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }
        .sidebar-brand .logo-mark {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--gel-accent), var(--gel-accent-2));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .sidebar-brand span {
            color: white;
            font-weight: 800;
            font-size: 1.2rem;
            white-space: nowrap;
            position: relative;
        }

        .sidebar-nav { flex: 1; padding: 0.75rem 0; }
        .sidebar-section {
            padding: 0.5rem 1.25rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.35);
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem; margin: 0 0 4px 1rem; border-radius: 20px 0 0 20px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            
            white-space: nowrap;
            position: relative;
        }
        .sidebar-link:hover {
            background: var(--gel-sidebar-hover);
            color: white;
        }
                .sidebar-link.active {
            background: var(--gel-bg);
            color: var(--gel-primary);
            font-weight: 600;
        }
        .sidebar-link.active::before,
        .sidebar-link.active::after {
            content: '';
            position: absolute;
            right: 0;
            width: 20px;
            height: 20px;
            background: transparent;
            pointer-events: none;
        }
        .sidebar-link.active::before {
            bottom: 100%;
            border-bottom-right-radius: 20px;
            box-shadow: 10px 10px 0 10px var(--gel-bg);
        }
        .sidebar-link.active::after {
            top: 100%;
            border-top-right-radius: 20px;
            box-shadow: 10px -10px 0 10px var(--gel-bg);
        }
        .sidebar-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .sidebar-link .badge {
            margin-left: auto;
            font-size: 0.7rem;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255,255,255,0.7);
        }
        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gel-accent), var(--gel-accent-2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name { font-size: 0.85rem; font-weight: 600; color: white; }
        .sidebar-user-role { font-size: 0.75rem; color: rgba(255,255,255,0.4); }

        /* ═══════ HEADER ═══════ */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--gel-border);
            z-index: 1020;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            transition: left 0.3s ease;
        }
        .header-left { display: flex; align-items: center; gap: 1rem; }
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gel-text-muted);
            cursor: pointer;
            padding: 0.25rem;
            display: none;
        }
        .header-search {
            display: flex;
            align-items: center;
            background: var(--gel-bg);
            border-radius: 8px;
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--gel-border);
            width: 320px;
        }
        .header-search i { color: var(--gel-text-muted); margin-right: 0.5rem; }
        .header-search input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 0.85rem;
            font-family: inherit;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--gel-border);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gel-text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            text-decoration: none;
        }
        .header-icon:hover {
            background: var(--gel-bg);
            color: var(--gel-primary);
        }
        .header-icon .notif-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gel-danger);
            border: 2px solid white;
        }

        /* ═══════ MAIN CONTENT ═══════ */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        /* ═══════ SIDEBAR COLLAPSED ═══════ */
        .sidebar.collapsed { width: var(--sidebar-collapsed); }
        .sidebar.collapsed + .main-header { left: var(--sidebar-collapsed); }
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed); }
        .sidebar.collapsed .sidebar-brand span,
        .sidebar.collapsed .sidebar-link span,
        .sidebar.collapsed .sidebar-section,
        .sidebar.collapsed .sidebar-user-info,
        .sidebar.collapsed .badge { display: none; }
        .sidebar.collapsed .sidebar-link { justify-content: center; padding: 0.65rem 0; }
        .sidebar.collapsed .sidebar-link i { margin: 0; }

        /* ═══════ RESPONSIVE ═══════ */
        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed);
                transform: translateX(-100%);
            }
            .sidebar.show { transform: translateX(0); width: var(--sidebar-width); }
            .sidebar.show .sidebar-brand span,
            .sidebar.show .sidebar-link span,
            .sidebar.show .sidebar-section,
            .sidebar.show .sidebar-user-info,
            .sidebar.show .badge { display: inline; }
            .sidebar.show .sidebar-link { justify-content: flex-start; padding: 0.65rem 1rem; margin: 0 0 4px 1rem; border-radius: 20px 0 0 20px; }

            .main-header { left: 0; }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: block; }
            .header-search { width: 200px; }
        }

        /* ═══════ UTILITIES ═══════ */
        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gel-primary);
            margin: 0;
        }
        .page-subtitle {
            font-size: 0.9rem;
            color: var(--gel-text-muted);
            margin: 0.25rem 0 0 0;
        }
        .breadcrumb-custom {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            font-size: 0.8rem;
            color: var(--gel-text-muted);
            margin-bottom: 1rem;
        }
        .breadcrumb-custom a {
            color: var(--gel-accent-2);
            text-decoration: none;
        }
        .breadcrumb-custom a:hover { text-decoration: underline; }
    </style>

    @stack('styles')
    @yield('styles')
</head>
<body>

    {{-- ═══════ SIDEBAR ═══════ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo-mark">G</div>
            <span>GEL Cabinet</span>
        </div>

        <nav class="sidebar-nav">
            @php
                $user = Auth::user();
                $currentRoute = Route::currentRouteName();
            @endphp

            <div class="sidebar-section">Général</div>
            <a href="{{ route('gel.dashboard') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="{{ route('gel.activity') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'activity') ? 'active' : '' }}">
                <i class="bi bi-activity"></i>
                <span>Activité</span>
                <span class="badge bg-danger rounded-pill" id="sidebar-notif-count">0</span>
            </a>

            <div class="sidebar-section">Comptabilité</div>
            @can('comptabilite.voir')
            <a href="{{ route('gel.comptabilite.plan-comptable.index') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'gel/comptabilite/plan-comptable') ? 'active' : '' }}">
                <i class="bi bi-list-columns-reverse"></i>
                <span>Plan comptable</span>
            </a>
            <a href="{{ route('gel.comptabilite.journaux.index') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'gel/comptabilite/journaux') ? 'active' : '' }}">
                <i class="bi bi-bookmark-check"></i>
                <span>Journaux</span>
            </a>
            <a href="{{ route('gel.comptabilite.ecritures.index') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'gel/comptabilite/ecritures') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Écritures</span>
            </a>
            <a href="{{ route('gel.comptabilite.grand-livre.index') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'gel/comptabilite/grand-livre') ? 'active' : '' }}">
                <i class="bi bi-book"></i>
                <span>Grand Livre</span>
            </a>
            <a href="{{ route('gel.comptabilite.balance.index') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'gel/comptabilite/balance') ? 'active' : '' }}">
                <i class="bi bi-table"></i>
                <span>Balance</span>
            </a>
            <a href="{{ route('gel.comptabilite.etats-financiers.index') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'gel/comptabilite/etats-financiers') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>États financiers</span>
            </a>
            @endcan

            <div class="sidebar-section">Facturation</div>
            @can('crm.consulter')
            <a href="{{ route('gel.factures') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'facture') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Factures</span>
            </a>
            <a href="{{ route('gel.devis') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'devis') ? 'active' : '' }}">
                <i class="bi bi-file-text"></i>
                <span>Devis</span>
            </a>
            <a href="{{ route('gel.relances') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'relance') ? 'active' : '' }}">
                <i class="bi bi-bell"></i>
                <span>Relances</span>
            </a>
            @endcan

            <div class="sidebar-section">Paie & RH</div>
            @can('paie.consulter')
            <a href="{{ route('gel.paie') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'paie') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Paie</span>
            </a>
            <a href="{{ route('gel.employes') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'employe') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Employés</span>
            </a>
            @endcan

            <div class="sidebar-section">Clients</div>
            @can('client.consulter')
            <a href="{{ route('gel.clients') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'client') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>Clients</span>
            </a>
            @endcan

            <div class="sidebar-section">Trésorerie</div>
            @can('comptabilite.consulter')
            <a href="{{ route('gel.tresorerie') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'tresorerie') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i>
                <span>Trésorerie</span>
            </a>
            <a href="{{ route('gel.rapprochement') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'rapprochement') ? 'active' : '' }}">
                <i class="bi bi-bank"></i>
                <span>Rapprochement</span>
            </a>
            @endcan

            <div class="sidebar-section">Fiscalité</div>
            @can('fiscalite.consulter')
            <a href="{{ route('gel.fiscalite') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'fiscalite') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Fiscalité</span>
            </a>
            <a href="{{ route('gel.declarations') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'declaration') ? 'active' : '' }}">
                <i class="bi bi-journal-check"></i>
                <span>Déclarations</span>
            </a>
            @endcan

            <div class="sidebar-section">GED</div>
            @can('ged.consulter')
            <a href="{{ route('gel.ged') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'ged') ? 'active' : '' }}">
                <i class="bi bi-folder"></i>
                <span>Documents</span>
            </a>
            @endcan

            <div class="sidebar-section">Intelligence Artificielle</div>
            @can('ia.consulter')
            <a href="{{ route('gel.ia.chat') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'ia.chat') ? 'active' : '' }}">
                <i class="bi bi-cpu"></i>
                <span>Chat IA</span>
            </a>
            <a href="{{ route('gel.ia.accounting') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'ia.accounting') ? 'active' : '' }}">
                <i class="bi bi-calculator"></i>
                <span>Comptabilité IA</span>
            </a>
            <a href="{{ route('gel.ia.suggestions') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'ia.suggestion') ? 'active' : '' }}">
                <i class="bi bi-lightbulb"></i>
                <span>Suggestions IA</span>
                <span class="badge bg-accent rounded-pill" id="ia-suggestions-count">0</span>
            </a>
            <a href="{{ route('gel.ia.finance') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'ia.finance') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span>Finance Agent</span>
            </a>
            <a href="{{ route('gel.ia.customer') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'ia.customer') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Customer AI</span>
            </a>
            @endcan

            <div class="sidebar-section">Rapports</div>
            @can('comptabilite.consulter')
            <a href="{{ route('gel.reports') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'report') ? 'active' : '' }}">
                <i class="bi bi-pie-chart"></i>
                <span>Rapports</span>
            </a>
            @endcan

            <div class="sidebar-section">Administration</div>
            @can('admin.acces')
            <a href="{{ route('gel.admin.utilisateurs') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'admin.utilisateur') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Utilisateurs</span>
            </a>
            @endcan
            @can('admin.config')
            <a href="{{ route('gel.admin.roles') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'admin.role') ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i>
                <span>Rôles & Permissions</span>
            </a>
            @endcan
            @can('admin.logs')
            <a href="{{ route('gel.admin.audit-logs') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'admin.audit') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Audit Logs</span>
            </a>
            @endcan
            @can('admin.config')
            <a href="{{ route('gel.admin.modules') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'admin.module') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>Modules</span>
            </a>
            @endcan
            @can('admin.acces')
            <a href="{{ route('gel.admin.cabinet') }}"
               class="sidebar-link {{ str_contains($currentRoute, 'admin.cabinet') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span>Mon cabinet</span>
            </a>
            @endcan
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $user->name }}</div>
                    <div class="sidebar-user-role">
                        {{ $user->roles->first()?->label_fr ?? 'Utilisateur' }}
                    </div>
                </div>
                <a href="{{ route('logout') }}" class="text-white-50" onclick="event.preventDefault();document.getElementById('logout-form').submit();" title="Déconnexion">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </aside>

    {{-- ═══════ HEADER ═══════ --}}
    <header class="main-header" id="mainHeader">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div class="header-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Rechercher une fonctionnalité, un client..." id="globalSearch">
                <kbd style="font-size:0.7rem;background:var(--gel-border);padding:0.1rem 0.4rem;border-radius:4px;color:var(--gel-text-muted);margin-left:auto;">Ctrl+K</kbd>
            </div>
        </div>
        <div class="header-right">
            <a href="{{ route('gel.ia.chat') }}" class="header-icon" title="Chat IA">
                <i class="bi bi-cpu"></i>
            </a>
            <a href="{{ route('gel.activity') }}" class="header-icon" title="Notifications" id="notif-icon">
                <i class="bi bi-bell"></i>
                <span class="notif-dot" id="notif-dot" style="display:none;"></span>
            </a>
            <a href="{{ route('gel.admin.cabinet') }}" class="header-icon" title="Paramètres">
                <i class="bi bi-gear"></i>
            </a>
            <div class="vr mx-1" style="height:30px;"></div>
            <div class="d-flex align-items-center gap-2">
                <div class="text-end d-none d-md-block">
                    <div style="font-size:0.8rem;font-weight:600;color:var(--gel-primary);line-height:1.2;">{{ $user->name }}</div>
                    <div style="font-size:0.7rem;color:var(--gel-text-muted);">{{ $user->cabinet?->nom ?? 'Cabinet' }}</div>
                </div>
                <div class="sidebar-user-avatar" style="width:38px;height:38px;font-size:0.8rem;cursor:pointer;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>
        </div>
    </header>

    {{-- ═══════ MAIN CONTENT ═══════ --}}
    <main class="main-content" id="mainContent">
        <div class="container-fluid px-4 py-3">
            @yield('content')
        </div>
    </main>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ─── Sidebar Toggle ───
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        }

        // ─── Global Search (Ctrl+K) ───
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('globalSearch')?.focus();
            }
        });

        document.getElementById('globalSearch')?.addEventListener('keyup', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                window.location.href = '{{ route("gel.search") }}?q=' + encodeURIComponent(this.value.trim());
            }
        });

        // ─── Notifications polling ───
        function pollNotifications() {
            fetch('/api/gel/notifications/unread', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(r => r.json())
            .then(data => {
                const count = data.count || 0;
                const dot = document.getElementById('notif-dot');
                const sidebarBadge = document.getElementById('sidebar-notif-count');
                if (count > 0) {
                    dot.style.display = 'block';
                    sidebarBadge.textContent = count > 99 ? '99+' : count;
                    sidebarBadge.style.display = 'inline';
                } else {
                    dot.style.display = 'none';
                    sidebarBadge.style.display = 'none';
                }
            })
            .catch(() => {});
        }

        // ─── IA Suggestions count ───
        function pollIaSuggestions() {
            fetch('/api/ai/suggestions/unread-count', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('ia-suggestions-count');
                if (badge && data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else if (badge) {
                    badge.style.display = 'none';
                }
            })
            .catch(() => {});
        }

        // Initialiser
        document.addEventListener('DOMContentLoaded', function() {
            pollNotifications();
            pollIaSuggestions();

            // Polling toutes les 60 secondes
            setInterval(pollNotifications, 60000);
            setInterval(pollIaSuggestions, 90000);
        });

        // ─── Close sidebar on outside click (mobile) ───
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
