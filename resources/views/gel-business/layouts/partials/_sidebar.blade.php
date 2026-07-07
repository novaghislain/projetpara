@php
$user = Auth::user();
$client = $user?->client ?? null;
$companyName = $client->nom_entreprise ?? 'Mon Entreprise';
$currentSection = $currentSection ?? '';
@endphp
<div class="gel-sidebar-brand">
    <div class="gel-logo" style="background:#00A86B;">G</div>
    <div>
        <span style="font-size:13px;">{{ $companyName }}</span>
        @if($client?->ifu)<small>IFU: {{ $client->ifu }}</small>@endif
    </div>
</div>

<nav class="gel-sidebar-nav">
    <div class="gel-sidebar-heading">GENERAL</div>
    <a href="{{ route('gel-business.dashboard') }}" class="gel-sidebar-link {{ $currentSection === 'dashboard' ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i> Tableau de bord
    </a>

    <div class="gel-sidebar-heading">VENTES</div>
    <a href="{{ route('gel-business.ventes.factures') ?? '#' }}" class="gel-sidebar-link"><i class="fas fa-file-invoice"></i> Factures</a>
    <a href="{{ route('gel-business.ventes.devis') ?? '#' }}" class="gel-sidebar-link"><i class="fas fa-file-alt"></i> Devis</a>
    <a href="{{ route('gel-business.ventes.clients') ?? '#' }}" class="gel-sidebar-link"><i class="fas fa-users"></i> Clients</a>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-box"></i> Produits & services</a>

    <div class="gel-sidebar-heading">DÉPENSES</div>
    <a href="{{ route('gel-business.depenses.index') ?? '#' }}" class="gel-sidebar-link"><i class="fas fa-money-bill-wave"></i> Dépenses</a>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-file-invoice-dollar"></i> Factures fournisseurs</a>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-shopping-cart"></i> Bons de commande</a>
    <a href="#" class="gel-sidebar-link"><i class="fas fa-truck"></i> Fournisseurs</a>

    <div class="gel-sidebar-heading">BANQUE</div>
    <a href="{{ route('gel-business.banque.index') ?? '#' }}" class="gel-sidebar-link"><i class="fas fa-exchange-alt"></i> Transactions</a>
    <a href="{{ route('gel-business.banque.rapprochement') ?? '#' }}" class="gel-sidebar-link"><i class="fas fa-handshake"></i> Rapprochement</a>
    <a href="{{ route('gel-business.comptabilite.grand-livre') }}" class="gel-sidebar-link"><i class="fas fa-book"></i> Grand livre</a>
    <a href="{{ route('gel-business.comptabilite.balance') }}" class="gel-sidebar-link"><i class="fas fa-balance-scale"></i> Balance</a>
    <a href="{{ route('gel-business.comptabilite.etats-financiers') }}" class="gel-sidebar-link"><i class="fas fa-chart-bar"></i> États financiers</a>

    <div class="gel-sidebar-heading">PARAMÈTRES</div>
    <a href="{{ route('gel-business.profile') }}" class="gel-sidebar-link"><i class="fas fa-building"></i> Mon entreprise</a>
    <a href="{{ route('gel-business.inviter-comptable') }}" class="gel-sidebar-link"><i class="fas fa-user-plus"></i> Inviter un comptable</a>
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
