<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FAQ | GEL Cabinet</title>
    <meta name="description" content="Foire aux questions GEL Cabinet : réponses aux questions fréquentes sur la plateforme, les services, la facturation, la fiscalité et l'assistance.">

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
        body { font-family: var(--font-body); background: var(--gel-light); color: var(--gel-text); line-height: 1.6; }
        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading); font-weight: 700; }

        .anim-fade-up { opacity: 0; transform: translateY(36px); transition: opacity 0.65s var(--transition), transform 0.65s var(--transition); }
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

        .gel-page-header {
            margin-top: var(--nav-height);
            background: linear-gradient(135deg, #0A1628 0%, #1E293B 25%, #0F172A 50%, #1E293B 75%, #0A1628 100%);
            background-size: 300% 300%;
            animation: gelGradientMove 12s ease infinite;
            padding: 100px 0 80px;
            position: relative; overflow: hidden;
        }
        @keyframes gelGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gel-page-header::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(var(--gel-svc-rgb),0.12) 0%, transparent 70%); border-radius: 50%; animation: gelFloatA 8s ease-in-out infinite; }
        .gel-page-header::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
        @keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
        @keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(2.2rem, 4.5vw, 3.2rem); font-weight: 900; color: #fff; letter-spacing: -1px; position: relative; z-index: 1; }
        .gel-page-header p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 600px; margin-top: 12px; line-height: 1.7; position: relative; z-index: 1; }
        .gel-page-header-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); padding: 5px 14px; border-radius: 100px; font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; position: relative; z-index: 1; }

        .gel-section { padding: 80px 0; }
        .gel-section-title { font-family: var(--font-heading); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--gel-dark); margin-bottom: 14px; }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); max-width: 540px; line-height: 1.7; }

        .gel-faq-category { margin-bottom: 48px; }
        .gel-faq-cat-title { font-size: 18px; font-weight: 800; color: var(--gel-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .gel-faq-cat-title i { color: var(--gel-primary); }
        .gel-faq-item { background: var(--gel-white); border: 1px solid var(--gel-border); border-radius: 12px; margin-bottom: 8px; overflow: hidden; transition: border-color var(--transition); }
        .gel-faq-item:hover { border-color: var(--gel-primary); }
        .gel-faq-q { padding: 18px 22px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 14px; font-weight: 600; color: var(--gel-text); background: none; border: none; width: 100%; text-align: left; font-family: var(--font-body); transition: color var(--transition); }
        .gel-faq-q:hover { color: var(--gel-primary); }
        .gel-faq-q i { font-size: 14px; color: var(--gel-muted); transition: transform var(--transition), color var(--transition); flex-shrink: 0; }
        .gel-faq-item.open .gel-faq-q i { transform: rotate(180deg); color: var(--gel-primary); }
        .gel-faq-a { max-height: 0; overflow: hidden; transition: max-height 0.3s ease, padding 0.3s ease; padding: 0 22px; }
        .gel-faq-item.open .gel-faq-a { max-height: 350px; padding: 0 22px 18px; }
        .gel-faq-a p { font-size: 13.5px; color: var(--gel-muted); line-height: 1.7; margin: 0; }
        .gel-faq-a ul { margin: 8px 0 0; padding-left: 20px; font-size: 13.5px; color: var(--gel-muted); }
        .gel-faq-a ul li { margin-bottom: 4px; }

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

        .gel-search-box { position: relative; max-width: 500px; }
        .gel-search-box input { width: 100%; padding: 14px 18px 14px 46px; border-radius: 10px; border: 1px solid var(--gel-border); font-size: 14px; outline: none; transition: border-color var(--transition), box-shadow var(--transition); background: var(--gel-white); }
        .gel-search-box input:focus { border-color: var(--gel-primary); box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
        .gel-search-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--gel-muted); font-size: 16px; }

        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 80px 0 60px; } }
    </style>
</head>
<body>

    @include('partials.navbar')

    <!-- page header -->
    <div class="gel-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="gel-page-header-badge"><i class="bi-question-circle"></i> Ressources</div>
                    <h1>Foire aux Questions</h1>
                    <p>Les réponses à vos questions. Si vous ne trouvez pas la vôtre, appelez-nous ou laissez-nous un message.</p>
                </div>
                <div class="col-lg-4 d-flex align-items-end justify-content-lg-end mt-4 mt-lg-0">
                    <div class="gel-search-box">
                        <i class="bi-search"></i>
                        <input type="text" placeholder="Rechercher une question..." oninput="filterFaq(this.value)">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <section class="gel-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <!-- Général -->
                    <div class="gel-faq-category">
                        <h3 class="gel-faq-cat-title"><i class="bi-info-circle"></i> Général</h3>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Qu'est-ce que GEL Cabinet ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>GEL Cabinet est une plateforme de gestion de cabinet. CRM Clients, GED, Pôles & Missions, Comptabilité (SYSCOA/OHADA) et ERP Intégré (stocks, facturation, RH, paie, trésorerie).</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">À qui s'adresse GEL Cabinet ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>GEL Cabinet s'adresse aux :<ul><li>Cabinets comptables et d'expertise comptable</li><li>Cabinets juridiques et d'avocats</li><li>Cabinets fiscaux et consultants en gestion</li><li>Cabinets sociaux et RH</li><li>Entreprises souhaitant centraliser leur gestion administrative et financière</li></ul></p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">GEL Cabinet est-il accessible en ligne ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, GEL Cabinet est en ligne, 100% cloud. Depuis Chrome, Firefox, Safari ou Edge, sur PC, tablette ou téléphone. Pas d'installation, juste une connexion internet.</p></div>
                        </div>
                    </div>

                    <!-- Comptes & Abonnements -->
                    <div class="gel-faq-category">
                        <h3 class="gel-faq-cat-title"><i class="bi-person-badge"></i> Comptes & Abonnements</h3>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Comment créer un compte ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Cliquez sur <strong>S'inscrire</strong>, remplissez le formulaire (nom, email, mot de passe) et validez votre email. C'est gratuit et sans engagement.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Y a-t-il une période d'essai gratuite ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, <strong>14 jours</strong> gratuits pour tester toutes les fonctionnalités, sans carte bancaire ni limite. Après, vous choisissez l'offre qui vous va.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Quels sont les modes de paiement acceptés ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Nous acceptons les paiements par :<ul><li><strong>Mobile Money</strong> : MTN MoMo, Moov MoMo</li><li><strong>Cartes bancaires</strong> : Visa, Mastercard</li><li><strong>Virement bancaire</strong> : pour les abonnements annuels</li></ul></p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Puis-je résilier mon abonnement à tout moment ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, résiliable à tout moment depuis votre espace client. La résiliation prend effet en fin de période. Vos données restent accessibles en lecture <strong>30 jours</strong> après résiliation, le temps de les exporter.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Proposez-vous des offres pour les grandes équipes ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, pour les équipes de plus de 10 utilisateurs. Contactez notre équipe commerciale pour un devis.</p></div>
                        </div>
                    </div>

                    <!-- Plateforme & Modules -->
                    <div class="gel-faq-category">
                        <h3 class="gel-faq-cat-title"><i class="bi-grid-3x3-gap"></i> Plateforme & Modules</h3>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Quels modules sont disponibles ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>GEL Cabinet propose <strong>5 modules intégrés</strong> :<ul><li><strong>CRM Clients</strong> : gestion des prospects, clients, relances et suivi commercial</li><li><strong>GED</strong> : documents, dossiers, versions, OCR et archivage légal</li><li><strong>Pôles & Missions</strong> : organisation du travail, planification et suivi des dossiers</li><li><strong>Comptabilité</strong> : plan comptable SYSCOA/OHADA, journaux, bilan, TVA, IB</li><li><strong>ERP Intégré</strong> : stocks, facturation, RH, paie, trésorerie et tableaux de bord</li></ul></p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Les modules communiquent-ils entre eux ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, tous les modules sont <strong>intégrés</strong>. Les écritures comptables alimentent les déclarations, les données RH sont reprises par la paie, la facturation met à jour la compta en temps réel.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Puis-je utiliser un module indépendamment des autres ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, chaque module peut être utilisé séparément. Vous pouvez par exemple n'utiliser que la GED ou que la Comptabilité. Les modules s'activent individuellement selon vos besoins, et vous pouvez les ajouter ultérieurement.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Puis-je importer mes données existantes ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, nous proposons des outils d'import pour vos données clients, plans comptables, écritures et documents. Notre équipe vous accompagne dans la migration depuis votre ancien logiciel.</p></div>
                        </div>
                    </div>

                    <!-- Impôt & Fiscalité -->
                    <div class="gel-faq-category">
                        <h3 class="gel-faq-cat-title"><i class="bi-receipt"></i> Impôt & Fiscalité</h3>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Quels types de déclarations fiscales puis-je générer ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>GEL Cabinet permet de préparer et générer :<ul><li><strong>TVA</strong> : déclaration mensuelle ou trimestrielle</li><li><strong>IB</strong> : Impôt sur les Bénéfices (annuel)</li><li><strong>IRPP</strong> : Impôt sur le Revenu des Personnes Physiques</li><li><strong>État C</strong> : déclaration statistique et fiscale</li><li>Autres états périodiques requis par la DGI</li></ul></p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Comment sont calculés les acomptes d'IB ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Les acomptes d'Impôt sur les Bénéfices sont calculés automatiquement par le système sur la base de l'IB dû au titre de l'exercice précédent. Le module Fiscalité de GEL Cabinet calcule et planifie les échéances d'acomptes pour vous.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">La plateforme est-elle conforme aux normes de la DGI ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, GEL Cabinet est conçu en conformité avec la réglementation fiscale béninoise et OHADA. Les déclarations générées respectent les formats et les barèmes en vigueur. Nous mettons à jour la plateforme à chaque changement de la législation.</p></div>
                        </div>
                    </div>

                    <!-- Sécurité & Données -->
                    <div class="gel-faq-category">
                        <h3 class="gel-faq-cat-title"><i class="bi-shield-check"></i> Sécurité & Données</h3>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Mes données sont-elles sécurisées ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Absolument. Données <strong>chiffrées en transit et au repos</strong> (TLS 1.3, AES-256), sauvegardes quotidiennes avec rétention 30 jours, hébergement sécurisé avec redondance.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">GEL Cabinet est-il conforme au RGPD ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, RGPD conforme. Droit d'accès, rectification, suppression de vos données. Export possible à tout moment.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Comment sont gérées les sauvegardes ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Automatisées chaque jour, rétention <strong>30 jours</strong>. Restauration sous <strong>4h ouvrées</strong> en cas de pépin. Une sauvegarde supplémentaire avant chaque mise à jour majeure.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Puis-je exporter mes données ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, export Excel (écritures, balance, clients), PDF (factures, rapports), ZIP (documents GED). Aucune limite, disponible depuis votre espace.</p></div>
                        </div>
                    </div>

                    <!-- Assistance -->
                    <div class="gel-faq-category">
                        <h3 class="gel-faq-cat-title"><i class="bi-headset"></i> Assistance</h3>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Comment contacter le support ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Vous pouvez nous contacter par :<ul><li><strong>Email</strong> : support@gelcabinet.com — réponse sous 24h ouvrées</li><li><strong>Téléphone</strong> : +229 XX XX XX XX — du lundi au vendredi de 8h à 18h</li><li><strong>Formulaire de contact</strong> : via la page Centre d'aide</li></ul></p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Proposez-vous une assistance téléphonique ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, notre assistance téléphonique est disponible du lundi au vendredi de <strong>8h à 18h (GMT+1)</strong> au +229 XX XX XX XX. Pour les clients Premium, une ligne prioritaire est disponible 24/7.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Y a-t-il une documentation utilisateur ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, nous proposons une documentation complète avec :<ul><li>Guides d'utilisation par module</li><li>Tutoriels vidéo pas à pas</li><li>Base de connaissances consultable</li><li>FAQ détaillée</li></ul>Le tout accessible depuis la section Documentation de notre site.</p></div>
                        </div>

                        <div class="gel-faq-item">
                            <button class="gel-faq-q" onclick="toggleFaq(this)">Proposez-vous des formations ?<i class="bi-chevron-down"></i></button>
                            <div class="gel-faq-a"><p>Oui, nous proposons des formations en ligne et en présentiel pour vous aider à maîtriser la plateforme. Des sessions collectives et individuelles sont disponibles. Contactez notre équipe pour réserver votre session.</p></div>
                        </div>
                    </div>

                    <!-- Pas trouvé ? -->
                    <div class="text-center anim-fade-up" style="background:var(--gel-light2);border-radius:14px;padding:40px;border:1px solid var(--gel-border);margin-top:20px;">
                        <i class="bi-chat-dots" style="font-size:36px;color:var(--gel-primary);"></i>
                        <h4 style="font-size:18px;font-weight:800;margin-top:12px;">Vous n'avez pas trouvé votre réponse ?</h4>
                        <p style="font-size:13.5px;color:var(--gel-muted);max-width:400px;margin:8px auto 18px;">Contactez-nous par téléphone ou laissez-nous un message, on vous répond rapidement.</p>
                        <a href="#" class="gel-btn-nav gel-btn-nav-primary" onclick="openModal()"><i class="bi-envelope"></i> Nous contacter</a>
                        <a href="tel:+22900000000" class="gel-btn-nav gel-btn-nav-outline" style="margin-left:6px;"><i class="bi-telephone"></i> +229 XX XX XX XX</a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="gel-modal-overlay" id="authModal">
        <div class="gel-modal-box">
            <button class="gel-modal-close" onclick="closeModal()">&times;</button>
            <div class="gel-modal-icon"><i class="bi-question-circle-fill"></i></div>
            <h3>Besoin d'aide ?</h3>
            <p>Créez un compte pour accéder à l'assistance prioritaire et à la base de connaissance.</p>
            <a href="/register" class="gel-modal-btn gel-modal-btn-primary"><i class="bi-person-plus"></i> Créer un compte</a>
            <div class="gel-modal-divider">ou</div>
            <a href="/login" class="gel-modal-btn gel-modal-btn-outline"><i class="bi-box-arrow-in-right"></i> Se connecter</a>
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

        function toggleFaq(btn) {
            const item = btn.parentElement;
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.gel-faq-item.open').forEach(el => el.classList.remove('open'));
            if (!isOpen) item.classList.add('open');
        }

        function filterFaq(q) {
            const items = document.querySelectorAll('.gel-faq-item');
            const cats = document.querySelectorAll('.gel-faq-category');
            q = q.toLowerCase().trim();
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
            cats.forEach(cat => {
                const visible = Array.from(cat.querySelectorAll('.gel-faq-item')).some(item => item.style.display !== 'none');
                cat.style.display = (!q || visible) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
