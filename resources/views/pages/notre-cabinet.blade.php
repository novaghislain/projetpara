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
            background: linear-gradient(135deg, #0A1628 0%, #1E293B 25%, #0F172A 50%, #1E293B 75%, #0A1628 100%);
            background-size: 300% 300%;
            animation: gelGradientMove 12s ease infinite;
            padding: 70px 0 50px;
            position: relative;
            overflow: hidden;
        }
        @keyframes gelGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gel-page-header::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(var(--gel-svc-rgb),0.12) 0%, transparent 70%); border-radius: 50%; animation: gelFloatA 8s ease-in-out infinite; }
        .gel-page-header::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(var(--gel-svc-rgb),0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
        @keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
        @keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(1.8rem, 3vw, 2.4rem); font-weight: 900; color: #fff; letter-spacing: -1px; position: relative; z-index: 1; }
        .gel-page-header p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 620px; margin-top: 12px; line-height: 1.7; position: relative; z-index: 1; }

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
        /* AI Chat Styles */
        .chat-fab{position:fixed;bottom:24px;right:24px;width:56px;height:56px;border-radius:50%;background:#FF7900;color:white;border:none;font-size:24px;cursor:pointer;box-shadow:0 4px 20px rgba(255,121,0,0.3);transition:all 0.3s;z-index:1060;display:flex;align-items:center;justify-content:center}.chat-fab:hover{transform:scale(1.05);background:#e06700}.chat-window{position:fixed;bottom:90px;right:24px;width:380px;height:560px;background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,0.15);z-index:1060;display:flex;flex-direction:column;overflow:hidden;border:1px solid rgba(0,0,0,0.06)}.chat-window.minimized{height:56px}.chat-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#0B1120;color:white;flex-shrink:0}.chat-header-left{display:flex;align-items:center;gap:10px}.chat-avatar{width:32px;height:32px;background:#FF7900;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px}.chat-header h4{font-size:14px;font-weight:600;margin:0;color:white}.chat-status{font-size:11px;color:rgba(255,255,255,0.55);display:flex;align-items:center;gap:4px}.status-dot{width:6px;height:6px;background:#10b981;border-radius:50%;display:inline-block}.chat-header-actions{display:flex;gap:4px}.chat-header-actions button{background:none;border:none;color:rgba(255,255,255,0.6);cursor:pointer;padding:4px 6px;border-radius:4px;font-size:14px}.chat-header-actions button:hover{background:rgba(255,255,255,0.1);color:white}.chat-messages{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:8px;background:#F9FAFB}.message{display:flex;max-width:85%}.message-assistant{align-self:flex-start}.message-user{align-self:flex-end}.message-content{padding:10px 14px;border-radius:12px;font-size:13.5px;line-height:1.5}.message-assistant .message-content{background:white;border:1px solid rgba(0,0,0,0.06);color:#1F2937;border-bottom-left-radius:4px}.message-user .message-content{background:#FF7900;color:white;border-bottom-right-radius:4px}.chat-input-area{display:flex;align-items:center;gap:8px;padding:12px 16px;border-top:1px solid rgba(0,0,0,0.06);background:white}.chat-input-area input{flex:1;border:none;outline:none;font-size:13px;padding:8px 0;color:#1F2937;background:transparent}.chat-input-area input::placeholder{color:#9CA3AF}.chat-input-area button{background:#FF7900;color:white;border:none;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px}.chat-input-area button:hover{background:#e06700}
    </style>
</head>
<body>

    @include('partials.navbar')

    <!-- Page Header -->
    <header class="gel-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="anim-fade-up">Notre Cabinet</h1>
                    <p class="anim-fade-up delay-1">GEL Cabinet en quelques mots : notre histoire, notre mission, nos valeurs.</p>
                </div>
            </div>
        </div>
    </header>

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
    <!-- Pourquoi choisir GEL -->
    <section class="gel-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 order-lg-2 anim-fade-up">

                    <h2 class="gel-section-title">Pourquoi choisir GEL Cabinet ?</h2>
                    <p class="gel-section-sub" style="max-width:100%;">Nous ne sommes pas juste un éditeur de logiciel, nous sommes votre partenaire de croissance. Voici ce qui nous différencie.</p>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="gel-card-icon me-3" style="min-width:48px; height:48px; background:var(--gel-primary-light); color:var(--gel-primary);"><i class="bi-check-all"></i></div>
                        <div>
                            <h5 class="mb-1">Plateforme Tout-en-un</h5>
                            <p class="text-muted mb-0">De la comptabilité au CRM, tout est centralisé dans un seul écosystème puissant et sécurisé.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="gel-card-icon me-3" style="min-width:48px; height:48px; background:var(--gel-primary-light); color:var(--gel-primary);"><i class="bi-check-all"></i></div>
                        <div>
                            <h5 class="mb-1">Conçu pour les Experts</h5>
                            <p class="text-muted mb-0">Développé en collaboration avec des professionnels pour répondre précisément à vos besoins métiers.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="gel-card-icon me-3" style="min-width:48px; height:48px; background:var(--gel-primary-light); color:var(--gel-primary);"><i class="bi-check-all"></i></div>
                        <div>
                            <h5 class="mb-1">Sécurité Bancaire</h5>
                            <p class="text-muted mb-0">Vos données et celles de vos clients sont protégées par les meilleurs standards de sécurité.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1 anim-fade-up delay-1">
                    <div style="background:linear-gradient(135deg, rgba(249,115,22,0.1), rgba(249,115,22,0.05)); border-radius:24px; padding:40px; text-align:center; height:100%;">
                        <i class="bi-shield-check text-orange" style="font-size:80px; opacity:0.8;"></i>
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

                    <h2 class="gel-section-title anim-fade-up delay-1">Du concept à la référence</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">L'histoire de GEL Cabinet est celle d'une vision : créer l'outil ultime pour les cabinets d'expertise.</p>
                </div>
            </div>
            <div class="row g-4">
                <!-- Atout 1 -->
                <div class="col-md-4 anim-fade-up">
                    <div class="gel-value-card text-center">
                        <div class="gel-value-icon"><i class="bi-shield-check"></i></div>
                        <h4>Conformité e-MECeF & OHADA</h4>
                        <p>Émission de factures normalisées DGI et génération des états financiers certifiés SYSCOHADA en toute sérénité.</p>
                    </div>
                </div>
                <!-- Atout 2 -->
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-value-card text-center">
                        <div class="gel-value-icon"><i class="bi-phone"></i></div>
                        <h4>Finance Locale & Mobile</h4>
                        <p>Intégration native des paiements Mobile Money (MTN/Moov) et gestion numérisée des Tontines / Microfinance.</p>
                    </div>
                </div>
                <!-- Atout 3 -->
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-value-card text-center">
                        <div class="gel-value-icon"><i class="bi-robot"></i></div>
                        <h4>GEL Intelligence (IA)</h4>
                        <p>Automatisation de la saisie (OCR), assistance virtuelle et fil d'activité intelligent pour décupler votre productivité.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Domaines d'expertise -->
    <section class="gel-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">

                    <h2 class="gel-section-title anim-fade-up delay-1">Une maîtrise à 360°</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Nos solutions couvrent l'ensemble des besoins de votre cabinet, de la gestion interne à la relation client.</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <!-- Pôle 1 -->
                <div class="col-lg-3 col-md-6 anim-fade-up">
                    <div class="gel-expertise-card">
                        <div class="gel-expertise-icon"><i class="bi-calculator"></i></div>
                        <h4>Comptabilité & Finance</h4>
                        <p>Saisie automatisée, rapprochement bancaire, liasse fiscale et clôture annuelle certifiée SYSCOHADA.</p>
                    </div>
                </div>

                <!-- Pôle 2 -->
                <div class="col-lg-3 col-md-6 anim-fade-up delay-1">
                    <div class="gel-expertise-card">
                        <div class="gel-expertise-icon"><i class="bi-receipt"></i></div>
                        <h4>Fiscalité & Facturation</h4>
                        <p>Facturation e-MECeF directe avec la DGI, gestion de la TVA et télédéclarations automatisées.</p>
                    </div>
                </div>

                <!-- Pôle 3 -->
                <div class="col-lg-3 col-md-6 anim-fade-up delay-2">
                    <div class="gel-expertise-card">
                        <div class="gel-expertise-icon"><i class="bi-bank2"></i></div>
                        <h4>Juridique & Conformité</h4>
                        <p>Secrétariat DAE, gestion des actes, assemblées générales, et workflows de validation stricts.</p>
                    </div>
                </div>

                <!-- Pôle 4 -->
                <div class="col-lg-3 col-md-6 anim-fade-up delay-3">
                    <div class="gel-expertise-card">
                        <div class="gel-expertise-icon"><i class="bi-person-vcard"></i></div>
                        <h4>Social & Paie</h4>
                        <p>Édition de fiches de paie intégrées aux barèmes IRPP/CNSS du Bénin et suivi des congés.</p>
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

    @include('partials.footer')

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
    @include('partials.ai-chat-floating')
</body>
</html>