{{-- resources/views/services/index.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nos Services | GEL Cabinet</title>
    <meta name="description" content="Services GEL Cabinet : Consultation, Fiscal, IT, Social & Paie, Juridique, Logiciel Comptabilité et Administration.">
    @include('partials.styles')
    @vite(['resources/css/app.css'])
    <style>
        .page-hero {
            background: linear-gradient(135deg, #0B1120 0%, #162044 100%); padding: 140px 0 80px;
            position: relative; overflow: hidden; text-align: center;
        }
        .page-hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 50%, rgba(255,121,0,0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 30%, rgba(255,121,0,0.05) 0%, transparent 50%);
        }
        .page-hero * { position: relative; z-index: 2; }
        .page-hero h1 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: white; }
        .page-hero p { color: rgba(255,255,255,0.6); max-width: 600px; margin: 16px auto 0; font-size: 1.05rem; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,121,0,0.15); border: 1px solid rgba(255,121,0,0.3);
            color: var(--gel-primary); padding: 6px 16px; border-radius: 50px;
            font-size: 12px; font-weight: 600; margin-bottom: 24px;
        }

        .section-padding { padding: 80px 0; }
        .section-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,121,0,0.08); border: 1px solid rgba(255,121,0,0.15);
            padding: 4px 14px; border-radius: 50px; font-size: 12px;
            font-weight: 600; color: var(--gel-primary); margin-bottom: 16px;
        }

        .service-card {
            background: white; border-radius: 16px; padding: 32px 28px;
            transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.04);
            height: 100%; display: flex; flex-direction: column;
        }
        .service-card:hover {
            transform: translateY(-6px); box-shadow: var(--shadow-lg);
            border-color: rgba(255,121,0,0.1);
        }
        .service-card.featured { border: 2px solid var(--gel-primary); position: relative; }
        .service-card.featured::before {
            content: 'Populaire'; position: absolute; top: 12px; right: 12px;
            background: var(--gel-primary); color: #fff; padding: 3px 10px;
            border-radius: 50px; font-size: 10px; font-weight: 700;
        }
        .service-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 16px;
        }
        .service-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
        .service-card p { color: var(--gel-muted); font-size: 14px; line-height: 1.7; flex: 1; margin-bottom: 16px; }
        .service-link {
            font-size: 13px; font-weight: 600; color: var(--gel-primary); text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;
        }
        .service-link:hover { gap: 8px; color: var(--gel-primary-hov); }

        .cta-section {
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%); padding: 80px 0; text-align: center; color: white;
        }
        .cta-section h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; }
        .cta-section p { opacity: 0.85; max-width: 500px; margin: 12px auto 32px; }
        .btn-cta-white {
            background: white; color: var(--gel-primary); border: none;
            padding: 14px 40px; border-radius: 50px; font-weight: 700; font-size: 15px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            transition: all 0.3s; box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); color: var(--gel-primary-hov); }

        @media (max-width: 768px) { .page-hero { padding: 110px 0 60px; } .section-padding { padding: 60px 0; } }
    </style>
</head>
<body>

    @include('partials.navbar')

    <section class="page-hero">
        <div class="container">
            <div class="hero-badge anim-fade-up anim-visible"><i class="bi bi-star"></i> Expertise pluridisciplinaire</div>
            <h1 class="anim-fade-up delay-1">Nos <span>services</span></h1>
            <p class="anim-fade-up delay-2">Des services comptables, fiscaux, juridiques et RH pour accompagner votre entreprise au quotidien.</p>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                    <div class="service-card featured">
                        <div class="service-icon" style="background:#FEF3C7;color:#D97706;"><i class="bi bi-calculator"></i></div>
                        <h3>Comptabilité</h3>
                        <p>Plan comptable SYSCOHADA complet, saisie des journaux, balance, bilan, compte de résultat. Conforme aux normes OHADA avec production d'états financiers.</p>
                        <a href="/services/comptabilite" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                    <div class="service-card">
                        <div class="service-icon" style="background:#FEE2E2;color:#DC2626;"><i class="bi bi-receipt"></i></div>
                        <h3>Fiscal</h3>
                        <p>Optimisation fiscale, déclarations TVA, IRPP, CNSS, IS. Conformité e-MECeF et factures normalisées DCI. Accompagnement sur mesure.</p>
                        <a href="/services/fiscal" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="service-card">
                        <div class="service-icon" style="background:#E0E7FF;color:#4F46E5;"><i class="bi bi-bank2"></i></div>
                        <h3>Juridique</h3>
                        <p>Constitution, formalités, rédaction de contrats, veille juridique. Accompagnement pour sécuriser vos décisions et rester en conformité.</p>
                        <a href="/services/juridique" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                    <div class="service-card">
                        <div class="service-icon" style="background:#D1FAE5;color:#059669;"><i class="bi bi-people"></i></div>
                        <h3>Social & Paie</h3>
                        <p>Gestion des employés, contrats, paie avec barèmes IRPP/CNSS Bénin intégrés, congés, absences et déclarations sociales.</p>
                        <a href="/services/social-paie" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                    <div class="service-card">
                        <div class="service-icon" style="background:#F3E8FF;color:#9333EA;"><i class="bi bi-shield-lock"></i></div>
                        <h3>Administration</h3>
                        <p>Gestion des courriers, contrats, suivi des échéances, numérisation et classement. Déléguez vos tâches administratives.</p>
                        <a href="/services/administration" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="service-card">
                        <div class="service-icon" style="background:#DBEAFE;color:#2563EB;"><i class="bi bi-chat-dots"></i></div>
                        <h3>Consultation</h3>
                        <p>Conseil en stratégie d'entreprise, planification financière, recherche de financement et accompagnement des dirigeants.</p>
                        <a href="/services/consultation" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                    <div class="service-card">
                        <div class="service-icon" style="background:#FCE7F3;color:#DB2777;"><i class="bi bi-laptop"></i></div>
                        <h3>IT</h3>
                        <p>Support technique, maintenance, sécurité des données, hébergement et infrastructure IT pour votre cabinet.</p>
                        <a href="/services/it" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                    <div class="service-card">
                        <div class="service-icon" style="background:#FFF7ED;color:#C2410C;"><i class="bi bi-cpu"></i></div>
                        <h3>Logiciel Comptabilité</h3>
                        <p>Licence standalone ou SaaS conforme OHADA/SYSCOHADA. Plan comptable, journaux, bilan, déclarations. Achat en ligne.</p>
                        <a href="/logiciel-comptabilite" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="service-card">
                        <div class="service-icon" style="background:#E5E7EB;color:#374151;"><i class="bi bi-gear"></i></div>
                        <h3>ERP & DAE</h3>
                        <p>Direction Administrative (DAE) : ERP intégré, stocks, achats, trésorerie, gestion des établissements et entités.</p>
                        <a href="/services/erp" class="service-link">En savoir plus <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2 class="anim-fade-up">Besoin d'un accompagnement personnalisé ?</h2>
            <p class="anim-fade-up delay-1">Notre équipe d'experts est là pour vous conseiller et vous accompagner dans la gestion de votre cabinet.</p>
            <div class="anim-fade-up delay-2">
                <a href="/contact" class="btn-cta-white"><i class="bi bi-envelope"></i> Nous contacter</a>
            </div>
        </div>
    </section>

    @include('partials.footer')
    @include('partials.ai-chat-floating')

<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('anim-visible');
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.anim-fade-up:not(.anim-visible)').forEach(el => observer.observe(el));
</script>
</body>
</html>
