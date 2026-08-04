@php
$user = Auth::user();
$cabinet = $user?->cabinet ?? null;
$clients = [];
try { $clients = \App\Models\Gel\Client::where('cabinet_id', $cabinet?->id)->where('statut', 'actif')->get(); } catch(\Exception $e) {}
$currentClientId = request('client_id', session('current_client_id'));
@endphp
<header class="gel-topbar">
    <div class="gel-topbar-left">
        <button class="gel-topbar-icon" onclick="toggleSidebar()" style="display:none;"><i class="fas fa-bars"></i></button>

        {{-- GO TO GEL BUSINESS --}}
        <div class="gel-dropdown">
            <button class="gel-btn gel-btn-secondary gel-btn-sm" onclick="toggleDropdown('goToBusiness')">
                <i class="fas fa-exchange-alt"></i> Go To GEL Business <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i>
            </button>
            <div class="gel-dropdown-menu" id="goToBusiness" style="width:320px;padding:8px;">
                <div style="font-weight:700;font-size:14px;padding:4px 8px 8px;border-bottom:1px solid var(--gel-border);">ALL CLIENTS</div>
                @forelse($clients as $c)
                <a href="{{ route('gel-business.dashboard', ['client_id' => $c->id]) }}" class="gel-dropdown-item">
                    <i class="fas fa-building" style="width:16px;"></i>
                    <div>
                        <div style="font-weight:600;">{{ $c->nom_entreprise }}</div>
                        <div style="font-size:11px;color:var(--gel-text-muted);">{{ $c->ifu ?? 'N/A' }}</div>
                    </div>
                </a>
                @empty
                <div class="gel-dropdown-item" style="color:var(--gel-text-muted);">Aucun client pour le moment</div>
                @endforelse
                <div class="gel-dropdown-divider"></div>
                <a href="{{ route('gel-accountant.clients') }}" class="gel-dropdown-item" style="font-weight:600;color:var(--gel-primary);">
                    <i class="fas fa-cog"></i> Gérer les clients
                </a>
            </div>
        </div>

        {{-- Accountant Tools --}}
        <div class="gel-dropdown">
            <button class="gel-btn gel-btn-secondary gel-btn-sm" onclick="toggleDropdown('accountantTools')">
                <i class="fas fa-wrench"></i> Accountant Tools <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i>
            </button>
            <div class="gel-dropdown-menu gel-dropdown-two-col" id="accountantTools">
                <div class="gel-dropdown-col">
                    <div class="gel-dropdown-col-title">Quick Links</div>
                    <a href="{{ route('gel-accountant.comptabilite.plan-comptable') }}" class="gel-dropdown-item"><i class="fas fa-sitemap"></i> Plan comptable</a>
                    <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="gel-dropdown-item"><i class="fas fa-pen"></i> Saisie écritures</a>
                    <a href="#" class="gel-dropdown-item" onclick="showToast('Rapprochement','info')"><i class="fas fa-handshake"></i> Rapprocher</a>
                    <a href="{{ route('gel-accountant.rapports.index') }}" class="gel-dropdown-item"><i class="fas fa-chart-bar"></i> Rapports</a>
                    <a href="#" class="gel-dropdown-item" onclick="showToast('Écritures supprimées','info')"><i class="fas fa-trash-alt"></i> Écritures supprimées</a>
                    <a href="#" class="gel-dropdown-item" onclick="showToast('Clôture des comptes','info')"><i class="fas fa-lock"></i> Clôturer</a>
                </div>
                <div class="gel-dropdown-col">
                    <div class="gel-dropdown-col-title">Outils</div>
                    <a href="#" class="gel-dropdown-item" onclick="showToast('Reclassement','info')"><i class="fas fa-tags"></i> Reclasser</a>
                    <a href="#" class="gel-dropdown-item" onclick="showToast('Options rapports','info')"><i class="fas fa-sliders-h"></i> Options rapports</a>
                    <a href="#" class="gel-dropdown-item" onclick="showToast('Annuler factures','info')"><i class="fas fa-file-invoice"></i> Annuler factures</a>
                    <a href="#" class="gel-dropdown-item" onclick="showToast('Modèles COA','info')"><i class="fas fa-file-import"></i> Modèles COA</a>
                </div>
            </div>
        </div>
    </div>

    <div class="gel-topbar-right">
        {{-- Search --}}
        <div class="gel-search-wrapper">
            <div class="gel-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Rechercher... Ctrl+K" id="gelSearchInput"
                       onfocus="openSearchDropdown()" onblur="setTimeout(closeSearchDropdown, 200)">
                <kbd style="font-size:10px;background:var(--gel-border);padding:1px 4px;border-radius:3px;">Ctrl+K</kbd>
            </div>
            <div class="gel-search-dropdown" id="gelSearchDropdown">
                <div class="search-section">RÉCEMMENTS</div>
                <div class="search-item" onclick="closeSearchDropdown();showToast('Recherche...','info')">
                    <i class="fas fa-pen"></i> OD-2026-124 - Achat fournitures
                </div>
                <div class="search-section">RACCOURCIS</div>
                <a href="{{ route('gel-accountant.dashboard') }}" class="search-item" onclick="closeSearchDropdown()">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
                <a href="{{ route('gel-accountant.clients') }}" class="search-item" onclick="closeSearchDropdown()">
                    <i class="fas fa-users"></i> Liste des clients
                </a>
                <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="search-item" onclick="closeSearchDropdown()">
                    <i class="fas fa-pen"></i> Saisir une écriture
                </a>
            </div>
        </div>

        {{-- Icons --}}
        <button class="gel-topbar-icon" title="Fil d'actualité"><i class="fas fa-rss"></i></button>
        <button class="gel-topbar-icon" title="Aide"><i class="fas fa-question-circle"></i></button>
        <button class="gel-topbar-icon" title="Applications"><i class="fas fa-th"></i></button>

        {{-- Messagerie icon with unread badge --}}
        @php
            try {
                $unreadMsgCount = \App\Models\Gel\GelMessage::where('cabinet_id', $cabinet?->id)
                    ->where('sender_type', 'business')
                    ->where('est_lu', false)
                    ->count();
            } catch(\Exception $e) { $unreadMsgCount = 0; }
        @endphp
        <a href="{{ route('gel-accountant.messagerie') }}" class="gel-topbar-icon" title="Messagerie" style="position:relative; text-decoration: none; color: inherit;">
            <i class="fas fa-comments"></i>
            @if($unreadMsgCount > 0)
            <span style="position:absolute; top:-4px; right:-4px; background:var(--gel-danger); color:#fff; border-radius:50%; width:17px; height:17px; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; line-height:1;">{{ $unreadMsgCount > 9 ? '9+' : $unreadMsgCount }}</span>
            @endif
        </a>

        {{-- Notifications --}}
        <div class="gel-dropdown">
            <button class="gel-topbar-icon gel-notif-dot" onclick="toggleDropdown('notifDropdown')" title="Notifications">
                <i class="fas fa-bell"></i>
            </button>
            <div class="gel-dropdown-menu gel-notif-dropdown" id="notifDropdown">
                <div class="gel-notif-header">
                    <span>Notifications</span>
                    <a href="#" style="font-size:12px;font-weight:600;color:var(--gel-primary);text-decoration:none;">Tout marquer</a>
                </div>
                <div class="gel-notif-item">
                    <i class="fas fa-exclamation-triangle" style="color:var(--gel-warning);"></i>
                    <div>
                        <div>Facture impayée - SARL Bénin - 150 000 F</div>
                        <div class="notif-time">Il y a 2 heures</div>
                    </div>
                </div>
                <div class="gel-notif-item">
                    <i class="fas fa-check-circle" style="color:var(--gel-success);"></i>
                    <div>
                        <div>Écriture validée - OD-2026-124</div>
                        <div class="notif-time">Il y a 5 heures</div>
                    </div>
                </div>
                <div class="gel-notif-item">
                    <i class="fas fa-calendar" style="color:var(--gel-info);"></i>
                    <div>
                        <div>Échéance TVA dans 5 jours</div>
                        <div class="notif-time">Il y a 1 jour</div>
                    </div>
                </div>
                <div style="padding:8px;text-align:center;">
                    <a href="#" style="font-size:12px;color:var(--gel-primary);text-decoration:none;font-weight:600;">Voir toutes</a>
                </div>
            </div>
        </div>

        <button class="gel-topbar-icon" title="Paramètres" onclick="window.location.href='{{ route('gel-accountant.settings') }}'">
            <i class="fas fa-cog"></i>
        </button>

        {{-- User --}}
        <div class="gel-dropdown">
            <div class="gel-topbar-user" onclick="toggleDropdown('userMenu')">
                <div class="gel-avatar" style="background:#005BAC;">{{ strtoupper(substr($user->name ?? $user->email, 0, 2)) }}</div>
                <i class="fas fa-chevron-down" style="font-size:10px;color:var(--gel-text-muted);"></i>
            </div>
            <div class="gel-dropdown-menu gel-user-dropdown" id="userMenu">
                <div class="gel-user-header">
                    <div class="gel-user-name">{{ $user->name ?? $user->email }}</div>
                    <div class="gel-user-email">{{ $user->email }}</div>
                    <div class="gel-user-role">Comptable</div>
                </div>
                <a href="{{ route('gel-accountant.profile') }}" class="gel-dropdown-item"><i class="fas fa-user"></i> Mon profil</a>
                <a href="{{ route('gel-accountant.settings') }}" class="gel-dropdown-item"><i class="fas fa-cog"></i> Paramètres</a>
                <div class="gel-dropdown-divider"></div>
                <a href="{{ route('logout') }}" class="gel-dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</header>
