<script setup>
import { ShieldCheck, Target, Zap, Apple } from 'lucide-vue-next';

const packs = [
    {
        id: 'bronze',
        name: 'Pack Bronze Minceur',
        period: '5 Jours',
        price: '15.000',
        description: 'Un programme express pour relancer votre métabolisme et drainer votre corps.',
        features: ['Thé Détox Wahoo (5 jours)', 'Guide alimentaire simplifié', 'Suivi WhatsApp de base'],
        color: 'border-warning',
        bg: 'bg-warning'
    },
    {
        id: 'silver',
        name: 'Pack Silver Équilibre',
        period: '10 Jours',
        price: '25.000',
        description: 'Stabilisez vos résultats et adoptez de nouvelles habitudes saines durablement.',
        features: ['Thé Détox & Infusion Sommeil', 'Plan de repas personnalisé', 'Coaching groupé 10 jours', 'Fiche de suivi de poids'],
        color: 'border-secondary',
        bg: 'bg-secondary'
    },
    {
        id: 'gold',
        name: 'Pack Gold Transformation',
        period: '21 Jours',
        price: '45.000',
        description: 'Une transformation complète du corps et de l\'esprit avec un accompagnement VIP.',
        features: ['Gamme complète thés & compléments', 'Diagnostic nutritionnel complet', 'Coaching individuel 24h/24', 'Soin visage offert en centre'],
        color: 'border-primary',
        bg: 'bg-primary'
    }
];

const benefits = [
    { title: "Approche Holistique", text: "Nous traitons les causes, pas seulement les symptômes.", icon: Target },
    { title: "Résultats Durables", text: "Pas d'effet yoyo, nous vous apprenons à bien vivre.", icon: Zap },
    { title: "Support Constant", text: "Une équipe à votre écoute au Bénin et en ligne.", icon: Apple }
];
</script>

<template>
    <PremiumLayout title="Nos Packs - Good Régime">
        <PageHero 
            title="Nos Packs Signature" 
            subtitle="Des solutions clés en main conçues par nos experts pour vous aider à atteindre vos objectifs de santé et de bien-être au quotidien."
            image="/images/heroes/packs.png"
        />

        <div class="container py-5">
            <!-- Packs Grid -->
            <div class="row g-4 align-items-stretch mb-5 pb-5">
                <div v-for="(pack, i) in packs" :key="i" class="col-lg-4">
                    <div class="card h-100 border-2 rounded-5 shadow-sm p-4 bg-white hover-shadow transition-all" :class="pack.color">
                        <div class="card-body p-0 d-flex flex-column">
                            <div class="mb-4">
                                <h2 class="h3 fw-bold mb-1">{{ pack.name }}</h2>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold small text-uppercase">{{ pack.period }}</span>
                            </div>
                            
                            <div class="display-4 fw-bold text-dark mb-4 ls-tight">
                                {{ pack.price }} <small class="fs-5 text-muted fw-normal">FCFA</small>
                            </div>

                            <p class="text-muted mb-4 flex-grow-1">{{ pack.description }}</p>

                            <div class="bg-light p-4 rounded-4 mb-4">
                                <h6 class="fw-bold mb-3 small text-uppercase ls-wide" style="font-size: 11px">Ce qui est inclus :</h6>
                                <ul class="list-unstyled mb-0">
                                    <li v-for="(feat, idx) in pack.features" :key="idx" class="small d-flex align-items-start gap-2 mb-2">
                                        <ShieldCheck :size="16" class="text-primary mt-1 flex-shrink-0" />
                                        <span class="text-dark fw-medium">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <a :href="`/packs/${pack.id}`" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg mt-auto">
                                Détails & Commande
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="row g-5 align-items-center py-5 border-top">
                <div class="col-lg-6">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=1000&auto=format&fit=crop" class="img-fluid rounded-5 shadow-lg" alt="Wellness" />
                        <div class="position-absolute bottom-0 end-0 m-4 p-4 bg-white rounded-4 shadow-premium border-start border-4 border-primary">
                            <div class="h3 fw-bold text-primary mb-0">100% Naturel</div>
                            <p class="small text-muted mb-0">Produits certifiés et garantis.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="display-5 fw-bold mb-4">Pourquoi choisir nos packs ?</h2>
                    <div class="d-grid gap-4">
                        <div v-for="(item, i) in benefits" :key="i" class="d-flex gap-4 p-4 rounded-4 bg-white shadow-sm border-start border-4 border-primary hover-shadow transition-all">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle" style="height: fit-content;">
                                <component :is="item.icon" :size="24" />
                            </div>
                            <div>
                                <h4 class="h5 fw-bold mb-2">{{ item.title }}</h4>
                                <p class="text-muted mb-0 small">{{ item.text }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PremiumLayout>
</template>

<style scoped>
.ls-tight { letter-spacing: -0.02em; }
.ls-wide { letter-spacing: 0.1em; }
.hover-shadow:hover { 
    transform: translateY(-10px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important;
}
</style>
