<script setup>
import {
  Mail, Phone, MapPin, Instagram, Facebook, Twitter,
  Send, CheckCircle2, Clock, MessageCircle,
  ChevronRight, Star, ArrowRight
} from 'lucide-vue-next';
import { ref } from 'vue';

const form = ref({ name: '', email: '', subject: '', message: '' });
const success = ref(false);
const loading = ref(false);
const errors = ref({});

const contactInfo = [
  {
    title: 'Notre Centre',
    text: 'Cotonou, Fidjrossè<br/>Rue après la pharmacie du Conseil',
    icon: MapPin, color: 'bg-primary'
  },
  {
    title: 'Téléphone / WhatsApp',
    text: '+229 00 00 00 00',
    icon: Phone, color: 'bg-success'
  },
  {
    title: 'Email',
    text: 'contact@victoirepara.bj',
    icon: Mail, color: 'bg-warning'
  },
  {
    title: 'Horaires d\'ouverture',
    text: 'Lun-Sam : 8h00 - 19h00<br/>Dim : 9h00 - 13h00',
    icon: Clock, color: 'bg-info'
  }
];

const faqs = [
  {
    q: 'Quels sont les délais de livraison ?',
    r: 'Livraison sous 24h à Cotonou et 48-72h dans le reste du Bénin.'
  },
  {
    q: 'Puis-je retourner un produit ?',
    r: 'Oui, sous 7 jours après réception, en parfait état, avec le ticket de caisse.'
  },
  {
    q: 'Comment fonctionne l\'analyse de peau IA ?',
    r: 'Prenez une photo de votre visage, notre IA Perfect Corp analyse 16 catégories pour vous recommander les produits adaptés.'
  },
  {
    q: 'Proposez-vous des conseils personnalisés ?',
    r: 'Oui, nos pharmaciens sont disponibles sur place, par téléphone ou WhatsApp pour vous conseiller.'
  }
];

const validate = () => {
  errors.value = {};
  if (!form.value.name.trim()) errors.value.name = 'Nom requis';
  if (!form.value.email.trim()) errors.value.email = 'Email requis';
  if (!form.value.subject.trim()) errors.value.subject = 'Sujet requis';
  if (!form.value.message.trim()) errors.value.message = 'Message requis';
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  if (!validate()) return;
  loading.value = true;
  setTimeout(() => {
    loading.value = false;
    success.value = true;
    form.value = { name: '', email: '', subject: '', message: '' };
  }, 1500);
};

const openFaq = ref(null);
const toggleFaq = (i) => {
  openFaq.value = openFaq.value === i ? null : i;
};
</script>

<template>
  <PremiumLayout title="Contact - Victoire Para">
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
                <li class="breadcrumb-item active text-white fw-bold small" aria-current="page">Contact</li>
              </ol>
            </nav>
            <h1 class="display-5 fw-bold text-white mb-3">Contactez-nous</h1>
            <p class="text-white text-opacity-85 lead mb-0" style="max-width: 550px;">
              Une question ? Un conseil ? Notre équipe est à votre disposition pour vous accompagner dans votre parcours bien-être.
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-15 rounded-4 p-3 text-white">
              <div>
                <div class="fw-bold h4 mb-0">24h</div>
                <small class="text-white text-opacity-75">Livraison express</small>
              </div>
              <div class="bg-white bg-opacity-20 rounded-circle p-2">
                <Clock :size="24" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="container py-5">
      <div class="row g-5">
        <!-- ===== LEFT: CONTACT INFO ===== -->
        <div class="col-lg-5">
          <div class="mb-4">
            <p class="text-gold fw-bold text-uppercase ls-wide small mb-2">Nos Coordonnées</p>
            <h2 class="display-font h1 fw-bold mb-3">Restons en <span class="text-primary">Contact</span></h2>
            <p class="text-muted">Notre équipe est disponible pour répondre à toutes vos questions et vous accompagner dans vos achats.</p>
          </div>

          <div class="d-flex flex-column gap-3 mb-5">
            <div v-for="(item, i) in contactInfo" :key="i"
                 class="card border-0 shadow-sm rounded-4 p-3 bg-white hover-shadow-sm transition-all">
              <div class="d-flex align-items-center gap-3">
                <div :class="[item.color, 'text-white d-flex align-items-center justify-content-center rounded-2 flex-shrink-0']" style="width: 44px; height: 44px;">
                  <component :is="item.icon" :size="20" />
                </div>
                <div>
                  <h6 class="fw-bold small mb-0">{{ item.title }}</h6>
                  <p class="text-muted mb-0 small" v-html="item.text"></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Social + CTA WhatsApp -->
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h5 class="fw-bold mb-3">Suivez-nous</h5>
            <div class="d-flex gap-2 mb-4">
              <a href="#" class="btn btn-outline-primary rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm transition-all"
                 style="width: 42px; height: 42px;">
                <Facebook :size="18" />
              </a>
              <a href="#" class="btn btn-outline-primary rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm transition-all"
                 style="width: 42px; height: 42px;">
                <Instagram :size="18" />
              </a>
              <a href="#" class="btn btn-outline-primary rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm transition-all"
                 style="width: 42px; height: 42px;">
                <Twitter :size="18" />
              </a>
            </div>
            <a href="https://wa.me/22900000000" target="_blank"
               class="btn btn-success w-100 rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
              <MessageCircle :size="18" /> WhatsApp direct
            </a>
          </div>

          <!-- Quick Stats -->
          <div class="row g-2">
            <div class="col-6">
              <div class="bg-primary rounded-3 p-3 text-center text-white">
                <div class="fw-bold h4 mb-0">1000+</div>
                <small class="text-white text-opacity-75">Clients</small>
              </div>
            </div>
            <div class="col-6">
              <div class="bg-light rounded-3 p-3 text-center">
                <div class="fw-bold h4 mb-0 text-primary">4.9★</div>
                <small class="text-muted">Satisfaction</small>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== RIGHT: FORM + FAQ ===== -->
        <div class="col-lg-7">
          <!-- Contact Form -->
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
            <div class="mb-4">
              <h3 class="fw-bold mb-1">Envoyez-nous un message</h3>
              <p class="text-muted small mb-0">Réponse garantie sous 24h</p>
            </div>

            <div v-if="success" class="text-center py-5">
              <div class="d-inline-flex bg-success bg-opacity-10 rounded-circle p-4 mb-4">
                <CheckCircle2 :size="64" class="text-success" />
              </div>
              <h3 class="fw-bold">Message Envoyé !</h3>
              <p class="text-muted mb-1">Merci pour votre confiance.</p>
              <p class="text-muted small mb-4">Nous vous répondrons très bientôt.</p>
              <button @click="success = false" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                Nouveau Message
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
                <label class="form-label small fw-bold text-muted">Email</label>
                <input v-model="form.email" type="email" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                       :class="{ 'is-invalid': errors.email }" placeholder="votre@email.com" required />
                <div v-if="errors.email" class="invalid-feedback small">{{ errors.email }}</div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold text-muted">Sujet</label>
                <input v-model="form.subject" type="text" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                       :class="{ 'is-invalid': errors.subject }" placeholder="Comment pouvons-nous vous aider ?" required />
                <div v-if="errors.subject" class="invalid-feedback small">{{ errors.subject }}</div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold text-muted">Message</label>
                <textarea v-model="form.message" class="form-control bg-light border-0 rounded-3 px-4 py-2"
                          :class="{ 'is-invalid': errors.message }" rows="4" placeholder="Votre message..." required></textarea>
                <div v-if="errors.message" class="invalid-feedback small">{{ errors.message }}</div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                        :disabled="loading">
                  <div v-if="loading" class="spinner-border spinner-border-sm"></div>
                  <span v-else><Send :size="18" /> Envoyer le message</span>
                </button>
              </div>
            </form>
          </div>

          <!-- FAQ -->
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h3 class="fw-bold mb-1">Questions fréquentes</h3>
            <p class="text-muted small mb-4">Tout ce que vous devez savoir</p>
            <div class="d-flex flex-column gap-2">
              <div v-for="(faq, i) in faqs" :key="'faq-'+i"
                   class="border border-light rounded-3 overflow-hidden transition-all">
                <button @click="toggleFaq(i)"
                        class="btn w-100 text-start d-flex align-items-center justify-content-between px-3 py-3 border-0 bg-transparent fw-semibold small">
                  {{ faq.q }}
                  <ChevronRight :size="16" class="transition-all flex-shrink-0"
                    :class="openFaq === i ? 'rotate-90' : ''" />
                </button>
                <div v-show="openFaq === i" class="px-3 pb-3">
                  <p class="text-muted small mb-0">{{ faq.r }}</p>
                </div>
              </div>
            </div>
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

.hover-shadow-sm:hover {
  box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.08) !important;
  transform: translateY(-2px);
}

.transition-all {
  transition: all 0.3s ease-in-out !important;
}

.rotate-90 {
  transform: rotate(90deg) !important;
}

.spinner-border {
  width: 1.2rem;
  height: 1.2rem;
  border-width: 0.15em;
}
</style>
