<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    @include('partials.styles')
    <style>
        .gel-faq-header {
            margin-top: 80px; /* nav height */
            background: linear-gradient(135deg, #0A1628 0%, #1E293B 25%, #0F172A 50%, #1E293B 75%, #0A1628 100%);
            background-size: 300% 300%;
            animation: gelGradientMove 12s ease infinite;
            padding: 70px 0 50px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        @keyframes gelGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

        .gel-accordion-item {
            border: none;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 10px;
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }

        .gel-accordion-item:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }

        .accordion-button {
            font-family: var(--font-heading);
            font-weight: 700;
            color: var(--gel-text);
            background-color: white !important;
            padding: 20px 25px;
            box-shadow: none !important;
            font-size: 15px;
        }

        .accordion-button:not(.collapsed) {
            color: var(--gel-primary);
            background-color: #f8fafc !important;
        }

        .accordion-button::after {
            filter: grayscale(1);
        }

        .accordion-button:not(.collapsed)::after {
            filter: invert(34%) sepia(87%) saturate(1915%) hue-rotate(185deg) brightness(97%) contrast(101%);
        }

        .accordion-body {
            color: var(--gel-muted);
            line-height: 1.7;
            padding: 0 25px 25px 25px;
            background-color: #f8fafc;
            font-size: 13px;
        }

        .gel-faq-cat-title {
            font-family: var(--font-heading);
            color: var(--gel-primary);
            font-weight: 800;
            font-size: 18px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(2, 132, 199, 0.1);
            display: inline-block;
        }

        .gel-faq-cat-title i {
            color: var(--gel-accent);
            margin-right: 10px;
        }
    </style>
</head>
<body>

    @include('partials.navbar')

    <!-- En-tête -->
    <header class="gel-faq-header">
        <div class="container">
            <h1 class="fw-bold mb-3 anim-fade-up" style="font-size: clamp(1.8rem, 3vw, 2.4rem); font-family: var(--font-heading);">Questions Fréquentes (FAQ)</h1>
            <p class="lead mb-4 anim-fade-up delay-1" style="opacity: 0.9;">Trouvez rapidement des réponses concernant nos modules, la sécurité et la conformité au Bénin.</p>
        </div>
    </header>

    <!-- Contenu Principal -->
    <section class="py-5" style="background-color: #f8fafc;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">

                    <!-- Catégorie 1 -->
                    <div class="mb-5 anim-fade-up">
                        <h2 class="gel-faq-cat-title"><i class="bi-box"></i> Services, Modules & Conformité OHADA</h2>
                        <div class="accordion" id="faqServices">
                            
                            <div class="accordion-item gel-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseS1">
                                        La plateforme est-elle certifiée pour la facturation e-MECeF au Bénin ?
                                    </button>
                                </h2>
                                <div id="collapseS1" class="accordion-collapse collapse show" data-bs-parent="#faqServices">
                                    <div class="accordion-body">
                                        Oui, GEL Cabinet est totalement interfacé avec l'API e-MECeF de la DGI du Bénin. Vos factures normalisées sont transmises en temps réel avec QR Code et NIM, sans aucune saisie double.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item gel-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseS2">
                                        Les états financiers respectent-ils le format SYSCOHADA révisé ?
                                    </button>
                                </h2>
                                <div id="collapseS2" class="accordion-collapse collapse" data-bs-parent="#faqServices">
                                    <div class="accordion-body">
                                        Absolument. Nos modules de Comptabilité et d'ERP génèrent automatiquement les liasses fiscales et états financiers (bilan, compte de résultat, TAFIRE) selon le format obligatoire SYSCOHADA.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item gel-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseS3">
                                        Peut-on souscrire uniquement au module Tontine / Microfinance ?
                                    </button>
                                </h2>
                                <div id="collapseS3" class="accordion-collapse collapse" data-bs-parent="#faqServices">
                                    <div class="accordion-body">
                                        Oui ! Notre architecture modulaire vous permet de souscrire au module Tontine de façon indépendante, tout en l'intégrant si vous le souhaitez avec le Mobile Money (MTN/Moov) pour les paiements.
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <!-- Catégorie 2 -->
                    <div class="mb-5 anim-fade-up">
                        <h2 class="gel-faq-cat-title"><i class="bi-shield-check"></i> Sécurité & Données</h2>
                        <div class="accordion" id="faqSecurity">
                            
                            <div class="accordion-item gel-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSec1">
                                        Comment sont sécurisées nos données comptables ?
                                    </button>
                                </h2>
                                <div id="collapseSec1" class="accordion-collapse collapse" data-bs-parent="#faqSecurity">
                                    <div class="accordion-body">
                                        La sécurité est notre priorité. L'accès est protégé par authentification à double facteur (2FA). Nous disposons d'un système de permissions granulaires et d'un audit log complet des actions de chaque utilisateur.
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.anim-fade-up').forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
