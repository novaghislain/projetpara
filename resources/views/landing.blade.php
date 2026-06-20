<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GEL Cabinet | Gestion de Cabinet</title>
    <meta name="description" content="CRM, GED, Pôles, Missions, Comptabilité, ERP — la gestion de cabinet tout-en-un.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preload" href="/images/hero_slide_1.jpg" as="image" fetchpriority="high">
    <link rel="preload" href="/images/hero_slide_2.jpg" as="image">
    <link rel="preload" href="/images/hero_slide_3.jpg" as="image">

    <style>
        :root {
            --gel-primary:     #FF7900;
            --gel-primary-hov: #e06700;
            --gel-primary-soft:rgba(255,121,0,0.06);
            --gel-blue:        #3B82F6;
            --gel-blue-dark:   #1E3A5F;
            --gel-blue-light:  #F0F7FF;
            --gel-blue-medium: #DBEAFE;
            --gel-blue-soft:   rgba(59,130,246,0.06);
            --gel-dark:        #111827;
            --gel-darker:      #0F172A;
            --gel-white:       #ffffff;
            --gel-light:       #F8FAFC;
            --gel-light2:      #F1F5F9;
            --gel-border:      #E2E8F0;
            --gel-border-light:#F1F5F9;
            --gel-muted:       #64748B;
            --gel-text:        #1E293B;
            --font-body:       'Inter', sans-serif;
            --font-heading:    'Outfit', sans-serif;
            --nav-height:      72px;
            --radius:          4px;
            --shadow-sm:       0 1px 3px rgba(0,0,0,0.04);
            --shadow-md:       0 4px 20px rgba(0,0,0,0.06);
            --shadow-lg:       0 12px 40px rgba(0,0,0,0.08);
            --transition:      0.25s cubic-bezier(0.4,0,0.2,1);
        }

        html { scroll-behavior: smooth; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            background: var(--gel-white);
            color: var(--gel-text);
            line-height: 1.6;
        }

        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading); font-weight: 700; }

        /* ============================================================
           SCROLL ANIMATIONS
        ============================================================ */
        .anim-fade-up { opacity: 0; transform: translateY(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-fade-left { opacity: 0; transform: translateX(-36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-fade-right { opacity: 0; transform: translateX(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
        .anim-scale { opacity: 0; transform: scale(0.92); transition: opacity 0.55s var(--transition), transform 0.55s var(--transition); }
        .anim-visible { opacity: 1 !important; transform: none !important; }
        .anim-preset { opacity: 1; transform: none; }
        .delay-1 { transition-delay: 0.1s !important; }
        .delay-2 { transition-delay: 0.2s !important; }
        .delay-3 { transition-delay: 0.3s !important; }
        .delay-4 { transition-delay: 0.4s !important; }
        .delay-5 { transition-delay: 0.5s !important; }
        .delay-6 { transition-delay: 0.6s !important; }

        /* ============================================================
           NAVBAR — Glassmorphism
        ============================================================ */
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

        .gel-hero {
            position: relative; width: 100%; height: 100vh;
            max-height: 750px; min-height: 480px;
            margin-top: 0; overflow: hidden;
        }
        .gel-slides-wrapper { position: relative; width: 100%; height: 100%; }
        .gel-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 1s ease; z-index: 1; }
        .gel-slide.active { opacity: 1; z-index: 2; }
        .gel-slide-bg { position: absolute; inset: 0; background-size: cover; background-position: center; background-repeat: no-repeat; background-color: #0F172A; transform: scale(1.08); transition: transform 6s ease; }
        @supports (background-image: image-set(url('x.webp') type('image/webp'))) {
            .gel-slide-bg { background-image: -webkit-image-set(var(--bg-webp) type('image/webp'), var(--bg-jpg) type('image/jpeg')); background-image: image-set(var(--bg-webp) type('image/webp'), var(--bg-jpg) type('image/jpeg')); }
        }
        .gel-slide.active .gel-slide-bg { transform: scale(1); }
        .gel-slide-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(15,23,42,0.75) 0%, rgba(15,23,42,0.40) 100%); }
        .gel-slide-content { position: relative; z-index: 3; height: 100%; display: flex; align-items: center; padding: 0 24px; }
        .gel-slide-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 6px 16px; border-radius: 100px;
            font-size: 12px; font-weight: 600;
            color: rgba(255,255,255,0.9); margin-bottom: 18px;
            letter-spacing: 0.02em;
        }
        .gel-slide-badge i { color: var(--gel-primary); font-size: 13px; }
        .gel-slide-content h1 { font-family: var(--font-heading); font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 900; color: #fff; letter-spacing: -1px; line-height: 1.1; }
        .gel-slide-content h1 span { color: var(--gel-primary); }
        .gel-slide-sub { font-size: clamp(14px, 1.1vw, 16px); color: rgba(255,255,255,0.7); max-width: 480px; margin-top: 14px; line-height: 1.7; }
        .gel-slide-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .gel-slide-btn { display: inline-flex; align-items: center; gap: 7px; padding: 12px 26px; border-radius: var(--radius); font-size: 14px; font-weight: 700; text-decoration: none; transition: all 0.3s; }
        .gel-slide-btn-primary { background: var(--gel-primary); color: #fff; }
        .gel-slide-btn-primary:hover { background: var(--gel-primary-hov); color: #fff; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,121,0,0.4); }
        .gel-slide-btn-outline { background: rgba(255,255,255,0.1); backdrop-filter: blur(8px); border: 1.5px solid rgba(255,255,255,0.3); color: #fff; }
        .gel-slide-btn-outline:hover { background: rgba(255,255,255,0.2); color: #fff; transform: translateY(-2px); }

        .gel-hero-cta-float { position: absolute; top: 20px; right: 24px; z-index: 10; display: flex; align-items: center; gap: 6px; padding: 8px 18px; background: rgba(255,255,255,0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); border-radius: 100px; color: rgba(255,255,255,0.85); font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
        .gel-hero-cta-float:hover { background: rgba(255,255,255,0.2); color: #fff; transform: translateY(-2px); }

        .gel-carousel-prev, .gel-carousel-next { position: absolute; top: 50%; transform: translateY(-50%); z-index: 10; width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .gel-carousel-prev { left: 20px; }
        .gel-carousel-next { right: 20px; }
        .gel-carousel-prev:hover, .gel-carousel-next:hover { background: rgba(255,255,255,0.2); border-color: var(--gel-primary); color: var(--gel-primary); transform: translateY(-50%) scale(1.1); }

        .gel-carousel-dots { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 10; display: flex; gap: 10px; }
        .gel-dot { width: 8px; height: 8px; border-radius: 50%; border: none; background: rgba(255,255,255,0.35); cursor: pointer; transition: all 0.3s; padding: 0; }
        .gel-dot.active { background: var(--gel-primary); width: 24px; border-radius: 4px; }

        @media (max-width: 575px) {
            .gel-hero { max-height: 520px; min-height: 420px; }
            .gel-carousel-prev, .gel-carousel-next { display: none; }
            .gel-slide-actions { flex-direction: column; }
            .gel-slide-btn { justify-content: center; }
            .gel-hero-cta-float { top: 12px; right: 12px; font-size: 11px; padding: 6px 14px; }
        }

        .gel-section { padding: 80px 0; }
        .gel-section-alt { background: var(--gel-light); }
        .gel-section-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--gel-white);
            color: var(--gel-primary);
            font-size: 11px; font-weight: 700; padding: 4px 14px;
            border-radius: 100px; text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 14px;
            border: 1.5px solid rgba(255,121,0,0.2);
        }
        .gel-section-chip i { font-size: 12px; }
        .gel-section-title {
            font-family: var(--font-heading);
            font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800;
            color: var(--gel-dark); letter-spacing: -0.5px; line-height: 1.2;
            margin-bottom: 14px;
        }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); max-width: 540px; line-height: 1.7; }
        .gel-section-sub.mx-auto { margin-left: auto; margin-right: auto; }

        .gel-stats-band {
            background: var(--gel-white);
            padding: 48px 0;
            border-bottom: 1px solid var(--gel-border);
        }
        .gel-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .gel-stat-item { text-align: center; padding: 8px; }
        .gel-stat-number {
            font-family: var(--font-heading);
            font-size: 2.2rem; font-weight: 900;
            color: var(--gel-primary);
            display: block;
            line-height: 1.1;
        }
        .gel-stat-label {
            font-size: 13px; font-weight: 500;
            color: var(--gel-muted);
            margin-top: 6px;
            letter-spacing: 0.02em;
        }

        @media (max-width: 767px) {
            .gel-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .gel-stat-number { font-size: 1.8rem; }
        }

        .gel-why-card {
            background: var(--gel-white);
            border: 1px solid var(--gel-border);
            border-radius: 10px;
            padding: 28px 24px;
            height: 100%;
            transition: transform var(--transition), box-shadow var(--transition);
        }
        .gel-why-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .gel-why-icon {
            width: 44px; height: 44px;
            background: var(--gel-light);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: var(--gel-primary);
            font-size: 20px;
            margin-bottom: 14px;
            transition: background var(--transition), color var(--transition);
        }
        .gel-why-card:hover .gel-why-icon { background: var(--gel-primary); color: #fff; }
        .gel-why-card h6 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
        .gel-why-card p { font-size: 13px; color: var(--gel-muted); margin: 0; line-height: 1.6; }

        .gel-service-card {
            background: var(--gel-white);
            border: 1px solid var(--gel-border);
            border-radius: 10px;
            padding: 28px 24px;
            height: 100%;
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        }
        .gel-service-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--gel-primary);
        }
        .gel-service-icon-wrap {
            width: 44px; height: 44px;
            background: var(--gel-light2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            transition: background var(--transition);
        }
        .gel-service-card:hover .gel-service-icon-wrap { background: var(--gel-primary-soft); }
        .gel-service-icon-wrap svg { width: 22px; height: 22px; }
        .gel-service-card h5 { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
        .gel-service-card { display: flex; flex-direction: column; }
        .gel-service-card p { font-size: 13px; color: var(--gel-muted); margin-bottom: 14px; line-height: 1.7; flex: 1; }
        .gel-service-card .gel-service-arrow { margin-top: auto; }
        .gel-service-arrow { font-size: 12.5px; font-weight: 600; color: var(--gel-primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: gap var(--transition); }
        .gel-service-arrow:hover { gap: 8px; color: var(--gel-primary-hov); }

        /* ============================================================
           TESTIMONIALS
        ============================================================ */
        .gel-testimonial-card {
            background: var(--gel-white);
            border: 1px solid var(--gel-border);
            border-radius: 12px;
            padding: 32px 28px;
            height: 100%;
            position: relative;
            transition: transform var(--transition), box-shadow var(--transition);
        }
        .gel-testimonial-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .gel-testimonial-card::before {
            content: '\201C';
            font-family: Georgia, serif;
            font-size: 56px; line-height: 1;
            color: var(--gel-primary);
            opacity: 0.2;
            position: absolute; top: 12px; left: 20px;
        }
        .gel-testimonial-text {
            font-size: 14px; color: var(--gel-text);
            line-height: 1.7; font-style: italic;
            margin: 8px 0 18px;
        }
        .gel-testimonial-author { display: flex; align-items: center; gap: 12px; }
        .gel-testimonial-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: var(--gel-light2);
            display: flex; align-items: center; justify-content: center;
            color: var(--gel-muted); font-size: 18px;
            flex-shrink: 0;
        }
        .gel-testimonial-name { font-size: 13px; font-weight: 700; }
        .gel-testimonial-role { font-size: 11.5px; color: var(--gel-muted); }

        .gel-value-card {
            text-align: center;
            padding: 28px 16px;
        }
        .gel-value-icon {
            width: 56px; height: 56px;
            background: var(--gel-light);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
            color: var(--gel-primary);
            transition: background var(--transition), color var(--transition), transform var(--transition);
        }
        .gel-value-card:hover .gel-value-icon {
            background: var(--gel-primary);
            color: #fff;
            transform: scale(1.08);
        }
        .gel-value-card h6 { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .gel-value-card p { font-size: 13px; color: var(--gel-muted); margin: 0; line-height: 1.6; }

        .gel-features-light {
            background: var(--gel-white);
            padding: 80px 0;
        }
        .gel-feature-item {
            display: flex; align-items: flex-start; gap: 14px;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--gel-border);
            background: var(--gel-white);
            height: 100%;
            transition: border-color var(--transition), box-shadow var(--transition), transform var(--transition);
        }
        .gel-feature-item:hover {
            border-color: var(--gel-primary);
            box-shadow: var(--shadow-sm);
            transform: translateY(-2px);
        }
        .gel-feature-icon {
            width: 40px; height: 40px; flex-shrink: 0;
            background: var(--gel-light);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--gel-primary);
            font-size: 18px;
            transition: background var(--transition), color var(--transition);
        }
        .gel-feature-item:hover .gel-feature-icon { background: var(--gel-primary); color: #fff; }
        .gel-feature-item h6 { font-size: 14px; font-weight: 700; color: var(--gel-dark); margin-bottom: 4px; }
        .gel-feature-item p { font-size: 12.5px; color: var(--gel-muted); margin: 0; line-height: 1.55; }

        .gel-pourqui-card { text-align:center; padding:28px 20px; background:var(--gel-white); border:1px solid var(--gel-border); border-radius:12px; height:100%; transition: all var(--transition); }
        .gel-pourqui-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .gel-pourqui-icon { width:56px; height:56px; background:var(--gel-light); border-radius:16px; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:var(--gel-primary); font-size:26px; transition: all var(--transition); }
        .gel-pourqui-card:hover .gel-pourqui-icon { background: var(--gel-primary); color: #fff; }

        .gel-blog-card { background:var(--gel-white); border:1px solid var(--gel-border); border-radius:12px; overflow:hidden; height:100%; transition: all var(--transition); }
        .gel-blog-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .gel-blog-card-body { padding: 24px; }
        .gel-blog-tag { font-size:11px; font-weight:600; color:var(--gel-primary); text-transform:uppercase; letter-spacing:0.06em; }
        .gel-blog-card h5 { font-size:15px; font-weight:700; margin:10px 0 8px; }
        .gel-blog-card p { font-size:13px; color:var(--gel-muted); line-height:1.7; margin:0; }
        .gel-blog-link { display:inline-flex; align-items:center; gap:4px; font-size:12.5px; font-weight:600; color:var(--gel-primary); text-decoration:none; margin-top:14px; transition: gap var(--transition); }
        .gel-blog-link:hover { gap: 8px; }

        .gel-process-step { text-align: center; padding: 20px; }
        .gel-process-num {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: var(--gel-white);
            border: 2px solid var(--gel-primary);
            color: var(--gel-primary);
            font-family: var(--font-heading);
            font-size: 20px; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            transition: background var(--transition), color var(--transition), transform var(--transition);
        }
        .gel-process-step:hover .gel-process-num { background: var(--gel-primary); color: #fff; transform: scale(1.08); }
        .gel-process-step h5 { font-size: 15px; font-weight: 700; color: var(--gel-dark); margin-bottom: 8px; }
        .gel-process-step p { font-size: 13px; color: var(--gel-muted); line-height: 1.6; margin: 0; }

        .gel-cta-band {
            background: linear-gradient(135deg, var(--gel-primary) 0%, #ff9a3c 100%);
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        .gel-cta-band::before { content: ''; position: absolute; top: -60px; right: -60px; width: 280px; height: 280px; background: rgba(255,255,255,0.08); border-radius: 50%; }
        .gel-cta-band::after { content: ''; position: absolute; bottom: -80px; left: -40px; width: 200px; height: 200px; background: rgba(255,255,255,0.06); border-radius: 50%; }
        .gel-cta-band h2 { font-size: clamp(1.5rem, 2.5vw, 2rem); font-weight: 900; color: #fff; letter-spacing: -0.5px; }
        .gel-cta-band p { color: rgba(255,255,255,0.85); font-size: 15px; margin-top: 8px; }
        .gel-btn-white { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: var(--gel-primary); font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: var(--radius); text-decoration: none; transition: all 0.3s; }
        .gel-btn-white:hover { background: var(--gel-dark); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,0.15); }

        .gel-contact-info {
            padding: 36px;
            background: var(--gel-light);
            border: 1px solid var(--gel-border);
            border-radius: 10px;
            height: 100%;
        }
        .gel-contact-info h3 { font-size: 22px; margin-bottom: 8px; color: var(--gel-dark); }
        .gel-contact-info > p { font-size: 14px; color: var(--gel-muted); line-height: 1.7; }
        .gel-contact-detail { display: flex; align-items: flex-start; gap: 12px; margin-top: 20px; }
        .gel-contact-detail-icon { width: 38px; height: 38px; flex-shrink: 0; background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--gel-primary); font-size: 16px; }
        .gel-contact-detail h6 { font-size: 11px; font-weight: 600; color: var(--gel-muted); text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 2px; }
        .gel-contact-detail p { font-size: 14px; font-weight: 500; color: var(--gel-dark); margin: 0; }

        .gel-form-wrap { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 10px; padding: 36px; }
        .gel-form-title { font-size: 20px; font-weight: 800; margin-bottom: 6px; }
        .gel-form-sub { font-size: 13px; color: var(--gel-muted); margin-bottom: 28px; }
        .gel-form-label { font-size: 12px; font-weight: 600; color: var(--gel-text); margin-bottom: 6px; display: block; }
        .gel-form-control { width: 100%; border: 1.5px solid var(--gel-border); border-radius: var(--radius); padding: 11px 14px; font-size: 13.5px; font-family: var(--font-body); color: var(--gel-text); background: var(--gel-white); transition: border-color 0.2s, box-shadow 0.2s; outline: none; }
        .gel-form-control:focus { border-color: var(--gel-primary); box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
        .gel-form-control::placeholder { color: #c0cbd8; }
        .gel-btn-submit { width: 100%; background: var(--gel-primary); color: #fff; font-size: 14px; font-weight: 700; padding: 13px; border: none; border-radius: var(--radius); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; margin-top: 8px; }

        .gel-btn-pricing { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:var(--radius); font-size:13px; font-weight:700; text-decoration:none; transition:all var(--transition); cursor:pointer; }
        .gel-btn-pricing-primary { background:var(--gel-primary); color:#fff; }
        .gel-btn-pricing-primary:hover { background:var(--gel-primary-hov); color:#fff; transform:translateY(-2px); box-shadow:0 6px 20px rgba(255,121,0,0.3); }

        .gel-payment-wrap { display:flex; gap:8px; flex-wrap:wrap; }
        .gel-payment-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; background:var(--gel-light); border:1px solid var(--gel-border); border-radius:6px; color:var(--gel-muted); }
        .gel-btn-submit:hover { background: var(--gel-primary-hov); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(255,121,0,0.35); }

        .gel-footer { background: #0A1628; padding: 64px 0 0; border-top: 3px solid var(--gel-primary); }
        .gel-footer-brand, .gel-footer-brand-name { font-family: var(--font-heading); font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .gel-footer-sub, .gel-footer-brand-sub { font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; }
        .gel-footer-desc { font-size: 13px; color: rgba(255,255,255,0.45); line-height: 1.7; margin-top: 16px; max-width: 280px; }
        .gel-footer-social { display: flex; gap: 10px; margin-top: 20px; }
        .gel-social-btn { width: 36px; height: 36px; border-radius: 6px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.55); font-size: 15px; text-decoration: none; transition: all var(--transition); }
        .gel-social-btn:hover { background: var(--gel-primary); border-color: var(--gel-primary); color: #fff; transform: translateY(-2px); }
        .gel-footer-heading { font-family: var(--font-heading); font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 18px; }
        .gel-footer-links { list-style: none; padding: 0; }
        .gel-footer-links li { margin-bottom: 10px; }
        .gel-footer-links a { font-size: 13.5px; color: rgba(255,255,255,0.45); text-decoration: none; transition: color var(--transition); display: flex; align-items: center; gap: 6px; }
        .gel-footer-links a:hover { color: var(--gel-primary); }
        .gel-footer-links a::before { content: '\203A'; color: var(--gel-primary); font-size: 16px; }
        .gel-footer-bottom { border-top: 1px solid rgba(255,255,255,0.07); padding: 20px 0; margin-top: 48px; }
        .gel-footer-bottom p { font-size: 12px; color: rgba(255,255,255,0.3); margin: 0; }
        .gel-footer-bottom a { font-size: 12px; color: rgba(255,255,255,0.3); text-decoration: none; transition: color var(--transition); margin-left: 16px; }
        .gel-footer-bottom a:hover { color: var(--gel-primary); }

        .text-orange { color: var(--gel-primary); }
        .bg-orange { background: var(--gel-primary); }

        @media (max-width: 991px) {
            .gel-section { padding: 60px 0; }
            .gel-features-light { padding: 60px 0; }
            .gel-cta-band { padding: 48px 0; }
            .gel-hero { max-height: 600px; }
        }
        @media (max-width: 575px) {
            .gel-contact-info { padding: 24px 20px; }
            .gel-form-wrap { padding: 24px 20px; }
        }
    </style>
</head>
<body>

        <!-- navbar -->
@include('partials.navbar')

    <section class="gel-hero anim-preset" id="hero">
        <div class="gel-slides-wrapper" id="gelSlides">

            <!-- Slide 1 -->
            <div class="gel-slide active" id="slide-0">
                <div class="gel-slide-bg" style="background-image: url('/images/hero_slide_1.jpg'); --bg-webp: url('/images/hero_slide_1.webp'); --bg-jpg: url('/images/hero_slide_1.jpg');"></div>
                <div class="gel-slide-overlay"></div>
                <div class="gel-slide-content">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-7">
                                <span class="gel-slide-badge"><i class="bi-lightning-fill"></i> La gestion de votre cabinet, réinventée</span>
                                <h1>Gérez votre cabinet.<br><span>Vendez votre expertise.</span></h1>
                                <p class="gel-slide-sub">CRM, GED, Comptabilité OHADA, ERP, RH — la plateforme SaaS tout-en-un pour les cabinets d'expertise comptable au Bénin.</p>
                                <div class="gel-slide-actions">
                                    <a href="/register" class="gel-slide-btn gel-slide-btn-primary">
                                        <i class="bi-rocket-takeoff"></i> Démarrer l'essai gratuit
                                    </a>
                                    <a href="/nos-modules" class="gel-slide-btn gel-slide-btn-outline">
                                        <i class="bi-grid-3x3-gap"></i> Voir la démo
                                    </a>
                                    <a href="/login" class="gel-slide-btn gel-slide-btn-outline">
                                        <i class="bi-box-arrow-in-right"></i> Espace Client
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="gel-slide" id="slide-1">
                <div class="gel-slide-bg" style="background-image: url('/images/hero_slide_2.jpg'); --bg-webp: url('/images/hero_slide_2.webp'); --bg-jpg: url('/images/hero_slide_2.jpg');"></div>
                <div class="gel-slide-overlay"></div>
                <div class="gel-slide-content">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-7">
                                <span class="gel-slide-badge"><i class="bi-cpu"></i> Logiciel conforme OHADA</span>
                                <h1>Le logiciel de comptabilité<br><span>pour les pros</span></h1>
                                <p class="gel-slide-sub">Plan SYSCOA, journaux, bilan, CRP, déclarations fiscales — conforme aux normes OHADA. Licence standalone ou en SaaS.</p>
                                <div class="gel-slide-actions">
                                    <a href="/logiciel-comptabilite" class="gel-slide-btn gel-slide-btn-primary">
                                        <i class="bi-cpu"></i> Découvrir le logiciel
                                    </a>
                                    <a href="/tarifs" class="gel-slide-btn gel-slide-btn-outline">
                                        <i class="bi-currency-dollar"></i> Voir les tarifs
                                    </a>
                                    <a href="/login" class="gel-slide-btn gel-slide-btn-outline">
                                        <i class="bi-box-arrow-in-right"></i> Accéder à mon espace
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="gel-slide" id="slide-2">
                <div class="gel-slide-bg" style="background-image: url('/images/hero_slide_3.jpg'); --bg-webp: url('/images/hero_slide_3.webp'); --bg-jpg: url('/images/hero_slide_3.jpg');"></div>
                <div class="gel-slide-overlay"></div>
                <div class="gel-slide-content">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-7">
                                <span class="gel-slide-badge"><i class="bi-shield-check"></i> Sécurité &amp; Conformité</span>
                                <h1>Des données<br><span>toujours protégées</span></h1>
                                <p class="gel-slide-sub">Authentification multi-facteurs, rôles personnalisés, chiffrement et audit : votre cabinet est protégé à chaque niveau.</p>
                                <div class="gel-slide-actions">
                                    <a href="/register" class="gel-slide-btn gel-slide-btn-primary">
                                        <i class="bi-rocket-takeoff"></i> Commencer maintenant
                                    </a>
                                    <a href="/login" class="gel-slide-btn gel-slide-btn-outline">
                                        <i class="bi-box-arrow-in-right"></i> Accéder à mon espace
                                    </a>
                                    <a href="/contact" class="gel-slide-btn gel-slide-btn-outline">
                                        <i class="bi-info-circle"></i> Nous contacter
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a href="/login" class="gel-hero-cta-float d-none d-md-flex">
            <i class="bi-speedometer2"></i> Accéder à mon espace
        </a>

        <button class="gel-carousel-prev" id="prevSlide" aria-label="Slide précédent">
            <i class="bi-chevron-left"></i>
        </button>
        <button class="gel-carousel-next" id="nextSlide" aria-label="Slide suivant">
            <i class="bi-chevron-right"></i>
        </button>

        <div class="gel-carousel-dots" id="carouselDots">
            <button class="gel-dot active" data-slide="0" aria-label="Slide 1"></button>
            <button class="gel-dot" data-slide="1" aria-label="Slide 2"></button>
            <button class="gel-dot" data-slide="2" aria-label="Slide 3"></button>
        </div>
    </section>

    <!-- stats -->
    <div class="gel-stats-band">
        <div class="container">
            <div class="gel-stats-grid">
                <div class="gel-stat-item anim-fade-up">
                    <span class="gel-stat-number" data-target="9">0</span>
                    <div class="gel-stat-label">Modules intégrés</div>
                </div>
                <div class="gel-stat-item anim-fade-up delay-1">
                    <span class="gel-stat-number" data-target="1500" data-suffix="+">0</span>
                    <div class="gel-stat-label">Clients accompagnés</div>
                </div>
                <div class="gel-stat-item anim-fade-up delay-2">
                    <span class="gel-stat-number" data-target="99" data-suffix="%">0</span>
                    <div class="gel-stat-label">Disponibilité</div>
                </div>
                <div class="gel-stat-item anim-fade-up delay-3">
                    <span class="gel-stat-number" data-target="3">0</span>
                    <div class="gel-stat-label">Pays couverts</div>
                </div>
            </div>
        </div>
    </div>

    <!-- pour qui -->
    <section class="gel-section" style="padding:60px 0 40px;">
        <div class="container">
            <div class="row justify-content-center text-center mb-4">
                <div class="col-lg-7">
                    <h2 class="gel-section-title anim-fade-up delay-1">Pour qui ?</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Que vous soyez expert-comptable, dirigeant d'entreprise ou indépendant, nous avons la solution qu'il vous faut.</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-pourqui-card">
                        <div class="gel-pourqui-icon"><i class="bi-bank2"></i></div>
                        <h5 style="font-size:15px;font-weight:700;margin-bottom:8px;">Cabinet comptable</h5>
                        <p style="font-size:13px;color:var(--gel-muted);margin:0;line-height:1.6;">Vous gérez un cabinet d'expertise comptable, juridique ou fiscal. Centralisez vos missions, vos dossiers et votre équipe sur une seule plateforme.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-pourqui-card">
                        <div class="gel-pourqui-icon"><i class="bi-building"></i></div>
                        <h5 style="font-size:15px;font-weight:700;margin-bottom:8px;">Entreprise cliente</h5>
                        <p style="font-size:13px;color:var(--gel-muted);margin:0;line-height:1.6;">Vous êtes une entreprise cliente de GEL Cabinet. Accédez à votre portail, vos documents comptables, vos factures et votre suivi RH en temps réel.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-pourqui-card">
                        <div class="gel-pourqui-icon"><i class="bi-person"></i></div>
                        <h5 style="font-size:15px;font-weight:700;margin-bottom:8px;">Particulier</h5>
                        <p style="font-size:13px;color:var(--gel-muted);margin:0;line-height:1.6;">Suivez vos déclarations fiscales, vos documents personnels et échangez avec votre comptable via notre portail client sécurisé.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- pourquoi -->
    <section class="gel-section gel-section-alt">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <h2 class="gel-section-title anim-fade-up delay-1">Ce qu'on vous propose</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Six bonnes raisons de travailler avec GEL Cabinet.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-why-card">
                        <div class="gel-why-icon"><i class="bi-boxes"></i></div>
                        <h6>Une plateforme, tout-en-un</h6>
                        <p>CRM, GED, Comptabilité, ERP, Missions — plus besoin de jongler entre 5 outils. Tout est intégré.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-why-card">
                        <div class="gel-why-icon"><i class="bi-clock-history"></i></div>
                        <h6>Gagnez du temps</h6>
                        <p>Fini les tâches répétitives : vos données sont centralisées, votre temps est libéré.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-why-card">
                        <div class="gel-why-icon"><i class="bi-shield-check"></i></div>
                        <h6>Sécurité & Conformité</h6>
                        <p>Authentification, chiffrement, permissions et audit — vos données sont sous contrôle.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-why-card">
                        <div class="gel-why-icon"><i class="bi-people-fill"></i></div>
                        <h6>Multi-utilisateurs & Rôles</h6>
                        <p>Des comptes pour toute l'équipe avec des rôles personnalisés. Chacun voit ce qui le concerne.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-why-card">
                        <div class="gel-why-icon"><i class="bi-cloud-arrow-up"></i></div>
                        <h6>Accessible partout</h6>
                        <p>100% cloud : ordinateur, tablette ou smartphone — votre cabinet vous suit.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-why-card">
                        <div class="gel-why-icon"><i class="bi-headset"></i></div>
                        <h6>Support dédié 24/7</h6>
                        <p>Une équipe à votre écoute, une assistance en ligne et une formation à votre déploiement.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- services -->
    <section id="services" class="gel-section">
        <div class="container">
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 text-center">
                    <h2 class="gel-section-title anim-fade-up delay-1">Tout ce qu'il faut pour<br>votre cabinet</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2" style="max-width:680px;">Tous les services comptables, fiscaux, juridiques et RH pour vous accompagner au quotidien dans la gestion de votre entreprise. Épargnez du temps et de l'argent. Faites équipe avec des techniciens et des professionnels passionnés et dynamiques, prêts à transformer votre façon de travailler.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Production d'états financiers -->
                <div class="col-md-6 col-sm-6 anim-fade-up delay-1">
                    <div class="gel-service-card">
                        <div class="gel-service-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                                <path d="M6 8h2l2 4 2-6 2 6 2-4 2 2"/>
                            </svg>
                        </div>
                        <h5>Production d'états financiers</h5>
                        <p>Bénéficiez d'un accompagnement comptable complet pour piloter sereinement votre entreprise au quotidien. Notre équipe de techniciens et de comptables professionnels, passionnés et dynamiques, vous aide à gagner du temps et à réduire vos coûts. Nous produisons vos états financiers avec le niveau d'assurance adapté aux attentes de vos partenaires, élément clé de tout travail fiscal et financier. Faites confiance à nos experts CPA pour renforcer la transparence et la crédibilité de votre gestion.</p>
                        <a href="/services/comptabilite" class="gel-service-arrow">En savoir plus <i class="bi-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Impôts -->
                <div class="col-md-6 col-sm-6 anim-fade-up delay-2">
                    <div class="gel-service-card">
                        <div class="gel-service-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </div>
                        <h5>Impôts</h5>
                        <p>Confiez vos déclarations fiscales à de véritables professionnels. Nous prenons en charge les déclarations d'impôts des particuliers, des entreprises et des fiducies, pour vous garantir une parfaite conformité avec les autorités fiscales. Chaque déclaration est préparée par des comptables expérimentés et rigoureusement révisée par des fiscalistes. Un rapport d'impôt signé par un bureau comptable professionnel renforce considérablement votre crédibilité auprès des administrations publiques.</p>
                        <a href="/services/fiscal" class="gel-service-arrow">En savoir plus <i class="bi-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Fiscalité -->
                <div class="col-md-6 col-sm-6 anim-fade-up delay-3">
                    <div class="gel-service-card">
                        <div class="gel-service-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7v1c0 6.1 3 12 10 15 7-3 10-8.9 10-15V7l-10-5z"/>
                                <path d="M12 6v10"/>
                                <path d="M8 9h8"/>
                            </svg>
                        </div>
                        <h5>Fiscalité</h5>
                        <p>Optimisez votre stratégie fiscale avec un accompagnement sur mesure. Les impôts représentent souvent la troisième charge la plus lourde pour un entrepreneur — nos fiscalistes vous aident à la réduire intelligemment. Que ce soit pour l'achat ou la vente d'une entreprise, la planification de la relève, l'impôt au décès, la TPS et la TVQ, notre équipe vous propose des solutions avantageuses adaptées à chaque situation.</p>
                        <a href="/services/fiscal" class="gel-service-arrow">En savoir plus <i class="bi-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Ressources Humaines -->
                <div class="col-md-6 col-sm-6 anim-fade-up delay-1">
                    <div class="gel-service-card">
                        <div class="gel-service-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <h5>Ressources Humaines</h5>
                        <p>Valorisez votre capital humain avec une gestion RH professionnelle et personnalisée. Bien qu'il n'apparaisse pas au bilan, le capital humain est souvent l'actif le plus précieux d'une entreprise. Nos conseillers en ressources humaines vous accompagnent pour recruter, fidéliser et manager vos équipes avec des solutions adaptées. Face à la complexité des décisions liées à la gestion du personnel, laissez nos experts vous épauler au quotidien.</p>
                        <a href="/services/social-paie" class="gel-service-arrow">En savoir plus <i class="bi-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Stratégie -->
                <div class="col-md-6 col-sm-6 anim-fade-up delay-2">
                    <div class="gel-service-card">
                        <div class="gel-service-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6L23 6H6"/>
                            </svg>
                        </div>
                        <h5>Stratégie</h5>
                        <p>Bâtissez un avantage concurrentiel durable grâce à une stratégie d'entreprise solide. Le succès en affaires n'est jamais le fruit du hasard — les entreprises les plus performantes sont celles qui élaborent une stratégie claire et la communiquent efficacement. Pourtant, nombreux sont les entrepreneurs qui négligent cette étape cruciale. Pour la planification stratégique, la rédaction de plans d'affaires, la recherche de financement et les conseils aux dirigeants, fiez-vous à l'expertise de nos spécialistes.</p>
                        <a href="/services/commercial" class="gel-service-arrow">En savoir plus <i class="bi-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Juridique -->
                <div class="col-md-6 col-sm-6 anim-fade-up delay-3">
                    <div class="gel-service-card">
                        <div class="gel-service-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7v1c0 6.1 3 12 10 15 7-3 10-8.9 10-15V7l-10-5z"/>
                                <path d="M12 6v10"/>
                                <path d="M8 9h8"/>
                            </svg>
                        </div>
                        <h5>Juridique</h5>
                        <p>Constitution de sociétés, formalités administratives, rédaction de contrats et veille juridique. Notre équipe vous accompagne dans toutes vos démarches légales pour sécuriser vos décisions et rester en conformité avec la réglementation en vigueur. De la création d'entreprise aux contentieux, bénéficiez d'un accompagnement sur mesure.</p>
                        <a href="/services/juridique" class="gel-service-arrow">En savoir plus <i class="bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 anim-fade-up">
                <a href="/nos-services" class="gel-slide-btn gel-slide-btn-primary" style="display:inline-flex;">
                    <i class="bi-arrow-right-circle"></i> Tous nos services
                </a>
            </div>
        </div>
    </section>

    <!-- témoignages -->
    <section class="gel-section gel-section-alt">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <h2 class="gel-section-title anim-fade-up delay-1">Ce qu'ils disent de nous</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Quelques retours de cabinets qui utilisent GEL Cabinet.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 anim-fade-up delay-1">
                    <div class="gel-testimonial-card">
                        <p class="gel-testimonial-text">« GEL Cabinet a complètement transformé notre organisation. Plus besoin de 4 logiciels différents, tout est accessible depuis une seule plateforme. Un gain de temps considérable. »</p>
                        <div class="gel-testimonial-author">
                            <div class="gel-testimonial-avatar"><i class="bi-person-fill"></i></div>
                            <div>
                                <div class="gel-testimonial-name">Isabelle Kpossou</div>
                                <div class="gel-testimonial-role">Directrice, Cabinet BSA</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 anim-fade-up delay-2">
                    <div class="gel-testimonial-card">
                        <p class="gel-testimonial-text">« La gestion des missions et la GED intégrée nous ont permis de réduire de 30% le temps passé sur la documentation. L'accompagnement de l'équipe GEL est remarquable. »</p>
                        <div class="gel-testimonial-author">
                            <div class="gel-testimonial-avatar"><i class="bi-person-fill"></i></div>
                            <div>
                                <div class="gel-testimonial-name">Marc Tchobo</div>
                                <div class="gel-testimonial-role">Expert-comptable, Cabinet MT</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 anim-fade-up delay-3">
                    <div class="gel-testimonial-card">
                        <p class="gel-testimonial-text">« Nous gérons 5 sociétés avec des pôles différents. GEL Cabinet nous offre une vue globale et centralisée que nous n'avions jamais eue auparavant. Indispensable au quotidien. »</p>
                        <div class="gel-testimonial-author">
                            <div class="gel-testimonial-avatar"><i class="bi-person-fill"></i></div>
                            <div>
                                <div class="gel-testimonial-name">Awa Sèna</div>
                                <div class="gel-testimonial-role">CEO, Groupe AS Holding</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- modules -->
    <section id="modules" class="gel-features-light">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <span class="gel-section-chip anim-fade-up"><i class="bi-grid-3x3-gap"></i> Modules</span>
                    <h2 class="gel-section-title anim-fade-up delay-1">Tous les modules pour votre cabinet</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">De la gestion client à la comptabilité en passant par les RH et l'ERP, activez les modules selon vos besoins.</p>
                </div>
            </div>

            <div class="row g-3">
                @foreach([
                    ['bi-people-fill','CRM Clients','Gérez vos entreprises, contacts et relances. Historique complet des échanges et suivi des opportunités.'],
                    ['bi-diagram-3-fill','Pôles & Missions','Structurez votre cabinet par départements. Suivez les missions en temps réel avec vos équipes.'],
                    ['bi-folder2-open','GED','Stockez, indexez et retrouvez tous vos documents en un clic. Versionning et partage sécurisé.'],
                    ['bi-calculator-fill','Comptabilité','Plan comptable SYSCOA/OHADA, journaux, balance, bilan, CRP. Déclarations fiscales intégrées.'],
                    ['bi-graph-up-arrow','ERP Intégré','Stocks, facturation, RH, paie, trésorerie — un ERP complet synchronisé avec votre comptabilité.'],
                    ['bi-file-earmark-text','Juridique','Constitution de sociétés, formalités, contrats et veille juridique. Suivi des dossiers contentieux.'],
                    ['bi-robot','Assistant IA','Analyse automatique de documents, suggestions intelligentes et classification aidée par intelligence artificielle.'],
                    ['bi-chat-dots','Communication','Messagerie intégrée, notifications temps réel, alerts et rappels automatiques pour votre équipe.'],
                    ['bi-shield-check','Sécurité & Conformité','Authentification, rôles personnalisés, chiffrement, audit trail. Conforme aux normes en vigueur.'],
                ] as $f)
                <div class="col-lg-4 col-md-6 anim-fade-up">
                    <div class="gel-feature-item">
                        <div class="gel-feature-icon">
                            <i class="bi {{ $f[0] }}"></i>
                        </div>
                        <div>
                            <h6>{{ $f[1] }}</h6>
                            <p>{{ $f[2] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-5 anim-fade-up">
                <a href="/nos-modules" class="gel-slide-btn gel-slide-btn-primary" style="display:inline-flex;">
                    <i class="bi-grid-3x3-gap"></i> Voir tous les modules
                </a>
            </div>
        </div>
    </section>

    <!-- logiciel de comptabilité -->
    <section class="gel-section-alt" style="padding:60px 0;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 anim-fade-left">
                    <span class="gel-section-chip"><i class="bi-cpu"></i> Logiciel de Comptabilité</span>
                    <h2 class="gel-section-title">Conforme <span style="color:var(--gel-primary);">OHADA/SYSCOA</span><br>Licence standalone ou SaaS</h2>
                    <p style="font-size:14px;color:var(--gel-muted);line-height:1.7;margin-bottom:20px;">Notre logiciel de comptabilité professionnel vous offre tout le nécessaire pour tenir votre comptabilité : plan comptable, saisie des journaux, balance, bilan, compte de résultat et déclarations fiscales.</p>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--gel-primary-soft);color:var(--gel-primary);border-radius:100px;font-size:12px;font-weight:600;"><i class="bi-check-lg"></i> Plan SYSCOA</span>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--gel-primary-soft);color:var(--gel-primary);border-radius:100px;font-size:12px;font-weight:600;"><i class="bi-check-lg"></i> Journaux</span>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--gel-primary-soft);color:var(--gel-primary);border-radius:100px;font-size:12px;font-weight:600;"><i class="bi-check-lg"></i> Bilan &amp; CRP</span>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--gel-primary-soft);color:var(--gel-primary);border-radius:100px;font-size:12px;font-weight:600;"><i class="bi-check-lg"></i> Déclarations fiscales</span>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--gel-primary-soft);color:var(--gel-primary);border-radius:100px;font-size:12px;font-weight:600;"><i class="bi-check-lg"></i> Multi-utilisateurs</span>
                    </div>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                        <a href="/logiciel-comptabilite" class="gel-slide-btn gel-slide-btn-primary" style="display:inline-flex;"><i class="bi-cpu"></i> Découvrir le logiciel</a>
                        <a href="/tarifs" class="gel-slide-btn" style="display:inline-flex;background:transparent;color:var(--gel-text);border:1.5px solid var(--gel-border);"><i class="bi-currency-dollar"></i> Voir les tarifs</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0 anim-fade-right">
                    <div style="background:var(--gel-white);border:1px solid var(--gel-border);border-radius:16px;padding:32px;text-align:center;box-shadow:var(--shadow-md);">
                        <div style="font-size:48px;color:var(--gel-primary);margin-bottom:12px;"><i class="bi-cpu"></i></div>
                        <h3 style="font-family:var(--font-heading);font-size:24px;font-weight:900;color:var(--gel-dark);">GEL Comptabilité</h3>
                        <p style="font-size:14px;color:var(--gel-muted);margin:8px 0;">Le logiciel de comptabilité des experts-comptables au Bénin</p>
                        <div style="font-family:var(--font-heading);font-size:2.6rem;font-weight:900;color:#163A5E;line-height:1;">150 000 <span style="font-size:15px;font-weight:500;color:var(--gel-muted);">FCFA</span></div>
                        <p style="font-size:12px;color:var(--gel-muted);margin-top:4px;">Licence perpétuelle &mdash; 1 poste</p>
                        <a href="/catalogue" class="gel-btn-pricing gel-btn-pricing-primary"><i class="bi-cart3"></i> Acheter la licence</a>
                        <div class="gel-payment-wrap">
                            <span class="gel-payment-badge"><i class="bi-phone" style="color:var(--gel-primary);"></i> MoMo</span>
                            <span class="gel-payment-badge"><i class="bi-bank" style="color:var(--gel-primary);"></i> Virement</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- processus -->
    <section class="gel-section gel-section-alt">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <span class="gel-section-chip anim-fade-up"><i class="bi-skip-forward"></i> Comment ça marche</span>
                    <h2 class="gel-section-title anim-fade-up delay-1">Prêt en <span style="color:var(--gel-primary);">3 étapes</span></h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Créez votre compte, paramétrez votre espace et pilotez votre cabinet depuis un tableau de bord unifié.</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-process-step">
                        <div style="font-size:32px;color:var(--gel-primary);margin-bottom:16px;"><i class="bi-person-plus"></i></div>
                        <div class="gel-process-num">1</div>
                        <h5>Inscription</h5>
                        <p>Créez votre compte gratuitement et choisissez la formule adaptée à votre cabinet ou entreprise.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-process-step">
                        <div style="font-size:32px;color:var(--gel-primary);margin-bottom:16px;"><i class="bi-gear"></i></div>
                        <div class="gel-process-num">2</div>
                        <h5>Configuration</h5>
                        <p>Activez vos modules, invitez vos collaborateurs, importez vos données et personnalisez vos espaces.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-process-step">
                        <div style="font-size:32px;color:var(--gel-primary);margin-bottom:16px;"><i class="bi-rocket-takeoff"></i></div>
                        <div class="gel-process-num">3</div>
                        <h5>Productivité</h5>
                        <p>Vous êtes opérationnel ! Pilotez votre activité en temps réel avec des tableaux de bord intelligents.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- blog -->
    <section class="gel-section" style="padding:60px 0;">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <span class="gel-section-chip anim-fade-up"><i class="bi-pencil-square"></i> Blog & Actualités</span>
                    <h2 class="gel-section-title anim-fade-up delay-1">Conseils & <span style="color:var(--gel-primary);">actualités</span></h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Retrouvez nos derniers articles sur la gestion de cabinet, la comptabilité OHADA et les innovations du secteur.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="gel-blog-card">
                        <div style="height:180px;background:linear-gradient(135deg,#1E3A5F,#0F172A);display:flex;align-items:center;justify-content:center;color:var(--gel-primary);font-size:40px;"><i class="bi-cpu"></i></div>
                        <div class="gel-blog-card-body">
                            <span class="gel-blog-tag"><i class="bi-tag"></i> Comptabilité</span>
                            <h5>SYSCOA 2025 : Tout savoir sur le nouveau plan comptable OHADA</h5>
                            <p>Le plan comptable SYSCOA évolue en 2025. Découvrez les principaux changements et comment adapter votre cabinet.</p>
                            <a href="/blogue/syscoa-2025" class="gel-blog-link">Lire l'article <i class="bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="gel-blog-card">
                        <div style="height:180px;background:linear-gradient(135deg,#FF7900,#ff9a3c);display:flex;align-items:center;justify-content:center;color:#fff;font-size:40px;"><i class="bi-robot"></i></div>
                        <div class="gel-blog-card-body">
                            <span class="gel-blog-tag"><i class="bi-tag"></i> Innovation</span>
                            <h5>Comment l'IA transforme la gestion des cabinets comptables</h5>
                            <p>Automatisation, classification, analyse prédictive — l'intelligence artificielle révolutionne le métier d'expert-comptable.</p>
                            <a href="/blogue/ia-cabinets-comptables" class="gel-blog-link">Lire l'article <i class="bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="gel-blog-card">
                        <div style="height:180px;background:linear-gradient(135deg,#163A5E,#1E3A5F);display:flex;align-items:center;justify-content:center;color:var(--gel-primary);font-size:40px;"><i class="bi-shield-check"></i></div>
                        <div class="gel-blog-card-body">
                            <span class="gel-blog-tag"><i class="bi-tag"></i> Sécurité</span>
                            <h5>Protection des données : guide pour les cabinets comptables</h5>
                            <p>RGPD, chiffrement, sauvegarde — les bonnes pratiques pour sécuriser les données sensibles de vos clients.</p>
                            <a href="/blogue/protection-donnees-cabinet" class="gel-blog-link">Lire l'article <i class="bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 anim-fade-up">
                <a href="/blogue" class="gel-slide-btn gel-slide-btn-primary" style="display:inline-flex;">
                    <i class="bi-pencil-square"></i> Voir tous les articles
                </a>
            </div>
        </div>
    </section>

    <!-- cta -->
    <section class="gel-cta-band">
        <div class="container" style="position:relative; z-index:1;">
            <div class="row align-items-center">
                <div class="col-lg-8 anim-fade-left">
                    <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.15);padding:4px 14px;border-radius:100px;font-size:11px;font-weight:600;color:rgba(255,255,255,0.9);margin-bottom:14px;"><i class="bi-lightning-fill"></i> Prêt à passer à l'action ?</span>
                    <h2>Prêt à transformer<br>votre cabinet ?</h2>
                    <p>Rejoignez les cabinets comptables qui pilotent déjà leur activité avec GEL Cabinet. Essayez gratuitement pendant 30 jours.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 anim-fade-right">
                    <a href="/register" class="gel-btn-white me-3" style="margin-bottom:8px;">
                        <i class="bi-rocket-takeoff"></i> Essai gratuit 30 jours
                    </a>
                    <a href="/tarifs" class="gel-btn-white" style="background:rgba(255,255,255,0.15); color:#fff; border:2px solid rgba(255,255,255,0.5);">
                        <i class="bi-currency-dollar"></i> Voir les tarifs
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- contact -->
    <section id="contact" class="gel-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <h2 class="gel-section-title anim-fade-up delay-1">Contact</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-2">Une question ? Un besoin spécifique ? On est là pour vous répondre.</p>
                </div>
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-lg-4 anim-fade-left">
                    <div class="gel-contact-info">
                        <h3>GEL Cabinet</h3>
                        <p>CRM, GED, Comptabilité, ERP — la plateforme tout-en-un pour votre cabinet.</p>

                        <div class="gel-contact-detail">
                            <div class="gel-contact-detail-icon"><i class="bi-geo-alt-fill"></i></div>
                            <div>
                                <h6>Adresse</h6>
                                <p>Cotonou, Bénin</p>
                            </div>
                        </div>
                        <div class="gel-contact-detail">
                            <div class="gel-contact-detail-icon"><i class="bi-telephone-fill"></i></div>
                            <div>
                                <h6>Téléphone</h6>
                                <p>+229 XX XX XX XX</p>
                            </div>
                        </div>
                        <div class="gel-contact-detail">
                            <div class="gel-contact-detail-icon"><i class="bi-envelope-fill"></i></div>
                            <div>
                                <h6>Email</h6>
                                <p>contact@gelcabinet.com</p>
                            </div>
                        </div>

                        <div style="margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--gel-border);">
                            <div class="gel-footer-heading" style="margin-bottom: 14px; color:var(--gel-dark);">Suivez-nous</div>
                            <div class="gel-footer-social">
                                <a href="#" class="gel-social-btn" style="background:var(--gel-light); border-color:var(--gel-border); color:var(--gel-muted);" aria-label="Facebook"><i class="bi-facebook"></i></a>
                                <a href="#" class="gel-social-btn" style="background:var(--gel-light); border-color:var(--gel-border); color:var(--gel-muted);" aria-label="LinkedIn"><i class="bi-linkedin"></i></a>
                                <a href="#" class="gel-social-btn" style="background:var(--gel-light); border-color:var(--gel-border); color:var(--gel-muted);" aria-label="Twitter"><i class="bi-twitter-x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 anim-fade-right">
                    <div class="gel-form-wrap">
                        <div class="gel-form-title">Demander un logiciel</div>
                        <div class="gel-form-sub">Laissez vos coordonnées, on vous recontacte sous 24h pour vous présenter la solution adaptée.</div>

                        @if(session('success'))
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:6px; padding:12px 16px; font-size:13px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                                <i class="bi-check-circle-fill"></i> {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="/demande">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="gel-form-label">Entreprise</label>
                                    <input type="text" name="company_name" class="gel-form-control" required placeholder="Nom de votre entreprise">
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Personne à contacter</label>
                                    <input type="text" name="contact_name" class="gel-form-control" required placeholder="Votre nom complet">
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Email</label>
                                    <input type="email" name="email" class="gel-form-control" required placeholder="email@entreprise.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Téléphone</label>
                                    <input type="tel" name="phone" class="gel-form-control" placeholder="+229 XX XX XX XX">
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Secteur d'activité</label>
                                    <select name="sector" class="gel-form-control">
                                        <option value="">Choisir un secteur</option>
                                        <option>Cabinet d'expertise comptable</option>
                                        <option>Cabinet juridique</option>
                                        <option>Cabinet multi-pôles</option>
                                        <option>Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="gel-form-label">Module souhaité</label>
                                    <select name="module" class="gel-form-control">
                                        <option value="">Choisir un module</option>
                                        <option>CRM Clients</option>
                                        <option>GED — Documents</option>
                                        <option>Comptabilité</option>
                                        <option>ERP Intégré</option>
                                        <option>Tous les modules</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="gel-form-label">Message</label>
                                    <textarea name="message" class="gel-form-control" rows="4" placeholder="Décrivez vos besoins, le nombre d'utilisateurs, votre contexte..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="gel-btn-submit">
                                        <i class="bi-send-fill"></i> Envoyer ma demande
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- footer -->
    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // NAVBAR SCROLL
    const navbar = document.getElementById('gelNavbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    // MOBILE MENU
    const toggler = document.getElementById('gelToggler');
    const mobileMenu = document.getElementById('gelMobileMenu');
    const togglerIcon = document.getElementById('togglerIcon');

    toggler.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('open');
        togglerIcon.className = isOpen ? 'bi-x-lg' : 'bi-list';
        document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    function closeMobileMenu() {
        mobileMenu.classList.remove('open');
        togglerIcon.className = 'bi-list';
        document.body.style.overflow = '';
    }

    document.addEventListener('click', (e) => {
        if (!navbar.contains(e.target) && !mobileMenu.contains(e.target)) {
            closeMobileMenu();
        }
    });

    // HERO CAROUSEL
    const slides = document.querySelectorAll('.gel-slide');
    const dots = document.querySelectorAll('.gel-dot');
    let currentSlide = 0;
    let autoplayTimer;

    function goToSlide(n) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }

    function startAutoplay() {
        autoplayTimer = setInterval(nextSlide, 6000);
    }
    function resetAutoplay() {
        clearInterval(autoplayTimer);
        startAutoplay();
    }

    document.getElementById('nextSlide').addEventListener('click', () => { nextSlide(); resetAutoplay(); });
    document.getElementById('prevSlide').addEventListener('click', () => { prevSlide(); resetAutoplay(); });
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => { goToSlide(i); resetAutoplay(); });
    });

    let touchStartX = 0;
    const heroEl = document.getElementById('hero');
    heroEl.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
    heroEl.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) { diff > 0 ? nextSlide() : prevSlide(); resetAutoplay(); }
    }, { passive: true });

    startAutoplay();

    // SCROLL ANIMATIONS
    const animEls = document.querySelectorAll('.anim-fade-up, .anim-fade-left, .anim-fade-right, .anim-scale');
    const animObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('anim-visible');
                animObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });
    animEls.forEach(el => {
        // Marquer immédiatement visible si déjà dans le viewport
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            el.classList.add('anim-visible');
        } else {
            animObserver.observe(el);
        }
    });

    // COUNTER ANIMATION
    const counters = document.querySelectorAll('[data-target]');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    function animateCounter(el) {
        const target = parseInt(el.dataset.target);
        const suffix = el.dataset.suffix || '';
        const duration = 1800;
        const start = performance.now();
        const update = (ts) => {
            const progress = Math.min((ts - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(eased * target) + suffix;
            if (progress < 1) requestAnimationFrame(update);
        };
        requestAnimationFrame(update);
    }
    counters.forEach(c => counterObserver.observe(c));

    // SMOOTH SCROLL FOR ANCHOR LINKS
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', e => {
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-height'));
                window.scrollTo({
                    top: target.getBoundingClientRect().top + window.scrollY - navH - 12,
                    behavior: 'smooth'
                });
                closeMobileMenu();
            }
        });
    });
    </script>
</body>
</html>