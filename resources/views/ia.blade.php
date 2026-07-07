{{-- resources/views/ia.blade.php — Intelligence Artificielle GEL Cabinet --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Intelligence Artificielle | GEL Cabinet</title>
    <meta name="description" content="GEL Cabinet IA : 6 agents intelligents pour automatiser votre comptabilité, fiscalité, relances, rapprochements bancaires et trésorerie.">
    @include('partials.styles')
    @vite(['resources/css/app.css'])
    <style>
        .page-hero {
            background: linear-gradient(135deg, #0B1120 0%, #162044 100%);
            padding: 140px 0 80px;
            position: relative; overflow: hidden; text-align: center;
        }
        .page-hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 20% 30%, rgba(255,121,0,0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(139,92,246,0.08) 0%, transparent 50%);
        }
        .page-hero * { position: relative; z-index: 2; }
        .page-hero h1 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: white; }
        .page-hero p { color: rgba(255,255,255,0.6); max-width: 600px; margin: 16px auto 0; font-size: 1.05rem; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.3);
            color: #A78BFA; padding: 6px 16px; border-radius: 50px;
            font-size: 12px; font-weight: 600; margin-bottom: 24px;
        }

        .section-padding { padding: 80px 0; }
        .section-header { text-align: center; margin-bottom: 50px; }
        .section-header h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; }
        .section-header p { color: var(--gel-muted); max-width: 540px; margin: 12px auto 0; }
        .section-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,121,0,0.08); border: 1px solid rgba(255,121,0,0.15);
            padding: 4px 14px; border-radius: 50px; font-size: 12px;
            font-weight: 600; color: var(--gel-primary); margin-bottom: 16px;
        }

        .agent-card {
            background: white; border-radius: 16px; padding: 32px 28px;
            transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.04);
            height: 100%;
        }
        .agent-card:hover {
            transform: translateY(-6px); box-shadow: var(--shadow-lg);
            border-color: rgba(255,121,0,0.1);
        }
        .agent-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 16px;
        }
        .agent-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; }
        .agent-card p { color: var(--gel-muted); font-size: 14px; line-height: 1.7; margin-bottom: 12px; }
        .agent-tag {
            display: inline-block; padding: 3px 10px; border-radius: 50px;
            font-size: 11px; font-weight: 600;
        }

        .step-card {
            background: white; border-radius: 16px; padding: 32px 28px;
            border: 1px solid rgba(0,0,0,0.04); text-align: center; height: 100%;
            transition: all 0.3s ease;
        }
        .step-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .step-number {
            width: 48px; height: 48px; border-radius: 50%;
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 800; margin: 0 auto 16px;
        }
        .step-card h4 { font-size: 1.05rem; font-weight: 700; margin-bottom: 8px; }
        .step-card p { color: var(--gel-muted); font-size: 14px; line-height: 1.6; margin: 0; }

        .cta-section {
            background: linear-gradient(135deg, #0B1120 0%, #162044 100%);
            color: white;
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
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%);
            border: none; color: white;
            padding: 14px 36px; border-radius: 50px; font-weight: 700; font-size: 15px;
            transition: all 0.3s ease; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 16px rgba(255,121,0,0.3);
        }
        .btn-primary-gel:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,121,0,0.4); color: white; }
        .btn-outline-gel {
            background: transparent; border: 2px solid rgba(255,255,255,0.3); color: white;
            padding: 12px 32px; border-radius: 50px; font-weight: 700; font-size: 15px;
            transition: all 0.3s ease; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-outline-gel:hover { border-color: var(--gel-primary); color: var(--gel-primary); transform: translateY(-2px); }

        .stats-section {
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%);
            padding: 60px 0;
        }
        .stat-item { text-align: center; color: white; }
        .stat-item h3 { font-size: 2.4rem; font-weight: 800; margin: 0; }
        .stat-item p { opacity: 0.85; font-size: 14px; margin: 4px 0 0; }

        @media (max-width: 768px) { .page-hero { padding: 110px 0 60px; } .section-padding { padding: 60px 0; } }
    </style>
</head>
<body>

    @include('partials.navbar')

    <section class="page-hero">
        <div class="container">
            <div class="hero-badge anim-fade-up anim-visible"><i class="bi bi-robot"></i> Intelligence Artificielle</div>
            <h1 class="anim-fade-up delay-1">6 agents IA pour<br><span style="background:linear-gradient(135deg,#FF7900 0%,#FF9A3C 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">transformer votre cabinet</span></h1>
            <p class="anim-fade-up delay-2">Automatisez les tâches répétitives, détectez les anomalies et prenez de meilleures décisions — chaque suggestion est soumise à votre validation.</p>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="section-header anim-fade-up">
                <div class="section-badge"><i class="bi bi-cpu"></i> Agents intelligents</div>
                <h2>Nos <span style="background:linear-gradient(135deg,#FF7900 0%,#FF9A3C 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">6 agents IA</span></h2>
                <p>Chaque agent est spécialisé dans un domaine métier et travaille en continu sur vos données</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                    <div class="agent-card">
                        <div class="agent-icon" style="background:rgba(255,121,0,0.1);color:var(--gel-primary);"><i class="bi bi-calculator"></i></div>
                        <h3>Agent OHADA</h3>
                        <p>Analyse les écritures comptables, détecte les anomalies de saisie, propose des régularisations et vérifie la conformité SYSCOHADA.</p>
                        <span class="agent-tag" style="background:rgba(255,121,0,0.1);color:var(--gel-primary);">Comptabilité</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                    <div class="agent-card">
                        <div class="agent-icon" style="background:rgba(245,158,11,0.1);color:#D97706;"><i class="bi bi-cash-stack"></i></div>
                        <h3>Agent Fiscal</h3>
                        <p>Calcule et propose les déclarations TVA, IS, IRPP, CNSS. Suit les échéances fiscales et alerte en cas de risque de pénalités.</p>
                        <span class="agent-tag" style="background:rgba(245,158,11,0.1);color:#D97706;">Fiscalité</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="agent-card">
                        <div class="agent-icon" style="background:rgba(16,185,129,0.1);color:#10B981;"><i class="bi bi-bank"></i></div>
                        <h3>Agent Rapprochement</h3>
                        <p>Importe les relevés bancaires, propose des correspondances avec les écritures comptables et facilite les rapprochements.</p>
                        <span class="agent-tag" style="background:rgba(16,185,129,0.1);color:#10B981;">Banque</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                    <div class="agent-card">
                        <div class="agent-icon" style="background:rgba(59,130,246,0.1);color:#3B82F6;"><i class="bi bi-send"></i></div>
                        <h3>Agent Relance</h3>
                        <p>Analyse les échéances impayées, segmente les clients par risque et génère des relances personnalisées automatiques.</p>
                        <span class="agent-tag" style="background:rgba(59,130,246,0.1);color:#3B82F6;">Recouvrement</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                    <div class="agent-card">
                        <div class="agent-icon" style="background:rgba(139,92,246,0.1);color:#8B5CF6;"><i class="bi bi-file-text"></i></div>
                        <h3>Agent OCR</h3>
                        <p>Reconnaît et extrait automatiquement les données des factures, reçus et documents scannés. Alimente la comptabilité sans saisie.</p>
                        <span class="agent-tag" style="background:rgba(139,92,246,0.1);color:#8B5CF6;">Documents</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                    <div class="agent-card">
                        <div class="agent-icon" style="background:rgba(20,184,166,0.1);color:#14B8A6;"><i class="bi bi-graph-up"></i></div>
                        <h3>Agent Trésorerie</h3>
                        <p>Analyse les flux de trésorerie, détecte les déséquilibres, projette les encaissements et alerte sur les risques de découvert.</p>
                        <span class="agent-tag" style="background:rgba(20,184,166,0.1);color:#14B8A6;">Finance</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section anim-fade-up">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3 stat-item">
                    <h3>6</h3>
                    <p>Agents IA spécialisés</p>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <h3>95%</h3>
                    <p>Précision des suggestions</p>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <h3>-70%</h3>
                    <p>Saisie manuelle réduite</p>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <h3>24/7</h3>
                    <p>Analyse en continu</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding bg-white">
        <div class="container">
            <div class="section-header anim-fade-up">
                <div class="section-badge"><i class="bi bi-gear"></i> Fonctionnement</div>
                <h2>Comment ça <span style="background:linear-gradient(135deg,#FF7900 0%,#FF9A3C 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">marche</span></h2>
                <p>Un processus simple en 3 étapes : l'IA analyse, propose, vous validez</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Analyse automatique</h4>
                        <p>Nos agents IA scrutent vos données en continu : écritures, échéances, relevés bancaires, documents scannés.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Suggestions intelligentes</h4>
                        <p>Chaque agent vous propose des actions classées par priorité : anomalies, régularisations, relances, opportunités.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Vous gardez le contrôle</h4>
                        <p>Consultez, approuvez, rejetez ou exécutez chaque suggestion en un clic. L'IA apprend de vos décisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="section-header anim-fade-up">
                <div class="section-badge"><i class="bi bi-shield-check"></i> Sécurité</div>
                <h2>Une IA <span style="background:linear-gradient(135deg,#FF7900 0%,#FF9A3C 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">sûre et transparente</span></h2>
                <p>Nous plaçons la sécurité et la confidentialité de vos données au cœur de notre conception</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 anim-fade-up delay-1">
                    <div class="agent-card text-center" style="padding:40px 24px;">
                        <div style="font-size:40px;color:var(--gel-primary);margin-bottom:16px;"><i class="bi bi-eye-slash"></i></div>
                        <h4>Confidentialité totale</h4>
                        <p class="mb-0">Vos données ne sont jamais utilisées pour entraîner des modèles externes. Tout reste dans votre environnement.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-2">
                    <div class="agent-card text-center" style="padding:40px 24px;">
                        <div style="font-size:40px;color:var(--gel-primary);margin-bottom:16px;"><i class="bi bi-person-check"></i></div>
                        <h4>Validation humaine</h4>
                        <p class="mb-0">L'IA propose, vous disposez. Aucune action n'est exécutée sans votre approbation explicite.</p>
                    </div>
                </div>
                <div class="col-md-4 anim-fade-up delay-3">
                    <div class="agent-card text-center" style="padding:40px 24px;">
                        <div style="font-size:40px;color:var(--gel-primary);margin-bottom:16px;"><i class="bi bi-journal-code"></i></div>
                        <h4>Journal d'apprentissage</h4>
                        <p class="mb-0">Chaque décision est enregistrée dans un journal d'apprentissage pour améliorer continuellement les suggestions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container anim-fade-up">
            <h2>Prêt à libérer le potentiel de l'IA ?</h2>
            <p>Activez les agents IA dans votre espace GEL Cabinet dès aujourd'hui.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="/register" class="btn btn-primary-gel"><i class="bi bi-rocket-takeoff"></i> Essai gratuit</a>
                <a href="/contact" class="btn btn-outline-gel"><i class="bi bi-telephone"></i> Demander une démo</a>
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
