{{-- resources/views/pages/nos-modules.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nos Modules | GEL Cabinet</title>
    <meta name="description" content="Modules GEL Cabinet : CRM Clients, GED, Pôles & Missions, Comptabilité et ERP Intégré. Plateforme multi-portail conforme OHADA.">
    @include('partials.styles')
    @vite(['resources/css/app.css'])
    <style>
        .page-hero {
            background: linear-gradient(135deg, #0B1120 0%, #162044 100%);
            padding: 140px 0 80px; position: relative; overflow: hidden; text-align: center;
        }
        .page-hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 50%, rgba(255,121,0,0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 30%, rgba(255,121,0,0.05) 0%, transparent 50%);
        }
        .page-hero * { position: relative; z-index: 2; }
        .page-hero h1 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: white; }
        .page-hero p { color: rgba(255,255,255,0.6); max-width: 600px; margin: 16px auto 0; font-size: 1.05rem; }
        .gradient-text { background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        .pole-card {
            background: white; border-radius: 16px; padding: 36px 28px;
            transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.04);
            height: 100%;
        }
        .pole-card:hover {
            transform: translateY(-6px); box-shadow: var(--shadow-lg);
            border-color: rgba(255,121,0,0.1);
        }
        .pole-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 18px;
        }
        .pole-card h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; }
        .pole-card p { color: var(--gel-muted); font-size: 14px; line-height: 1.7; margin-bottom: 16px; }
        .pole-modules { display: flex; flex-wrap: wrap; gap: 6px; }
        .pole-module-tag {
            background: var(--gel-primary-soft); color: var(--gel-primary);
            padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;
        }

        .stats-section {
            background: linear-gradient(135deg, #0B1120 0%, #162044 100%); color: white;
            position: relative; overflow: hidden; padding: 60px 0;
        }
        .stats-section::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255,121,0,0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .stat-item { text-align: center; position: relative; z-index: 1; }
        .stat-item h3 {
            font-size: 2.4rem; font-weight: 800; margin: 0;
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%); -webkit-background-clip: text;
            -webkit-text-fill-color: transparent; background-clip: text;
        }
        .stat-item p { opacity: 0.7; font-size: 14px; margin: 4px 0 0; }

        .cta-section {
            background: linear-gradient(135deg, #0B1120 0%, #162044 100%); color: white;
            padding: 80px 0; text-align: center; position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: ''; position: absolute; top: -50%; right: -20%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(255,121,0,0.12) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .cta-section * { position: relative; z-index: 1; }
        .cta-section h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; }
        .cta-section p { color: rgba(255,255,255,0.6); max-width: 500px; margin: 12px auto 32px; }

        .btn-primary-gel {
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%); border: none; color: white;
            padding: 14px 36px; border-radius: 50px; font-weight: 700; font-size: 15px;
            transition: all 0.3s ease; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 16px rgba(255,121,0,0.3);
        }
        .btn-primary-gel:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,121,0,0.4); color: white; }

        @media (max-width: 768px) {
            .page-hero { padding: 110px 0 60px; }
            .gel-section { padding: 60px 0; }
        }
    </style>
</head>
<body>

    @include('partials.navbar')

    <section class="page-hero">
        <div class="container">
            <div class="hero-badge anim-fade-up anim-visible"><i class="bi bi-grid-3x3-gap"></i> Plateforme modulaire</div>
            <h1 class="anim-fade-up anim-visible delay-1">Explorez nos <span class="gradient-text">modules</span></h1>
            <p class="anim-fade-up anim-visible delay-2">Activez les modules selon les besoins de votre cabinet. Chaque pôle regroupe des fonctionnalités interconnectées.</p>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                    <div class="pole-card">
                        <div class="pole-icon" style="background:#DBEAFE;color:#2563EB;"><i class="bi bi-shield-lock"></i></div>
                        <h3>Pôle Administration</h3>
                        <p>Gestion centralisée des clients, fournisseurs, contrats et documents. Tableau de bord complet avec indicateurs clés.</p>
                        <div class="pole-modules">
                            <span class="pole-module-tag"><i class="bi bi-check"></i> CRM</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> GED</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Messagerie</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Planning</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Rapports</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                    <div class="pole-card">
                        <div class="pole-icon" style="background:#FEF3C7;color:#D97706;"><i class="bi bi-calculator"></i></div>
                        <h3>Pôle Comptabilité / Finance</h3>
                        <p>Plan comptable SYSCOHADA complet, journaux, balance, bilan et compte de résultat. Génération automatique des états financiers.</p>
                        <div class="pole-modules">
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Plan comptable</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Journaux</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Balance</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Bilan/CRP</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> TVA</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="pole-card">
                        <div class="pole-icon" style="background:#FEE2E2;color:#DC2626;"><i class="bi bi-receipt"></i></div>
                        <h3>Pôle Fiscal</h3>
                        <p>Déclarations fiscales intégrées, calcul automatique TVA/IRPP/IS/CNSS, échéancier avec alertes J-15, J-7, J-1.</p>
                        <div class="pole-modules">
                            <span class="pole-module-tag"><i class="bi bi-check"></i> TVA</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> IRPP</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> CNSS</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> IS</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> e-MECeF</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                    <div class="pole-card">
                        <div class="pole-icon" style="background:#D1FAE5;color:#059669;"><i class="bi bi-people"></i></div>
                        <h3>Pôle Social & Paie</h3>
                        <p>Gestion des employés, contrats, paie avec barèmes Bénin intégrés. Tableau de bord RH, congés, absences, planning.</p>
                        <div class="pole-modules">
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Employés</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Contrats</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Paie</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Congés</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> CNSS</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                    <div class="pole-card">
                        <div class="pole-icon" style="background:#E0E7FF;color:#4F46E5;"><i class="bi bi-bank2"></i></div>
                        <h3>Pôle Juridique</h3>
                        <p>Constitution de sociétés, rédaction de contrats, suivi des contentieux, assemblées générales, bibliothèque d'actes.</p>
                        <div class="pole-modules">
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Sociétés</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Contrats</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Contentieux</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Assemblées</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="pole-card">
                        <div class="pole-icon" style="background:#F3E8FF;color:#9333EA;"><i class="bi bi-laptop"></i></div>
                        <h3>Pôle IT</h3>
                        <p>Support technique, maintenance, tickets d'incidents, gestion des accès, monitoring des serveurs et sauvegardes.</p>
                        <div class="pole-modules">
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Tickets</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Maintenance</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Monitoring</span>
                            <span class="pole-module-tag"><i class="bi bi-check"></i> Sauvegardes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3 stat-item anim-fade-up">
                    <h3>15+</h3>
                    <p>Modules interconnectés</p>
                </div>
                <div class="col-6 col-md-3 stat-item anim-fade-up delay-1">
                    <h3>6</h3>
                    <p>Pôles métier</p>
                </div>
                <div class="col-6 col-md-3 stat-item anim-fade-up delay-2">
                    <h3>100%</h3>
                    <p>Conforme OHADA</p>
                </div>
                <div class="col-6 col-md-3 stat-item anim-fade-up delay-3">
                    <h3>3</h3>
                    <p>Pays couverts</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2 class="anim-fade-up">Prêt à activer vos modules ?</h2>
            <p class="anim-fade-up delay-1">Configurez votre espace en quelques minutes et activez les modules selon vos besoins.</p>
            <div class="anim-fade-up delay-2">
                <a href="/register" class="btn btn-primary-gel">
                    <i class="bi bi-rocket-takeoff"></i> Commencer gratuitement
                </a>
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
