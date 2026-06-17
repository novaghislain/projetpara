<script setup>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    categories: { type: Array, required: true }
});

const searchQuery = ref('');
const mobileOpen = ref(false);
const showAuthModal = ref(false);
const animated = ref(false);
const counters = reactive({ services: 0, categories: 0, started: false });
let observer = null;

const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) return props.categories;
    const q = searchQuery.value.toLowerCase();
    return props.categories.map(cat => ({
        ...cat,
        services: cat.services.filter(s =>
            s.nom?.toLowerCase().includes(q) ||
            s.description?.toLowerCase().includes(q)
        )
    })).filter(cat => cat.services.length > 0);
});

const totalServices = computed(() =>
    props.categories.reduce((sum, cat) => sum + cat.services.length, 0)
);

const hasNoResults = computed(() =>
    searchQuery.value.trim() && filteredCategories.value.length === 0
);

const initObserver = () => {
    observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('sv-visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.sv-anim').forEach(el => observer.observe(el));
};

const animateCounters = () => {
    if (counters.started) return;
    counters.started = true;
    const targetS = totalServices.value;
    const targetC = props.categories.length;
    const duration = 1400;
    const start = performance.now();
    const step = (now) => {
        const p = Math.min((now - start) / duration, 1);
        const e = 1 - Math.pow(1 - p, 3);
        counters.services = Math.floor(e * targetS);
        counters.categories = Math.floor(e * targetC);
        if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
};

let counterObs = null;
const initCounterObserver = () => {
    const el = document.getElementById('sv-stats');
    if (!el) return;
    counterObs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) { animateCounters(); counterObs.unobserve(e.target); }
        });
    }, { threshold: 0.3 });
    counterObs.observe(el);
};

onMounted(() => {
    setTimeout(() => { animated.value = true; }, 80);
    requestAnimationFrame(() => {
        initObserver();
        initCounterObserver();
    });
});

onUnmounted(() => {
    if (observer) observer.disconnect();
    if (counterObs) counterObs.disconnect();
});

const handleScroll = () => {
    const nav = document.getElementById('svNavbar');
    if (nav) nav.classList.toggle('sv-scrolled', window.scrollY > 20);
};
onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

const categoryColor = (name) => {
    const n = (name || '').toLowerCase();
    if (n.includes('comptab') || n.includes('fiscal')) return '#FF7900';
    if (n.includes('jurid')) return '#3B82F6';
    if (n.includes('social') || n.includes('paie')) return '#8B5CF6';
    if (n.includes('commerc') || n.includes('crm')) return '#06B6D4';
    if (n.includes('erp')) return '#10B981';
    if (n.includes('créat')) return '#F59E0B';
    if (n.includes('ged')) return '#EC4899';
    if (n.includes('mission')) return '#6366F1';
    return '#FF7900';
};
const categoryIcon = (name) => {
    const n = (name || '').toLowerCase();
    if (n.includes('comptab') || n.includes('fiscal')) return 'bi-calculator-fill';
    if (n.includes('jurid')) return 'bi-bank2';
    if (n.includes('social') || n.includes('paie')) return 'bi-people-fill';
    if (n.includes('commerc')) return 'bi-cart3';
    if (n.includes('erp')) return 'bi-box-seam-fill';
    if (n.includes('créat')) return 'bi-rocket-takeoff';
    if (n.includes('admin')) return 'bi-gear-wide-connected';
    if (n.includes('crm')) return 'bi-person-badge';
    if (n.includes('ged')) return 'bi-folder2-open';
    if (n.includes('mission')) return 'bi-diagram-3';
    return 'bi-grid-3x3-gap';
};
</script>

<template>
    <div class="page-services">

        <!-- ════════════════════════════════════════
             NAVBAR
        ════════════════════════════════════════ -->
        <nav class="gel-navbar" id="svNavbar">
            <div class="container-fluid">
                <a href="/" class="gel-brand">
                    <div class="gel-brand-logo">GEL</div>
                    <div class="gel-brand-text">
                        <span class="gel-brand-name">GEL Cabinet</span>
                        <span class="gel-brand-sub">Gestion Multi-Pôles</span>
                    </div>
                </a>
                <ul class="gel-nav-center" id="gelNavCenter">
                    <li class="gel-nav-item">
                        <a href="#" class="gel-nav-link">Nos Modules <i class="bi-chevron-down chevron"></i></a>
                        <ul class="gel-dropdown">
                            <li><a href="/login"><span class="drop-icon"><i class="bi-people"></i></span> CRM Clients</a></li>
                            <li><a href="/login"><span class="drop-icon"><i class="bi-folder2-open"></i></span> GED — Documents</a></li>
                            <li><a href="/login"><span class="drop-icon"><i class="bi-diagram-3"></i></span> Pôles & Missions</a></li>
                            <li><hr class="gel-dropdown-divider"></li>
                            <li><a href="/login"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                            <li><a href="/login"><span class="drop-icon"><i class="bi-box-seam"></i></span> ERP Intégré</a></li>
                        </ul>
                    </li>
                    <li class="gel-nav-item">
                        <a href="/services" class="gel-nav-link active">Services <i class="bi-chevron-down chevron"></i></a>
                        <ul class="gel-dropdown">
                            <li><a href="/services/comptabilite"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                            <li><a href="/services/juridique"><span class="drop-icon"><i class="bi-bank2"></i></span> Juridique</a></li>
                            <li><a href="/services/fiscal"><span class="drop-icon"><i class="bi-receipt"></i></span> Fiscal</a></li>
                            <li><a href="/services/social-paie"><span class="drop-icon"><i class="bi-people"></i></span> Social & Paie</a></li>
                            <li><hr class="gel-dropdown-divider"></li>
                            <li><a href="/nos-services"><span class="drop-icon"><i class="bi-grid-3x3-gap"></i></span> Tous les services</a></li>
                        </ul>
                    </li>
                    <li class="gel-nav-item">
                        <a href="#" class="gel-nav-link">À propos <i class="bi-chevron-down chevron"></i></a>
                        <ul class="gel-dropdown">
                            <li><a href="/notre-cabinet"><span class="drop-icon"><i class="bi-building"></i></span> Notre Cabinet</a></li>
                            <li><a href="/notre-equipe"><span class="drop-icon"><i class="bi-people-fill"></i></span> Notre Équipe</a></li>
                            <li><a href="/carrieres"><span class="drop-icon"><i class="bi-briefcase-fill"></i></span> Carrières</a></li>
                        </ul>
                    </li>
                    <li class="gel-nav-item"><a href="/tarifs" class="gel-nav-link">Tarifs</a></li>
                    <li class="gel-nav-item"><a href="/contact" class="gel-nav-link">Contact</a></li>
                </ul>
                <div class="gel-nav-right">
                    <a v-if="authStore.isAuthenticated" href="/dashboard" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-speedometer2"></i> Mon Espace</a>
                    <template v-else>
                        <a href="/register" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-person-plus"></i> S'inscrire</a>
                        <a href="/login" class="gel-btn-nav gel-btn-nav-primary" id="nav-login-btn"><i class="bi-box-arrow-in-right"></i> Connexion</a>
                    </template>
                    <button class="gel-toggler" @click="mobileOpen = !mobileOpen" aria-label="Menu"><i :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i></button>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div :class="['gel-mobile-menu', { open: mobileOpen }]" id="gelMobileMenu">
            <a href="/" class="gel-mobile-link"><i class="bi-house text-orange me-2"></i>Accueil</a>
            <a href="/nos-modules" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Nos Modules</a>
            <a href="/services/comptabilite" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Services</a>
            <a href="/notre-cabinet" class="gel-mobile-link"><i class="bi-building text-orange me-2"></i>Notre Cabinet</a>
            <a href="/notre-equipe" class="gel-mobile-link"><i class="bi-people-fill text-orange me-2"></i>Notre Équipe</a>
            <a href="/carrieres" class="gel-mobile-link"><i class="bi-briefcase-fill text-orange me-2"></i>Carrières</a>
            <a href="/tarifs" class="gel-mobile-link"><i class="bi-currency-dollar text-orange me-2"></i>Tarifs</a>
            <a href="/contact" class="gel-mobile-link"><i class="bi-envelope text-orange me-2"></i>Contact</a>
            <a v-if="!authStore.isAuthenticated" href="/login" class="gel-mobile-link"><i class="bi-box-arrow-in-right text-orange me-2"></i>Connexion</a>
            <a v-else href="/dashboard" class="gel-mobile-link"><i class="bi-speedometer2 text-orange me-2"></i>Mon Espace</a>
        </div>
        <div v-if="mobileOpen" class="sv-overlay" @click="mobileOpen = false"></div>

        <!-- ════════════════════════════════════════
             HERO
        ════════════════════════════════════════ -->
        <section class="sv-hero" :class="{ 'sv-hero-ready': animated }">
            <div class="sv-hero-bg">
                <div class="sv-hero-orbe sv-hero-orbe-1"></div>
                <div class="sv-hero-orbe sv-hero-orbe-2"></div>
                <div class="sv-hero-orbe sv-hero-orbe-3"></div>
                <div class="sv-hero-particule" style="top:15%;left:10%;width:6px;height:6px;animation-delay:0s;"></div>
                <div class="sv-hero-particule" style="top:25%;left:70%;width:4px;height:4px;animation-delay:1s;"></div>
                <div class="sv-hero-particule" style="top:60%;left:15%;width:5px;height:5px;animation-delay:2s;"></div>
                <div class="sv-hero-particule" style="top:45%;left:85%;width:3px;height:3px;animation-delay:0.5s;"></div>
                <div class="sv-hero-particule" style="top:75%;left:50%;width:7px;height:7px;animation-delay:1.5s;"></div>
                <div class="sv-hero-particule" style="top:10%;left:45%;width:4px;height:4px;animation-delay:3s;"></div>
                <div class="sv-hero-particule" style="top:80%;left:80%;width:5px;height:5px;animation-delay:2.5s;"></div>
                <div class="sv-hero-particule" style="top:35%;left:30%;width:3px;height:3px;animation-delay:0.8s;"></div>
            </div>
            <div class="container position-relative" style="z-index:2;">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="sv-hero-title" style="display:none;">
                            L'excellence<br>
                            <span class="sv-hero-accent">à chaque service</span>
                        </h1>
                        <p class="sv-hero-text" style="display:none;">
                            Comptabilité, juridique, fiscal, social — une gamme complète de
                            services pensés pour accompagner votre cabinet dans toutes ses missions.
                        </p>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="sv-search">
                            <i class="bi-search sv-search-icon"></i>
                            <input v-model="searchQuery" type="text" class="sv-search-input" placeholder="Rechercher un service…" />
                            <button v-if="searchQuery" class="sv-search-clear" @click="searchQuery = ''"><i class="bi-x-lg"></i></button>
                        </div>
                    </div>
                </div>
                <div class="sv-stats-row" style="display:none;">
                    <div class="sv-stat">
                        <span class="sv-stat-value">{{ totalServices }}</span>
                        <span class="sv-stat-label">Services</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-value">{{ props.categories.length }}</span>
                        <span class="sv-stat-label">Pôles</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-value">24/7</span>
                        <span class="sv-stat-label">Assistance</span>
                    </div>
                </div>
            </div>
            <div class="sv-hero-wave">
                <svg viewBox="0 0 1440 60" preserveAspectRatio="none">
                    <path d="M0,30 C240,60 480,0 720,30 C960,60 1200,0 1440,30 L1440,60 L0,60 Z" fill="#F8FAFC" />
                </svg>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             SERVICES
        ════════════════════════════════════════ -->
        <section class="sv-body">
            <div class="container">

                <!-- Search info -->
                <div v-if="searchQuery.trim() && !hasNoResults" class="sv-search-info sv-anim">
                    <i class="bi-info-circle"></i>
                    <span><strong>{{ filteredCategories.reduce((s, c) => s + c.services.length, 0) }}</strong> résultat(s)</span>
                    <button class="sv-search-info-clear" @click="searchQuery = ''">Effacer</button>
                </div>

                <!-- Empty state -->
                <div v-if="hasNoResults" class="sv-empty sv-anim">
                    <div class="sv-empty-icon"><i class="bi-search"></i></div>
                    <h3>Aucun service trouvé</h3>
                    <p>Essayez un autre terme de recherche.</p>
                    <button class="sv-empty-btn" @click="searchQuery = ''"><i class="bi-x-lg me-2"></i>Voir tous</button>
                </div>

                <!-- Categories -->
                <div v-for="(cat, ci) in filteredCategories" :key="cat.id" class="sv-cat">
                    <div class="sv-cat-anchor" :id="'cat-' + cat.id"></div>
                    <div class="sv-cat-head sv-anim" :style="{ transitionDelay: ci * 0.08 + 's' }">
                        <h2 class="sv-cat-title">{{ cat.nom }}</h2>
                        <p v-if="cat.description" class="sv-cat-desc">{{ cat.description }}</p>
                        <span class="sv-cat-pill" :style="{ background: categoryColor(cat.nom) }">{{ cat.services.length }}</span>
                    </div>
                    <div class="sv-cat-grid">
                        <div v-for="(svc, si) in cat.services" :key="svc.id" class="sv-card-wrap">
                            <div class="sv-card sv-anim" :style="{ transitionDelay: (ci * 0.08 + si * 0.05) + 's' }">
                                <div class="sv-card-top">
                                    <div class="sv-card-icon" :style="{
                                        background: categoryColor(cat.nom) + '12',
                                        color: categoryColor(cat.nom),
                                        borderColor: categoryColor(cat.nom) + '25'
                                    }">
                                        <i :class="categoryIcon(cat.nom)"></i>
                                    </div>
                                    <span class="sv-card-badge" v-if="svc.tarif_type === 'fixe'">{{ svc.tarif_fcfa?.toLocaleString('fr-FR') }} FCFA</span>
                                    <span class="sv-card-badge sv-card-badge--ghost" v-else>Sur devis</span>
                                </div>
                                <h3 class="sv-card-title">{{ svc.nom }}</h3>
                                <p class="sv-card-text">{{ svc.description }}</p>
                                <div class="sv-card-meta">
                                    <span><i class="bi-clock"></i> {{ svc.delai_jours || 'Sur mesure' }}</span>
                                    <span v-if="svc.inclus_json?.length"><i class="bi-check-circle"></i> {{ svc.inclus_json.length }} inclus</span>
                                </div>
                                <a href="javascript:void(0)" @click.prevent="showAuthModal = true" class="sv-card-btn" :style="{ '--btn-bg': categoryColor(cat.nom) }">
                                    Voir la fiche <i class="bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div v-if="!cat.services.length" class="sv-cat-empty sv-anim">
                        <i class="bi-inbox me-2"></i>Aucun service disponible dans cette catégorie.
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             CTA
        ════════════════════════════════════════ -->
        <section class="sv-cta">
            <div class="container text-center">
                <h2 class="sv-cta-title">Prêt à démarrer ?</h2>
                <p class="sv-cta-text">Créez votre espace client et soumettez votre demande en quelques clics.</p>
                <div class="sv-cta-actions">
                    <a href="/register" class="sv-cta-btn"><i class="bi-person-plus-fill me-2"></i> Créer un compte</a>
                    <a href="/login" class="sv-cta-link">Déjà inscrit ? Se connecter <i class="bi-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             MODAL INSCRIPTION
        ════════════════════════════════════════ -->
        <div v-if="showAuthModal" class="gel-modal-overlay" @click.self="showAuthModal = false">
            <div class="gel-modal-box">
                <button class="gel-modal-close" @click="showAuthModal = false">&times;</button>
                <div class="gel-modal-icon"><i class="bi-person-plus-fill"></i></div>
                <h3>Accédez à tous les services</h3>
                <p>Créez votre compte gratuitement pour découvrir l'ensemble de nos services et soumettre votre demande.</p>
                <a href="/register" class="gel-modal-btn gel-modal-btn-primary">
                    <i class="bi-person-plus"></i> Créer un compte
                </a>
                <div class="gel-modal-divider">ou</div>
                <a href="/login" class="gel-modal-btn gel-modal-btn-outline">
                    <i class="bi-box-arrow-in-right"></i> Se connecter
                </a>
                <p style="font-size:11px; color:var(--sv-gray); margin-top:14px;">Gratuit — Sans engagement — 1 clic</p>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             FOOTER
        ════════════════════════════════════════ -->
        <footer class="sv-footer">
            <div class="container">
                <div class="sv-footer-grid">
                    <div class="sv-footer-col">
                        <div class="sv-footer-brand">
                            <div class="sv-footer-logo">GEL</div>
                            <div>
                                <div class="sv-footer-name">GEL Cabinet</div>
                                <div class="sv-footer-sub">Gestion Multi-Pôles</div>
                            </div>
                        </div>
                        <p class="sv-footer-desc">Plateforme intégrée de gestion de cabinet pluridisciplinaire.</p>
                    </div>
                    <div class="sv-footer-col">
                        <h4 class="sv-footer-h">Modules</h4>
                        <ul><li><a href="/login">CRM Clients</a></li><li><a href="/login">GED</a></li><li><a href="/login">Comptabilité</a></li><li><a href="/login">ERP Intégré</a></li></ul>
                    </div>
                    <div class="sv-footer-col">
                        <h4 class="sv-footer-h">Services</h4>
                        <ul><li><a href="/services/comptabilite">Comptabilité</a></li><li><a href="/services/juridique">Juridique</a></li><li><a href="/services/fiscal">Fiscal</a></li><li><a href="/services/social-paie">Social & Paie</a></li></ul>
                    </div>
                    <div class="sv-footer-col">
                        <h4 class="sv-footer-h">Contact</h4>
                        <ul><li><i class="bi-envelope"></i> contact@gel-cabinet.com</li><li><i class="bi-telephone"></i> +229 XX XX XX XX</li><li><i class="bi-geo-alt"></i> Cotonou, Bénin</li></ul>
                    </div>
                </div>
                <div class="sv-footer-bar">
                    <span>&copy; {{ new Date().getFullYear() }} GEL Cabinet</span>
                    <span class="sv-footer-bar-links"><a href="/login">Mentions légales</a><a href="/login">Confidentialité</a></span>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
:root {
    --sv-orange: #FF7900;
    --sv-orange-hov: #e06700;
    --sv-dark: #0F172A;
    --sv-gray: #64748B;
    --sv-light: #F8FAFC;
    --sv-border: #E2E8F0;
    --sv-text: #1E293B;
}
</style>

<style scoped>
/* ── Base ── */
.page-services {
    background: #fff;
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
    color: var(--sv-text);
}
.sv-anim {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s cubic-bezier(0.16, 0.6, 0.2, 1),
                transform 0.5s cubic-bezier(0.16, 0.6, 0.2, 1);
}
.sv-visible { opacity: 1 !important; transform: none !important; }

/* ── Mobile overlay ── */
.sv-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.2); z-index: 1039; }

/* ── Import navbar styles from landing (reuse classes) ── */
.gel-navbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1050;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid transparent;
    height: 72px;
    display: flex; align-items: center;
    transition: border-color 0.25s, box-shadow 0.25s;
}
.gel-navbar.sv-scrolled {
    background: rgba(255,255,255,0.98);
    border-bottom-color: var(--sv-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.gel-navbar .container-fluid {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 32px; max-width: 1320px; margin: 0 auto; width: 100%;
}
.gel-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; flex-shrink: 0; }
.gel-brand-logo {
    width: 38px; height: 38px; background: var(--sv-orange); border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 900; color: #fff;
    letter-spacing: -0.5px;
}
.gel-brand-text { display: flex; flex-direction: column; line-height: 1.1; }
.gel-brand-name { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 16px; color: #111827; letter-spacing: -0.3px; }
.gel-brand-sub { font-size: 8.5px; font-weight: 600; color: var(--sv-gray); text-transform: uppercase; letter-spacing: 0.1em; }
.gel-nav-center { display: flex; align-items: center; gap: 0; list-style: none; margin: 0; padding: 0; }
.gel-nav-item { position: relative; }
.gel-nav-link {
    display: flex; align-items: center; gap: 3px;
    padding: 7px 13px; font-size: 13px; font-weight: 500;
    color: #1E293B; text-decoration: none;
    border-radius: 4px;
    transition: color 0.25s, background 0.25s;
    white-space: nowrap;
}
.gel-nav-link:hover, .gel-nav-link.active { color: var(--sv-orange); background: rgba(255,121,0,0.06); }
.gel-nav-link .chevron { font-size: 10px; transition: transform 0.25s; }
.gel-nav-item:hover > a .chevron { transform: rotate(180deg); }
.gel-dropdown {
    position: absolute; top: calc(100% + 8px); left: 50%;
    transform: translateX(-50%);
    background: #fff; border: 1px solid var(--sv-border);
    border-radius: 10px; box-shadow: 0 12px 40px rgba(0,0,0,0.08);
    padding: 6px; min-width: 220px;
    opacity: 0; visibility: hidden;
    transform: translateX(-50%) translateY(-8px);
    transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
    list-style: none;
}
.gel-nav-item:hover .gel-dropdown { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
.gel-dropdown li a {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px; font-size: 13px; font-weight: 500;
    color: #1E293B; text-decoration: none;
    border-radius: 4px;
    transition: background 0.25s, color 0.25s;
}
.gel-dropdown li a:hover { background: rgba(255,121,0,0.06); color: var(--sv-orange); }
.drop-icon {
    width: 26px; height: 26px; background: #F1F5F9;
    border-radius: 4px; display: flex; align-items: center; justify-content: center;
    color: var(--sv-orange); font-size: 12px; flex-shrink: 0;
}
.gel-dropdown-divider { border: none; border-top: 1px solid var(--sv-border); margin: 4px 0; }
.gel-nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.gel-btn-nav {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 16px; font-size: 12.5px; font-weight: 600;
    border-radius: 6px; text-decoration: none;
    transition: all 0.25s; border: none; cursor: pointer;
}
.gel-btn-nav-primary { background: var(--sv-orange); color: #fff; }
.gel-btn-nav-primary:hover { background: var(--sv-orange-hov); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(255,121,0,0.3); }
.gel-btn-nav-outline { background: transparent; color: #1E293B; border: 1.5px solid var(--sv-border); }
.gel-btn-nav-outline:hover { border-color: var(--sv-orange); color: var(--sv-orange); }
.gel-toggler { display: none; background: none; border: 1.5px solid var(--sv-border); border-radius: 4px; padding: 6px 9px; cursor: pointer; color: #111827; font-size: 17px; transition: all 0.25s; }
.gel-toggler:hover { border-color: var(--sv-orange); color: var(--sv-orange); }
.gel-mobile-menu {
    display: none; position: fixed; top: 72px; left: 0; right: 0;
    background: #fff; border-bottom: 3px solid var(--sv-orange);
    box-shadow: 0 8px 30px rgba(0,0,0,0.1); z-index: 1040;
    padding: 16px 24px 24px;
    max-height: calc(100vh - 72px); overflow-y: auto;
}
.gel-mobile-menu.open { display: block; }
.gel-mobile-link {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 0; font-size: 14px; font-weight: 500;
    color: #1E293B; text-decoration: none;
    border-bottom: 1px solid var(--sv-border);
}
.gel-mobile-link:last-child { border-bottom: none; }
.gel-mobile-link:hover { color: var(--sv-orange); }
.text-orange { color: var(--sv-orange); }

@media (max-width: 991px) {
    .gel-nav-center { display: none; }
    .gel-toggler { display: flex; }
    .gel-navbar .container-fluid { padding: 0 16px; }
}

/* ── KEYFRAMES ── */
@keyframes svOrbe {
    0%   { transform: translate(0, 0) scale(1); opacity: 0.12; }
    25%  { transform: translate(40px, -40px) scale(1.15); opacity: 0.18; }
    50%  { transform: translate(-30px, 20px) scale(0.9); opacity: 0.10; }
    75%  { transform: translate(20px, 30px) scale(1.05); opacity: 0.15; }
    100% { transform: translate(0, 0) scale(1); opacity: 0.12; }
}
@keyframes svGradient {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@keyframes svParticle {
    0%   { transform: translateY(0) scale(1); opacity: 0.6; }
    50%  { transform: translateY(-20px) scale(1.3); opacity: 1; }
    100% { transform: translateY(0) scale(1); opacity: 0.6; }
}
@keyframes svFadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: none; }
}

/* ── HERO ── */
.sv-hero {
    margin-top: 72px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #0F172A, #1E293B, #0F172A, #334155, #0F172A);
    background-size: 400% 400%;
    animation: svGradient 15s ease infinite;
    padding: 80px 0 0;
    text-align: center;
}
.sv-hero-bg { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
.sv-hero-orbe {
    position: absolute; border-radius: 50%;
    filter: blur(80px);
    animation: svOrbe 8s ease-in-out infinite;
}
.sv-hero-orbe-1 { width: 500px; height: 500px; background: #FF7900; top: -200px; right: -100px; }
.sv-hero-orbe-2 { width: 350px; height: 350px; background: #3B82F6; bottom: -120px; left: -60px; animation-delay: -3s; }
.sv-hero-orbe-3 { width: 250px; height: 250px; background: #8B5CF6; top: 30%; left: 50%; animation-delay: -5s; }

.sv-hero-particule {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.4);
    animation: svParticle 4s ease-in-out infinite;
    pointer-events: none;
}

.sv-hero-wave {
    position: absolute; bottom: 0; left: 0; right: 0; line-height: 0; pointer-events: none;
}
.sv-hero-wave svg { display: block; width: 100%; height: 60px; }

.sv-hero-ready .sv-hero-title { animation: svFadeUp 0.6s both; animation-delay: 0.1s; }
.sv-hero-ready .sv-hero-text { animation: svFadeUp 0.6s both; animation-delay: 0.2s; }
.sv-hero-ready .sv-search { animation: svFadeUp 0.6s both; animation-delay: 0.3s; }
.sv-hero-ready .sv-stats-row { animation: svFadeUp 0.6s both; animation-delay: 0.4s; }

.sv-hero-title {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    color: #fff;
    letter-spacing: -1px;
    line-height: 1.15;
    margin-bottom: 20px;
}
.sv-hero-accent { color: #FF7900; }
.sv-hero-text {
    font-size: 16px;
    color: rgba(255,255,255,0.6);
    line-height: 1.7;
    max-width: 600px;
    margin: 0 auto 32px;
}

.sv-search {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.08);
    border: 1.5px solid rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 0 16px;
    backdrop-filter: blur(8px);
    transition: border-color 0.3s, background 0.3s;
}
.sv-search:focus-within {
    border-color: rgba(255,121,0,0.5);
    background: rgba(255,255,255,0.12);
}
.sv-search-icon { color: rgba(255,255,255,0.4); font-size: 15px; margin-right: 12px; }
.sv-search-input {
    flex: 1; border: none; outline: none; background: none;
    padding: 14px 0; font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: #fff;
}
.sv-search-input::placeholder { color: rgba(255,255,255,0.35); }
.sv-search-clear {
    background: none; border: none; cursor: pointer;
    color: rgba(255,255,255,0.4); font-size: 10px; padding: 4px;
    transition: color 0.2s;
}
.sv-search-clear:hover { color: #ef4444; }

.sv-stats-row {
    display: flex;
    justify-content: center;
    gap: 48px;
    margin-top: 40px;
    padding: 32px 0 40px;
}
.sv-stat { text-align: center; }
.sv-stat-value {
    display: block;
    font-family: 'Outfit', sans-serif;
    font-size: 28px;
    font-weight: 900;
    color: #FF7900;
    line-height: 1;
}
.sv-stat-label {
    font-size: 13px;
    color: rgba(255,255,255,0.5);
    margin-top: 4px;
}
@media (max-width: 600px) {
    .sv-hero { padding: 60px 0 0; }
    .sv-stats-row { gap: 24px; padding-bottom: 32px; }
    .sv-stat-value { font-size: 22px; }
    .sv-hero-wave svg { height: 40px; }
}

/* ── BODY / CATEGORIES ── */
.sv-body { padding: 40px 0 60px; }

/* Search info */
.sv-search-info {
    display: flex; align-items: center; gap: 10px;
    background: #EFF6FF; border: 1px solid #BFDBFE;
    border-radius: 10px; padding: 10px 16px;
    font-size: 13px; color: #1E40AF;
    margin-bottom: 32px;
}
.sv-search-info-clear {
    margin-left: auto; background: none; border: none;
    color: #3B82F6; font-weight: 600; font-size: 12px; cursor: pointer;
    padding: 4px 8px; border-radius: 4px;
}
.sv-search-info-clear:hover { background: rgba(59,130,246,0.1); }

/* Empty */
.sv-empty { text-align: center; padding: 60px 20px; }
.sv-empty-icon {
    width: 64px; height: 64px; border-radius: 16px;
    background: rgba(255,121,0,0.08);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: var(--sv-orange);
    margin: 0 auto 16px;
}
.sv-empty h3 { font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: var(--sv-dark); }
.sv-empty p { font-size: 13px; color: var(--sv-gray); margin: 8px 0 20px; }
.sv-empty-btn {
    background: var(--sv-orange); color: #fff; border: none;
    padding: 10px 24px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: background 0.2s;
}
.sv-empty-btn:hover { background: var(--sv-orange-hov); }

/* Category */
.sv-cat { margin-bottom: 48px; }
.sv-cat-anchor { scroll-margin-top: 90px; }
.sv-cat-head {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 2px solid var(--sv-border);
}
.sv-cat-title {
    font-family: 'Outfit', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--sv-dark);
    margin: 0;
}
.sv-cat-desc {
    font-size: 13px;
    color: var(--sv-gray);
    margin: 0;
    flex-basis: 100%;
}
.sv-cat-pill {
    margin-left: auto;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    padding: 3px 11px;
    border-radius: 100px;
}
.sv-cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}
.sv-card-wrap { display: flex; }
.sv-cat-empty {
    background: #F8FAFC;
    border: 1px dashed #CBD5E1;
    border-radius: 12px;
    padding: 20px;
    font-size: 13px;
    color: var(--sv-gray);
    text-align: center;
}

/* Card */
.sv-card {
    background: #fff;
    border: 1px solid var(--sv-border);
    border-radius: 12px;
    display: flex; flex-direction: column;
    width: 100%;
    padding: 24px;
    transition: box-shadow 0.3s, transform 0.3s, border-color 0.3s;
}
.sv-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    border-color: rgba(255,121,0,0.15);
    transform: translateY(-3px);
}

.sv-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}
.sv-card-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
    border: 1px solid;
    transition: transform 0.3s;
}
.sv-card:hover .sv-card-icon { transform: scale(1.05); }
.sv-card-badge {
    font-size: 11px; font-weight: 600;
    background: rgba(16,185,129,0.1); color: #059669;
    padding: 4px 10px; border-radius: 100px;
    white-space: nowrap;
}
.sv-card-badge--ghost { background: #F1F5F9; color: var(--sv-gray); }

.sv-card-title {
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--sv-dark);
    margin: 0 0 8px;
}
.sv-card-text {
    font-size: 13px;
    color: var(--sv-gray);
    line-height: 1.6;
    flex-grow: 1;
    margin: 0 0 16px;
    display: -webkit-box; -webkit-line-clamp: 3;
    -webkit-box-orient: vertical; overflow: hidden;
}
.sv-card-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: var(--sv-gray);
    margin-bottom: 18px;
}
.sv-card-meta i { margin-right: 5px; font-size: 12px; }

.sv-card-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: var(--btn-bg, var(--sv-orange));
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}
.sv-card-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
.sv-card-btn i { transition: transform 0.2s; }
.sv-card-btn:hover i { transform: translateX(3px); }

@media (max-width: 640px) {
    .sv-cat-grid { grid-template-columns: 1fr; }
}

/* ── CTA ── */
.sv-cta {
    background: var(--sv-dark);
    padding: 56px 0;
}
.sv-cta-title {
    font-family: 'Outfit', sans-serif;
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 10px;
}
.sv-cta-text {
    font-size: 15px;
    color: rgba(255,255,255,0.55);
    margin-bottom: 28px;
}
.sv-cta-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}
.sv-cta-btn {
    display: inline-flex;
    align-items: center;
    background: var(--sv-orange);
    color: #fff;
    padding: 13px 32px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    font-family: 'Outfit', sans-serif;
    transition: background 0.25s, transform 0.2s;
}
.sv-cta-btn:hover { background: var(--sv-orange-hov); color: #fff; transform: translateY(-2px); }
.sv-cta-link {
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    font-size: 13px;
    transition: color 0.25s;
}
.sv-cta-link:hover { color: var(--sv-orange); }

/* ── FOOTER ── */
.sv-footer {
    background: #060E1A;
    padding: 48px 0 0;
    border-top: 3px solid var(--sv-orange);
}
.sv-footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 32px;
}
.sv-footer-col ul { list-style: none; padding: 0; margin: 0; }
.sv-footer-col ul li { margin-bottom: 8px; font-size: 13px; color: rgba(255,255,255,0.35); }
.sv-footer-col ul li a { color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.25s; }
.sv-footer-col ul li a:hover { color: var(--sv-orange); }
.sv-footer-col ul li i { margin-right: 6px; color: var(--sv-orange); font-size: 12px; }
.sv-footer-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.sv-footer-logo {
    width: 40px; height: 40px; background: var(--sv-orange);
    border-radius: 6px; display: flex; align-items: center; justify-content: center;
    font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 900; color: #fff;
}
.sv-footer-name { font-family: 'Outfit', sans-serif; font-size: 17px; font-weight: 800; color: #fff; }
.sv-footer-sub { font-size: 9px; font-weight: 600; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; }
.sv-footer-desc { font-size: 13px; color: rgba(255,255,255,0.35); line-height: 1.6; max-width: 280px; }
.sv-footer-h {
    font-family: 'Outfit', sans-serif;
    font-size: 11px; font-weight: 700;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase; letter-spacing: 0.08em;
    margin-bottom: 14px;
}
.sv-footer-bar {
    display: flex; align-items: center; justify-content: space-between;
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 20px 0; margin-top: 40px;
    font-size: 12px; color: rgba(255,255,255,0.25);
}
.sv-footer-bar-links { display: flex; gap: 16px; }
.sv-footer-bar a { color: rgba(255,255,255,0.25); text-decoration: none; transition: color 0.25s; }
.sv-footer-bar a:hover { color: var(--sv-orange); }

@media (max-width: 768px) {
    .sv-footer-grid { grid-template-columns: 1fr 1fr; gap: 24px; }
    .sv-footer-bar { flex-direction: column; text-align: center; gap: 8px; }
}
@media (max-width: 480px) {
    .sv-footer-grid { grid-template-columns: 1fr; }
}

</style>
