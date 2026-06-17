<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nos Modules | GEL Cabinet</title>
    <meta name="description" content="Modules GEL Cabinet : CRM Clients, GED, Pôles & Missions, Comptabilité et ERP Intégré.">

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
        .anim-fade-left { opacity: 0; transform: translateX(-36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-fade-right { opacity: 0; transform: translateX(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-scale { opacity: 0; transform: scale(0.92); transition: opacity 0.55s var(--transition), transform 0.55s var(--transition); }
        .anim-visible { opacity: 1 !important; transform: none !important; }
        .delay-1 { transition-delay: 0.1s !important; }
        .delay-2 { transition-delay: 0.2s !important; }
        .delay-3 { transition-delay: 0.3s !important; }
        .delay-4 { transition-delay: 0.4s !important; }
        .delay-5 { transition-delay: 0.5s !important; }

        /* Navbar */
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
        .text-orange { color: var(--gel-primary); }
        @media (max-width: 991px) {
            .gel-nav-center { display: none; }
            .gel-phone { display: none; }
            .gel-toggler { display: flex; }
            .gel-navbar .container-fluid { padding: 0 16px; }
        }

        /* Page Header */
        .gel-page-header {
            margin-top: var(--nav-height);
            background: linear-gradient(135deg, #0A1628 0%, #1E293B 25%, #0F172A 50%, #1E293B 75%, #0A1628 100%);
            background-size: 300% 300%;
            animation: gelGradientMove 12s ease infinite;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }
        @keyframes gelGradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .gel-page-header::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,121,0,0.12) 0%, transparent 70%);
            border-radius: 50%;
            animation: gelFloatA 8s ease-in-out infinite;
        }
        .gel-page-header::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: gelFloatB 10s ease-in-out infinite;
        }
        @keyframes gelFloatA {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-30px, 20px) scale(1.05); }
            66% { transform: translate(20px, -10px) scale(0.95); }
        }
        @keyframes gelFloatB {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -20px) scale(1.08); }
            66% { transform: translate(-20px, 10px) scale(0.92); }
        }
        .gel-page-header h1 {
            font-family: var(--font-heading);
            font-size: clamp(2.2rem, 4.5vw, 3.2rem);
            font-weight: 900;
            color: #fff;
            letter-spacing: -1px;
            position: relative;
            z-index: 1;
        }
        .gel-page-header p {
            color: rgba(255,255,255,0.6);
            font-size: 15px;
            max-width: 580px;
            margin-top: 12px;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }
        .gel-page-header-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 5px 14px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        /* Sections */
        .gel-section { padding: 80px 0; }
        .gel-section-alt { background: var(--gel-light); }
        .gel-section-chip { display: inline-flex; align-items: center; gap: 6px; background: var(--gel-white); color: var(--gel-primary); font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px; border: 1.5px solid rgba(255,121,0,0.2); }
        .gel-section-title { font-family: var(--font-heading); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--gel-dark); letter-spacing: -0.5px; line-height: 1.2; margin-bottom: 14px; }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); max-width: 540px; line-height: 1.7; }

        /* Module Cards */
        .gel-module-card {
            background: var(--gel-white);
            border: 1px solid var(--gel-border);
            border-radius: 12px;
            padding: 32px 28px;
            height: 100%;
            transition: transform 0.3s var(--transition), box-shadow 0.3s var(--transition), border-color 0.3s var(--transition);
            position: relative;
            overflow: hidden;
        }
        .gel-module-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 12px 12px 0 0;
        }
        .gel-module-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: transparent;
        }
        .gel-module-card .module-top {
            display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px;
        }
        .gel-module-card .module-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .gel-module-card .module-icon.orange { background: rgba(255,121,0,0.1); color: var(--gel-primary); }
        .gel-module-card .module-icon.blue { background: rgba(59,130,246,0.1); color: #3B82F6; }
        .gel-module-card .module-icon.green { background: rgba(16,185,129,0.1); color: #10B981; }
        .gel-module-card .module-icon.purple { background: rgba(139,92,246,0.1); color: #8B5CF6; }
        .gel-module-card .module-icon.teal { background: rgba(20,184,166,0.1); color: #14B8A6; }
        .gel-module-card h3 { font-size: 16px; font-weight: 800; margin-bottom: 4px; }
        .gel-module-card .module-sub { font-size: 12px; color: var(--gel-muted); margin-bottom: 12px; }
        .gel-module-card p { font-size: 13px; color: var(--gel-muted); line-height: 1.6; }
        .gel-module-card .module-features { list-style: none; padding: 0; margin: 14px 0 0; }
        .gel-module-card .module-features li {
            font-size: 12.5px;
            color: var(--gel-text);
            padding: 5px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--gel-light2);
        }
        .gel-module-card .module-features li:last-child { border-bottom: none; }
        .gel-module-card .module-features li i { color: var(--gel-primary); font-size: 11px; }
        .gel-module-card .module-link {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12.5px; font-weight: 600;
            color: var(--gel-primary);
            text-decoration: none;
            margin-top: 12px;
            transition: gap var(--transition);
        }
        .gel-module-card .module-link:hover { gap: 10px; }

        /* CTA */
        .gel-cta-band { background: linear-gradient(135deg, var(--gel-primary) 0%, #ff9a3c 100%); padding: 60px 0; overflow: hidden; position: relative; }
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

        /* Modal Inscription */
        .gel-modal-overlay {
            display: none;
            position: fixed; inset: 0; z-index: 2000;
            background: rgba(15,23,42,0.6);
            backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
            padding: 20px;
        }
        .gel-modal-overlay.open { display: flex; }
        .gel-modal-box {
            background: var(--gel-white);
            border-radius: 16px;
            padding: 40px 36px 36px;
            max-width: 420px; width: 100%;
            text-align: center;
            box-shadow: 0 24px 80px rgba(0,0,0,0.2);
            animation: modalPop 0.3s ease;
            position: relative;
        }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.92) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .gel-modal-close {
            position: absolute; top: 14px; right: 18px;
            background: none; border: none;
            font-size: 20px; color: var(--gel-muted);
            cursor: pointer; transition: color var(--transition);
            padding: 4px;
        }
        .gel-modal-close:hover { color: var(--gel-dark); }
        .gel-modal-icon {
            width: 64px; height: 64px;
            background: var(--gel-primary-soft);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: var(--gel-primary);
        }
        .gel-modal-box h3 { font-size: 20px; font-weight: 800; color: var(--gel-dark); margin-bottom: 6px; }
        .gel-modal-box > p { font-size: 13.5px; color: var(--gel-muted); margin-bottom: 24px; line-height: 1.6; }
        .gel-modal-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 13px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; transition: all 0.3s; border: none; cursor: pointer; margin-bottom: 10px; }
        .gel-modal-btn-primary { background: var(--gel-primary); color: #fff; }
        .gel-modal-btn-primary:hover { background: var(--gel-primary-hov); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(255,121,0,0.3); }
        .gel-modal-btn-outline { background: transparent; color: var(--gel-text); border: 1.5px solid var(--gel-border); }
        .gel-modal-btn-outline:hover { border-color: var(--gel-primary); color: var(--gel-primary); }
        .gel-modal-divider {
            display: flex; align-items: center; gap: 12px;
            margin: 16px 0;
            color: var(--gel-muted);
            font-size: 11px; font-weight: 500;
            text-transform: uppercase;
        }
        .gel-modal-divider::before,
        .gel-modal-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gel-border);
        }

        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 80px 0 60px; } }
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
                    <a href="#" class="gel-nav-link active">
                        Nos Modules
                        <i class="bi-chevron-down chevron"></i>
                    </a>
                    <ul class="gel-dropdown">
                        <li><a href="#module-crm" style="background:var(--gel-primary-soft);color:var(--gel-primary);font-weight:600;"><span class="drop-icon"><i class="bi-people"></i></span> CRM Clients</a></li>
                        <li><a href="#module-ged"><span class="drop-icon"><i class="bi-folder2-open"></i></span> GED — Documents</a></li>
                        <li><a href="#module-poles"><span class="drop-icon"><i class="bi-diagram-3"></i></span> Pôles & Missions</a></li>
                        <li><hr class="gel-dropdown-divider"></li>
                        <li><a href="#module-compta"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                        <li><a href="#module-erp"><span class="drop-icon"><i class="bi-box-seam"></i></span> ERP Intégré</a></li>
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
                    <a href="#" class="gel-nav-link">
                        À propos
                        <i class="bi-chevron-down chevron"></i>
                    </a>
                    <ul class="gel-dropdown">
                        <li><a href="{{ route('notre-cabinet') }}"><span class="drop-icon"><i class="bi-building"></i></span> Notre Cabinet</a></li>
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
                <li class="gel-nav-item"><a href="/" class="gel-nav-link">Contact</a></li>
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
                    <a href="/register" class="gel-btn-nav gel-btn-nav-outline">
                        <i class="bi-person-plus"></i> S'inscrire
                    </a>
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
        <a href="{{ route('nos-modules') }}" class="gel-mobile-link" style="color:var(--gel-primary);font-weight:600;"><i class="bi-grid-3x3-gap text-orange me-2"></i>Nos Modules</a>
        <a href="/services" class="gel-mobile-link"><i class="bi-shop text-orange me-2"></i>Services</a>
        <a href="/blogue" class="gel-mobile-link"><i class="bi-pencil-square text-orange me-2"></i>Blogue</a>
        <a href="/documentation" class="gel-mobile-link"><i class="bi-file-text text-orange me-2"></i>Documentation</a>
        <a href="/faq" class="gel-mobile-link"><i class="bi-question-circle text-orange me-2"></i>FAQ</a>
        <a href="/centre-aide" class="gel-mobile-link"><i class="bi-headset text-orange me-2"></i>Centre d'aide</a>
        <a href="{{ route('notre-cabinet') }}" class="gel-mobile-link"><i class="bi-building text-orange me-2"></i>Notre Cabinet</a>
        <a href="{{ route('notre-equipe') }}" class="gel-mobile-link"><i class="bi-people-fill text-orange me-2"></i>Notre Équipe</a>
        <a href="{{ route('carrieres') }}" class="gel-mobile-link"><i class="bi-briefcase-fill text-orange me-2"></i>Carrières</a>
        <a href="#" class="gel-mobile-link"><i class="bi-currency-dollar text-orange me-2"></i>Tarifs</a>
        <a href="/" class="gel-mobile-link"><i class="bi-envelope text-orange me-2"></i>Contact</a>
        <a href="/login" class="gel-mobile-link"><i class="bi-box-arrow-in-right text-orange me-2"></i>Connexion</a>
        <a href="/register" class="gel-mobile-link"><i class="bi-person-plus text-orange me-2"></i>S'inscrire</a>
    </div>

    <!-- Page Header -->
    <div class="gel-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="gel-page-header-badge" style="display:none;"><i class="bi-grid-3x3-gap-fill"></i> Plateforme modulaire</div>
                    <h1>Nos Modules</h1>
                    <p>Cinq modules complémentaires pour couvrir l'intégralité des besoins de votre cabinet — CRM, GED, Pôles, Comptabilité et ERP — dans une seule plateforme unifiée.</p>
                </div>
            </div>
        </div>
    </div>


    <!-- Modules Grid -->
    <section class="gel-section">
        <div class="container">
            <div class="row g-4">

                <!-- CRM Clients -->
                <div class="col-lg-4 col-md-6 anim-fade-up" id="module-crm">
                    <div class="gel-module-card" style="border-top: 3px solid #FF7900;">
                        <div class="module-top">
                            <div class="module-icon orange"><i class="bi-people-fill"></i></div>
                            <div>
                                <h3>CRM Clients</h3>
                                <div class="module-sub">Gestion de la relation client</div>
                            </div>
                        </div>
                        <p>Centralisez toutes les informations de vos clients (particuliers, entreprises, associations) et accédez à un historique complet de vos interactions.</p>
                        <ul class="module-features">
                            <li><i class="bi-check-lg"></i> Fiches clients complètes avec données personnalisées</li>
                            <li><i class="bi-check-lg"></i> Historique des échanges et documents associés</li>
                            <li><i class="bi-check-lg"></i> Suivi des rendez-vous, appels et tâches</li>
                            <li><i class="bi-check-lg"></i> Segmentation par type de client et secteur</li>
                            <li><i class="bi-check-lg"></i> Export des données et mailing intégré</li>
                        </ul>
                        <a href="javascript:void(0)" onclick="openModal()" class="module-link">Accéder au module <i class="bi-arrow-right"></i></a>
                    </div>
                </div>

                <!-- GED -->
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1" id="module-ged">
                    <div class="gel-module-card" style="border-top: 3px solid #3B82F6;">
                        <div class="module-top">
                            <div class="module-icon blue"><i class="bi-folder2-open"></i></div>
                            <div>
                                <h3>GED — Documents</h3>
                                <div class="module-sub">Gestion électronique de documents</div>
                            </div>
                        </div>
                        <p>Stockez, organisez et retrouvez instantanément tous vos documents grâce à une indexation intelligente et des espaces de classement partagés.</p>
                        <ul class="module-features">
                            <li><i class="bi-check-lg"></i> Arborescence de dossiers personnalisable</li>
                            <li><i class="bi-check-lg"></i> Indexation multi-critères et recherche full-text</li>
                            <li><i class="bi-check-lg"></i> Versions des documents avec historique</li>
                            <li><i class="bi-check-lg"></i> Partage sécurisé par lien ou dossier</li>
                            <li><i class="bi-check-lg"></i> Signature électronique intégrée</li>
                        </ul>
                        <a href="javascript:void(0)" onclick="openModal()" class="module-link">Accéder au module <i class="bi-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Pôles & Missions -->
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2" id="module-poles">
                    <div class="gel-module-card" style="border-top: 3px solid #10B981;">
                        <div class="module-top">
                            <div class="module-icon green"><i class="bi-diagram-3"></i></div>
                            <div>
                                <h3>Pôles & Missions</h3>
                                <div class="module-sub">Organisation et suivi d'activité</div>
                            </div>
                        </div>
                        <p>Structurez votre cabinet en pôles d'expertise (Comptabilité, Juridique, Fiscal, Social) et suivez chaque mission de bout en bout.</p>
                        <ul class="module-features">
                            <li><i class="bi-check-lg"></i> Création de pôles et équipes dédiées</li>
                            <li><i class="bi-check-lg"></i> Missions avec étapes, deadlines et jalons</li>
                            <li><i class="bi-check-lg"></i> Tableaux de bord par pôle et par collaborateur</li>
                            <li><i class="bi-check-lg"></i> Workflows de validation automatisés</li>
                            <li><i class="bi-check-lg"></i> Rapports de productivité et facturation</li>
                        </ul>
                        <a href="javascript:void(0)" onclick="openModal()" class="module-link">Accéder au module <i class="bi-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Comptabilité -->
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1" id="module-compta">
                    <div class="gel-module-card" style="border-top: 3px solid #8B5CF6;">
                        <div class="module-top">
                            <div class="module-icon purple"><i class="bi-calculator-fill"></i></div>
                            <div>
                                <h3>Comptabilité</h3>
                                <div class="module-sub">Chaîne comptable complète</div>
                            </div>
                        </div>
                        <p>Gérez l'intégralité de la chaîne comptable — du journal au bilan — avec une automatisation poussée et un plan comptable personnalisable.</p>
                        <ul class="module-features">
                            <li><i class="bi-check-lg"></i> Plan comptable SYSCOA / OHADA personnalisable</li>
                            <li><i class="bi-check-lg"></i> Saisie des journaux et pièces comptables</li>
                            <li><i class="bi-check-lg"></i> Balance, grand livre, brouillard</li>
                            <li><i class="bi-check-lg"></i> Bilan et compte de résultat automatisés</li>
                            <li><i class="bi-check-lg"></i> Déclarations fiscales intégrées (TVA, BIC, IS)</li>
                        </ul>
                        <a href="javascript:void(0)" onclick="openModal()" class="module-link">Accéder au module <i class="bi-arrow-right"></i></a>
                    </div>
                </div>

                <!-- ERP Intégré -->
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2" id="module-erp">
                    <div class="gel-module-card" style="border-top: 3px solid #14B8A6;">
                        <div class="module-top">
                            <div class="module-icon teal"><i class="bi-box-seam-fill"></i></div>
                            <div>
                                <h3>ERP Intégré</h3>
                                <div class="module-sub">Gestion d'entreprise complète</div>
                            </div>
                        </div>
                        <p>Un ERP complet qui dépasse la gestion de cabinet : stocks, factures, RH, trésorerie et reporting — le tout synchronisé en temps réel.</p>
                        <ul class="module-features">
                            <li><i class="bi-check-lg"></i> Gestion des stocks et approvisionnements</li>
                            <li><i class="bi-check-lg"></i> Facturation et relances automatisées</li>
                            <li><i class="bi-check-lg"></i> RH & Paie : contrats, absences, fiches de paie</li>
                            <li><i class="bi-check-lg"></i> Trésorerie et rapprochement bancaire</li>
                            <li><i class="bi-check-lg"></i> Tableaux de bord financiers et KPI</li>
                        </ul>
                        <a href="javascript:void(0)" onclick="openModal()" class="module-link">Accéder au module <i class="bi-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Intégration — carte spéciale -->
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="gel-module-card" style="border: 2px dashed var(--gel-border); background: var(--gel-light);" onmouseover="this.style.borderColor='#FF7900';this.style.borderStyle='solid'" onmouseout="this.style.borderColor='var(--gel-border)';this.style.borderStyle='dashed'">
                        <div class="module-top">
                            <div class="module-icon" style="background:rgba(255,121,0,0.08);color:var(--gel-primary);font-size:22px;"><i class="bi-puzzle-fill"></i></div>
                            <div>
                                <h3>Tout est intégré</h3>
                                <div class="module-sub">Modules interconnectés</div>
                            </div>
                        </div>
                        <p>L'avantage unique de GEL Cabinet : tous les modules communiquent entre eux. Un dossier client créé dans le CRM est accessible depuis la GED, la Comptabilité et l'ERP — sans duplication.</p>
                        <ul class="module-features">
                            <li><i class="bi-check-lg"></i> Données synchronisées en temps réel</li>
                            <li><i class="bi-check-lg"></i> Interface unique et navigation fluide</li>
                            <li><i class="bi-check-lg"></i> Pas de double saisie ni d'incohérences</li>
                            <li><i class="bi-check-lg"></i> Mise à jour automatique de tous les modules</li>
                            <li><i class="bi-check-lg"></i> API ouverte pour intégrations tierces</li>
                        </ul>
                        <a href="javascript:void(0)" onclick="openModal()" class="module-link">Démarrez maintenant <i class="bi-arrow-right"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="gel-section gel-section-alt">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <div class="gel-section-chip anim-fade-up"><i class="bi-lightning-charge-fill"></i> Pourquoi une plateforme intégrée ?</div>
                    <h2 class="gel-section-title anim-fade-up delay-1">Unifier, c'est gagner</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Chaque module est puissant seul. Ensemble, ils transforment votre façon de travailler.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-module-card" style="text-align:center; padding:36px 24px;">
                        <div style="font-size:36px; color:var(--gel-primary); margin-bottom:12px;"><i class="bi-clock-fill"></i></div>
                        <h3 style="font-size:17px;">Gain de temps</h3>
                        <p style="font-size:13px; color:var(--gel-muted); margin:8px 0 0;">Finies les doubles saisies. Un seul outil pour tout gérer, c'est jusqu'à 40% de temps gagné sur l'administratif.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-module-card" style="text-align:center; padding:36px 24px;">
                        <div style="font-size:36px; color:var(--gel-primary); margin-bottom:12px;"><i class="bi-shield-fill-check"></i></div>
                        <h3 style="font-size:17px;">Fiabilité</h3>
                        <p style="font-size:13px; color:var(--gel-muted); margin:8px 0 0;">Toutes vos données sont centralisées et cohérentes. Plus de risque d'erreur entre deux logiciels séparés.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-module-card" style="text-align:center; padding:36px 24px;">
                        <div style="font-size:36px; color:var(--gel-primary); margin-bottom:12px;"><i class="bi-graph-up-arrow"></i></div>
                        <h3 style="font-size:17px;">Visibilité globale</h3>
                        <p style="font-size:13px; color:var(--gel-muted); margin:8px 0 0;">Un tableau de bord unique qui vous donne une vue d'ensemble sur l'activité complète de votre cabinet.</p>
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
                    <h2>Prêt à découvrir tous nos modules en action ?</h2>
                    <p>Inscrivez-vous gratuitement ou demandez une démonstration personnalisée.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 anim-fade-up delay-1">
                    <a href="/register" class="gel-btn-white me-2"><i class="bi-person-plus"></i> Créer un compte</a>
                    <a href="/login" class="gel-btn-white" style="background:rgba(255,255,255,0.15);color:#fff;border:2px solid rgba(255,255,255,0.5);margin-top:8px;display:inline-flex;"><i class="bi-box-arrow-in-right"></i> Connexion</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════════
         MODAL INSCRIPTION / CONNEXION
    ════════════════════════════════════════════════════ -->
    <div class="gel-modal-overlay" id="authModal">
        <div class="gel-modal-box">
            <button class="gel-modal-close" onclick="closeModal()" aria-label="Fermer">&times;</button>
            <div class="gel-modal-icon"><i class="bi-person-plus-fill"></i></div>
            <h3>Accédez à tous les modules</h3>
            <p>Créez un compte gratuitement pour découvrir l'ensemble des fonctionnalités de GEL Cabinet.</p>
            <a href="/register" class="gel-modal-btn gel-modal-btn-primary">
                <i class="bi-person-plus"></i> Créer un compte
            </a>
            <div class="gel-modal-divider">ou</div>
            <a href="/login" class="gel-modal-btn gel-modal-btn-outline">
                <i class="bi-box-arrow-in-right"></i> Se connecter
            </a>
            <p style="font-size:11px; color:var(--gel-muted); margin-top:14px;">Gratuit — Sans engagement — 1 clic</p>
        </div>
    </div>

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
                    <div class="gel-footer-heading">Modules</div>
                    <ul class="gel-footer-links">
                        <li><a href="{{ route('nos-modules') }}#module-crm">CRM Clients</a></li>
                        <li><a href="{{ route('nos-modules') }}#module-ged">GED</a></li>
                        <li><a href="{{ route('nos-modules') }}#module-poles">Pôles & Missions</a></li>
                        <li><a href="{{ route('nos-modules') }}#module-compta">Comptabilité</a></li>
                        <li><a href="{{ route('nos-modules') }}#module-erp">ERP Intégré</a></li>
                    </ul>
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

    <script>
        // Modal Inscription/Connexion
        function openModal() {
            document.getElementById('authModal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('authModal').classList.remove('open');
            document.body.style.overflow = '';
        }
        document.getElementById('authModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

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