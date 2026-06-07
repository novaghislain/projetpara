<script setup>
import { ref, computed } from 'vue';
import { CalendarClock, CheckCircle2, ShoppingCart, ArrowLeft, Phone, User, MapPin, FileText, Package } from 'lucide-vue-next';

const props = defineProps({
    product: { type: Object, required: true },
    user: { type: Object, default: null }
});

const step = ref(1);
const submitting = ref(false);
const errorMsg = ref('');
const success = ref(null);

const formData = ref({
    product_id: props.product.id,
    customer_firstName: props.user?.name?.split(' ').slice(1).join(' ') || '',
    customer_lastName: props.user?.name?.split(' ')[0] || '',
    customer_phone: '',
    customer_email: props.user?.email || '',
    customer_city: 'Cotonou',
    customer_address: '',
    reservation_date: '',
    reservation_time: '',
    notes: '',
    quantity: 1,
});

const formatPrice = (p) => Number(p).toLocaleString() + ' FCFA';
const formValid = computed(() => formData.value.customer_lastName && formData.value.customer_firstName && formData.value.customer_phone && formData.value.reservation_date);

const minDate = new Date().toISOString().split('T')[0];

const timeSlots = [
    '08:00 - 09:00', '09:00 - 10:00', '10:00 - 11:00',
    '11:00 - 12:00', '14:00 - 15:00', '15:00 - 16:00',
    '16:00 - 17:00', '17:00 - 18:00'
];

const handleSubmit = async () => {
    submitting.value = true;
    errorMsg.value = '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const res = await fetch('/api/reservations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData.value)
        });

        if (!res.ok) {
            const err = await res.json();
            throw new Error(err.message || 'Erreur lors de la réservation');
        }

        success.value = await res.json();
        step.value = 2;
    } catch (e) {
        errorMsg.value = e.message || 'Une erreur est survenue. Veuillez réessayer.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <PremiumLayout title="Réservation - Victoire Para">
        <!-- Hero -->
        <section class="position-relative overflow-hidden bg-primary py-4">
            <div class="container position-relative z-1 py-3">
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/" class="text-white text-opacity-75 text-decoration-none small">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="/shop" class="text-white text-opacity-75 text-decoration-none small">Boutique</a></li>
                        <li class="breadcrumb-item active text-white fw-bold small" aria-current="page">Réservation</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold text-white mb-0">Réserver un Produit</h1>
            </div>
        </section>

        <div class="container py-4">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-8">
                    <!-- Step 1: Reservation Form -->
                    <div v-if="step === 1" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-2 p-2">
                                <CalendarClock :size="22" class="text-primary" />
                            </div>
                            <div>
                                <h2 class="h4 fw-bold mb-0">Formulaire de Réservation</h2>
                                <p class="text-muted small mb-0">Réservez ce produit pour le retirer plus tard</p>
                            </div>
                        </div>

                        <!-- Product Summary -->
                        <div class="bg-light rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
                            <img :src="product.img" :alt="product.name"
                                 class="rounded-3 shadow-sm object-fit-cover bg-white"
                                 style="width: 70px; height: 70px;" />
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ product.name }}</h6>
                                <p class="text-primary fw-bold mb-0 small">{{ formatPrice(product.price) }}</p>
                            </div>
                            <div>
                                <Package :size="20" class="text-muted" />
                            </div>
                        </div>

                        <div v-if="errorMsg" class="alert alert-danger rounded-3 small py-2 mb-3">{{ errorMsg }}</div>

                        <div class="row g-3 mb-4">
                            <h6 class="fw-bold small text-muted mb-0">
                                <User :size="14" class="me-1" /> Vos informations
                            </h6>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nom</label>
                                <input v-model="formData.customer_lastName" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="Votre nom" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Prénom</label>
                                <input v-model="formData.customer_firstName" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="Votre prénom" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Téléphone</label>
                                <input v-model="formData.customer_phone" type="tel" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="+229 XX XX XX XX" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email</label>
                                <input v-model="formData.customer_email" type="email" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="votre@email.com" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Ville</label>
                                <select v-model="formData.customer_city" class="form-select bg-light border-0 rounded-3 px-4 py-2">
                                    <option>Cotonou</option>
                                    <option>Abomey-Calavi</option>
                                    <option>Porto-Novo</option>
                                    <option>Parakou</option>
                                    <option>Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Adresse</label>
                                <input v-model="formData.customer_address" class="form-control bg-light border-0 rounded-3 px-4 py-2" placeholder="Quartier, rue..." />
                            </div>
                        </div>

                        <hr />

                        <div class="row g-3 mb-4">
                            <h6 class="fw-bold small text-muted mb-0">
                                <CalendarClock :size="14" class="me-1" /> Date et Heure de réservation
                            </h6>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Date souhaitée</label>
                                <input v-model="formData.reservation_date" type="date" :min="minDate"
                                       class="form-control bg-light border-0 rounded-3 px-4 py-2" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Créneau horaire</label>
                                <select v-model="formData.reservation_time" class="form-select bg-light border-0 rounded-3 px-4 py-2">
                                    <option value="">Sélectionnez un créneau</option>
                                    <option v-for="slot in timeSlots" :key="slot" :value="slot">{{ slot }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Quantité</label>
                                <input v-model.number="formData.quantity" type="number" min="1" max="99"
                                       class="form-control bg-light border-0 rounded-3 px-4 py-2" />
                            </div>
                        </div>

                        <hr />

                        <div class="mb-4">
                            <h6 class="fw-bold small text-muted mb-2">
                                <FileText :size="14" class="me-1" /> Notes supplémentaires
                            </h6>
                            <textarea v-model="formData.notes" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                                      rows="3" placeholder="Informations complémentaires..."></textarea>
                        </div>

                        <button @click="handleSubmit" :disabled="!formValid || submitting"
                                class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <div v-if="submitting" class="spinner-border spinner-border-sm"></div>
                            <span v-else><CalendarClock :size="18" /> Confirmer la Réservation</span>
                        </button>

                        <a href="/shop" class="btn btn-link text-muted mt-2 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                            <ArrowLeft :size="14" /> Retour à la boutique
                        </a>
                    </div>

                    <!-- Step 2: Success -->
                    <div v-else class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                        <div class="d-inline-flex bg-success bg-opacity-10 rounded-circle p-4 mb-4">
                            <CheckCircle2 :size="64" class="text-success" />
                        </div>
                        <h2 class="fw-bold mb-2">Réservation Confirmée !</h2>
                        <p class="text-muted mb-1">Votre réservation pour <strong>{{ product.name }}</strong> a été enregistrée.</p>
                        <p class="text-muted small mb-1">Date : <strong>{{ formData.reservation_date }}</strong></p>
                        <p class="text-muted small mb-1">Quantité : <strong>{{ formData.quantity }}</strong></p>
                        <p class="text-muted small mb-4">Un agent vous contactera pour confirmer le rendez-vous.</p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="/" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">Retour à l'accueil</a>
                            <a href="/shop" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">Continuer mes achats</a>
                        </div>
                    </div>
                </div>

                <!-- Info Sidebar -->
                <div v-if="step === 1" class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="position: sticky; top: 100px;">
                        <h5 class="fw-bold mb-3">
                            <CalendarClock :size="18" class="text-primary me-1" /> Infos utiles
                        </h5>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <CheckCircle2 :size="14" class="text-success mt-1 flex-shrink-0" />
                                <span>Réservez votre produit et retirez-le en magasin à la date choisie</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <CheckCircle2 :size="14" class="text-success mt-1 flex-shrink-0" />
                                <span>Un acompte peut être demandé pour les produits de grande valeur</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start gap-2">
                                <CheckCircle2 :size="14" class="text-success mt-1 flex-shrink-0" />
                                <span>Annulation gratuite jusqu'à 24h avant la date de réservation</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <CheckCircle2 :size="14" class="text-success mt-1 flex-shrink-0" />
                                <span>Un agent vous confirmera la disponibilité par téléphone</span>
                            </li>
                        </ul>
                        <hr />
                        <h6 class="fw-bold small mb-2">Produit réservé</h6>
                        <div class="d-flex align-items-center gap-2">
                            <img :src="product.img" class="rounded-2" style="width: 40px; height: 40px; object-fit: cover;" />
                            <div>
                                <small class="fw-bold d-block text-truncate">{{ product.name }}</small>
                                <small class="text-primary fw-bold">{{ formatPrice(product.price) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PremiumLayout>
</template>

<style scoped>
.spinner-border { width: 1.2rem; height: 1.2rem; border-width: 0.15em; }
</style>
