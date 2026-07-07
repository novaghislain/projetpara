@php
$user = Auth::user();
$client = $user?->client ?? null;
@endphp
<header class="gel-topbar">
    <div class="gel-topbar-left">
        <button class="gel-topbar-icon" onclick="toggleSidebar()" style="display:none;"><i class="fas fa-bars"></i></button>
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="gel-logo" style="width:28px;height:28px;font-size:12px;background:#00A86B;">G</div>
            <span style="font-weight:600;font-size:14px;color:var(--gel-text-primary);">GEL Business</span>
        </div>
    </div>
    <div class="gel-topbar-right">
        <div class="gel-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Rechercher…" id="gelSearchInput">
        </div>
        <button class="gel-topbar-icon" title="Contacter mon comptable" onclick="showToast('Contactez votre comptable par email','info')">
            <i class="fas fa-headset"></i>
        </button>
        <button class="gel-topbar-icon" title="Notifications"><i class="fas fa-bell"></i></button>
        <div class="gel-dropdown">
            <div class="gel-topbar-user" onclick="toggleDropdown('userMenuBiz')">
                <div class="gel-avatar" style="background:#00A86B;">{{ strtoupper(substr($user->name ?? $user->email, 0, 2)) }}</div>
                <i class="fas fa-chevron-down" style="font-size:10px;color:var(--gel-text-muted);"></i>
            </div>
            <div class="gel-dropdown-menu" id="userMenuBiz">
                <a href="{{ route('gel-business.profile') }}" class="gel-dropdown-item"><i class="fas fa-building"></i> Mon entreprise</a>
                <div class="gel-dropdown-divider"></div>
                <a href="{{ route('logout') }}" class="gel-dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form3').submit();">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        <form id="logout-form3" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    </div>
</header>
