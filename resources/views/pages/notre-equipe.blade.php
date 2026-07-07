<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notre Équipe | GEL Cabinet</title>
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

        /* Team Card */
        .gel-team-card { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: all 0.3s ease; height: 100%; border: 1px solid rgba(0,0,0,0.04); position: relative; }
        .gel-team-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(249,115,22,0.1); border-color: rgba(249,115,22,0.2); }
        .gel-team-img-wrap { height: 260px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .gel-team-img-wrap i { font-size: 80px; color: #94a3b8; }
        .gel-team-info { padding: 30px; text-align: center; }
        .gel-team-name { font-size: 15px; margin-bottom: 4px; font-weight: 800; }
        .gel-team-role { color: var(--gel-primary); font-weight: 600; font-size: 12px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        .gel-team-desc { font-size: 13px; color: var(--gel-muted); margin-bottom: 20px; line-height: 1.6; }
        
        .gel-social-links { display: flex; justify-content: center; gap: 12px; }
        .gel-social-link { width: 36px; height: 36px; border-radius: 50%; background: var(--gel-bg); display: flex; align-items: center; justify-content: center; color: var(--gel-muted); text-decoration: none; transition: all 0.2s ease; }
        .gel-social-link:hover { background: var(--gel-primary); color: white; }

        /* CTA */
        .gel-cta-band { background: var(--gel-dark); padding: 80px 0; color: white; position: relative; overflow: hidden; }
        .gel-cta-band h2 { color: white; margin-bottom: 1rem; }
        .gel-cta-band p { color: rgba(255,255,255,0.7); font-size: 1.15rem; margin-bottom: 0; }
        .gel-btn-white { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: white; color: var(--gel-dark); font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.3s ease; font-size: 14px; }
        .gel-btn-white:hover { background: var(--gel-primary); color: white; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(249,115,22,0.2); }

        /* Animations */
        .anim-fade-up { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .anim-fade-up.anim-visible { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }

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
                    <h1 class="anim-fade-up">Rencontrez nos experts</h1>
                    <p class="anim-fade-up delay-1">Une équipe de professionnels passionnés, dédiés à l'innovation et à la réussite de votre cabinet.</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Direction -->
    <section class="gel-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <h2 class="gel-section-title anim-fade-up">Direction & Management</h2>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <!-- Membre 1 -->
                <div class="col-md-6 col-lg-4 anim-fade-up">
                    <div class="gel-team-card">
                        <div class="gel-team-img-wrap" style="background: linear-gradient(135deg, #e2e8f0, #cbd5e1);">
                            <i class="bi-person"></i>
                        </div>
                        <div class="gel-team-info">
                            <h4 class="gel-team-name">Jean Dupont</h4>
                            <div class="gel-team-role">Directeur Général (CEO)</div>
                            <p class="gel-team-desc">Expert-comptable de formation, Jean a fondé GEL avec la vision de transformer le quotidien des cabinets.</p>
                            <div class="gel-social-links">
                                <a href="#" class="gel-social-link"><i class="bi-linkedin"></i></a>
                                <a href="#" class="gel-social-link"><i class="bi-twitter-x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Membre 2 -->
                <div class="col-md-6 col-lg-4 anim-fade-up delay-1">
                    <div class="gel-team-card">
                        <div class="gel-team-img-wrap" style="background: linear-gradient(135deg, #ffedd5, #fdba74);">
                            <i class="bi-person" style="color: #ea580c;"></i>
                        </div>
                        <div class="gel-team-info">
                            <h4 class="gel-team-name">Marie Claire</h4>
                            <div class="gel-team-role">Directrice Technique (CTO)</div>
                            <p class="gel-team-desc">Architecte logicielle passionnée, Marie s'assure que notre plateforme est robuste, rapide et sécurisée.</p>
                            <div class="gel-social-links">
                                <a href="#" class="gel-social-link"><i class="bi-linkedin"></i></a>
                                <a href="#" class="gel-social-link"><i class="bi-github"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Experts Métiers -->
    <section class="gel-section gel-section-alt">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center">
                    <h2 class="gel-section-title anim-fade-up">Nos Experts Métiers</h2>
                    <p class="gel-section-sub mx-auto anim-fade-up delay-1">Des spécialistes de chaque domaine pour concevoir des modules qui répondent précisément à vos attentes.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 anim-fade-up">
                    <div class="gel-team-card">
                        <div class="gel-team-img-wrap">
                            <i class="bi-person"></i>
                        </div>
                        <div class="gel-team-info">
                            <h4 class="gel-team-name">Marc L.</h4>
                            <div class="gel-team-role">Lead Comptabilité</div>
                            <p class="gel-team-desc">Supervise le développement du module comptable.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 anim-fade-up delay-1">
                    <div class="gel-team-card">
                        <div class="gel-team-img-wrap">
                            <i class="bi-person"></i>
                        </div>
                        <div class="gel-team-info">
                            <h4 class="gel-team-name">Sophie R.</h4>
                            <div class="gel-team-role">Expert Juridique</div>
                            <p class="gel-team-desc">Garante de la conformité légale de nos solutions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 anim-fade-up delay-2">
                    <div class="gel-team-card">
                        <div class="gel-team-img-wrap">
                            <i class="bi-person"></i>
                        </div>
                        <div class="gel-team-info">
                            <h4 class="gel-team-name">Thomas B.</h4>
                            <div class="gel-team-role">Responsable Produit</div>
                            <p class="gel-team-desc">À l'écoute de nos clients pour améliorer nos outils.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 anim-fade-up delay-3">
                    <div class="gel-team-card">
                        <div class="gel-team-img-wrap">
                            <i class="bi-person"></i>
                        </div>
                        <div class="gel-team-info">
                            <h4 class="gel-team-name">Amélie D.</h4>
                            <div class="gel-team-role">Support Client</div>
                            <p class="gel-team-desc">Toujours prête à vous accompagner au quotidien.</p>
                        </div>
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
                    <h2>Envie de faire partie de l'équipe ?</h2>
                    <p>Découvrez nos offres d'emploi et rejoignez l'aventure GEL Cabinet.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 anim-fade-up delay-1">
                    <a href="/carrieres" class="gel-btn-white"><i class="bi-briefcase"></i> Voir les offres</a>
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