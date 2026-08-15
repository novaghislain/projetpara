<script setup>
import { ref, onMounted } from 'vue';
import PublicLayout from '../../Layouts/Public/PublicLayout.vue';

// On utiliserait normalement les props d'Inertia, on simule les données pour l'instant
const props = defineProps({
    service: {
        type: Object,
        default: () => ({
            id: 1,
            title: "Création d'entreprise (SARL, SAS, SUARL)",
            category: "Juridique",
            description: "Nous vous accompagnons de A à Z dans la création de votre entreprise au Bénin.",
            price: "À partir de 150 000 FCFA",
            icon: "bi-building",
            color: "#3b82f6",
            features: [
                "Rédaction des statuts sur mesure",
                "Immatriculation au RCCM",
                "Obtention du numéro IFU",
                "Déclaration d'existence à la DGI",
                "Frais d'agence APIEx inclus",
                "Accompagnement ouverture de compte bancaire"
            ],
            duration: "3 à 5 jours ouvrés",
            deliverables: [
                "Statuts signés et enregistrés",
                "Extrait RCCM",
                "Carte d'importateur (si applicable)"
            ]
        })
    }
});
</script>

<template>
    <PublicLayout :title="service.title + ' | GEL Cabinet'">
        <div class="service-detail">
            
            <!-- Hero Header -->
            <div class="service-hero" :style="{ background: `linear-gradient(135deg, ${service.color}22, ${service.color}11)` }">
                <div class="service-hero__container">
                    <a href="/catalogue" class="back-link"><i class="bi bi-arrow-left"></i> Retour au catalogue</a>
                    <div class="service-hero__content">
                        <div class="service-hero__icon" :style="{ backgroundColor: service.color, color: 'white' }">
                            <i :class="service.icon"></i>
                        </div>
                        <div class="service-hero__text">
                            <div class="service-category" :style="{ color: service.color }">{{ service.category }}</div>
                            <h1 class="service-title">{{ service.title }}</h1>
                            <p class="service-desc">{{ service.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="service-content">
                <div class="service-content__container">
                    
                    <div class="service-main">
                        <div class="detail-section">
                            <h2 class="detail-title">Ce qui est inclus</h2>
                            <ul class="feature-list">
                                <li v-for="(feature, idx) in service.features" :key="idx" class="feature-item">
                                    <i class="bi bi-check-circle-fill feature-icon" :style="{ color: service.color }"></i>
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h2 class="detail-title">Livrables</h2>
                            <ul class="deliverable-list">
                                <li v-for="(doc, idx) in service.deliverables" :key="idx">
                                    <i class="bi bi-file-earmark-text"></i> {{ doc }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Sidebar Pricing Card -->
                    <div class="service-sidebar">
                        <div class="pricing-card">
                            <div class="pricing-card__header">
                                <h3 class="pricing-title">Tarification</h3>
                                <div class="pricing-amount">{{ service.price }}</div>
                            </div>
                            
                            <div class="pricing-card__body">
                                <div class="pricing-info">
                                    <i class="bi bi-clock-history"></i>
                                    <span><strong>Délai estimé :</strong> {{ service.duration }}</span>
                                </div>
                                <div class="pricing-info">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Garantie de conformité OHADA</span>
                                </div>
                                
                                <a :href="`/order/wizard?service_id=${service.id}`" class="order-btn" :style="{ backgroundColor: service.color }">
                                    Commander ce service
                                </a>
                                <button class="quote-btn">Demander un devis</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </PublicLayout>
</template>

<style scoped>
.service-detail {
    min-height: calc(100vh - 80px);
    background: #f8fafc;
}

/* Hero */
.service-hero {
    padding: 3rem 2rem 4rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.service-hero__container {
    max-width: 1280px;
    margin: 0 auto;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 2rem;
    transition: color 0.2s;
}

.back-link:hover {
    color: #0f172a;
}

.service-hero__content {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

.service-hero__icon {
    width: 5rem;
    height: 5rem;
    border-radius: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    flex-shrink: 0;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.service-category {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
}

.service-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.service-desc {
    font-size: 1.125rem;
    color: #475569;
    line-height: 1.6;
    max-width: 700px;
}

/* Content Layout */
.service-content {
    padding: 4rem 2rem;
}

.service-content__container {
    max-width: 1280px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 4rem;
    align-items: start;
}

/* Details */
.detail-section {
    background: white;
    padding: 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    margin-bottom: 2rem;
}

.detail-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f1f5f9;
}

/* Lists */
.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    font-size: 1.05rem;
    color: #334155;
    line-height: 1.5;
}

.feature-icon {
    font-size: 1.25rem;
    margin-top: 2px;
}

.deliverable-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.deliverable-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.05rem;
    color: #475569;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

/* Pricing Sidebar */
.pricing-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    position: sticky;
    top: 6rem;
}

.pricing-card__header {
    background: #f8fafc;
    padding: 2rem;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
}

.pricing-title {
    color: #64748b;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.pricing-amount {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0f172a;
}

.pricing-card__body {
    padding: 2rem;
}

.pricing-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    color: #475569;
    font-size: 0.95rem;
}

.pricing-info i {
    font-size: 1.25rem;
    color: #64748b;
}

.order-btn {
    display: block;
    width: 100%;
    padding: 1rem;
    border-radius: 0.5rem;
    color: white;
    text-align: center;
    font-weight: 600;
    text-decoration: none;
    margin-top: 2rem;
    margin-bottom: 1rem;
    transition: all 0.2s;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.order-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
}

.quote-btn {
    display: block;
    width: 100%;
    padding: 1rem;
    border-radius: 0.5rem;
    background: transparent;
    color: #0f172a;
    border: 1px solid #cbd5e1;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.quote-btn:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}

@media (max-width: 1024px) {
    .service-content__container {
        grid-template-columns: 1fr;
    }
    .service-hero__content {
        flex-direction: column;
    }
    .feature-list {
        grid-template-columns: 1fr;
    }
}
</style>
