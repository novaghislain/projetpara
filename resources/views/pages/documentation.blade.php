<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Documentation | GEL Cabinet</title>
    <meta name="description" content="Guides, tutoriels et documentation GEL Cabinet pour utiliser la plateforme.">

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

        .gel-doc-card { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 12px; padding: 28px 24px; height: 100%; transition: transform var(--transition), box-shadow var(--transition); text-decoration: none; color: inherit; display: block; }
        .gel-doc-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); color: inherit; }
        .gel-doc-icon { width: 48px; height: 48px; border-radius: 10px; background: var(--gel-primary-soft); display: flex; align-items: center; justify-content: center; color: var(--gel-primary); font-size: 24px; margin-bottom: 14px; }
        .gel-doc-card h5 { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
        .gel-doc-card p { font-size: 13px; color: var(--gel-muted); line-height: 1.6; margin-bottom: 12px; }
        .gel-doc-count { font-size: 11px; color: var(--gel-muted); display: flex; align-items: center; gap: 4px; }
        .gel-doc-count i { color: var(--gel-primary); }

        .gel-doc-article { display: flex; align-items: center; gap: 14px; padding: 16px 20px; background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 10px; text-decoration: none; color: inherit; transition: all var(--transition); margin-bottom: 8px; }
        .gel-doc-article:hover { border-color: var(--gel-primary); box-shadow: var(--shadow-sm); color: inherit; }
        .gel-doc-article-icon { width: 36px; height: 36px; background: var(--gel-primary-soft); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--gel-primary); font-size: 16px; flex-shrink: 0; }
        .gel-doc-article-content { flex: 1; }
        .gel-doc-article-content h6 { font-size: 13.5px; font-weight: 600; margin: 0 0 2px; }
        .gel-doc-article-content span { font-size: 12px; color: var(--gel-muted); }
        .gel-doc-article-arrow { color: var(--gel-border); font-size: 14px; transition: color var(--transition), transform var(--transition); }
        .gel-doc-article:hover .gel-doc-article-arrow { color: var(--gel-primary); transform: translateX(3px); }

        .gel-video-card { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 12px; overflow: hidden; height: 100%; transition: transform var(--transition), box-shadow var(--transition); text-decoration: none; color: inherit; display: block; }
        .gel-video-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); color: inherit; }
        .gel-video-thumb { width: 100%; height: 150px; background: var(--gel-darker); display: flex; align-items: center; justify-content: center; position: relative; }
        .gel-video-thumb i { font-size: 40px; color: var(--gel-primary); opacity: 0.8; transition: opacity var(--transition), transform var(--transition); }
        .gel-video-card:hover .gel-video-thumb i { opacity: 1; transform: scale(1.1); }
        .gel-video-thumb span { position: absolute; bottom: 8px; right: 10px; background: rgba(0,0,0,0.7); color: #fff; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 3px; }
        .gel-video-body { padding: 14px 16px 18px; }
        .gel-video-body h6 { font-size: 13px; font-weight: 600; margin-bottom: 4px; }
        .gel-video-body p { font-size: 11.5px; color: var(--gel-muted); margin: 0; line-height: 1.5; }

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
                        <li><a href="/blogue"><span class="drop-icon"><i class="bi-pencil-square"></i></span> Blogue</a></li>
                        <li><a href="/documentation" style="background:var(--gel-primary-soft);color:var(--gel-primary);font-weight:600;"><span class="drop-icon"><i class="bi-file-text"></i></span> Documentation</a></li>
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
        <a href="/blogue" class="gel-mobile-link"><i class="bi-pencil-square text-orange me-2"></i>Blogue</a>
        <a href="/documentation" class="gel-mobile-link" style="color:var(--gel-primary);font-weight:600;"><i class="bi-file-text text-orange me-2"></i>Documentation</a>
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
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="gel-page-header-badge"><i class="bi-file-text"></i> Ressources</div>
                    <h1>Documentation</h1>
                    <p>Toute la documentation de GEL Cabinet. Guides, tutoriels vidéo, manuels de référence pour utiliser la plateforme sans vous prendre la tête.</p>
                </div>
                <div class="col-lg-4 d-flex align-items-end justify-content-lg-end mt-4 mt-lg-0">
                    <div style="max-width:320px;width:100%;position:relative;">
                        <i class="bi-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gel-muted);font-size:15px;"></i>
                        <input type="text" placeholder="Rechercher dans la doc..." oninput="filterDocs(this.value)" style="width:100%;padding:12px 16px 12px 40px;border-radius:8px;border:1px solid rgba(255,255,255,0.2);font-size:13px;outline:none;background:rgba(255,255,255,0.08);color:#fff;backdrop-filter:blur(4px);">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CATEGORIES -->
    <section class="gel-section" style="padding-bottom:0;">
        <div class="container">
            <div class="row g-4" id="docCategories">
                <div class="col-md-6 col-lg-3 doc-cat">
                    <a href="#" class="gel-doc-card" onclick="event.preventDefault();showSection('debuter')">
                        <div class="gel-doc-icon"><i class="bi-rocket-takeoff"></i></div>
                        <h5>Débuter</h5>
                        <p>Guide de démarrage rapide, installation, création de compte et premiers pas sur la plateforme.</p>
                        <div class="gel-doc-count"><i class="bi-file-text"></i> 6 articles</div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 doc-cat">
                    <a href="#" class="gel-doc-card" onclick="event.preventDefault();showSection('compta')">
                        <div class="gel-doc-icon" style="color:#FF7900;background:rgba(255,121,0,0.08);"><i class="bi-calculator"></i></div>
                        <h5>Comptabilité</h5>
                        <p>Plan comptable, journaux, balance, bilan, déclarations TVA, IB et rapprochement bancaire.</p>
                        <div class="gel-doc-count"><i class="bi-file-text"></i> 12 articles</div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 doc-cat">
                    <a href="#" class="gel-doc-card" onclick="event.preventDefault();showSection('ged')">
                        <div class="gel-doc-icon" style="color:#3B82F6;background:rgba(59,130,246,0.08);"><i class="bi-folder2-open"></i></div>
                        <h5>GED & Documents</h5>
                        <p>Gestion documentaire, arborescence, versions, recherche full-text, OCR et archivage légal.</p>
                        <div class="gel-doc-count"><i class="bi-file-text"></i> 8 articles</div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 doc-cat">
                    <a href="#" class="gel-doc-card" onclick="event.preventDefault();showSection('erp')">
                        <div class="gel-doc-icon" style="color:#10B981;background:rgba(16,185,129,0.08);"><i class="bi-box-seam"></i></div>
                        <h5>ERP Intégré</h5>
                        <p>Stocks, facturation, RH, paie, trésorerie, tableau de bord et rapports de gestion.</p>
                        <div class="gel-doc-count"><i class="bi-file-text"></i> 10 articles</div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ARTICLES LIST -->
    <section class="gel-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="gel-section-title" id="docSectionTitle">Tous les articles</h3>
                    <p class="gel-section-sub" id="docSectionSub">Parcourez l'ensemble de notre base documentaire.</p>
                </div>
            </div>

            <div class="row mt-4" id="docArticles">

                <!-- === Débuter (6 articles) === -->
                <div class="col-lg-6 doc-article-item" data-section="debuter">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon"><i class="bi-rocket-takeoff"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Premiers pas sur GEL Cabinet</h6>
                            <span>Compte, espace de travail, interface : tout est dans la poche.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="debuter">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon"><i class="bi-person-plus"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Créer et gérer vos utilisateurs</h6>
                            <span>Invitez vos collaborateurs, assignez des rôles et gérez leurs permissions d'accès aux modules.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="debuter">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon"><i class="bi-gear"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Configuration du cabinet</h6>
                            <span>Paramétrez vos services, modules, préférences générales et personnalisez l'interface de votre cabinet.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="debuter">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon"><i class="bi-shield-check"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Sécurité et confidentialité</h6>
                            <span>Bonnes pratiques de sécurité, gestion des mots de passe, double facteur et chiffrement des données.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="debuter">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon"><i class="bi-phone"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Application mobile GEL Cabinet</h6>
                            <span>Installez et utilisez l'application mobile pour accéder à vos données depuis votre téléphone.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="debuter">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon"><i class="bi-clock-history"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Mises à jour et versions</h6>
                            <span>Suivez les nouveautés, les correctifs et l'historique des versions de la plateforme.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>

                <!-- === Comptabilité (6 articles) === -->
                <div class="col-lg-6 doc-article-item" data-section="compta">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#FF7900;background:rgba(255,121,0,0.08);"><i class="bi-journal-text"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Configurer le plan comptable</h6>
                            <span>Personnalisez le plan comptable SYSCOA/OHADA : comptes, sous-comptes et paramétrage des classes.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="compta">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#FF7900;background:rgba(255,121,0,0.08);"><i class="bi-pencil-square"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Saisir des écritures comptables</h6>
                            <span>Saisie des journaux : achats, ventes, banque, caisse et opérations diverses.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="compta">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#FF7900;background:rgba(255,121,0,0.08);"><i class="bi-bar-chart-line"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Générer le bilan et le compte de résultat</h6>
                            <span>Produisez les états financiers réglementaires OHADA : bilan, CR, ETIC et annexes.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="compta">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#FF7900;background:rgba(255,121,0,0.08);"><i class="bi-receipt"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Déclarations TVA et IB</h6>
                            <span>Préparez et générez vos déclarations fiscales : TVA, IB, IRPP et autres états périodiques.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="compta">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#FF7900;background:rgba(255,121,0,0.08);"><i class="bi-journal-check"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Rapprochement bancaire</h6>
                            <span>Importez vos relevés bancaires, pointez les écritures et générez les rapprochements périodiques.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="compta">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#FF7900;background:rgba(255,121,0,0.08);"><i class="bi-file-lock2"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Clôture mensuelle et annuelle</h6>
                            <span>Procédures de clôture, arrêté des comptes, provisions et report à nouveau.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>

                <!-- === GED & Documents (5 articles) === -->
                <div class="col-lg-6 doc-article-item" data-section="ged">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#3B82F6;background:rgba(59,130,246,0.08);"><i class="bi-folder-plus"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Créer une arborescence de dossiers</h6>
                            <span>Organisez vos documents par clients, années et types avec une arborescence hiérarchique flexible.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="ged">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#3B82F6;background:rgba(59,130,246,0.08);"><i class="bi-upload"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Uploader et versionner des documents</h6>
                            <span>Ajoutez des fichiers, gérez les versions, restaurez une version antérieure et suivez les modifications.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="ged">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#3B82F6;background:rgba(59,130,246,0.08);"><i class="bi-search-heart"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Recherche full-text et OCR</h6>
                            <span>Recherchez du contenu dans vos documents scannés grâce à l'OCR et au moteur de recherche full-text.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="ged">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#3B82F6;background:rgba(59,130,246,0.08);"><i class="bi-share"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Partage et collaboration</h6>
                            <span>Partagez des documents avec vos clients et collaborateurs en toute sécurité.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="ged">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#3B82F6;background:rgba(59,130,246,0.08);"><i class="bi-archive"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Archivage légal et conservation</h6>
                            <span>Conformité aux obligations d'archivage : durée légale, horodatage et intégrité des documents.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>

                <!-- === ERP Intégré (5 articles) === -->
                <div class="col-lg-6 doc-article-item" data-section="erp">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#10B981;background:rgba(16,185,129,0.08);"><i class="bi-box"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Gérer les stocks et le catalogue</h6>
                            <span>Créez des articles, catégories, gammes et suivez les mouvements de stock en temps réel.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="erp">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#10B981;background:rgba(16,185,129,0.08);"><i class="bi-file-invoice"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Créer une facture et gérer les paiements</h6>
                            <span>Devis, factures, avoirs, relances et suivi des encaissements.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="erp">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#10B981;background:rgba(16,185,129,0.08);"><i class="bi-cash-coin"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Trésorerie et rapprochement</h6>
                            <span>Suivez votre trésorerie, gérez les encaissements et décaissements et rapprochez vos comptes.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="erp">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#10B981;background:rgba(16,185,129,0.08);"><i class="bi-people"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Gestion RH et paie intégrée</h6>
                            <span>Gérez les employés, contrats, absences, congés et calculez la paie avec les cotisations CNSS.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>
                <div class="col-lg-6 doc-article-item" data-section="erp">
                    <a href="#" class="gel-doc-article">
                        <div class="gel-doc-article-icon" style="color:#10B981;background:rgba(16,185,129,0.08);"><i class="bi-graph-up"></i></div>
                        <div class="gel-doc-article-content">
                            <h6>Tableaux de bord et rapports</h6>
                            <span>Créez des tableaux de bord personnalisés, indicateurs de performance et rapports de gestion exportables.</span>
                        </div>
                        <div class="gel-doc-article-arrow"><i class="bi-arrow-right"></i></div>
                    </a>
                </div>

            </div>

            <div class="text-center mt-4">
                <span style="font-size:13px;color:var(--gel-muted);" id="docCount">22 articles et guides disponibles</span>
            </div>
        </div>
    </section>

    <!-- GUIDES VIDÉO -->
    <section class="gel-section" style="padding-top:0;">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="gel-section-title">Guides vidéo</h3>
                    <p class="gel-section-sub">Pour ceux qui préfèrent regarder plutôt que lire.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-sm-6 col-lg-3">
                    <a href="#" class="gel-video-card anim-fade-up delay-1">
                        <div class="gel-video-thumb" style="background:linear-gradient(135deg,#0A1628,#1E293B);">
                            <i class="bi-play-circle-fill"></i>
                            <span>5:32</span>
                        </div>
                        <div class="gel-video-body">
                            <h6>Premiers pas sur GEL Cabinet</h6>
                            <p>L'interface et les bases en 5 minutes.</p>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="#" class="gel-video-card anim-fade-up delay-2">
                        <div class="gel-video-thumb" style="background:linear-gradient(135deg,#1a0a2e,#2d1b4e);">
                            <i class="bi-play-circle-fill"></i>
                            <span>8:15</span>
                        </div>
                        <div class="gel-video-body">
                            <h6>Saisir des écritures comptables</h6>
                            <p>Apprenez à saisir vos premières écritures dans les journaux.</p>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="#" class="gel-video-card anim-fade-up delay-3">
                        <div class="gel-video-thumb" style="background:linear-gradient(135deg,#0a2e1a,#1b4e2d);">
                            <i class="bi-play-circle-fill"></i>
                            <span>6:48</span>
                        </div>
                        <div class="gel-video-body">
                            <h6>Gérer votre GED documentaire</h6>
                            <p>Organisez, classez et retrouvez vos documents facilement.</p>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="#" class="gel-video-card anim-fade-up delay-4">
                        <div class="gel-video-thumb" style="background:linear-gradient(135deg,#2e1a0a,#4e2d1b);">
                            <i class="bi-play-circle-fill"></i>
                            <span>7:20</span>
                        </div>
                        <div class="gel-video-body">
                            <h6>Créer une facture en 2 clics</h6>
                            <p>Générez des factures professionnelles et suivez les paiements.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="gel-modal-overlay" id="authModal">
        <div class="gel-modal-box">
            <button class="gel-modal-close" onclick="closeModal()">&times;</button>
            <div class="gel-modal-icon"><i class="bi-file-text-fill"></i></div>
            <h3>Documentation complète</h3>
            <p>Créez un compte pour accéder à toute la documentation, guides et tutoriels vidéo.</p>
            <a href="/register" class="gel-modal-btn gel-modal-btn-primary"><i class="bi-person-plus"></i> Créer un compte</a>
            <div class="gel-modal-divider">ou</div>
            <a href="/login" class="gel-modal-btn gel-modal-btn-outline"><i class="bi-box-arrow-in-right"></i> Se connecter</a>
        </div>
    </div>

    <!-- Footer -->
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

        function showSection(section) {
            document.querySelectorAll('.doc-article-item').forEach(item => {
                item.style.display = (!section || item.dataset.section === section) ? '' : 'none';
            });
            const titles = { 'debuter':'Débuter','compta':'Comptabilité','ged':'GED & Documents','erp':'ERP Intégré' };
            document.getElementById('docSectionTitle').textContent = titles[section] || 'Tous les articles';
            document.getElementById('docSectionSub').textContent = section ? 'Articles et guides pour la section sélectionnée.' : 'Parcourez l\'ensemble de notre base documentaire.';
            document.getElementById('docCategories').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function filterDocs(q) {
            const articles = document.querySelectorAll('.gel-doc-article');
            articles.forEach(a => {
                const text = a.textContent.toLowerCase();
                const item = a.closest('.doc-article-item');
                if (item) item.style.display = text.includes(q.toLowerCase()) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
