@php
$user = Auth::user();
$cabinet = $user?->cabinet ?? null;
$currentSection = $currentSection ?? '';
$currentPage = $currentPage ?? '';
@endphp
{{-- Brand --}}
<div class="gel-sidebar-brand">
    <div class="gel-logo">G</div>
    <div>
        <span>GEL Accountant</span>
        @if($cabinet)<small>{{ $cabinet->nom }}</small>@endif
    </div>
</div>

{{-- ─── Client actif (Bascule comptable) ─── --}}
@php
    $currentClientId = session('current_client_id');
    $activeClient = null;
    if ($currentClientId) {
        $activeClient = \App\Models\Client::find($currentClientId);
    }
@endphp
@if($activeClient)
<div class="gel-active-client-banner">
    <i class="fas fa-desktop"></i>
    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;" title="{{ $activeClient->nom_entreprise }}">
        {{ $activeClient->nom_entreprise }}
    </span>
    <a href="{{ route('gel-accountant.client.deselect') }}" title="Quitter le client">Quitter</a>
</div>
@endif


{{-- [+ New] button --}}
<div style="padding:8px 12px;">
    <button class="gel-btn-new" onclick="showToast('Nouvelle action…','info')">
        <i class="fas fa-plus"></i> New
    </button>
</div>

<nav class="gel-sidebar-nav" id="gelSidebarNav">

    {{-- ═══════════ YOUR PRACTICE ═══════════ --}}
    <div class="gel-nav-section">Your Practice</div>

    <a href="{{ route('gel-accountant.clients') }}"
       class="gel-nav-item {{ $currentSection === 'clients' ? 'active' : '' }}"
       onclick="closeAllNested()">
        <i class="fas fa-users"></i> Clients
    </a>

    <a href="{{ route('gel-accountant.messagerie') }}"
       class="gel-nav-item {{ $currentSection === 'messagerie' ? 'active' : '' }}"
       onclick="closeAllNested()">
        <i class="fas fa-comments"></i> Messagerie
    </a>

    <button class="gel-nav-item" data-nav="workMenu"
        onmouseenter="openNested('workMenu', this)"
        onclick="toggleNested('workMenu', this)">
        <i class="fas fa-briefcase"></i> Work
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>

    <button class="gel-nav-item" data-nav="teamMenu"
        onmouseenter="openNested('teamMenu', this)"
        onclick="toggleNested('teamMenu', this)">
        <i class="fas fa-user-friends"></i> Team
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>

    {{-- ═══════════ BOOKMARKS ═══════════ --}}
    <div class="gel-nav-section">Bookmarks</div>

    <a href="{{ route('gel-accountant.comptabilite.grand-livre') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-book"></i> General Ledger
    </a>
    <a href="{{ route('gel-accountant.comptabilite.balance') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-balance-scale"></i> Trial Balance
    </a>
    <a href="{{ route('gel-accountant.comptabilite.plan-comptable') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-sitemap"></i> Chart of Accounts
    </a>
    <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-pen"></i> Journal Entries
    </a>
    <div class="gel-bookmark-add"><i class="fas fa-plus"></i> Bookmark this page</div>

    {{-- ═══════════ YOUR BOOKS ═══════════ --}}
    <div class="gel-nav-section">Your Books</div>

    <a href="{{ route('gel-accountant.dashboard') }}"
       class="gel-nav-item {{ $currentSection === 'dashboard' ? 'active' : '' }}"
       onclick="closeAllNested()">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    {{-- Comptabilité --}}
    <button class="gel-nav-item" data-nav="comptaMenu"
        onmouseenter="openNested('comptaMenu', this)"
        onclick="toggleNested('comptaMenu', this)">
        <i class="fas fa-book"></i> Comptabilité
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>

    {{-- Facturation --}}
    <button class="gel-nav-item" data-nav="factMenu"
        onmouseenter="openNested('factMenu', this)"
        onclick="toggleNested('factMenu', this)">
        <i class="fas fa-file-invoice"></i> Facturation
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>

    {{-- Banque --}}
    <button class="gel-nav-item" data-nav="bankMenu"
        onmouseenter="openNested('bankMenu', this)"
        onclick="toggleNested('bankMenu', this)">
        <i class="fas fa-university"></i> Banque
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>

    {{-- Dépenses --}}
    <button class="gel-nav-item" data-nav="depMenu"
        onmouseenter="openNested('depMenu', this)"
        onclick="toggleNested('depMenu', this)">
        <i class="fas fa-money-bill-wave"></i> Dépenses
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>

    {{-- Rapports --}}
    <button class="gel-nav-item" data-nav="rptMenu"
        onmouseenter="openNested('rptMenu', this)"
        onclick="toggleNested('rptMenu', this)">
        <i class="fas fa-chart-bar"></i> Rapports
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>

    <a href="{{ route('gel-accountant.tasks.index') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-tasks"></i> Tâches
    </a>
    <a href="{{ route('gel-accountant.workflows.index') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-robot"></i> Workflows
    </a>
    <a href="{{ route('gel-accountant.settings') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-cog"></i> Paramètres
    </a>
</nav>

{{-- ═══════════ NESTED DROPDOWNS (Level 2) ═══════════ --}}

{{-- Work --}}
<div class="gel-nested-dropdown" id="workMenu" onmouseleave="closeNestedDelayed('workMenu')">
    <a href="{{ route('gel-accountant.work') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-briefcase"></i> Travaux en cours
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Gestion des projets','info')">
        <i class="fas fa-project-diagram"></i> Projets
    </a>
</div>

{{-- Team --}}
<div class="gel-nested-dropdown" id="teamMenu" onmouseleave="closeNestedDelayed('teamMenu')">
    <div class="dropdown-header">Équipe</div>
    <a href="{{ route('gel-accountant.team') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-users"></i> Membres
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Inviter un membre','info')">
        <i class="fas fa-user-plus"></i> Inviter
    </a>
    <div class="nav-divider"></div>
    <a href="{{ route('gel-accountant.invitations') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-envelope"></i> Invitations en attente
    </a>
</div>

{{-- Comptabilité --}}
<div class="gel-nested-dropdown" id="comptaMenu" onmouseleave="closeNestedDelayed('comptaMenu')">
    <div class="dropdown-header">Comptabilité</div>
    <a href="{{ route('gel-accountant.comptabilite.plan-comptable') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-sitemap"></i> Plan comptable
    </a>
    <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-pen"></i> Saisie d'écritures
    </a>
    <a href="{{ route('gel-accountant.comptabilite.grand-livre') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-book"></i> Grand livre
    </a>
    <a href="{{ route('gel-accountant.comptabilite.balance') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-balance-scale"></i> Balance
    </a>
    <button class="gel-nav-item" data-nav="etatsFinMenu"
        onmouseenter="openNested('etatsFinMenu', this)"
        onclick="toggleNested('etatsFinMenu', this)">
        <i class="fas fa-file-alt"></i> États financiers
        <i class="fas fa-chevron-right nav-arrow"></i>
    </button>
    <div class="nav-divider"></div>
    <a href="{{ route('gel-accountant.comptabilite.journaux') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-journal-whills"></i> Journaux
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Gestion des exercices','info')">
        <i class="fas fa-calendar-alt"></i> Exercices comptables
    </a>
    <a href="{{ route('gel-accountant.comptabilite.revenus.index') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-chart-line"></i> Revenus différés
    </a>
    <a href="{{ route('gel-accountant.comptabilite.immobilisations.index') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-building"></i> Immobilisations
    </a>
</div>

{{-- États financiers (Niveau 3) --}}
<div class="gel-nested-dropdown" id="etatsFinMenu" onmouseleave="closeNestedDelayed('etatsFinMenu')">
    <div class="dropdown-header">États financiers</div>
    <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-balance-scale"></i> Bilan (Actif/Passif)
    </a>
    <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-chart-line"></i> Compte de résultat
    </a>
    <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-table"></i> SIG
    </a>
    <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-file-invoice"></i> TAFIRE
    </a>
    <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-money-bill-wave"></i> Flux de trésorerie
    </a>
    <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-paperclip"></i> Annexe
    </a>
</div>

{{-- Facturation --}}
<div class="gel-nested-dropdown" id="factMenu" onmouseleave="closeNestedDelayed('factMenu')">
    <div class="dropdown-header">Facturation</div>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Module Factures','info')">
        <i class="fas fa-file-invoice-dollar"></i> Factures
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Module Devis','info')">
        <i class="fas fa-file-signature"></i> Devis
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Module Clients','info')">
        <i class="fas fa-user-tie"></i> Clients
    </a>
    <div class="nav-divider"></div>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Produits et services','info')">
        <i class="fas fa-box"></i> Produits et services
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Paiements reçus','info')">
        <i class="fas fa-credit-card"></i> Paiements reçus
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Factures récurrentes','info')">
        <i class="fas fa-sync"></i> Factures récurrentes
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Relances','info')">
        <i class="fas fa-bell"></i> Relances
    </a>
</div>

{{-- Banque --}}
<div class="gel-nested-dropdown" id="bankMenu" onmouseleave="closeNestedDelayed('bankMenu')">
    <div class="dropdown-header">Banque</div>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Transactions bancaires','info')">
        <i class="fas fa-exchange-alt"></i> Transactions bancaires
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Rapprochement bancaire','info')">
        <i class="fas fa-handshake"></i> Rapprochement bancaire
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Règles bancaires','info')">
        <i class="fas fa-rule"></i> Règles bancaires
    </a>
    <div class="nav-divider"></div>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Comptes bancaires','info')">
        <i class="fas fa-university"></i> Comptes bancaires
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Virements','info')">
        <i class="fas fa-paper-plane"></i> Virements
    </a>
</div>

{{-- Dépenses --}}
<div class="gel-nested-dropdown" id="depMenu" onmouseleave="closeNestedDelayed('depMenu')">
    <div class="dropdown-header">Dépenses</div>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Dépenses','info')">
        <i class="fas fa-money-bill-wave"></i> Dépenses
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Factures fournisseurs','info')">
        <i class="fas fa-file-invoice"></i> Factures fournisseurs
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Bons de commande','info')">
        <i class="fas fa-shopping-cart"></i> Bons de commande
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Fournisseurs','info')">
        <i class="fas fa-truck"></i> Fournisseurs
    </a>
    <div class="nav-divider"></div>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Notes de frais','info')">
        <i class="fas fa-plane"></i> Notes de frais
    </a>
    <a href="#" class="gel-nav-item" onclick="closeAllNested();showToast('Dépenses récurrentes','info')">
        <i class="fas fa-sync"></i> Dépenses récurrentes
    </a>
</div>

{{-- Rapports --}}
<div class="gel-nested-dropdown" id="rptMenu" onmouseleave="closeNestedDelayed('rptMenu')">
    <div class="dropdown-header">Rapports</div>
    <a href="{{ route('gel-accountant.rapports.index') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-file-alt"></i> Rapports standards
    </a>
    <a href="{{ route('gel-accountant.rapports.index') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-tachometer-alt"></i> Centre de performance
    </a>
    <a href="{{ route('gel-accountant.rapports.create') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-sliders-h"></i> Rapports personnalisés
    </a>
    <div class="nav-divider"></div>
    <a href="{{ route('gel-accountant.rapports.index') }}" class="gel-nav-item" onclick="closeAllNested()">
        <i class="fas fa-folder"></i> Rapports sauvegardés
    </a>
</div>

{{-- ═══════════ User Footer ═══════════ --}}
@if($user)
<div style="padding:12px;border-top:1px solid var(--gel-border);display:flex;align-items:center;gap:10px;flex-shrink:0;">
    <div class="gel-avatar" style="background:#005BAC;">{{ strtoupper(substr($user->name ?? $user->email, 0, 2)) }}</div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:600;color:var(--gel-text-primary);">{{ $user->name ?? $user->email }}</div>
        @if($cabinet)<div style="font-size:11px;color:var(--gel-text-muted);">{{ $cabinet->nom }}</div>@endif
    </div>
    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" style="color:var(--gel-text-muted);font-size:16px;">
        <i class="fas fa-sign-out-alt"></i>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
</div>
@endif
