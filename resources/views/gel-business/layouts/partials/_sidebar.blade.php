@php
$user = Auth::user();
$client = $user?->client ?? null;
$companyName = $client->company_name ?? $client->nom_entreprise ?? 'Mon Entreprise';
$currentSection = $currentSection ?? '';
@endphp
<div class="gel-sidebar-brand">
    <div class="gel-logo" style="background:#00A86B;">G</div>
    <div>
        <span style="font-size:13px;">{{ $companyName }}</span>
        @if($client?->ifu)<small>IFU: {{ $client->ifu }}</small>@endif
    </div>
</div>

@php
    $isAccountantInBusiness = ($user && method_exists($user, 'isAccountant') && $user->isAccountant()) || session('current_client_id');
@endphp

    <div class="gel-sidebar-heading">GENERAL</div>
    <a href="{{ route('gel-business.dashboard') }}" class="gel-sidebar-link {{ $currentSection === 'dashboard' ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i> Tableau de bord
    </a>
    @php
        $unreadBizMsgCount = 0;
        if($client) {
            try {
                $unreadBizMsgCount = \App\Models\Gel\GelMessage::where('client_id', $client->id)
                    ->whereIn('sender_type', ['accountant', 'secretary'])
                    ->where('est_lu', false)
                    ->count();
            } catch(\Exception $e) {}
        }
    @endphp

    @if($isAccountantInBusiness)
    <a href="{{ route('gel-accountant.messagerie') }}" class="gel-sidebar-link {{ $currentSection === 'messagerie' ? 'active' : '' }}" style="position:relative;">
        <i class="fas fa-comments"></i> Messagerie Cabinet
    </a>
    @else
    <a href="{{ route('gel-business.messagerie') }}" class="gel-sidebar-link {{ $currentSection === 'messagerie' ? 'active' : '' }}" style="position:relative; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fas fa-comments"></i> Messagerie</span>
        @if($unreadBizMsgCount > 0)
        <span style="background:var(--gel-danger); color:#fff; border-radius:50%; width:18px; height:18px; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center;">{{ $unreadBizMsgCount > 9 ? '9+' : $unreadBizMsgCount }}</span>
        @endif
    </a>
    @endif

    @if(session('active_workspace') === 'secretariat')
    <!-- Menus Secrétariat (Espace Entreprise) -->
    <div class="gel-sidebar-heading">SECRÉTARIAT</div>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-calendar-alt"></i> Agenda</a>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-phone-alt"></i> Centre de relances</a>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-folder-open"></i> Documents (GED)</a>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-tasks"></i> Tâches & Projets</a>
    @elseif(session('active_workspace') === 'gestion')
    <!-- Menus Gestion (Ventes / Dépenses / Banque) -->
    <div class="gel-sidebar-heading">VENTES</div>
    <a href="{{ route('gel-business.ventes.factures') }}" class="gel-sidebar-link {{ $currentSection === 'ventes-factures' ? 'active' : '' }}"><i class="fas fa-file-invoice"></i> Factures</a>
    <a href="{{ route('gel-business.ventes.devis') }}" class="gel-sidebar-link {{ $currentSection === 'ventes-devis' ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Devis</a>
    <a href="{{ route('gel-business.ventes.clients') }}" class="gel-sidebar-link {{ $currentSection === 'ventes-clients' ? 'active' : '' }}"><i class="fas fa-users"></i> Clients</a>
    <a href="{{ route('gel-business.ventes.produits') }}" class="gel-sidebar-link {{ $currentSection === 'ventes-produits' ? 'active' : '' }}"><i class="fas fa-box"></i> Produits &amp; services</a>

    <div class="gel-sidebar-heading">DÉPENSES</div>
    <a href="{{ route('gel-business.depenses.index') }}" class="gel-sidebar-link {{ $currentSection === 'depenses' ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> Dépenses</a>
    <a href="{{ route('gel-business.depenses.factures-fournisseurs') }}" class="gel-sidebar-link {{ $currentSection === 'factures-fournisseurs' ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Factures fournisseurs</a>
    <a href="{{ route('gel-business.depenses.bons-commande') }}" class="gel-sidebar-link {{ $currentSection === 'bons-commande' ? 'active' : '' }}"><i class="fas fa-shopping-cart"></i> Bons de commande</a>
    <a href="{{ route('gel-business.depenses.fournisseurs') }}" class="gel-sidebar-link {{ $currentSection === 'fournisseurs' ? 'active' : '' }}"><i class="fas fa-truck"></i> Fournisseurs</a>

    <div class="gel-sidebar-heading">BANQUE</div>
    <a href="{{ route('gel-business.banque.index') }}" class="gel-sidebar-link {{ $currentSection === 'banque' ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Transactions</a>
    <a href="{{ route('gel-business.banque.rapprochement') }}" class="gel-sidebar-link {{ $currentSection === 'rapprochement' ? 'active' : '' }}"><i class="fas fa-handshake"></i> Rapprochement</a>
    @else
    <!-- Menus Comptabilité (Espace par défaut) -->
    <div class="gel-sidebar-heading">COMPTABILITÉ</div>
    <a href="{{ route('gel-business.comptabilite.grand-livre') }}" class="gel-sidebar-link {{ $currentSection === 'grand-livre' ? 'active' : '' }}"><i class="fas fa-book"></i> Grand livre</a>
    <a href="{{ route('gel-business.comptabilite.balance') }}" class="gel-sidebar-link {{ $currentSection === 'balance' ? 'active' : '' }}"><i class="fas fa-balance-scale"></i> Balance</a>
    <a href="{{ route('gel-business.comptabilite.etats-financiers') }}" class="gel-sidebar-link {{ $currentSection === 'etats-financiers' ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> États financiers</a>
    @endif

    @if(!$isAccountantInBusiness)
    <div class="gel-sidebar-heading">PARAMÈTRES</div>
    @if($user && ($user->isCompanyAdmin() || in_array($user->account_type, ['cabinet', 'entreprise', 'client'])))
    <a href="{{ route('gel-admin.dashboard') }}" class="gel-sidebar-link" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B; border-left: 3px solid #F59E0B;"><i class="fas fa-shield-alt"></i> Espace Administrateur</a>
    @endif
    <a href="{{ route('gel-business.profile') }}" class="gel-sidebar-link"><i class="fas fa-building"></i> Mon entreprise</a>
    <a href="{{ route('gel-business.inviter-comptable') }}" class="gel-sidebar-link"><i class="fas fa-user-plus"></i> Inviter un collaborateur</a>
    @endif

    <div class="gel-workspace-switch">
        <a href="{{ route('gel-business.dashboard') }}?clear_workspace=1" class="gel-workspace-switch-link">
            <i class="fas fa-exchange-alt"></i> Changer d'espace
        </a>
    </div>
</nav>

@if($user)
<div style="padding:12px;border-top:1px solid var(--gel-border);display:flex;align-items:center;gap:10px;">
    <div class="gel-avatar" style="background:#00A86B;">{{ strtoupper(substr($user->name ?? $user->email, 0, 2)) }}</div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:600;color:var(--gel-text-primary);">{{ $user->name ?? $user->email }}</div>
        <div style="font-size:11px;color:var(--gel-text-muted);">Entreprise</div>
    </div>
    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form2').submit();" style="color:var(--gel-text-muted);">
        <i class="fas fa-sign-out-alt"></i>
    </a>
    <form id="logout-form2" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
</div>
@endif
