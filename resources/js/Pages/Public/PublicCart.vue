<script setup>
import { ref, computed } from 'vue';
import PublicLayout from '../../Layouts/Public/PublicLayout.vue';

// Données mockées pour le panier
const cartItems = ref([
    {
        id: 1,
        title: "Création d'entreprise (SARL, SAS, SUARL)",
        company: "Acme Corp",
        price: 150000,
        type: "Prestation de service"
    },
    {
        id: 2,
        title: "Tenue Comptable Annuelle",
        company: "Acme Corp",
        price: 600000,
        type: "Abonnement (50k/mois)"
    }
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(value);
};

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + item.price, 0);
});

const tax = computed(() => {
    return subtotal.value * 0.18; // TVA 18% Bénin
});

const total = computed(() => {
    return subtotal.value + tax.value;
});

const removeItem = (id) => {
    cartItems.value = cartItems.value.filter(item => item.id !== id);
};

</script>

<template>
    <PublicLayout title="Mon Panier | GEL Cabinet">
        <div class="cart-page">
            <div class="cart-container">
                
                <div class="cart-header">
                    <h1 class="cart-title">Votre Panier</h1>
                    <p class="cart-subtitle">Revoyez les services que vous avez sélectionnés avant de valider.</p>
                </div>

                <div v-if="cartItems.length > 0" class="cart-layout">
                    <!-- Items List -->
                    <div class="cart-items">
                        <div v-for="item in cartItems" :key="item.id" class="cart-item">
                            <div class="item-icon">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <div class="item-details">
                                <h3 class="item-title">{{ item.title }}</h3>
                                <div class="item-meta">
                                    <span class="meta-tag">{{ item.type }}</span>
                                    <span class="meta-company"><i class="bi bi-building"></i> {{ item.company }}</span>
                                </div>
                            </div>
                            <div class="item-price-action">
                                <div class="item-price">{{ formatCurrency(item.price) }}</div>
                                <button class="btn-remove" @click="removeItem(item.id)" title="Retirer">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="cart-summary-wrapper">
                        <div class="cart-summary">
                            <h2 class="summary-title">Résumé de la commande</h2>
                            
                            <div class="summary-row">
                                <span class="summary-label">Sous-total HT</span>
                                <span class="summary-value">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">TVA (18%)</span>
                                <span class="summary-value">{{ formatCurrency(tax) }}</span>
                            </div>
                            
                            <div class="summary-total">
                                <span class="total-label">Total TTC</span>
                                <span class="total-value">{{ formatCurrency(total) }}</span>
                            </div>

                            <a href="/saas/register" class="btn-checkout">
                                Valider la commande
                            </a>
                            <a href="/catalogue" class="btn-continue">
                                Ajouter un autre service
                            </a>
                            
                            <div class="secure-payment">
                                <i class="bi bi-shield-lock-fill"></i>
                                Paiement sécurisé via portail client
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="cart-empty">
                    <div class="empty-icon">
                        <i class="bi bi-cart-x"></i>
                    </div>
                    <h2 class="empty-title">Votre panier est vide</h2>
                    <p class="empty-desc">Découvrez nos services d'accompagnement pour propulser votre entreprise.</p>
                    <a href="/catalogue" class="btn-primary">Parcourir le catalogue</a>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.cart-page {
    background: #f8fafc;
    min-height: calc(100vh - 80px);
    padding: 3rem 2rem 5rem;
}

.cart-container {
    max-width: 1200px;
    margin: 0 auto;
}

.cart-header {
    margin-bottom: 2.5rem;
}

.cart-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.cart-subtitle {
    color: #64748b;
    font-size: 1.125rem;
}

/* Layout */
.cart-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    align-items: start;
}

/* Items */
.cart-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.cart-item {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    transition: transform 0.2s, box-shadow 0.2s;
}

.cart-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
}

.item-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 0.75rem;
    background: #eff6ff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}

.item-details {
    flex: 1;
}

.item-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.meta-tag {
    background: #f1f5f9;
    color: #475569;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 600;
}

.meta-company {
    color: #64748b;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.item-price-action {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1rem;
}

.item-price {
    font-weight: 800;
    font-size: 1.25rem;
    color: #0f172a;
}

.btn-remove {
    background: transparent;
    border: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 1.15rem;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: background 0.2s;
}

.btn-remove:hover {
    background: #fef2f2;
}

/* Summary */
.cart-summary-wrapper {
    position: sticky;
    top: 6rem;
}

.cart-summary {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
}

.summary-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #0f172a;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    color: #475569;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px dashed #e2e8f0;
}

.total-label {
    font-weight: 700;
    color: #0f172a;
    font-size: 1.15rem;
}

.total-value {
    font-weight: 800;
    font-size: 1.5rem;
    color: #3b82f6;
}

.btn-checkout {
    display: block;
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    text-align: center;
    text-decoration: none;
    font-weight: 700;
    border-radius: 0.5rem;
    margin-top: 2rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
}

.btn-continue {
    display: block;
    width: 100%;
    padding: 1rem;
    background: transparent;
    color: #0f172a;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    border-radius: 0.5rem;
    border: 1px solid #cbd5e1;
    transition: background 0.2s;
}

.btn-continue:hover {
    background: #f1f5f9;
}

.secure-payment {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.85rem;
}

.secure-payment i {
    color: #10b981;
}

/* Empty State */
.cart-empty {
    background: white;
    border-radius: 1rem;
    padding: 5rem 2rem;
    text-align: center;
    border: 1px dashed #cbd5e1;
}

.empty-icon {
    font-size: 4rem;
    color: #94a3b8;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.empty-desc {
    color: #64748b;
    margin-bottom: 2rem;
}

.btn-primary {
    display: inline-block;
    padding: 0.75rem 2rem;
    background: #0f172a;
    color: white;
    text-decoration: none;
    font-weight: 600;
    border-radius: 0.5rem;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #1e293b;
}

@media (max-width: 900px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
}
</style>
