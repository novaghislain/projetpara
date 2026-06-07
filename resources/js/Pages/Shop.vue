<script setup>
import { ref, computed } from 'vue';
import {
  ShoppingCart, Star, Search as SearchIcon,
  ChevronDown, Filter, SlidersHorizontal,
  Grid3X3, List, ArrowUpDown, TrendingUp,
  Clock, DollarSign, Sparkles, CheckCircle
} from 'lucide-vue-next';
import { addToCart } from '../stores/cart';

const props = defineProps({
  products: { type: Array, default: () => [] }
});

const activeCategory = ref('Tous');
const sortBy = ref('default');
const viewMode = ref('grid');
const notification = ref('');

const showNotif = (msg) => {
  notification.value = msg;
  setTimeout(() => { notification.value = ''; }, 2500);
};

const categories = ['Tous', 'Beauté & Soins', 'Huiles Naturelles', 'Thés & Infusions', 'Packs Bien-être', 'Produits Bébé'];

const searchQuery = ref(new URLSearchParams(window.location.search).get('search') || '');

const filteredProducts = computed(() => {
  let result = [...props.products];

  // Category filter
  if (activeCategory.value !== 'Tous') {
    result = result.filter(p => p.category === activeCategory.value);
  }

  // Search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(p =>
      p.name.toLowerCase().includes(query) ||
      p.category.toLowerCase().includes(query) ||
      (p.description && p.description.toLowerCase().includes(query))
    );
  }

  // Sorting
  switch (sortBy.value) {
    case 'price-asc':
      result.sort((a, b) => a.price - b.price);
      break;
    case 'price-desc':
      result.sort((a, b) => b.price - a.price);
      break;
    case 'name':
      result.sort((a, b) => a.name.localeCompare(b.name));
      break;
    case 'rating':
      result.sort((a, b) => b.rating - a.rating);
      break;
  }

  return result;
});

const displayedCategories = computed(() => {
  const counts = {};
  props.products.forEach(p => {
    counts[p.category] = (counts[p.category] || 0) + 1;
  });
  return categories.map(c => ({
    name: c,
    count: c === 'Tous' ? props.products.length : (counts[c] || 0)
  }));
});

const formatPrice = (price) => {
  return Number(price).toLocaleString() + ' FCFA';
};
</script>

<template>
  <PremiumLayout title="Boutique - Victoire Para">
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
                <li class="breadcrumb-item active text-white fw-bold small" aria-current="page">Boutique</li>
              </ol>
            </nav>
            <h1 class="display-5 fw-bold text-white mb-3">Notre Boutique</h1>
            <p class="text-white text-opacity-85 lead mb-0" style="max-width: 550px;">
              Le meilleur de la parapharmacie, nutrition et soins naturels. Des produits certifiés pour votre bien-être.
            </p>
          </div>
          <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-15 rounded-4 p-3 text-white">
              <div>
                <div class="fw-bold h4 mb-0">{{ props.products.length }}</div>
                <small class="text-white text-opacity-75">Produits disponibles</small>
              </div>
              <div class="bg-white bg-opacity-20 rounded-circle p-2">
                <Sparkles :size="24" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="container py-5">
      <!-- ===== FILTER BAR ===== -->
      <div class="bg-white rounded-4 shadow-sm border border-light p-3 p-md-4 mb-4">
        <!-- Category Pills -->
        <div class="d-flex flex-wrap gap-2 mb-3">
          <button
            v-for="cat in displayedCategories"
            :key="cat.name"
            @click="activeCategory = cat.name"
            class="btn px-3 py-2 rounded-pill fw-semibold border-0 transition-all small d-inline-flex align-items-center gap-2"
            :class="activeCategory === cat.name ? 'btn-primary shadow-sm text-white' : 'btn-light text-muted bg-light'"
          >
            {{ cat.name }}
            <span v-if="cat.count > 0" class="badge" :class="activeCategory === cat.name ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-muted'">
              {{ cat.count }}
            </span>
          </button>
        </div>

        <!-- Sort & View Controls -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top border-light">
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <ArrowUpDown :size="16" class="text-muted" />
              <select v-model="sortBy" class="form-select form-select-sm rounded-pill border-0 bg-light fw-semibold small" style="max-width: 180px;">
                <option value="default">Trier par : Défaut</option>
                <option value="price-asc">Prix : croissant</option>
                <option value="price-desc">Prix : décroissant</option>
                <option value="name">Nom : A-Z</option>
                <option value="rating">Meilleures notes</option>
              </select>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <!-- Search -->
            <div class="position-relative">
              <SearchIcon :size="16" class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" />
              <input
                v-model="searchQuery"
                type="text"
                class="form-control form-control-sm rounded-pill border-0 bg-light ps-5 py-2 small"
                placeholder="Rechercher un produit..."
                style="width: 220px;"
              />
            </div>
            <!-- View Toggle -->
            <div class="d-flex bg-light rounded-pill p-1">
              <button @click="viewMode = 'grid'"
                class="btn btn-sm rounded-pill border-0 px-3 transition-all"
                :class="viewMode === 'grid' ? 'btn-primary shadow-sm' : 'btn-light text-muted'">
                <Grid3X3 :size="16" />
              </button>
              <button @click="viewMode = 'list'"
                class="btn btn-sm rounded-pill border-0 px-3 transition-all"
                :class="viewMode === 'list' ? 'btn-primary shadow-sm' : 'btn-light text-muted'">
                <List :size="16" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== PRODUCT COUNT ===== -->
      <div class="mb-4">
        <small class="text-muted">
          <strong class="text-dark">{{ filteredProducts.length }}</strong> produit{{ filteredProducts.length > 1 ? 's' : '' }} trouvé{{ filteredProducts.length > 1 ? 's' : '' }}
          <template v-if="activeCategory !== 'Tous'"> dans <strong>{{ activeCategory }}</strong></template>
          <template v-if="searchQuery"> pour "<strong>{{ searchQuery }}</strong>"</template>
        </small>
      </div>

      <!-- ===== GRID VIEW ===== -->
      <div v-if="viewMode === 'grid'" class="row g-4">
        <div v-for="product in filteredProducts" :key="product.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-shadow-lg transition-all bg-white group">
            <!-- Image -->
            <div class="position-relative overflow-hidden bg-light" style="aspect-ratio: 1/1;">
              <img :src="product.img" :alt="product.name" class="img-fluid w-100 h-100 object-fit-cover transition-all"
                   style="transition: transform 0.5s ease;"
                   @mouseenter="$event.target.style.transform='scale(1.1)'"
                   @mouseleave="$event.target.style.transform='scale(1)'" />

              <!-- Badges -->
              <div class="position-absolute top-3 start-3 d-flex flex-column gap-1">
                <span v-if="product.is_victoire" class="badge bg-primary rounded-pill px-2 py-1 small">
                  Victoire
                </span>
                <span v-if="product.rating >= 4.5" class="badge bg-gold text-white rounded-pill px-2 py-1 small">
                  Top Vente
                </span>
              </div>

              <!-- Quick Add -->
              <button @click="addToCart(product.id, 1); showNotif('✓ Ajouté au panier')"
                 class="btn btn-white position-absolute bottom-3 end-3 p-2 rounded-circle shadow-sm bg-white text-dark border-0 transition-all"
                 @mouseenter="$event.target.classList.add('bg-primary','text-white')"
                 @mouseleave="$event.target.classList.remove('bg-primary','text-white')"
                 title="Ajouter au panier">
                <ShoppingCart :size="16" />
              </button>
            </div>

            <!-- Info -->
            <div class="card-body p-3 d-flex flex-column">
              <p class="text-gold fw-bold text-uppercase small mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">
                {{ product.category }}
              </p>
              <h6 class="fw-bold mb-2 text-truncate" :title="product.name">{{ product.name }}</h6>

              <!-- Features mini badges -->
              <div v-if="product.features && product.features.length" class="d-flex flex-wrap gap-1 mb-2">
                <span v-for="(feat, fi) in product.features.slice(0,2)" :key="fi"
                      class="badge bg-light text-muted fw-normal" style="font-size: 0.6rem;">
                  {{ feat }}
                </span>
              </div>

              <div class="d-flex text-warning mb-2 small gap-1">
                <Star v-for="i in 5" :key="i" :size="11"
                  :fill="i <= product.rating ? 'currentColor' : 'none'"
                  :stroke-width="i <= product.rating ? 0 : 1.5" />
              </div>

              <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light mt-auto">
                <span class="fw-bold text-primary fs-5">{{ formatPrice(product.price) }}</span>
                <a :href="`/product/${product.id}`" class="btn btn-sm btn-outline-primary rounded-pill px-3">Détails</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== LIST VIEW ===== -->
      <div v-if="viewMode === 'list'" class="d-flex flex-column gap-3">
        <div v-for="product in filteredProducts" :key="'list-'+product.id"
             class="card border-0 shadow-sm rounded-4 overflow-hidden hover-shadow-lg transition-all bg-white">
          <div class="row g-0 align-items-center">
            <div class="col-md-3 col-lg-2">
              <img :src="product.img" :alt="product.name" class="img-fluid w-100 object-fit-cover" style="height: 180px;" />
            </div>
            <div class="col-md-9 col-lg-10">
              <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="text-gold fw-bold text-uppercase small" style="font-size: 0.65rem; letter-spacing: 1px;">{{ product.category }}</span>
                    <span v-if="product.is_victoire" class="badge bg-primary rounded-pill" style="font-size: 0.55rem;">Victoire</span>
                  </div>
                  <h6 class="fw-bold mb-1">{{ product.name }}</h6>
                  <p class="text-muted small mb-2 d-none d-md-block">{{ product.description }}</p>
                  <div class="d-flex text-warning small gap-1">
                    <Star v-for="i in 5" :key="i" :size="11"
                      :fill="i <= product.rating ? 'currentColor' : 'none'"
                      :stroke-width="i <= product.rating ? 0 : 1.5" />
                  </div>
                </div>
                <div class="text-md-end flex-shrink-0">
                  <div class="fw-bold text-primary fs-5 mb-2">{{ formatPrice(product.price) }}</div>
                  <div class="d-flex gap-2">
                    <button @click="addToCart(product.id, 1); showNotif('✓ Ajouté au panier')" class="btn btn-primary btn-sm rounded-pill px-3">
                      <ShoppingCart :size="14" class="me-1" /> Acheter
                    </button>
                    <a :href="`/product/${product.id}`" class="btn btn-outline-primary btn-sm rounded-pill px-3">Détails</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== EMPTY STATE ===== -->
      <div v-if="filteredProducts.length === 0" class="text-center py-5">
        <div class="d-inline-flex bg-light rounded-circle p-4 mb-4">
          <SearchIcon :size="48" class="text-muted opacity-50" />
        </div>
        <h3 class="fw-bold">Aucun produit trouvé</h3>
        <p class="text-muted mb-4">
          Aucun produit ne correspond à vos critères. Essayez d'ajuster vos filtres.
        </p>
        <button @click="activeCategory = 'Tous'; searchQuery = ''; sortBy = 'default'"
          class="btn btn-primary rounded-pill px-4">
          Réinitialiser les filtres
        </button>
      </div>
        <!-- Toast Notification -->
        <div v-if="notification"
             class="position-fixed bottom-0 end-0 m-4 bg-success text-white px-4 py-3 rounded-4 shadow-lg fw-semibold small z-3"
             style="animation: fadeInUp 0.3s ease;">
          {{ notification }}
        </div>
      </div>
    </PremiumLayout>
  </template>

<style scoped>
.ls-wide { letter-spacing: 0.15em; }

.opacity-5 { opacity: 0.05; }

.rounded-start-5 {
  border-start-start-radius: var(--bs-border-radius-xxl, 3rem) !important;
  border-end-start-radius: var(--bs-border-radius-xxl, 3rem) !important;
}

.bg-opacity-15 { --bs-bg-opacity: 0.15; }
.bg-opacity-20 { --bs-bg-opacity: 0.20; }
.bg-opacity-50 { --bs-bg-opacity: 0.50; }

/* Prevent text from wrapping on category pills */
.form-select-sm {
  font-size: 0.85rem;
}

.hover-shadow-lg:hover {
  box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
  transform: translateY(-5px);
}

.transition-all {
  transition: all 0.3s ease-in-out !important;
}

/* Responsive search */
@media (max-width: 576px) {
  .form-control-sm {
    width: 140px !important;
  }
}
</style>
