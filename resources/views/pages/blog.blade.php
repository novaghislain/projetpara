<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog & Actualités | GEL Cabinet</title>
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
        }
        @keyframes gelGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gel-page-header::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(249, 115, 22,0.12) 0%, transparent 70%); border-radius: 50%; animation: gelFloatA 8s ease-in-out infinite; }
        .gel-page-header::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(249, 115, 22,0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
        @keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
        @keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
        .gel-page-header h1 { font-family: var(--font-heading); font-size: clamp(1.8rem, 3vw, 2.4rem); font-weight: 900; color: #fff; letter-spacing: -1px; position: relative; z-index: 1; }
        .gel-page-header p { color: rgba(255,255,255,0.6); font-size: 15px; max-width: 620px; margin-top: 12px; line-height: 1.7; position: relative; z-index: 1; }
        
        .gel-section-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: var(--gel-primary-light); color: var(--gel-primary); font-size: 14px; font-weight: 600; border-radius: 100px; margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Blog Filters */
        .gel-filters { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 40px; }
        .gel-filter-btn { padding: 8px 20px; border-radius: 100px; background: white; color: var(--gel-muted); border: 1px solid #e2e8f0; font-weight: 500; font-size: 13px; text-decoration: none; transition: all 0.2s ease; cursor: pointer; }
        .gel-filter-btn:hover, .gel-filter-btn.active { background: var(--gel-dark); color: white; border-color: var(--gel-dark); }

        /* Featured Article */
        .gel-featured-card { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.03); display: flex; flex-direction: column; transition: all 0.3s; text-decoration: none; color: inherit; margin: 0 0 60px 0; max-width: 950px; }
        .gel-featured-card:hover { transform: translateY(-4px); box-shadow: 0 16px 50px rgba(249,115,22,0.1); border-color: rgba(249,115,22,0.2); }
        .gel-featured-img { width: 100%; height: 300px; object-fit: cover; background: #e2e8f0; }
        .gel-featured-content { padding: 40px; display: flex; flex-direction: column; justify-content: center; }
        
        /* Standard Article Card */
        .gel-article-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.04); height: 100%; transition: all 0.3s ease; display: flex; flex-direction: column; text-decoration: none; color: inherit; }
        .gel-article-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(249,115,22,0.08); border-color: rgba(249,115,22,0.15); }
        .gel-article-img { width: 100%; height: 200px; object-fit: cover; background: #e2e8f0; }
        .gel-article-body { padding: 24px; flex-grow: 1; display: flex; flex-direction: column; }
        .gel-article-meta { font-size: 12px; color: var(--gel-muted); margin-bottom: 12px; display: flex; align-items: center; gap: 12px; }
        .gel-article-cat { font-weight: 600; color: var(--gel-primary); text-transform: uppercase; letter-spacing: 0.5px; }
        .gel-article-title { font-size: 15px; margin-bottom: 12px; line-height: 1.4; font-weight: 800; }
        .gel-article-excerpt { font-size: 13px; color: var(--gel-muted); margin-bottom: 20px; flex-grow: 1; line-height: 1.6; }
        .gel-article-footer { display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 16px; margin-top: auto; }
        .gel-article-author { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; }
        .gel-article-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--gel-primary-light); color: var(--gel-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; }
        .gel-article-readmore { color: var(--gel-primary); font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 4px; }

        /* Animations */
        .anim-fade-up { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .anim-fade-up.anim-visible { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }

        @media (min-width: 992px) { .gel-featured-card { flex-direction: row; } .gel-featured-img { width: 45%; height: auto; } .gel-featured-content { width: 55%; padding: 30px 40px; } }
        @media (max-width: 991px) { .gel-section { padding: 60px 0; } .gel-page-header { padding: 60px 0 40px; } }
    </style>
</head>
<body>

    @include('partials.navbar')

    <!-- Page Header -->
    <header class="gel-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="anim-fade-up">Actualités et Conseils</h1>
                    <p class="anim-fade-up delay-1">Découvrez nos derniers articles, analyses fiscales et conseils pour optimiser la gestion de votre cabinet.</p>
                </div>
            </div>
        </div>
    </header>

    <section class="gel-section">
        <div class="container">
            <!-- Filtres -->
            <div class="gel-filters anim-fade-up">
                <button class="gel-filter-btn active">Tous</button>
                <button class="gel-filter-btn">Actualités Cabinet</button>
                <button class="gel-filter-btn">Conseils Fiscaux</button>
                <button class="gel-filter-btn">Comptabilité</button>
                <button class="gel-filter-btn">Gestion & IT</button>
            </div>

            <!-- Article à la une -->
            <a href="/blogue/article" class="gel-featured-card anim-fade-up delay-1">
                <div class="gel-featured-img">
                    <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Finance" style="width:100%; height:100%; object-fit:cover;">
                </div>
                <div class="gel-featured-content">
                    <div class="gel-article-meta mb-3">
                        <span class="gel-article-cat">Conseils Fiscaux</span>
                        <span><i class="bi-calendar3"></i> 24 Juin 2026</span>
                        <span><i class="bi-clock"></i> 5 min de lecture</span>
                    </div>
                    <h2 class="mb-3" style="font-size: clamp(1.4rem, 2vw, 1.8rem);">Facturation e-MECeF : Comment réussir son intégration avec la DGI</h2>
                    <p class="gel-article-excerpt" style="font-size: 14px;">Découvrez les bonnes pratiques pour connecter votre cabinet à l'API de la Direction Générale des Impôts du Bénin et automatiser vos factures normalisées.</p>
                    <div class="gel-article-footer mt-4" style="border:none; padding:0;">
                        <div class="gel-article-author">
                            <div class="gel-article-avatar">M</div>
                            <span>Michel Dossa</span>
                        </div>
                        <span class="gel-article-readmore">Lire l'article <i class="bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>


            

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

        // Filtrage des articles
        const filterBtns = document.querySelectorAll('.gel-filter-btn');
        const articles = document.querySelectorAll('.col-md-6.col-lg-4');
        const featuredArticle = document.querySelector('.gel-featured-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons
                filterBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                btn.classList.add('active');

                const filterValue = btn.textContent.trim().toLowerCase();

                // Filtrer l'article à la une (s'il existe)
                if (featuredArticle) {
                    const featuredCat = featuredArticle.querySelector('.gel-article-cat');
                    if (featuredCat) {
                        const catText = featuredCat.textContent.trim().toLowerCase();
                        if (filterValue === 'tous' || catText === filterValue) {
                            featuredArticle.style.display = 'flex';
                        } else {
                            featuredArticle.style.display = 'none';
                        }
                    }
                }

                // Filtrer les articles standard
                articles.forEach(article => {
                    const catEl = article.querySelector('.gel-article-cat');
                    if (catEl) {
                        const catText = catEl.textContent.trim().toLowerCase();
                        if (filterValue === 'tous' || catText === filterValue) {
                            article.style.display = 'block';
                        } else {
                            article.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
