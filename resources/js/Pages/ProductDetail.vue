<script setup>
import { ref } from 'vue';
import { ShoppingCart, Star, ShieldCheck, Leaf, Sparkles, Check, ArrowLeft, CalendarClock } from 'lucide-vue-next';
import { addToCart } from '../stores/cart';

const props = defineProps({
    product: { type: Object, required: true }
});

const quantity = ref(1);
const notif = ref('');

const showNotif = (msg) => {
    notif.value = msg;
    setTimeout(() => { notif.value = ''; }, 2500);
};

const handleAddToCart = async () => {
    const ok = await addToCart(props.product.id, quantity.value);
    if (ok) {
        showNotif('✓ Ajouté au panier !');
    } else {
        showNotif('✗ Erreur lors de l\'ajout');
    }
};

// Gallery support - fallback if product doesn't have images array
const images = props.product.images || [props.product.img];
</script>

<template>
    <PremiumLayout :title="product.name + ' - Good Régime'">
        <PageHero 
            :title="product.name" 
            :subtitle="product.category"
            image="/images/heroes/product.png"
        />

        <div class="container py-5">
            <nav class="mb-4">
                <a href="/shop" class="text-muted text-decoration-none d-flex align-items-center gap-2 hover-text-primary transition-all small">
                    <ArrowLeft :size="16" />
                    Retour à la boutique
                </a>
            </nav>

            <div class="row g-5">
                <!-- Images Gallery -->
                <div class="col-lg-6">
                    <div>
                        <img :src="images[0]" :alt="product.name" class="img-fluid w-100 object-fit-cover shadow-sm" style="aspect-ratio: 1/1" />
                    </div>
                    <div v-if="images.length > 1" class="row g-3">
                        <div v-for="(img, i) in images" :key="i" class="col-3">
                            <div class="card border-2 rounded-4 overflow-hidden shadow-sm cursor-pointer border-light hover-border-primary transition-all">
                                <img :src="img" class="img-fluid" alt="Gallery" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-lg-6">
                    <div>
                        <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3 d-inline-flex align-items-center gap-2">
                            <Sparkles :size="14" />
                            <span>{{ product.category }}</span>
                        </div>
                        <h1 class="display-4 fw-bold mb-3">{{ product.name }}</h1>
                        
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="d-flex text-warning">
                                <Star v-for="i in 5" :key="i" :size="16" :fill="i <= product.rating ? 'currentColor' : 'none'" />
                            </div>
                            <span class="text-muted small">(45 avis vérifiés au Bénin)</span>
                        </div>

                        <div class="h2 fw-bold text-primary mb-4">
                            {{ product.price.toLocaleString() }} FCFA
                        </div>

                        <p class="lead text-muted mb-5" style="line-height: 1.8">
                            {{ product.description || 'Une solution naturelle de haute qualité pour votre bien-être quotidien. Formulé avec soin pour garantir une efficacité maximale et une tolérance parfaite.' }}
                        </p>

                        <div class="bg-light p-4 rounded-4 mb-5 border border-success border-opacity-10">
                            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <Leaf :size="18" class="text-primary" />
                                Bienfaits & Caractéristiques
                            </h6>
                            <ul class="row g-3 list-unstyled mb-0">
                                <li v-for="feature in (product.features || product.benefits || ['Hydratation intense', 'Éclat naturel', 'Bio & Éthique', 'Texture légère'])" :key="feature" class="col-6 small d-flex align-items-center gap-2 fw-semibold text-dark">
                                    <Check :size="16" class="text-primary" />
                                    {{ feature }}
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="d-flex align-items-center bg-light rounded-pill p-1 border">
                                <button @click="quantity > 1 && quantity--" class="btn btn-link text-dark p-2 rounded-circle hover-bg-white transition-all text-decoration-none">-</button>
                                <span class="px-3 fw-bold">{{ quantity }}</span>
                                <button @click="quantity++" class="btn btn-link text-dark p-2 rounded-circle hover-bg-white transition-all text-decoration-none">+</button>
                            </div>
                            <a :href="`/checkout?id=${product.id}`" class="btn btn-primary btn-lg flex-grow-1 px-5 py-3 rounded-pill shadow-lg d-flex align-items-center justify-content-center gap-3 fw-bold transition-all hover-scale">
                                <ShoppingCart :size="20" />
                                Commander
                            </a>
                        </div>
                        <div class="d-flex gap-3 mb-5">
                            <button @click="handleAddToCart"
                                    class="btn btn-outline-primary btn-lg flex-grow-1 px-4 py-3 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2 transition-all">
                                <ShoppingCart :size="18" />
                                Ajouter au panier
                            </button>
                            <a :href="`/reservation/${product.id}`"
                               class="btn btn-outline-success btn-lg flex-grow-1 px-4 py-3 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2 transition-all">
                                <CalendarClock :size="18" />
                                Réserver
                            </a>
                        </div>

                        <div class="row g-4 pt-4 border-top">
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary">
                                        <ShieldCheck :size="24" />
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 small">Sécurité</h6>
                                        <p class="x-small text-muted mb-0" style="font-size: 10px">100% Certifié</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary">
                                        <Leaf :size="24" />
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 small">Naturel</h6>
                                        <p class="x-small text-muted mb-0" style="font-size: 10px">Bio & Éthique</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PremiumLayout>

    <!-- Toast Notification -->
    <div v-if="notif"
         class="position-fixed bottom-0 end-0 m-4 bg-success text-white px-4 py-3 rounded-4 shadow-lg fw-semibold small z-3"
         style="animation: fadeInUp 0.3s ease;">
      {{ notif }}
    </div>
</template>

<style scoped>
.hover-text-primary:hover { color: var(--primary) !important; }
.hover-border-primary:hover { border-color: var(--primary) !important; }
.hover-bg-white:hover { background-color: white !important; }
.hover-scale:hover { transform: translateY(-2px); }
.shadow-premium { box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1); }
</style>
