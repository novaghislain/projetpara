<script setup>
import { ref, computed } from 'vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    }
});

const searchQuery = ref('');

const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) return props.categories;
    const q = searchQuery.value.toLowerCase();
    return props.categories
        .map(cat => ({
            ...cat,
            services: cat.services.filter(s =>
                s.nom?.toLowerCase().includes(q) ||
                s.description?.toLowerCase().includes(q)
            )
        }))
        .filter(cat => cat.services.length > 0);
});

const totalServices = computed(() =>
    props.categories.reduce((sum, cat) => sum + cat.services.length, 0)
);
</script>

<template>
    <div style="font-family: 'Inter', sans-serif; background: #f4f5f7; min-height: 100vh;">

        <!-- Navbar iSupplier -->
        <nav class="cat-navbar">
            <div class="container d-flex align-items-center justify-content-between">
                <a href="/" class="cat-brand text-decoration-none">
                    <div class="cat-brand-icon">GEL</div>
                    <span class="cat-brand-name">GEL Cabinet</span>
                </a>
                <div class="d-flex align-items-center gap-2">
                    <a href="/" class="cat-nav-link">
                        <i class="bi-house me-1"></i>Accueil
                    </a>
                    <a v-if="authStore.isAuthenticated" href="/dashboard" class="cat-btn-primary">
                        <i class="bi-speedometer2 me-1"></i>Mon Espace
                    </a>
                    <a v-else href="/login" class="cat-btn-outline">
                        <i class="bi-box-arrow-in-right me-1"></i>Connexion
                    </a>
                </div>
            </div>
        </nav>

        <!-- Orange sub-bar -->
        <div class="cat-subbar">
            <div class="container d-flex align-items-center gap-3">
                <a href="/" class="cat-subnav-link">Accueil</a>
                <span style="color:rgba(255,255,255,0.4);">/</span>
                <span class="cat-subnav-link active">Catalogue des services</span>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="cat-hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="cat-hero-badge">
                            <i class="bi-shop me-1"></i>Catalogue Officiel GEL Cabinet
                        </div>
                        <h1 class="cat-hero-title">Nos Services</h1>
                        <p class="cat-hero-sub">
                            Découvrez l'ensemble de nos services d'assistance, conseil,
                            création d'entreprise et gestion administrative.
                        </p>
                        <!-- Search bar -->
                        <div class="cat-search-box mt-4">
                            <div class="cat-search-inner">
                                <i class="bi-search cat-search-icon"></i>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="cat-search-input"
                                    placeholder="Rechercher un service..."
                                />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-none d-lg-block text-center">
                        <div class="cat-hero-stat-box">
                            <div class="cat-hero-stat-num">{{ totalServices }}</div>
                            <div class="cat-hero-stat-lbl">Services disponibles</div>
                            <div class="cat-hero-stat-num mt-3">{{ categories.length }}</div>
                            <div class="cat-hero-stat-lbl">Catégories</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalogue Content -->
        <div class="container py-5">

            <!-- Empty state global -->
            <div v-if="!filteredCategories.length" class="text-center py-5">
                <div class="cat-empty-icon">
                    <i class="bi-search" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold mt-3" style="font-size: 18px;">Aucun résultat</h3>
                <p class="text-muted" style="font-size: 13px;">Essayez d'autres mots-clés</p>
                <button @click="searchQuery=''" class="cat-btn-primary">
                    Voir tous les services
                </button>
            </div>

            <!-- Categories -->
            <div v-for="category in filteredCategories" :key="category.id" class="cat-category mb-5">

                <!-- Category Header -->
                <div class="cat-category-header mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cat-category-icon" v-html="category.icone"></div>
                        <div>
                            <h2 class="cat-category-title mb-0">{{ category.nom }}</h2>
                            <p v-if="category.description" class="cat-category-desc mb-0">{{ category.description }}</p>
                        </div>
                        <span class="cat-count-badge ms-auto">{{ category.services.length }} service(s)</span>
                    </div>
                </div>

                <!-- Services Grid -->
                <div class="row g-3">
                    <div v-for="service in category.services" :key="service.id" class="col-md-6 col-lg-4">
                        <div class="cat-service-card h-100">
                            <!-- Service header -->
                            <div class="cat-service-header">
                                <h3 class="cat-service-title">{{ service.nom }}</h3>
                            </div>
                            <!-- Service body -->
                            <div class="cat-service-body">
                                <p class="cat-service-desc">{{ service.description }}</p>

                                <div class="cat-service-meta">
                                    <div class="cat-meta-row">
                                        <span class="cat-meta-label">
                                            <i class="bi-clock me-1"></i>Délai
                                        </span>
                                        <span class="cat-meta-value">{{ service.delai_jours || 'Sur mesure' }}</span>
                                    </div>
                                    <div class="cat-meta-row">
                                        <span class="cat-meta-label">
                                            <i class="bi-tag me-1"></i>Tarif
                                        </span>
                                        <span class="cat-meta-value cat-tarif">
                                            <template v-if="service.tarif_type === 'fixe'">
                                                {{ service.tarif_fcfa?.toLocaleString('fr-FR') }} FCFA
                                            </template>
                                            <template v-else>Sur devis</template>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- Service footer -->
                            <div class="cat-service-footer">
                                <a :href="'/nos-services/' + category.id + '/' + service.id" class="cat-btn-service">
                                    Consulter ce service <i class="bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Category -->
                <div v-if="!category.services.length" class="cat-empty-cat">
                    <i class="bi-inbox me-2"></i>Aucun service disponible dans cette catégorie.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="cat-footer">
            <div class="container d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:22px;height:22px;background:#FF7900;border-radius:2px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:#fff;">GEL</div>
                    <span style="font-size:12px;color:rgba(255,255,255,0.6);">&copy; {{ new Date().getFullYear() }} GEL Cabinet</span>
                </div>
                <a href="/" style="font-size:12px;color:rgba(255,255,255,0.5);text-decoration:none;">
                    <i class="bi-house me-1"></i>Retour à l'accueil
                </a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ── Navbar ─────────────────────────────────────────── */
.cat-navbar {
    background: #163A5E;
    height: 56px;
    border-bottom: 3px solid #FF7900;
    display: flex; align-items: center;
    position: sticky; top: 0; z-index: 100;
}
.cat-brand { display: flex; align-items: center; gap: 10px; }
.cat-brand-icon {
    width: 32px; height: 32px; background: #FF7900;
    border-radius: 2px; display: flex; align-items: center;
    justify-content: center; font-size: 12px; font-weight: 900; color: #fff;
    letter-spacing: -0.5px;
}
.cat-brand-name {
    font-family: 'Outfit', sans-serif;
    font-weight: 700; font-size: 16px; color: #fff;
}
.cat-nav-link {
    color: rgba(255,255,255,0.75); font-size: 13px; font-weight: 500;
    text-decoration: none; padding: 6px 12px; border-radius: 2px;
    transition: all 0.15s;
}
.cat-nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
.cat-btn-primary {
    background: #FF7900; color: #fff; border: none; border-radius: 2px;
    padding: 7px 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: background 0.15s;
}
.cat-btn-primary:hover { background: #e06700; color: #fff; }
.cat-btn-outline {
    background: transparent; color: rgba(255,255,255,0.85);
    border: 1px solid rgba(255,255,255,0.35); border-radius: 2px;
    padding: 6px 14px; font-size: 13px; font-weight: 500;
    text-decoration: none; transition: all 0.15s;
}
.cat-btn-outline:hover { background: rgba(255,255,255,0.1); color: #fff; }

/* ── Sub-bar ─────────────────────────────────────────── */
.cat-subbar {
    background: #FF7900; height: 32px;
    display: flex; align-items: center;
}
.cat-subnav-link {
    color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 600;
    text-decoration: none; text-transform: uppercase;
    letter-spacing: 0.04em; transition: color 0.15s;
}
.cat-subnav-link:hover, .cat-subnav-link.active { color: #fff; }

/* ── Hero ────────────────────────────────────────────── */
.cat-hero {
    background: linear-gradient(135deg, #163A5E 0%, #1e4d7a 100%);
    padding: 48px 0 40px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.cat-hero-badge {
    display: inline-flex; align-items: center;
    background: rgba(255,121,0,0.2); border: 1px solid rgba(255,121,0,0.4);
    color: #FF7900; font-size: 11px; font-weight: 700;
    padding: 4px 12px; border-radius: 2px; margin-bottom: 16px;
    text-transform: uppercase; letter-spacing: 0.06em;
}
.cat-hero-title {
    font-family: 'Outfit', sans-serif;
    font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 10px;
}
.cat-hero-sub {
    font-size: 14px; color: rgba(255,255,255,0.75); max-width: 560px;
}
.cat-search-box { max-width: 480px; }
.cat-search-inner {
    background: #fff; border-radius: 2px;
    display: flex; align-items: center;
    border: 2px solid rgba(255,121,0,0.4);
    overflow: hidden;
}
.cat-search-icon {
    font-size: 15px; color: #999; padding: 0 14px;
}
.cat-search-input {
    border: none; outline: none; width: 100%;
    padding: 11px 14px 11px 0; font-size: 13px; color: #333;
}
.cat-hero-stat-box {
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
    border-radius: 2px; padding: 24px; text-align: center;
}
.cat-hero-stat-num {
    font-size: 32px; font-weight: 800; color: #FF7900;
    font-family: 'Outfit', sans-serif;
}
.cat-hero-stat-lbl { font-size: 12px; color: rgba(255,255,255,0.6); }

/* ── Category ────────────────────────────────────────── */
.cat-category-header {
    background: #fff; border: 1px solid #e5e5e5;
    border-radius: 2px; padding: 16px 20px;
    border-left: 4px solid #FF7900;
}
.cat-category-icon {
    width: 40px; height: 40px;
    background: rgba(255,121,0,0.1); color: #FF7900;
    border-radius: 2px; display: flex; align-items: center;
    justify-content: center; font-size: 20px; flex-shrink: 0;
}
.cat-category-title {
    font-family: 'Outfit', sans-serif;
    font-size: 18px; font-weight: 700; color: #1a1a1a;
}
.cat-category-desc { font-size: 13px; color: #777; }
.cat-count-badge {
    background: #163A5E; color: #fff;
    font-size: 11px; font-weight: 700;
    padding: 3px 10px; border-radius: 2px;
    white-space: nowrap;
}

/* ── Service Card ────────────────────────────────────── */
.cat-service-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 2px;
    display: flex; flex-direction: column;
    transition: box-shadow 0.15s, transform 0.1s, border-color 0.15s;
    overflow: hidden;
}
.cat-service-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    transform: translateY(-2px);
    border-color: rgba(255,121,0,0.35);
}
.cat-service-header {
    background: #f8f8f8; border-bottom: 1px solid #ebebeb;
    padding: 14px 16px;
}
.cat-service-title {
    font-size: 14px; font-weight: 700; color: #163A5E;
    margin: 0; font-family: 'Outfit', sans-serif;
}
.cat-service-body {
    padding: 14px 16px; flex-grow: 1;
}
.cat-service-desc {
    font-size: 12px; color: #666; line-height: 1.6; margin-bottom: 14px;
    display: -webkit-box; -webkit-line-clamp: 4;
    -webkit-box-orient: vertical; overflow: hidden;
}
.cat-service-meta { border-top: 1px solid #f0f0f0; padding-top: 12px; }
.cat-meta-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 5px 0;
}
.cat-meta-label { font-size: 12px; color: #999; }
.cat-meta-value { font-size: 12px; font-weight: 600; color: #333; }
.cat-tarif { color: #FF7900; }
.cat-service-footer {
    padding: 12px 16px;
    border-top: 1px solid #f0f0f0;
}
.cat-btn-service {
    display: block; text-align: center;
    background: #FF7900; color: #fff;
    border: none; border-radius: 2px;
    padding: 9px 16px; font-size: 12px; font-weight: 600;
    text-decoration: none; transition: background 0.15s;
}
.cat-btn-service:hover { background: #e06700; color: #fff; }

/* ── Empty states ────────────────────────────────────── */
.cat-empty-icon {
    width: 72px; height: 72px;
    background: rgba(255,121,0,0.1); color: #FF7900;
    border-radius: 2px; display: flex; align-items: center;
    justify-content: center; margin: 0 auto 16px;
}
.cat-empty-cat {
    background: #f8f8f8; border: 1px dashed #d8d8d8;
    border-radius: 2px; padding: 16px 20px;
    font-size: 13px; color: #999;
}

/* ── Footer ─────────────────────────────────────────── */
.cat-footer {
    background: #1a1a1a; border-top: 3px solid #FF7900;
    padding: 18px 0;
}
</style>
