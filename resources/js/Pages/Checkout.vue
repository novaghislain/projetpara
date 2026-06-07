<script setup>
import { ref, computed, onMounted } from 'vue';
import { ShieldCheck, CreditCard, CheckCircle2, MapPin, ShoppingCart, ArrowLeft, Phone, User, Truck, Copy } from 'lucide-vue-next';
import { cartStore, fetchCart, clearCart } from '../stores/cart';

const props = defineProps({
  productId: { type: [Number, String], default: null }
});

const step = ref(1);
const payMethod = ref('');
const product = ref(null);
const loading = ref(true);
const submitting = ref(false);
const errorMsg = ref('');
const orderResult = ref(null);
const payRef = ref('');
const payConfirmed = ref(false);

const formData = ref({
  lastName: '',
  firstName: '',
  phone: '',
  city: 'Cotonou',
  address: ''
});

// Generate a payment reference
const generateRef = () => 'VP-' + Date.now().toString(36).toUpperCase() + '-' + Math.random().toString(36).substring(2, 6).toUpperCase();

onMounted(async () => {
  const params = new URLSearchParams(window.location.search);
  const id = props.productId || params.get('id');

  if (id) {
    // Direct product checkout
    try {
      const res = await fetch(`/api/products/${id}`);
      if (!res.ok) throw new Error('Produit introuvable');
      product.value = await res.json();
    } catch (e) {
      errorMsg.value = 'Impossible de charger le produit.';
    }
  } else {
    // Cart checkout — load from cart store
    await fetchCart();
  }
  loading.value = false;
});

const formatPrice = (p) => Number(p).toLocaleString() + ' FCFA';

// Items for order summary
const orderItems = computed(() => {
  if (product.value) {
    return [{ ...product.value, quantity: 1 }];
  }
  return cartStore.items;
});

const total = computed(() => {
  if (product.value) {
    return product.value.price;
  }
  return cartStore.total;
});

const itemCount = computed(() => {
  if (product.value) return 1;
  return cartStore.count;
});

const formValid = computed(() => formData.value.firstName && formData.value.lastName && formData.value.phone);

const handleOrder = async () => {
  submitting.value = true;
  errorMsg.value = '';

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // Generate payment reference for MoMo/Moov
  const reference = payMethod.value !== 'cod' ? generateRef() : null;
  payRef.value = reference;

  if (payMethod.value !== 'cod' && !payConfirmed.value) {
    // Show payment reference, wait for user to confirm payment
    step.value = 2.5;
    submitting.value = false;
    return;
  }

  try {
    const res = await fetch('/api/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        customer: formData.value,
        items: orderItems.value.map(item => ({
          id: item.id,
          name: item.name,
          price: item.price,
          quantity: item.quantity || 1
        })),
        total: total.value,
        paymentMethod: payMethod.value,
        paymentReference: payRef.value
      })
    });

    if (!res.ok) {
      const err = await res.json();
      throw new Error(err.message || 'Erreur lors de la commande');
    }

    orderResult.value = await res.json();

    // Clear cart if this was a cart checkout
    if (!product.value) {
      await clearCart();
    }

    step.value = 3;
  } catch (e) {
    errorMsg.value = e.message || 'Une erreur est survenue. Veuillez réessayer.';
  } finally {
    submitting.value = false;
  }
};

const copyRef = () => {
  if (payRef.value) {
    navigator.clipboard.writeText(payRef.value);
  }
};
</script>

<template>
  <PremiumLayout title="Paiement - Victoire Para">
    <!-- Hero -->
    <section class="position-relative overflow-hidden bg-primary py-4">
      <div class="container position-relative z-1 py-3">
        <nav aria-label="breadcrumb" class="mb-2">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/" class="text-white text-opacity-75 text-decoration-none small">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/shop" class="text-white text-opacity-75 text-decoration-none small">Boutique</a></li>
            <li class="breadcrumb-item active text-white fw-bold small" aria-current="page">Paiement</li>
          </ol>
        </nav>
        <h1 class="display-6 fw-bold text-white mb-0">Finaliser la Commande</h1>
      </div>
    </section>

    <div class="container py-4">
      <div v-if="loading" class="text-center py-5 mt-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Préparation de votre commande...</p>
      </div>

      <div v-else-if="errorMsg && !product && cartStore.items.length === 0" class="text-center py-5 mt-4 bg-white rounded-4 shadow-sm p-5">
        <h3 class="fw-bold text-danger">Erreur</h3>
        <p class="text-muted mb-4">{{ errorMsg }}</p>
        <a href="/shop" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Retour à la boutique</a>
      </div>

      <div v-else-if="orderItems.length === 0 && !loading" class="text-center py-5 mt-4 bg-white rounded-4 shadow-sm p-5">
        <div class="d-inline-flex bg-light rounded-circle p-4 mb-4">
          <ShoppingCart :size="64" class="text-muted opacity-50" />
        </div>
        <h3 class="fw-bold">Votre panier est vide</h3>
        <p class="text-muted mb-4">Ajoutez des produits depuis la boutique.</p>
        <a href="/shop" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">Aller à la boutique</a>
      </div>

      <div v-else class="row g-4 justify-content-center">
        <div class="col-lg-8">
          <!-- Steps Indicator -->
          <div class="d-flex justify-content-center mb-5 gap-2">
            <div v-for="(s, idx) in ['Livraison', 'Paiement', 'Confirmation']" :key="idx"
                 class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill small fw-bold shadow-sm transition-all"
                 :class="step >= idx + 1 ? 'bg-primary text-white' : 'bg-light text-muted'">
              <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center fw-bold"
                   style="width: 22px; height: 22px; font-size: 11px;">{{ idx + 1 }}</div>
              {{ s }}
            </div>
          </div>

          <!-- Step 1: Delivery Info -->
          <div v-if="step === 1" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex align-items-center gap-3 mb-4">
              <div class="bg-primary bg-opacity-10 rounded-2 p-2">
                <Truck :size="22" class="text-primary" />
              </div>
              <h2 class="h4 fw-bold mb-0">Informations de livraison</h2>
            </div>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Nom</label>
                <input v-model="formData.lastName" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="Votre nom" />
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Prénom</label>
                <input v-model="formData.firstName" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="Votre prénom" />
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Téléphone (Mobile Money)</label>
                <input v-model="formData.phone" type="tel" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="+229 XX XX XX XX" />
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Ville</label>
                <select v-model="formData.city" class="form-select bg-light border-0 rounded-3 px-4 py-2">
                  <option>Cotonou</option>
                  <option>Abomey-Calavi</option>
                  <option>Porto-Novo</option>
                  <option>Parakou</option>
                  <option>Autre</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold text-muted">Adresse complète</label>
                <input v-model="formData.address" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="Quartier, rue, point de repère..." />
              </div>
            </div>
            <button @click="step = 2" :disabled="!formValid"
                    class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
              Continuer vers le Paiement <CreditCard :size="18" />
            </button>
          </div>

          <!-- Step 2: Payment Method -->
          <div v-else-if="step === 2" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="d-flex align-items-center gap-3 mb-4">
              <div class="bg-primary bg-opacity-10 rounded-2 p-2">
                <CreditCard :size="22" class="text-primary" />
              </div>
              <h2 class="h4 fw-bold mb-0">Mode de Paiement</h2>
            </div>

            <div class="d-grid gap-3 mb-4">
              <div v-for="m in [
                {id: 'momo', label: 'MTN Mobile Money', icon: Phone, desc: 'Paiement mobile rapide et sécurisé'},
                {id: 'moov', label: 'Moov Money', icon: Phone, desc: 'Paiement mobile rapide et sécurisé'},
                {id: 'cod', label: 'Paiement à la livraison', icon: Truck, desc: 'Payez en espèces à la réception'}
              ]" :key="m.id"
                   @click="payMethod = m.id; payConfirmed = false"
                   class="card border-2 p-3 rounded-3 cursor-pointer transition-all d-flex flex-row align-items-center gap-3"
                   :class="payMethod === m.id ? 'border-primary bg-primary bg-opacity-5' : 'border-light'">
                <div :class="'rounded-2 p-2 ' + (payMethod === m.id ? 'bg-primary text-white' : 'bg-light text-muted')">
                  <component :is="m.icon" :size="20" />
                </div>
                <div class="flex-grow-1">
                  <h6 class="fw-bold small mb-0">{{ m.label }}</h6>
                  <small class="text-muted">{{ m.desc }}</small>
                </div>
                <div class="rounded-circle border-2 d-flex align-items-center justify-content-center flex-shrink-0"
                     :class="payMethod === m.id ? 'border-primary bg-primary' : 'border-muted'"
                     style="width:22px; height:22px;">
                  <div v-if="payMethod === m.id" class="bg-white rounded-circle" style="width:8px; height:8px;"></div>
                </div>
              </div>
            </div>

            <div v-if="errorMsg" class="alert alert-danger rounded-3 small py-2 mb-3">{{ errorMsg }}</div>

            <div class="d-flex gap-3">
              <button @click="step = 1" class="btn btn-outline-secondary rounded-pill py-3 px-4 d-flex align-items-center gap-2">
                <ArrowLeft :size="18" /> Retour
              </button>
              <button @click="handleOrder" :disabled="!payMethod || submitting"
                      class="btn btn-primary flex-grow-1 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <div v-if="submitting" class="spinner-border spinner-border-sm"></div>
                <span v-else><ShieldCheck :size="18" /> Confirmer la Commande</span>
              </button>
            </div>
          </div>

          <!-- Step 2.5: Payment Reference (MoMo/Moov) -->
          <div v-else-if="step === 2.5" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
            <div class="d-inline-flex bg-primary bg-opacity-10 rounded-circle p-3 mb-3">
              <Phone :size="32" class="text-primary" />
            </div>
            <h3 class="fw-bold mb-2">Paiement Mobile Money</h3>
            <p class="text-muted mb-1">Effectuez le paiement via <strong>{{ payMethod === 'momo' ? 'MTN Mobile Money' : 'Moov Money' }}</strong></p>
            <p class="text-muted small mb-3">Envoyez le montant au numéro ci-dessous :</p>

            <div class="bg-light rounded-4 p-4 mb-4 d-inline-block mx-auto">
              <p class="text-muted small mb-1">Montant à payer</p>
              <p class="display-6 fw-bold text-primary mb-0">{{ formatPrice(total) }}</p>
            </div>

            <div class="bg-light rounded-4 p-4 mb-3 text-start mx-auto" style="max-width: 400px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Numéro de paiement :</span>
              </div>
              <p class="fw-bold h4 mb-3">+229 01 XX XX XX XX</p>

              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Référence :</span>
                <button @click="copyRef" class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-1">
                  <Copy :size="12" /> Copier
                </button>
              </div>
              <p class="fw-bold h5 text-primary mb-0" style="letter-spacing: 2px;">{{ payRef }}</p>
            </div>

            <p class="small text-muted mb-4">
              Après le paiement, cliquez sur "J'ai payé" pour confirmer votre commande.
            </p>

            <div class="d-flex gap-3 justify-content-center">
              <button @click="step = 2" class="btn btn-outline-secondary rounded-pill py-2 px-4">Modifier</button>
              <button @click="payConfirmed = true; handleOrder()" :disabled="submitting"
                      class="btn btn-success rounded-pill py-2 px-5 fw-bold shadow-sm d-flex align-items-center gap-2">
                <div v-if="submitting" class="spinner-border spinner-border-sm"></div>
                <span v-else><CheckCircle2 :size="18" /> J'ai payé</span>
              </button>
            </div>
          </div>

          <!-- Step 3: Success -->
          <div v-else class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="d-inline-flex bg-success bg-opacity-10 rounded-circle p-4 mb-4">
              <CheckCircle2 :size="64" class="text-success" />
            </div>
            <h2 class="fw-bold mb-2">Commande Confirmée !</h2>
            <p class="text-muted mb-1">Merci pour votre commande.</p>
            <p class="text-muted small mb-1">Référence : <strong class="text-dark">{{ payRef || 'N/A' }}</strong></p>
            <p class="text-muted small mb-4">Un agent va vous contacter dans les plus brefs délais pour confirmer la livraison.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
              <a href="/" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">Retour à l'accueil</a>
              <a href="/shop" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">Continuer mes achats</a>
            </div>
          </div>
        </div>

        <!-- Summary Sidebar -->
        <div v-if="step < 3" class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="position: sticky; top: 100px;">
            <h5 class="fw-bold mb-3">Récapitulatif</h5>
            <div v-for="(item, i) in orderItems" :key="item.id"
                 class="d-flex align-items-center gap-3 mb-3 pb-3"
                 :class="{ 'border-bottom border-light': i < orderItems.length - 1 }">
              <img :src="item.img"
                   class="rounded-3 shadow-sm object-fit-cover bg-light"
                   style="width: 56px; height: 56px; object-fit: cover;" />
              <div class="flex-grow-1 overflow-hidden">
                <h6 class="small fw-bold mb-0 text-truncate">{{ item.name }}</h6>
                <small class="text-muted">Qté : {{ item.quantity || 1 }}</small>
              </div>
              <span class="fw-bold text-primary small">{{ formatPrice(item.price * (item.quantity || 1)) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <small class="text-muted">Sous-total</small>
              <small>{{ formatPrice(total) }}</small>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <small class="text-muted">Livraison</small>
              <small class="text-success fw-semibold">Gratuite</small>
            </div>
            <hr class="my-2" />
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold">Total</span>
              <span class="fw-bold text-primary fs-5">{{ formatPrice(total) }}</span>
            </div>
            <div class="text-center mt-3 pt-3 border-top border-light">
              <ShieldCheck :size="16" class="text-muted" />
              <small class="text-muted ms-1">Paiement sécurisé</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PremiumLayout>
</template>

<style scoped>
.transition-all { transition: all 0.3s ease; }
.bg-opacity-5 { --bs-bg-opacity: 0.05; }
.bg-opacity-10 { --bs-bg-opacity: 0.10; }
.bg-opacity-25 { --bs-bg-opacity: 0.25; }
.cursor-pointer { cursor: pointer; }
.spinner-border { width: 1.2rem; height: 1.2rem; border-width: 0.15em; }

@media (max-width: 576px) {
  .display-6 { font-size: 1.5rem; }
}
</style>
