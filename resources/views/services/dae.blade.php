<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Direction Administrative Externalisée | GEL Cabinet</title>
    <meta name="description" content="Externalisez votre gestion administrative avec GEL Cabinet : courriers, emails, agenda, contrats, RH, conformité, rapports et tâches — un module DAE complet.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --gel-primary: #FF7900; --gel-primary-hov: #e06700; --gel-primary-soft: rgba(255,121,0,0.06);
            --gel-dae: #8B5CF6; --gel-dae-rgb: 139,92,246;
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
        .gel-hero { padding: calc(var(--nav-height) + 60px) 0 80px; background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%); position: relative; overflow: hidden; }
        .gel-hero::before { content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(139,92,246,0.08) 0%, transparent 70%); pointer-events: none; }
        .gel-hero-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; background: rgba(139,92,246,0.1); color: var(--gel-dae); border-radius: 100px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
        .gel-hero h1 { font-size: clamp(2rem, 5vw, 3rem); font-weight: 800; letter-spacing: -0.02em; line-height: 1.15; color: var(--gel-darker); margin-bottom: 16px; }
        .gel-hero h1 .highlight { color: var(--gel-dae); }
        .gel-hero p { font-size: 16px; color: var(--gel-muted); max-width: 540px; margin-bottom: 28px; }
        .gel-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .gel-btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 12px 28px; background: var(--gel-dae); color: #fff; font-weight: 600; font-size: 14px; border-radius: 6px; text-decoration: none; transition: all var(--transition); border: none; cursor: pointer; }
        .gel-btn-primary:hover { background: #7C3AED; color: #fff; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(139,92,246,0.3); }
        .gel-btn-outline { display: inline-flex; align-items: center; gap: 6px; padding: 12px 28px; background: transparent; color: var(--gel-text); font-weight: 600; font-size: 14px; border-radius: 6px; text-decoration: none; border: 1.5px solid var(--gel-border); transition: all var(--transition); }
        .gel-btn-outline:hover { border-color: var(--gel-dae); color: var(--gel-dae); background: rgba(139,92,246,0.04); }
        .gel-section { padding: 80px 0; }
        .gel-section-title { text-align: center; margin-bottom: 48px; }
        .gel-section-title h2 { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: var(--gel-darker); margin-bottom: 10px; }
        .gel-section-title p { font-size: 15px; color: var(--gel-muted); max-width: 560px; margin: 0 auto; }
        .gel-card-feature { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 10px; padding: 28px 24px; height: 100%; transition: all var(--transition); }
        .gel-card-feature:hover { border-color: transparent; box-shadow: var(--shadow-lg); transform: translateY(-4px); }
        .gel-card-feature .icon-box { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px; }
        .gel-card-feature h5 { font-size: 16px; font-weight: 700; margin-bottom: 8px; color: var(--gel-darker); }
        .gel-card-feature p { font-size: 13px; color: var(--gel-muted); margin-bottom: 0; line-height: 1.5; }
        .gel-card-value { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 10px; padding: 32px 24px; text-align: center; height: 100%; transition: all var(--transition); }
        .gel-card-value:hover { box-shadow: var(--shadow-md); }
        .gel-card-value .icon-box { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 16px; }
        .gel-card-value h5 { font-weight: 700; color: var(--gel-darker); margin-bottom: 8px; }
        .gel-card-value p { font-size: 13px; color: var(--gel-muted); margin-bottom: 0; }
        .gel-cta { background: linear-gradient(135deg, var(--gel-darker) 0%, #1E293B 100%); padding: 72px 0; text-align: center; }
        .gel-cta h2 { color: #fff; font-weight: 800; font-size: clamp(1.3rem, 2.5vw, 1.8rem); margin-bottom: 10px; }
        .gel-cta p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 500px; margin: 0 auto 24px; }
        .gel-footer { background: var(--gel-darker); padding: 32px 0; border-top: 1px solid rgba(255,255,255,0.06); text-align: center; }
        .gel-footer p { font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 0; }
        .bg-dae-soft { background: rgba(139,92,246,0.08); }
        .bg-success-soft { background: rgba(16,185,129,0.08); }
        .bg-warning-soft { background: rgba(255,193,7,0.1); }
        .bg-info-soft { background: rgba(59,130,246,0.08); }
        .text-dae { color: var(--gel-dae); }
        @media (max-width: 768px) { .gel-navbar .container-fluid { padding: 0 16px; } .gel-nav-center, .gel-phone { display: none !important; } .gel-hero { padding-top: calc(var(--nav-height) + 40px); } }
    </style>
</head>
<body>
    @include('partials.navbar')

    <section class="gel-hero">
        <div class="container" style="max-width:1200px;margin:0 auto;padding:0 32px;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="gel-hero-badge"><i class="bi bi-file-text"></i> Module DAE</div>
                    <h1>Direction <span class="highlight">Administrative Externalisée</span></h1>
                    <p>Confiez-nous la gestion administrative de votre entreprise : courriers, contrats, documents RH, conformité et reporting. Notre équipe de secrétaires dédiés vous libère du quotidien pour que vous puissiez vous concentrer sur l'essentiel.</p>
                    <div class="gel-hero-actions">
                        <a href="{{ route('contact') }}" class="gel-btn-primary"><i class="bi bi-telephone"></i> Demander une démo</a>
                        <a href="{{ route('tarifs') }}" class="gel-btn-outline"><i class="bi bi-currency-euro"></i> Voir les tarifs</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="bg-dae-soft rounded-4 p-5 text-center">
                            <i class="bi bi-building fs-1 text-dae d-block mb-3"></i>
                            <h4 class="fw-bold">Gestion Administrative Complète</h4>
                            <p class="text-muted small">Courriers, emails, agenda, contrats, RH, conformité, reporting — un guichet unique pour votre administration.</p>
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <span class="badge bg-white text-dark px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-check-circle text-success me-1"></i>Gain de temps</span>
                                <span class="badge bg-white text-dark px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-shield-check text-dae me-1"></i>Sécurisé</span>
                                <span class="badge bg-white text-dark px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-graph-up-arrow text-primary me-1"></i>Conforme</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gel-section">
        <div class="container" style="max-width:1200px;margin:0 auto;padding:0 32px;">
            <div class="gel-section-title">
                <h2>10 modules pour une externalisation complète</h2>
                <p>Notre équipe de secrétaires DAE prend en charge l'intégralité de votre gestion administrative.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-dae-soft text-dae"><i class="bi bi-envelope"></i></div>
                        <h5>Courriers</h5>
                        <p>Gestion des courriers entrants, sortants et internes avec suivi, classement et archivage. Modèles prêts à l'emploi.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-info-soft text-info"><i class="bi bi-chat-dots"></i></div>
                        <h5>Emails</h5>
                        <p>Boîte de réception partagée, réponse automatisée, classement par dossier et archivage pour une traçabilité complète.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-success-soft text-success"><i class="bi bi-calendar-event"></i></div>
                        <h5>Agenda</h5>
                        <p>Planification de rendez-vous, réunions et appels avec rappels automatiques. Vue calendrier mois/semaine/jour.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-warning-soft text-warning"><i class="bi bi-file-text"></i></div>
                        <h5>Contrats</h5>
                        <p>Suivi des contrats avec alertes d'expiration, renouvellement automatique et historique complet des versions.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-dae-soft text-dae"><i class="bi bi-folder"></i></div>
                        <h5>Documents</h5>
                        <p>GED complète avec versioning, catégorisation, expiration et alertes. Import et export simplifiés.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-info-soft text-info"><i class="bi bi-people"></i></div>
                        <h5>Personnel RH</h5>
                        <p>Dossiers employés, compétences, contrats de travail, paie et documents RH centralisés.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-success-soft text-success"><i class="bi bi-shield-check"></i></div>
                        <h5>Conformité</h5>
                        <p>Suivi des obligations réglementaires, échéances et audits. Alertes automatiques en cas de non-conformité.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-warning-soft text-warning"><i class="bi bi-graph-up"></i></div>
                        <h5>Rapports</h5>
                        <p>Génération de rapports périodiques, indicateurs d'activité et tableaux de bord personnalisables.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="gel-card-feature">
                        <div class="icon-box bg-dae-soft text-dae"><i class="bi bi-list-task"></i></div>
                        <h5>Tâches & Suivi</h5>
                        <p>Gestion des tâches avec vue Kanban, assignation, priorités et échéances. Suivi en temps réel.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gel-section bg-light">
        <div class="container" style="max-width:1200px;margin:0 auto;padding:0 32px;">
            <div class="gel-section-title">
                <h2>Pourquoi externaliser votre direction administrative ?</h2>
                <p>Les avantages concrets d'un secrétariat externalisé professionnel.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="gel-card-value">
                        <div class="icon-box bg-success-soft text-success"><i class="bi bi-clock-history"></i></div>
                        <h5>Gagnez du temps</h5>
                        <p>Jusqu'à 60% de temps libéré sur les tâches administratives quotidiennes. Concentrez-vous sur votre cœur de métier.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gel-card-value">
                        <div class="icon-box bg-dae-soft text-dae"><i class="bi bi-shield-check"></i></div>
                        <h5>Sécurité & Conformité</h5>
                        <p>Audit trail complet, traçabilité de toutes les actions, conformité RGPD et stockage sécurisé des données.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gel-card-value">
                        <div class="icon-box bg-info-soft text-info"><i class="bi bi-graph-up-arrow"></i></div>
                        <h5>Professionnalisme</h5>
                        <p>Des secrétaires formés et dédiés, des processus standardisés et une qualité de service irréprochable.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gel-cta">
        <div class="container" style="max-width:1200px;margin:0 auto;padding:0 32px;">
            <h2>Prêt à externaliser votre gestion administrative ?</h2>
            <p>Nos experts DAE sont disponibles pour étudier votre dossier et vous proposer une solution sur mesure.</p>
            <a href="{{ route('contact') }}" class="gel-btn-primary" style="background:#fff;color:var(--gel-darker);">
                <i class="bi bi-arrow-right"></i> Demander une démo gratuite
            </a>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
