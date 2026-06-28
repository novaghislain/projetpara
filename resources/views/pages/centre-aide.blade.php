<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centre d'aide | GEL Cabinet</title>
    @include("partials.styles")

    <style>
        :root {
            --gel-primary: #f97316;
            --gel-primary-light: rgba(249, 115, 22, 0.1);
            --gel-dark: #0f172a;
            --gel-muted: #64748b;
            --gel-bg: #f8fafc;
            --font-heading: 'Outfit', system-ui, sans-serif;
            --font-body: 'Inter', system-ui, sans-serif;
            --nav-height: 80px;
        }

        body {
            font-family: var(--font-body);
            color: #334155;
            background: var(--gel-bg);
            line-height: 1.6;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-heading);
            color: var(--gel-dark);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .gel-section {
            padding: 80px 0;
        }

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

        @keyframes gelGradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .gel-page-header::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            animation: gelFloatA 8s ease-in-out infinite;
        }

        .gel-page-header::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: gelFloatB 10s ease-in-out infinite;
        }

        @keyframes gelFloatA {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(-30px, 20px) scale(1.05);
            }

            66% {
                transform: translate(20px, -10px) scale(0.95);
            }
        }

        @keyframes gelFloatB {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -20px) scale(1.08);
            }

            66% {
                transform: translate(-20px, 10px) scale(0.92);
            }
        }

        .gel-page-header h1 {
            font-family: var(--font-heading);
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 900;
            color: #fff;
            letter-spacing: -1px;
            position: relative;
            z-index: 1;
        }

        .gel-page-header p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
            max-width: 620px;
            margin-top: 12px;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        .gel-section-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: var(--gel-primary-light);
            color: var(--gel-primary);
            font-size: 14px;
            font-weight: 600;
            border-radius: 100px;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .gel-section-title {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        /* Contact Cards */
        .gel-contact-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.3s;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            height: 100%;
        }

        .gel-contact-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(249, 115, 22, 0.08);
            border-color: rgba(249, 115, 22, 0.15);
        }

        .gel-contact-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: var(--gel-primary-light);
            color: var(--gel-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .gel-contact-info h4 {
            font-size: 1.25rem;
            margin-bottom: 8px;
        }

        .gel-contact-info p {
            color: var(--gel-muted);
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .gel-contact-info a {
            color: var(--gel-primary);
            font-weight: 600;
            text-decoration: none;
            font-size: 1.1rem;
        }

        /* Form */
        .gel-form-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 14px 20px;
            border-color: #e2e8f0;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--gel-primary);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--gel-dark);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .gel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 32px;
            background: var(--gel-primary);
            color: white;
            font-weight: 600;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            width: 100%;
        }

        .gel-btn:hover {
            background: var(--gel-primary-hover);
            color: white;
        }

        /* Animations */
        .anim-fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .anim-fade-up.anim-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .delay-1 {
            transition-delay: 0.1s;
        }

        @media (max-width: 991px) {
            .gel-section {
                padding: 60px 0;
            }

            .gel-page-header {
                padding: 60px 0 40px;
            }
        }
    </style>
</head>

<body>

    @include('partials.navbar')

    <!-- Page Header -->
    <header class="gel-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="anim-fade-up">Centre d'aide</h1>
                    <p class="anim-fade-up delay-1">Notre équipe est à votre disposition pour répondre à vos questions
                        et résoudre vos problèmes le plus rapidement possible.</p>
                </div>
            </div>
        </div>
    </header>

    <section class="gel-section">
        <div class="container">

            <div class="row g-4 mb-5">
                <div class="col-md-6 anim-fade-up">
                    <div class="gel-contact-card">
                        <div class="gel-contact-icon"><i class="bi-telephone-fill"></i></div>
                        <div class="gel-contact-info">
                            <h4>Par téléphone</h4>
                            <p>Du lundi au vendredi, de 9h00 à 18h00.</p>
                            <a href="tel:+33123456789">+229 XXXXXXXXXX</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 anim-fade-up delay-1">
                    <div class="gel-contact-card">
                        <div class="gel-contact-icon"><i class="bi-envelope-fill"></i></div>
                        <div class="gel-contact-info">
                            <h4>Par e-mail</h4>
                            <p>Nous nous engageons à répondre sous 24h ouvrées.</p>
                            <a href="mailto:contact@gelcabinet.com">contact@gelcabinet.com</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                    <div class="gel-form-card anim-fade-up">
                        <div class="text-center mb-5">
                            <h2 class="gel-section-title" style="font-size: 2rem;">Ouvrir un ticket d'assistance</h2>
                            <p class="text-muted">Remplissez le formulaire ci-dessous pour signaler un problème
                                technique ou poser une question sur l'utilisation d'un module.</p>
                        </div>

                        <form action="#" method="POST" @submit.prevent>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nom du cabinet</label>
                                    <input type="text" class="form-control" placeholder="Cabinet Dupont & Associés"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Votre nom complet</label>
                                    <input type="text" class="form-control" placeholder="Jean Dupont" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email de contact</label>
                                    <input type="email" class="form-control" placeholder="jean.dupont@cabinet.fr"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Numéro client (Optionnel)</label>
                                    <input type="text" class="form-control" placeholder="GEL-12345">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Module concerné</label>
                                    <select class="form-select" required>
                                        <option value="" selected disabled>Sélectionnez un module...</option>
                                        <option value="general">Général / Mon Compte</option>
                                        <option value="ged">GED (Documents)</option>
                                        <option value="compta">Comptabilité</option>
                                        <option value="crm">CRM & Clients</option>
                                        <option value="facturation">Facturation</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Sujet de votre demande</label>
                                    <input type="text" class="form-control" placeholder="Résumé court de votre problème"
                                        required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description détaillée</label>
                                    <textarea class="form-control" rows="6"
                                        placeholder="Décrivez votre problème le plus précisément possible (messages d'erreur, actions effectuées...)"
                                        required></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Pièce jointe (Capture d'écran, etc.)</label>
                                    <input type="file" class="form-control" accept="image/*,.pdf">
                                </div>
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="gel-btn">
                                        <i class="bi-ticket-detailed"></i> Créer le ticket
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="text-center mt-4 anim-fade-up">
                        <p class="text-muted mb-2">Vous avez déjà ouvert un ticket ?</p>
                        <a href="#" class="text-decoration-none" style="color:var(--gel-primary); font-weight:600;"><i
                                class="bi-box-arrow-in-right"></i> Suivre mes demandes dans l'espace client</a>
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