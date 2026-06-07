<script setup>
import { ShieldCheck, CheckCircle2, ShoppingCart, Phone, Zap, Target, Apple } from 'lucide-vue-next';

const props = defineProps({
    id: String
});

const PACKS_DATA = {
    'bronze': {
      name: 'Pack Bronze Minceur',
      period: '5 Jours',
      price: 15000,
      description: 'Un programme express pour relancer votre métabolisme et drainer votre corps. Idéal pour une première expérience détox.',
      fullDescription: 'Le Pack Bronze est conçu pour ceux qui souhaitent une remise en forme rapide sans contraintes majeures. Grâce au Thé Détox Wahoo et à notre guide simplifié, vous ressentirez une sensation de légèreté dès les premiers jours.',
      features: ['Thé Détox Wahoo (5 jours)', 'Guide alimentaire simplifié', 'Suivi WhatsApp de base', 'Accès à la communauté'],
      image: '/images/products/detox-wahoo.jpg'
    },
    'silver': {
      name: 'Pack Silver Équilibre',
      period: '10 Jours',
      price: 25000,
      description: 'Stabilisez vos résultats et adoptez de nouvelles habitudes saines durablement avec un plan personnalisé.',
      fullDescription: 'Le Pack Silver va plus loin en proposant un véritable rééquilibrage. Avec 10 jours de programme, vous ancrez de nouvelles habitudes. Le plan de repas personnalisé s\'adapte à votre mode de vie pour des résultats concrets.',
      features: ['Thé Détox & Infusion Sommeil', 'Plan de repas personnalisé', 'Coaching groupé 10 jours', 'Fiche de suivi de poids', 'Soutien motivationnel'],
      image: '/images/products/detox-wahoo.jpg'
    },
    'gold': {
      name: 'Pack Gold Transformation',
      period: '21 Jours',
      price: 45000,
      description: 'Une transformation complète du corps et de l\'esprit avec un accompagnement VIP et des soins exclusifs.',
      fullDescription: 'L\'expérience ultime Good Régime. 3 semaines de transformation profonde. Vous bénéficiez d\'un diagnostic complet et d\'un coaching individuel disponible 24h/24 pour garantir votre succès. Le pack inclut également un soin en centre pour couronner votre effort.',
      features: ['Gamme complète thés & compléments', 'Diagnostic nutritionnel complet', 'Coaching individuel 24h/24', 'Soin visage offert en centre', 'Priorité réservation événements'],
      image: '/images/products/detox-wahoo.jpg'
    }
};

const pack = PACKS_DATA[props.id] || PACKS_DATA['bronze'];
</script>

<template>
    <PremiumLayout :title="pack.name + ' - Good Régime'">
        <PageHero 
            :title="pack.name" 
            :subtitle="pack.description"
            image="/images/heroes/packs.png"
        />

        <div class="container py-5">
            <div class="row g-5 mb-5 pb-5 mt-2">
                <!-- Left: Product Image -->
                <div class="col-lg-6">
                    <div class="position-relative">
                        <img :src="pack.image" class="img-fluid rounded-5 shadow-premium border border-light" :alt="pack.name" />
                        <div class="position-absolute top-0 start-0 m-4">
                            <span class="badge bg-gold text-white px-4 py-2 rounded-pill fw-bold shadow-lg">ÉDITION LIMITÉE</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Content -->
                <div class="col-lg-6">
                    <div class="ps-lg-4">
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="breadcrumb small text-uppercase fw-bold ls-wide mb-0">
                                <li class="breadcrumb-item"><a href="/packs" class="text-muted text-decoration-none">Nos Packs</a></li>
                                <li class="breadcrumb-item active text-primary" aria-current="page">{{ pack.name }}</li>
                            </ol>
                        </nav>

                        <h1 class="display-4 fw-bold text-dark mb-3">{{ pack.name }}</h1>
                        
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="h2 fw-bold text-primary mb-0">{{ pack.price.toLocaleString() }} FCFA</div>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">{{ pack.period }}</span>
                        </div>

                        <p class="lead text-muted mb-5" style="line-height: 1.8">{{ pack.fullDescription }}</p>

                        <div class="row g-3 mb-5">
                            <div v-for="(feat, i) in pack.features" :key="i" class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <CheckCircle2 :size="20" class="text-gold flex-shrink-0" />
                                    <span class="fw-medium text-dark small">{{ feat }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-3">
                            <a :href="`/checkout?type=pack&id=${id}`" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg d-flex align-items-center justify-content-center gap-2 flex-grow-1 transition-all hover-scale">
                                Commander Maintenant <ShoppingCart :size="20" />
                            </a>
                            <a href="https://wa.me/22900000000" target="_blank" class="btn btn-outline-success btn-lg px-4 py-3 rounded-pill d-flex align-items-center justify-content-center gap-2">
                                <Phone :size="20" /> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Benefits Cards -->
            <div class="row g-4 py-5 bg-light rounded-5 p-4 p-md-5">
                <div v-for="(item, i) in [{ title: 'Objectif', text: 'Perte de poids saine et détoxification naturelle du corps.', icon: Target }, { title: 'Efficacité', text: 'Des résultats visibles dès la fin du programme choisi.', icon: Zap }, { title: 'Qualité', text: 'Tous nos produits sont 100% naturels et certifiés.', icon: ShieldCheck }]" :key="i" class="col-lg-4 text-center">
                    <div class="bg-white p-4 rounded-4 shadow-sm h-100 hover-shadow transition-all">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-inline-flex mb-3">
                            <component :is="item.icon" :size="32" />
                        </div>
                        <h5 class="fw-bold mb-3">{{ item.title }}</h5>
                        <p class="text-muted small mb-0">{{ item.text }}</p>
                    </div>
                </div>
            </div>
        </div>
    </PremiumLayout>
</template>

<style scoped>
.ls-wide { letter-spacing: 0.1em; }
.hover-scale:hover { transform: scale(1.02); }
.hover-shadow:hover { 
    transform: translateY(-5px);
    box-shadow: 0 1rem 2rem rgba(var(--primary-rgb),.05) !important;
}
</style>
