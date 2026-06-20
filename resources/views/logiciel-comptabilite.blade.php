<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GEL Comptabilité | Logiciel de Gestion Comptable &amp; d'Entreprise | GEL Cabinet</title>
    <meta name="description" content="ERP complet pour cabinets comptables au Bénin — comptabilité OHADA/SYSCOA, stock, ventes, paie, caisse, Mobile Money, hôtel, scolaire, location, transport, tontine. Licence perpétuelle.">
    <meta property="og:title" content="GEL Comptabilité — Logiciel de Gestion Comptable &amp; d'Entreprise">
    <meta property="og:description" content="ERP complet avec 35+ modules : comptabilité, stock, ventes, paie, caisse, Mobile Money, hôtel, scolaire, location, transport, tontine. Conforme OHADA.">
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --gel-primary: #FF7900; --gel-primary-hov: #e06700; --gel-primary-soft: rgba(255,121,0,0.08);
            --gel-blue: #163A5E; --gel-blue-light: #EFF6FF; --gel-blue-soft: rgba(22,58,94,0.06);
            --gel-dark: #111827; --gel-darker: #0F172A;
            --gel-white: #ffffff; --gel-light: #F8FAFC; --gel-light2: #F1F5F9;
            --gel-border: #E2E8F0; --gel-muted: #64748B; --gel-text: #1E293B;
            --font-body: 'Inter', sans-serif; --font-heading: 'Outfit', sans-serif;
            --nav-height: 72px; --radius: 6px; --radius-lg: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 8px 30px rgba(0,0,0,0.07);
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.1);
            --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-body); background: var(--gel-white); color: var(--gel-text); line-height: 1.6; -webkit-font-smoothing: antialiased; }
        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading); font-weight: 700; }

        .anim-fade-up { opacity: 0; transform: translateY(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-fade-left { opacity: 0; transform: translateX(-36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-fade-right { opacity: 0; transform: translateX(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-visible { opacity: 1 !important; transform: none !important; }
        .delay-1 { transition-delay: 0.1s !important; }
        .delay-2 { transition-delay: 0.2s !important; }
        .delay-3 { transition-delay: 0.3s !important; }
        .delay-4 { transition-delay: 0.4s !important; }

        .gel-navbar { position: fixed; top:0; left:0; right:0; z-index:1050; background:rgba(255,255,255,0.95); backdrop-filter:blur(12px); border-bottom:1px solid transparent; height:var(--nav-height); display:flex; align-items:center; transition:border-color var(--transition), box-shadow var(--transition); }
        .gel-navbar.scrolled { border-bottom-color:var(--gel-border); box-shadow:var(--shadow-sm); }
        .gel-navbar .container-fluid { display:flex; align-items:center; justify-content:space-between; padding:0 32px; max-width:1320px; margin:0 auto; width:100%; }
        .gel-brand { display:flex; align-items:center; gap:11px; text-decoration:none; }
        .gel-brand-logo { width:38px; height:38px; background:var(--gel-primary); border-radius:6px; display:flex; align-items:center; justify-content:center; font-family:var(--font-heading); font-size:13px; font-weight:900; color:#fff; letter-spacing:-0.5px; }
        .gel-brand-text { display:flex; flex-direction:column; line-height:1.1; }
        .gel-brand-name { font-family:var(--font-heading); font-weight:800; font-size:16px; color:var(--gel-dark); letter-spacing:-0.3px; }
        .gel-brand-sub { font-size:8.5px; font-weight:600; color:var(--gel-muted); letter-spacing:0.1em; text-transform:uppercase; }
        .gel-nav-center { display:flex; align-items:center; gap:0; list-style:none; }
        .gel-nav-link { display:flex; align-items:center; gap:3px; padding:7px 13px; font-size:13px; font-weight:500; color:var(--gel-text); text-decoration:none; border-radius:6px; transition:all var(--transition); white-space:nowrap; }
        .gel-nav-link:hover, .gel-nav-link.active { color:var(--gel-primary); background:var(--gel-primary-soft); }
        .gel-nav-item { position: relative; }
        .gel-nav-link .chevron { font-size:10px; transition:transform var(--transition); }
        .gel-nav-item:hover > a .chevron { transform:rotate(180deg); }
        .gel-dropdown { position:absolute; top:calc(100% + 8px); left:50%; transform:translateX(-50%); background:var(--gel-white); border:1px solid var(--gel-border); border-radius:10px; box-shadow:var(--shadow-lg); padding:6px; min-width:220px; opacity:0; visibility:hidden; transform:translateX(-50%) translateY(-8px); transition:opacity 0.2s, transform 0.2s, visibility 0.2s; list-style:none; }
        .gel-nav-item:hover .gel-dropdown { opacity:1; visibility:visible; transform:translateX(-50%) translateY(0); }
        .gel-dropdown li a { display:flex; align-items:center; gap:10px; padding:8px 12px; font-size:13px; font-weight:500; color:var(--gel-text); text-decoration:none; border-radius:var(--radius); transition:background var(--transition), color var(--transition); }
        .gel-dropdown li a:hover { background:var(--gel-primary-soft); color:var(--gel-primary); }
        .gel-dropdown li a .drop-icon { width:26px; height:26px; background:var(--gel-light2); border-radius:4px; display:flex; align-items:center; justify-content:center; color:var(--gel-primary); font-size:12px; flex-shrink:0; }
        .gel-dropdown-divider { border:none; border-top:1px solid var(--gel-border); margin:4px 0; }
        .gel-phone { display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:500; color:var(--gel-muted); text-decoration:none; padding:6px 10px; border-radius:var(--radius); transition:color var(--transition); }
        .gel-phone:hover { color:var(--gel-primary); }
        .gel-phone i { color:var(--gel-primary); font-size:13px; }
        .gel-nav-right { display:flex; align-items:center; gap:8px; }
        .gel-btn-nav { display:inline-flex; align-items:center; gap:5px; padding:7px 16px; font-size:12.5px; font-weight:600; border-radius:6px; text-decoration:none; transition:all var(--transition); border:none; cursor:pointer; }
        .gel-btn-nav-outline { background:transparent; color:var(--gel-text); border:1.5px solid var(--gel-border); }
        .gel-btn-nav-outline:hover { border-color:var(--gel-primary); color:var(--gel-primary); }
        .gel-btn-nav-primary { background:var(--gel-primary); color:#fff; }
        .gel-btn-nav-primary:hover { background:var(--gel-primary-hov); color:#fff; transform:translateY(-1px); box-shadow:0 4px 16px rgba(255,121,0,0.3); }
        .gel-toggler { display:none; background:none; border:1.5px solid var(--gel-border); border-radius:6px; padding:6px 9px; cursor:pointer; color:var(--gel-dark); font-size:17px; }
        .gel-toggler:hover { border-color:var(--gel-primary); color:var(--gel-primary); }
        @media (max-width:991px) { .gel-nav-center { display:none; } .gel-toggler { display:flex; } .gel-navbar .container-fluid { padding:0 16px; } }

        .gel-mobile-menu { display:none; position:fixed; top:var(--nav-height); left:0; right:0; background:var(--gel-white); border-bottom:3px solid var(--gel-primary); box-shadow:var(--shadow-md); z-index:1040; padding:16px 24px 24px; max-height:calc(100vh - var(--nav-height)); overflow-y:auto; }
        .gel-mobile-menu.open { display:block; }
        .gel-mobile-link { display:flex; align-items:center; gap:10px; padding:11px 0; font-size:14px; font-weight:500; color:var(--gel-text); text-decoration:none; border-bottom:1px solid var(--gel-border); }
        .gel-mobile-link:hover { color:var(--gel-primary); }

        .gel-hero-inner { padding:120px 0 80px; background:linear-gradient(135deg,#0F172A 0%,#1E3A5F 50%,#163A5E 100%); position:relative; overflow:hidden; }
        .gel-hero-inner::before { content:''; position:absolute; top:-50%; right:-20%; width:600px; height:600px; background:radial-gradient(circle,rgba(255,121,0,0.15) 0%,transparent 70%); border-radius:50%; pointer-events:none; }
        .gel-hero-inner h1 { font-size:clamp(2rem,4.5vw,3.2rem); font-weight:900; color:#fff; letter-spacing:-1px; line-height:1.1; }
        .gel-hero-inner h1 span { color:var(--gel-primary); }
        .gel-hero-inner p { font-size:16px; color:rgba(255,255,255,0.65); max-width:560px; line-height:1.7; }
        .gel-hero-inner .breadcrumb { background:transparent; padding:0; margin-bottom:16px; }
        .gel-hero-inner .breadcrumb-item { font-size:13px; color:rgba(255,255,255,0.5); }
        .gel-hero-inner .breadcrumb-item a { color:rgba(255,255,255,0.7); text-decoration:none; }
        .gel-hero-inner .breadcrumb-item.active { color:var(--gel-primary); }
        .gel-hero-inner .breadcrumb-item+.breadcrumb-item::before { color:rgba(255,255,255,0.3); }

        .gel-section { padding:80px 0; }
        .gel-section-alt { background:var(--gel-light); }
        .gel-section-chip { display:inline-flex; align-items:center; gap:6px; background:var(--gel-white); color:var(--gel-primary); font-size:11px; font-weight:700; padding:4px 14px; border-radius:100px; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:14px; border:1.5px solid rgba(255,121,0,0.2); }
        .gel-section-title { font-family:var(--font-heading); font-size:clamp(1.6rem,3vw,2.2rem); font-weight:800; color:var(--gel-dark); letter-spacing:-0.5px; line-height:1.2; margin-bottom:14px; }
        .gel-section-sub { font-size:15px; color:var(--gel-muted); max-width:540px; line-height:1.7; }
        .gel-section-sub.mx-auto { margin-left:auto; margin-right:auto; }
        .gel-section-sub.wide { max-width:680px; }

        .gel-feat-card { background:var(--gel-white); border:1px solid var(--gel-border); border-radius:var(--radius-lg); padding:28px 24px; height:100%; transition:all var(--transition); }
        .gel-feat-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:transparent; }
        .gel-feat-icon { width:48px; height:48px; background:var(--gel-primary-soft); border-radius:12px; display:flex; align-items:center; justify-content:center; color:var(--gel-primary); font-size:22px; margin-bottom:16px; transition:all var(--transition); }
        .gel-feat-card:hover .gel-feat-icon { background:var(--gel-primary); color:#fff; }
        .gel-feat-card h5 { font-size:15px; font-weight:700; margin-bottom:8px; }
        .gel-feat-card p { font-size:13px; color:var(--gel-muted); margin:0; line-height:1.6; }

        .gel-mockup-wrap { position:relative; }
        .gel-mockup { width:100%; border-radius:16px; border:1px solid var(--gel-border); box-shadow:var(--shadow-lg); background:var(--gel-white); overflow:hidden; }
        .gel-mockup-bar { background:#F1F5F9; padding:12px 16px; display:flex; align-items:center; gap:8px; border-bottom:1px solid var(--gel-border); }
        .gel-mockup-dot { width:10px; height:10px; border-radius:50%; }
        .gel-mockup-dot.red { background:#ef4444; }
        .gel-mockup-dot.yellow { background:#eab308; }
        .gel-mockup-dot.green { background:#22c55e; }
        .gel-mockup-body { padding:24px; }

        .gel-license-card { background:var(--gel-white); border:1.5px solid var(--gel-border); border-radius:var(--radius-lg); padding:36px 28px; height:100%; transition:all var(--transition); position:relative; }
        .gel-license-card:hover { transform:translateY(-6px); box-shadow:var(--shadow-lg); border-color:transparent; }
        .gel-license-card.featured { border-color:var(--gel-primary); box-shadow:0 0 0 1px var(--gel-primary), var(--shadow-md); }
        .gel-license-card.featured::before { content:'Recommandé'; position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:var(--gel-primary); color:#fff; font-size:11px; font-weight:700; padding:4px 18px; border-radius:100px; text-transform:uppercase; letter-spacing:0.06em; }
        .gel-license-card h4 { font-size:18px; font-weight:800; }
        .gel-license-price { font-family:var(--font-heading); font-size:2.4rem; font-weight:900; color:var(--gel-blue); line-height:1; }
        .gel-license-price span { font-size:14px; font-weight:500; color:var(--gel-muted); }
        .gel-license-features { list-style:none; padding:0; margin:20px 0; }
        .gel-license-features li { display:flex; align-items:center; gap:10px; padding:8px 0; font-size:13.5px; color:var(--gel-text); border-bottom:1px solid var(--gel-border); }
        .gel-license-features li:last-child { border-bottom:none; }
        .gel-license-features li i { color:#22c55e; font-size:14px; }
        .gel-btn-license { display:flex; align-items:center; justify-content:center; gap:6px; width:100%; padding:12px; font-size:14px; font-weight:700; border-radius:8px; text-decoration:none; transition:all var(--transition); }
        .gel-btn-license-primary { background:var(--gel-primary); color:#fff; }
        .gel-btn-license-primary:hover { background:var(--gel-primary-hov); color:#fff; transform:translateY(-1px); box-shadow:0 6px 20px rgba(255,121,0,0.3); }
        .gel-btn-license-outline { background:transparent; color:var(--gel-primary); border:1.5px solid var(--gel-primary); }

        .gel-payment-wrap { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:12px; }
        .gel-payment-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:var(--gel-light); border:1px solid var(--gel-border); border-radius:100px; font-size:12px; font-weight:600; color:var(--gel-muted); }

        .gel-faq-item { border:1px solid var(--gel-border); border-radius:var(--radius-lg); margin-bottom:12px; overflow:hidden; }
        .gel-faq-question { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; cursor:pointer; font-size:14px; font-weight:600; transition:background var(--transition); }
        .gel-faq-question:hover { background:var(--gel-light); }
        .gel-faq-question i { transition:transform var(--transition); color:var(--gel-primary); }
        .gel-faq-question.open i { transform:rotate(180deg); }
        .gel-faq-answer { padding:0 24px; max-height:0; overflow:hidden; transition:max-height 0.3s, padding 0.3s; }
        .gel-faq-answer.open { max-height:300px; padding:0 24px 18px; }
        .gel-faq-answer p { font-size:13.5px; color:var(--gel-muted); line-height:1.7; margin:0; }

        .gel-footer { background:#0A1628; padding:64px 0 0; border-top:3px solid var(--gel-primary); }
        .gel-footer-brand-name { font-family:var(--font-heading); font-size:20px; font-weight:800; color:#fff; }
        .gel-footer-brand-sub { font-size:10px; font-weight:600; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:0.1em; }
        .gel-footer-desc { font-size:13px; color:rgba(255,255,255,0.45); line-height:1.7; margin-top:16px; max-width:280px; }
        .gel-footer-social { display:flex; gap:10px; margin-top:20px; }
        .gel-social-btn { width:36px; height:36px; border-radius:6px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.55); font-size:15px; text-decoration:none; transition:all var(--transition); }
        .gel-social-btn:hover { background:var(--gel-primary); border-color:var(--gel-primary); color:#fff; transform:translateY(-2px); }
        .gel-footer-heading { font-family:var(--font-heading); font-size:13px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:18px; }
        .gel-footer-links { list-style:none; padding:0; }
        .gel-footer-links li { margin-bottom:10px; }
        .gel-footer-links a { font-size:13.5px; color:rgba(255,255,255,0.45); text-decoration:none; transition:color var(--transition); display:flex; align-items:center; gap:6px; }
        .gel-footer-links a:hover { color:var(--gel-primary); }
        .gel-footer-links a::before { content:'\203A'; color:var(--gel-primary); font-size:16px; }
        .gel-footer-bottom { border-top:1px solid rgba(255,255,255,0.07); padding:20px 0; margin-top:48px; }
        .gel-footer-bottom p { font-size:12px; color:rgba(255,255,255,0.3); margin:0; }
        .gel-footer-bottom a { font-size:12px; color:rgba(255,255,255,0.3); text-decoration:none; margin-left:16px; }
        .gel-footer-bottom a:hover { color:var(--gel-primary); }

        .gel-domain-group { margin-bottom:32px; }
        .gel-domain-header { display:flex; align-items:center; gap:10px; font-family:var(--font-heading); font-size:15px; font-weight:700; color:var(--gel-blue); margin-bottom:12px; padding:10px 16px; background:var(--gel-blue-soft); border-radius:8px; border-left:3px solid var(--gel-primary); }
        .gel-domain-header i { font-size:18px; color:var(--gel-primary); }
        .gel-module-tag { display:inline-flex; align-items:center; gap:5px; padding:6px 13px; background:var(--gel-white); border:1px solid var(--gel-border); border-radius:100px; font-size:11.5px; font-weight:500; color:var(--gel-text); transition:all var(--transition); margin:0 5px 8px 0; }
        .gel-module-tag:hover { border-color:var(--gel-primary); color:var(--gel-primary); background:var(--gel-primary-soft); transform:translateY(-1px); }
        .gel-module-tag i { color:var(--gel-primary); font-size:10px; }

        .gel-opp-card { background:var(--gel-white); border:1px solid var(--gel-border); border-radius:var(--radius-lg); padding:24px 18px; height:100%; transition:all var(--transition); text-align:center; }
        .gel-opp-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:transparent; }
        .gel-opp-icon { width:56px; height:56px; margin:0 auto 14px; background:var(--gel-primary-soft); border-radius:16px; display:flex; align-items:center; justify-content:center; color:var(--gel-primary); font-size:24px; transition:all var(--transition); }
        .gel-opp-card:hover .gel-opp-icon { background:var(--gel-primary); color:#fff; }
        .gel-opp-card h5 { font-size:14px; font-weight:700; margin-bottom:8px; color:var(--gel-blue); }
        .gel-opp-card p { font-size:12.5px; color:var(--gel-muted); line-height:1.6; margin:0; }

        .gel-stats-row { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; margin-top:32px; }
        .gel-stat-badge { display:flex; flex-direction:column; align-items:center; padding:16px 28px; background:var(--gel-white); border:1px solid var(--gel-border); border-radius:12px; min-width:110px; }
        .gel-stat-number { font-family:var(--font-heading); font-size:28px; font-weight:900; color:var(--gel-primary); line-height:1; }
        .gel-stat-label { font-size:11px; color:var(--gel-muted); font-weight:500; margin-top:4px; text-transform:uppercase; letter-spacing:0.04em; }

        .text-orange { color:var(--gel-primary); }
        @media (max-width:991px) { .gel-section { padding:60px 0; } }
        @media (max-width:575px) { .gel-hero-inner h1 { font-size:1.8rem; } }
    </style>
</head>
<body>

@include('partials.navbar')

<section class="gel-hero-inner">
    <div class="container" style="position:relative;z-index:1;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/"><i class="bi-house"></i></a></li>
                <li class="breadcrumb-item active">Logiciel Comptabilité</li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1>Logiciel de <span>comptabilité</span><br>ERP complet &amp; multi-modules</h1>
                <p>Plan comptable SYSCOA, saisie des journaux, balance, bilan, compte de résultat, stock, ventes, paie, caisse Mobile Money, hôtel, scolaire, location, transport, tontine &mdash; <strong>35+ modules</strong> dans un logiciel tout-en-un pour les cabinets au Bénin.</p>
                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:28px;">
                    <a href="/tarifs" class="gel-btn-nav gel-btn-nav-primary" style="padding:12px 24px;font-size:14px;"><i class="bi-cart3"></i> Acheter la licence</a>
                    <a href="/tarifs" class="gel-btn-nav" style="padding:12px 24px;font-size:14px;background:rgba(255,255,255,0.1);color:#fff;border:1.5px solid rgba(255,255,255,0.3);"><i class="bi-currency-dollar"></i> Voir les tarifs</a>
                </div>
                <div class="gel-payment-wrap">
                    <span class="gel-payment-badge" style="border-color:rgba(255,255,255,0.2);color:rgba(255,255,255,0.7);"><i class="bi-phone" style="color:var(--gel-primary);"></i> Mobile Money</span>
                    <span class="gel-payment-badge" style="border-color:rgba(255,255,255,0.2);color:rgba(255,255,255,0.7);"><i class="bi-bank" style="color:var(--gel-primary);"></i> Virement bancaire</span>
                </div>
            </div>
            <div class="col-lg-5 mt-5 mt-lg-0">
                <div class="gel-mockup-wrap anim-fade-right">
                    <div class="gel-mockup">
                        <div class="gel-mockup-bar">
                            <div class="gel-mockup-dot red"></div>
                            <div class="gel-mockup-dot yellow"></div>
                            <div class="gel-mockup-dot green"></div>
                            <span style="font-size:11px;color:var(--gel-muted);margin-left:8px;">GEL Comptabilité &mdash; Plan comptable SYSCOA &amp; modules</span>
                        </div>
                        <div class="gel-mockup-body" style="background:linear-gradient(135deg,#F8FAFC,#fff);min-height:220px;display:flex;flex-direction:column;gap:12px;">
                            <div style="display:flex;gap:12px;">
                                <div style="flex:1;height:16px;background:#E2E8F0;border-radius:4px;"></div>
                                <div style="flex:2;height:16px;background:#E2E8F0;border-radius:4px;"></div>
                                <div style="flex:1;height:16px;background:#FF7900;border-radius:4px;"></div>
                            </div>
                            <div style="display:flex;gap:12px;">
                                <div style="flex:1;height:32px;background:#F1F5F9;border-radius:6px;"></div>
                                <div style="flex:2;height:32px;background:#F1F5F9;border-radius:6px;"></div>
                                <div style="flex:1;height:32px;background:#F1F5F9;border-radius:6px;"></div>
                            </div>
                            <div style="display:flex;gap:12px;">
                                <div style="flex:0.5;height:24px;background:#DBEAFE;border-radius:4px;"></div>
                                <div style="flex:1;height:24px;background:#DBEAFE;border-radius:4px;"></div>
                                <div style="flex:2;height:24px;background:#DBEAFE;border-radius:4px;"></div>
                            </div>
                            <div style="display:flex;gap:12px;">
                                <div style="flex:1;height:40px;background:#F1F5F9;border-radius:6px;"></div>
                                <div style="flex:2;height:40px;background:#FF7900;border-radius:6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-stars"></i> Fonctionnalités</span>
                <h2 class="gel-section-title">Tout ce qu'il faut pour votre comptabilité</h2>
                <p class="gel-section-sub wide mx-auto">Un logiciel complet conforme aux normes OHADA/SYSCOA, utilisé par des experts-comptables au Bénin et dans la zone UEMOA.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                <div class="gel-feat-card">
                    <div class="gel-feat-icon"><i class="bi-layout-text-window"></i></div>
                    <h5>Plan comptable SYSCOA</h5>
                    <p>Plan comptable complet conforme au Système Comptable Ouest-Africain (SYSCOA/OHADA). Classes 1 à 8, paramétrable.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                <div class="gel-feat-card">
                    <div class="gel-feat-icon"><i class="bi-journal-text"></i></div>
                    <h5>Journaux comptables</h5>
                    <p>Saisie des journaux (achats, ventes, banque, caisse, OD) avec contrôle automatique des équilibres et lettrage.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                <div class="gel-feat-card">
                    <div class="gel-feat-icon"><i class="bi-bar-chart-line"></i></div>
                    <h5>Balance &amp; Grand Livre</h5>
                    <p>Balance générale et auxiliaire, grand livre, balance âgée &mdash; tous les états de synthèse à portée de clic.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                <div class="gel-feat-card">
                    <div class="gel-feat-icon"><i class="bi-file-earmark-bar-graph"></i></div>
                    <h5>Bilan &amp; CRP</h5>
                    <p>Bilan actif/passif et Compte de Résultat : génération automatique, export PDF/Excel prêt pour le dépôt.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                <div class="gel-feat-card">
                    <div class="gel-feat-icon"><i class="bi-receipt"></i></div>
                    <h5>Déclarations fiscales</h5>
                    <p>Déclaration TVA, état des immobilisations, relevé des honoraires &mdash; conformes à la réglementation béninoise.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                <div class="gel-feat-card">
                    <div class="gel-feat-icon"><i class="bi-people"></i></div>
                    <h5>Multi-utilisateurs</h5>
                    <p>Gérez les accès par profil : comptable, superviseur, expert. Chacun voit et fait ce qui le concerne.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section" style="background:linear-gradient(135deg,#F8FAFC 0%,#EFF6FF 100%);">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-grid-3x3-gap"></i> Domaines couverts</span>
                <h2 class="gel-section-title">Un ERP complet pour votre cabinet</h2>
                <p class="gel-section-sub wide mx-auto">Au-delà de la comptabilité, GEL Comptabilité couvre <strong>plus de 35 modules métier</strong> répartis en 7 domaines. Une solution tout-en-un qui centralise l'ensemble des opérations de votre cabinet et de vos clients.</p>
                <div class="gel-stats-row">
                    <div class="gel-stat-badge"><span class="gel-stat-number">35+</span><span class="gel-stat-label">Modules</span></div>
                    <div class="gel-stat-badge"><span class="gel-stat-number">7</span><span class="gel-stat-label">Domaines</span></div>
                    <div class="gel-stat-badge"><span class="gel-stat-number">10</span><span class="gel-stat-label">Profils</span></div>
                    <div class="gel-stat-badge"><span class="gel-stat-number">5</span><span class="gel-stat-label">Bases dédiées</span></div>
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
                <span class="gel-module-tag"><i class="bi-check2"></i> Bénéfice brut par produit</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> Déclarations fiscales (TVA, AIB, TS)</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> Gestion exercices (ouverture/clôture)</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> Finances &amp; ratios</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> Comptes par défaut</span>
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
                <span class="gel-module-tag"><i class="bi-check2"></i> Commission commerciale</span>
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
                <span class="gel-module-tag"><i class="bi-check2"></i> Emballage &amp; consignation</span>
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
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Hôtel</strong> &mdash; Chambres, réservations, taxe nuitée, minibar</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Location</strong> &mdash; Biens, locataires, quittances, loyers</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Scolaire</strong> &mdash; Classes, élèves, notes, bulletins, frais</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Tontine</strong> &mdash; Types, adhérents, cotisations, échéancier</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Transport</strong> &mdash; Camions, tournées, charges, bénéfice</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Transit</strong> &mdash; Dossiers, chargements, points de transit</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Pressing</strong> &mdash; Commandes, bon de livraison, suivi client</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> <strong>Morgue</strong> &mdash; Dépôts, retraits, paramètres</span>
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
                <span class="gel-module-tag"><i class="bi-check2"></i> Corbeille &amp; historique des suppressions</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> Configuration imprimante &amp; police</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> Multi-sociétés &amp; dossiers</span>
                <span class="gel-module-tag"><i class="bi-check2"></i> Base de données Firebird sécurisée</span>
            </div>
        </div>

        <div class="text-center mt-4 anim-fade-up delay-2">
            <a href="/tarifs" class="gel-btn-nav gel-btn-nav-primary" style="padding:12px 32px;font-size:14px;"><i class="bi-cart3"></i> Découvrir tous les modules</a>
        </div>
    </div>
</section>

<section class="gel-section gel-section-alt">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 anim-fade-left">
                <span class="gel-section-chip"><i class="bi-info-circle"></i> Pourquoi GEL Comptabilité ?</span>
                <h2 class="gel-section-title">Conçu pour les experts-comptables <span style="color:var(--gel-primary);">africains</span></h2>
                <p style="font-size:14px;color:var(--gel-muted);line-height:1.7;margin-bottom:20px;">Notre logiciel de comptabilité a été spécifiquement développé pour répondre aux besoins des cabinets d'expertise comptable au Bénin et dans la zone UEMOA. Conforme au plan comptable SYSCOA/OHADA, il intègre toutes les spécificités locales :</p>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--gel-white);border-radius:8px;border:1px solid var(--gel-border);">
                        <div style="width:32px;height:32px;background:var(--gel-primary-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--gel-primary);flex-shrink:0;"><i class="bi-check-lg"></i></div>
                        <div><strong style="font-size:13px;">Conforme OHADA</strong><p style="font-size:12px;color:var(--gel-muted);margin:0;">Plan SYSCOA révisé, classes 1 à 8</p></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--gel-white);border-radius:8px;border:1px solid var(--gel-border);">
                        <div style="width:32px;height:32px;background:var(--gel-primary-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--gel-primary);flex-shrink:0;"><i class="bi-check-lg"></i></div>
                        <div><strong style="font-size:13px;">Devise FCFA</strong><p style="font-size:12px;color:var(--gel-muted);margin:0;">Gestion en Franc CFA, arrondis conformes</p></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--gel-white);border-radius:8px;border:1px solid var(--gel-border);">
                        <div style="width:32px;height:32px;background:var(--gel-primary-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--gel-primary);flex-shrink:0;"><i class="bi-check-lg"></i></div>
                        <div><strong style="font-size:13px;">Licence perpétuelle</strong><p style="font-size:12px;color:var(--gel-muted);margin:0;">Payez une fois, utilisez à vie</p></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--gel-white);border-radius:8px;border:1px solid var(--gel-border);">
                        <div style="width:32px;height:32px;background:var(--gel-primary-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--gel-primary);flex-shrink:0;"><i class="bi-check-lg"></i></div>
                        <div><strong style="font-size:13px;">Support local</strong><p style="font-size:12px;color:var(--gel-muted);margin:0;">Équipe basée au Bénin, assistance en Français</p></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0 anim-fade-right">
                <div style="background:var(--gel-white);border:1px solid var(--gel-border);border-radius:var(--radius-lg);padding:32px;text-align:center;">
                    <div style="font-size:48px;color:var(--gel-primary);margin-bottom:16px;"><i class="bi-cpu"></i></div>
                    <h3 style="font-size:22px;font-weight:800;">Logiciel standalone</h3>
                    <p style="font-size:14px;color:var(--gel-muted);margin:8px 0 20px;">Installation sur votre poste de travail. Pas d'abonnement mensuel. Mises à jour incluses.</p>
                    <div style="font-family:var(--font-heading);font-size:2.8rem;font-weight:900;color:var(--gel-blue);">150 000 <span style="font-size:16px;color:var(--gel-muted);">FCFA</span></div>
                    <p style="font-size:12px;color:var(--gel-muted);margin-top:4px;">Licence starter &mdash; 1 poste</p>
                    <a href="/tarifs" class="gel-btn-license gel-btn-license-primary" style="display:inline-flex;margin-top:20px;width:auto;padding:12px 32px;"><i class="bi-cart3"></i> Acheter</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-tags"></i> Licences</span>
                <h2 class="gel-section-title">Choisissez votre licence</h2>
                <p class="gel-section-sub wide mx-auto">Des formules adaptées à votre structure, de l'indépendant au cabinet multi-pôles.</p>
            </div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                <div class="gel-license-card">
                    <span class="gel-section-chip" style="margin-bottom:12px;">Individuel</span>
                    <h4>Starter</h4>
                    <div class="gel-license-price">150 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 20px;">Licence perpétuelle &mdash; 1 poste</p>
                    <ul class="gel-license-features">
                        <li><i class="bi-check-lg"></i> Plan comptable OHADA/SYSCOA</li>
                        <li><i class="bi-check-lg"></i> Saisie des journaux</li>
                        <li><i class="bi-check-lg"></i> Balance &amp; Grand Livre</li>
                        <li><i class="bi-check-lg"></i> Bilan &amp; CRP</li>
                        <li><i class="bi-check-lg"></i> 1 an de mises à jour</li>
                        <li><i class="bi-check-lg"></i> Support email</li>
                    </ul>
                    <a href="/tarifs" class="gel-btn-license gel-btn-license-primary"><i class="bi-cart3"></i> Acheter</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                <div class="gel-license-card featured">
                    <span class="gel-section-chip" style="margin-bottom:12px;">Cabinet</span>
                    <h4>Pro</h4>
                    <div class="gel-license-price">350 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 20px;">Licence perpétuelle &mdash; 3 postes</p>
                    <ul class="gel-license-features">
                        <li><i class="bi-check-lg"></i> Tout Starter +</li>
                        <li><i class="bi-check-lg"></i> Multi-utilisateurs (3 postes)</li>
                        <li><i class="bi-check-lg"></i> Déclarations fiscales</li>
                        <li><i class="bi-check-lg"></i> TVA &amp; États statistiques</li>
                        <li><i class="bi-check-lg"></i> 2 ans de mises à jour</li>
                        <li><i class="bi-check-lg"></i> Support prioritaire</li>
                    </ul>
                    <a href="/tarifs" class="gel-btn-license gel-btn-license-primary"><i class="bi-cart3"></i> Acheter</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                <div class="gel-license-card">
                    <span class="gel-section-chip" style="margin-bottom:12px;">Entreprise</span>
                    <h4>Cabinet</h4>
                    <div class="gel-license-price">750 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 20px;">Licence perpétuelle &mdash; postes illimités</p>
                    <ul class="gel-license-features">
                        <li><i class="bi-check-lg"></i> Tout Pro +</li>
                        <li><i class="bi-check-lg"></i> Postes illimités</li>
                        <li><i class="bi-check-lg"></i> Modules complémentaires</li>
                        <li><i class="bi-check-lg"></i> Formation sur site</li>
                        <li><i class="bi-check-lg"></i> Mises à jour à vie</li>
                        <li><i class="bi-check-lg"></i> Support dédié 24/7</li>
                    </ul>
                    <a href="/tarifs" class="gel-btn-license gel-btn-license-primary"><i class="bi-cart3"></i> Acheter</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-stars"></i> Opportunités par secteur</span>
                <h2 class="gel-section-title">Une solution pour chaque métier</h2>
                <p class="gel-section-sub wide mx-auto">Grâce à ses modules spécialisés, GEL Comptabilité s'adapte à une grande variété de secteurs d'activité au Bénin et dans la zone UEMOA.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-building"></i></div>
                    <h5>Hôtels &amp; Résidences</h5>
                    <p>Gestion complète des chambres, réservations, taxe de nuitée, minibar et facturation clients. Planning d'occupation visuel, impressions A4 et ticket.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-mortarboard"></i></div>
                    <h5>Établissements scolaires</h5>
                    <p>Années scolaires, classes, matières, saisie des notes, bulletins trimestre/annuel, frais scolaires, récompenses, sanctions et présence.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-house-door"></i></div>
                    <h5>Agences immobilières</h5>
                    <p>Gestion des biens (maisons, appartements), locataires, quittances de loyer, avances/cautions, loyers impayés et relevés propriétaires.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-truck"></i></div>
                    <h5>Transport &amp; Transit</h5>
                    <p>Gestion des camions, tournées, charges transport, dossiers de transit, chargements, points de contrôle et suivi logistique complet.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-people"></i></div>
                    <h5>Associations &amp; Tontines</h5>
                    <p>Types de tontine (quotidienne, hebdo, mensuelle), adhérents, comptes, cotisations, échéancier et attribution automatique.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-handbag"></i></div>
                    <h5>Pressings &amp; Services</h5>
                    <p>Commandes de pressing, dépôt vêtements, bon de livraison, suivi par client et relevé des commandes en cours.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-shop"></i></div>
                    <h5>Commerces &amp; Distribution</h5>
                    <p>Gestion des ventes, facturation (normale, rapide, prestation), commandes, clients, encaissements Mobile Money et suivi des impayés.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-boxes"></i></div>
                    <h5>Industries &amp; Production</h5>
                    <p>Arrivage marchandises, contrôle quantitatif, inventaire, gestion des emballages/consignations, stock final et suivi des pertes.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 anim-fade-up delay-3">
                <div class="gel-opp-card">
                    <div class="gel-opp-icon"><i class="bi-file-earmark-text"></i></div>
                    <h5>Cabinets comptables</h5>
                    <p>Multi-sociétés, plan comptable SYSCOA, déclarations fiscales, paie, bilan CRP, exercices multi-périodes, 10 profils utilisateurs.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section" style="background:linear-gradient(135deg,#0F172A,#1E3A5F);padding:60px 0;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <span class="gel-section-chip" style="background:rgba(255,255,255,0.1);border-color:rgba(255,121,0,0.3);color:var(--gel-primary);"><i class="bi-play-circle"></i> Démo</span>
                <h2 style="font-size:clamp(1.4rem,2.5vw,2rem);font-weight:900;color:#fff;">Vous voulez voir le logiciel en action ?</h2>
                <p style="color:rgba(255,255,255,0.65);font-size:15px;margin-top:8px;">Demandez une démo personnalisée avec un de nos experts.</p>
                <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap;">
                    <a href="/contact" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--gel-primary);font-size:14px;font-weight:700;padding:12px 28px;border-radius:6px;text-decoration:none;transition:all 0.3s;"><i class="bi-calendar-check"></i> Demander une démo</a>
                    <a href="https://wa.me/22900000000" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.1);color:#fff;font-size:14px;font-weight:700;padding:12px 28px;border-radius:6px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.3);transition:all 0.3s;"><i class="bi-whatsapp"></i> WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-question-circle"></i> FAQ</span>
                <h2 class="gel-section-title">Questions fréquentes</h2>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="gel-faq-item">
                    <div class="gel-faq-question" onclick="toggleFaq(this)">Le logiciel est-il conforme OHADA ? <i class="bi-chevron-down"></i></div>
                    <div class="gel-faq-answer"><p>Oui, notre logiciel est entièrement conforme au plan comptable SYSCOA/OHADA révisé (classes 1 à 8). Il intègre également les spécificités fiscales du Bénin.</p></div>
                </div>
                <div class="gel-faq-item">
                    <div class="gel-faq-question" onclick="toggleFaq(this)">Puis-je payer en plusieurs fois ? <i class="bi-chevron-down"></i></div>
                    <div class="gel-faq-answer"><p>Oui, nous proposons des facilités de paiement pour la licence Pro et Cabinet. Contactez notre équipe commerciale pour plus de détails.</p></div>
                </div>
                <div class="gel-faq-item">
                    <div class="gel-faq-question" onclick="toggleFaq(this)">Comment se passe l'installation ? <i class="bi-chevron-down"></i></div>
                    <div class="gel-faq-answer"><p>Après achat, vous recevez un lien de téléchargement et une clé de licence par email. L'installation prend moins de 10 minutes. Un guide pas-à-pas est inclus.</p></div>
                </div>
                <div class="gel-faq-item">
                    <div class="gel-faq-question" onclick="toggleFaq(this)">Quels sont les moyens de paiement acceptés ? <i class="bi-chevron-down"></i></div>
                    <div class="gel-faq-answer"><p>Nous acceptons MTN Mobile Money (MoMo Bénin), Moov Money, le virement bancaire et le paiement par carte. Paiement 100% sécurisé.</p></div>
                </div>
                <div class="gel-faq-item">
                    <div class="gel-faq-question" onclick="toggleFaq(this)">Y a-t-il une version d'essai ? <i class="bi-chevron-down"></i></div>
                    <div class="gel-faq-answer"><p>Oui, vous pouvez demander une version de démonstration en nous contactant. Nous organisons une session de 30 minutes pour vous présenter le logiciel.</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('partials.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const navbar = document.getElementById('gelNavbar');
window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 20), { passive: true });
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
function toggleFaq(el) {
    el.classList.toggle('open');
    const answer = el.nextElementSibling;
    answer.classList.toggle('open');
}
const animEls = document.querySelectorAll('.anim-fade-up, .anim-fade-left, .anim-fade-right');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('anim-visible'); observer.unobserve(entry.target); } });
}, { threshold: 0.12 });
animEls.forEach(el => observer.observe(el));
</script>
</body>
</html>