<script setup>
/**
 * Index.vue — Page publique de présentation du catalogue (services et modèles)
 * Rôle : Affiche les catégories et services avec filtres, recherche, animations
 *        et ajout au panier. Ce composant inclut navbar, hero, grille de services,
 *        témoignages, CTA et footer.
 * L'utilisateur peut filtrer par catégorie, rechercher, ajouter au panier.
 * Dépendances : authStore pour l'état d'authentification
 */
import { ref, computed, reactive, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { authStore } from '../../../stores/auth';

/* Props : liste des catégories (avec leurs services) reçue du backend */
const props = defineProps({
    categories: { type: Array, required: true }
});

/* États locaux de filtrage et d'affichage */
const searchQuery = ref('');
const selectedCategory = ref('');
const showAllServices = ref(false);

/* Filtre les services par catégorie + scroll fluide vers la grille */
const filterByCategory = (cat) => {
    selectedCategory.value = cat;
    showAllServices.value = false;
    if (cat) {
        nextTick(() => {
            const el = document.getElementById('services-list-container');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
};

const showAll = () => {
    selectedCategory.value = '';
    searchQuery.value = '';
    showAllServices.value = true;
    nextTick(() => {
        const el = document.getElementById('services-list-container');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    });
};

const csrfToken = computed(() => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
});

/* État de l'UI : menu mobile, modale auth, animations et panier */
const mobileOpen = ref(false);
const showAuthModal = ref(false);
const animated = ref(false);
const cartCount = ref(0);
const processingCart = ref({});
const cartAdded = ref({});

/* Ajoute un service au panier via l'API /api/cart/add */
const addToCart = async (serviceId) => {
    processingCart.value[serviceId] = true;
    try {
        const res = await fetch('/api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: serviceId, quantity: 1 })
        });
        if (res.ok) {
            const data = await res.json();
            cartAdded.value[serviceId] = true;
            cartCount.value = data.count;
            setTimeout(() => { cartAdded.value[serviceId] = false; }, 2000);
        } else {
            alert('Impossible d\'ajouter ce service. Veuillez réessayer.');
        }
    } catch (e) {
        console.error(e);
    } finally {
        processingCart.value[serviceId] = false;
    }
};

const counters = reactive({ services: 0, categories: 0, started: false });
let observer = null;

const filteredCategories = computed(() => {
    let cats = props.categories;
    if (selectedCategory.value) {
        cats = cats.filter(c => c.nom === selectedCategory.value);
    }
    if (!searchQuery.value.trim()) return cats;
    const q = searchQuery.value.toLowerCase();
    return cats.map(cat => ({
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

const allFilteredServices = computed(() => {
    let list = [];
    filteredCategories.value.forEach(cat => {
        cat.services.forEach(svc => {
            list.push({
                ...svc,
                _category: cat
            });
        });
    });
    return list;
});

const initObserver = () => {
    if (observer) {
        observer.disconnect();
    }
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

watch([selectedCategory, searchQuery], () => {
    nextTick(() => {
        initObserver();
    });
});

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
    // Charger le nombre d'articles du panier
    fetch('/api/cart')
        .then(r => r.json())
        .then(data => { cartCount.value = data.count || 0; })
        .catch(() => {});
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
    // Return standard GEL Orange instead of multi-color
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
                        <a href="/nos-modules" class="gel-nav-link">Nos Modules <i class="bi-chevron-down chevron"></i></a>
                        <ul class="gel-dropdown">
                            <li><a href="/nos-modules#pole-administration"><span class="drop-icon"><i class="bi-shield-lock"></i></span> Pôle Administration</a></li>
                            <li><a href="/nos-modules#pole-comptabilite"><span class="drop-icon"><i class="bi-calculator"></i></span> Pôle Comptabilité / Finance</a></li>
                            <li><a href="/nos-modules#pole-fiscal"><span class="drop-icon"><i class="bi-receipt"></i></span> Pôle Fiscal</a></li>
                            <li><a href="/nos-modules#pole-social"><span class="drop-icon"><i class="bi-people"></i></span> Pôle Social & Paie</a></li>
                            <li><a href="/nos-modules#pole-juridique"><span class="drop-icon"><i class="bi-bank2"></i></span> Pôle Juridique</a></li>
                            <li><a href="/nos-modules#pole-it"><span class="drop-icon"><i class="bi-laptop"></i></span> Pôle IT</a></li>
                            <li><hr class="gel-dropdown-divider"></li>
                            <li><a href="/nos-modules"><span class="drop-icon"><i class="bi-grid-3x3-gap"></i></span> Découvrir tous les modules</a></li>
                        </ul>
                    </li>
                    <li class="gel-nav-item">
                        <a href="/nos-services" class="gel-nav-link active" @click.prevent="filterByCategory('')">Services <i class="bi-chevron-down chevron"></i></a>
                        <ul class="gel-dropdown">
                            <li v-for="cat in props.categories" :key="'nav-'+cat.id">
                                <a href="#" @click.prevent="filterByCategory(cat.nom)">
                                    <span class="drop-icon"><i :class="cat.icone"></i></span> {{ cat.nom }}
                                </a>
                            </li>
                            <li><hr class="gel-dropdown-divider"></li>
                            <li><a href="#" @click.prevent="showAll()"><span class="drop-icon"><i class="bi-grid-3x3-gap"></i></span> Tous les services</a></li>
                        </ul>
                    </li>
                    <li class="gel-nav-item">
                        <a href="/a-propos" class="gel-nav-link">À propos <i class="bi-chevron-down chevron"></i></a>
                        <ul class="gel-dropdown">
                            <li><a href="/notre-cabinet"><span class="drop-icon"><i class="bi-building"></i></span> Notre Cabinet</a></li>
                            <li><a href="/notre-equipe"><span class="drop-icon"><i class="bi-people-fill"></i></span> Notre Équipe</a></li>
                            <li><a href="/carrieres"><span class="drop-icon"><i class="bi-briefcase-fill"></i></span> Carrières</a></li>
                        </ul>
                    </li>
                    <li class="gel-nav-item">
                        <a href="/ressources" class="gel-nav-link">Ressources <i class="bi-chevron-down chevron"></i></a>
                        <ul class="gel-dropdown">
                            <li><a href="/blogue"><span class="drop-icon"><i class="bi-pencil-square"></i></span> Blogue</a></li>
                            <li><a href="/documentation"><span class="drop-icon"><i class="bi-file-text"></i></span> Documentation</a></li>
                            <li><a href="/faq"><span class="drop-icon"><i class="bi-question-circle"></i></span> FAQ</a></li>
                            <li><hr class="gel-dropdown-divider"></li>
                            <li><a href="/centre-aide"><span class="drop-icon"><i class="bi-headset"></i></span> Centre d'aide</a></li>
                        </ul>
                    </li>
                    <li class="gel-nav-item"><a href="/tarifs" class="gel-nav-link">Tarifs</a></li>
                    <li class="gel-nav-item"><a href="/contact" class="gel-nav-link">Contact</a></li>
                </ul>
                <div class="gel-nav-right">
                    <!-- Icône Panier -->
                    <a href="/panier" class="gel-btn-nav gel-btn-nav-outline" style="position:relative;" title="Mon panier">
                        <i class="bi-cart"></i>
                        <span v-if="cartCount > 0"
                              style="position:absolute; top:-6px; right:-8px; background:#FF7900; color:#fff;
                                     font-size:10px; font-weight:700; padding:1px 5px; border-radius:10px; line-height:1.4;">
                            {{ cartCount }}
                        </span>
                    </a>
                    
                    <a v-if="authStore.isAuthenticated" href="/dashboard" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-speedometer2"></i> Mon Espace</a>
                    <template v-else>
                        <a href="/register" class="gel-btn-nav gel-btn-nav-outline"><i class="bi-person-plus"></i> S'inscrire</a>
                        <a href="/login" class="gel-btn-nav gel-btn-nav-primary"><i class="bi-box-arrow-in-right"></i> Connexion</a>
                    </template>
                    <button class="gel-toggler" @click="mobileOpen = !mobileOpen" aria-label="Menu"><i :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i></button>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div :class="['gel-mobile-menu', { open: mobileOpen }]" id="gelMobileMenu">
            <a href="/" class="gel-mobile-link"><i class="bi-house text-orange me-2"></i>Accueil</a>
            <a href="/nos-modules" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Nos Modules</a>
            <a href="/nos-services" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Services</a>
            <div style="padding-left:36px;font-size:12px;color:rgba(30,41,59,0.7);margin-bottom:4px;">
                <a v-for="cat in props.categories" :key="'mob-'+cat.id" :href="'/nos-services?category=' + encodeURIComponent(cat.nom)" @click="mobileOpen = false" style="color:inherit;text-decoration:none;display:block;padding:6px 0;">{{ cat.nom }}</a>
            </div>
            <a href="/blogue" class="gel-mobile-link"><i class="bi-pencil-square text-orange me-2"></i>Blogue</a>
            <a href="/documentation" class="gel-mobile-link"><i class="bi-file-text text-orange me-2"></i>Documentation</a>
            <a href="/faq" class="gel-mobile-link"><i class="bi-question-circle text-orange me-2"></i>FAQ</a>
            <a href="/centre-aide" class="gel-mobile-link"><i class="bi-headset text-orange me-2"></i>Centre d'aide</a>
            <a href="/notre-cabinet" class="gel-mobile-link"><i class="bi-building text-orange me-2"></i>Notre Cabinet</a>
            <a href="/notre-equipe" class="gel-mobile-link"><i class="bi-people-fill text-orange me-2"></i>Notre Équipe</a>
            <a href="/carrieres" class="gel-mobile-link"><i class="bi-briefcase-fill text-orange me-2"></i>Carrières</a>
            <a href="/tarifs" class="gel-mobile-link"><i class="bi-currency-dollar text-orange me-2"></i>Tarifs</a>
            <a href="/contact" class="gel-mobile-link"><i class="bi-envelope text-orange me-2"></i>Contact</a>
            
            <template v-if="authStore.isAuthenticated">
                <a v-if="authStore.user?.role === 'client'" href="/client/orders" class="gel-mobile-link"><i class="bi-speedometer2 text-orange me-2"></i>Mon Espace</a>
                <a v-else-if="authStore.user?.client_id" href="/company/dashboard" class="gel-mobile-link"><i class="bi-speedometer2 text-orange me-2"></i>Portail</a>
                <a v-else href="/dashboard" class="gel-mobile-link"><i class="bi-speedometer2 text-orange me-2"></i>Tableau de bord</a>
                <form method="POST" action="/logout" style="display:block; width:100%; margin:0;">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <button type="submit" class="gel-mobile-link" style="background:transparent; border:none; text-align:left; width:100%;">
                        <i class="bi-box-arrow-right text-orange me-2"></i>Déconnexion
                    </button>
                </form>
            </template>
            <template v-else>
                <a href="/login" class="gel-mobile-link"><i class="bi-box-arrow-in-right text-orange me-2"></i>Connexion</a>
                <a href="/register" class="gel-mobile-link"><i class="bi-person-plus text-orange me-2"></i>S'inscrire</a>
            </template>
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

                        <h1 class="sv-hero-title" :class="{ 'sv-visible': animated }">
                            Des services adaptés à vos<br>
                            <span class="sv-hero-accent">besoins professionnels</span>
                        </h1>
                        <p class="sv-hero-text" :class="{ 'sv-visible': animated }">
                            Notre cabinet vous accompagne dans la gestion comptable, fiscale, juridique, sociale et digitale avec des solutions adaptées à votre activité.
                        </p>
                        
                        <div class="d-flex align-items-center justify-content-center gap-3 mt-4" :class="{ 'sv-visible': animated }" style="opacity:0; transform:translateY(24px); transition:all 0.6s 0.25s;">
                            <a href="#services-grid" class="sv-cta-btn" @click.prevent="document.getElementById('services-grid').scrollIntoView({behavior:'smooth'})">Découvrir nos services</a>
                            <a href="/contact" class="sv-cta-btn" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);">Demander un devis</a>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center mt-5">
                    <div class="col-lg-6">
                        <div class="sv-search" :class="{ 'sv-visible': animated }">
                            <i class="bi-search sv-search-icon"></i>
                            <input v-model="searchQuery" type="text" class="sv-search-input" placeholder="Rechercher un service…" />
                            <button v-if="searchQuery" class="sv-search-clear" @click="searchQuery = ''"><i class="bi-x-lg"></i></button>
                        </div>
                    </div>
                </div>
                <div class="sv-stats-row" :class="{ 'sv-visible': animated }">
                    <div class="sv-stat">
                        <span class="sv-stat-value">7</span>
                        <span class="sv-stat-label">Pôles d'expertise</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-value">24/7</span>
                        <span class="sv-stat-label">Services disponibles</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-value"><i class="bi-person-check" style="font-size:28px;"></i></span>
                        <span class="sv-stat-label">Accompagnement personnalisé</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-value"><i class="bi-buildings" style="font-size:28px;"></i></span>
                        <span class="sv-stat-label">Solutions adaptées</span>
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
             APPROCHE (RICH SECTION)
        <!-- ════════════════════════════════════════
             DOMAINES D'EXPERTISE
        ════════════════════════════════════════ -->
        <section class="sv-body" style="padding-top:60px; padding-bottom:20px; background:#f8fafc;" id="services-grid">
            <div class="container">
                <div class="text-center mb-5 sv-anim">
                    <h2 style="font-family:'Outfit',sans-serif; font-size:clamp(1.6rem, 2.5vw, 2.2rem); font-weight:800; color:#0F172A; margin-bottom:12px;">Nos domaines d'expertise</h2>
                    <p style="color:#64748b; font-size:15px; max-width:600px; margin:0 auto;">Découvrez l'ensemble de nos pôles d'expertise conçus pour répondre à toutes les exigences de votre cabinet.</p>
                </div>
                <div class="row g-4 justify-content-center">
                    <div v-for="(cat, index) in props.categories" :key="'dom-'+cat.id" class="col-md-6 col-lg-4 sv-anim" :style="{ transitionDelay: (index * 0.1) + 's' }">
                        <div style="background:#fff; border-radius:16px; padding:24px; height:100%; border:1px solid #e2e8f0; transition:all 0.3s; cursor:pointer;" @click="filterByCategory(cat.nom)" class="hover-shadow-sm">
                            <div style="display:flex; align-items:center; gap:16px; margin-bottom:12px;">
                                <div :style="{ width:'48px', height:'48px', borderRadius:'12px', background:categoryColor(cat.nom)+'15', color:categoryColor(cat.nom), display:'flex', alignItems:'center', justifyContent:'center', fontSize:'22px' }">
                                    <i :class="categoryIcon(cat.nom)"></i>
                                </div>
                                <h3 style="font-size:17px; font-weight:700; color:#1e293b; margin:0;">{{ cat.nom }}</h3>
                            </div>
                            <p style="font-size:13.5px; color:#64748b; line-height:1.6; margin:0;">
                                {{ cat.description || 'Gérez efficacement vos opérations avec nos outils spécialisés pour ce domaine.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             SERVICES
        ════════════════════════════════════════ -->
        <section class="sv-body" id="services-list-container">
            <div class="container">

                <!-- Search info -->
                <div v-if="searchQuery.trim() && !hasNoResults" class="sv-search-info sv-anim">
                    <i class="bi-info-circle"></i>
                    <span>
                        <template v-if="selectedCategory">Catégorie: <strong>{{ selectedCategory }}</strong> - </template>
                        <strong>{{ filteredCategories.reduce((s, c) => s + c.services.length, 0) }}</strong> résultat(s)
                    </span>
                    <button class="sv-search-info-clear" @click="filterByCategory(''); searchQuery = ''; showAllServices = false;">Effacer</button>
                </div>

                <!-- Empty state -->
                <div v-if="hasNoResults" class="sv-empty sv-anim">
                    <div class="sv-empty-icon"><i class="bi-search"></i></div>
                    <h3>Aucun service trouvé</h3>
                    <p>Essayez un autre terme de recherche.</p>
                    <button class="sv-empty-btn" @click="searchQuery = ''; showAllServices = true;"><i class="bi-x-lg me-2"></i>Voir tous</button>
                </div>

                <!-- Categories -->
                <template v-if="selectedCategory">
                    <div v-for="(cat, ci) in filteredCategories" :key="cat.id" class="sv-cat">
                        <div class="sv-cat-anchor" :id="'cat-' + cat.id"></div>
                        <div class="sv-cat-head sv-anim" :style="{ transitionDelay: ci * 0.08 + 's' }">
                            <h2 class="sv-cat-title">{{ cat.nom }}</h2>
                            <p v-if="cat.description" class="sv-cat-desc">{{ cat.description }}</p>
                            <span class="sv-cat-pill" :style="{ background: categoryColor(cat.nom) }">{{ cat.services.length }}</span>
                        </div>
                        <div class="sv-cat-grid">
                            <div v-for="(svc, si) in cat.services" :key="svc.id" class="sv-card-wrap">
                                <div class="sv-card sv-anim"
                                     :style="{
                                         transitionDelay: (ci * 0.08 + si * 0.05) + 's',
                                         '--card-accent': categoryColor(cat.nom),
                                         '--card-btn-bg': categoryColor(cat.nom),
                                     }">
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
                                    <div style="display:flex; gap:10px; margin-top:20px;">
                                        <a :href="'/nos-services/' + cat.id + '/' + svc.id" class="sv-card-btn" style="flex:1; justify-content:center; padding:10px; font-size:13px;">
                                            Détails
                                        </a>
                                        <button @click="addToCart(svc.id)" class="sv-card-btn" style="flex:1; justify-content:center; padding:10px; font-size:13px; background:#1e293b; color:#fff;" :disabled="processingCart[svc.id]">
                                            <template v-if="processingCart[svc.id]">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </template>
                                            <template v-else-if="cartAdded[svc.id]">
                                                <i class="bi-check2"></i> Ajouté
                                            </template>
                                            <template v-else>
                                                Commander
                                            </template>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!cat.services.length" class="sv-cat-empty sv-anim">
                            <i class="bi-inbox me-2"></i>Aucun service disponible dans cette catégorie.
                        </div>
                    </div>
                </template>
                <template v-else-if="searchQuery.trim() || showAllServices">
                    <div class="sv-cat-grid">
                        <div v-for="(svc, si) in allFilteredServices" :key="svc.id" class="sv-card-wrap">
                            <div class="sv-card sv-anim"
                                 :style="{
                                     transitionDelay: ((si % 15) * 0.05) + 's',
                                     '--card-accent': categoryColor(svc._category.nom),
                                     '--card-btn-bg': categoryColor(svc._category.nom),
                                 }">
                                <div class="sv-card-top">
                                    <div class="sv-card-icon" :style="{
                                        background: categoryColor(svc._category.nom) + '12',
                                        color: categoryColor(svc._category.nom),
                                        borderColor: categoryColor(svc._category.nom) + '25'
                                    }">
                                        <i :class="categoryIcon(svc._category.nom)"></i>
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
                                <div style="display:flex; gap:10px; margin-top:20px;">
                                    <a :href="'/nos-services/' + svc._category.id + '/' + svc.id" class="sv-card-btn" style="flex:1; justify-content:center; padding:10px; font-size:13px;">
                                        Détails
                                    </a>
                                    <button @click="addToCart(svc.id)" class="sv-card-btn" style="flex:1; justify-content:center; padding:10px; font-size:13px; background:#1e293b; color:#fff;" :disabled="processingCart[svc.id]">
                                        <template v-if="processingCart[svc.id]">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        </template>
                                        <template v-else-if="cartAdded[svc.id]">
                                            <i class="bi-check2"></i> Ajouté
                                        </template>
                                        <template v-else>
                                            Commander
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             POURQUOI CHOISIR NOS SERVICES
        ════════════════════════════════════════ -->
        <section class="sv-body" style="padding-top:80px; padding-bottom:60px;">
            <div class="container">
                <div class="row justify-content-center text-center mb-5">
                    <div class="col-lg-8 sv-anim">
                        <h2 style="font-family:'Outfit',sans-serif; font-size:clamp(1.6rem, 2.5vw, 2.2rem); font-weight:800; color:#0F172A; margin-bottom:12px;">Pourquoi choisir nos services ?</h2>
                        <p style="color:#64748b; font-size:15px; max-width:600px; margin:0 auto;">Des atouts majeurs pour vous garantir une expérience optimale et des résultats concrets.</p>
                    </div>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4 col-sm-6 sv-anim" style="transition-delay: 0.1s;">
                        <div style="background:#fff; border-radius:16px; padding:24px; text-align:center; height:100%; box-shadow:0 4px 20px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
                            <div style="width:56px; height:56px; background:#EFF6FF; color:#3B82F6; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                                <i class="bi-award"></i>
                            </div>
                            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:10px;">Expertise métier</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 sv-anim" style="transition-delay: 0.2s;">
                        <div style="background:#fff; border-radius:16px; padding:24px; text-align:center; height:100%; box-shadow:0 4px 20px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
                            <div style="width:56px; height:56px; background:#FEF3C7; color:#F59E0B; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                                <i class="bi-stopwatch"></i>
                            </div>
                            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:10px;">Gain de temps</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 sv-anim" style="transition-delay: 0.3s;">
                        <div style="background:#fff; border-radius:16px; padding:24px; text-align:center; height:100%; box-shadow:0 4px 20px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
                            <div style="width:56px; height:56px; background:#FCE7F3; color:#EC4899; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                                <i class="bi-person-check"></i>
                            </div>
                            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:10px;">Suivi personnalisé</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 sv-anim" style="transition-delay: 0.4s;">
                        <div style="background:#fff; border-radius:16px; padding:24px; text-align:center; height:100%; box-shadow:0 4px 20px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
                            <div style="width:56px; height:56px; background:#DCFCE7; color:#10B981; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                                <i class="bi-shield-check"></i>
                            </div>
                            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:10px;">Sécurité des données</h3>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 sv-anim" style="transition-delay: 0.5s;">
                        <div style="background:#fff; border-radius:16px; padding:24px; text-align:center; height:100%; box-shadow:0 4px 20px rgba(0,0,0,0.04); border:1px solid #e2e8f0;">
                            <div style="width:56px; height:56px; background:#F3E8FF; color:#8B5CF6; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                                <i class="bi-headset"></i>
                            </div>
                            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:10px;">Accompagnement continu</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             PARCOURS CLIENT
        ════════════════════════════════════════ -->
        <section class="sv-body" style="padding-top:40px; padding-bottom:80px; background:#fff;">
            <div class="container">
                <div class="row justify-content-center text-center mb-5">
                    <div class="col-lg-8 sv-anim">
                        <h2 style="font-family:'Outfit',sans-serif; font-size:clamp(1.6rem, 2.5vw, 2.2rem); font-weight:800; color:#0F172A; margin-bottom:12px;">Comment ça marche ?</h2>
                        <p style="color:#64748b; font-size:15px; max-width:600px; margin:0 auto;">Un processus simple et transparent pour répondre rapidement à vos besoins.</p>
                    </div>
                </div>
                <div class="row g-4 position-relative">
                    <div class="col-md-3 text-center sv-anim" style="transition-delay: 0.1s; position:relative;">
                        <div style="width:64px; height:64px; background:var(--sv-orange); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:bold; margin:0 auto 16px; position:relative; z-index:2; box-shadow:0 4px 15px rgba(255,121,0,0.3);">
                            1
                        </div>
                        <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:8px;">Choisissez votre service</h3>
                    </div>
                    <div class="col-md-3 text-center sv-anim" style="transition-delay: 0.2s; position:relative;">
                        <div style="width:64px; height:64px; background:var(--sv-orange); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:bold; margin:0 auto 16px; position:relative; z-index:2; box-shadow:0 4px 15px rgba(255,121,0,0.3);">
                            2
                        </div>
                        <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:8px;">Envoyez votre demande</h3>
                    </div>
                    <div class="col-md-3 text-center sv-anim" style="transition-delay: 0.3s; position:relative;">
                        <div style="width:64px; height:64px; background:var(--sv-orange); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:bold; margin:0 auto 16px; position:relative; z-index:2; box-shadow:0 4px 15px rgba(255,121,0,0.3);">
                            3
                        </div>
                        <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:8px;">Recevez une proposition</h3>
                    </div>
                    <div class="col-md-3 text-center sv-anim" style="transition-delay: 0.4s; position:relative;">
                        <div style="width:64px; height:64px; background:var(--sv-orange); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:bold; margin:0 auto 16px; position:relative; z-index:2; box-shadow:0 4px 15px rgba(255,121,0,0.3);">
                            4
                        </div>
                        <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:8px;">Profitez de l'accompagnement</h3>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             TEMOIGNAGES
        ════════════════════════════════════════ -->
        <section class="sv-body" style="padding-top:40px; padding-bottom:80px; background:linear-gradient(135deg,#ffffff 0%,#f8fafc 100%);">
            <div class="container">
                <div class="row justify-content-center text-center mb-5">
                    <div class="col-lg-8 sv-anim">
                        <h2 style="font-family:'Outfit',sans-serif; font-size:clamp(1.8rem, 3vw, 2.4rem); font-weight:800; color:#0F172A; margin-bottom:12px;">Ils nous font confiance</h2>
                        <p style="font-size:15px; color:#64748b; line-height:1.7;">Des cabinets et entreprises béninoises qui ont transformé leur gestion grâce à GEL.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-4 sv-anim" style="transition-delay: 0.1s;">
                        <div style="background:#fff;padding:32px 24px;border-radius:16px;height:100%;position:relative;border:1px solid #e2e8f0;box-shadow:0 4px 20px rgba(0,0,0,0.03);">
                            <i class="bi-quote" style="position:absolute;top:20px;right:24px;font-size:40px;color:#FF7900;opacity:0.15;"></i>
                            <div class="d-flex align-items-center gap-2 mb-3" style="color:#FFB800;font-size:13px;">
                                <i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i>
                            </div>
                            <p style="font-size:13.5px;color:#334155;line-height:1.7;margin-bottom:24px;font-style:italic;">"Une solution simple pour gérer nos besoins administratifs."</p>
                            <div class="d-flex align-items-center gap-3 mt-auto">
                                <img src="https://ui-avatars.com/api/?name=Aminata+D&background=FF7900&color=fff&bold=true" alt="User" style="width:40px;height:40px;border-radius:50%;">
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#0F172A;">Aminata Dossou</div>
                                    <div style="font-size:11.5px;color:#64748b;">Gérante, Entreprise AD</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 sv-anim" style="transition-delay: 0.2s;">
                        <div style="background:#fff;padding:32px 24px;border-radius:16px;height:100%;position:relative;border:1px solid #e2e8f0;box-shadow:0 4px 20px rgba(0,0,0,0.03);">
                            <i class="bi-quote" style="position:absolute;top:20px;right:24px;font-size:40px;color:#FF7900;opacity:0.15;"></i>
                            <div class="d-flex align-items-center gap-2 mb-3" style="color:#FFB800;font-size:13px;">
                                <i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i>
                            </div>
                            <p style="font-size:13.5px;color:#334155;line-height:1.7;margin-bottom:24px;font-style:italic;">"Un gain de temps précieux dans notre gestion quotidienne."</p>
                            <div class="d-flex align-items-center gap-3 mt-auto">
                                <img src="https://ui-avatars.com/api/?name=Koffi+A&background=3B82F6&color=fff&bold=true" alt="User" style="width:40px;height:40px;border-radius:50%;">
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#0F172A;">Koffi Atrokpo</div>
                                    <div style="font-size:11.5px;color:#64748b;">Directeur, AutoLoc Bénin</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 sv-anim" style="transition-delay: 0.3s;">
                        <div style="background:#fff;padding:32px 24px;border-radius:16px;height:100%;position:relative;border:1px solid #e2e8f0;box-shadow:0 4px 20px rgba(0,0,0,0.03);">
                            <i class="bi-quote" style="position:absolute;top:20px;right:24px;font-size:40px;color:#FF7900;opacity:0.15;"></i>
                            <div class="d-flex align-items-center gap-2 mb-3" style="color:#FFB800;font-size:13px;">
                                <i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i><i class="bi-star-fill"></i>
                            </div>
                            <p style="font-size:13.5px;color:#334155;line-height:1.7;margin-bottom:24px;font-style:italic;">"Une expertise pointue et des conseils très utiles. Je recommande fortement."</p>
                            <div class="d-flex align-items-center gap-3 mt-auto">
                                <img src="https://ui-avatars.com/api/?name=Sena+K&background=10B981&color=fff&bold=true" alt="User" style="width:40px;height:40px;border-radius:50%;">
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#0F172A;">Sèna Kouassi</div>
                                    <div style="font-size:11.5px;color:#64748b;">Expert-Comptable</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             CTA
        ════════════════════════════════════════ -->
        <section class="sv-cta">
            <div class="container text-center">
                <h2 class="sv-cta-title">Besoin d’un accompagnement professionnel ?</h2>
                <p class="sv-cta-text">Découvrez l'ensemble de nos offres et demandez un devis gratuit.</p>
                <div class="sv-cta-actions">
                    <a href="/contact" class="sv-cta-btn"><i class="bi-envelope me-2"></i> Contactez-nous</a>
                    <a href="/register" class="sv-cta-link">Créer un espace client gratuit <i class="bi-arrow-right"></i></a>
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

/* Hero elements: start hidden, fade in with sv-visible */
.sv-hero-badge,
.sv-hero-title,
.sv-hero-text,
.sv-search,
.sv-stats-row {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.6s cubic-bezier(0.16, 0.6, 0.2, 1),
                transform 0.6s cubic-bezier(0.16, 0.6, 0.2, 1);
}
.sv-hero-badge { transition-delay: 0.05s; }
.sv-hero-title { transition-delay: 0.12s; }
.sv-hero-text { transition-delay: 0.2s; }
.sv-search { transition-delay: 0.28s; }
.sv-stats-row { transition-delay: 0.36s; }
.sv-hero .sv-visible {
    opacity: 1 !important;
    transform: none !important;
}

.sv-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,121,0,0.15);
    color: #FF7900;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 100px;
    margin-bottom: 16px;
    border: 1px solid rgba(255,121,0,0.2);
}

.sv-hero-title {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(1.8rem, 3vw, 2.4rem);
    font-weight: 800;
    color: #fff;
    letter-spacing: -1px;
    line-height: 1.15;
    margin-bottom: 16px;
}
.sv-hero-accent { color: #FF7900; }
.sv-hero-text {
    font-size: 16px;
    color: rgba(255,255,255,0.55);
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
    color: rgba(255,255,255,0.45);
    margin-top: 4px;
}
@media (max-width: 600px) {
    .sv-hero { padding: 60px 0 0; }
    .sv-stats-row { gap: 24px; padding-bottom: 32px; }
    .sv-stat-value { font-size: 22px; }
    .sv-hero-wave svg { height: 40px; }
}

/* ── BODY / CATEGORIES ── */
.sv-body { padding: 48px 0 80px; background: #F8FAFC; }

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
.sv-cat { margin-bottom: 56px; }
.sv-cat-anchor { scroll-margin-top: 90px; }
.sv-cat-head {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--sv-border);
}
.sv-cat-title {
    font-family: 'Outfit', sans-serif;
    font-size: 22px;
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
    gap: 20px;
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
    border-radius: 14px;
    display: flex; flex-direction: column;
    width: 100%;
    padding: 24px;
    transition: box-shadow 0.3s, transform 0.3s, border-color 0.3s;
    position: relative;
    overflow: hidden;
}
.sv-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--card-accent, var(--sv-orange));
    opacity: 0;
    transition: opacity 0.3s;
}
.sv-card:hover::before { opacity: 1; }
.sv-card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.07);
    border-color: transparent;
    transform: translateY(-4px);
}

.sv-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}
.sv-card-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
    border: 1px solid;
    transition: transform 0.3s, box-shadow 0.3s;
}
.sv-card:hover .sv-card-icon { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.sv-card-badge {
    font-size: 11px; font-weight: 700;
    background: rgba(16,185,129,0.1); color: #059669;
    padding: 4px 12px; border-radius: 100px;
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
    line-height: 1.7;
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
    padding-top: 12px;
    border-top: 1px solid #F1F5F9;
}
.sv-card-meta i { margin-right: 5px; font-size: 12px; }

.sv-card-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: var(--card-btn-bg, var(--sv-orange));
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 11px 16px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}
.sv-card-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
.sv-card-btn i { transition: transform 0.2s; }
.sv-card-btn:hover i { transform: translateX(4px); }

@media (max-width: 640px) {
    .sv-cat-grid { grid-template-columns: 1fr; }
}

/* ── CTA ── */
.sv-cta {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    padding: 64px 0;
    position: relative;
    overflow: hidden;
}
.sv-cta::before {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(255,121,0,0.08) 0%, transparent 70%);
    top: -100px;
    right: -100px;
    border-radius: 50%;
    pointer-events: none;
}
.sv-cta-title {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(1.6rem, 3vw, 2.2rem);
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
