<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nos Services | GEL Cabinet</title>
    <meta name="description" content="Services GEL Cabinet : Comptabilité, Juridique, Fiscal et Social & Paie pour la gestion de votre cabinet.">

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
        body { font-family: var(--font-body); background: var(--gel-white); color: var(--gel-text); line-height: 1.6; }
        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading); font-weight: 700; }

        .anim-fade-up { opacity: 0; transform: translateY(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-fade-left { opacity: 0; transform: translateX(-36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-fade-right { opacity: 0; transform: translateX(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-visible { opacity: 1 !important; transform: none !important; }
        .delay-1 { transition-delay: 0.1s !important; }
        .delay-2 { transition-delay: 0.2s !important; }
        .delay-3 { transition-delay: 0.3s !important; }
        .delay-4 { transition-delay: 0.4s !important; }

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
        @keyframes gelGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gel-page-header::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(var(--gel-svc-rgb),0.12) 0%, transparent 70%); border-radius: 50%; animation: gelFloatA 8s ease-in-out infinite; }
        .gel-page-header::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
        @keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
        @keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(2.2rem, 4.5vw, 3.2rem); font-weight: 900; color: #fff; letter-spacing: -1px; position: relative; z-index: 1; }
        .gel-page-header p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 620px; margin-top: 12px; line-height: 1.7; position: relative; z-index: 1; }
        .gel-page-header-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); padding: 5px 14px; border-radius: 100px; font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; position: relative; z-index: 1; }

        /* Sections */
        .gel-section { padding: 80px 0; }
        .gel-section-alt { background: var(--gel-light); }
        .gel-section-chip { display: inline-flex; align-items: center; gap: 6px; background: var(--gel-white); color: var(--gel-primary); font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px; border: 1.5px solid rgba(255,121,0,0.2); }
        .gel-section-title { font-family: var(--font-heading); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--gel-dark); letter-spacing: -0.5px; line-height: 1.2; margin-bottom: 14px; }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); max-width: 540px; line-height: 1.7; }

        /* Service Cards (overview) */
        .gel-svc-card {
            background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 16px;
            padding: 36px 28px 32px; height: 100%;
            transition: transform var(--transition), box-shadow var(--transition);
            position: relative; overflow: hidden;
            text-decoration: none; color: inherit; display: block;
        }
        .gel-svc-card:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); color: inherit; }
        .gel-svc-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: var(--svc-color);
        }
        .gel-svc-card-icon {
            width: 52px; height: 52px; border-radius: 12px;
            background: var(--svc-light);
            display: flex; align-items: center; justify-content: center;
            color: var(--svc-color); font-size: 24px;
            margin-bottom: 16px;
            transition: background var(--transition), color var(--transition), transform var(--transition);
        }
        .gel-svc-card:hover .gel-svc-card-icon { background: var(--svc-color); color: #fff; transform: scale(1.05); }
        .gel-svc-card h4 { font-size: 17px; font-weight: 800; color: var(--gel-dark); margin-bottom: 8px; }
        .gel-svc-card p { font-size: 13.5px; color: var(--gel-muted); line-height: 1.6; margin-bottom: 14px; }
        .gel-svc-card-link { font-size: 12.5px; font-weight: 700; color: var(--svc-color); display: inline-flex; align-items: center; gap: 5px; }
        .gel-svc-card:hover .gel-svc-card-link i { transform: translateX(3px); }
        .gel-svc-card-link i { transition: transform var(--transition); }
        .gel-svc-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: auto; }
        .gel-svc-tag { font-size: 10.5px; font-weight: 600; padding: 3px 10px; border-radius: 100px; background: var(--svc-light); color: var(--svc-color); }

        /* Stats */
        .gel-stats-row { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; margin-top: 40px; }
        .gel-stat-item { text-align: center; }
        .gel-stat-num { font-family: var(--font-heading); font-size: 28px; font-weight: 900; color: #fff; }
        .gel-stat-label { font-size: 12px; color: rgba(255,255,255,0.5); font-weight: 500; margin-top: 2px; }

        /* Feature Cards (for "Pourquoi choisir" section) */
        .gel-feature-card { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 12px; padding: 28px 24px; height: 100%; transition: transform var(--transition), box-shadow var(--transition); }
        .gel-feature-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .gel-feature-icon { width: 44px; height: 44px; background: var(--gel-light2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 14px; }
        .gel-feature-card h5 { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
        .gel-feature-card p { font-size: 13px; color: var(--gel-muted); line-height: 1.6; margin: 0; }

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

        /* Modal */
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

        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 80px 0 60px; } .gel-stats-row { gap: 24px; } }

        /* Domaines couverts */
        .gel-domain-group { margin-bottom: 32px; }
        .gel-domain-header {
            display: flex; align-items: center; gap: 10px;
            font-family: var(--font-heading); font-size: 16px; font-weight: 700;
            color: var(--gel-primary); margin-bottom: 14px;
            padding: 10px 16px; background: var(--gel-primary-soft);
            border-radius: 6px; border-left: 3px solid var(--gel-primary);
        }
        .gel-domain-header i { font-size: 18px; }
        .gel-module-tag {
            display: inline-flex; align-items: center; gap: 5px;
            background: var(--gel-white); border: 1px solid var(--gel-border);
            border-radius: 100px; padding: 4px 14px; font-size: 12px;
            color: var(--gel-text); margin: 0 6px 8px 0;
            transition: border-color var(--transition), background var(--transition);
        }
        .gel-module-tag:hover { border-color: var(--gel-primary); background: var(--gel-primary-soft); }
        .gel-module-tag i { color: var(--gel-primary); font-size: 10px; }
        .gel-domain-stats { display: flex; justify-content: center; gap: 28px; flex-wrap: wrap; margin-top: 28px; }
        .gel-domain-stat-num { font-family: var(--font-heading); font-size: 28px; font-weight: 900; color: #163A5E; display: block; }
        .gel-domain-stat-lbl { font-size: 11px; color: var(--gel-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .gel-domain-stat-item { text-align: center; }
        .gel-section-sub.wide { max-width: 720px; }

        /* Opportunités */
        .gel-opp-card { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 12px; padding: 24px 18px; height: 100%; transition: all var(--transition); text-align: center; }
        .gel-opp-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: transparent; }
        .gel-opp-icon { width: 48px; height: 48px; background: var(--gel-primary-soft); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; color: var(--gel-primary); font-size: 22px; transition: all var(--transition); }
        .gel-opp-card:hover .gel-opp-icon { background: var(--gel-primary); color: #fff; }
        .gel-opp-card h5 { font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #163A5E; }
        .gel-opp-card p { font-size: 12.5px; color: var(--gel-muted); line-height: 1.6; margin: 0; }
    </style>
</head>
<body>

    @include('partials.navbar')

    <!-- header -->
    <div class="gel-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="gel-page-header-badge"><i class="bi-grid-3x3-gap-fill"></i> Services</div>
                    <h1>Nos Services</h1>
                    <p>Comptabilité, juridique, fiscal, social — on couvre tous les pôles de votre cabinet.</p>
                </div>
            </div>
            <div class="gel-stats-row">
                <div class="gel-stat-item anim-fade-up delay-1">
                    <div class="gel-stat-num">4</div>
                    <div class="gel-stat-label">Services experts</div>
                </div>
                <div class="gel-stat-item anim-fade-up delay-2">
                    <div class="gel-stat-num">24/7</div>
                    <div class="gel-stat-label">Disponibilité</div>
                </div>
                <div class="gel-stat-item anim-fade-up delay-3">
                    <div class="gel-stat-num">100%</div>
                    <div class="gel-stat-label">Cloud & Sécurisé</div>
                </div>
                <div class="gel-stat-item anim-fade-up delay-4">
                    <div class="gel-stat-num">1</div>
                    <div class="gel-stat-label">Plateforme unique</div>
                </div>
            </div>
        </div>
    </div>

    <!-- services grid -->
    <section class="gel-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <div class="gel-section-chip"><i class="bi-grid-3x3-gap-fill"></i> Expertise métier</div>
                    <h2 class="gel-section-title anim-fade-up">Des services adaptés à votre cabinet</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-1">Des services adaptés à votre cabinet, pour travailler mieux et plus vite.</p>
                </div>
            </div>
            <div class="row g-4">

                <!-- Comptabilité -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                    <a href="/services/comptabilite" class="gel-svc-card" style="--svc-color:#FF7900;--svc-light:rgba(255,121,0,0.08);">
                        <div class="gel-svc-card-icon"><i class="bi-calculator"></i></div>
                        <h4>Comptabilité</h4>
                        <p>Plan comptable SYSCOA/OHADA, journaux, balance, bilan, déclarations fiscales et rapprochement bancaire.</p>
                        <div class="gel-svc-tags">
                            <span class="gel-svc-tag">SYSCOA</span>
                            <span class="gel-svc-tag">Bilan</span>
                            <span class="gel-svc-tag">TVA</span>
                        </div>
                        <div style="margin-top:14px;" class="gel-svc-card-link">Découvrir <i class="bi-arrow-right"></i></div>
                    </a>
                </div>

                <!-- Juridique -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                    <a href="/services/juridique" class="gel-svc-card" style="--svc-color:#3B82F6;--svc-light:rgba(59,130,246,0.08);">
                        <div class="gel-svc-card-icon"><i class="bi-bank2"></i></div>
                        <h4>Juridique</h4>
                        <p>Constitution de sociétés, veille juridique, rédaction de contrats, formalités légales et gestion documentaire.</p>
                        <div class="gel-svc-tags">
                            <span class="gel-svc-tag">Contrats</span>
                            <span class="gel-svc-tag">Sociétés</span>
                            <span class="gel-svc-tag">Veille</span>
                        </div>
                        <div style="margin-top:14px;" class="gel-svc-card-link">Découvrir <i class="bi-arrow-right"></i></div>
                    </a>
                </div>

                <!-- Fiscal -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                    <a href="/services/fiscal" class="gel-svc-card" style="--svc-color:#8B5CF6;--svc-light:rgba(139,92,246,0.08);">
                        <div class="gel-svc-card-icon"><i class="bi-receipt"></i></div>
                        <h4>Fiscal</h4>
                        <p>Déclarations TVA, BIC, IS, IRPP, optimisation fiscale, échéances & rappels et contrôle fiscal.</p>
                        <div class="gel-svc-tags">
                            <span class="gel-svc-tag">TVA</span>
                            <span class="gel-svc-tag">IS</span>
                            <span class="gel-svc-tag">IRPP</span>
                        </div>
                        <div style="margin-top:14px;" class="gel-svc-card-link">Découvrir <i class="bi-arrow-right"></i></div>
                    </a>
                </div>

                <!-- Social & Paie -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-4">
                    <a href="/services/social-paie" class="gel-svc-card" style="--svc-color:#10B981;--svc-light:rgba(16,185,129,0.08);">
                        <div class="gel-svc-card-icon"><i class="bi-people"></i></div>
                        <h4>Social & Paie</h4>
                        <p>Gestion de la paie, déclarations sociales, contrats de travail, absences, congés et tableaux de bord sociaux.</p>
                        <div class="gel-svc-tags">
                            <span class="gel-svc-tag">Paie</span>
                            <span class="gel-svc-tag">Déclarations</span>
                            <span class="gel-svc-tag">Congés</span>
                        </div>
                        <div style="margin-top:14px;" class="gel-svc-card-link">Découvrir <i class="bi-arrow-right"></i></div>
                    </a>
                </div>

                <!-- Logiciel Comptabilité (ERP) -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-4">
                    <a href="/logiciel-comptabilite" class="gel-svc-card" style="--svc-color:#163A5E;--svc-light:rgba(22,58,94,0.08);">
                        <div class="gel-svc-card-icon"><i class="bi-cpu"></i></div>
                        <h4>Logiciel Comptabilité</h4>
                        <p>ERP complet avec 35+ modules : comptabilité, stock, ventes, paie, caisse, Mobile Money, hôtel, scolaire et plus.</p>
                        <div class="gel-svc-tags">
                            <span class="gel-svc-tag">OHADA/SYSCOA</span>
                            <span class="gel-svc-tag">35+ modules</span>
                            <span class="gel-svc-tag">Licence</span>
                        </div>
                        <div style="margin-top:14px;" class="gel-svc-card-link">Découvrir <i class="bi-arrow-right"></i></div>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- domaines couverts (Logiciel Comptabilité) -->
    <section class="gel-section" style="background:linear-gradient(135deg,#F8FAFC 0%,#EFF6FF 100%);">
        <div class="container">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-8">
                    <span class="gel-section-chip"><i class="bi-cpu"></i> Domaines couverts</span>
                    <h2 class="gel-section-title">Un ERP complet pour votre cabinet</h2>
                    <p class="gel-section-sub wide mx-auto">Au-delà de la comptabilité, <strong>GEL Comptabilité</strong> couvre <strong>plus de 35 modules métier</strong> répartis en 7 domaines. Une solution tout-en-un qui centralise l'ensemble des opérations.</p>
                    <div class="gel-domain-stats">
                        <div class="gel-domain-stat-item"><span class="gel-domain-stat-num">35+</span><span class="gel-domain-stat-lbl">Modules</span></div>
                        <div class="gel-domain-stat-item"><span class="gel-domain-stat-num">7</span><span class="gel-domain-stat-lbl">Domaines</span></div>
                        <div class="gel-domain-stat-item"><span class="gel-domain-stat-num">10</span><span class="gel-domain-stat-lbl">Profils</span></div>
                        <div class="gel-domain-stat-item"><span class="gel-domain-stat-num">5</span><span class="gel-domain-stat-lbl">Bases dédiées</span></div>
                    </div>
                </div>
            </div>

            <!-- Domaine 1 : Comptabilité & Finance -->
            <div class="gel-domain-group anim-fade-up delay-1">
                <div class="gel-domain-header"><i class="bi-layout-text-window"></i> Comptabilité &amp; Finance</div>
                <div>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Plan comptable SYSCOA/OHADA</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Saisie journaux (ACH/VTE/BQ/CAISSE/OD)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Balance &amp; Grand Livre</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Bilan actif/passif</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Compte de résultat</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Déclarations fiscales (TVA, AIB, TS)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Gestion exercices (ouverture/clôture)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Finances &amp; ratios</span>
                </div>
            </div>

            <!-- Domaine 2 : Ventes & Facturation -->
            <div class="gel-domain-group anim-fade-up delay-2">
                <div class="gel-domain-header"><i class="bi-receipt"></i> Ventes &amp; Facturation</div>
                <div>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Facture normale &amp; prestation</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Facture rapide (caisse)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Facture normalisée OHADA</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Commande client &amp; provisoire</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Bordereau de livraison</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Cotation &amp; grille tarifaire</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Livraison totale/partielle</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Impression multi-format (Ticket/A4/A5/A6)</span>
                </div>
            </div>

            <!-- Domaine 3 : Stock & Achats -->
            <div class="gel-domain-group anim-fade-up delay-3">
                <div class="gel-domain-header"><i class="bi-box-seam"></i> Stock, Achats &amp; Logistique</div>
                <div>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Gestion des produits &amp; articles</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Familles, catégories, départements</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Arrivage &amp; réception fournisseur</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Inventaire &amp; ajustement</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Stock actuel, détaillé, final</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Tarifs &amp; grilles par client/catégorie</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Code-barres &amp; scan produit</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Pertes, avaries &amp; écarts</span>
                </div>
            </div>

            <!-- Domaine 4 : Trésorerie -->
            <div class="gel-domain-group anim-fade-up delay-1">
                <div class="gel-domain-header"><i class="bi-cash-stack"></i> Trésorerie &amp; Paiements</div>
                <div>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Caisse &amp; point caisse</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Billetage (coupures 50 à 10 000 FCFA)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Situation caisse journalière/principale</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Mobile Money (MTN MoMo, Moov Money)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Export MCF (formats 31/33/38/C1)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Virements bancaires</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Transfert d'argent inter-caisse</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Chèques &amp; espèces</span>
                </div>
            </div>

            <!-- Domaine 5 : Paie & RH -->
            <div class="gel-domain-group anim-fade-up delay-2">
                <div class="gel-domain-header"><i class="bi-people"></i> Paie &amp; Ressources Humaines</div>
                <div>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Gestion des employés (personnel)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Paramètres salaire (SMIC, CNSS, IRPP)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Bulletins de paie individuels</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Charges sociales &amp; allocations</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Édition A4/A5/A6</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Historique des salaires</span>
                </div>
            </div>

            <!-- Domaine 6 : Modules spécialisés -->
            <div class="gel-domain-group anim-fade-up delay-3">
                <div class="gel-domain-header"><i class="bi-building"></i> Modules spécialisés</div>
                <div>
                    <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Hôtel</strong> &mdash; Chambres, réservations, taxe nuitée</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Location</strong> &mdash; Biens, locataires, quittances, loyers</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Scolaire</strong> &mdash; Classes, élèves, notes, bulletins</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Tontine</strong> &mdash; Types, adhérents, cotisations, échéancier</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Transport</strong> &mdash; Camions, tournées, charges</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Pressing</strong> &mdash; Commandes, bon de livraison</span>
                </div>
            </div>

            <!-- Domaine 7 : Administration -->
            <div class="gel-domain-group anim-fade-up delay-1">
                <div class="gel-domain-header"><i class="bi-gear"></i> Administration &amp; Sécurité</div>
                <div>
                    <span class="gel-module-tag"><i class="bi-check2"></i> 10 profils utilisateurs (PDG à caissier)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Permissions individuelles &amp; menus</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Sauvegarde &amp; restauration automatique</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Licence &amp; activation (30 jours d'essai)</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Multi-sociétés &amp; dossiers</span>
                    <span class="gel-module-tag"><i class="bi-check2"></i> Base de données sécurisée</span>
                </div>
            </div>

            <div class="text-center mt-4 anim-fade-up delay-2">
                <a href="/logiciel-comptabilite" class="gel-btn-nav gel-btn-nav-primary" style="padding:12px 32px;font-size:14px;"><i class="bi-cpu"></i> Découvrir tous les modules</a>
            </div>
        </div>
    </section>

    <!-- opportunités par secteur -->
    <section class="gel-section">
        <div class="container">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-8">
                    <span class="gel-section-chip"><i class="bi-stars"></i> Opportunités par secteur</span>
                    <h2 class="gel-section-title">Une solution pour chaque métier</h2>
                    <p class="gel-section-sub wide mx-auto">Grâce à ses modules spécialisés, GEL Comptabilité s'adapte à une grande variété de secteurs d'activité.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                    <div class="gel-opp-card">
                        <div class="gel-opp-icon"><i class="bi-building"></i></div>
                        <h5>Hôtels &amp; Résidences</h5>
                        <p>Gestion complète des chambres, réservations, taxe de nuitée, minibar et facturation clients.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                    <div class="gel-opp-card">
                        <div class="gel-opp-icon"><i class="bi-mortarboard"></i></div>
                        <h5>Établissements scolaires</h5>
                        <p>Années scolaires, classes, matières, saisie des notes, bulletins, frais scolaires et suivi des élèves.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                    <div class="gel-opp-card">
                        <div class="gel-opp-icon"><i class="bi-house-door"></i></div>
                        <h5>Agences immobilières</h5>
                        <p>Gestion des biens, locataires, quittances de loyer, avances/cautions et relevés propriétaires.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                    <div class="gel-opp-card">
                        <div class="gel-opp-icon"><i class="bi-truck"></i></div>
                        <h5>Transport &amp; Transit</h5>
                        <p>Gestion des camions, tournées, charges transport, dossiers de transit et suivi logistique.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                    <div class="gel-opp-card">
                        <div class="gel-opp-icon"><i class="bi-people"></i></div>
                        <h5>Associations &amp; Tontines</h5>
                        <p>Types de tontine, adhérents, comptes, cotisations, échéancier et attribution automatique.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                    <div class="gel-opp-card">
                        <div class="gel-opp-icon"><i class="bi-shop"></i></div>
                        <h5>Commerces &amp; Distribution</h5>
                        <p>Gestion des ventes, facturation, commandes, encaissements Mobile Money et suivi des impayés.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- why -->
    <section class="gel-section gel-section-alt">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <div class="gel-section-chip"><i class="bi-star"></i> Avantages</div>
                    <h2 class="gel-section-title anim-fade-up">Ce qu'on vous propose</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-1">Comptabilité, juridique, fiscal, social — on couvre tous les pôles de votre cabinet.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-feature-card" style="border-color:rgba(255,121,0,0.2);">
                        <div class="gel-feature-icon" style="color:#FF7900;"><i class="bi-phone"></i></div>
                        <h5>Plateforme 100% Cloud</h5>
                        <p>Accédez à vos données depuis n'importe où, à tout moment, sans installation. Mise à jour automatique.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-feature-card" style="border-color:rgba(59,130,246,0.2);">
                        <div class="gel-feature-icon" style="color:#3B82F6;"><i class="bi-shield-check"></i></div>
                        <h5>Sécurité & Conformité</h5>
                        <p>Données chiffrées, sauvegarde automatisée, conformité RGPD et hébergement sécurisé.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-feature-card" style="border-color:rgba(139,92,246,0.2);">
                        <div class="gel-feature-icon" style="color:#8B5CF6;"><i class="bi-arrow-repeat"></i></div>
                        <h5>Modules Intégrés</h5>
                        <p>Tous vos services communiquent entre eux : la comptabilité alimente le fiscal, les RH alimentent la paie.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- cta -->
    <section class="gel-cta-band">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 anim-fade-up">
                    <h2>Prêt à transformer votre cabinet ?</h2>
                    <p>Créez un compte et testez tous nos services.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 anim-fade-up delay-1">
                    <a href="javascript:void(0)" onclick="openModal()" class="gel-btn-white me-2"><i class="bi-person-plus"></i> Créer un compte</a>
                    <a href="/login" class="gel-btn-white" style="background:rgba(255,255,255,0.15);color:#fff;border:2px solid rgba(255,255,255,0.5);display:inline-flex;margin-top:6px;"><i class="bi-box-arrow-in-right"></i> Connexion</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="gel-modal-overlay" id="authModal">
        <div class="gel-modal-box">
            <button class="gel-modal-close" onclick="closeModal()">&times;</button>
            <div class="gel-modal-icon"><i class="bi-person-plus-fill"></i></div>
            <h3>Accédez à nos services</h3>
            <p>Créez un compte gratuitement pour découvrir nos services et modules.</p>
            <a href="/register" class="gel-modal-btn gel-modal-btn-primary"><i class="bi-person-plus"></i> Créer un compte</a>
            <div class="gel-modal-divider">ou</div>
            <a href="/login" class="gel-modal-btn gel-modal-btn-outline"><i class="bi-box-arrow-in-right"></i> Se connecter</a>
            <p style="font-size:11px; color:var(--gel-muted); margin-top:14px;">Gratuit — Sans engagement — 1 clic</p>
        </div>
    </div>

    @include('partials.footer')

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
    </script>
</body>
</html>
