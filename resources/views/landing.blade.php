<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GEL Cabinet | Gestion de Cabinet Multi-Pôles</title>
    <meta name="description" content="Plateforme intégrée de gestion de cabinet pluridisciplinaire. CRM, GED, Pôles, Missions et Comptabilité.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --gel-primary:   #FF7900;
            --gel-navy:      #13476E;
            --gel-navy:      #FF7900;
            --gel-dark:      #1a1a1a;
            --gel-light:     #f4f5f7;
            --gel-border:    #e0e0e0;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            color: #1a1a1a;
            margin: 0;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        /* ── Navbar ─────────────────────────────────────────── */
        .gel-navbar {
            background: var(--gel-navy);
            height: 56px;
            border-bottom: 3px solid var(--gel-primary);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
        }
        .gel-navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .gel-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .gel-brand-icon {
            width: 32px; height: 32px;
            background: var(--gel-primary);
            border-radius: 2px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 900;
            color: #fff; letter-spacing: -0.5px;
        }
        .gel-brand-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700; font-size: 16px;
            color: #fff;
        }
        .gel-nav-links { display: flex; align-items: center; gap: 4px; }
        .gel-nav-link {
            color: rgba(255,255,255,0.75);
            font-size: 13px; font-weight: 500;
            padding: 6px 14px;
            text-decoration: none;
            border-radius: 2px;
            transition: all 0.15s;
        }
        .gel-nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .gel-btn-primary {
            background: var(--gel-primary);
            color: #fff;
            border: none; border-radius: 2px;
            padding: 7px 18px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: background 0.15s;
        }
        .gel-btn-primary:hover { background: #e06700; color: #fff; }
        .gel-btn-outline {
            background: transparent;
            color: rgba(255,255,255,0.85);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 2px;
            padding: 6px 16px;
            font-size: 13px; font-weight: 500;
            cursor: pointer; text-decoration: none;
            transition: all 0.15s;
        }
        .gel-btn-outline:hover { background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.6); }
        .gel-btn-danger {
            background: #cd3c14; color: #fff;
            border: none; border-radius: 2px;
            padding: 6px 16px; font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: background 0.15s;
        }
        .gel-btn-danger:hover { background: #a83010; color: #fff; }
        .navbar-toggler-custom {
            background: none; border: 1px solid rgba(255,255,255,0.3);
            border-radius: 2px; color: #fff;
            padding: 6px 10px; cursor: pointer;
            display: none;
        }
        @media (max-width: 991px) {
            .gel-nav-links { display: none; }
            .navbar-toggler-custom { display: block; }
        }

        /* ── Orange Sub-bar ─────────────────────────────────── */
        .gel-subbar {
            background: var(--gel-primary);
            height: 32px;
            margin-top: 56px;
            display: flex; align-items: center;
        }
        .gel-subbar .container {
            display: flex; align-items: center; gap: 24px;
        }
        .gel-subnav-link {
            color: rgba(255,255,255,0.85);
            font-size: 12px; font-weight: 600;
            text-decoration: none;
            padding: 0 2px;
            border-bottom: 2px solid transparent;
            transition: all 0.15s;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .gel-subnav-link:hover, .gel-subnav-link.active {
            color: #fff;
            border-bottom-color: rgba(255,255,255,0.6);
        }

        /* ── Hero ───────────────────────────────────────────── */
        .gel-hero {
            background: linear-gradient(135deg, var(--gel-navy) 0%, #FF7900 100%);
            padding: 72px 0 64px;
            color: #fff;
            position: relative;
            overflow: hidden;
            border-bottom: 4px solid var(--gel-primary);
        }
        .gel-hero::before {
            content: '';
            position: absolute; top: 0; right: 0;
            width: 400px; height: 100%;
            background: rgba(255,121,0,0.06);
            clip-path: polygon(100px 0, 100% 0, 100% 100%, 0 100%);
        }
        .gel-hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,121,0,0.2);
            border: 1px solid rgba(255,121,0,0.4);
            color: #FF7900;
            font-size: 12px; font-weight: 600;
            padding: 5px 14px; border-radius: 2px;
            margin-bottom: 20px;
            text-transform: uppercase; letter-spacing: 0.06em;
        }
        .gel-hero h1 { font-size: 2.6rem; font-weight: 800; line-height: 1.2; color: #fff; }
        .gel-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.8); }
        .gel-hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 28px; }
        .gel-hero-btn-main {
            background: var(--gel-primary); color: #fff;
            border: none; border-radius: 2px;
            padding: 12px 28px; font-size: 14px; font-weight: 700;
            cursor: pointer; text-decoration: none;
            transition: background 0.15s;
        }
        .gel-hero-btn-main:hover { background: #e06700; color: #fff; }
        .gel-hero-btn-sec {
            background: transparent; color: #fff;
            border: 2px solid rgba(255,255,255,0.45); border-radius: 2px;
            padding: 12px 28px; font-size: 14px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all 0.15s;
        }
        .gel-hero-btn-sec:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.8); color: #fff; }
        .gel-hero-visual {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 2px;
            padding: 32px;
            text-align: center;
        }
        .gel-hero-stats {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 40px;
        }
        .gel-stat {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 2px; padding: 14px;
        }
        .gel-stat-num { font-size: 22px; font-weight: 800; color: var(--gel-primary); }
        .gel-stat-lbl { font-size: 11px; color: rgba(255,255,255,0.6); margin-top: 2px; }

        /* ── Sections ────────────────────────────────────────── */
        .gel-section { padding: 64px 0; }
        .gel-section-alt { background: var(--gel-light); }
        .gel-section-header { margin-bottom: 40px; }
        .gel-section-label {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,121,0,0.1); color: var(--gel-primary);
            font-size: 11px; font-weight: 700;
            padding: 4px 12px; border-radius: 2px;
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 12px;
        }
        .gel-section-title { font-size: 2rem; font-weight: 800; color: #1a1a1a; margin-bottom: 8px; }
        .gel-section-sub { font-size: 15px; color: #666; }

        /* ── Service Cards ───────────────────────────────────── */
        .gel-service-card {
            background: #fff;
            border: 1px solid var(--gel-border);
            border-radius: 2px;
            padding: 28px 24px;
            height: 100%;
            transition: box-shadow 0.2s, transform 0.2s, border-color 0.2s;
            position: relative;
            overflow: hidden;
        }
        .gel-service-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--gel-primary);
            transform: scaleX(0);
            transition: transform 0.2s;
            transform-origin: left;
        }
        .gel-service-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            border-color: rgba(255,121,0,0.3);
        }
        .gel-service-card:hover::before { transform: scaleX(1); }
        .gel-service-icon {
            width: 52px; height: 52px;
            border-radius: 2px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        .gel-service-card h5 { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 10px; }
        .gel-service-card p { font-size: 13px; color: #666; line-height: 1.6; margin: 0; }

        /* ── Feature Cards ───────────────────────────────────── */
        .gel-feature-card {
            background: #fff;
            border: 1px solid var(--gel-border);
            border-radius: 2px;
            padding: 20px;
            height: 100%;
            display: flex; align-items: flex-start; gap: 14px;
            transition: box-shadow 0.15s;
        }
        .gel-feature-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
        .gel-feature-icon {
            width: 40px; height: 40px; flex-shrink: 0;
            background: rgba(255,121,0,0.1);
            border-radius: 2px;
            display: flex; align-items: center; justify-content: center;
            color: var(--gel-primary); font-size: 18px;
        }
        .gel-feature-card h6 { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
        .gel-feature-card p { font-size: 12px; color: #777; margin: 0; line-height: 1.5; }

        /* ── Contact Form ────────────────────────────────────── */
        .gel-form-card {
            background: #fff;
            border: 1px solid var(--gel-border);
            border-radius: 2px;
            padding: 36px;
        }
        .form-label { font-size: 12px; font-weight: 600; color: #444; margin-bottom: 5px; }
        .form-control, .form-select {
            border-radius: 2px !important;
            border: 1px solid var(--gel-border);
            font-size: 13px;
            padding: 10px 14px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--gel-primary);
            box-shadow: 0 0 0 2px rgba(255,121,0,0.15);
        }

        /* ── Footer ─────────────────────────────────────────── */
        .gel-footer {
            background: var(--gel-dark);
            color: rgba(255,255,255,0.6);
            padding: 24px 0;
            border-top: 3px solid var(--gel-primary);
        }
        .gel-footer a { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 13px; }
        .gel-footer a:hover { color: var(--gel-primary); }

        /* ── Section divider ─────────────────────────────────── */
        .gel-divider {
            height: 3px;
            background: linear-gradient(90deg, var(--gel-primary) 0%, var(--gel-navy) 100%);
        }
    </style>
</head>
<body>
    <!-- Navbar iSupplier style -->
    <nav class="gel-navbar">
        <div class="container">
            <a class="gel-brand" href="/">
                <div class="gel-brand-icon">GEL</div>
                <span class="gel-brand-name">GEL Cabinet</span>
            </a>
            <div class="gel-nav-links">
                <a class="gel-nav-link" href="/nos-services"><i class="bi-shop me-1"></i>Nos Services</a>
                <a class="gel-nav-link" href="#services">Modules ERP</a>
                <a class="gel-nav-link" href="#fonctionnalites">Fonctionnalités</a>
                <a class="gel-nav-link" href="#contact">Contact</a>
                @auth
                    @if(auth()->user()->role === 'client')
                        <a href="{{ route('client.orders.index') }}" class="gel-btn-outline ms-2">
                            <i class="bi-speedometer2 me-1"></i>Mon Espace
                        </a>
                    @elseif(auth()->user()->client_id)
                        <a href="{{ route('company.dashboard') }}" class="gel-btn-outline ms-2">
                            <i class="bi-speedometer2 me-1"></i>Portail Entreprise
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="gel-btn-outline ms-2">
                            <i class="bi-speedometer2 me-1"></i>Tableau de bord
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="gel-btn-danger ms-2">
                            <i class="bi-box-arrow-right me-1"></i>Déconnexion
                        </button>
                    </form>
                @else
                    <a href="/login" class="gel-btn-primary ms-2">
                        <i class="bi-box-arrow-in-right me-1"></i>Connexion
                    </a>
                @endauth
            </div>
            <button class="navbar-toggler-custom" type="button">
                <i class="bi-list"></i>
            </button>
        </div>
    </nav>

    <!-- Orange sub-navigation bar (iSupplier style) -->
    <div class="gel-subbar">
        <div class="container">
            <a href="/" class="gel-subnav-link active">Accueil</a>
            <a href="/nos-services" class="gel-subnav-link">Catalogue</a>
            <a href="#services" class="gel-subnav-link">ERP</a>
            <a href="#contact" class="gel-subnav-link">Contact</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="gel-hero">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="gel-hero-badge">
                        <i class="bi-star-fill" style="color: #FF7900;"></i>Solution intégrée de gestion
                    </div>
                    <h1>Gérez votre cabinet<br>d'expertise en toute sérénité</h1>
                    <p class="my-3">CRM, GED, Pôles, Missions, Comptabilité et ERP — une plateforme unique pour piloter l'intégralité de votre cabinet pluridisciplinaire.</p>
                    <div class="gel-hero-actions">
                        <a href="/nos-services" class="gel-hero-btn-main">
                            <i class="bi-shop me-2"></i>Découvrir nos services
                        </a>
                        <a href="/login" class="gel-hero-btn-sec">
                            <i class="bi-box-arrow-in-right me-2"></i>Espace Client
                        </a>
                    </div>
                    <!-- Stats bar -->
                    <div class="gel-hero-stats">
                        <div class="gel-stat">
                            <div class="gel-stat-num">6+</div>
                            <div class="gel-stat-lbl">Modules ERP</div>
                        </div>
                        <div class="gel-stat">
                            <div class="gel-stat-num">100%</div>
                            <div class="gel-stat-lbl">Sécurisé</div>
                        </div>
                        <div class="gel-stat">
                            <div class="gel-stat-num">24/7</div>
                            <div class="gel-stat-lbl">Disponible</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-center">
                    <div class="gel-hero-visual">
                        <i class="bi-building" style="font-size: 5rem; color: rgba(255,121,0,0.6);"></i>
                        <p class="mt-3 small" style="color: rgba(255,255,255,0.6);">Gestion complète de votre cabinet</p>
                        <!-- Mini springboard preview -->
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                            @foreach([['bi-calculator','Compta'],['bi-folder','GED'],['bi-people','RH'],['bi-receipt','Factures'],['bi-graph-up','ERP'],['bi-shield','Sécurité']] as $item)
                            <div style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.18); border-radius:2px; padding:8px 12px; text-align:center; min-width:72px;">
                                <i class="bi {{ $item[0] }}" style="color:#FF7900; font-size:18px; display:block;"></i>
                                <span style="font-size:10px; color:rgba(255,255,255,0.7);">{{ $item[1] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gel-divider"></div>

    <!-- Services -->
    <section id="services" class="gel-section">
        <div class="container">
            <div class="gel-section-header text-center">
                <div class="gel-section-label"><i class="bi-grid-3x3-gap"></i>Nos Services</div>
                <h2 class="gel-section-title">Tout ce qu'il faut pour votre cabinet</h2>
                <p class="gel-section-sub">Des modules spécialisés pour chaque pôle d'expertise</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="gel-service-card">
                        <div class="gel-service-icon" style="background: #e3f2fd; color: #1565c0;">
                            <i class="bi-calculator"></i>
                        </div>
                        <h5>Comptabilité</h5>
                        <p>Plan comptable, journaux, balance, bilan et compte de résultat. Gestion complète de la chaîne comptable.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gel-service-card">
                        <div class="gel-service-icon" style="background: #fce4ec; color: #c62828;">
                            <i class="bi-bank"></i>
                        </div>
                        <h5>Juridique</h5>
                        <p>Suivi des dossiers, veille juridique, gestion des formalités et constitution de sociétés.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gel-service-card">
                        <div class="gel-service-icon" style="background: #e8f5e9; color: #2e7d32;">
                            <i class="bi-file-earmark-text"></i>
                        </div>
                        <h5>Fiscal</h5>
                        <p>Déclarations fiscales, optimisation, gestion des échéances et accompagnement contrôle fiscal.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gel-service-card">
                        <div class="gel-service-icon" style="background: #fff3e0; color: #e65100;">
                            <i class="bi-people"></i>
                        </div>
                        <h5>Social &amp; Paie</h5>
                        <p>Gestion de la paie, déclarations sociales, contrats de travail et administration du personnel.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gel-service-card">
                        <div class="gel-service-icon" style="background: #f3e5f5; color: #6a1b9a;">
                            <i class="bi-shop"></i>
                        </div>
                        <h5>Commercial</h5>
                        <p>Suivi commercial, facturation, relances clients et gestion des contrats de prestation.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gel-service-card">
                        <div class="gel-service-icon" style="background: rgba(255,121,0,0.1); color: #FF7900;">
                            <i class="bi-box-seam"></i>
                        </div>
                        <h5>ERP Intégré</h5>
                        <p>Catalogue, stocks, facturation, RH &amp; paie, trésorerie. Un ERP complet pour votre cabinet.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="/nos-services" class="gel-hero-btn-main">
                    <i class="bi-arrow-right me-2"></i>Voir tous nos services
                </a>
            </div>
        </div>
    </section>

    <div class="gel-divider"></div>

    <!-- Fonctionnalités -->
    <section id="fonctionnalites" class="gel-section gel-section-alt">
        <div class="container">
            <div class="gel-section-header text-center">
                <div class="gel-section-label"><i class="bi-lightning-charge"></i>Fonctionnalités</div>
                <h2 class="gel-section-title">Une plateforme complète</h2>
                <p class="gel-section-sub">Tout ce dont vous avez besoin, intégré en un seul outil</p>
            </div>
            <div class="row g-3">
                <div class="col-md-4 col-6">
                    <div class="gel-feature-card">
                        <div class="gel-feature-icon"><i class="bi-people"></i></div>
                        <div>
                            <h6>CRM Clients</h6>
                            <p>Gestion des entreprises, contacts et relations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="gel-feature-card">
                        <div class="gel-feature-icon"><i class="bi-diagram-3"></i></div>
                        <div>
                            <h6>Pôles &amp; Missions</h6>
                            <p>Organisation par départements et suivi des missions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="gel-feature-card">
                        <div class="gel-feature-icon"><i class="bi-folder2-open"></i></div>
                        <div>
                            <h6>GED</h6>
                            <p>Gestion électronique de documents et dossiers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="gel-feature-card">
                        <div class="gel-feature-icon"><i class="bi-calculator"></i></div>
                        <div>
                            <h6>Comptabilité</h6>
                            <p>Plan comptable, journaux, bilan, résultat</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="gel-feature-card">
                        <div class="gel-feature-icon"><i class="bi-graph-up"></i></div>
                        <div>
                            <h6>ERP Intégré</h6>
                            <p>Stocks, factures, RH, trésorerie</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="gel-feature-card">
                        <div class="gel-feature-icon"><i class="bi-shield-check"></i></div>
                        <div>
                            <h6>Sécurisé</h6>
                            <p>Authentification, rôles et permissions avancées</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gel-divider"></div>

    <!-- Contact / Demande -->
    <section id="contact" class="gel-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="gel-section-header text-center">
                        <div class="gel-section-label"><i class="bi-envelope"></i>Contact</div>
                        <h2 class="gel-section-title">Demander une démo</h2>
                        <p class="gel-section-sub">Laissez-nous vos coordonnées, nous vous recontacterons</p>
                    </div>
                    <div class="gel-form-card">
                        <form id="demo-form" method="POST" action="/demande">
                            @csrf
                            @if(session('success'))
                                <div class="alert" style="background:#e8f5e9; border:1px solid #c8e6c9; color:#198754; border-radius:2px; font-size:13px; padding:12px 16px; margin-bottom:20px;">
                                    <i class="bi-check-circle me-2"></i>{{ session('success') }}
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Entreprise</label>
                                    <input type="text" name="company_name" class="form-control" required placeholder="Nom de votre entreprise">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Personne à contacter</label>
                                    <input type="text" name="contact_name" class="form-control" required placeholder="Votre nom">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required placeholder="email@entreprise.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+229 XX XX XX XX">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="3" placeholder="Parlez-nous de vos besoins..."></textarea>
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <button type="submit" class="gel-hero-btn-main" style="padding: 12px 40px; font-size: 14px;">
                                        <i class="bi-send me-2"></i>Envoyer la demande
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="gel-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:24px; height:24px; background:#FF7900; border-radius:2px; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:900; color:#fff;">GEL</div>
                        <span style="font-family:'Outfit',sans-serif; font-weight:700; font-size:14px; color:rgba(255,255,255,0.8);">GEL Cabinet</span>
                    </div>
                    <p class="mb-0" style="font-size:12px;">
                        &copy; {{ date('Y') }} GEL Cabinet. Tous droits réservés.
                    </p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <a href="/login" class="me-3">
                        <i class="bi-box-arrow-in-right me-1"></i>Connexion
                    </a>
                    <a href="/nos-services">
                        <i class="bi-shop me-1"></i>Catalogue
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
