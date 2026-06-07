<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Good Régime | Santé & Bien-Être au Naturel</title>
    <meta name="description" content="Votre destination privilégiée pour la parapharmacie, les soins naturels et un accompagnement bien-être sur mesure au Bénin.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS (Premium Feel) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Branding Overrides -->
    <style>
      :root {
        --bs-primary: #1b5e20 !important;
        --bs-primary-rgb: 27, 94, 32 !important;
        --bs-secondary: #81c784 !important;
        --bs-secondary-rgb: 129, 199, 132 !important;
        --bs-info: #81c784 !important;
        --bs-link-color: #1b5e20 !important;
        --bs-link-hover-color: #2e7d32 !important;
      }
      .bg-primary { background-color: #1b5e20 !important; }
      .text-primary { color: #1b5e20 !important; }
      .btn-primary { background-color: #1b5e20 !important; border-color: #1b5e20 !important; }
      .btn-outline-primary { color: #1b5e20 !important; border-color: #1b5e20 !important; }
      .btn-outline-primary:hover { background-color: #1b5e20 !important; color: white !important; }
    </style>
    
    <!-- Scripts & Styles -->
    @routes
    @vite(['resources/js/app.js'])
</head>
<body class="bg-light">
    <div id="app">
        @yield('content')
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auth Data for Vue -->
    <script id="auth-data" type="application/json">{
        "user": {{ Auth::check()
            ? json_encode(['id' => Auth::id(), 'name' => Auth::user()->name, 'email' => Auth::user()->email])
            : 'null' }}
    }</script>
</body>
</html>
