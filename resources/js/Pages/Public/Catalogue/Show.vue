<script setup>
/**
 * Show.vue — Page détaillée d'un service du catalogue public
 * Rôle : Affiche les informations complètes d'un service (description, inclus,
 *        documents requis) et permet de l'ajouter au panier.
 * Props : category (objet catégorie parente), service (objet service à afficher)
 * Dépendances : authStore pour l'état d'authentification
 */
import { ref, onMounted, onUnmounted } from 'vue';
import { authStore } from '../../../stores/auth';

/* Props reçues du backend : catégorie et service à afficher */
const props = defineProps({
    category: { type: Object, required: true },
    service: { type: Object, required: true }
});

/* États réactifs : chargement, animation, panier, jeton CSRF */
const processing = ref(false);
const animated = ref(false);
const cartCount = ref(0);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

/* Observateur d'intersection pour les animations au défilement */
let observer = null;
const initObserver = () => {
    observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('show-visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.anim-show').forEach(el => observer.observe(el));
};

/* État local : indicateur d'ajout au panier réussi */
const cartAdded = ref(false);

/* Ajoute le service courant au panier via l'API /api/cart/add */
const addToCart = async () => {
    processing.value = true;
    try {
        const res = await fetch('/api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: props.service.id, quantity: 1 })
        });
        if (res.ok) {
            const data = await res.json();
            cartAdded.value = true;
            cartCount.value = data.count;
        } else {
            alert('Impossible d\'ajouter ce service. Veuillez réessayer.');
        }
    } catch (e) {
        console.error(e);
    } finally {
        processing.value = false;
    }
};

/* Montage du composant : déclenche les animations et charge le panier */
onMounted(() => {
    setTimeout(() => { animated.value = true; }, 80);
    requestAnimationFrame(() => initObserver());
    /* Charger le nombre d'articles du panier */
    fetch('/api/cart')
        .then(r => r.json())
        .then(data => { cartCount.value = data.count || 0; })
        .catch(() => {});
});

/* Démontage : nettoie l'observateur d'intersection */
onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <div style="font-family: 'Inter', sans-serif; background: #F8FAFC; min-height: 100vh;">

        <!-- ═══ Navbar ═══ -->
        <nav class="show-navbar">
            <div class="container d-flex align-items-center justify-content-between">
                <a href="/" class="show-brand text-decoration-none">
                    <div class="show-brand-icon">GEL</div>
                    <span class="show-brand-name">GEL Cabinet</span>
                </a>
                <div class="d-flex align-items-center gap-2">
                    <a href="/nos-services" class="show-nav-link">
                        <i class="bi-arrow-left me-1"></i>Catalogue
                    </a>
                    <!-- Icône Panier -->
                    <a href="/panier" class="show-btn-outline" style="position:relative; padding: 8px 14px;" title="Mon panier">
                        <i class="bi-cart"></i>
                        <span v-if="cartCount > 0"
                              style="position:absolute; top:-6px; right:-6px; background:#FF7900; color:#fff;
                                     font-size:10px; font-weight:700; padding:1px 5px; border-radius:10px; line-height:1.4;">
                            {{ cartCount }}
                        </span>
                    </a>
                    <a v-if="authStore.isAuthenticated" href="/dashboard" class="show-btn-primary">
                        <i class="bi-speedometer2 me-1"></i>Mon Espace
                    </a>
                    <a v-else href="/login" class="show-btn-outline">
                        <i class="bi-box-arrow-in-right me-1"></i>Connexion
                    </a>
                </div>
            </div>
        </nav>



        <!-- ═══ Hero Banner ═══ -->
        <div :class="['show-hero', { 'show-anim-hero': animated }]">
            <div class="container">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="show-category-badge"><i :class="category.icone"></i></div>
                    <span class="show-category-name">{{ category.nom }}</span>
                </div>
                <h1 class="show-service-title">{{ service.nom }}</h1>
                <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
                    <div class="show-quick-info">
                        <i class="bi-clock me-1" style="color: #FF7900;"></i>
                        <span>Délai : <strong>{{ service.delai_jours || 'Sur mesure' }}</strong></span>
                    </div>
                    <div class="show-quick-info">
                        <i class="bi-tag me-1" style="color: #FF7900;"></i>
                        <span>Tarif :
                            <strong>
                                <template v-if="service.tarif_type === 'fixe'">{{ service.tarif_fcfa?.toLocaleString('fr-FR') }} FCFA</template>
                                <template v-else>Sur devis</template>
                            </strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ Main Content ═══ -->
        <div class="container py-5">
            <div class="row g-4">

                <!-- ─── Left: Service Info ─── -->
                <div class="col-lg-8">

                    <!-- Description card -->
                    <div class="show-card mb-4 anim-show" style="transition-delay: 0.1s;">
                        <div class="show-card-header">
                            <i class="bi-info-circle me-2" style="color: #FF7900;"></i>
                            Description du service
                        </div>
                        <div class="show-card-body">
                            <div class="show-description">{{ service.description }}</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Included items -->
                        <div v-if="service.inclus_json && service.inclus_json.length" class="col-md-6">
                            <div class="show-card h-100 anim-show" style="transition-delay: 0.2s;">
                                <div class="show-card-header">
                                    <i class="bi-check-circle me-2" style="color: #198754;"></i>
                                    Ce qui est inclus
                                </div>
                                <div class="show-card-body">
                                    <ul class="show-checklist">
                                        <li v-for="(item, i) in service.inclus_json" :key="i" class="show-check-item">
                                            <div class="show-check-dot show-check-ok">
                                                <i class="bi-check" style="font-size: 11px;"></i>
                                            </div>
                                            <span>{{ item }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Required documents -->
                        <div v-if="service.documents_requis_json && service.documents_requis_json.length" class="col-md-6">
                            <div class="show-card h-100 anim-show" style="transition-delay: 0.3s;">
                                <div class="show-card-header">
                                    <i class="bi-file-earmark-text me-2" style="color: #FF7900;"></i>
                                    Documents requis
                                </div>
                                <div class="show-card-body">
                                    <ul class="show-checklist">
                                        <li v-for="(doc, i) in service.documents_requis_json" :key="i" class="show-check-item">
                                            <div class="show-check-dot show-check-doc">
                                                <i class="bi-file-text" style="font-size: 10px;"></i>
                                            </div>
                                            <span>{{ doc }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Right: Order Box ─── -->
                <div class="col-lg-4">
                    <div class="position-sticky" style="top: 100px;">
                        <div class="show-order-card anim-show" style="transition-delay: 0.4s;">
                            <div class="show-order-header">
                                <i class="bi-cart-plus me-2"></i>Commander ce service
                            </div>
                            <div class="show-order-body">
                                <div class="show-order-row">
                                    <div class="show-order-label">
                                        <i class="bi-clock-history me-2" style="color: #FF7900;"></i>
                                        Délai de réalisation
                                    </div>
                                    <div class="show-order-value">{{ service.delai_jours || 'Sur mesure' }}</div>
                                </div>
                                <div class="show-order-row">
                                    <div class="show-order-label">
                                        <i class="bi-tag me-2" style="color: #FF7900;"></i>
                                        Honoraires
                                    </div>
                                    <div class="show-tarif">
                                        <span v-if="service.tarif_type === 'fixe'">
                                            {{ service.tarif_fcfa?.toLocaleString('fr-FR') }} FCFA
                                        </span>
                                        <span v-else>Sur devis</span>
                                    </div>
                                </div>
                                <div class="show-order-row">
                                    <div class="show-order-label">
                                        <i class="bi-folder me-2" style="color: #FF7900;"></i>
                                        Catégorie
                                    </div>
                                    <div class="show-order-value">{{ category.nom }}</div>
                                </div>
                                <button
                                    @click="addToCart"
                                    :disabled="processing || cartAdded"
                                    class="show-cta-btn mt-4"
                                >
                                    <span v-if="processing">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Ajout en cours...
                                    </span>
                                    <span v-else-if="cartAdded">
                                        <i class="bi-check-circle me-2"></i>Ajouté au panier !
                                    </span>
                                    <span v-else>
                                        <i class="bi-cart-plus me-2"></i>Ajouter au panier
                                    </span>
                                </button>
                                <a v-if="cartAdded" href="/panier"
                                   class="show-cta-btn mt-2"
                                   style="background:transparent; border:2px solid #FF7900; color:#FF7900; text-decoration:none; display:block; text-align:center;">
                                    <i class="bi-cart me-2"></i>Voir mon panier
                                </a>
                                <p class="text-center text-muted mt-3 mb-0" style="font-size: 12px;">
                                    <i class="bi-shield-check me-1 text-success"></i>
                                    Sans engagement immédiat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══ Footer ═══ -->
        <footer class="show-footer">
            <div class="container d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:22px;height:22px;background:#FF7900;border-radius:2px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:#fff;">GEL</div>
                    <span style="font-size:12px;color:rgba(255,255,255,0.6);">&copy; {{ new Date().getFullYear() }} GEL Cabinet</span>
                </div>
                <a href="/nos-services" style="font-size:12px;color:rgba(255,255,255,0.5);text-decoration:none;">
                    <i class="bi-shop me-1"></i>Tous les services
                </a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════════════
   KEYFRAMES
   ═══════════════════════════════════════════════════════════════ */
@keyframes showFadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: none; }
}
@keyframes showFadeDown {
    from { opacity: 0; transform: translateY(-12px); }
    to   { opacity: 1; transform: none; }
}
@keyframes showScaleIn {
    from { opacity: 0; transform: scale(0.92); }
    to   { opacity: 1; transform: scale(1); }
}
@keyframes showSlideRight {
    from { opacity: 0; transform: translateX(-20px); }
    to   { opacity: 1; transform: none; }
}
@keyframes showShimmer {
    0%   { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
@keyframes showPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,121,0,0.3); }
    50%      { box-shadow: 0 0 0 8px rgba(255,121,0,0); }
}

/* ═══════════════════════════════════════════════════════════════
   SCROLL ANIMATIONS
   ═══════════════════════════════════════════════════════════════ */
.anim-show {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.55s cubic-bezier(0.16, 0.6, 0.2, 1),
                transform 0.55s cubic-bezier(0.16, 0.6, 0.2, 1);
}
.show-visible {
    opacity: 1 !important;
    transform: none !important;
}

/* Hero entrance — keyframe based (plays once on mount) */
.show-anim-hero .show-category-badge {
    animation: showFadeDown 0.4s cubic-bezier(0.16, 0.6, 0.2, 1) both;
}
.show-anim-hero .show-service-title {
    animation: showFadeUp 0.5s cubic-bezier(0.16, 0.6, 0.2, 1) 0.1s both;
}
.show-anim-hero .show-quick-info {
    animation: showFadeUp 0.5s cubic-bezier(0.16, 0.6, 0.2, 1) 0.2s both;
}
.show-anim-hero .show-quick-info:last-child {
    animation-delay: 0.28s;
}

/* ═══════════════════════════════════════════════════════════════
   NAVBAR
   ═══════════════════════════════════════════════════════════════ */
.show-navbar {
    background: #ffffff;
    height: 56px;
    border-bottom: 3px solid #FF7900;
    display: flex; align-items: center;
    position: sticky; top: 0; z-index: 100;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.show-brand { display: flex; align-items: center; gap: 10px; }
.show-brand-icon {
    width: 32px; height: 32px; background: #FF7900;
    border-radius: 2px; display: flex; align-items: center;
    justify-content: center; font-size: 12px; font-weight: 900; color: #fff;
}
.show-brand-name { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 16px; color: #1E293B; }
.show-nav-link {
    color: #64748B; font-size: 13px; font-weight: 500;
    text-decoration: none; padding: 6px 12px; border-radius: 2px;
    transition: all 0.2s;
}
.show-nav-link:hover { color: #1E293B; background: #F1F5F9; }
.show-btn-primary {
    background: #FF7900; color: #fff; border: none; border-radius: 2px;
    padding: 7px 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}
.show-btn-primary:hover { background: #e06700; color: #fff; transform: translateY(-1px); }
.show-btn-outline {
    background: transparent;
    color: #64748B;
    border: 1px solid #CBD5E1;
    border-radius: 2px; padding: 6px 14px;
    font-size: 13px; font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}
.show-btn-outline:hover {
    background: #F1F5F9; color: #1E293B;
    border-color: #94A3B8;
}

/* ═══════════════════════════════════════════════════════════════
   SUB-BAR
   ═══════════════════════════════════════════════════════════════ */
.show-subbar {
    background: #FF7900; height: 32px;
    display: flex; align-items: center;
}
.show-subnav-link {
    color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 500;
    text-decoration: none; transition: color 0.15s;
}
.show-subnav-link:hover, .show-subnav-link.active { color: #fff; }

/* ═══════════════════════════════════════════════════════════════
   HERO BANNER
   ═══════════════════════════════════════════════════════════════ */
.show-hero {
    background: linear-gradient(135deg, #0A1628 0%, #1E293B 25%, #0F172A 50%, #1E293B 75%, #0A1628 100%);
    background-size: 300% 300%;
    animation: showGradientMove 12s ease infinite;
    padding: 36px 0 32px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
    position: relative;
}
@keyframes showGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.show-hero::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,121,0,0.12) 0%, transparent 70%); border-radius: 50%; animation: gelFloatA 8s ease-in-out infinite; }
.show-hero::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,121,0,0.08) 0%, transparent 70%); border-radius: 50%; animation: gelFloatB 10s ease-in-out infinite; }
@keyframes gelFloatA { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,20px) scale(1.05); } 66% { transform: translate(20px,-10px) scale(0.95); } }
@keyframes gelFloatB { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.08); } 66% { transform: translate(-20px,10px) scale(0.92); } }
.show-category-badge {
    width: 36px; height: 36px;
    background: rgba(255,121,0,0.2);
    border: 1px solid rgba(255,121,0,0.4);
    border-radius: 2px; display: flex; align-items: center;
    justify-content: center; font-size: 18px; color: #FF7900; flex-shrink: 0;
}
.show-category-name {
    font-size: 13px; font-weight: 700; color: #FF7900;
    text-transform: uppercase; letter-spacing: 0.06em;
}
.show-service-title {
    font-family: 'Outfit', sans-serif;
    font-size: 2rem; font-weight: 800; color: #fff; margin: 0;
}
.show-quick-info {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 2px; padding: 6px 14px;
    font-size: 13px; color: rgba(255,255,255,0.85);
}

/* ═══════════════════════════════════════════════════════════════
   CARDS
   ═══════════════════════════════════════════════════════════════ */
.show-card {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 4px;
    overflow: hidden;
    transition: box-shadow 0.35s cubic-bezier(0.16, 0.6, 0.2, 1),
                transform 0.35s cubic-bezier(0.16, 0.6, 0.2, 1),
                border-color 0.3s;
}
.show-card:hover {
    box-shadow: 0 8px 28px rgba(0,0,0,0.07);
    border-color: rgba(255,121,0,0.15);
}
.show-card-header {
    padding: 12px 18px;
    font-size: 13px; font-weight: 700; color: #1E293B;
    display: flex; align-items: center;
    background: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
}
.show-card-body { padding: 18px; }
.show-description {
    font-size: 14px; color: #475569;
    line-height: 1.8; white-space: pre-line;
}

/* ═══════════════════════════════════════════════════════════════
   CHECKLIST
   ═══════════════════════════════════════════════════════════════ */
.show-checklist { list-style: none; padding: 0; margin: 0; }
.show-check-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #F1F5F9;
    font-size: 13px; color: #475569;
    animation: showFadeUp 0.4s cubic-bezier(0.16, 0.6, 0.2, 1) both;
}
.show-check-item:nth-child(1) { animation-delay: 0.05s; }
.show-check-item:nth-child(2) { animation-delay: 0.10s; }
.show-check-item:nth-child(3) { animation-delay: 0.15s; }
.show-check-item:nth-child(4) { animation-delay: 0.20s; }
.show-check-item:nth-child(5) { animation-delay: 0.25s; }
.show-check-item:nth-child(6) { animation-delay: 0.30s; }
.show-check-item:nth-child(7) { animation-delay: 0.35s; }
.show-check-item:nth-child(8) { animation-delay: 0.40s; }
.show-check-item:last-child { border-bottom: none; }
.show-check-dot {
    width: 22px; height: 22px; border-radius: 2px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}
.show-check-ok { background: #F0FDF4; color: #16A34A; }
.show-check-doc { background: rgba(255,121,0,0.08); color: #FF7900; }

/* ═══════════════════════════════════════════════════════════════
   ORDER BOX
   ═══════════════════════════════════════════════════════════════ */
.show-order-card {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 16px;
    transition: box-shadow 0.35s cubic-bezier(0.16, 0.6, 0.2, 1),
                transform 0.35s cubic-bezier(0.16, 0.6, 0.2, 1);
}
.show-order-card:hover {
    box-shadow: 0 12px 36px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.show-order-header {
    background: #1E293B; color: #fff;
    padding: 14px 18px;
    font-size: 14px; font-weight: 700;
    font-family: 'Outfit', sans-serif;
    display: flex; align-items: center;
}
.show-order-body { padding: 20px; }
.show-order-row {
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}
.show-order-row:last-child { border-bottom: none; }
.show-order-label {
    font-size: 11px; font-weight: 600; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.05em;
    margin-bottom: 4px; display: flex; align-items: center;
}
.show-order-value { font-size: 14px; font-weight: 600; color: #1a1a1a; }
.show-tarif { font-size: 22px; font-weight: 800; color: #FF7900; font-family: 'Outfit', sans-serif; }
.show-cta-btn {
    width: 100%; background: #FF7900; color: #fff;
    border: none; border-radius: 2px; padding: 14px;
    font-size: 14px; font-weight: 700; cursor: pointer;
    font-family: 'Outfit', sans-serif;
    transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
}
.show-cta-btn:hover:not(:disabled) {
    background: #e06700;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(255,121,0,0.3);
}
.show-cta-btn:active:not(:disabled) {
    transform: translateY(0);
}
.show-cta-btn:disabled { opacity: 0.7; cursor: not-allowed; }

/* ═══════════════════════════════════════════════════════════════
   BACK LINK
   ═══════════════════════════════════════════════════════════════ */
.show-back-link {
    display: flex; align-items: center;
    color: #3B82F6; font-size: 13px; font-weight: 600;
    text-decoration: none; padding: 12px 18px;
    background: #fff; border: 1px solid #E2E8F0;
    border-radius: 4px;
    transition: all 0.25s cubic-bezier(0.16, 0.6, 0.2, 1);
}
.show-back-link:hover {
    background: #EFF6FF;
    border-color: #3B82F6;
    color: #2563EB;
    transform: translateX(-4px);
}

/* ═══════════════════════════════════════════════════════════════
   FOOTER
   ═══════════════════════════════════════════════════════════════ */
.show-footer {
    background: #1a1a1a;
    border-top: 3px solid #FF7900;
    padding: 18px 0;
}
</style>
