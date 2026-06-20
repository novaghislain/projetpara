<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- DIAG: HTML starts rendering -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ request()->root() }}">
    <title>GEL Cabinet | Gestion de Cabinet</title>
    <meta name="description" content="CRM, GED, Pôles, Missions, Comptabilité — votre cabinet tout-en-un.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Branding Overrides -->
    <style>
      :root {
        --bs-primary: #FF7900 !important;
        --bs-primary-rgb: 255, 121, 0 !important;
        --bs-secondary: #000000 !important;
        --bs-secondary-rgb: 0, 0, 0 !important;
        --bs-info: #50be50 !important;
        --bs-success: #32c832 !important;
        --bs-warning: #ffcc00 !important;
        --bs-danger: #cd3c14 !important;
        --bs-link-color: #FF7900 !important;
        --bs-link-hover-color: #ff9433 !important;
        --gel-dark: #000000;
        --gel-primary: #FF7900;
        --gel-secondary: #000000;
        --gel-accent: #FF7900;
        --gel-light: #f6f6f6;
      }
      body {
        font-family: 'Inter', sans-serif;
        background-color: #f5f7fa;
        color: #000000;
      }
      h1, h2, h3, h4, h5, h6, .font-heading {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
      }
      .bg-primary { background-color: var(--bs-primary) !important; }
      .text-primary { color: var(--bs-primary) !important; }
      .btn-primary {
        background-color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
        color: #000000 !important;
        font-weight: 700 !important;
        border-radius: 0px !important;
      }
      .btn-primary:hover {
        background-color: var(--bs-secondary) !important;
        border-color: var(--bs-secondary) !important;
        color: white !important;
      }
      .btn-outline-primary {
        color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
        border-radius: 0px !important;
      }
      .btn-outline-primary:hover {
        background-color: var(--bs-primary) !important;
        color: #000000 !important;
      }
      .sidebar {
        min-height: 100vh;
        background: #000000;
        border-right: 1px solid #222222;
      }
      .sidebar .nav-link {
        color: rgba(255,255,255,0.7);
        border-radius: 0px;
        padding: 12px 20px;
        margin: 0;
        font-size: 14px;
        transition: all 0.2s;
        border-left: 4px solid transparent;
      }
      .sidebar .nav-link:hover {
        color: #FF7900 !important;
        background: rgba(255,255,255,0.05);
      }
      .sidebar .nav-link.active {
        color: #FF7900 !important;
        background: rgba(255,255,255,0.08);
        border-left-color: #FF7900;
        font-weight: 600;
      }
      .sidebar .nav-link i {
        width: 20px;
        margin-right: 10px;
        font-size: 16px;
      }
      .card-dashboard {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: transform 0.2s;
      }
      .card-dashboard:hover {
        transform: translateY(-2px);
      }
      .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
      }
      .badge-eden {
        background: var(--gel-light);
        color: var(--gel-primary);
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 20px;
      }
    </style>

    @vite(['resources/js/app.js'])
</head>
<body>
    @php
        $_allProps = $props ?? [];
        foreach (['clientId','poleId','missionId','serviceId','courrierId','contratId','agId','id','categories','service','user','orders','order','kanban','statuts','team','category'] as $_key) {
            if (isset($$_key)) $_allProps[$_key] = $$_key;
        }
    @endphp
    <div id="app" data-page="{{ $page ?? '' }}" data-props='@json($_allProps)'></div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auth Data for Vue -->
    @php
        $authUserApp = Auth::check() ? [
            'id' => Auth::id(),
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'role' => Auth::user()->role,
            'role_id' => Auth::user()->role_id,
            'role_name' => Auth::user()->roleModel?->name,
            'fonction' => Auth::user()->fonction,
            'pole_id' => Auth::user()->pole_id,
            'client_id' => Auth::user()->client_id,
            'active_client_id' => Auth::user()->active_client_id,
            'is_company_admin' => Auth::user()->is_company_admin,
            'is_super_admin' => Auth::user()->is_super_admin ?? false,
            'is_comptable' => method_exists(Auth::user(), 'isComptable') ? Auth::user()->isComptable() : false,
            'is_client' => method_exists(Auth::user(), 'isClient') ? Auth::user()->isClient() : false,
            'is_suspended' => Auth::user()->is_suspended ?? false,
            'must_change_password' => Auth::user()->must_change_password ?? false,
            'email_verified_at' => Auth::user()->email_verified_at,
            'role_secretaire' => Auth::user()->role_secretaire ?? false,
            'is_admin' => Auth::user()->is_admin ?? false,
            'is_active' => Auth::user()->is_active ?? true,
        ] : null;
    @endphp
    <script id="auth-data" type="application/json">{"user": @json($authUserApp)}</script>
</body>
</html>
