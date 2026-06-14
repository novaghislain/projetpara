<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mon Espace | GEL Cabinet</title>
    <meta name="description" content="Espace client GEL Cabinet">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
      :root {
        --bs-primary: #1a237e !important;
        --bs-primary-rgb: 26, 35, 126 !important;
        --bs-secondary: #3949ab !important;
        --bs-secondary-rgb: 57, 73, 171 !important;
        --gel-dark: #0d1b2a;
        --gel-primary: #1a237e;
        --gel-secondary: #3949ab;
        --gel-accent: #5c6bc0;
        --gel-light: #e8eaf6;
      }
      body {
        font-family: 'Inter', sans-serif;
        background-color: #f5f7fa;
      }
      h1, h2, h3, h4, h5, h6, .font-heading {
        font-family: 'Outfit', sans-serif;
      }
      .bg-primary { background-color: var(--bs-primary) !important; }
      .text-primary { color: var(--bs-primary) !important; }
      .btn-primary {
        background-color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
      }
      .btn-primary:hover {
        background-color: var(--bs-secondary) !important;
        border-color: var(--bs-secondary) !important;
      }
      .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, var(--gel-dark) 0%, var(--gel-primary) 100%);
      }
      .sidebar .nav-link {
        color: rgba(255,255,255,0.7);
        border-radius: 10px;
        padding: 10px 16px;
        margin: 2px 8px;
        font-size: 14px;
        transition: all 0.2s;
      }
      .sidebar .nav-link:hover, .sidebar .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.1);
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
      .form-control, .form-select {
        border-radius: 10px;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
      }
      .form-control:focus, .form-select:focus {
        border-color: var(--gel-primary);
        box-shadow: 0 0 0 3px rgba(26,35,126,0.15);
      }
    </style>

    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app">
        @switch($page)
            @case('company-dashboard')
                <company-dashboard></company-dashboard>
                @break
            @case('company-services')
                <company-services></company-services>
                @break
            @case('company-profile')
                <company-profile></company-profile>
                @break
            @case('company-users')
                <company-users></company-users>
                @break
            @case('company-ged')
                <company-ged></company-ged>
                @break
            @case('company-accounting')
                <company-accounting></company-accounting>
                @break
            @default
                <company-dashboard></company-dashboard>
        @endswitch
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auth Data for Vue -->
    <script id="auth-data" type="application/json">{
        "user": {{ Auth::check()
            ? json_encode([
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
            ])
            : 'null' }}
    }</script>

    <script>
        window.__CLIENT_ID__ = {{ Auth::check() && Auth::user()->client_id ? Auth::user()->client_id : 'null' }};
    </script>
</body>
</html>
