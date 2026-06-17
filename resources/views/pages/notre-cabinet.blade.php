<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notre Cabinet | GEL Cabinet</title>
    <meta name="description" content="GEL Cabinet — la plateforme de gestion pour cabinets comptables, juridiques et multi-pôles.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --gel-primary: #FF7900; --gel-primary-hov: #e06700; --gel-primary-soft: rgba(255,121,0,0.06);
            --gel-blue: #3B82F6; --gel-blue-dark: #1E3A5F; --gel-blue-soft: rgba(59,130,246,0.06);
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
        body { font-family: var(--font-body); background: var(--gel-white); color: var(--gel-text); line-height: 1.6; }
        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading); font-weight: 700; }

        .anim-fade-up { opacity: 0; transform: translateY(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-visible { opacity: 1 !important; transform: none !important; }
        .delay-1 { transition-delay: 0.1s !important; }
        .delay-2 { transition-delay: 0.2s !important; }
        .delay-3 { transition-delay: 0.3s !important; }
        .delay-4 { transition-delay: 0.4s !important; }

        /* Navbar — identique à l'accueil */
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
            padding: 7px 13px;
            font-size: 13px; font-weight: 500;
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
        @media (max-width: 991px) {
            .gel-nav-center { display: none; }
            .gel-phone { display: none; }
            .gel-toggler { display: flex; }
            .gel-navbar .container-fluid { padding: 0 16px; }
        }

        /* Page Header */
        .gel-page-header {
            margin-top: var(--nav-height);
            background: var(--gel-light);
            padding: 80px 0 60px;
            border-bottom: 1px solid var(--gel-border);
        }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(2rem, 4vw, 2.8rem); font-weight: 900; color: var(--gel-dark); letter-spacing: -1px; }
        .gel-page-header p { color: var(--gel-muted); font-size: 15px; max-width: 540px; margin-top: 10px; line-height: 1.7; }

        .gel-section { padding: 80px 0; }
        .gel-section-alt { background: var(--gel-light); }
        .gel-section-chip { display: inline-flex; align-items: center; gap: 6px; background: var(--gel-white); color: var(--gel-primary); font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px; border: 1.5px solid rgba(255,121,0,0.2); }
        .gel-section-title { font-family: var(--font-heading); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--gel-dark); letter-spacing: -0.5px; line-height: 1.2; margin-bottom: 14px; }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); max-width: 540px; line-height: 1.7; }

        /* Stats */
        .gel-stats { background: var(--gel-white); padding: 40px 0; border-bottom: 1px solid var(--gel-border); }
        .gel-stat-item { text-align: center; padding: 12px 16px; }
        .gel-stat-num { font-family: var(--font-heading); font-size: 2rem; font-weight: 900; color: var(--gel-primary); display: block; }
        .gel-stat-lbl { font-size: 12px; font-weight: 500; color: var(--gel-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4px; }

        /* Cards */
        .gel-card { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 10px; padding: 28px 24px; height: 100%; transition: transform var(--transition), box-shadow var(--transition); }
        .gel-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .gel-card-icon { width: 44px; height: 44px; background: var(--gel-light2); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--gel-primary); font-size: 20px; margin-bottom: 14px; transition: background var(--transition), color var(--transition); }
        .gel-card:hover .gel-card-icon { background: var(--gel-primary); color: #fff; }
        .gel-card h5 { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
        .gel-card p { font-size: 13px; color: var(--gel-muted); margin: 0; line-height: 1.65; }

        /* CTA */
        .gel-cta-band { background: linear-gradient(135deg, var(--gel-primary) 0%, #ff9a3c 100%); padding: 60px 0; overflow: hidden; }
        .gel-cta-band h2 { font-size: clamp(1.5rem, 2.5vw, 2rem); font-weight: 900; color: #fff; }
        .gel-cta-band p { color: rgba(255,255,255,0.85); font-size: 15px; margin-top: 8px; }
        .gel-btn-white { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: var(--gel-primary); font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: var(--radius); text-decoration: none; transition: all 0.3s; }
        .gel-btn-white:hover { background: var(--gel-dark); color: #fff; transform: translateY(-2px); }

        /* Footer */
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

        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 60px 0 40px; } }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="gel-navbar" id="gelNavbar">
        <div class="container-fluid">
            <a href="/" class="gel-brand">
                <div class="gel-brand-logo">GEL</div>
                <div class="gel-brand-text">
                    <span class="gel-brand-name">GEL Cabinet</span>
                    <span class="gel-brand-sub">Gestion Multi-Pôles</span>
                </div>
            </a>

            <ul class="gel-nav-center" id="gelNavCenter">
                <li class="gel-nav-item">
                    <a href="#" class="gel-nav-link">
                        Nos Modules
                        <i class="bi-chevron-down chevron"></i>
                    </a>
                    <ul class="gel-dropdown">
                        <li><a href="/login"><span class="drop-icon"><i class="bi-people"></i></span> CRM Clients</a></li>
                        <li><a href="/login"><span class="drop-icon"><i class="bi-folder2-open"></i></span> GED — Documents</a></li>
                        <li><a href="/login"><span class="drop-icon"><i class="bi-diagram-3"></i></span> Pôles & Missions</a></li>
                        <li><hr class="gel-dropdown-divider"></li>
                        <li><a href="/login"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                        <li><a href="/login"><span class="drop-icon"><i class="bi-box-seam"></i></span> ERP Intégré</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item">
                    <a href="/services" class="gel-nav-link">
                        Services
                        <i class="bi-chevron-down chevron"></i>
                    </a>
                    <ul class="gel-dropdown">
                        <li><a href="/services/comptabilite"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                        <li><a href="/services/juridique"><span class="drop-icon"><i class="bi-bank2"></i></span> Juridique</a></li>
                        <li><a href="/services/fiscal"><span class="drop-icon"><i class="bi-receipt"></i></span> Fiscal</a></li>
                        <li><a href="/services/social-paie"><span class="drop-icon"><i class="bi-people"></i></span> Social & Paie</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item">
                    <a href="#" class="gel-nav-link active">
                        À propos
                        <i class="bi-chevron-down chevron"></i>
                    </a>
                    <ul class="gel-dropdown">
                        <li><a href="{{ route('notre-cabinet') }}" style="background:var(--gel-primary-soft);color:var(--gel-primary);font-weight:600;"><span class="drop-icon"><i class="bi-building"></i></span> Notre Cabinet</a></li>
                        <li><a href="{{ route('notre-equipe') }}"><span class="drop-icon"><i class="bi-people-fill"></i></span> Notre Équipe</a></li>
                        <li><a href="{{ route('carrieres') }}"><span class="drop-icon"><i class="bi-briefcase-fill"></i></span> Carrières</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item">
                    <a href="#" class="gel-nav-link">
                        Ressources
                        <i class="bi-chevron-down chevron"></i>
                    </a>
                    <ul class="gel-dropdown">
                        <li><a href="/blogue"><span class="drop-icon"><i class="bi-pencil-square"></i></span> Blogue</a></li>
                        <li><a href="/documentation"><span class="drop-icon"><i class="bi-file-text"></i></span> Documentation</a></li>
                        <li><a href="/faq"><span class="drop-icon"><i class="bi-question-circle"></i></span> FAQ</a></li>
                        <li><hr class="gel-dropdown-divider"></li>
                        <li><a href="/centre-aide"><span class="drop-icon"><i class="bi-headset"></i></span> Centre d'aide</a></li>
                    </ul>
                </li>
                <li class="gel-nav-item"><a href="#" class="gel-nav-link">Tarifs</a></li>
                <li class="gel-nav-item"><a href="#contact" class="gel-nav-link">Contact</a></li>
            </ul>

            <div class="gel-nav-right">
                <a href="tel:+22900000000" class="gel-phone d-none d-lg-flex">
                    <i class="bi-telephone-fill"></i>
                    +229 XX XX XX XX
                </a>
                @auth
                    @if(auth()->user()->role === 'client')
                        <a href="{{ route('client.orders.index') }}" class="gel-btn-nav gel-btn-nav-outline">
                            <i class="bi-speedometer2"></i> Mon Espace
                        </a>
                    @elseif(auth()->user()->client_id)
                        <a href="{{ route('company.dashboard') }}" class="gel-btn-nav gel-btn-nav-outline">
                            <i class="bi-speedometer2"></i> Portail
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="gel-btn-nav gel-btn-nav-outline">
                            <i class="bi-speedometer2"></i> Tableau de bord
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="gel-btn-nav gel-btn-nav-outline" style="color:#dc2626; border-color:#fca5a5;">
                            <i class="bi-box-arrow-right"></i>
                        </button>
                    </form>
                @else
                    <a href="/login" class="gel-btn-nav gel-btn-nav-primary">
                        <i class="bi-box-arrow-in-right"></i> Connexion
                    </a>
                @endauth
                <button class="gel-toggler" id="gelToggler" aria-label="Menu">
                    <i class="bi-list" id="togglerIcon"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="gel-mobile-menu" id="gelMobileMenu">
        <a href="/" class="gel-mobile-link"><i class="bi-house text-orange me-2"></i>Accueil</a>
        <a href="#" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Nos Modules</a>
        <a href="/services" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Services</a>
        <a href="/blogue" class="gel-mobile-link"><i class="bi-pencil-square text-orange me-2"></i>Blogue</a>
        <a href="/documentation" class="gel-mobile-link"><i class="bi-file-text text-orange me-2"></i>Documentation</a>
        <a href="/faq" class="gel-mobile-link"><i class="bi-question-circle text-orange me-2"></i>FAQ</a>
        <a href="/centre-aide" class="gel-mobile-link"><i class="bi-headset text-orange me-2"></i>Centre d'aide</a>
        <a href="{{ route('notre-cabinet') }}" class="gel-mobile-link" style="color:var(--gel-primary);font-weight:600;"><i class="bi-building text-orange me-2"></i>Notre Cabinet</a>
        <a href="{{ route('notre-equipe') }}" class="gel-mobile-link"><i class="bi-people-fill text-orange me-2"></i>Notre Équipe</a>
        <a href="{{ route('carrieres') }}" class="gel-mobile-link"><i class="bi-briefcase-fill text-orange me-2"></i>Carrières</a>
        <a href="#" class="gel-mobile-link"><i class="bi-currency-dollar text-orange me-2"></i>Tarifs</a>
        <a href="#contact" class="gel-mobile-link"><i class="bi-envelope text-orange me-2"></i>Contact</a>
        <a href="/login" class="gel-mobile-link"><i class="bi-box-arrow-in-right text-orange me-2"></i>Connexion</a>
    </div>

    <!-- Page Header -->
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="gel-section-chip"><i class="bi-building"></i> À propos</div>
                    <h1>Notre Cabinet</h1>
                    <p>GEL Cabinet en quelques mots : notre histoire, notre mission, nos valeurs.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="gel-stats">
        <div class="container">
            <div class="row">
                <div class="col-6 col-md-3 gel-stat-item anim-fade-up">
                    <span class="gel-stat-num">10+</span>
                    <div class="gel-stat-lbl">Années d'expertise</div>
                </div>
                <div class="col-6 col-md-3 gel-stat-item anim-fade-up delay-1">
                    <span class="gel-stat-num">500+</span>
                    <div class="gel-stat-lbl">Cabinets clients</div>
                </div>
                <div class="col-6 col-md-3 gel-stat-item anim-fade-up delay-2">
                    <span class="gel-stat-num">50+</span>
                    <div class="gel-stat-lbl">Experts dédiés</div>
                </div>
                <div class="col-6 col-md-3 gel-stat-item anim-fade-up delay-3">
                    <span class="gel-stat-num">98%</span>
                    <div class="gel-stat-lbl">Satisfaction client</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission & Valeurs -->
    <section class="gel-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 anim-fade-up">
                    <div class="gel-section-chip"><i class="bi-bullseye"></i> Notre Mission</div>
                    <h2 class="gel-section-title">Simplifier la gestion<br>des cabinets d'expertise</h2>
                    <p class="gel-section-sub" style="max-width:100%;">Chez GEL Cabinet, on met la technologie au service de l'expertise. Notre mission : donner aux cabinets comptables, juridiques et multi-pôles une plateforme qui centralise et simplifie leurs opérations au quotidien.</p>
                    <p class="gel-section-sub" style="max-width:100%; margin-top:12px;">CRM, GED, comptabilité, pilotage des missions — on accompagne chaque cabinet dans sa transformation digitale.</p>
                </div>
                <div class="col-lg-6 anim-fade-up delay-1">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="gel-card" style="border-top: 3px solid var(--gel-primary);">
                                <div class="gel-card-icon"><i class="bi-star-fill"></i></div>
                                <h5>Excellence</h5>
                                <p>Nous visons l'excellence dans chaque solution que nous déployons.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="gel-card" style="border-top: 3px solid var(--gel-primary);">
                                <div class="gel-card-icon"><i class="bi-shield-check"></i></div>
                                <h5>Confiance</h5>
                                <p>La sécurité et la confidentialité des données sont notre priorité.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="gel-card" style="border-top: 3px solid var(--gel-primary);">
                                <div class="gel-card-icon"><i class="bi-lightbulb-fill"></i></div>
                                <h5>Innovation</h5>
                                <p>Nous innovons constamment pour répondre aux défis du métier.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="gel-card" style="border-top: 3px solid var(--gel-primary);">
                                <div class="gel-card-icon"><i class="bi-people-fill"></i></div>
                                <h5>Proximité</h5>
                                <p>Un accompagnement humain et personnalisé pour chaque client.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Histoire -->
    <section class="gel-section gel-section-alt">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <div class="gel-section-chip anim-fade-up"><i class="bi-clock-history"></i> Notre Histoire</div>
                    <h2 class="gel-section-title anim-fade-up delay-1">Du concept à la référence</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">L'histoire de GEL Cabinet est celle d'une vision : créer l'outil ultime pour les cabinets d'expertise.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-card text-center">
                        <div style="font-family:var(--font-heading); font-size:32px; font-weight:900; color:var(--gel-primary); margin-bottom:8px;">2015</div>
                        <h5>La naissance</h5>
                        <p>Fondation de GEL Cabinet avec une vision claire : digitaliser la gestion des cabinets d'expertise comptable.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-card text-center">
                        <div style="font-family:var(--font-heading); font-size:32px; font-weight:900; color:var(--gel-primary); margin-bottom:8px;">2020</div>
                        <h5>L'expansion</h5>
                        <p>Lancement de la plateforme multi-pôles intégrant CRM, GED, Comptabilité et ERP en un seul outil.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-card text-center">
                        <div style="font-family:var(--font-heading); font-size:32px; font-weight:900; color:var(--gel-primary); margin-bottom:8px;">2025</div>
                        <h5>La référence</h5>
                        <p>Plus de 500 cabinets clients font confiance à GEL Cabinet pour leur gestion quotidienne.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="gel-cta-band">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 anim-fade-up">
                    <h2>Prêt à rejoindre les 500+ cabinets qui nous font confiance ?</h2>
                    <p>Demandez une démonstration personnalisée de notre plateforme.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 anim-fade-up delay-1">
                    <a href="/" class="gel-btn-white"><i class="bi-calendar-check"></i> Prendre un logiciel</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="gel-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div style="width:36px; height:36px; background:#FF7900; border-radius:4px; display:flex; align-items:center; justify-content:center; font-family:'Outfit',sans-serif; font-size:12px; font-weight:900; color:#fff;">GEL</div>
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
                    <div class="gel-footer-heading">Pages</div>
                    <ul class="gel-footer-links">
                        <li><a href="/">Accueil</a></li>
                        <li><a href="{{ route('notre-cabinet') }}">Notre Cabinet</a></li>
                        <li><a href="{{ route('notre-equipe') }}">Notre Équipe</a></li>
                        <li><a href="{{ route('carrieres') }}">Carrières</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="gel-footer-heading">Services</div>
                    <ul class="gel-footer-links">
                        <li><a href="/">Comptabilité</a></li>
                        <li><a href="/">Juridique</a></li>
                        <li><a href="/">Fiscal</a></li>
                        <li><a href="/">Social & Paie</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="gel-footer-heading">Contact</div>
                    <ul class="gel-footer-links" style="font-size:13px; color:rgba(255,255,255,0.4);">
                        <li><i class="bi-geo-alt" style="color:#FF7900; margin-right:6px;"></i>Cotonou, Bénin</li>
                        <li><i class="bi-telephone" style="color:#FF7900; margin-right:6px;"></i>+229 XX XX XX XX</li>
                        <li><i class="bi-envelope" style="color:#FF7900; margin-right:6px;"></i>contact@gelcabinet.com</li>
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
        const navbar = document.getElementById('gelNavbar');
        window.addEventListener('scroll', () => { navbar.classList.toggle('scrolled', window.scrollY > 20); }, { passive: true });
        const toggler = document.getElementById('gelToggler');
        const mobileMenu = document.getElementById('gelMobileMenu');
        const togglerIcon = document.getElementById('togglerIcon');
        toggler.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.toggle('open');
            togglerIcon.className = isOpen ? 'bi-x-lg' : 'bi-list';
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });
        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.remove('open');
                togglerIcon.className = 'bi-list';
                document.body.style.overflow = '';
            }
        });
        const animEls = document.querySelectorAll('.anim-fade-up');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('anim-visible'); observer.unobserve(entry.target); }
            });
        }, { threshold: 0.12 });
        animEls.forEach(el => observer.observe(el));
    </script>
</body>
</html>