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

<div class="gel-sidebar-heading">ADMINISTRATION RACINE</div>

<a href="{{ route('company.dashboard') }}" class="gel-sidebar-link">
    <i class="fas fa-tachometer-alt"></i> Vue d'ensemble
</a>
<a href="{{ route('company.profile') }}" class="gel-sidebar-link">
    <i class="fas fa-building"></i> Mon entreprise
</a>
<a href="{{ route('company.users') }}" class="gel-sidebar-link">
    <i class="fas fa-users"></i> Équipe
</a>
<a href="{{ route('company.roles') }}" class="gel-sidebar-link">
    <i class="fas fa-user-shield"></i> Rôles & Accès
</a>
<a href="{{ route('company.client-requests') }}" class="gel-sidebar-link">
    <i class="fas fa-envelope"></i> Demandes B2C
</a>
<a href="{{ route('company.subscription') }}" class="gel-sidebar-link">
    <i class="fas fa-credit-card"></i> Abonnement
</a>
<a href="{{ route('company.security') }}" class="gel-sidebar-link">
    <i class="fas fa-shield-alt"></i> Sécurité
</a>
<a href="{{ route('company.audit') }}" class="gel-sidebar-link">
    <i class="fas fa-history"></i> Historique
</a>

<div class="gel-workspace-switch">
    <a href="{{ route('gel-business.dashboard') }}?clear_workspace=1" class="gel-workspace-switch-link">
        <i class="fas fa-exchange-alt"></i> Changer d'espace
    </a>
</div>

@if($user)
<div style="padding:12px;border-top:1px solid var(--gel-border);display:flex;align-items:center;gap:10px;">
    <div class="gel-avatar" style="background:#00A86B;">{{ strtoupper(substr($user->name ?? $user->email, 0, 2)) }}</div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:600;color:var(--gel-text-primary);">{{ $user->name ?? $user->email }}</div>
        <div style="font-size:11px;color:var(--gel-text-muted);">Administrateur</div>
    </div>
    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form2').submit();" style="color:var(--gel-text-muted);">
        <i class="fas fa-sign-out-alt"></i>
    </a>
    <form id="logout-form2" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
</div>
@endif
