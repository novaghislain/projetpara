<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact | GEL Cabinet</title>
    <meta name="description" content="Contactez GEL Cabinet : Cotonou, Bénin. Téléphone, email, formulaire de contact et plan d'accès.">

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
        .gel-page-header::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(var(--gel-svc-rgb),0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
        @keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
        @keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(2.2rem, 4.5vw, 3.2rem); font-weight: 900; color: #fff; letter-spacing: -1px; position: relative; z-index: 1; }
        .gel-page-header p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 620px; margin-top: 12px; line-height: 1.7; position: relative; z-index: 1; }
        .gel-page-header-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); padding: 5px 14px; border-radius: 100px; font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; position: relative; z-index: 1; }

        /* Sections */
        .gel-section { padding: 80px 0; }
        .gel-section-alt { background: var(--gel-light); }
        .gel-section-title { font-family: var(--font-heading); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--gel-dark); letter-spacing: -0.5px; line-height: 1.2; margin-bottom: 14px; }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); max-width: 540px; line-height: 1.7; }

        /* Contact Cards */
        .gel-contact-card {
            background: var(--gel-white);
            border: 1px solid var(--gel-border);
            border-radius: 12px;
            padding: 28px 24px;
            height: 100%;
            text-align: center;
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        }
        .gel-contact-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--gel-primary);
        }
        .gel-contact-card-icon {
            width: 52px; height: 52px;
            background: var(--gel-primary-soft);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            color: var(--gel-primary);
            font-size: 22px;
            transition: background var(--transition), color var(--transition);
        }
        .gel-contact-card:hover .gel-contact-card-icon { background: var(--gel-primary); color: #fff; }
        .gel-contact-card h5 { font-size: 14px; font-weight: 700; color: var(--gel-dark); margin-bottom: 8px; }
        .gel-contact-card p { font-size: 14px; color: var(--gel-text); margin: 2px 0; line-height: 1.5; }
        .gel-contact-card small { font-size: 12px; color: var(--gel-muted); }

        /* Form */
        .gel-form-wrap { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 12px; padding: 36px; height: 100%; }
        .gel-form-title { font-size: 20px; font-weight: 800; margin-bottom: 6px; }
        .gel-form-sub { font-size: 13px; color: var(--gel-muted); margin-bottom: 28px; }
        .gel-form-label { font-size: 12px; font-weight: 600; color: var(--gel-text); margin-bottom: 6px; display: block; }
        .gel-form-control { width: 100%; border: 1.5px solid var(--gel-border); border-radius: var(--radius); padding: 11px 14px; font-size: 13.5px; font-family: var(--font-body); color: var(--gel-text); background: var(--gel-white); transition: border-color 0.2s, box-shadow 0.2s; outline: none; }
        .gel-form-control:focus { border-color: var(--gel-primary); box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
        .gel-form-control::placeholder { color: #c0cbd8; }
        textarea.gel-form-control { resize: vertical; min-height: 120px; }
        .gel-btn-submit { width: 100%; background: var(--gel-primary); color: #fff; font-size: 14px; font-weight: 700; padding: 13px; border: none; border-radius: var(--radius); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; margin-top: 8px; }
        .gel-btn-submit:hover { background: var(--gel-primary-hov); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(255,121,0,0.35); }

        /* Map */
        .gel-map-wrap {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--gel-border);
            box-shadow: var(--shadow-sm);
            height: 100%;
            min-height: 400px;
        }
        .gel-map-wrap iframe {
            width: 100%;
            height: 100%;
            min-height: 400px;
            display: block;
            border: none;
        }

        /* Satisfaction bar */
        .gel-satisfaction {
            background: var(--gel-light);
            border: 1px solid var(--gel-border);
            border-radius: 12px;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .gel-satisfaction-stars { color: #f59e0b; font-size: 18px; letter-spacing: 2px; white-space: nowrap; }
        .gel-satisfaction p { font-size: 13px; color: var(--gel-muted); margin: 0; }

        /* Social sidebar */
        .gel-social-sidebar {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            background: var(--gel-white);
            border: 1px solid var(--gel-border);
            border-radius: 0 12px 12px 0;
            padding: 12px 10px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 900;
            box-shadow: var(--shadow-md);
        }
        .gel-social-sidebar a {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--gel-muted);
            text-decoration: none;
            font-size: 16px;
            transition: all var(--transition);
        }
        .gel-social-sidebar a:hover { background: var(--gel-primary); color: #fff; transform: scale(1.1); }
        @media (max-width: 767px) { .gel-social-sidebar { display: none; } }

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

        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 80px 0 60px; } .gel-form-wrap { padding: 24px 20px; } }
    </style>
</head>
<body>

    <!-- Social Sidebar -->
    <div class="gel-social-sidebar">
        <a href="#" aria-label="WhatsApp"><i class="bi-whatsapp"></i></a>
        <a href="#" aria-label="Facebook"><i class="bi-facebook"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="bi-linkedin"></i></a>
        <a href="#" aria-label="Twitter"><i class="bi-twitter-x"></i></a>
        <a href="tel:+22900000000" aria-label="Téléphone"><i class="bi-telephone-fill"></i></a>
    </div>

    @include('partials.navbar')
    <!-- Page Header -->
    <div class="gel-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="gel-page-header-badge"><i class="bi-envelope-fill"></i> Contact</div>
                    <h1>Contactez-nous</h1>
                    <p>Une question ? Un besoin spécifique ? Notre équipe est à votre écoute. Laissez-nous un message et nous vous répondrons sous 24h.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Cards -->
    <section class="gel-section" style="padding-bottom:0;">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 anim-fade-up delay-1">
                    <div class="gel-contact-card">
                        <div class="gel-contact-card-icon"><i class="bi-geo-alt-fill"></i></div>
                        <h5>Adresse</h5>
                        <p>Cotonou, Bénin</p>
                        <small>Zone des Ambassades</small>
                    </div>
                </div>
                <div class="col-md-3 anim-fade-up delay-2">
                    <div class="gel-contact-card">
                        <div class="gel-contact-card-icon"><i class="bi-telephone-fill"></i></div>
                        <h5>Téléphone</h5>
                        <p>+229 XX XX XX XX</p>
                        <small>Lun–Ven 8h–18h</small>
                    </div>
                </div>
                <div class="col-md-3 anim-fade-up delay-3">
                    <div class="gel-contact-card">
                        <div class="gel-contact-card-icon"><i class="bi-envelope-fill"></i></div>
                        <h5>Email</h5>
                        <p>contact@gelcabinet.com</p>
                        <small>Réponse sous 24h</small>
                    </div>
                </div>
                <div class="col-md-3 anim-fade-up delay-4">
                    <div class="gel-contact-card">
                        <div class="gel-contact-card-icon"><i class="bi-clock-fill"></i></div>
                        <h5>Horaires</h5>
                        <p>Lun–Ven : 8h–18h</p>
                        <small>Sam : 9h–13h</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map + Form -->
    <section class="gel-section">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <!-- Map -->
                <div class="col-lg-6 anim-fade-left">
                    <div class="gel-map-wrap">
                        <iframe
                            src="https://www.openstreetmap.org/export/embed.html?bbox=2.317%2C6.343%2C2.419%2C6.395&layer=mapnik&marker=6.369%2C2.368"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Plan d'accès GEL Cabinet - Cotonou, Bénin">
                        </iframe>
                    </div>
                    <div class="gel-satisfaction mt-3">
                        <div class="gel-satisfaction-stars">★★★★★</div>
                        <p><strong>4.9 / 5</strong> — Satisfaction client basée sur 150+ avis</p>
                    </div>
                </div>

                <!-- Form -->
                <div class="col-lg-6 anim-fade-right">
                    <div class="gel-form-wrap">
                        <div class="gel-form-title">Envoyez-nous un message</div>
                        <div class="gel-form-sub">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</div>

                        @if(session('success'))
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:6px; padding:12px 16px; font-size:13px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                                <i class="bi-check-circle-fill"></i> {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="gel-form-label">Nom complet *</label>
                                    <input type="text" name="name" class="gel-form-control" required placeholder="Votre nom">
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Email *</label>
                                    <input type="email" name="email" class="gel-form-control" required placeholder="votre@email.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Téléphone</label>
                                    <input type="tel" name="phone" class="gel-form-control" placeholder="+229 XX XX XX XX">
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Sujet *</label>
                                    <select name="subject" class="gel-form-control" required>
                                        <option value="">Choisir un sujet</option>
                                        <option>Demande d'information</option>
                                        <option>Assistance technique</option>
                                        <option>Proposition commerciale</option>
                                        <option>Réclamation</option>
                                        <option>Autre</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="gel-form-label">Message *</label>
                                    <textarea name="message" class="gel-form-control" rows="5" required placeholder="Décrivez votre demande en quelques lignes..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="gel-btn-submit">
                                        <i class="bi-send-fill"></i> Envoyer le message
                                    </button>
                                </div>
                            </div>
                        </form>
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
                    <h2>Prêt à rejoindre GEL Cabinet ?</h2>
                    <p>Créez un compte gratuitement et découvrez tous nos modules.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 anim-fade-up delay-1">
                    <a href="/register" class="gel-btn-white me-2"><i class="bi-person-plus"></i> Créer un compte</a>
                    <a href="/login" class="gel-btn-white" style="background:rgba(255,255,255,0.15);color:#fff;border:2px solid rgba(255,255,255,0.5);display:inline-flex;margin-top:6px;"><i class="bi-box-arrow-in-right"></i> Connexion</a>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const navbar = document.getElementById('gelNavbar');
        window.addEventListener('scroll', () => { navbar.classList.toggle('scrolled', window.scrollY > 20); }, { passive: true });
        const toggler = document.getElementById('gelToggler');
        const mobileMenu = document.getElementById('gelMobileMenu');
        const togglerIcon = document.getElementById('togglerIcon');
        toggler.addEventListener('click', () => { const isOpen = mobileMenu.classList.toggle('open'); togglerIcon.className = isOpen ? 'bi-x-lg' : 'bi-list'; document.body.style.overflow = isOpen ? 'hidden' : ''; });
        document.addEventListener('click', (e) => { if (!navbar.contains(e.target) && !mobileMenu.contains(e.target)) { mobileMenu.classList.remove('open'); togglerIcon.className = 'bi-list'; document.body.style.overflow = ''; } });
        const animEls = document.querySelectorAll('.anim-fade-up, .anim-fade-left, .anim-fade-right');
        const observer = new IntersectionObserver((entries) => { entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('anim-visible'); observer.unobserve(entry.target); } }); }, { threshold: 0.12 });
        animEls.forEach(el => observer.observe(el));
    </script>
</body>
</html>
