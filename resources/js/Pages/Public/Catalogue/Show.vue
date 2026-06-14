<script setup>
import { ref } from 'vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    service: {
        type: Object,
        required: true,
    }
});

const processing = ref(false);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const startOrder = async () => {
    processing.value = true;

    if (!authStore.isAuthenticated) {
        try {
            await fetch('/commande/preparer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ service_id: props.service.id })
            });
        } catch (e) {
            console.error(e);
        }
        window.location.href = '/login';
        return;
    }

    try {
        const res = await fetch('/commande/initialiser', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ service_id: props.service.id })
        });

        if (res.ok || res.redirected) {
            window.location.href = res.url || '/commande/etape';
        }
    } catch (e) {
        console.error(e);
        processing.value = false;
    }
};
</script>

<template>
    <div style="font-family: 'Inter', sans-serif; background: #f4f5f7; min-height: 100vh;">

        <!-- Navbar iSupplier -->
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
                    <a v-if="authStore.isAuthenticated" href="/dashboard" class="show-btn-primary">
                        <i class="bi-speedometer2 me-1"></i>Mon Espace
                    </a>
                    <a v-else href="/login" class="show-btn-outline">
                        <i class="bi-box-arrow-in-right me-1"></i>Connexion
                    </a>
                </div>
            </div>
        </nav>

        <!-- Orange sub-bar + breadcrumb -->
        <div class="show-subbar">
            <div class="container d-flex align-items-center gap-2" style="font-size: 12px;">
                <a href="/" class="show-subnav-link">Accueil</a>
                <span style="color:rgba(255,255,255,0.4);">/</span>
                <a href="/nos-services" class="show-subnav-link">Catalogue</a>
                <span style="color:rgba(255,255,255,0.4);">/</span>
                <span class="show-subnav-link active">{{ service.nom }}</span>
            </div>
        </div>

        <!-- Service Header Banner -->
        <div class="show-hero">
            <div class="container">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="show-category-badge" v-html="category.icone"></div>
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

        <!-- Main Content -->
        <div class="container py-5">
            <div class="row g-4">

                <!-- Left: Service Info -->
                <div class="col-lg-8">

                    <!-- Description card -->
                    <div class="show-card mb-4">
                        <div class="show-card-header">
                            <i class="bi-info-circle me-2" style="color: #FF7900;"></i>
                            Description du service
                        </div>
                        <div class="show-card-body">
                            <div class="show-description">{{ service.description }}</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Ce qui est inclus -->
                        <div v-if="service.inclus_json && service.inclus_json.length" class="col-md-6">
                            <div class="show-card h-100">
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

                        <!-- Documents requis -->
                        <div v-if="service.documents_requis_json && service.documents_requis_json.length" class="col-md-6">
                            <div class="show-card h-100">
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

                <!-- Right: Order Box -->
                <div class="col-lg-4">
                    <div class="position-sticky" style="top: 100px;">
                        <div class="show-order-card">
                            <!-- Card header -->
                            <div class="show-order-header">
                                <i class="bi-cart-plus me-2"></i>Commander ce service
                            </div>
                            <div class="show-order-body">
                                <!-- Délai -->
                                <div class="show-order-row">
                                    <div class="show-order-label">
                                        <i class="bi-clock-history me-2" style="color: #FF7900;"></i>
                                        Délai de réalisation
                                    </div>
                                    <div class="show-order-value">{{ service.delai_jours || 'Sur mesure' }}</div>
                                </div>

                                <!-- Tarif -->
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

                                <!-- Catégorie -->
                                <div class="show-order-row">
                                    <div class="show-order-label">
                                        <i class="bi-folder me-2" style="color: #FF7900;"></i>
                                        Catégorie
                                    </div>
                                    <div class="show-order-value">{{ category.nom }}</div>
                                </div>

                                <!-- CTA Button -->
                                <button
                                    @click="startOrder"
                                    :disabled="processing"
                                    class="show-cta-btn mt-4"
                                >
                                    <span v-if="processing">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Chargement...
                                    </span>
                                    <span v-else>
                                        <i class="bi-send me-2"></i>Faire une demande
                                    </span>
                                </button>
                                <p class="text-center text-muted mt-3 mb-0" style="font-size: 12px;">
                                    <i class="bi-shield-check me-1 text-success"></i>
                                    Sans engagement immédiat.
                                </p>
                            </div>
                        </div>

                        <!-- Back link -->
                        <a href="/nos-services" class="show-back-link">
                            <i class="bi-arrow-left me-2"></i>Retour au catalogue
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
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
/* ── Navbar ─────────────────────────────────────────── */
.show-navbar {
    background: #163A5E; height: 56px;
    border-bottom: 3px solid #FF7900;
    display: flex; align-items: center;
    position: sticky; top: 0; z-index: 100;
}
.show-brand { display: flex; align-items: center; gap: 10px; }
.show-brand-icon {
    width: 32px; height: 32px; background: #FF7900;
    border-radius: 2px; display: flex; align-items: center;
    justify-content: center; font-size: 12px; font-weight: 900; color: #fff;
}
.show-brand-name { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 16px; color: #fff; }
.show-nav-link {
    color: rgba(255,255,255,0.75); font-size: 13px; font-weight: 500;
    text-decoration: none; padding: 6px 12px; border-radius: 2px; transition: all 0.15s;
}
.show-nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
.show-btn-primary {
    background: #FF7900; color: #fff; border: none; border-radius: 2px;
    padding: 7px 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: background 0.15s;
}
.show-btn-primary:hover { background: #e06700; color: #fff; }
.show-btn-outline {
    background: transparent; color: rgba(255,255,255,0.85);
    border: 1px solid rgba(255,255,255,0.35); border-radius: 2px;
    padding: 6px 14px; font-size: 13px; font-weight: 500;
    text-decoration: none; transition: all 0.15s;
}
.show-btn-outline:hover { background: rgba(255,255,255,0.1); color: #fff; }

/* ── Sub-bar ─────────────────────────────────────────── */
.show-subbar {
    background: #FF7900; height: 32px;
    display: flex; align-items: center;
}
.show-subnav-link {
    color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 500;
    text-decoration: none; transition: color 0.15s;
}
.show-subnav-link:hover, .show-subnav-link.active { color: #fff; }

/* ── Hero Banner ─────────────────────────────────────── */
.show-hero {
    background: linear-gradient(135deg, #163A5E 0%, #1e4d7a 100%);
    padding: 36px 0 32px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.show-category-badge {
    width: 36px; height: 36px; background: rgba(255,121,0,0.2);
    border: 1px solid rgba(255,121,0,0.4); border-radius: 2px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #FF7900; flex-shrink: 0;
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
    background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
    border-radius: 2px; padding: 6px 14px; font-size: 13px; color: rgba(255,255,255,0.85);
}

/* ── Cards ───────────────────────────────────────────── */
.show-card {
    background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden;
}
.show-card-header {
    background: #f8f8f8; border-bottom: 1px solid #e5e5e5;
    padding: 11px 16px; font-size: 13px; font-weight: 700; color: #333;
    display: flex; align-items: center;
}
.show-card-body { padding: 16px; }
.show-description {
    font-size: 14px; color: #444; line-height: 1.8; white-space: pre-line;
}

/* ── Checklist ───────────────────────────────────────── */
.show-checklist { list-style: none; padding: 0; margin: 0; }
.show-check-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 7px 0; border-bottom: 1px solid #f5f5f5; font-size: 13px; color: #444;
}
.show-check-item:last-child { border-bottom: none; }
.show-check-dot {
    width: 22px; height: 22px; border-radius: 2px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.show-check-ok { background: #e8f5e9; color: #198754; }
.show-check-doc { background: rgba(255,121,0,0.1); color: #FF7900; }

/* ── Order Box ───────────────────────────────────────── */
.show-order-card {
    background: #fff; border: 1px solid #e5e5e5; border-radius: 2px;
    overflow: hidden; margin-bottom: 16px;
}
.show-order-header {
    background: #163A5E; color: #fff;
    padding: 14px 16px; font-size: 14px; font-weight: 700;
    font-family: 'Outfit', sans-serif;
    display: flex; align-items: center;
}
.show-order-body { padding: 20px; }
.show-order-row {
    padding: 12px 0; border-bottom: 1px solid #f0f0f0;
}
.show-order-row:last-child { border-bottom: none; }
.show-order-label {
    font-size: 11px; font-weight: 600; color: #999;
    text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;
    display: flex; align-items: center;
}
.show-order-value { font-size: 14px; font-weight: 600; color: #1a1a1a; }
.show-tarif { font-size: 22px; font-weight: 800; color: #FF7900; font-family: 'Outfit', sans-serif; }
.show-cta-btn {
    width: 100%; background: #FF7900; color: #fff;
    border: none; border-radius: 2px; padding: 14px;
    font-size: 14px; font-weight: 700; cursor: pointer;
    transition: background 0.15s; font-family: 'Outfit', sans-serif;
}
.show-cta-btn:hover:not(:disabled) { background: #e06700; }
.show-cta-btn:disabled { opacity: 0.7; cursor: not-allowed; }

/* ── Back link ───────────────────────────────────────── */
.show-back-link {
    display: flex; align-items: center;
    color: #163A5E; font-size: 13px; font-weight: 600;
    text-decoration: none; padding: 12px 16px;
    background: #fff; border: 1px solid #e5e5e5;
    border-radius: 2px; transition: all 0.15s;
}
.show-back-link:hover { background: #eef5fb; color: #163A5E; }

/* ── Footer ─────────────────────────────────────────── */
.show-footer {
    background: #1a1a1a; border-top: 3px solid #FF7900; padding: 18px 0;
}
</style>
