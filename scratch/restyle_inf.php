<?php

$secPath = __DIR__.'/../resources/views/layouts/gel-secretary.blade.php';
$infPath = __DIR__.'/../resources/views/layouts/gel-informaticien.blade.php';

$content = file_get_contents($secPath);

// Replace title and topbar
$content = str_replace(
    "@yield('title', 'GEL Secrétariat') — Espace Secrétaire",
    "@yield('title', 'GEL Informatique') — Pôle Informatique",
    $content
);
$content = str_replace(
    "GEL <small>Secrétariat</small>",
    "GEL <small>Informatique</small>",
    $content
);

// Replace colors
$content = str_replace(
    [
        '--sec-primary: #0D9488;',
        '--sec-primary-dark: #0F766E;',
        '--sec-primary-light: #F0FDFA;',
        '--sec-sidebar-hover: #F0FDFA;',
        '--sec-sidebar-active-bg: #CCFBF1;',
        '#0D9488',
        '#14B8A6'
    ],
    [
        '--sec-primary: #FF7900;',
        '--sec-primary-dark: #163A5E;',
        '--sec-primary-light: #FFF4E6;',
        '--sec-sidebar-hover: rgba(255,255,255,0.1);',
        '--sec-sidebar-active-bg: rgba(255,121,0,0.1);',
        '#FF7900',
        '#FF9C40'
    ],
    $content
);

// Replace sidebar content
// We need to replace the entire <nav class="sec-sidebar-nav"> block
$sidebarStart = strpos($content, '<nav class="sec-sidebar-nav">');
$sidebarEnd = strpos($content, '</nav>', $sidebarStart) + 6;

$newSidebar = <<<HTML
    <nav class="sec-sidebar-nav">
      <div class="sec-sidebar-sub">GEL Informatique</div>
      <a href="{{ route('gel-informaticien.dashboard') }}" class="sec-sidebar-link {{ request()->routeIs('gel-informaticien.dashboard') ? 'active' : '' }}">
        <i class="fas fa-heartbeat"></i> Santé Globale
      </a>
      <a href="{{ route('gel-informaticien.tickets.index') }}" class="sec-sidebar-link {{ request()->routeIs('gel-informaticien.tickets.*') ? 'active' : '' }}">
        <i class="fas fa-ticket-alt"></i> Centre de Support
      </a>
      <a href="{{ route('gel-informaticien.security.index') }}" class="sec-sidebar-link {{ request()->routeIs('gel-informaticien.security.*') ? 'active' : '' }}">
        <i class="fas fa-shield-alt"></i> Sécurité & Accès
      </a>
      <a href="{{ route('gel-informaticien.maintenance.index') }}" class="sec-sidebar-link {{ request()->routeIs('gel-informaticien.maintenance.*') ? 'active' : '' }}">
        <i class="fas fa-server"></i> Sauvegardes
      </a>
      <a href="{{ route('gel-informaticien.dev-requests.index') }}" class="sec-sidebar-link {{ request()->routeIs('gel-informaticien.dev-requests.*') ? 'active' : '' }}">
        <i class="fas fa-code"></i> Projets Digitaux
      </a>
    </nav>
HTML;

$content = substr_replace($content, $newSidebar, $sidebarStart, $sidebarEnd - $sidebarStart);

// Remove the client switcher logic since Informaticien doesn't need to select a client to see his own portal
// (The client switcher is mostly for business/secretary). 
// Actually, let's keep it but just disable or remove it for informaticien.
$clientSwitcherStart = strpos($content, '{{-- Sélecteur d\'entreprise --}}');
$clientSwitcherEnd = strpos($content, '</div>', strpos($content, '</div>', strpos($content, '<div class="client-dropdown"')) + 6) + 6;
// Wait, regex might be easier. Let's just remove the block from `{{-- Sélecteur d'entreprise --}}` to the end of `sec-client-switcher` div.
$clientSwitcherCode = substr($content, $clientSwitcherStart, strpos($content, '</div>', strpos($content, 'id="clientSwitcher"')) - $clientSwitcherStart);
// Let's do it simply by providing a new content file or just saving as is, because informaticien might not need it at all.
$content = preg_replace('/\{\{-- Sélecteur d\'entreprise --\}\}.*?<\/div>\s*<\/div>/is', '', $content);

// Replace user avatar logic 
$content = str_replace(
    '{{ auth()->user()?->name ?? \'Secrétaire\' }}',
    '{{ auth()->user()?->prenom ?? \'Informaticien\' }}',
    $content
);
$content = str_replace(
    'Secrétaire de cabinet',
    'Pôle Informatique',
    $content
);

file_put_contents($infPath, $content);
echo "Done";
