<script setup>
import {
  ShieldCheck, Star, ArrowRight, ShoppingCart,
  Heart, Leaf, Sparkles, Zap, ChevronRight,
  Award, Truck, Users, Clock, Quote,
  CheckCircle, BarChart3, Search, Camera
} from 'lucide-vue-next';

const props = defineProps({
  bestSellers: { type: Array, default: () => [] }
});

const navigate = (url) => {
  window.location.href = url;
};

const categories = [
  { name: 'Beauté & Soins', icon: Sparkles, color: 'bg-danger-subtle', img: '/images/products/serum-vitamine-c.jpg' },
  { name: 'Huiles Naturelles', icon: Leaf, color: 'bg-success-subtle', img: '/images/products/huile-naturelle.jpg' },
  { name: 'Thés & Infusions', icon: Zap, color: 'bg-warning-subtle', img: '/images/products/detox-wahoo.jpg' },
  { name: 'Produits Bébé', icon: Heart, color: 'bg-primary-subtle', img: '/images/products/produit-bebe.jpg' }
];

const services = [
  {
    icon: BarChart3, title: 'Analyse de Peau IA',
    desc: 'Diagnostic cutané intelligent par IA pour des soins personnalisés',
    color: 'bg-primary', link: '/consultation'
  },
  {
    icon: Search, title: 'Conseil Pharma',
    desc: 'Accompagnement professionnel par nos pharmaciens certifiés',
    color: 'bg-success', link: '/services'
  },
  {
    icon: Heart, title: 'Bien-être & Nutrition',
    desc: 'Programmes sur mesure pour votre santé et votre vitalité',
    color: 'bg-warning', link: '/services'
  },
  {
    icon: Truck, title: 'Livraison Rapide',
    desc: 'Livraison express à Cotonou et dans tout le Bénin',
    color: 'bg-info', link: '/shop'
  }
];

const stats = [
  { value: '1000+', label: 'Clients satisfaits' },
  { value: '99%', label: 'Produits certifiés' },
  { value: '24h', label: 'Livraison express' },
  { value: '4.9', label: 'Note moyenne' }
];

const testimonials = [
  {
    text: "Grâce à l'analyse de peau, j'ai trouvé les produits parfaits pour mon type de peau. Résultats visibles dès la première semaine !",
    name: "Marie K.",
    role: "Cliente régulière",
    rating: 5
  },
  {
    text: "Service exceptionnel et conseils personnalisés. La livraison est rapide et les produits sont de grande qualité.",
    name: "Jean B.",
    role: "Client depuis 2023",
    rating: 5
  },
  {
    text: "Enfin une parapharmacie qui comprend les besoins spécifiques de notre peau sous les tropiques. Je recommande vivement !",
    name: "Amina S.",
    role: "Nouvelle cliente",
    rating: 5
  }
];
</script>

<template>
  <PremiumLayout title="Victoire Para - Santé & Bien-Être au Naturel">
    <div class="min-h-screen overflow-x-hidden bg-white">

      <!-- ===== HERO SECTION ===== -->
      <section class="position-relative overflow-hidden">
        <!-- Background Decor -->
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-primary-light opacity-30"></div>
        <div class="position-absolute top-0 end-0 w-50 h-100 d-none d-lg-block">
          <div class="bg-primary rounded-start-5 h-100 w-100 opacity-10" style="border-radius: 0 0 0 30%;"></div>
        </div>
        <div class="position-absolute bottom-0 start-0 mb-5 ms-5 d-none d-lg-block">
          <div class="bg-gold rounded-circle opacity-20" style="width: 200px; height: 200px; filter: blur(60px);"></div>
        </div>

        <div class="container position-relative z-1 py-5 py-lg-6">
          <div class="row align-items-center g-5 min-vh-70">
            <div class="col-lg-6">
              <div>
                <!-- Badge -->
                <div class="d-inline-flex align-items-center gap-2 bg-primary bg-opacity-10 text-primary fw-semibold small px-3 py-1 rounded-pill mb-4">
                  <ShieldCheck :size="14" />
                  Qualité Pharmaceutique Certifiée
                </div>

                <h1 class="display-5 fw-bold text-dark mb-3 lh-sm">
                  Votre Santé,<br />
                  <span class="text-primary">Notre Priorité</span>
                </h1>

                <p class="text-muted mb-4 fs-6 pe-lg-5 lead opacity-85">
                  Parapharmacie premium, nutrition sportive et soins naturels au Bénin.
                  Découvrez une approche holistique du bien-être avec des produits certifiés
                  et des conseils d'experts.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
                  <a href="/shop" class="btn btn-primary px-4 py-2 rounded-pill shadow-lg d-flex align-items-center gap-2 fw-bold">
                    Boutique
                    <ArrowRight :size="18" />
                  </a>
                  <a href="/services" class="btn btn-outline-dark px-4 py-2 rounded-pill fw-bold border-2 transition-all">
                    Nos Services
                  </a>
                  <a href="/consultation" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold border-2 transition-all">
                    Analyse de Peau
                  </a>
                </div>

                <!-- Stats bar -->
                <div class="d-flex gap-4 gap-md-5 flex-wrap">
                  <div v-for="(stat, i) in stats.slice(0,3)" :key="i" class="text-center text-start">
                    <div class="fw-bold text-primary h5 mb-0">{{ stat.value }}</div>
                    <small class="text-muted">{{ stat.label }}</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="position-relative">
                <!-- Main Image -->
                <div class="card border-0 rounded-5 shadow-3xl overflow-hidden bg-white">
                  <img src="/images/heroes/victoire_center.jpg" alt="Victoire Center" class="img-fluid" style="max-height: 500px; width: 100%; object-fit: cover;" />
                  <!-- Floating badge -->
                  <div class="position-absolute bottom-0 start-0 m-3 bg-white rounded-4 shadow-lg p-3 d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded-3 p-2">
                      <ShieldCheck :size="24" class="text-success" />
                    </div>
                    <div>
                      <small class="fw-bold text-dark d-block">100% Certifié</small>
                      <small class="text-muted">Produits authentiques</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== SERVICES / EXPERTISE ===== -->
      <section class="py-5 py-lg-6 bg-light position-relative overflow-hidden">
        <div class="container position-relative z-1">
          <div class="text-center mb-5">
            <p class="text-gold fw-bold text-uppercase ls-wide small mb-2">Notre Expertise</p>
            <h2 class="display-font h1 fw-bold mb-3">Des Services Complets</h2>
            <p class="text-muted mx-auto" style="max-width: 550px;">
              De l'analyse cutanée intelligente à la livraison rapide, tout pour votre bien-être
            </p>
          </div>
          <div class="row g-4">
            <div v-for="(service, i) in services" :key="'svc-'+i" class="col-12 col-sm-6 col-lg-3">
              <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white hover-shadow-lg transition-all cursor-pointer" @click="navigate(service.link)">
                <div :class="[service.color, 'text-white d-inline-flex p-3 rounded-3 mb-3 shadow-sm']" style="width: fit-content;">
                  <component :is="service.icon" :size="24" />
                </div>
                <h5 class="fw-bold mb-2">{{ service.title }}</h5>
                <p class="text-muted small mb-0">{{ service.desc }}</p>
                <div class="mt-3 text-primary fw-semibold small d-flex align-items-center gap-1">
                  En savoir plus <ChevronRight :size="14" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== WHY CHOOSE US ===== -->
      <section class="py-5 py-lg-6 position-relative overflow-hidden">
        <div class="container">
          <div class="row g-5 align-items-center">
            <div class="col-lg-5">
              <p class="text-gold fw-bold text-uppercase ls-wide small mb-2">Pourquoi Nous</p>
              <h2 class="display-font h1 fw-bold mb-4">L'Excellence au Service de Votre Santé</h2>
              <p class="text-muted mb-4">
                Victoire Para allie expertise pharmaceutique, produits certifiés et innovation
                technologique pour vous offrir une expérience bien-être unique au Bénin.
              </p>
              <div class="d-grid gap-4">
                <div v-for="(item, i) in [
                  { icon: Award, title: 'Produits Certifiés', desc: 'Sélection rigoureuse des meilleures marques' },
                  { icon: Users, title: 'Experts à Votre Écoute', desc: 'Pharmaciens et conseillers dédiés' },
                  { icon: Clock, title: 'Service Rapide', desc: 'Traitement et livraison en 24h' },
                  { icon: Camera, title: 'Analyse IA', desc: 'Diagnostic cutané par Intelligence Artificielle' }
                ]" :key="'why-'+i" class="d-flex gap-3">
                  <div class="bg-primary bg-opacity-10 rounded-2 p-2 flex-shrink-0" style="width: 44px; height: 44px;">
                    <component :is="item.icon" :size="24" class="text-primary" />
                  </div>
                  <div>
                    <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                    <p class="text-muted small mb-0">{{ item.desc }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6 offset-lg-1">
              <div class="row g-3">
                <div class="col-6">
                  <div class="bg-light rounded-4 p-4 text-center h-100">
                    <div class="fw-bold text-primary display-6 mb-1">98%</div>
                    <small class="text-muted">Clients satisfaits</small>
                  </div>
                </div>
                <div class="col-6">
                  <div class="bg-light rounded-4 p-4 text-center h-100 mt-5">
                    <div class="fw-bold text-primary display-6 mb-1">500+</div>
                    <small class="text-muted">Produits disponibles</small>
                  </div>
                </div>
                <div class="col-6">
                  <div class="bg-primary rounded-4 p-4 text-center h-100 text-white">
                    <div class="fw-bold display-6 mb-1">24h</div>
                    <small class="text-white text-opacity-75">Livraison express</small>
                  </div>
                </div>
                <div class="col-6">
                  <div class="bg-gold rounded-4 p-4 text-center h-100 text-white">
                    <div class="fw-bold display-6 mb-1">4.9★</div>
                    <small class="text-white text-opacity-75">Note moyenne</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== CATEGORIES ===== -->
      <section class="py-5 py-lg-6 bg-light overflow-hidden">
        <div class="container">
          <div class="row align-items-end mb-5">
            <div class="col">
              <p class="text-gold fw-bold text-uppercase ls-wide small mb-1">Catalogue</p>
              <h2 class="display-font h1 fw-bold mb-0">Parcourir par Univers</h2>
            </div>
            <div class="col-auto">
              <a href="/shop" class="btn btn-link text-primary fw-bold text-decoration-none d-flex align-items-center gap-2 ps-0">
                Tout voir <ArrowRight :size="20" />
              </a>
            </div>
          </div>
          <div class="row g-4">
            <div v-for="(cat, idx) in categories" :key="'cat-'+idx" class="col-12 col-sm-6 col-md-3">
              <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden cursor-pointer bg-white group" @click="navigate('/shop')">
                <div class="position-relative overflow-hidden" style="aspect-ratio: 1/1;">
                  <img :src="cat.img" :alt="cat.name" class="img-fluid w-100 h-100 object-fit-cover transition-all" style="transition: transform 0.5s ease;"
                       @mouseenter="$event.target.style.transform='scale(1.1)'"
                       @mouseleave="$event.target.style.transform='scale(1)'" />
                  <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-to-t text-white opacity-0 transition-all" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);"
                       @mouseenter="$event.target.style.opacity='1'" @mouseleave="$event.target.style.opacity='0'">
                    <span class="fw-bold small">Découvrir <ChevronRight :size="14" /></span>
                  </div>
                </div>
                <div class="card-body text-center p-3">
                  <div :class="[cat.color, 'text-primary d-inline-flex p-2 rounded-3 mb-2 shadow-sm']">
                    <component :is="cat.icon" :size="20" />
                  </div>
                  <h5 class="fw-bold mb-0">{{ cat.name }}</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== CONSULTATION CTA ===== -->
      <section class="py-5 py-lg-6 position-relative overflow-hidden">
        <div class="position-absolute top-50 start-50 translate-middle w-75 h-75 bg-primary opacity-5 rounded-circle" style="filter: blur(80px);"></div>
        <div class="container position-relative z-1">
          <div class="card border-0 rounded-5 overflow-hidden bg-primary text-white">
            <div class="row g-0 align-items-center">
              <div class="col-lg-6 p-4 p-lg-5">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-20 text-white fw-semibold small px-3 py-1 rounded-pill mb-4">
                  <Sparkles :size="14" />
                  Nouveau - Intelligence Artificielle
                </div>
                <h2 class="display-font fw-bold mb-3 h1">Analyse de Peau par IA</h2>
                <p class="text-white text-opacity-85 mb-4 lead" style="font-size: 1.05rem;">
                  Obtenez un diagnostic personnalisé de votre peau grâce à notre technologie
                  Perfect Corp Skin Analysis. 16 catégories HD analysées pour des recommandations
                  produits sur mesure.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                  <div v-for="(ft, i) in ['HD Rides', 'Pores', 'Acné', 'Rougeurs', 'Hydratation', 'Type de peau']" :key="i"
                       class="bg-white bg-opacity-15 text-white small px-3 py-1 rounded-pill">
                    {{ ft }}
                  </div>
                </div>
                <a href="/consultation" class="btn btn-gold text-white px-5 py-2 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                  <Camera :size="18" /> Faire mon analyse
                </a>
              </div>
              <div class="col-lg-6 d-none d-lg-block">
                <div class="bg-white bg-opacity-10 p-5 rounded-start-5 m-4">
                  <img src="/images/products/serum-vitamine-c.jpg" alt="Skin Analysis" class="img-fluid rounded-4 shadow-lg" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== BEST SELLERS ===== -->
      <section class="py-5 py-lg-6 bg-light" v-if="bestSellers.length">
        <div class="container">
          <div class="text-center mb-5">
            <p class="text-gold fw-bold text-uppercase ls-wide small mb-1">Populaires</p>
            <h2 class="display-font h1 fw-bold">Nos Incontournables</h2>
            <p class="text-muted mx-auto" style="max-width: 450px;">
              Les produits préférés de nos clients
            </p>
          </div>
          <div class="row g-4">
            <div v-for="(item, i) in bestSellers" :key="i" class="col-12 col-sm-6 col-lg-3">
              <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-shadow-lg transition-all bg-white group">
                <div class="position-relative overflow-hidden bg-light" style="aspect-ratio: 1/1;">
                  <img :src="item.img" :alt="item.name" class="img-fluid w-100 h-100 object-fit-cover transition-all"
                       style="transition: transform 0.5s ease;"
                       @mouseenter="$event.target.style.transform='scale(1.1)'"
                       @mouseleave="$event.target.style.transform='scale(1)'" />
                  <div class="position-absolute top-3 end-3 d-flex gap-2">
                    <span class="badge bg-primary rounded-pill px-2 py-1 small">Top</span>
                  </div>
                  <a :href="`/checkout?id=${item.id}`" class="btn btn-white position-absolute bottom-3 end-3 p-2 rounded-circle shadow-sm bg-white text-dark transition-all"
                     @mouseenter="$event.target.classList.add('bg-primary','text-white')"
                     @mouseleave="$event.target.classList.remove('bg-primary','text-white')">
                    <ShoppingCart :size="16" />
                  </a>
                </div>
                <div class="card-body p-3 text-center">
                  <div class="d-flex justify-content-center text-warning mb-2 small gap-1">
                    <Star v-for="s in 5" :key="s" :size="11" :fill="s <= item.rating ? 'currentColor' : 'none'" :stroke-width="s <= item.rating ? 0 : 1.5" />
                  </div>
                  <h6 class="fw-bold text-truncate mb-2">{{ item.name }}</h6>
                  <p class="fw-bold text-primary mb-3 fs-5">{{ Number(item.price).toLocaleString() }} FCFA</p>
                  <a :href="`/product/${item.id}`" class="btn btn-outline-primary btn-sm w-100 rounded-pill">Détails</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== TESTIMONIALS ===== -->
      <section class="py-5 py-lg-6 position-relative overflow-hidden">
        <div class="position-absolute end-0 bottom-0 mb-5 me-5 d-none d-lg-block">
          <div class="bg-primary rounded-circle opacity-10" style="width: 300px; height: 300px; filter: blur(80px);"></div>
        </div>
        <div class="container position-relative z-1">
          <div class="text-center mb-5">
            <p class="text-gold fw-bold text-uppercase ls-wide small mb-2">Témoignages</p>
            <h2 class="display-font h1 fw-bold mb-0">Ce Que Disent Nos Clients</h2>
          </div>
          <div class="row g-4">
            <div v-for="(t, i) in testimonials" :key="'test-'+i" class="col-lg-4">
              <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex text-warning mb-3 gap-1">
                  <Star v-for="s in t.rating" :key="s" :size="16" fill="currentColor" />
                </div>
                <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.7; font-style: italic;">
                  "{{ t.text }}"
                </p>
                <div class="d-flex align-items-center gap-3 pt-3 border-top border-light">
                  <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <span class="fw-bold text-primary small">{{ t.name.charAt(0) }}</span>
                  </div>
                  <div>
                    <small class="fw-bold d-block">{{ t.name }}</small>
                    <small class="text-muted">{{ t.role }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== FINAL CTA ===== -->
      <section class="py-5 py-lg-6 bg-light">
        <div class="container">
          <div class="text-center">
            <h2 class="display-font h1 fw-bold mb-3">Prêt à Transformer Votre Bien-Être ?</h2>
            <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
              Rejoignez des milliers de clients satisfaits et découvrez une nouvelle façon de prendre soin de vous
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
              <a href="/register" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                Créer un Compte <ArrowRight :size="18" />
              </a>
              <a href="/shop" class="btn btn-outline-dark px-5 py-2 rounded-pill fw-bold">
                Découvrir la Boutique
              </a>
            </div>
          </div>
        </div>
      </section>

    </div>
  </PremiumLayout>
</template>

<style scoped>
.italic { font-style: italic; }
.ls-wide { letter-spacing: 0.15em; }
.shadow-3xl { box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.15); }

.min-vh-70 {
  min-height: 70vh;
}

.py-lg-6 {
  padding-top: 5rem;
  padding-bottom: 5rem;
}

.bg-gradient-primary-light {
  background: linear-gradient(135deg, rgba(27, 94, 32, 0.05) 0%, rgba(255, 255, 255, 0) 50%);
}

.rounded-start-5 {
  border-start-start-radius: var(--bs-border-radius-xxl) !important;
  border-end-start-radius: var(--bs-border-radius-xxl) !important;
}

.btn-gold {
  background: linear-gradient(135deg, #c9a84c, #d4af37);
  border: none;
  color: white;
  transition: all 0.3s ease;
}
.btn-gold:hover {
  background: linear-gradient(135deg, #d4af37, #e6c25a);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3) !important;
}

.opacity-5 { opacity: 0.05; }
.opacity-15 { opacity: 0.15; }
.opacity-30 { opacity: 0.30; }
.opacity-85 { opacity: 0.85; }

.group:hover .group-hover-scale {
  transform: scale(1.05);
}

.bg-opacity-15 { --bs-bg-opacity: 0.15; }

.bg-gradient-to-t {
  background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
}
</style>
