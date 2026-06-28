<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation | GEL Cabinet</title>
    @include("partials.styles")
    
    <style>
        :root {
            --gel-primary: #f97316;
            --gel-primary-light: rgba(249, 115, 22, 0.1);
            --gel-primary-hover: #ea580c;
            --gel-dark: #0f172a;
            --gel-muted: #64748b;
            --gel-bg: #f8fafc;
            --font-heading: 'Outfit', system-ui, sans-serif;
            --font-body: 'Inter', system-ui, sans-serif;
            --nav-height: 80px;
        }

        body { font-family: var(--font-body); color: #334155; background: var(--gel-bg); line-height: 1.6; }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); color: var(--gel-dark); font-weight: 700; letter-spacing: -0.02em; }
        
        .gel-section { padding: 80px 0; }
        .gel-section-alt { background: white; }
        
        /* Page Header */
        .gel-page-header {
            margin-top: var(--nav-height);
            background: linear-gradient(135deg, #0A1628 0%, #1E293B 25%, #0F172A 50%, #1E293B 75%, #0A1628 100%);
            background-size: 300% 300%;
            animation: gelGradientMove 12s ease infinite;
            padding: 70px 0 50px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        @keyframes gelGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gel-page-header::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(249, 115, 22,0.12) 0%, transparent 70%); border-radius: 50%; animation: gelFloatA 8s ease-in-out infinite; }
        .gel-page-header::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(249, 115, 22,0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
        @keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
        @keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(1.8rem, 3vw, 2.4rem); font-weight: 900; color: #fff; letter-spacing: -1px; position: relative; z-index: 1; }
        .gel-page-header p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 620px; margin: 12px auto 0; line-height: 1.7; position: relative; z-index: 1; }
        
        .gel-section-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: var(--gel-primary-light); color: var(--gel-primary); font-size: 14px; font-weight: 600; border-radius: 100px; margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Search Box */
        .gel-search-box { max-width: 700px; margin: 40px auto 0; position: relative; }
        .gel-search-box input { width: 100%; padding: 20px 24px 20px 60px; border-radius: 100px; border: 1px solid rgba(0,0,0,0.1); font-size: 1.1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: all 0.3s; }
        .gel-search-box input:focus { outline: none; border-color: var(--gel-primary); box-shadow: 0 0 0 4px rgba(249,115,22,0.1); }
        .gel-search-box i { position: absolute; left: 24px; top: 50%; transform: translateY(-50%); font-size: 1.25rem; color: var(--gel-muted); }

        /* Doc Cards */
        .gel-doc-card { background: white; border-radius: 20px; padding: 32px; border: 1px solid rgba(0,0,0,0.04); transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; text-decoration: none; color: inherit; }
        .gel-doc-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(249,115,22,0.08); border-color: rgba(249,115,22,0.15); }
        .gel-doc-icon { width: 60px; height: 60px; border-radius: 16px; background: var(--gel-bg); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 24px; color: var(--gel-primary); transition: all 0.3s; }
        .gel-doc-card:hover .gel-doc-icon { background: var(--gel-primary); color: white; }
        .gel-doc-title { font-size: 1.25rem; margin-bottom: 12px; }
        .gel-doc-desc { color: var(--gel-muted); font-size: 0.95rem; margin-bottom: 24px; flex-grow: 1; }
        .gel-doc-links { list-style: none; padding: 0; margin: 0; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        .gel-doc-links li { margin-bottom: 10px; }
        .gel-doc-links li:last-child { margin-bottom: 0; }
        .gel-doc-links a { color: var(--gel-dark); text-decoration: none; font-weight: 500; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s; }
        .gel-doc-links a:hover { color: var(--gel-primary); }

        /* Animations */
        .anim-fade-up { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .anim-fade-up.anim-visible { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }

        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 60px 0 40px; } }
    </style>
</head>
<body>

    @include('partials.navbar')

    <!-- Page Header -->
    <header class="gel-page-header">
        <div class="container">
            <div class="anim-fade-up">
                <h1 class="anim-fade-up">Centre de documentation</h1>
                <p class="anim-fade-up delay-1">Trouvez des guides, des manuels d'utilisation et des tutoriels pour exploiter tout le potentiel de GEL Cabinet.</p>
                
                <div class="gel-search-box">
                    <i class="bi-search"></i>
                    <input type="text" id="docSearchInput" placeholder="Rechercher un guide, un module, un mot-clé...">
                </div>
            </div>
        </div>
    </header>

    <section class="gel-section">
        <div class="container">
            <div class="row g-4">
                
                <!-- Catégorie 1 -->
                <div class="col-md-6 col-lg-4 anim-fade-up">
                    <div class="gel-doc-card">
                        <div class="gel-doc-icon"><i class="bi-rocket-takeoff"></i></div>
                        <h3 class="gel-doc-title">Premiers pas</h3>
                        <p class="gel-doc-desc">Tout ce dont vous avez besoin pour configurer votre compte et commencer à utiliser la plateforme.</p>
                        <ul class="gel-doc-links">
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Lier son compte DGI (e-MECeF)</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Ajouter des collaborateurs</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Importer sa balance de départ</a></li>
                            <li><a href="#" style="color:var(--gel-primary); margin-top:8px;">Voir tous les articles <i class="bi-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>

                <!-- Catégorie 2 -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                    <div class="gel-doc-card">
                        <div class="gel-doc-icon"><i class="bi-receipt"></i></div>
                        <h3 class="gel-doc-title">Facturation & e-MECeF</h3>
                        <p class="gel-doc-desc">Manuels d'utilisation pour la gestion, la numérisation et la transmission de vos factures à la DGI.</p>
                        <ul class="gel-doc-links">
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Créer une facture normalisée</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Configurer la TVA</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Gérer les QR Codes</a></li>
                            <li><a href="#" style="color:var(--gel-primary); margin-top:8px;">Voir tous les articles <i class="bi-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>

                <!-- Catégorie 3 -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                    <div class="gel-doc-card">
                        <div class="gel-doc-icon"><i class="bi-calculator"></i></div>
                        <h3 class="gel-doc-title">Comptabilité OHADA</h3>
                        <p class="gel-doc-desc">Guides détaillés sur l'utilisation du module comptable conforme au SYSCOHADA révisé.</p>
                        <ul class="gel-doc-links">
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Saisie automatisée par l'IA</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Rapprochement bancaire</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Génération de la liasse TAFIRE</a></li>
                            <li><a href="#" style="color:var(--gel-primary); margin-top:8px;">Voir tous les articles <i class="bi-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>

                <!-- Catégorie 4 -->
                <div class="col-md-6 col-lg-4 anim-fade-up">
                    <div class="gel-doc-card">
                        <div class="gel-doc-icon"><i class="bi-people"></i></div>
                        <h3 class="gel-doc-title">CRM & Clients</h3>
                        <p class="gel-doc-desc">Apprenez à gérer votre portefeuille client, les missions et le temps passé.</p>
                        <ul class="gel-doc-links">
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Créer une fiche client</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Suivi des temps (Time Tracking)</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Facturation des missions</a></li>
                            <li><a href="#" style="color:var(--gel-primary); margin-top:8px;">Voir tous les articles <i class="bi-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Catégorie 5 -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                    <div class="gel-doc-card">
                        <div class="gel-doc-icon"><i class="bi-shield-lock"></i></div>
                        <h3 class="gel-doc-title">Administration</h3>
                        <p class="gel-doc-desc">Documentation technique pour les administrateurs du cabinet (facturation, rôles).</p>
                        <ul class="gel-doc-links">
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Gestion de l'abonnement</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Configuration SSO / API</a></li>
                            <li><a href="#"><i class="bi-file-earmark-text text-orange"></i> Audit et logs de sécurité</a></li>
                            <li><a href="#" style="color:var(--gel-primary); margin-top:8px;">Voir tous les articles <i class="bi-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>

                <!-- CTA Support -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-2">
                    <div class="gel-doc-card" style="background:var(--gel-dark); color:white;">
                        <div class="gel-doc-icon" style="background:rgba(255,255,255,0.1); color:white;"><i class="bi-headset"></i></div>
                        <h3 class="gel-doc-title" style="color:white;">Besoin d'aide ?</h3>
                        <p class="gel-doc-desc" style="color:rgba(255,255,255,0.7);">Vous ne trouvez pas la réponse à votre question dans notre documentation ?</p>
                        <div class="mt-auto pt-3">
                            <a href="/centre-aide" class="btn btn-light" style="border-radius:10px; font-weight:600; width:100%; padding:12px;">Contactez le support</a>
                        </div>
                    </div>
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

        // Fonctionnalité de recherche pour la documentation
        const docSearchInput = document.getElementById('docSearchInput');
        const docCards = document.querySelectorAll('.gel-doc-card');
        if (docSearchInput) {
            docSearchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                docCards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    // On remonte au conteneur parent (col-md-6) pour le masquer entièrement
                    const col = card.closest('.col-md-6');
                    if (text.includes(term)) {
                        col.style.display = 'block';
                    } else {
                        col.style.display = 'none';
                    }
                });
            });
        }
    </script>
</body>
</html>
