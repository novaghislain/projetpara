<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Blogue | GEL Cabinet</title>
    <meta name="description" content="Blogue GEL Cabinet : actualités, conseils et guides sur la comptabilité, la gestion de cabinet, le juridique, le fiscal et le social.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --gel-primary: #FF7900; --gel-primary-hov: #e06700; --gel-primary-soft: rgba(255,121,0,0.06);
            --gel-svc: #FF7900; --gel-svc-rgb: 255,121,0;
            --gel-darker: #0F172A; --gel-dark: #111827; --gel-white: #ffffff;
            --gel-light: #F8FAFC; --gel-light2: #F1F5F9; --gel-border: #E2E8F0;
            --gel-muted: #64748B; --gel-text: #1E293B;
            --font-body: 'Inter', sans-serif; --font-heading: 'Outfit', sans-serif;
            --nav-height: 72px; --radius: 4px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.06);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.08);
            --transition: 0.25s cubic-bezier(0.4,0,0.2,1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-body); background: var(--gel-light); color: var(--gel-text); line-height: 1.6; }
        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading); font-weight: 700; }

        .anim-fade-up { opacity: 0; transform: translateY(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-visible { opacity: 1 !important; transform: none !important; }
        .delay-1 { transition-delay: 0.1s !important; }
        .delay-2 { transition-delay: 0.2s !important; }
        .delay-3 { transition-delay: 0.3s !important; }
        .delay-4 { transition-delay: 0.4s !important; }

        .gel-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1050;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid transparent;
            height: var(--nav-height);
            display: flex; align-items: center;
            transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
        }
        .gel-navbar.scrolled { background: rgba(255,255,255,0.98); border-bottom-color: var(--gel-border); box-shadow: var(--shadow-sm); }
        .gel-navbar .container-fluid { display: flex; align-items: center; justify-content: space-between; padding: 0 32px; max-width: 1320px; margin: 0 auto; width: 100%; }
        .gel-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; flex-shrink: 0; }
        .gel-brand-logo { width: 38px; height: 38px; background: var(--gel-primary); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-family: var(--font-heading); font-size: 13px; font-weight: 900; color: #fff; letter-spacing: -0.5px; flex-shrink: 0; }
        .gel-brand-text { display: flex; flex-direction: column; line-height: 1.1; }
        .gel-brand-name { font-family: var(--font-heading); font-weight: 800; font-size: 16px; color: var(--gel-dark); letter-spacing: -0.3px; }
        .gel-brand-sub { font-size: 8.5px; font-weight: 600; color: var(--gel-muted); letter-spacing: 0.1em; text-transform: uppercase; }
        .gel-nav-center { display: flex; align-items: center; gap: 0; list-style: none; }
        .gel-nav-item { position: relative; }
        .gel-nav-link { display: flex; align-items: center; gap: 3px; padding: 7px 13px; font-size: 13px; font-weight: 500; color: var(--gel-text); text-decoration: none; border-radius: var(--radius); transition: color var(--transition), background var(--transition); white-space: nowrap; }
        .gel-nav-link:hover, .gel-nav-link.active { color: var(--gel-primary); background: var(--gel-primary-soft); }
        .gel-nav-link .chevron { font-size: 10px; transition: transform var(--transition); }
        .gel-nav-item:hover > a .chevron { transform: rotate(180deg); }
        .gel-dropdown { position: absolute; top: calc(100% + 8px); left: 50%; transform: translateX(-50%); background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 10px; box-shadow: var(--shadow-lg); padding: 6px; min-width: 220px; opacity: 0; visibility: hidden; transform: translateX(-50%) translateY(-8px); transition: opacity 0.2s, transform 0.2s, visibility 0.2s; list-style: none; }
        .gel-nav-item:hover .gel-dropdown { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
        .gel-dropdown li a { display: flex; align-items: center; gap: 10px; padding: 8px 12px; font-size: 13px; font-weight: 500; color: var(--gel-text); text-decoration: none; border-radius: var(--radius); transition: background var(--transition), color var(--transition); }
        .gel-dropdown li a:hover { background: var(--gel-primary-soft); color: var(--gel-primary); }
        .gel-dropdown li a .drop-icon { width: 26px; height: 26px; background: var(--gel-light2); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--gel-primary); font-size: 12px; flex-shrink: 0; }
        .gel-dropdown-divider { border: none; border-top: 1px solid var(--gel-border); margin: 4px 0; }
        .gel-nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .gel-phone { display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 500; color: var(--gel-muted); text-decoration: none; padding: 6px 10px; border-radius: var(--radius); transition: color var(--transition); }
        .gel-phone:hover { color: var(--gel-primary); }
        .gel-phone i { color: var(--gel-primary); font-size: 13px; }
        .gel-btn-nav { display: inline-flex; align-items: center; gap: 5px; padding: 7px 16px; font-size: 12.5px; font-weight: 600; border-radius: 6px; text-decoration: none; transition: all var(--transition); border: none; cursor: pointer; }
        .gel-btn-nav-outline { background: transparent; color: var(--gel-text); border: 1.5px solid var(--gel-border); }
        .gel-btn-nav-outline:hover { border-color: var(--gel-primary); color: var(--gel-primary); }
        .gel-btn-nav-primary { background: var(--gel-primary); color: #fff; }
        .gel-btn-nav-primary:hover { background: var(--gel-primary-hov); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(255,121,0,0.3); }
        .gel-toggler { display: none; background: none; border: 1.5px solid var(--gel-border); border-radius: var(--radius); padding: 6px 9px; cursor: pointer; color: var(--gel-dark); font-size: 17px; transition: all var(--transition); }
        .gel-toggler:hover { border-color: var(--gel-primary); color: var(--gel-primary); }
        .gel-mobile-menu { display: none; position: fixed; top: var(--nav-height); left: 0; right: 0; background: var(--gel-white); border-bottom: 3px solid var(--gel-primary); box-shadow: var(--shadow-md); z-index: 1040; padding: 16px 24px 24px; max-height: calc(100vh - var(--nav-height)); overflow-y: auto; }
        .gel-mobile-menu.open { display: block; }
        .gel-mobile-link { display: flex; align-items: center; gap: 10px; padding: 11px 0; font-size: 14px; font-weight: 500; color: var(--gel-text); text-decoration: none; border-bottom: 1px solid var(--gel-border); }
        .gel-mobile-link:last-child { border-bottom: none; }
        .gel-mobile-link:hover { color: var(--gel-primary); }
        .text-orange { color: var(--gel-primary); }
        @media (max-width: 991px) { .gel-nav-center { display: none; } .gel-phone { display: none; } .gel-toggler { display: flex; } .gel-navbar .container-fluid { padding: 0 16px; } }

        .gel-page-header {
            margin-top: var(--nav-height);
            background: linear-gradient(135deg, #0A1628 0%, #1E293B 25%, #0F172A 50%, #1E293B 75%, #0A1628 100%);
            background-size: 300% 300%;
            animation: gelGradientMove 12s ease infinite;
            padding: 100px 0 80px;
            position: relative; overflow: hidden;
        }
        @keyframes gelGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gel-page-header::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(var(--gel-svc-rgb),0.12) 0%, transparent 70%); border-radius: 50%; animation: gelFloatA 8s ease-in-out infinite; }
        .gel-page-header::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
        @keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
        @keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(2.2rem, 4.5vw, 3.2rem); font-weight: 900; color: #fff; letter-spacing: -1px; position: relative; z-index: 1; }
        .gel-page-header p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 580px; margin-top: 12px; line-height: 1.7; position: relative; z-index: 1; }
        .gel-page-header-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); padding: 5px 14px; border-radius: 100px; font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; position: relative; z-index: 1; }

        .gel-section { padding: 80px 0; }
        .gel-section-title { font-family: var(--font-heading); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--gel-dark); letter-spacing: -0.5px; margin-bottom: 14px; }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); max-width: 540px; line-height: 1.7; }

        .gel-blog-card { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 14px; overflow: hidden; height: 100%; transition: transform var(--transition), box-shadow var(--transition); text-decoration: none; color: inherit; display: block; }
        .gel-blog-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); color: inherit; }
        .gel-blog-img { width: 100%; height: 180px; object-fit: cover; background: var(--gel-light2); display: flex; align-items: center; justify-content: center; font-size: 36px; color: var(--gel-muted); }
        .gel-blog-body { padding: 20px 22px 24px; }
        .gel-blog-tag { font-size: 10px; font-weight: 700; color: var(--gel-primary); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
        .gel-blog-body h5 { font-size: 15px; font-weight: 700; color: var(--gel-dark); margin-bottom: 8px; line-height: 1.3; }
        .gel-blog-body p { font-size: 13px; color: var(--gel-muted); line-height: 1.6; margin-bottom: 12px; }
        .gel-blog-meta { font-size: 11px; color: var(--gel-muted); display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .gel-blog-meta i { margin-right: 3px; }

        .gel-footer { background: #0A1628; padding: 48px 0 0; border-top: 3px solid var(--gel-primary); }
        .gel-footer-brand { font-family: var(--font-heading); font-size: 18px; font-weight: 800; color: #fff; }
        .gel-footer-sub { font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; }
        .gel-footer-desc { font-size: 13px; color: rgba(255,255,255,0.4); line-height: 1.7; margin-top: 12px; max-width: 260px; }
        .gel-footer-social { display: flex; gap: 10px; margin-top: 16px; }
        .gel-social-btn { width: 36px; height: 36px; border-radius: 6px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.5); font-size: 15px; text-decoration: none; transition: all var(--transition); }
        .gel-social-btn:hover { background: var(--gel-primary); border-color: var(--gel-primary); color: #fff; transform: translateY(-2px); }
        .gel-footer-heading { font-family: var(--font-heading); font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px; }
        .gel-footer-links { list-style: none; padding: 0; }
        .gel-footer-links li { margin-bottom: 10px; }
        .gel-footer-links a { font-size: 13px; color: rgba(255,255,255,0.4); text-decoration: none; transition: color var(--transition); }
        .gel-footer-links a:hover { color: var(--gel-primary); }
        .gel-footer-bottom { border-top: 1px solid rgba(255,255,255,0.07); padding: 18px 0; margin-top: 40px; }
        .gel-footer-bottom p { font-size: 12px; color: rgba(255,255,255,0.25); margin: 0; }

        .gel-modal-overlay { display: none; position: fixed; inset: 0; z-index: 2000; background: rgba(15,23,42,0.6); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 20px; }
        .gel-modal-overlay.open { display: flex; }
        .gel-modal-box { background: var(--gel-white); border-radius: 16px; padding: 40px 36px 36px; max-width: 420px; width: 100%; text-align: center; box-shadow: 0 24px 80px rgba(0,0,0,0.2); animation: modalPop 0.3s ease; position: relative; }
        @keyframes modalPop { from { opacity: 0; transform: scale(0.92) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .gel-modal-close { position: absolute; top: 14px; right: 18px; background: none; border: none; font-size: 20px; color: var(--gel-muted); cursor: pointer; transition: color var(--transition); padding: 4px; }
        .gel-modal-close:hover { color: var(--gel-dark); }
        .gel-modal-icon { width: 64px; height: 64px; background: var(--gel-primary-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px; color: var(--gel-primary); }
        .gel-modal-box h3 { font-size: 20px; font-weight: 800; color: var(--gel-dark); margin-bottom: 6px; }
        .gel-modal-box > p { font-size: 13.5px; color: var(--gel-muted); margin-bottom: 24px; line-height: 1.6; }
        .gel-modal-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 13px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; transition: all 0.3s; border: none; cursor: pointer; margin-bottom: 10px; }
        .gel-modal-btn-primary { background: var(--gel-primary); color: #fff; }
        .gel-modal-btn-primary:hover { background: var(--gel-primary-hov); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(255,121,0,0.3); }
        .gel-modal-btn-outline { background: transparent; color: var(--gel-text); border: 1.5px solid var(--gel-border); }
        .gel-modal-btn-outline:hover { border-color: var(--gel-primary); color: var(--gel-primary); }
        .gel-modal-divider { display: flex; align-items: center; gap: 12px; margin: 16px 0; color: var(--gel-muted); font-size: 11px; font-weight: 500; text-transform: uppercase; }
        .gel-modal-divider::before, .gel-modal-divider::after { content: ''; flex: 1; height: 1px; background: var(--gel-border); }

        .gel-search-box { max-width: 500px; position: relative; }
        .gel-search-box input { width: 100%; padding: 12px 16px 12px 42px; border-radius: 8px; border: 1px solid var(--gel-border); font-size: 14px; outline: none; transition: border-color var(--transition), box-shadow var(--transition); background: var(--gel-white); }
        .gel-search-box input:focus { border-color: var(--gel-primary); box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
        .gel-search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gel-muted); font-size: 16px; }

        .gel-category-pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 100px; font-size: 12.5px; font-weight: 600; background: var(--gel-white); border: 1.5px solid var(--gel-border); color: var(--gel-text); text-decoration: none; transition: all var(--transition); cursor: pointer; }
        .gel-category-pill:hover, .gel-category-pill.active { background: var(--gel-primary); border-color: var(--gel-primary); color: #fff; }

        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 80px 0 60px; } }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="gel-navbar" id="gelNavbar">
        <div class="container-fluid">
            <a href="/" class="gel-brand">
                <div class="gel-brand-logo">GEL</div>
                <div class="gel-brand-text"><span class="gel-brand-name">GEL Cabinet</span><span class="gel-brand-sub">Gestion Multi-Pôles</span></div>
            </a>
            <ul class="gel-nav-center">
                <li class="gel-nav-item">
                    <a href="/nos-modules" class="gel-nav-link">Nos Modules <i class="bi-chevron-down chevron"></i></a>
                    <ul class="gel-dropdown">
                        <li><a href="/nos-modules#module-crm"><span class="drop-icon"><i class="bi-people"></i></span> CRM Clients</a></li>
                        <li><a href="/nos-modules#module-ged"><span class="drop-icon"><i class="bi-folder2-open"></i></span> GED</a></li>
                        <li><a href="/nos-modules#module-poles"><span class="drop-icon"><i class="bi-diagram-3"></i></span> Pôles & Missions</a></li>
                        <li><hr class="gel-dropdown-divider"></li>
                        <li><a href="/nos-modules#module-compta"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                        <li><a href="/nos-modules#module-erp"><span class="drop-icon"><i class="bi-box-seam"></i></span> ERP Intégré</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item">
                    <a href="/services" class="gel-nav-link">Services <i class="bi-chevron-down chevron"></i></a>
                    <ul class="gel-dropdown">
                        <li><a href="/services/comptabilite"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                        <li><a href="/services/juridique"><span class="drop-icon"><i class="bi-bank2"></i></span> Juridique</a></li>
                        <li><a href="/services/fiscal"><span class="drop-icon"><i class="bi-receipt"></i></span> Fiscal</a></li>
                        <li><a href="/services/social-paie"><span class="drop-icon"><i class="bi-people"></i></span> Social & Paie</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item">
                    <a href="#" class="gel-nav-link">À propos <i class="bi-chevron-down chevron"></i></a>
                    <ul class="gel-dropdown">
                        <li><a href="{{ route('notre-cabinet') }}"><span class="drop-icon"><i class="bi-building"></i></span> Notre Cabinet</a></li>
                        <li><a href="{{ route('notre-equipe') }}"><span class="drop-icon"><i class="bi-people-fill"></i></span> Notre Équipe</a></li>
                        <li><a href="{{ route('carrieres') }}"><span class="drop-icon"><i class="bi-briefcase-fill"></i></span> Carrières</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item"><a href="#" class="gel-nav-link active">Ressources <i class="bi-chevron-down chevron"></i></a>
                    <ul class="gel-dropdown">
                        <li><a href="/blogue" style="background:var(--gel-primary-soft);color:var(--gel-primary);font-weight:600;"><span class="drop-icon"><i class="bi-pencil-square"></i></span> Blogue</a></li>
                        <li><a href="/documentation"><span class="drop-icon"><i class="bi-file-text"></i></span> Documentation</a></li>
                        <li><a href="/faq"><span class="drop-icon"><i class="bi-question-circle"></i></span> FAQ</a></li>
                        <li><hr class="gel-dropdown-divider"></li>
                        <li><a href="/centre-aide"><span class="drop-icon"><i class="bi-headset"></i></span> Centre d'aide</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item"><a href="#" class="gel-nav-link">Tarifs</a></li>
                <li class="gel-nav-item"><a href="/" class="gel-nav-link">Contact</a></li>
            </ul>
            <div class="gel-nav-right">
                <a href="tel:+22900000000" class="gel-phone d-none d-lg-flex"><i class="bi-telephone-fill"></i> +229 XX XX XX XX</a>
                @auth
                    @if(auth()->user()->role === 'client')
                        <a href="{{ route('client.orders.index') }}" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-speedometer2"></i> Mon Espace</a>
                    @elseif(auth()->user()->client_id)
                        <a href="{{ route('company.dashboard') }}" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-speedometer2"></i> Portail</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-speedometer2"></i> Tableau de bord</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf<button type="submit" class="gel-btn-nav gel-btn-nav-outline" style="color:#dc2626;border-color:#fca5a5;"><i class="bi-box-arrow-right"></i></button></form>
                @else
                    <a href="/register" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-person-plus"></i> S'inscrire</a>
                    <a href="/login" class="gel-btn-nav gel-btn-nav-primary"><i class="bi-box-arrow-in-right"></i> Connexion</a>
                @endauth
                <button class="gel-toggler" id="gelToggler" aria-label="Menu"><i class="bi-list" id="togglerIcon"></i></button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="gel-mobile-menu" id="gelMobileMenu">
        <a href="/" class="gel-mobile-link"><i class="bi-house text-orange me-2"></i>Accueil</a>
        <a href="/nos-modules" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Nos Modules</a>
        <a href="/services" class="gel-mobile-link"><i class="bi-shop text-orange me-2"></i>Services</a>
        <a href="/blogue" class="gel-mobile-link" style="color:var(--gel-primary);font-weight:600;"><i class="bi-pencil-square text-orange me-2"></i>Blogue</a>
        <a href="/documentation" class="gel-mobile-link"><i class="bi-file-text text-orange me-2"></i>Documentation</a>
        <a href="/faq" class="gel-mobile-link"><i class="bi-question-circle text-orange me-2"></i>FAQ</a>
        <a href="/centre-aide" class="gel-mobile-link"><i class="bi-headset text-orange me-2"></i>Centre d'aide</a>
        <a href="{{ route('notre-cabinet') }}" class="gel-mobile-link"><i class="bi-building text-orange me-2"></i>Notre Cabinet</a>
        <a href="#" class="gel-mobile-link"><i class="bi-currency-dollar text-orange me-2"></i>Tarifs</a>
        <a href="/" class="gel-mobile-link"><i class="bi-envelope text-orange me-2"></i>Contact</a>
        <a href="/login" class="gel-mobile-link"><i class="bi-box-arrow-in-right text-orange me-2"></i>Connexion</a>
        <a href="/register" class="gel-mobile-link"><i class="bi-person-plus text-orange me-2"></i>S'inscrire</a>
    </div>

    <!-- PAGE HEADER -->
    <div class="gel-page-header">
            <div class="row">
                <div class="col-lg-8">
                    <div class="gel-page-header-badge"><i class="bi-pencil-square"></i> Ressources</div>
                    <h1>Blogue</h1>
                    <p>On parle comptabilité, fiscalité, juridique et gestion de cabinet. Des vrais sujets, des conseils de terrain et l'actu de GEL Cabinet.</p>
                </div>
                <div class="col-lg-4 d-flex align-items-end justify-content-lg-end mt-4 mt-lg-0">
                    <div class="gel-search-box">
                        <i class="bi-search"></i>
                        <input type="text" placeholder="Rechercher un article..." oninput="filterPosts(this.value)">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- categories -->
    <section style="padding:30px 0;border-bottom:1px solid var(--gel-border);background:var(--gel-white);">
        <div class="container">
            <div class="d-flex flex-wrap gap-2" id="blogCategories">
                <span class="gel-category-pill active" data-cat="all" onclick="setCategory('all',this)">Tous</span>
                <span class="gel-category-pill" data-cat="compta" onclick="setCategory('compta',this)"><i class="bi-calculator"></i> Comptabilité</span>
                <span class="gel-category-pill" data-cat="juridique" onclick="setCategory('juridique',this)"><i class="bi-bank2"></i> Juridique</span>
                <span class="gel-category-pill" data-cat="fiscal" onclick="setCategory('fiscal',this)"><i class="bi-receipt"></i> Fiscal</span>
                <span class="gel-category-pill" data-cat="social" onclick="setCategory('social',this)"><i class="bi-people"></i> Social & Paie</span>
                <span class="gel-category-pill" data-cat="cabinet" onclick="setCategory('cabinet',this)"><i class="bi-building"></i> Cabinet</span>
            </div>
        </div>
    </section>

    <!-- articles -->
    <section class="gel-section">
        <div class="container">
            <div class="row g-4" id="blogGrid">

                <!-- 1 - Compta -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="compta">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#fff3e6,#ffe4cc);color:#FF7900;"><i class="bi-calculator-fill"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Comptabilité</div>
                            <h5>Plan comptable SYSCOA/OHADA</h5>
                            <p>On refait le point sur le plan comptable SYSCOA révisé : nomenclature, bonnes pratiques et exemples concrets pour le Bénin.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Jean Kpossou</span><span><i class="bi-clock"></i> 5 min</span><span><i class="bi-calendar3"></i> 12 juin 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 2 - Juridique -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="juridique">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#e8f0fe,#d4e4fc);color:#3B82F6;"><i class="bi-bank2"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Juridique</div>
                            <h5>Créer sa société au Bénin : ce qui a changé en 2026</h5>
                            <p>SARL, SA, les formalités, les délais et les coûts à jour des dernières réformes. Un retour d'expérience de nos dossiers récents.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Amélie Agboton</span><span><i class="bi-clock"></i> 8 min</span><span><i class="bi-calendar3"></i> 5 juin 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 3 - Fiscal -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="fiscal">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#f0eaff,#e4d9fc);color:#8B5CF6;"><i class="bi-receipt"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Fiscal</div>
                            <h5>Déclarations TVA : le calendrier et les taux 2026</h5>
                            <p>Obligations, taux applicables, crédit de TVA, franchise et échéances DGI. On vous dit tout ce qu'il faut pour être en règle.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Jean Kpossou</span><span><i class="bi-clock"></i> 6 min</span><span><i class="bi-calendar3"></i> 28 mai 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 4 - Social & Paie -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="social">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#e8faf0,#d4f5e4);color:#10B981;"><i class="bi-people"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Social & Paie</div>
                            <h5>Paie au Bénin : ce que tout employeur doit savoir</h5>
                            <p>CNSS, IRPP, allocations familiales, échéances sociales. Pas le droit à l'erreur : on passe en revue les obligations qui comptent.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Amélie Agboton</span><span><i class="bi-clock"></i> 7 min</span><span><i class="bi-calendar3"></i> 20 mai 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 5 - Cabinet -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="cabinet">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#fff3e6,#ffe4cc);color:#FF7900;"><i class="bi-building"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Cabinet</div>
                            <h5>Quel logiciel de gestion pour votre cabinet ?</h5>
                            <p>Cloud ou pas cloud, quelles fonctionnalités regarder en priorité, combien ça coûte vraiment. Le retour d'un cabinet qui a testé.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Sèna Gbedo</span><span><i class="bi-clock"></i> 10 min</span><span><i class="bi-calendar3"></i> 15 mai 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 6 - Comptabilité -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="compta">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#fff3e6,#ffe4cc);color:#FF7900;"><i class="bi-file-spreadsheet"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Comptabilité</div>
                            <h5>Bilan OHADA : lire entre les lignes</h5>
                            <p>Actif, passif, ratios financiers : comment interpréter un bilan OHADA sans se noyer dans les chiffres.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Jean Kpossou</span><span><i class="bi-clock"></i> 6 min</span><span><i class="bi-calendar3"></i> 8 mai 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 7 - Fiscal -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="fiscal">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#f0eaff,#e4d9fc);color:#8B5CF6;"><i class="bi-file-earmark-text"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Fiscal</div>
                            <h5>IB 2026 : calcul, taux et déclaration</h5>
                            <p>Taux, amortissements, provisions, crédits d'impôt. Ce qu'il faut savoir pour l'Impôt sur les Bénéfices en régime réel.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Jean Kpossou</span><span><i class="bi-clock"></i> 7 min</span><span><i class="bi-calendar3"></i> 30 avril 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 8 - Juridique -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="juridique">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#e8f0fe,#d4e4fc);color:#3B82F6;"><i class="bi-file-earmark-text"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Juridique</div>
                            <h5>Rédiger des statuts SARL/SA : les clauses qui comptent</h5>
                            <p>Apports, capital, gérance, bénéfices, pacte d'actionnaires. On détaille les clauses qu'on retrouve dans tous les dossiers.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Amélie Agboton</span><span><i class="bi-clock"></i> 9 min</span><span><i class="bi-calendar3"></i> 22 avril 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 9 - Social & Paie -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="social">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#e8faf0,#d4f5e4);color:#10B981;"><i class="bi-clock"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Social & Paie</div>
                            <h5>CDI, CDD, CNSS : les bases du contrat de travail au Bénin</h5>
                            <p>Types de contrats, période d'essai, clauses particulières et déclarations CNSS. Les points à vérifier avant de recruter.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Amélie Agboton</span><span><i class="bi-clock"></i> 6 min</span><span><i class="bi-calendar3"></i> 15 avril 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 10 - Cabinet -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="cabinet">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#fff3e6,#ffe4cc);color:#FF7900;"><i class="bi-graph-up-arrow"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Cabinet</div>
                            <h5>Pourquoi passer au cloud ? Un cabinet témoigne</h5>
                            <p>Sécurité, mobilité, collaboration à distance. On a posé la question à des cabinets qui ont fait la bascule.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Sèna Gbedo</span><span><i class="bi-clock"></i> 8 min</span><span><i class="bi-calendar3"></i> 8 avril 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 11 - Comptabilité -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="compta">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#fff3e6,#ffe4cc);color:#FF7900;"><i class="bi-journal-text"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Comptabilité</div>
                            <h5>Rapprochement bancaire : les erreurs qui reviennent souvent</h5>
                            <p>Pointage, écritures de régularisation, erreurs fréquentes. Un petit guide pour ne plus rien laisser passer.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Jean Kpossou</span><span><i class="bi-clock"></i> 5 min</span><span><i class="bi-calendar3"></i> 1 avril 2026</span></div>
                        </div>
                    </a>
                </div>

                <!-- 12 - Fiscal -->
                <div class="col-md-6 col-lg-4 blog-item" data-cat="fiscal">
                    <a href="#" class="gel-blog-card">
                        <div class="gel-blog-img" style="background:linear-gradient(135deg,#f0eaff,#e4d9fc);color:#8B5CF6;"><i class="bi-cash-stack"></i></div>
                        <div class="gel-blog-body">
                            <div class="gel-blog-tag">Fiscal</div>
                            <h5>IRPP 2026 : barème et calcul mis à jour</h5>
                            <p>Tranches, abattements, déclaration. On refait le point sur l'impôt sur le revenu pour 2026.</p>
                            <div class="gel-blog-meta"><span><i class="bi-person"></i> Par Jean Kpossou</span><span><i class="bi-clock"></i> 6 min</span><span><i class="bi-calendar3"></i> 25 mars 2026</span></div>
                        </div>
                    </a>
                </div>

            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination" style="gap:4px;">
                        <li class="page-item disabled"><span class="page-link" style="border-radius:6px;border:1px solid var(--gel-border);padding:8px 14px;font-size:13px;color:var(--gel-muted);">◀ Précédent</span></li>
                        <li class="page-item active"><span class="page-link" style="border-radius:6px;background:var(--gel-primary);border-color:var(--gel-primary);padding:8px 14px;font-size:13px;color:#fff;font-weight:700;">1</span></li>
                        <li class="page-item"><a href="#" class="page-link" style="border-radius:6px;border:1px solid var(--gel-border);padding:8px 14px;font-size:13px;color:var(--gel-muted);text-decoration:none;">2</a></li>
                        <li class="page-item"><a href="#" class="page-link" style="border-radius:6px;border:1px solid var(--gel-border);padding:8px 14px;font-size:13px;color:var(--gel-muted);text-decoration:none;">3</a></li>
                        <li class="page-item"><span class="page-link" style="border-radius:6px;border:none;padding:8px 14px;font-size:13px;color:var(--gel-muted);">...</span></li>
                        <li class="page-item"><a href="#" class="page-link" style="border-radius:6px;border:1px solid var(--gel-border);padding:8px 14px;font-size:13px;color:var(--gel-text);text-decoration:none;">Suivant ▶</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Sujets populaires -->
            <div class="row mt-5">
                <div class="col-12 anim-fade-up">
                    <div style="background:var(--gel-white);border:1px solid var(--gel-border);border-radius:14px;padding:36px;">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                                <i class="bi-tag-fill" style="font-size:36px;color:var(--gel-primary);"></i>
                                <h5 style="font-size:18px;font-weight:800;margin-top:8px;">Sujets populaires</h5>
                                <p style="font-size:13px;color:var(--gel-muted);margin:0;">Ce qui intéresse le plus nos lecteurs</p>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="gel-category-pill" style="background:var(--gel-primary-soft);border-color:transparent;color:var(--gel-primary);cursor:default;"><i class="bi-calculator"></i> Plan comptable SYSCOA</span>
                                    <span class="gel-category-pill" style="background:rgba(59,130,246,0.08);border-color:transparent;color:#3B82F6;cursor:default;"><i class="bi-bank2"></i> Constitution SARL</span>
                                    <span class="gel-category-pill" style="background:rgba(139,92,246,0.08);border-color:transparent;color:#8B5CF6;cursor:default;"><i class="bi-receipt"></i> Déclarations TVA</span>
                                    <span class="gel-category-pill" style="background:rgba(16,185,129,0.08);border-color:transparent;color:#10B981;cursor:default;"><i class="bi-people"></i> Gestion paie CNSS</span>
                                    <span class="gel-category-pill" style="background:rgba(255,121,0,0.08);border-color:transparent;color:#FF7900;cursor:default;"><i class="bi-building"></i> Logiciel cabinet</span>
                                    <span class="gel-category-pill" style="background:rgba(139,92,246,0.08);border-color:transparent;color:#8B5CF6;cursor:default;"><i class="bi-cash-stack"></i> IRPP</span>
                                    <span class="gel-category-pill" style="background:rgba(255,121,0,0.08);border-color:transparent;color:#FF7900;cursor:default;"><i class="bi-file-spreadsheet"></i> Bilan OHADA</span>
                                    <span class="gel-category-pill" style="background:rgba(59,130,246,0.08);border-color:transparent;color:#3B82F6;cursor:default;"><i class="bi-file-earmark-text"></i> Statuts société</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="row justify-content-center mt-5">
                <div class="col-lg-6 text-center anim-fade-up">
                    <div style="background:var(--gel-light2);border-radius:14px;padding:40px 28px;border:1px solid var(--gel-border);">
                        <i class="bi-envelope-open" style="font-size:32px;color:var(--gel-primary);"></i>
                        <h4 style="font-size:18px;font-weight:800;margin-top:12px;">Restez informé</h4>
                        <p style="font-size:13px;color:var(--gel-muted);">Notre newsletter chaque semaine. Pas de spam, on déteste ça aussi. Désinscription en un clic.</p>
                        <form onsubmit="event.preventDefault();alert('Merci pour votre inscription ! Vous recevrez bientôt notre newsletter.');" style="display:flex;gap:8px;max-width:420px;margin:16px auto 0;">
                            <input type="email" placeholder="Votre adresse email" required style="flex:1;padding:11px 14px;border-radius:6px;border:1.5px solid var(--gel-border);font-size:13px;outline:none;">
                            <button type="submit" style="background:var(--gel-primary);color:#fff;border:none;padding:11px 20px;border-radius:6px;font-weight:700;font-size:13px;cursor:pointer;">S'abonner</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="gel-modal-overlay" id="authModal">
        <div class="gel-modal-box">
            <button class="gel-modal-close" onclick="closeModal()">&times;</button>
            <div class="gel-modal-icon"><i class="bi-person-plus-fill"></i></div>
            <h3>Accédez à nos ressources</h3>
            <p>Créez un compte pour accéder à tous nos articles, guides et documentations.</p>
            <a href="/register" class="gel-modal-btn gel-modal-btn-primary"><i class="bi-person-plus"></i> Créer un compte</a>
            <div class="gel-modal-divider">ou</div>
            <a href="/login" class="gel-modal-btn gel-modal-btn-outline"><i class="bi-box-arrow-in-right"></i> Se connecter</a>
        </div>
    </div>

    <footer class="gel-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div style="width:36px;height:36px;background:#FF7900;border-radius:4px;display:flex;align-items:center;justify-content:center;font-family:'Outfit',sans-serif;font-size:12px;font-weight:900;color:#fff;">GEL</div>
                        <div><div class="gel-footer-brand">GEL Cabinet</div><div class="gel-footer-sub">Gestion Multi-Pôles</div></div>
                    </div>
                    <p class="gel-footer-desc">CRM, GED, Pôles, Missions, Comptabilité — votre cabinet tout-en-un.</p>
                    <div class="gel-footer-social">
                        <a href="#" class="gel-social-btn" aria-label="Facebook"><i class="bi-facebook"></i></a>
                        <a href="#" class="gel-social-btn" aria-label="LinkedIn"><i class="bi-linkedin"></i></a>
                        <a href="#" class="gel-social-btn" aria-label="Twitter"><i class="bi-twitter-x"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="gel-footer-heading">Services</div>
                    <ul class="gel-footer-links">
                        <li><a href="/services/comptabilite">Comptabilité</a></li>
                        <li><a href="/services/juridique">Juridique</a></li>
                        <li><a href="/services/fiscal">Fiscal</a></li>
                        <li><a href="/services/social-paie">Social & Paie</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="gel-footer-heading">Ressources</div>
                    <ul class="gel-footer-links">
                        <li><a href="/blogue">Blogue</a></li>
                        <li><a href="/documentation">Documentation</a></li>
                        <li><a href="/faq">FAQ</a></li>
                        <li><a href="/centre-aide">Centre d'aide</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="gel-footer-heading">Contact</div>
                    <ul class="gel-footer-links" style="font-size:13px;color:rgba(255,255,255,0.4);">
                        <li><i class="bi-geo-alt" style="color:#FF7900;margin-right:6px;"></i>Cotonou, Bénin</li>
                        <li><i class="bi-telephone" style="color:#FF7900;margin-right:6px;"></i>+229 XX XX XX XX</li>
                        <li><i class="bi-envelope" style="color:#FF7900;margin-right:6px;"></i>contact@gelcabinet.com</li>
                    </ul>
                </div>
            </div>
            <div class="gel-footer-bottom">
                <p>&copy; {{ date('Y') }} GEL Cabinet. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal() { document.getElementById('authModal').classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeModal() { document.getElementById('authModal').classList.remove('open'); document.body.style.overflow = ''; }
        document.getElementById('authModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
        const navbar = document.getElementById('gelNavbar');
        window.addEventListener('scroll', () => { navbar.classList.toggle('scrolled', window.scrollY > 20); }, { passive: true });
        const toggler = document.getElementById('gelToggler');
        const mobileMenu = document.getElementById('gelMobileMenu');
        const togglerIcon = document.getElementById('togglerIcon');
        toggler.addEventListener('click', () => { const isOpen = mobileMenu.classList.toggle('open'); togglerIcon.className = isOpen ? 'bi-x-lg' : 'bi-list'; document.body.style.overflow = isOpen ? 'hidden' : ''; });
        document.addEventListener('click', (e) => { if (!navbar.contains(e.target) && !mobileMenu.contains(e.target)) { mobileMenu.classList.remove('open'); togglerIcon.className = 'bi-list'; document.body.style.overflow = ''; } });
        const animEls = document.querySelectorAll('.anim-fade-up');
        const observer = new IntersectionObserver((entries) => { entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('anim-visible'); observer.unobserve(entry.target); } }); }, { threshold: 0.12 });
        animEls.forEach(el => observer.observe(el));

        function setCategory(cat, el) {
            document.querySelectorAll('#blogCategories .gel-category-pill').forEach(p => p.classList.remove('active'));
            el.classList.add('active');
            document.querySelectorAll('.blog-item').forEach(item => {
                item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
            });
        }

        function filterPosts(q) {
            const cards = document.querySelectorAll('.gel-blog-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const item = card.closest('.blog-item');
                if (item) item.style.display = text.includes(q.toLowerCase()) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
