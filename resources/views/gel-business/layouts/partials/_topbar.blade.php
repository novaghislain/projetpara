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
        @php
            try {
                $client = $user?->client ?? $user?->activeClient;
                $cabinet = $client?->cabinet;
                $unreadBizMsgCount = \App\Models\Gel\GelMessage::where('cabinet_id', $cabinet?->id)
                    ->where('client_id', $client?->id)
                    ->whereIn('sender_type', ['accountant', 'secretary'])
                    ->where('est_lu', false)
                    ->count();
            } catch(\Exception $e) { $unreadBizMsgCount = 0; }
        @endphp
        <a href="{{ route('gel-business.messagerie') }}" class="gel-topbar-icon" title="Messagerie avec votre comptable" style="position:relative; text-decoration: none; color: inherit;">
            <i class="fas fa-comments"></i>
            @if($unreadBizMsgCount > 0)
            <span style="position:absolute; top:-4px; right:-4px; background:var(--gel-danger); color:#fff; border-radius:50%; width:17px; height:17px; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; line-height:1;">{{ $unreadBizMsgCount > 9 ? '9+' : $unreadBizMsgCount }}</span>
            @endif
        </a>
        <button class="gel-topbar-icon" title="Notifications"><i class="fas fa-bell"></i></button>
        <div class="gel-dropdown">
            <div class="gel-topbar-user" onclick="toggleDropdown('userMenuBiz')">
                <div class="gel-avatar" style="background:#00A86B;">{{ strtoupper(substr($user->name ?? $user->email, 0, 2)) }}</div>
                <i class="fas fa-chevron-down" style="font-size:10px;color:var(--gel-text-muted);"></i>
            </div>
            <div class="gel-dropdown-menu" id="userMenuBiz">
                @if(!$user->isAccountant())
                <a href="{{ route('gel-business.profile') }}" class="gel-dropdown-item"><i class="fas fa-building"></i> Mon entreprise</a>
                <div class="gel-dropdown-divider"></div>
                @endif
                <a href="{{ route('logout') }}" class="gel-dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form3').submit();">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        <form id="logout-form3" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    </div>
</header>
