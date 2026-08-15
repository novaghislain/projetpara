<script setup>
import { ref } from 'vue';
import PublicLayout from '../../Layouts/Public/PublicLayout.vue';

const services = ref([
    {
        id: 1,
        title: 'Création d\'entreprise (SARL, SAS, SUARL)',
        category: 'Juridique',
        description: 'Nous vous accompagnons de A à Z dans la création de votre entreprise au Bénin. Obtention du RCCM, IFU, et formalités APIEx incluses.',
        price: 'À partir de 150 000 FCFA',
        icon: 'bi-building',
        color: '#3b82f6'
    },
    {
        id: 2,
        title: 'Tenue Comptable et Fiscale OHADA',
        category: 'Comptabilité',
        description: 'Externalisation complète de votre comptabilité. Saisie, déclarations fiscales mensuelles, liasse fiscale annuelle et bilans.',
        price: 'Sur devis (dès 50 000 FCFA/mois)',
        icon: 'bi-calculator',
        color: '#10b981'
    },
    {
        id: 3,
        title: 'Gestion de la Paie (ITS/CNSS Bénin)',
        category: 'Ressources Humaines',
        description: 'Édition des fiches de paie, calcul automatisé de l\'ITS, VPS, et télédéclarations CNSS. Suivi des congés.',
        price: '5 000 FCFA / employé / mois',
        icon: 'bi-people',
        color: '#f59e0b'
    },
    {
        id: 4,
        title: 'Certification des Comptes (CAC)',
        category: 'Audit',
        description: 'Mission de commissariat aux comptes pour garantir la régularité et la sincérité de vos états financiers.',
        price: 'Sur devis',
        icon: 'bi-shield-check',
        color: '#8b5cf6'
    },
    {
        id: 5,
        title: 'Accompagnement e-MECeF',
        category: 'Fiscalité',
        description: 'Assistance pour le passage à la facturation électronique normalisée de la DGI du Bénin.',
        price: 'À partir de 75 000 FCFA',
        icon: 'bi-receipt',
        color: '#ef4444'
    },
    {
        id: 6,
        title: 'Secrétariat Juridique Annuel',
        category: 'Juridique',
        description: 'Organisation des AG ordinaires, rédaction des PV, dépôt des états financiers au greffe.',
        price: 'À partir de 100 000 FCFA/an',
        icon: 'bi-journal-bookmark',
        color: '#6366f1'
    }
]);

const activeCategory = ref('Tous');
const categories = ['Tous', 'Comptabilité', 'Fiscalité', 'Juridique', 'Ressources Humaines', 'Audit'];

const filteredServices = computed(() => {
    if (activeCategory.value === 'Tous') return services.value;
    return services.value.filter(s => s.category === activeCategory.value);
});

// Import computed to fix reactivity
import { computed } from 'vue';

</script>

<template>
    <PublicLayout title="Nos Services | GEL Cabinet">
        <div class="catalogue">
            
            <!-- Hero Section -->
            <section class="catalogue-hero">
                <div class="catalogue-hero__content">
                    <h1 class="catalogue-hero__title">Découvrez nos <span class="text-gradient">services premium</span></h1>
                    <p class="catalogue-hero__subtitle">
                        Une expertise locale et internationale pour propulser votre entreprise. Choisissez le service qui correspond à vos besoins.
                    </p>
                </div>
            </section>

            <!-- Catalogue Content -->
            <section class="catalogue-main">
                <div class="catalogue-container">
                    
                    <!-- Filters -->
                    <div class="catalogue-filters">
                        <button 
                            v-for="cat in categories" 
                            :key="cat"
                            :class="['filter-btn', { 'is-active': activeCategory === cat }]"
                            @click="activeCategory = cat"
                        >
                            {{ cat }}
                        </button>
                    </div>

                    <!-- Services Grid -->
                    <div class="services-grid">
                        <div v-for="service in filteredServices" :key="service.id" class="service-card">
                            <div class="service-card__icon-wrap" :style="{ backgroundColor: service.color + '15' }">
                                <i :class="[service.icon, 'service-card__icon']" :style="{ color: service.color }"></i>
                            </div>
                            <div class="service-card__category" :style="{ color: service.color }">{{ service.category }}</div>
                            <h3 class="service-card__title">{{ service.title }}</h3>
                            <p class="service-card__desc">{{ service.description }}</p>
                            
                            <div class="service-card__footer">
                                <div class="service-card__price">{{ service.price }}</div>
                                <a :href="'/catalogue/' + service.id" class="service-card__btn">En savoir plus <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

        </div>
    </PublicLayout>
</template>

<style scoped>
.catalogue {
    background: #f8fafc;
    min-height: calc(100vh - 80px);
}

/* Hero */
.catalogue-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    padding: 6rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.catalogue-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.15), transparent 50%);
    pointer-events: none;
}

.catalogue-hero__content {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.catalogue-hero__title {
    font-size: 3.5rem;
    font-weight: 800;
    color: white;
    margin-bottom: 1.5rem;
    letter-spacing: -0.025em;
    line-height: 1.1;
}

.text-gradient {
    background: linear-gradient(135deg, #60a5fa, #a78bfa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.catalogue-hero__subtitle {
    font-size: 1.25rem;
    color: #94a3b8;
    line-height: 1.6;
    max-width: 600px;
    margin: 0 auto;
}

/* Main */
.catalogue-main {
    padding: 4rem 2rem;
}

.catalogue-container {
    max-width: 1280px;
    margin: 0 auto;
}

/* Filters */
.catalogue-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
    margin-bottom: 3rem;
}

.filter-btn {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 1.25rem;
    border-radius: 999px;
    font-size: 0.95rem;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.filter-btn:hover {
    border-color: #cbd5e1;
    color: #0f172a;
    transform: translateY(-1px);
}

.filter-btn.is-active {
    background: #0f172a;
    color: white;
    border-color: #0f172a;
    box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
}

/* Grid */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

/* Card */
.service-card {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    border: 1px solid rgba(226, 232, 240, 0.5);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: #cbd5e1;
}

.service-card__icon-wrap {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.service-card__icon {
    font-size: 1.75rem;
}

.service-card__category {
    font-size: 0.875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.service-card__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.service-card__desc {
    color: #64748b;
    line-height: 1.6;
    font-size: 0.95rem;
    margin-bottom: 2rem;
    flex: 1;
}

.service-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1.5rem;
    border-top: 1px solid #f1f5f9;
}

.service-card__price {
    font-weight: 600;
    color: #334155;
    font-size: 0.95rem;
}

.service-card__btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: color 0.2s;
}

.service-card__btn:hover {
    color: #1d4ed8;
}

.service-card__btn i {
    transition: transform 0.2s;
}

.service-card__btn:hover i {
    transform: translateX(4px);
}

@media (max-width: 768px) {
    .catalogue-hero__title {
        font-size: 2.5rem;
    }
    .services-grid {
        grid-template-columns: 1fr;
    }
}
</style>
