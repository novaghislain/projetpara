<script setup>
import { ref, onMounted } from 'vue';

const cart = ref({ items: [], count: 0, total: 0 });
const loading = ref(true);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const fetchCart = async () => {
    loading.value = true;
    try {
        const res = await fetch('/api/cart');
        const data = await res.json();
        cart.value = data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const removeItem = async (id) => {
    if (!confirm('Retirer ce service du panier ?')) return;
    try {
        await fetch(`/api/cart/remove/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        await fetchCart();
        // Mettre à jour le badge navbar
        updateNavBadge(cart.value.count);
    } catch (e) {
        console.error(e);
    }
};

const clearCart = async () => {
    if (!confirm('Vider tout le panier ?')) return;
    try {
        await fetch('/api/cart/clear', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        await fetchCart();
        updateNavBadge(0);
    } catch (e) {
        console.error(e);
    }
};

const updateNavBadge = (count) => {
    const badge = document.getElementById('cart-badge-count');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
};

onMounted(fetchCart);
</script>

<template>
    <div style="background: #F8FAFC; min-height: 80vh; padding-top: 80px; padding-bottom: 60px; font-family: 'Inter', sans-serif;">
        <div class="container" style="max-width: 920px;">

            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <h1 style="font-family:'Outfit',sans-serif; font-weight:800; color:#1E293B; font-size:28px; margin:0;">
                        <i class="bi-cart me-2" style="color:#FF7900;"></i>Mon Panier
                    </h1>
                    <p class="text-muted mb-0" style="font-size:14px; margin-top:4px;">
                        {{ cart.count }} service(s) sélectionné(s)
                    </p>
                </div>
                <a href="/nos-services" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
                    <i class="bi-arrow-left me-1"></i> Continuer les achats
                </a>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border" style="color:#FF7900;" role="status"></div>
                <p class="text-muted mt-3">Chargement de votre panier...</p>
            </div>

            <!-- Panier vide -->
            <div v-else-if="cart.count === 0" class="text-center py-5 bg-white rounded-4 shadow-sm border">
                <i class="bi-cart-x" style="font-size:4rem; color:#CBD5E1;"></i>
                <h3 class="mt-3" style="color:#475569; font-family:'Outfit',sans-serif;">Votre panier est vide</h3>
                <p class="text-muted mb-4">Découvrez nos services et ajoutez ceux qui correspondent à vos besoins.</p>
                <a href="/nos-services" class="btn" style="background:#FF7900; color:#fff; font-weight:600; padding:12px 28px; border-radius:10px; text-decoration:none;">
                    <i class="bi-grid me-2"></i>Voir les services
                </a>
            </div>

            <!-- Contenu panier -->
            <div v-else class="row g-4">

                <!-- Liste des services -->
                <div class="col-lg-8">

                    <div v-for="item in cart.items" :key="item.id"
                         class="bg-white rounded-4 shadow-sm border mb-3 p-4 d-flex align-items-start gap-3"
                         style="transition: box-shadow 0.2s;"
                         @mouseenter="$event.currentTarget.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)'"
                         @mouseleave="$event.currentTarget.style.boxShadow=''">

                        <!-- Icône -->
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
                             style="width:52px; height:52px; background:rgba(59,130,246,0.08); color:#3B82F6; font-size:20px; border:1px solid rgba(59,130,246,0.12);">
                            <i :class="item.icone"></i>
                        </div>

                        <!-- Infos -->
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge" style="background:rgba(59,130,246,0.1); color:#3B82F6; font-weight:600; font-size:11px;">
                                    {{ item.category_nom }}
                                </span>
                            </div>
                            <h5 class="mb-1 fw-bold" style="color:#1E293B; font-family:'Outfit',sans-serif; font-size:16px;">
                                {{ item.nom }}
                            </h5>
                            <p class="mb-0 text-muted" style="font-size:13px;">Quantité : {{ item.quantity }}</p>
                        </div>

                        <!-- Prix + Action -->
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold mb-2" style="color:#FF7900; font-size:15px;">
                                <template v-if="item.tarif_type === 'fixe' && item.tarif_fcfa">
                                    {{ (item.tarif_fcfa * item.quantity).toLocaleString('fr-FR') }} FCFA
                                </template>
                                <template v-else>
                                    <span style="color:#64748B;">Sur devis</span>
                                </template>
                            </div>
                            <button class="btn btn-sm" @click="removeItem(item.id)"
                                    style="border:1px solid #fca5a5; color:#dc2626; border-radius:8px; font-size:12px; padding:4px 10px;">
                                <i class="bi-trash me-1"></i> Retirer
                            </button>
                        </div>
                    </div>

                    <!-- Vider le panier -->
                    <div class="text-end mt-2">
                        <button @click="clearCart" class="btn btn-sm text-muted"
                                style="font-size:12px; text-decoration:underline; background:none; border:none;">
                            Vider le panier
                        </button>
                    </div>
                </div>

                <!-- Récapitulatif -->
                <div class="col-lg-4">
                    <div class="bg-white rounded-4 shadow-sm border p-4" style="position:sticky; top:100px;">
                        <h5 class="fw-bold mb-4" style="font-family:'Outfit',sans-serif; color:#1E293B;">Récapitulatif</h5>

                        <div class="d-flex justify-content-between mb-2" style="font-size:14px;">
                            <span class="text-muted">Services ({{ cart.count }})</span>
                            <span class="fw-semibold">
                                {{ cart.total > 0 ? cart.total.toLocaleString('fr-FR') + ' FCFA' : '—' }}
                            </span>
                        </div>
                        <div v-if="cart.items.some(i => i.tarif_type !== 'fixe')" class="mb-3"
                             style="font-size:12px; color:#64748B; background:#F1F5F9; padding:8px 12px; border-radius:8px;">
                            <i class="bi-info-circle me-1"></i>
                            Certains services sont sur devis. Un conseiller vous contactera.
                        </div>

                        <hr style="border-color:#E2E8F0;">

                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold" style="color:#1E293B;">Total immédiat</span>
                            <span class="fw-bold" style="color:#FF7900; font-size:18px;">
                                {{ cart.total > 0 ? cart.total.toLocaleString('fr-FR') + ' FCFA' : 'Sur devis' }}
                            </span>
                        </div>

                        <a href="/commande/etape" class="btn w-100 fw-bold text-white py-3"
                           style="background:#3B82F6; border-radius:10px; font-size:15px; font-family:'Outfit',sans-serif; text-decoration:none; display:block; text-align:center;">
                            Valider la commande <i class="bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
