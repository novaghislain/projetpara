<template>
    <div class="crm-kanban-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800">Pipeline des Opportunités (IA)</h2>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    {{ totalDeals }} opportunité(s)
                </span>
            </div>
        </div>

        <FilterChips 
            :filters="activeFilters" 
            @remove="removeFilter" 
            @clearAll="clearFilters" 
        />

        <div class="kanban-board">
            <!-- Colonnes Kanban -->
            <div class="kanban-column" v-for="(deals, stageKey) in columns" :key="stageKey">
                <div class="column-header">
                    <h5 class="column-title">{{ getStageName(stageKey) }}</h5>
                    <span class="column-badge">{{ deals.length }}</span>
                </div>
                
                <div class="column-body">
                    <div class="kanban-card" v-for="deal in deals" :key="deal.id">
                        <div class="card-header-custom">
                            <h6 class="deal-title">{{ deal.title }}</h6>
                            <span :class="'badge bg-' + deal.ai_color">{{ deal.ai_temperature === 'hot' ? '🔥' : (deal.ai_temperature === 'warm' ? '🟡' : '🧊') }} {{ deal.ai_score }}%</span>
                        </div>
                        <div class="card-body-custom mt-2">
                            <p class="deal-contact mb-1 text-muted small"><i class="bi bi-person"></i> {{ deal.contact }}</p>
                            <p class="deal-amount fw-bold mb-2">{{ formatCurrency(deal.amount) }}</p>
                            
                            <div class="ai-insight alert alert-secondary p-2 mb-0" style="font-size: 0.8rem;">
                                <strong><i class="bi bi-robot text-primary"></i> IA Recommande:</strong>
                                <p class="mb-0 mt-1">{{ deal.ai_next_action }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="deals.length === 0" class="empty-column text-muted text-center py-4 small">
                        Aucune opportunité
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import FilterChips from '../../../../Components/FilterChips.vue';

// Mock filters for demonstration
const activeFilters = ref([
    { key: 'status', label: 'Statut', value: 'En cours' },
    { key: 'temperature', label: 'Température', value: 'Chaud (Hot)' }
]);

const removeFilter = (key) => {
    activeFilters.value = activeFilters.value.filter(f => f.key !== key);
};

const clearFilters = () => {
    activeFilters.value = [];
};

const props = defineProps({
    columns: {
        type: Object,
        required: true
    },
    totalDeals: {
        type: Number,
        default: 0
    }
});

const getStageName = (stage) => {
    const stages = {
        'prospecting': 'Prospection',
        'qualification': 'Qualification',
        'proposal': 'Proposition',
        'negociation': 'Négociation',
        'closing': 'Gagné / Perdu'
    };
    return stages[stage] || stage;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value || 0);
};
</script>

<style scoped>
.crm-kanban-container {
    padding: 20px;
}

.kanban-board {
    display: flex;
    overflow-x: auto;
    gap: 1.5rem;
    padding-bottom: 1rem;
    min-height: 70vh;
}

.kanban-column {
    flex: 0 0 320px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    display: flex;
    flex-direction: column;
}

.column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    margin-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.column-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.column-badge {
    background: #e9ecef;
    color: #495057;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.kanban-card {
    background: white;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.deal-title {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
    color: #212529;
}

.ai-insight {
    background-color: #f8f9fa;
    border-left: 3px solid #0d6efd;
}
</style>
