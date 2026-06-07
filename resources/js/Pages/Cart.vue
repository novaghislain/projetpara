<script setup>
import { onMounted, computed } from 'vue';
import { ShoppingCart, Trash2, Plus, Minus, ArrowLeft, CreditCard, Truck, ShieldCheck } from 'lucide-vue-next';
import { cartStore, fetchCart, updateCartItem, removeFromCart } from '../stores/cart';

onMounted(() => {
    fetchCart();
});

const formatPrice = (p) => Number(p).toLocaleString() + ' FCFA';

const cartItems = computed(() => cartStore.items);
const isEmpty = computed(() => cartItems.value.length === 0);

const increaseQty = async (item) => {
    await updateCartItem(item.id, item.quantity + 1);
};

const decreaseQty = async (item) => {
    if (item.quantity > 1) {
        await updateCartItem(item.id, item.quantity - 1);
    }
};

const removeItem = async (id) => {
    await removeFromCart(id);
};
</script>

<template>
    <PremiumLayout title="Panier - Victoire Para">
        <!-- Hero -->
        <section class="position-relative overflow-hidden bg-primary py-4">
            <div class="container position-relative z-1 py-3">
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/" class="text-white text-opacity-75 text-decoration-none small">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="/shop" class="text-white text-opacity-75 text-decoration-none small">Boutique</a></li>
                        <li class="breadcrumb-item active text-white fw-bold small" aria-current="page">Panier</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold text-white mb-0">Mon Panier</h1>
            </div>
        </section>

        <div class="container py-4">
            <!-- Loading -->
            <div v-if="cartStore.isLoading" class="text-center py-5 mt-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3 text-muted">Chargement du panier...</p>
            </div>

            <!-- Empty Cart -->
            <div v-else-if="isEmpty" class="text-center py-5 mt-4 bg-white rounded-4 shadow-sm p-5">
                <div class="d-inline-flex bg-light rounded-circle p-4 mb-4">
                    <ShoppingCart :size="64" class="text-muted opacity-50" />
                </div>
                <h3 class="fw-bold">Votre panier est vide</h3>
                <p class="text-muted mb-4">Ajoutez des produits depuis notre boutique.</p>
                <a href="/shop" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">Aller à la boutique</a>
            </div>

            <!-- Cart Content -->
            <div v-else class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                                <ShoppingCart :size="20" class="me-2 text-primary" />
                                {{ cartStore.count }} article{{ cartStore.count > 1 ? 's' : '' }}
                            </h5>
                        </div>

                        <div v-for="(item, index) in cartItems" :key="item.id"
                             class="d-flex align-items-center gap-3 py-3"
                             :class="{ 'border-top border-light': index > 0 }">
                            <!-- Image -->
                            <img :src="item.img" :alt="item.name"
                                 class="rounded-3 shadow-sm object-fit-cover bg-light"
                                 style="width: 80px; height: 80px;" />

                            <!-- Details -->
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold mb-1 text-truncate">{{ item.name }}</h6>
                                <p class="text-primary fw-bold mb-0 small">{{ formatPrice(item.price) }}</p>
                            </div>

                            <!-- Quantity -->
                            <div class="d-flex align-items-center bg-light rounded-pill border">
                                <button @click="decreaseQty(item)"
                                        class="btn btn-link text-dark p-1 px-2 rounded-circle text-decoration-none">
                                    <Minus :size="14" />
                                </button>
                                <span class="px-2 fw-bold small">{{ item.quantity }}</span>
                                <button @click="increaseQty(item)"
                                        class="btn btn-link text-dark p-1 px-2 rounded-circle text-decoration-none">
                                    <Plus :size="14" />
                                </button>
                            </div>

                            <!-- Subtotal -->
                            <div class="text-end" style="min-width: 100px;">
                                <p class="fw-bold text-primary mb-0">{{ formatPrice(item.price * item.quantity) }}</p>
                            </div>

                            <!-- Remove -->
                            <button @click="removeItem(item.id)"
                                    class="btn btn-link text-danger p-1 text-decoration-none"
                                    title="Supprimer">
                                <Trash2 :size="18" />
                            </button>
                        </div>
                    </div>

                    <a href="/shop" class="btn btn-link text-primary text-decoration-none mt-3 d-inline-flex align-items-center gap-2">
                        <ArrowLeft :size="16" /> Continuer mes achats
                    </a>
                </div>

                <!-- Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="position: sticky; top: 100px;">
                        <h5 class="fw-bold mb-4">Récapitulatif</h5>

                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Sous-total ({{ cartStore.count }} article(s))</small>
                            <small class="fw-bold">{{ formatPrice(cartStore.total) }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Livraison</small>
                            <small class="text-success fw-semibold">Gratuite</small>
                        </div>
                        <hr />
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold text-primary fs-5">{{ formatPrice(cartStore.total) }}</span>
                        </div>

                        <a href="/checkout"
                           class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <CreditCard :size="18" /> Commander
                        </a>

                        <div class="text-center mt-3 pt-3 border-top border-light">
                            <Truck :size="16" class="text-muted" />
                            <small class="text-muted ms-1">Livraison gratuite partout au Bénin</small>
                        </div>
                        <div class="text-center mt-2">
                            <ShieldCheck :size="16" class="text-muted" />
                            <small class="text-muted ms-1">Paiement sécurisé</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PremiumLayout>
</template>
