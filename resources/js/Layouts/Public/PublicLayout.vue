<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, default: 'GEL Cabinet' },
    transparentNavbar: { type: Boolean, default: false }
});

const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
    <div class="pub-layout">
        <!-- Navigation -->
        <nav :class="['pub-navbar', { 'is-transparent': transparentNavbar }]">
            <div class="pub-navbar__container">
                <div class="pub-navbar__brand">
                    <a href="/" class="pub-logo">
                        <span class="pub-logo__icon">G</span>
                        <span class="pub-logo__text">GEL Cabinet</span>
                    </a>
                </div>
                
                <div class="pub-navbar__links">
                    <a href="/catalogue" class="pub-nav-link">Nos Services</a>
                    <a href="/cabinet" class="pub-nav-link">Le Cabinet</a>
                    <a href="/contact" class="pub-nav-link">Contact</a>
                </div>

                <div class="pub-navbar__actions">
                    <a href="/login" class="pub-btn pub-btn--outline">Espace Cabinet</a>
                    <a href="/saas/login" class="pub-btn pub-btn--primary">Espace Client</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pub-main">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="pub-footer">
            <div class="pub-footer__container">
                <div class="pub-footer__grid">
                    <div class="pub-footer__col">
                        <h3 class="pub-footer__title">GEL Cabinet</h3>
                        <p class="pub-footer__text">
                            Votre partenaire d'expertise comptable, juridique et fiscale au Bénin.
                        </p>
                    </div>
                    <div class="pub-footer__col">
                        <h4 class="pub-footer__subtitle">Liens Rapides</h4>
                        <ul class="pub-footer__list">
                            <li><a href="/catalogue">Nos Services</a></li>
                            <li><a href="/cabinet">L'Équipe</a></li>
                            <li><a href="/saas/register">Ouvrir un compte</a></li>
                        </ul>
                    </div>
                    <div class="pub-footer__col">
                        <h4 class="pub-footer__subtitle">Légal</h4>
                        <ul class="pub-footer__list">
                            <li><a href="/legal/terms">Conditions d'utilisation</a></li>
                            <li><a href="/legal/privacy">Confidentialité</a></li>
                        </ul>
                    </div>
                </div>
                <div class="pub-footer__bottom">
                    &copy; {{ currentYear }} GEL Cabinet. Tous droits réservés.
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Design System Public Layout */
.pub-layout {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: #f8fafc;
    color: #0f172a;
}

/* Navbar */
.pub-navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.pub-navbar.is-transparent {
    background: transparent;
    border-bottom-color: transparent;
}

.pub-navbar__container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Logo */
.pub-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: inherit;
}

.pub-logo__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: white;
    font-weight: 800;
    font-size: 1.25rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
}

.pub-logo__text {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.025em;
    background: linear-gradient(to right, #0f172a, #334155);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Links */
.pub-navbar__links {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.pub-nav-link {
    text-decoration: none;
    color: #475569;
    font-weight: 500;
    font-size: 0.95rem;
    transition: color 0.2s ease;
    position: relative;
}

.pub-nav-link:hover {
    color: #2563eb;
}

.pub-nav-link::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #2563eb;
    transition: width 0.3s ease;
    border-radius: 2px;
}

.pub-nav-link:hover::after {
    width: 100%;
}

/* Actions */
.pub-navbar__actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* Buttons */
.pub-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.pub-btn--outline {
    background: transparent;
    color: #0f172a;
    border: 1px solid #cbd5e1;
}

.pub-btn--outline:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.pub-btn--primary {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
}

.pub-btn--primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px -2px rgba(37, 99, 235, 0.3);
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
}

/* Main */
.pub-main {
    flex: 1;
    padding-top: 5rem; /* Navbar height compensation */
}

/* Footer */
.pub-footer {
    background: #0f172a;
    color: #f8fafc;
    padding: 4rem 2rem 2rem;
    margin-top: auto;
}

.pub-footer__container {
    max-width: 1280px;
    margin: 0 auto;
}

.pub-footer__grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 4rem;
    margin-bottom: 3rem;
}

.pub-footer__title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    background: linear-gradient(to right, #fff, #cbd5e1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.pub-footer__text {
    color: #94a3b8;
    line-height: 1.6;
    max-width: 300px;
}

.pub-footer__subtitle {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
    color: #e2e8f0;
}

.pub-footer__list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.pub-footer__list a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.2s;
}

.pub-footer__list a:hover {
    color: #3b82f6;
}

.pub-footer__bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 2rem;
    text-align: center;
    color: #64748b;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .pub-navbar__links {
        display: none;
    }
    .pub-footer__grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>
