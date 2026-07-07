<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tarifs & Licences | GEL Cabinet</title>
    <meta name="description" content="Tarifs des abonnements SaaS GEL Cabinet et licence standalone du logiciel de comptabilité. Plans mensuels et annuels pour cabinets comptables au Bénin.">
    <meta property="og:title" content="Tarifs & Licences | GEL Cabinet">
    <meta property="og:description" content="Découvrez nos formules adaptées à votre cabinet : SaaS modulaire ou licence standalone.">
    <meta property="og:type" content="website">
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
        .page-hero p { color: rgba(255,255,255,0.6); max-width: 560px; margin: 16px auto 0; font-size: 1.05rem; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,121,0,0.15); border: 1px solid rgba(255,121,0,0.3);
            color: var(--gel-primary); padding: 6px 16px; border-radius: 50px;
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

        .pricing-card {
            background: white; border-radius: 20px; padding: 40px 32px;
            border: 1px solid rgba(0,0,0,0.04); transition: all 0.3s ease;
            position: relative; height: 100%;
        }
        .pricing-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); }
        .pricing-card.featured {
            border: 2px solid var(--gel-primary);
            box-shadow: 0 8px 32px rgba(255,121,0,0.15);
        }
        .pricing-card.featured .pricing-badge {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%); color: white;
            padding: 4px 20px; border-radius: 50px; font-size: 12px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        }
        .pricing-card h4 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .pricing-card .pricing-subtitle { font-size: 14px; color: var(--gel-muted); }
        .pricing-card .pricing-amount { margin: 24px 0; }
        .pricing-card .pricing-amount .amount { font-size: 48px; font-weight: 800; color: var(--gel-dark); }
        .pricing-card .pricing-amount .period { font-size: 16px; color: var(--gel-muted); }
        .pricing-card .pricing-features { list-style: none; padding: 0; margin: 0 0 32px; }
        .pricing-card .pricing-features li { padding: 8px 0; font-size: 14px; display: flex; align-items: center; gap: 8px; color: var(--gel-dark); }
        .pricing-card .pricing-features li i { color: #10B981; font-size: 16px; }

        .license-card {
            background: white; border-radius: 16px; padding: 36px 28px;
            border: 1px solid rgba(0,0,0,0.04); transition: all 0.3s ease; height: 100%;
            text-align: center;
        }
        .license-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); }
        .license-card h4 { font-size: 18px; font-weight: 800; }
        .license-price { font-family: var(--font-heading); font-size: 2.2rem; font-weight: 900; color: var(--gel-dark); }
        .license-price span { font-size: 14px; font-weight: 500; color: var(--gel-muted); }

        .toggle-wrap { display: inline-flex; align-items: center; gap: 12px; background: white; border-radius: 100px; padding: 4px; border: 1px solid rgba(0,0,0,0.06); }
        .toggle-btn { padding: 8px 20px; font-size: 13px; font-weight: 600; border: none; background: transparent; border-radius: 100px; cursor: pointer; transition: all 0.3s; color: var(--gel-muted); }
        .toggle-btn.active { background: var(--gel-primary); color: white; box-shadow: 0 4px 12px rgba(255,121,0,0.2); }
        .toggle-badge { font-size: 11px; background: rgba(255,121,0,0.15); color: var(--gel-primary); padding: 2px 10px; border-radius: 100px; font-weight: 700; margin-left: 4px; }

        .compare-table { border-collapse: separate; border-spacing: 0; width: 100%; }
        .compare-table th { padding: 16px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gel-muted); border-bottom: 2px solid rgba(0,0,0,0.06); background: white; }
        .compare-table th:first-child { border-radius: 10px 0 0 0; }
        .compare-table th:last-child { border-radius: 0 10px 0 0; }
        .compare-table td { padding: 14px 20px; font-size: 13.5px; border-bottom: 1px solid rgba(0,0,0,0.04); background: white; }
        .compare-table tr:last-child td:first-child { border-radius: 0 0 0 10px; }
        .compare-table tr:last-child td:last-child { border-radius: 0 0 10px 0; }
        .compare-table td:first-child { font-weight: 600; }
        .compare-table .bi-check-lg { color: #22c55e; font-size: 16px; }
        .compare-table .bi-x-lg { color: #ef4444; font-size: 14px; }

        .btn-primary-gel {
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%); border: none; color: white;
            padding: 12px 32px; border-radius: 50px; font-weight: 700; font-size: 15px;
            transition: all 0.3s ease; text-decoration: none;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 16px rgba(255,121,0,0.3);
        }
        .btn-primary-gel:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(255,121,0,0.4); color: white; }
        .btn-outline-gel {
            background: transparent; border: 2px solid var(--gel-primary); color: var(--gel-primary);
            padding: 10px 28px; border-radius: 50px; font-weight: 700; font-size: 15px;
            transition: all 0.3s ease; text-decoration: none;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-outline-gel:hover { background: var(--gel-primary); color: white; transform: translateY(-2px); }

        .cta-section {
            background: linear-gradient(135deg, #FF7900 0%, #FF9A3C 100%); padding: 60px 0; text-align: center; color: white;
        }
        .cta-section h2 { font-size: clamp(1.4rem, 2.5vw, 2rem); font-weight: 900; }
        .cta-section p { color: rgba(255,255,255,0.85); font-size: 15px; margin-top: 8px; }
        .btn-cta-white {
            display: inline-flex; align-items: center; gap: 8px;
            background: white; color: var(--gel-primary); font-size: 14px; font-weight: 700;
            padding: 12px 28px; border-radius: 50px; text-decoration: none;
            transition: all 0.3s; box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); color: var(--gel-primary-hov); }
        .btn-cta-outline {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.15); color: white; font-size: 14px; font-weight: 700;
            padding: 12px 28px; border-radius: 50px; text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.4); transition: all 0.3s;
        }
        .btn-cta-outline:hover { background: rgba(255,255,255,0.25); color: white; }

        @media (max-width: 768px) { .page-hero { padding: 110px 0 60px; } .gel-section { padding: 60px 0; } }
    </style>
</head>
<body>

@include('partials.navbar')

<section class="page-hero">
    <div class="container">
        <div class="hero-badge anim-fade-up anim-visible"><i class="bi bi-currency-exchange"></i> Transparence</div>
        <h1 class="anim-fade-up delay-1">Des tarifs <span class="gradient-text">transparents</span><br>pour chaque besoin</h1>
        <p class="anim-fade-up delay-2">SaaS modulaire pour votre cabinet ou licence standalone du logiciel de comptabilité &mdash; choisissez la formule qui vous correspond.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header anim-fade-up">
            <div class="section-badge"><i class="bi bi-boxes"></i> SaaS</div>
            <h2>Abonnements mensuels <span class="gradient-text">en FCFA</span></h2>
            <p>Activez uniquement les modules dont vous avez besoin. Résiliez à tout moment.</p>
        </div>

        <div class="d-flex justify-content-center mb-5 anim-fade-up">
            <div class="toggle-wrap" id="billingToggle">
                <button class="toggle-btn active" data-period="monthly">Mensuel</button>
                <button class="toggle-btn" data-period="annual">Annuel <span class="toggle-badge">-20%</span></button>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                <div class="pricing-card">
                    <h4>Starter</h4>
                    <p class="pricing-subtitle">Pour indépendant ou micro-cabinet</p>
                    <div class="pricing-amount">
                        <span class="amount" data-monthly="9900" data-annual="95000">9 900</span>
                        <span class="period">FCFA/mois</span>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> 1 module au choix</li>
                        <li><i class="bi bi-check-circle-fill"></i> Jusqu'à 5 clients</li>
                        <li><i class="bi bi-check-circle-fill"></i> 1 utilisateur</li>
                        <li><i class="bi bi-check-circle-fill"></i> Support email</li>
                        <li><i class="bi bi-check-circle-fill"></i> 1 Go stockage</li>
                    </ul>
                    <a href="/register" class="btn btn-outline-gel w-100 justify-content-center">Essai gratuit</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                <div class="pricing-card featured">
                    <div class="pricing-badge">Populaire</div>
                    <h4>Pro</h4>
                    <p class="pricing-subtitle">Pour cabinet en pleine croissance</p>
                    <div class="pricing-amount">
                        <span class="amount" data-monthly="25000" data-annual="240000">25 000</span>
                        <span class="period">FCFA/mois</span>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> 3 modules au choix</li>
                        <li><i class="bi bi-check-circle-fill"></i> Jusqu'à 25 clients</li>
                        <li><i class="bi bi-check-circle-fill"></i> 5 utilisateurs</li>
                        <li><i class="bi bi-check-circle-fill"></i> Support prioritaire</li>
                        <li><i class="bi bi-check-circle-fill"></i> 10 Go stockage</li>
                        <li><i class="bi bi-check-circle-fill"></i> API & intégrations</li>
                    </ul>
                    <a href="/register" class="btn btn-primary-gel w-100 justify-content-center">Essai gratuit</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                <div class="pricing-card">
                    <h4>Cabinet</h4>
                    <p class="pricing-subtitle">Pour cabinet établi ou multi-pôles</p>
                    <div class="pricing-amount">
                        <span class="amount" data-monthly="49500" data-annual="475000">49 500</span>
                        <span class="period">FCFA/mois</span>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="bi bi-check-circle-fill"></i> Tous les modules</li>
                        <li><i class="bi bi-check-circle-fill"></i> Clients illimités</li>
                        <li><i class="bi bi-check-circle-fill"></i> Utilisateurs illimités</li>
                        <li><i class="bi bi-check-circle-fill"></i> Support dédié 24/7</li>
                        <li><i class="bi bi-check-circle-fill"></i> Stockage illimité</li>
                        <li><i class="bi bi-check-circle-fill"></i> API & intégrations</li>
                        <li><i class="bi bi-check-circle-fill"></i> Formation incluse</li>
                    </ul>
                    <a href="/register" class="btn btn-outline-gel w-100 justify-content-center">Essai gratuit</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header anim-fade-up">
            <div class="section-badge"><i class="bi bi-cpu"></i> Licence</div>
            <h2>Logiciel de Comptabilité <span class="gradient-text">Standalone</span></h2>
            <p>Notre logiciel de comptabilité complet conforme OHADA, en licence perpétuelle.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 anim-fade-up delay-1">
                <div class="license-card">
                    <span class="section-badge" style="margin-bottom:12px;">Individuel</span>
                    <h4>Starter</h4>
                    <div class="license-price">150 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 24px;">Licence perpétuelle — 1 poste</p>
                    <ul class="pricing-features text-start">
                        <li><i class="bi bi-check-circle-fill"></i> Plan comptable OHADA/SYSCOA</li>
                        <li><i class="bi bi-check-circle-fill"></i> Saisie des journaux</li>
                        <li><i class="bi bi-check-circle-fill"></i> Balance & Grand Livre</li>
                        <li><i class="bi bi-check-circle-fill"></i> Bilan & CRP</li>
                        <li><i class="bi bi-check-circle-fill"></i> 1 an de mises à jour</li>
                    </ul>
                    <a href="/catalogue" class="btn btn-primary-gel w-100 justify-content-center" style="margin-top:20px;"><i class="bi bi-cart3"></i> Acheter</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-2">
                <div class="license-card" style="border:2px solid var(--gel-primary);position:relative;">
                    <span class="section-badge" style="margin-bottom:12px;background:var(--gel-primary);color:white;border-color:var(--gel-primary);">Populaire</span>
                    <h4>Pro</h4>
                    <div class="license-price">350 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 24px;">Licence perpétuelle — 3 postes</p>
                    <ul class="pricing-features text-start">
                        <li><i class="bi bi-check-circle-fill"></i> Tout le plan Starter</li>
                        <li><i class="bi bi-check-circle-fill"></i> Multi-utilisateurs (3 postes)</li>
                        <li><i class="bi bi-check-circle-fill"></i> Déclarations fiscales</li>
                        <li><i class="bi bi-check-circle-fill"></i> TVA & États statistiques</li>
                        <li><i class="bi bi-check-circle-fill"></i> 2 ans de mises à jour</li>
                        <li><i class="bi bi-check-circle-fill"></i> Support prioritaire</li>
                    </ul>
                    <a href="/catalogue" class="btn btn-primary-gel w-100 justify-content-center" style="margin-top:20px;"><i class="bi bi-cart3"></i> Acheter</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 anim-fade-up delay-3">
                <div class="license-card">
                    <span class="section-badge" style="margin-bottom:12px;">Entreprise</span>
                    <h4>Cabinet</h4>
                    <div class="license-price">750 000 <span>FCFA</span></div>
                    <p style="font-size:13px;color:var(--gel-muted);margin:8px 0 24px;">Licence perpétuelle — postes illimités</p>
                    <ul class="pricing-features text-start">
                        <li><i class="bi bi-check-circle-fill"></i> Tout le plan Pro</li>
                        <li><i class="bi bi-check-circle-fill"></i> Postes illimités</li>
                        <li><i class="bi bi-check-circle-fill"></i> Modules complémentaires</li>
                        <li><i class="bi bi-check-circle-fill"></i> Formation sur site</li>
                        <li><i class="bi bi-check-circle-fill"></i> Mises à jour à vie</li>
                        <li><i class="bi bi-check-circle-fill"></i> Support dédié 24/7</li>
                    </ul>
                    <a href="/catalogue" class="btn btn-primary-gel w-100 justify-content-center" style="margin-top:20px;"><i class="bi bi-cart3"></i> Acheter</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header anim-fade-up">
            <div class="section-badge"><i class="bi bi-arrow-left-right"></i> Comparatif</div>
            <h2>SaaS <span class="gradient-text">vs</span> Licence</h2>
            <p>Lequel choisir ? Tout dépend de vos besoins.</p>
        </div>
        <div class="row justify-content-center anim-fade-up">
            <div class="col-lg-10">
                <div style="overflow-x:auto;border:1px solid rgba(0,0,0,0.04);border-radius:16px;">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th>Caractéristique</th>
                                <th style="text-align:center;">SaaS Mensuel</th>
                                <th style="text-align:center;">Licence Standalone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Paiement</td><td style="text-align:center;">Abonnement mensuel/annuel</td><td style="text-align:center;">Paiement unique</td></tr>
                            <tr><td>Modules disponibles</td><td style="text-align:center;">Tous (CRM, GED, Compta, ERP, RH...)</td><td style="text-align:center;">Comptabilité uniquement</td></tr>
                            <tr><td>Hébergement</td><td style="text-align:center;"><i class="bi bi-check-lg"></i> Cloud (nous)</td><td style="text-align:center;">Sur votre poste</td></tr>
                            <tr><td>Mises à jour</td><td style="text-align:center;">Automatiques</td><td style="text-align:center;">Manuelles (incluse 1-2 ans)</td></tr>
                            <tr><td>Support</td><td style="text-align:center;">Inclus</td><td style="text-align:center;">Option payant après garantie</td></tr>
                            <tr><td>Idéal pour</td><td style="text-align:center;">Cabinets en croissance, multi-utilisateurs</td><td style="text-align:center;">Indépendants, coût maîtrisé</td></tr>
                            <tr><td>Moyens de paiement</td><td style="text-align:center;">Mobile Money, Carte, Virement</td><td style="text-align:center;">Mobile Money, Virement</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container anim-fade-up">
        <h2>Prêt à passer à l'action ?</h2>
        <p>Essayez gratuitement pendant 14 jours. Sans engagement.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="/register" class="btn-cta-white"><i class="bi bi-rocket-takeoff"></i> Essai gratuit</a>
            <a href="/contact" class="btn-cta-outline"><i class="bi bi-whatsapp"></i> Nous contacter</a>
        </div>
    </div>
</section>

@include('partials.footer')
@include('partials.ai-chat-floating')

    <script>
        // Animation au défilement
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('anim-visible');
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.anim-fade-up:not(.anim-visible)').forEach(el => observer.observe(el));

        // Billing toggle
        const toggleBtns = document.querySelectorAll('.toggle-btn');
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                toggleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const period = btn.dataset.period;
                document.querySelectorAll('[data-monthly]').forEach(el => {
                    const val = parseInt(el.dataset[period]);
                    if (period === 'annual') {
                        el.textContent = Math.round(val / 12).toLocaleString('fr-FR');
                    } else {
                        el.textContent = val.toLocaleString('fr-FR');
                    }
                });
            });
        });
    </script>
</body>
</html>
