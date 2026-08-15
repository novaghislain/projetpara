<?php
$rhFile = 'resources/views/layouts/gel-rh.blade.php';
$content = file_get_contents($rhFile);

// Colors
$content = str_replace('--gel-primary: #2CA01C;', '--gel-primary: #8B5CF6;', $content);
$content = str_replace('--gel-primary-hover: #1D7C13;', '--gel-primary-hover: #7C3AED;', $content);
$content = str_replace('--gel-primary-light: #EBF7E9;', '--gel-primary-light: #EDE9FE;', $content);

// Text
$content = str_replace("@yield('title', 'GEL Accountant')", "@yield('title', 'GEL RH')", $content);
$content = str_replace("<span>GEL <small>Accountant</small></span>", "<span>GEL <small>RH</small></span>", $content);

// Sidebar Replacement
$sidebarStart = strpos($content, '<ul class="sidebar-menu">');
$sidebarEnd = strpos($content, '{{-- User Footer --}}', $sidebarStart);

if ($sidebarStart !== false && $sidebarEnd !== false) {
    $newSidebar = '<ul class="sidebar-menu">
      <div class="sidebar-section">Général</div>
      <li class="sidebar-item {{ request()->routeIs(\'gel-rh.dashboard\') ? \'active\' : \'\' }}" onclick="window.location.href=\'{{ route(\\\'gel-rh.dashboard\\\') }}\'"><i class="fas fa-tachometer-alt me-2"></i> Tableau de bord</li>
      <li class="sidebar-item {{ request()->routeIs(\'gel-rh.leaves*\') ? \'active\' : \'\' }}" onclick="window.location.href=\'{{ route(\\\'gel-rh.leaves.index\\\') }}\'"><i class="fas fa-calendar-times me-2"></i> Congés & Absences</li>
      <li class="sidebar-item {{ request()->routeIs(\'gel-rh.payroll*\') ? \'active\' : \'\' }}" onclick="window.location.href=\'{{ route(\\\'gel-rh.payroll.index\\\') }}\'"><i class="fas fa-file-invoice-dollar me-2"></i> Paie & Bulletins</li>
    </ul>
    ';
    
    // Un-escape the single quotes inside the string
    $newSidebar = str_replace('\\\'', "'", $newSidebar);

    $content = substr_replace($content, $newSidebar, $sidebarStart, $sidebarEnd - $sidebarStart);
}

file_put_contents($rhFile, $content);
echo "GEL RH layout updated.\n";


// Legal Layout Update
$legalFile = 'resources/views/layouts/gel-legal.blade.php';
$content = file_get_contents($legalFile);

// Colors (Slate / Legal Blue)
$content = str_replace('--gel-primary: #2CA01C;', '--gel-primary: #334155;', $content);
$content = str_replace('--gel-primary-hover: #1D7C13;', '--gel-primary-hover: #1E293B;', $content);
$content = str_replace('--gel-primary-light: #EBF7E9;', '--gel-primary-light: #F1F5F9;', $content);

// Text
$content = str_replace("@yield('title', 'GEL Accountant')", "@yield('title', 'GEL Legal')", $content);
$content = str_replace("<span>GEL <small>Accountant</small></span>", "<span>GEL <small>Juridique</small></span>", $content);

// Sidebar Replacement
$sidebarStart = strpos($content, '<ul class="sidebar-menu">');
$sidebarEnd = strpos($content, '{{-- User Footer --}}', $sidebarStart);

if ($sidebarStart !== false && $sidebarEnd !== false) {
    $newSidebar = '<ul class="sidebar-menu">
      <div class="sidebar-section">Général</div>
      <li class="sidebar-item {{ request()->routeIs(\'gel-legal.dashboard\') ? \'active\' : \'\' }}" onclick="window.location.href=\'{{ route(\\\'gel-legal.dashboard\\\') }}\'"><i class="fas fa-gavel me-2"></i> Tableau de bord</li>
      <li class="sidebar-item {{ request()->routeIs(\'gel-legal.contracts*\') ? \'active\' : \'\' }}" onclick="window.location.href=\'{{ route(\\\'gel-legal.contracts.index\\\') }}\'"><i class="fas fa-file-signature me-2"></i> Contrats</li>
      <li class="sidebar-item {{ request()->routeIs(\'gel-legal.assemblies*\') ? \'active\' : \'\' }}" onclick="window.location.href=\'{{ route(\\\'gel-legal.assemblies.index\\\') }}\'"><i class="fas fa-users-cog me-2"></i> Assemblées</li>
    </ul>
    ';
    
    // Un-escape the single quotes inside the string
    $newSidebar = str_replace('\\\'', "'", $newSidebar);

    $content = substr_replace($content, $newSidebar, $sidebarStart, $sidebarEnd - $sidebarStart);
}

file_put_contents($legalFile, $content);
echo "GEL Legal layout updated.\n";
