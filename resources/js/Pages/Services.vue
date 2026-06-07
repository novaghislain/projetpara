<script setup>
import {
  Sparkles, Heart, Activity, Scissors, UserCheck, Zap,
  CheckCircle2, Clock, Star, ShieldCheck, ArrowRight,
  ChevronRight, Sun, Droplets, Leaf, Brain, Phone,
  CalendarCheck, MessageCircle
} from 'lucide-vue-next';
import { ref } from 'vue';

const services = [
  {
    title: "Soin du Visage & Éclat",
    icon: Sparkles,
    price: "15.000 FCFA",
    duration: "45 min",
    description: "Nettoyage en profondeur, hydratation intense et soin éclat pour révéler votre luminosité naturelle.",
    img: "/images/products/serum-vitamine-c.jpg",
    features: ["Nettoyage profond", "Hydratation intense", "Masque éclat", "Massage visage"]
  },
  {
    title: "Gommage Corps Soyeux",
    icon: Scissors,
    price: "20.000 FCFA",
    duration: "60 min",
    description: "Exfoliation douce aux huiles essentielles bio pour une peau satinée et revitalisée.",
    img: "/images/products/huile-naturelle.jpg",
    features: ["Gommage bio", "Huiles essentielles", "Enveloppement", "Hydratation"]
  },
  {
    title: "Consultation Nutrition",
    icon: Heart,
    price: "8.000 FCFA",
    duration: "30 min",
    description: "Accompagnement personnalisé avec notre nutritionniste pour atteindre vos objectifs bien-être.",
    img: "/images/products/detox-wahoo.jpg",
    features: ["Bilan nutritionnel", "Objectifs personnalisés", "Suivi mensuel", "Programme adapté"]
  },
  {
    title: "Soins Bébé & Maman",
    icon: Activity,
    price: "10.000 FCFA",
    duration: "40 min",
    description: "Des soins délicats certifiés bio pour les peaux sensibles des tout-petits et des mamans.",
    img: "/images/products/produit-bebe.jpg",
    features: ["Produits bio", "Peaux sensibles", "Douceur garantie", "Conseils experts"]
  }
];

const extraServices = [
  { title: "Analyse de Peau IA", desc: "Diagnostic cutané par intelligence artificielle", icon: Brain, color: "bg-primary" },
  { title: "Conseils Personnalisés", desc: "Recommandations produits sur mesure", icon: UserCheck, color: "bg-success" },
  { title: "Suivi Client", desc: "Accompagnement continu par nos experts", icon: Phone, color: "bg-warning" },
  { title: "Livraison Express", desc: "Vos produits livrés en 24h à domicile", icon: Zap, color: "bg-info" }
];

const testimonials = [
  { text: "Un soin du visage incroyable ! Ma peau n'a jamais été aussi lumineuse.", name: "Fatima D.", note: "5.0" },
  { text: "La consultation nutrition m'a vraiment aidée à retrouver mon équilibre.", name: "Rachida M.", note: "5.0" },
  { text: "Des produits adaptés à la peau de mon bébé. Je suis totalement rassurée.", name: "Bénédicte K.", note: "4.9" }
];

const success = ref(false);
const loading = ref(false);
const form = ref({ name: '', phone: '', service: 'Soin du Visage & Éclat', date: '', message: '' });
const errors = ref({});

const validate = () => {
  errors.value = {};
  if (!form.value.name.trim()) errors.value.name = 'Nom requis';
  if (!form.value.phone.trim()) errors.value.phone = 'Téléphone requis';
  if (!form.value.date) errors.value.date = 'Date requise';
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  if (!validate()) return;
  loading.value = true;
  setTimeout(() => {
    loading.value = false;
    success.value = true;
    form.value = { name: '', phone: '', service: 'Soin du Visage & Éclat', date: '', message: '' };
  }, 1500);
};
</script>

<template>
  <PremiumLayout title="Services - Victoire Para">
    <!-- Hero Banner -->
    <section class="position-relative overflow-hidden bg-primary py-5">
      <div class="position-absolute top-0 end-0 w-50 h-100 d-none d-lg-block">
        <div class="bg-white opacity-5 rounded-start-5 h-100 w-100" style="border-radius: 0 0 0 40%;"></div>
      </div>
      <div class="container position-relative z-1 py-4">
        <div class="row align-items-center g-4">
          <div class="col-lg-7">
            <nav aria-label="breadcrumb" class="mb-3">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/" class="text-white text-opacity-75 text-decoration-none small">Accueil</a></li>
                <li class="breadcrumb-item active text-white fw-bold small" aria-current="page">Services</li>
              </ol>
            </nav>
            <h1 class="display-5 fw-bold text-white mb-3">Nos Services</h1>
            <p class="text-white text-opacity-85 lead mb-0" style="max-width: 550px;">
              Une expertise dédiée à votre transformation et à votre harmonie quotidienne.
              Des soins personnalisés pour un équilibre corps-esprit.
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-15 rounded-4 p-3 text-white">
              <div>
                <div class="fw-bold h4 mb-0">4.9★</div>
                <small class="text-white text-opacity-75">Note moyenne</small>
              </div>
              <div class="bg-white bg-opacity-20 rounded-circle p-2">
                <Star :size="24" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="container py-5">

      <!-- ===== SERVICES GRID ===== -->
      <div class="row g-4 mb-5">
        <div v-for="(svc, i) in services" :key="i" class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-shadow-lg transition-all bg-white group">
            <div class="position-relative overflow-hidden" style="aspect-ratio: 4/3;">
              <img :src="svc.img" :alt="svc.title" class="img-fluid w-100 h-100 object-fit-cover transition-all"
                   style="transition: transform 0.5s ease;"
                   @mouseenter="$event.target.style.transform='scale(1.1)'"
                   @mouseleave="$event.target.style.transform='scale(1)'" />
              <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);">
                <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-bold shadow-sm">{{ svc.price }}</span>
                <span class="badge bg-white text-dark ms-2 px-3 py-2 rounded-pill fw-bold shadow-sm">
                  <Clock :size="12" class="me-1" />{{ svc.duration }}
                </span>
              </div>
            </div>
            <div class="card-body p-3 d-flex flex-column">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-primary bg-opacity-10 d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px;">
                  <component :is="svc.icon" :size="22" class="text-primary" />
                </div>
                <h5 class="fw-bold mb-0 flex-grow-1">{{ svc.title }}</h5>
              </div>
              <p class="small text-muted mb-3">{{ svc.description }}</p>
              <div class="d-flex flex-wrap gap-1 mb-3 mt-auto">
                <span v-for="(feat, fi) in svc.features" :key="fi"
                      class="badge bg-light text-muted fw-normal d-flex align-items-center gap-1" style="font-size: 0.65rem;">
                  <CheckCircle2 :size="10" class="text-primary" /> {{ feat }}
                </span>
              </div>
              <a href="#booking" class="btn btn-outline-primary btn-sm w-100 rounded-pill mt-2 d-flex align-items-center justify-content-center gap-1">
                Réserver <ChevronRight :size="14" />
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== SERVICES COMPLÉMENTAIRES ===== -->
      <section class="py-5 bg-light rounded-4 mb-5">
        <div class="container">
          <div class="text-center mb-4">
            <p class="text-gold fw-bold text-uppercase ls-wide small mb-2">En complément</p>
            <h2 class="display-font h2 fw-bold mb-0">Services Inclus</h2>
          </div>
          <div class="row g-3">
            <div v-for="(ex, i) in extraServices" :key="'ex-'+i" class="col-6 col-lg-3">
              <div class="card h-100 border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                <div :class="[ex.color, 'text-white d-inline-flex p-2 rounded-2 mb-2 mx-auto']" style="width: fit-content;">
                  <component :is="ex.icon" :size="20" />
                </div>
                <h6 class="fw-bold small mb-1">{{ ex.title }}</h6>
                <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ ex.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== BOOKING & TESTIMONIALS ===== -->
      <div class="row g-5 align-items-start" id="booking">
        <!-- Booking Form -->
        <div class="col-lg-7">
          <div class="mb-4">
            <p class="text-gold fw-bold text-uppercase ls-wide small mb-2">Prise de Rendez-vous</p>
            <h2 class="display-font h1 fw-bold mb-3">Planifiez Votre <span class="text-primary">Pause Bien-être</span></h2>
            <p class="text-muted mb-0">Prenez rendez-vous en quelques clics et profitez d'un accompagnement personnalisé par nos experts.</p>
          </div>

          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div v-if="success" class="text-center py-5">
              <div class="d-inline-flex bg-success bg-opacity-10 rounded-circle p-4 mb-4">
                <CheckCircle2 :size="64" class="text-success" />
              </div>
              <h3 class="fw-bold">Rendez-vous Demandé !</h3>
              <p class="text-muted mb-1">Nous vous contacterons très rapidement par téléphone ou WhatsApp.</p>
              <p class="text-muted small mb-4">Réponse sous 30 min habituellement.</p>
              <button @click="success = false" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                Nouveau Rendez-vous
              </button>
            </div>

            <form v-else @submit.prevent="submit" class="row g-4">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Nom complet</label>
                <input v-model="form.name" type="text" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                       :class="{ 'is-invalid': errors.name }" placeholder="Votre nom" required />
                <div v-if="errors.name" class="invalid-feedback small">{{ errors.name }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Téléphone</label>
                <input v-model="form.phone" type="tel" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                       :class="{ 'is-invalid': errors.phone }" placeholder="+229 XX XX XX XX" required />
                <div v-if="errors.phone" class="invalid-feedback small">{{ errors.phone }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Service souhaité</label>
                <select v-model="form.service" class="form-select bg-light border-0 rounded-3 px-4 py-2">
                  <option v-for="s in services" :key="s.title">{{ s.title }}</option>
                  <option>Analyse de Peau IA</option>
                  <option>Autre (Conseils)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Date souhaitée</label>
                <input v-model="form.date" type="date" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                       :class="{ 'is-invalid': errors.date }" required />
                <div v-if="errors.date" class="invalid-feedback small">{{ errors.date }}</div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold text-muted">Message (optionnel)</label>
                <textarea v-model="form.message" rows="2" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                          placeholder="Précisez vos besoins..."></textarea>
              </div>
              <div class="col-12 mt-3">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                        :disabled="loading">
                  <div v-if="loading" class="spinner-border spinner-border-sm"></div>
                  <span v-else><CalendarCheck :size="18" /> Réserver mon Instant Bien-être</span>
                </button>
              </div>

              <!-- Contact info -->
              <div class="col-12">
                <div class="bg-light rounded-3 p-3 d-flex align-items-center gap-3 mt-2">
                  <MessageCircle :size="20" class="text-primary" />
                  <small class="text-muted">Vous pouvez aussi nous joindre directement au <strong class="text-dark">+229 00 00 00 00</strong> (WhatsApp disponible)</small>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Testimonials sidebar -->
        <div class="col-lg-5">
          <div class="mb-4">
            <p class="text-gold fw-bold text-uppercase ls-wide small mb-2">Ils nous ont fait confiance</p>
            <h2 class="display-font h2 fw-bold mb-0">Avis Clients</h2>
          </div>
          <div class="d-flex flex-column gap-3">
            <div v-for="(t, i) in testimonials" :key="'t-'+i"
                 class="card border-0 shadow-sm rounded-4 p-3 bg-white">
              <div class="d-flex text-warning mb-2 gap-1">
                <Star v-for="s in 5" :key="s" :size="14" fill="currentColor" />
              </div>
              <p class="text-muted small mb-3" style="line-height: 1.6; font-style: italic;">
                "{{ t.text }}"
              </p>
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <span class="fw-bold text-primary small">{{ t.name.charAt(0) }}</span>
                  </div>
                  <small class="fw-bold">{{ t.name }}</small>
                </div>
                <small class="text-gold fw-bold">{{ t.note }}★</small>
              </div>
            </div>
          </div>

          <!-- Trust Badge -->
          <div class="bg-primary rounded-4 p-4 mt-4 text-white text-center">
            <ShieldCheck :size="36" class="mx-auto mb-2 opacity-75" />
            <h5 class="fw-bold text-white mb-1">Qualité Garantie</h5>
            <small class="text-white text-opacity-75">Tous nos soins sont réalisés avec des produits certifiés et par des professionnels qualifiés.</small>
          </div>
        </div>
      </div>
    </div>
  </PremiumLayout>
</template>

<style scoped>
.ls-wide { letter-spacing: 0.15em; }

.opacity-5 { opacity: 0.05; }
.opacity-75 { opacity: 0.75; }

.rounded-start-5 {
  border-start-start-radius: var(--bs-border-radius-xxl, 3rem) !important;
  border-end-start-radius: var(--bs-border-radius-xxl, 3rem) !important;
}

.bg-opacity-10 { --bs-bg-opacity: 0.10; }
.bg-opacity-15 { --bs-bg-opacity: 0.15; }
.bg-opacity-20 { --bs-bg-opacity: 0.20; }
.bg-opacity-50 { --bs-bg-opacity: 0.50; }

.hover-shadow-lg:hover {
  box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
  transform: translateY(-5px);
}

.transition-all {
  transition: all 0.3s ease-in-out !important;
}

/* Spinner compatibility */
.spinner-border {
  width: 1.2rem;
  height: 1.2rem;
  border-width: 0.15em;
}
</style>
