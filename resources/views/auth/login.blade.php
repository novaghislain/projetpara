<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion | GEL Cabinet</title>
    <meta name="description" content="Connectez-vous à votre espace GEL Cabinet.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ── Variables ── */
        :root {
            --gel-primary: #FF7900;
            --gel-primary-hov: #e06700;
            --gel-primary-soft: rgba(255,121,0,0.06);
            --gel-blue: #3B82F6;
            --gel-dark: #111827;
            --gel-darker: #0F172A;
            --gel-white: #ffffff;
            --gel-light: #F8FAFC;
            --gel-light2: #F1F5F9;
            --gel-border: #E2E8F0;
            --gel-muted: #64748B;
            --gel-text: #1E293B;
            --font-body: 'Inter', sans-serif;
            --font-heading: 'Outfit', sans-serif;
            --nav-height: 72px;
            --radius: 4px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.06);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.08);
            --transition: 0.25s cubic-bezier(0.4,0,0.2,1);
        }

        html { scroll-behavior: smooth; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            height: 100dvh;
            overflow: hidden;
            background: var(--gel-light);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            position: relative;
            padding-top: var(--nav-height);
        }

        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading); }

        /* ═══════════════════════════════════════════════════════════════
           NAVBAR — Importée de la page d'accueil
        ═══════════════════════════════════════════════════════════════ */
        .gel-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1050;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid transparent;
            height: var(--nav-height);
            display: flex; align-items: center;
            transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
        }
        .gel-navbar.scrolled {
            background: rgba(255,255,255,0.98);
            border-bottom-color: var(--gel-border);
            box-shadow: var(--shadow-sm);
        }
        .gel-navbar .container-fluid {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; max-width: 1320px; margin: 0 auto; width: 100%;
        }
        .gel-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; flex-shrink: 0; }
        .gel-brand-logo { width: 38px; height: 38px; background: var(--gel-primary); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-family: var(--font-heading); font-size: 13px; font-weight: 900; color: #fff; letter-spacing: -0.5px; flex-shrink: 0; }
        .gel-brand-text { display: flex; flex-direction: column; line-height: 1.1; }
        .gel-brand-name { font-family: var(--font-heading); font-weight: 800; font-size: 16px; color: var(--gel-dark); letter-spacing: -0.3px; }
        .gel-brand-sub { font-size: 8.5px; font-weight: 600; color: var(--gel-muted); letter-spacing: 0.1em; text-transform: uppercase; }

        .gel-nav-center { display: flex; align-items: center; gap: 0; list-style: none; }
        .gel-nav-item { position: relative; }
        .gel-nav-link {
            display: flex; align-items: center; gap: 3px;
            padding: 7px 13px; font-size: 13px; font-weight: 500;
            color: var(--gel-text); text-decoration: none;
            border-radius: var(--radius);
            transition: color var(--transition), background var(--transition);
            white-space: nowrap;
        }
        .gel-nav-link:hover, .gel-nav-link.active { color: var(--gel-primary); background: var(--gel-primary-soft); }
        .gel-nav-link .chevron { font-size: 10px; transition: transform var(--transition); }
        .gel-nav-item:hover > a .chevron { transform: rotate(180deg); }

        .gel-dropdown {
            position: absolute; top: calc(100% + 8px); left: 50%;
            transform: translateX(-50%);
            background: var(--gel-white); border: 1px solid var(--gel-border);
            border-radius: 10px; box-shadow: var(--shadow-lg);
            padding: 6px; min-width: 220px;
            opacity: 0; visibility: hidden;
            transform: translateX(-50%) translateY(-8px);
            transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
            list-style: none;
        }
        .gel-nav-item:hover .gel-dropdown { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
        .gel-dropdown li a {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; font-size: 13px; font-weight: 500;
            color: var(--gel-text); text-decoration: none;
            border-radius: var(--radius);
            transition: background var(--transition), color var(--transition);
        }
        .gel-dropdown li a:hover { background: var(--gel-primary-soft); color: var(--gel-primary); }
        .gel-dropdown li a .drop-icon { width: 26px; height: 26px; background: var(--gel-light2); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--gel-primary); font-size: 12px; flex-shrink: 0; }
        .gel-dropdown-divider { border: none; border-top: 1px solid var(--gel-border); margin: 4px 0; }

        .gel-nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .gel-btn-nav { display: inline-flex; align-items: center; gap: 5px; padding: 7px 16px; font-size: 12.5px; font-weight: 600; border-radius: 6px; text-decoration: none; transition: all var(--transition); border: none; cursor: pointer; }
        .gel-btn-nav-primary { background: var(--gel-primary); color: #fff; }
        .gel-btn-nav-primary:hover { background: var(--gel-primary-hov); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(255,121,0,0.3); }
        .gel-btn-nav-outline { background: transparent; color: var(--gel-text); border: 1.5px solid var(--gel-border); }
        .gel-btn-nav-outline:hover { border-color: var(--gel-primary); color: var(--gel-primary); }

        .gel-toggler { display: none; background: none; border: 1.5px solid var(--gel-border); border-radius: var(--radius); padding: 6px 9px; cursor: pointer; color: var(--gel-dark); font-size: 17px; transition: all var(--transition); }
        .gel-toggler:hover { border-color: var(--gel-primary); color: var(--gel-primary); }

        .gel-mobile-menu {
            display: none; position: fixed; top: var(--nav-height); left: 0; right: 0;
            background: var(--gel-white); border-bottom: 3px solid var(--gel-primary);
            box-shadow: var(--shadow-md); z-index: 1040;
            padding: 16px 24px 24px;
            max-height: calc(100vh - var(--nav-height)); overflow-y: auto;
        }
        .gel-mobile-menu.open { display: block; }
        .gel-mobile-link { display: flex; align-items: center; gap: 10px; padding: 11px 0; font-size: 14px; font-weight: 500; color: var(--gel-text); text-decoration: none; border-bottom: 1px solid var(--gel-border); }
        .gel-mobile-link:last-child { border-bottom: none; }
        .gel-mobile-link:hover { color: var(--gel-primary); }

        @media (max-width: 991px) {
            .gel-nav-center { display: none; }
            .gel-toggler { display: flex; }
            .gel-navbar .container-fluid { padding: 0 16px; }
            .gel-navbar.scrolled { border-bottom-color: var(--gel-border); }
        }

        /* ═══════════════════════════════════════════════════════════════
           WAVE ANIMATION
        ═══════════════════════════════════════════════════════════════ */
        .gel-waves {
            position: fixed;
            left: 0;
            width: 100%;
            height: auto;
            z-index: 0;
            pointer-events: none;
            line-height: 0;
        }
        .gel-waves svg {
            display: block;
            width: 100%;
            height: 100px;
        }
        .gel-wave-path {
            animation: gelWaveMove 12s cubic-bezier(0.55, 0.5, 0.45, 0.5) infinite;
        }
        .gel-wave-path:nth-child(1) { animation-duration: 7s; animation-delay: -2s; }
        .gel-wave-path:nth-child(2) { animation-duration: 10s; animation-delay: -3s; }
        .gel-wave-path:nth-child(3) { animation-duration: 13s; animation-delay: -4s; }
        .gel-wave-path:nth-child(4) { animation-duration: 20s; animation-delay: -5s; }

        @keyframes gelWaveMove {
            0%   { transform: translateX(0); }
            25%  { transform: translateX(-25px); }
            50%  { transform: translateX(-10px); }
            75%  { transform: translateX(15px); }
            100% { transform: translateX(0); }
        }

        /* Bottom Waves (Orange) */
        .gel-waves-bottom { bottom: 0; }

        /* Top Waves (Blue, reversed) — below navbar */
        .gel-waves-top { top: var(--nav-height); }
        .gel-waves-top svg { transform: scaleY(-1); }
        .gel-waves-top .gel-wave-path { animation-name: gelWaveMoveReverse; }

        @keyframes gelWaveMoveReverse {
            0%   { transform: translateX(0); }
            25%  { transform: translateX(25px); }
            50%  { transform: translateX(10px); }
            75%  { transform: translateX(-15px); }
            100% { transform: translateX(0); }
        }

        /* ═══════════════════════════════════════════════════════════════
           LOGIN CARD
        ═══════════════════════════════════════════════════════════════ */
        .gel-layout {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            max-height: 100%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .gel-layout::-webkit-scrollbar { display: none; }

        .gel-card {
            background: var(--gel-white);
            border: 1px solid var(--gel-border);
            border-radius: 14px;
            padding: 28px 30px 22px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.04);
            animation: cardReveal 0.5s cubic-bezier(0.2,0,0,1) both;
        }
        @keyframes cardReveal {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: none; }
        }

        .gel-logo {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--gel-primary) 0%, #FF8C1A 100%);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 10px;
            font-family: var(--font-heading); font-size: 18px; font-weight: 900;
            color: var(--gel-white);
        }

        .gel-title {
            font-family: var(--font-heading); font-size: 20px; font-weight: 800;
            color: var(--gel-dark); text-align: center;
            letter-spacing: -0.5px; line-height: 1.2;
        }
        .gel-subtitle {
            font-size: 12.5px; color: var(--gel-muted); text-align: center;
            margin-bottom: 18px;
        }

        .gel-alert {
            border-radius: 8px; padding: 8px 12px;
            font-size: 12px; margin-bottom: 14px;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .gel-alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; }
        .gel-alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #16A34A; }

        .gel-field { margin-bottom: 12px; }
        .gel-field-label {
            display: block; font-size: 11.5px; font-weight: 600;
            color: var(--gel-text); margin-bottom: 4px;
        }
        .gel-field-wrap { position: relative; }
        .gel-field-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--gel-muted); font-size: 14px;
            pointer-events: none;
        }
        .gel-input {
            width: 100%;
            background: var(--gel-white);
            border: 1.5px solid var(--gel-border);
            border-radius: 8px;
            padding: 10px 12px 10px 36px;
            font-size: 13px;
            font-family: var(--font-body);
            color: var(--gel-text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .gel-input::placeholder { color: #b0bcc9; }
        .gel-input:focus {
            border-color: var(--gel-primary);
            box-shadow: 0 0 0 3px rgba(255,121,0,0.1);
        }
        .gel-input:focus + .gel-field-icon { color: var(--gel-primary); }

        .gel-pw-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--gel-muted); cursor: pointer; font-size: 14px; padding: 0;
        }
        .gel-pw-toggle:hover { color: var(--gel-primary); }

        .gel-remember { display: flex; align-items: center; gap: 8px; margin: 8px 0 10px; }
        .gel-remember input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--gel-primary); }
        .gel-remember label { font-size: 12.5px; color: var(--gel-muted); cursor: pointer; }

        .gel-forgot { text-align: right; margin-bottom: 10px; }
        .gel-forgot a { font-size: 12px; color: var(--gel-blue); text-decoration: none; }
        .gel-forgot a:hover { text-decoration: underline; }

        .gel-submit {
            width: 100%;
            background: var(--gel-primary);
            color: var(--gel-white);
            font-family: var(--font-heading);
            font-size: 13px; font-weight: 700;
            padding: 11px;
            border: none; border-radius: 8px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s, opacity 0.3s;
        }
        .gel-submit:hover {
            background: var(--gel-primary-hov);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(255,121,0,0.35);
        }
        .gel-submit:active { transform: translateY(0); }
        .gel-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none !important; box-shadow: none !important; }
        .gel-submit .spinner { display: none; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: gelSpin 0.6s linear infinite; }
        .gel-submit.loading .spinner { display: inline-block; }
        .gel-submit.loading .btn-text { display: none; }
        .gel-submit.loading .btn-icon { display: none; }
        @keyframes gelSpin { to { transform: rotate(360deg); } }

        .gel-divider {
            display: flex; align-items: center; gap: 10px;
            margin: 14px 0 12px;
        }
        .gel-divider::before, .gel-divider::after {
            content: ''; flex: 1;
            height: 1px; background: var(--gel-border);
        }
        .gel-divider span {
            font-size: 10.5px; color: var(--gel-muted);
            white-space: nowrap; text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .gel-link { text-align: center; font-size: 12.5px; color: var(--gel-muted); }
        .gel-link a { color: var(--gel-primary); font-weight: 600; text-decoration: none; }
        .gel-link a:hover { text-decoration: underline; }

        .gel-security {
            display: flex; align-items: center; justify-content: center; gap: 5px;
            margin-top: 14px;
            font-size: 11px; color: var(--gel-muted);
        }
        .gel-security i { font-size: 11px; }

        /* ── Responsive ── */
        @media (max-width: 991px) {
            .gel-navbar .container-fluid { padding: 0 16px; }
        }
        @media (max-width: 480px) {
            body { padding: 72px 12px 0; }
            .gel-card { padding: 22px 20px 18px; border-radius: 12px; }
            .gel-logo { width: 42px; height: 42px; font-size: 16px; }
            .gel-title { font-size: 18px; }
            .gel-subtitle { margin-bottom: 14px; }
            .gel-input { padding: 9px 10px 9px 34px; font-size: 12.5px; }
            .gel-field { margin-bottom: 10px; }
            .gel-submit { padding: 10px; font-size: 12.5px; }
            .gel-waves svg { height: 70px; }
        }
        @media (max-height: 640px) {
            .gel-card { padding: 16px 18px 14px; }
            .gel-logo { width: 36px; height: 36px; font-size: 14px; margin-bottom: 6px; }
            .gel-title { font-size: 16px; }
            .gel-subtitle { font-size: 11px; margin-bottom: 10px; }
            .gel-field { margin-bottom: 8px; }
            .gel-field-label { font-size: 10.5px; }
            .gel-input { padding: 7px 10px 7px 32px; font-size: 12px; }
            .gel-submit { padding: 8px; font-size: 12px; }
            .gel-divider { margin: 10px 0 8px; }
            .gel-security { margin-top: 10px; font-size: 10px; }
            .gel-waves svg { height: 50px; }
        }
    </style>
</head>
<body>

    <!-- ═══ NAVBAR ═══ -->
@include('partials.navbar')
    <!-- ═══ LOGIN CARD ═══ -->
    <div class="gel-layout">
        <div class="gel-card">
            <div class="gel-logo">GEL</div>
            <div class="gel-title">GEL Cabinet</div>
            <div class="gel-subtitle">Connectez-vous à votre espace</div>

            @if ($errors->any())
                <div class="gel-alert gel-alert-error">
                    <i class="bi-exclamation-circle-fill" style="flex-shrink:0; margin-top:1px;"></i>
                    <div>
                        @foreach ($errors->all() as $e)
                            <div>{{ $e }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="gel-alert gel-alert-success">
                    <i class="bi-check-circle-fill"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="gel-field">
                    <label class="gel-field-label" for="email">Email</label>
                    <div class="gel-field-wrap">
                        <input type="email" id="email" name="email" class="gel-input" value="{{ old('email') }}" required autofocus placeholder="votre@email.com">
                        <i class="bi-envelope gel-field-icon"></i>
                    </div>
                </div>

                <div class="gel-field">
                    <label class="gel-field-label" for="password">Mot de passe</label>
                    <div class="gel-field-wrap">
                        <input type="password" id="password" name="password" class="gel-input" required placeholder="••••••••">
                        <i class="bi-lock gel-field-icon"></i>
                        <button type="button" class="gel-pw-toggle" id="pwToggle" aria-label="Afficher/Masquer">
                            <i class="bi-eye" id="pwToggleIcon"></i>
                        </button>
                    </div>
                </div>

                @if (Route::has('password.request'))
                    <div class="gel-forgot">
                        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                    </div>
                @endif

                <div class="gel-remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" class="gel-submit">
                    <i class="bi-box-arrow-in-right btn-icon"></i>
                    <span class="spinner"></span>
                    <span class="btn-text">Se connecter</span>
                </button>
            </form>

            <div class="gel-divider"><span>Nouveau ?</span></div>

            <div class="gel-link">
                <a href="{{ route('register') }}">Créer un compte</a>
            </div>
        </div>
    </div>

    <!-- ═══ Top Waves (Blue – reversed) ═══ -->
    <div class="gel-waves gel-waves-top">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
            <defs>
                <path id="gelWavePathTop" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
            </defs>
            <use href="#gelWavePathTop" class="gel-wave-path" fill="rgba(59,130,246,0.22)" />
            <use href="#gelWavePathTop" class="gel-wave-path" fill="rgba(59,130,246,0.14)" />
            <use href="#gelWavePathTop" class="gel-wave-path" fill="rgba(59,130,246,0.07)" />
            <use href="#gelWavePathTop" class="gel-wave-path" fill="#F8FAFC" />
        </svg>
    </div>

    <!-- ═══ Bottom Waves (Orange – forward) ═══ -->
    <div class="gel-waves gel-waves-bottom">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
            <defs>
                <path id="gelWavePathBot" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
            </defs>
            <use href="#gelWavePathBot" class="gel-wave-path" fill="rgba(255,121,0,0.20)" />
            <use href="#gelWavePathBot" class="gel-wave-path" fill="rgba(255,121,0,0.13)" />
            <use href="#gelWavePathBot" class="gel-wave-path" fill="rgba(255,121,0,0.06)" />
            <use href="#gelWavePathBot" class="gel-wave-path" fill="#F8FAFC" />
        </svg>
    </div>

    <script>
    // NAVBAR SCROLL
    const navbar = document.getElementById('gelNavbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    // MOBILE MENU
    const toggler = document.getElementById('gelToggler');
    const mobileMenu = document.getElementById('gelMobileMenu');
    const togglerIcon = document.getElementById('togglerIcon');

    toggler.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('open');
        togglerIcon.className = isOpen ? 'bi-x-lg' : 'bi-list';
        document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    function closeMobileMenu() {
        mobileMenu.classList.remove('open');
        togglerIcon.className = 'bi-list';
        document.body.style.overflow = '';
    }

    document.addEventListener('click', (e) => {
        if (!navbar.contains(e.target) && !mobileMenu.contains(e.target)) {
            closeMobileMenu();
        }
    });

    // PASSWORD TOGGLE
    document.getElementById('pwToggle')?.addEventListener('click', () => {
        const input = document.getElementById('password');
        const icon = document.getElementById('pwToggleIcon');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.className = isPassword ? 'bi-eye-slash' : 'bi-eye';
    });

    // SUBMIT PREVENTION
    document.querySelector('form')?.addEventListener('submit', function() {
        const btn = this.querySelector('.gel-submit');
        if (btn) { btn.disabled = true; btn.classList.add('loading'); }
    });
    </script>
</body>
</html>
