<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GEL Business') — Mon Entreprise</title>

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Design System CSS --}}
    <link rel="stylesheet" href="{{ asset('css/gel-app.css') }}">

    @stack('styles')
</head>
<body>
    <div class="gel-wrapper">
        <aside class="gel-sidebar" id="gelSidebar">
            @include('gel-business.layouts.partials._sidebar')
        </aside>

        <main class="gel-main">
            @include('gel-business.layouts.partials._topbar')

            <div class="gel-content">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Scripts partagés (toast, panel, modal, flash, dropdown) --}}
    @include('gel-business.layouts.partials._scripts')

    @stack('scripts')
</body>
</html>
