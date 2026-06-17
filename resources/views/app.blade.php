<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <div id="app">
        @if (isset($page))
            @switch($page)
                @case('gel-dashboard')
                    <gel-dashboard></gel-dashboard>
                    @break
                @case('gel-clients')
                    <gel-clients></gel-clients>
                    @break
                @case('gel-clients-create')
                @case('gel-clients-edit')
                    <gel-clients :client-id="{{ $clientId ?? 'null' }}"></gel-clients>
                    @break
                @case('gel-clients-show')
                    <gel-client-show :client-id="{{ $clientId }}"></gel-client-show>
                    @break
                @case('gel-poles')
                    <gel-poles></gel-poles>
                    @break
                @case('gel-poles-show')
                    <gel-pole-show :pole-id="{{ $poleId }}"></gel-pole-show>
                    @break
                @case('gel-missions')
                    <gel-missions></gel-missions>
                    @break
                @case('gel-missions-create')
                @case('gel-missions-edit')
                    <gel-mission-form :mission-id="{{ $missionId ?? 'null' }}"></gel-mission-form>
                    @break
                @case('gel-missions-show')
                    <gel-mission-form :mission-id="{{ $missionId }}"></gel-mission-form>
                    @break
                @case('gel-dossiers')
                    <gel-dossiers :client-id="{{ $clientId ?? 'null' }}"></gel-dossiers>
                    @break
                @case('gel-documents')
                    <gel-dossiers :client-id="{{ $clientId ?? 'null' }}"></gel-dossiers>
                    @break
                @case('gel-services')
                    <gel-services></gel-services>
                    @break
                @case('gel-services-show')
                    <gel-services-show :service-id="{{ $serviceId }}"></gel-services-show>
                    @break
                @case('gel-accounting-accounts')
                    <gel-accounting-accounts :client-id="{{ $clientId }}"></gel-accounting-accounts>
                    @break
                @case('gel-accounting-journals')
                    <gel-accounting-journals :client-id="{{ $clientId }}"></gel-accounting-journals>
                    @break
                @case('gel-accounting-journal-form')
                    <gel-accounting-journal-form :client-id="{{ $clientId }}"></gel-accounting-journal-form>
                    @break
                @case('gel-accounting-balance')
                    <gel-accounting-balance :client-id="{{ $clientId }}"></gel-accounting-balance>
                    @break
                @case('gel-accounting-ledger')
                    <gel-accounting-ledger :client-id="{{ $clientId }}"></gel-accounting-ledger>
                    @break
                @case('gel-accounting-bilan')
                    <gel-accounting-bilan :client-id="{{ $clientId }}"></gel-accounting-bilan>
                    @break
                @case('gel-accounting-resultat')
                    <gel-accounting-resultat :client-id="{{ $clientId }}"></gel-accounting-resultat>
                    @break
                @case('erp-catalogue')
                    <erp-catalogue></erp-catalogue>
                    @break
                @case('erp-stock')
                    <erp-stock></erp-stock>
                    @break
                @case('erp-invoice')
                    <erp-invoice></erp-invoice>
                    @break
                @case('erp-hr')
                    <erp-hr></erp-hr>
                    @break
                @case('erp-treasury')
                    <erp-treasury></erp-treasury>
                    @break
                @case('gel-licenses')
                    <gel-licenses></gel-licenses>
                    @break
                @case('gel-company-admins')
                    <gel-company-admins></gel-company-admins>
                    @break
                @case('gel-requests')
                    <gel-requests></gel-requests>
                    @break
                @case('public-catalogue-index')
                    <public-catalogue-index :categories='@json($props["categories"] ?? [])'></public-catalogue-index>
                    @break
                @case('public-catalogue-show')
                    <public-catalogue-show :category='@json($props["category"] ?? null)' :service='@json($props["service"] ?? null)'></public-catalogue-show>
                    @break
                @case('public-order-wizard')
                    <public-order-wizard :service='@json($props["service"] ?? null)' :user='@json($props["user"] ?? null)'></public-order-wizard>
                    @break
                @case('client-orders-index')
                    <client-orders-index :orders='@json($props["orders"] ?? [])'></client-orders-index>
                    @break
                @case('client-orders-show')
                    <client-orders-show :order='@json($props["order"] ?? null)'></client-orders-show>
                    @break
                @case('admin-orders-kanban')
                    <admin-orders-kanban :kanban='@json($props["kanban"] ?? [])' :statuts='@json($props["statuts"] ?? [])' :team='@json($props["team"] ?? [])'></admin-orders-kanban>
                    @break
                @case('admin-orders-archives')
                    <admin-orders-archives :orders='@json($props["orders"] ?? [])'></admin-orders-archives>
                    @break
                @case('admin-orders-show')
                    <admin-orders-show :order='@json($props["order"] ?? null)' :team='@json($props["team"] ?? [])' :statuts='@json($props["statuts"] ?? [])'></admin-orders-show>
                    @break
                @case('admin-services-index')
                    <admin-services-index :categories='@json($props["categories"] ?? [])'></admin-services-index>
                    @break
                @case('cpa-login')
                    <cpa-login></cpa-login>
                    @break
                @case('cpa-register')
                    <cpa-register></cpa-register>
                    @break
                @case('cpa-dashboard')
                    <cpa-dashboard></cpa-dashboard>
                    @break
                @case('settings')
                    <gel-settings></gel-settings>
                    @break
                @default
                    <gel-dashboard></gel-dashboard>
            @endswitch
        @else
            @yield('content')
        @endif
    </div>

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
            'is_company_admin' => Auth::user()->is_company_admin,
        ] : null;
    @endphp
    <script id="auth-data" type="application/json">{"user": @json($authUserApp)}</script>
</body>
</html>
