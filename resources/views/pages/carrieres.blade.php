<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrières | GEL Cabinet</title>
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
        .gel-section-title { font-family: var(--font-heading); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--gel-dark); letter-spacing: -0.5px; line-height: 1.2; margin-bottom: 14px; }
        .gel-section-sub { font-size: 15px; color: var(--gel-muted); margin-bottom: 3rem; max-width: 700px; line-height: 1.7; }

        /* Job Card */
        .gel-job-card { background: white; border-radius: 16px; padding: 32px; border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; }
        .gel-job-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.04); border-color: rgba(249,115,22,0.3); }
        .gel-job-title { font-size: 15px; font-weight: 800; margin-bottom: 8px; color: var(--gel-dark); }
        .gel-job-meta { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; font-size: 12px; color: var(--gel-muted); }
        .gel-job-meta span { display: inline-flex; align-items: center; gap: 6px; }
        .gel-job-desc { flex-grow: 1; color: var(--gel-muted); font-size: 13px; margin-bottom: 24px; line-height: 1.6; }
        
        .gel-btn { display: inline-flex; align-items: center; gap: 8px; padding: 13px; background: var(--gel-primary); color: white; font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; font-size: 14px; }
        .gel-btn:hover { background: var(--gel-primary-hover); color: white; }
        .gel-btn-outline { background: transparent; color: var(--gel-primary); border: 1.5px solid var(--gel-primary); padding: 11px 24px; }
        .gel-btn-outline:hover { background: var(--gel-primary); color: white; }

        /* Benefit Item */
        .gel-benefit-item { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 32px; }
        .gel-benefit-icon { min-width: 44px; height: 44px; border-radius: 10px; background: var(--gel-primary-light); color: var(--gel-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .gel-benefit-content h5 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
        .gel-benefit-content p { color: var(--gel-muted); font-size: 13px; margin-bottom: 0; line-height: 1.6; }

        /* Form */
        .gel-form-card { background: white; border-radius: 24px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.03); }
        .form-control, .form-select { border-radius: 6px; padding: 11px 14px; border-color: #e2e8f0; font-size: 13.5px; }
        .form-control:focus, .form-select:focus { border-color: var(--gel-primary); box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .form-label { font-weight: 600; color: var(--gel-dark); font-size: 12px; margin-bottom: 6px; }

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
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="anim-fade-up">Rejoignez l'aventure GEL</h1>
                    <p class="anim-fade-up delay-1">Découvrez nos opportunités et venez contribuer à la digitalisation des cabinets d'expertise de demain.</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Avantages -->
    <section class="gel-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 anim-fade-up">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Équipe GEL" class="img-fluid rounded-4 shadow-sm" style="border: 1px solid rgba(0,0,0,0.05);">
                </div>
                <div class="col-lg-6 anim-fade-up delay-1">

                    <h2 class="gel-section-title">Pourquoi nous rejoindre ?</h2>
                    <p class="gel-section-sub">Travailler chez GEL Cabinet, c'est évoluer dans un environnement stimulant qui valorise l'innovation, l'autonomie et le bien-être de chaque collaborateur.</p>
                    
                    <div class="gel-benefit-item">
                        <div class="gel-benefit-icon"><i class="bi-laptop"></i></div>
                        <div class="gel-benefit-content">
                            <h5>Télétravail Flexible</h5>
                            <p>Un équilibre vie pro/perso respecté avec jusqu'à 3 jours de télétravail par semaine.</p>
                        </div>
                    </div>
                    
                    <div class="gel-benefit-item">
                        <div class="gel-benefit-icon"><i class="bi-graph-up-arrow"></i></div>
                        <div class="gel-benefit-content">
                            <h5>Évolution Continue</h5>
                            <p>Des formations régulières et des perspectives d'évolution claires au sein de l'entreprise.</p>
                        </div>
                    </div>
                    
                    <div class="gel-benefit-item mb-0">
                        <div class="gel-benefit-icon"><i class="bi-cup-hot"></i></div>
                        <div class="gel-benefit-content">
                            <h5>Cadre Agréable</h5>
                            <p>Des locaux modernes, des événements d'équipe mensuels et une excellente mutuelle d'entreprise.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Offres -->
    <section class="gel-section gel-section-alt" id="offres">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <h2 class="gel-section-title anim-fade-up">Postes Ouverts</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-1">Parcourez nos offres actuelles et trouvez le poste qui correspond à vos ambitions.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Offre 1 -->
                <div class="col-md-6 anim-fade-up">
                    <div class="gel-job-card">
                        <h4 class="gel-job-title">Développeur Full-Stack (Vue.js / Laravel)</h4>
                        <div class="gel-job-meta">
                            <span><i class="bi-geo-alt"></i> Cotonou ou Remote</span>
                            <span><i class="bi-clock"></i> Temps plein (CDI)</span>
                            <span><i class="bi-tag"></i> Tech / Ingénierie</span>
                        </div>
                        <p class="gel-job-desc">Rejoignez notre équipe technique pour développer de nouveaux modules de la plateforme (Tontine, Mobile Money) et optimiser les performances.</p>
                        <div>
                            <a href="#candidature" class="gel-btn gel-btn-outline"><i class="bi-send"></i> Postuler</a>
                        </div>
                    </div>
                </div>
                
                <!-- Offre 2 -->
                <div class="col-md-6 anim-fade-up delay-1">
                    <div class="gel-job-card">
                        <h4 class="gel-job-title">Expert-Comptable (Spécialiste SYSCOHADA)</h4>
                        <div class="gel-job-meta">
                            <span><i class="bi-geo-alt"></i> Cotonou</span>
                            <span><i class="bi-clock"></i> Temps plein (CDI)</span>
                            <span><i class="bi-tag"></i> Expertise Métier</span>
                        </div>
                        <p class="gel-job-desc">Apportez votre expertise métier pour améliorer nos algorithmes de liasse fiscale TAFIRE et conseiller nos cabinets clients.</p>
                        <div>
                            <a href="#candidature" class="gel-btn gel-btn-outline"><i class="bi-send"></i> Postuler</a>
                        </div>
                    </div>
                </div>
                
                <!-- Offre 3 -->
                <div class="col-md-6 anim-fade-up delay-2">
                    <div class="gel-job-card">
                        <h4 class="gel-job-title">Customer Success Manager (Conformité DGI)</h4>
                        <div class="gel-job-meta">
                            <span><i class="bi-geo-alt"></i> Cotonou (Hybride)</span>
                            <span><i class="bi-clock"></i> Temps plein (CDI)</span>
                            <span><i class="bi-tag"></i> Relation Client</span>
                        </div>
                        <p class="gel-job-desc">Formez et accompagnez nos clients dans le déploiement de l'outil de facturation e-MECeF et la numérisation de leurs processus.</p>
                        <div>
                            <a href="#candidature" class="gel-btn gel-btn-outline"><i class="bi-send"></i> Postuler</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Formulaire Candidature -->
    <section class="gel-section" id="candidature">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="gel-form-card anim-fade-up">
                        <div class="text-center mb-5">
                            <h2 class="gel-section-title" style="font-size: 2rem;">Candidature Spontanée / Offre</h2>
                            <p class="text-muted">Vous ne trouvez pas d'offre qui vous correspond ? Envoyez-nous votre candidature spontanée.</p>
                        </div>
                        
                        <form action="#" method="POST" @submit.prevent>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" class="form-control" placeholder="Jean" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control" placeholder="Dupont" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" placeholder="jean.dupont@email.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" placeholder="+33 6 12 34 56 78">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Poste visé / Offre</label>
                                    <select class="form-select">
                                        <option value="" selected disabled>Sélectionnez un poste ou Candidature spontanée</option>
                                        <option value="dev">Développeur Full-Stack (Vue.js / Laravel)</option>
                                        <option value="consultant">Consultant Fonctionnel Comptabilité</option>
                                        <option value="csm">Customer Success Manager (B2B)</option>
                                        <option value="spontanee">Candidature spontanée</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Lien LinkedIn / Portfolio (optionnel)</label>
                                    <input type="url" class="form-control" placeholder="https://linkedin.com/in/jeandupont">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">CV (Format PDF)</label>
                                    <input type="file" class="form-control" accept=".pdf" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message de motivation</label>
                                    <textarea class="form-control" rows="5" placeholder="Parlez-nous de vous, de ce qui vous motive et de ce que vous pouvez apporter à l'équipe..."></textarea>
                                </div>
                                <div class="col-12 text-center mt-5">
                                    <button type="submit" class="gel-btn" style="padding: 16px 40px; font-size: 1.1rem; width: 100%; justify-content: center;">
                                        <i class="bi-send-fill"></i> Envoyer ma candidature
                                    </button>
                                </div>
                            </div>
                        </form>
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
    </script>
</body>
</html>