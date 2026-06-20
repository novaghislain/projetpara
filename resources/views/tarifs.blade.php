<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tarifs & Licences | GEL Cabinet</title>
    <meta name="description" content="Tarifs des abonnements SaaS GEL Cabinet et licence standalone du logiciel de comptabilité. Plans mensuels et annuels pour cabinets comptables au Bénin.">
    <meta property="og:title" content="Tarifs & Licences | GEL Cabinet">
    <meta property="og:description" content="Découvrez nos formules adaptées à votre cabinet : SaaS modulaire ou licence standalone.">
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --gel-primary: #FF7900; --gel-primary-hov: #e06700; --gel-primary-soft: rgba(255,121,0,0.08);
            --gel-blue: #163A5E; --gel-blue-light: #F0F7FF; --gel-blue-soft: rgba(22,58,94,0.06);
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
        .gel-hero-inner::after { content:''; position:absolute; bottom:-30%; left:-10%; width:400px; height:400px; background:radial-gradient(circle,rgba(255,121,0,0.08) 0%,transparent 70%); border-radius:50%; pointer-events:none; }
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

        /* Pricing */
        .gel-pricing-card { background:var(--gel-white); border:1.5px solid var(--gel-border); border-radius:var(--radius-lg); padding:36px 28px; height:100%; transition:all var(--transition); position:relative; }
        .gel-pricing-card:hover { transform:translateY(-6px); box-shadow:var(--shadow-lg); border-color:transparent; }
        .gel-pricing-card.featured { border-color:var(--gel-primary); box-shadow:0 0 0 1px var(--gel-primary), var(--shadow-md); }
        .gel-pricing-card.featured::before { content:'Populaire'; position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:var(--gel-primary); color:#fff; font-size:11px; font-weight:700; padding:4px 18px; border-radius:100px; text-transform:uppercase; letter-spacing:0.06em; }
        .gel-pricing-name { font-size:13px; font-weight:700; color:var(--gel-muted); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px; }
        .gel-pricing-price { font-family:var(--font-heading); font-size:2.6rem; font-weight:900; color:var(--gel-dark); line-height:1; }
        .gel-pricing-price span { font-size:16px; font-weight:600; color:var(--gel-muted); }
        .gel-pricing-desc { font-size:13px; color:var(--gel-muted); margin-top:8px; }
        .gel-pricing-features { list-style:none; padding:0; margin:24px 0; }
        .gel-pricing-features li { display:flex; align-items:center; gap:10px; padding:8px 0; font-size:13.5px; color:var(--gel-text); border-bottom:1px solid var(--gel-border); }
        .gel-pricing-features li:last-child { border-bottom:none; }
        .gel-pricing-features li i { color:var(--gel-primary); font-size:14px; flex-shrink:0; }
        .gel-btn-pricing { display:flex; align-items:center; justify-content:center; gap:6px; width:100%; padding:12px; font-size:14px; font-weight:700; border-radius:8px; text-decoration:none; transition:all var(--transition); border:none; cursor:pointer; }
        .gel-btn-pricing-primary { background:var(--gel-primary); color:#fff; }
        .gel-btn-pricing-primary:hover { background:var(--gel-primary-hov); color:#fff; transform:translateY(-1px); box-shadow:0 6px 20px rgba(255,121,0,0.3); }
        .gel-btn-pricing-outline { background:transparent; color:var(--gel-primary); border:1.5px solid var(--gel-primary); }
        .gel-btn-pricing-outline:hover { background:var(--gel-primary); color:#fff; }

        .gel-toggle-wrap { display:inline-flex; align-items:center; gap:12px; background:var(--gel-light); border-radius:100px; padding:4px; }
        .gel-toggle-btn { padding:8px 20px; font-size:13px; font-weight:600; border:none; background:transparent; border-radius:100px; cursor:pointer; transition:all var(--transition); color:var(--gel-muted); }
        .gel-toggle-btn.active { background:var(--gel-white); color:var(--gel-dark); box-shadow:var(--shadow-sm); }
        .gel-toggle-badge { font-size:11px; background:var(--gel-primary); color:#fff; padding:2px 10px; border-radius:100px; font-weight:700; }

        .gel-license-card { background:var(--gel-white); border:1px solid var(--gel-border); border-radius:var(--radius-lg); padding:32px 24px; height:100%; transition:all var(--transition); }
        .gel-license-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
        .gel-license-card h4 { font-size:18px; font-weight:800; }
        .gel-license-price { font-family:var(--font-heading); font-size:2.2rem; font-weight:900; color:var(--gel-blue); }
        .gel-license-price span { font-size:14px; font-weight:500; color:var(--gel-muted); }

        .gel-compare-table { border-collapse:separate; border-spacing:0; width:100%; }
        .gel-compare-table th { padding:16px 20px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--gel-muted); border-bottom:2px solid var(--gel-border); background:var(--gel-light); }
        .gel-compare-table th:first-child { border-radius:10px 0 0 0; }
        .gel-compare-table th:last-child { border-radius:0 10px 0 0; }
        .gel-compare-table td { padding:14px 20px; font-size:13.5px; border-bottom:1px solid var(--gel-border); }
        .gel-compare-table tr:last-child td:first-child { border-radius:0 0 0 10px; }
        .gel-compare-table tr:last-child td:last-child { border-radius:0 0 10px 0; }
        .gel-compare-table td:first-child { font-weight:600; }
        .gel-compare-table .bi-check-lg { color:#22c55e; font-size:16px; }
        .gel-compare-table .bi-x-lg { color:#ef4444; font-size:14px; }

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

        .text-orange { color:var(--gel-primary); }
        @media (max-width:991px) { .gel-section { padding:60px 0; } }
    </style>
</head>
<body>

@include('partials.navbar')

<section class="gel-hero-inner">
    <div class="container" style="position:relative;z-index:1;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/"><i class="bi-house"></i></a></li>
                <li class="breadcrumb-item active">Tarifs &amp; Licences</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-lg-8">
                <h1>Des tarifs <span>transparents</span><br>pour chaque besoin</h1>
                <p>SaaS modulaire pour votre cabinet ou licence standalone du logiciel de comptabilité &mdash; choisissez la formule qui vous correspond.</p>
            </div>
        </div>
    </div>
</section>

<section class="gel-section">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-cloud-check"></i> SaaS modulaire</span>
                <h2 class="gel-section-title">Abonnements mensuels en FCFA</h2>
                <p class="gel-section-sub mx-auto">Activez uniquement les modules dont vous avez besoin. R&eacute;siliez &agrave; tout moment. Paiement par Mobile Money ou virement.</p>
            </div>
        </div>

        <div class="d-flex justify-content-center mb-5">
            <div class="gel-toggle-wrap" id="billingToggle">
                <button class="gel-toggle-btn active" data-period="monthly">Mensuel</button>
                <button class="gel-toggle-btn" data-period="annual">Annuel <span class="gel-toggle-badge">-20%</span></button>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                <div class="gel-pricing-card">
                    <div class="gel-pricing-name">Starter</div>
                    <div class="gel-pricing-price" data-monthly="9900" data-annual="95000">9 900 <span>FCFA/mois</span></div>
                    <p class="gel-pricing-desc">Pour ind&eacute;pendant ou micro-cabinet</p>
                    <ul class="gel-pricing-features">
                        <li><i class="bi-check-lg"></i> 1 module au choix</li>
                        <li><i class="bi-check-lg"></i> Jusqu'&agrave; 5 clients</li>
                        <li><i class="bi-check-lg"></i> 1 utilisateur</li>
                        <li><i class="bi-check-lg"></i> Support email</li>
                        <li><i class="bi-check-lg"></i> 1 Go stockage</li>
                    </ul>
                    <a href="/register" class="gel-btn-pricing gel-btn-pricing-outline"><i class="bi-rocket-takeoff"></i> Essai gratuit</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                <div class="gel-pricing-card featured">
                    <div class="gel-pricing-name">Pro</div>
                    <div class="gel-pricing-price" data-monthly="25000" data-annual="240000">25 000 <span>FCFA/mois</span></div>
                    <p class="gel-pricing-desc">Pour cabinet en pleine croissance</p>
                    <ul class="gel-pricing-features">
                        <li><i class="bi-check-lg"></i> 3 modules au choix</li>
                        <li><i class="bi-check-lg"></i> Jusqu'&agrave; 25 clients</li>
                        <li><i class="bi-check-lg"></i> 5 utilisateurs</li>
                        <li><i class="bi-check-lg"></i> Support prioritaire</li>
                        <li><i class="bi-check-lg"></i> 10 Go stockage</li>
                        <li><i class="bi-check-lg"></i> API &amp; int&eacute;grations</li>
                    </ul>
                    <a href="/register" class="gel-btn-pricing gel-btn-pricing-primary"><i class="bi-rocket-takeoff"></i> Essai gratuit</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                <div class="gel-pricing-card">
                    <div class="gel-pricing-name">Cabinet</div>
                    <div class="gel-pricing-price" data-monthly="49500" data-annual="475000">49 500 <span>FCFA/mois</span></div>
                    <p class="gel-pricing-desc">Pour cabinet &eacute;tabli ou multi-p&ocirc;les</p>
                    <ul class="gel-pricing-features">
                        <li><i class="bi-check-lg"></i> Tous les modules</li>
                        <li><i class="bi-check-lg"></i> Clients illimit&eacute;s</li>
                        <li><i class="bi-check-lg"></i> Utilisateurs illimit&eacute;s</li>
                        <li><i class="bi-check-lg"></i> Support d&eacute;di&eacute; 24/7</li>
                        <li><i class="bi-check-lg"></i> Stockage illimit&eacute;</li>
                        <li><i class="bi-check-lg"></i> API &amp; int&eacute;grations</li>
                        <li><i class="bi-check-lg"></i> Formation incluse</li>
                    </ul>
                    <a href="/register" class="gel-btn-pricing gel-btn-pricing-outline"><i class="bi-rocket-takeoff"></i> Essai gratuit</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section gel-section-alt">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-cpu"></i> Licence standalone</span>
                <h2 class="gel-section-title">Logiciel de Comptabilit&eacute;</h2>
                <p class="gel-section-sub mx-auto">Notre logiciel de comptabilit&eacute; complet conforme OHADA, en licence perp&eacute;tuelle.</p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                <div class="gel-license-card text-center">
                    <span class="gel-section-chip" style="margin-bottom:12px;">Individuel</span>
                    <h4>Starter</h4>
                    <div class="gel-license-price">150 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 24px;">Licence perp&eacute;tuelle &mdash; 1 poste</p>
                    <ul class="gel-pricing-features text-start">
                        <li><i class="bi-check-lg"></i> Plan comptable OHADA/SYSCOA</li>
                        <li><i class="bi-check-lg"></i> Saisie des journaux</li>
                        <li><i class="bi-check-lg"></i> Balance &amp; Grand Livre</li>
                        <li><i class="bi-check-lg"></i> Bilan &amp; CRP</li>
                        <li><i class="bi-check-lg"></i> 1 an de mises &agrave; jour</li>
                    </ul>
                    <a href="/catalogue" class="gel-btn-pricing gel-btn-pricing-primary" style="margin-top:20px;"><i class="bi-cart3"></i> Acheter</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                <div class="gel-license-card text-center featured">
                    <span class="gel-section-chip" style="margin-bottom:12px;">Cabinet</span>
                    <h4>Pro</h4>
                    <div class="gel-license-price">350 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 24px;">Licence perp&eacute;tuelle &mdash; 3 postes</p>
                    <ul class="gel-pricing-features text-start">
                        <li><i class="bi-check-lg"></i> Tout le plan Starter</li>
                        <li><i class="bi-check-lg"></i> Multi-utilisateurs (3 postes)</li>
                        <li><i class="bi-check-lg"></i> D&eacute;clarations fiscales</li>
                        <li><i class="bi-check-lg"></i> TVA &amp; &Eacute;tats statistiques</li>
                        <li><i class="bi-check-lg"></i> 2 ans de mises &agrave; jour</li>
                        <li><i class="bi-check-lg"></i> Support prioritaire</li>
                    </ul>
                    <a href="/catalogue" class="gel-btn-pricing gel-btn-pricing-primary" style="margin-top:20px;"><i class="bi-cart3"></i> Acheter</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                <div class="gel-license-card text-center">
                    <span class="gel-section-chip" style="margin-bottom:12px;">Entreprise</span>
                    <h4>Cabinet</h4>
                    <div class="gel-license-price">750 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 24px;">Licence perp&eacute;tuelle &mdash; postes illimit&eacute;s</p>
                    <ul class="gel-pricing-features text-start">
                        <li><i class="bi-check-lg"></i> Tout le plan Pro</li>
                        <li><i class="bi-check-lg"></i> Postes illimit&eacute;s</li>
                        <li><i class="bi-check-lg"></i> Modules compl&eacute;mentaires</li>
                        <li><i class="bi-check-lg"></i> Formation sur site</li>
                        <li><i class="bi-check-lg"></i> Mises &agrave; jour &agrave; vie</li>
                        <li><i class="bi-check-lg"></i> Support d&eacute;di&eacute; 24/7</li>
                    </ul>
                    <a href="/catalogue" class="gel-btn-pricing gel-btn-pricing-primary" style="margin-top:20px;"><i class="bi-cart3"></i> Acheter</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <span class="gel-section-chip"><i class="bi-list-check"></i> Comparatif</span>
                <h2 class="gel-section-title">SaaS vs Licence</h2>
                <p class="gel-section-sub mx-auto">Lequel choisir ? Tout d&eacute;pend de vos besoins.</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div style="overflow-x:auto;border:1px solid var(--gel-border);border-radius:12px;">
                    <table class="gel-compare-table">
                        <thead>
                            <tr>
                                <th>Caract&eacute;ristique</th>
                                <th style="text-align:center;">SaaS Mensuel</th>
                                <th style="text-align:center;">Licence Standalone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Paiement</td><td style="text-align:center;">Abonnement mensuel/annuel</td><td style="text-align:center;">Paiement unique</td></tr>
                            <tr><td>Modules disponibles</td><td style="text-align:center;">Tous (CRM, GED, Compta, ERP, RH...)</td><td style="text-align:center;">Comptabilit&eacute; uniquement</td></tr>
                            <tr><td>H&eacute;bergement</td><td style="text-align:center;"><i class="bi-check-lg"></i> Cloud (nous)</td><td style="text-align:center;">Sur votre poste</td></tr>
                            <tr><td>Mises &agrave; jour</td><td style="text-align:center;">Automatiques</td><td style="text-align:center;">Manuelles (incluse 1-2 ans)</td></tr>
                            <tr><td>Support</td><td style="text-align:center;">Inclus</td><td style="text-align:center;">Option payant apr&egrave;s garantie</td></tr>
                            <tr><td>Id&eacute;al pour</td><td style="text-align:center;">Cabinets en croissance, multi-utilisateurs</td><td style="text-align:center;">Ind&eacute;pendants, co&ucirc;t ma&icirc;tris&eacute;</td></tr>
                            <tr><td>Moyens de paiement</td><td style="text-align:center;">Mobile Money, Carte, Virement</td><td style="text-align:center;">Mobile Money, Virement</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gel-section" style="background:linear-gradient(135deg,#FF7900,#ff9a3c);padding:60px 0;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 style="font-size:clamp(1.4rem,2.5vw,2rem);font-weight:900;color:#fff;">Pr&ecirc;t &agrave; passer &agrave; l'action ?</h2>
                <p style="color:rgba(255,255,255,0.85);font-size:15px;margin-top:8px;">Essayez gratuitement pendant 14 jours. Sans engagement.</p>
                <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap;">
                    <a href="/register" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--gel-primary);font-size:14px;font-weight:700;padding:12px 28px;border-radius:6px;text-decoration:none;transition:all 0.3s;"><i class="bi-rocket-takeoff"></i> Essai gratuit</a>
                    <a href="/contact" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);color:#fff;font-size:14px;font-weight:700;padding:12px 28px;border-radius:6px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.4);transition:all 0.3s;"><i class="bi-whatsapp"></i> Nous contacter</a>
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
function formatFCFA(num) {
    return parseInt(num).toLocaleString('fr-FR');
}
const toggleBtns = document.querySelectorAll('.gel-toggle-btn');
toggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        toggleBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const period = btn.dataset.period;
        document.querySelectorAll('[data-monthly]').forEach(el => {
            const val = parseInt(el.dataset[period]);
            if (period === 'annual') {
                // Annual: show monthly equivalent (total ÷ 12)
                el.innerHTML = formatFCFA(Math.round(val / 12)) + ' <span>FCFA/mois</span>';
            } else {
                el.innerHTML = formatFCFA(val) + ' <span>FCFA/mois</span>';
            }
        });
    });
});
const animEls = document.querySelectorAll('.anim-fade-up');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('anim-visible'); observer.unobserve(entry.target); } });
}, { threshold: 0.12 });
animEls.forEach(el => observer.observe(el));
</script>
</body>
</html>