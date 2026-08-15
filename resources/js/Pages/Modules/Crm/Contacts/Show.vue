<template>
    <div class="crm-contact-container">
        <!-- En-tête du contact -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        {{ contact.first_name.charAt(0) }}{{ contact.last_name.charAt(0) }}
                    </div>
                    <div>
                        <h2 class="mb-1 h4">{{ contact.first_name }} {{ contact.last_name }}</h2>
                        <p class="text-muted mb-0">
                            <i class="bi bi-building"></i> {{ contact.company || 'Aucune entreprise' }} 
                            <span v-if="contact.position"> - {{ contact.position }}</span>
                        </p>
                    </div>
                </div>
                <div>
                    <a :href="'mailto:' + contact.email" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                    <button class="btn btn-primary">
                        <i class="bi bi-telephone"></i> Appeler
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne Principale: Recommandations IA et Affaires -->
            <div class="col-lg-8">
                <!-- Insight IA -->
                <div class="card mb-4 border-primary shadow-sm" style="border-width: 2px;">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="bi bi-stars me-2 fs-5"></i> 
                        <h5 class="mb-0">Analyse IA (Revenue Intelligence)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h6 class="text-muted text-uppercase small fw-bold">Statut du Compte</h6>
                                <p class="lead mb-0">{{ aiInsight }}</p>
                                <div class="mt-3">
                                    <span class="badge bg-info text-dark rounded-pill px-3 py-2">
                                        {{ activeDealsCount }} opportunité(s) en cours
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 ps-md-4">
                                <h6 class="text-muted text-uppercase small fw-bold text-primary">Prochaine action recommandée</h6>
                                <div class="alert alert-primary mb-0 mt-2 d-flex align-items-start">
                                    <i class="bi bi-lightbulb-fill fs-4 me-3"></i>
                                    <div>
                                        <p class="mb-0 fw-bold">{{ aiAction }}</p>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-primary mt-3 w-100">Exécuter l'action</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des Affaires -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Opportunités Liées</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Titre</th>
                                        <th>Montant</th>
                                        <th>Stade</th>
                                        <th>Date prévue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="deal in contact.deals" :key="deal.id">
                                        <td class="fw-bold text-primary">{{ deal.title }}</td>
                                        <td>{{ formatCurrency(deal.amount) }}</td>
                                        <td><span class="badge bg-secondary">{{ deal.stage }}</span></td>
                                        <td>{{ formatDate(deal.expected_close_date) }}</td>
                                    </tr>
                                    <tr v-if="!contact.deals || contact.deals.length === 0">
                                        <td colspan="4" class="text-center text-muted py-4">Aucune opportunité</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Latérale: Historique des intéractions -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Historique des intéractions</h5>
                        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-plus"></i></button>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div v-for="interaction in contact.interactions" :key="interaction.id" class="timeline-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong class="text-dark">
                                        <i :class="getInteractionIcon(interaction.type)" class="me-2 text-primary"></i>
                                        {{ getInteractionTypeName(interaction.type) }}
                                    </strong>
                                    <small class="text-muted">{{ formatDate(interaction.created_at) }}</small>
                                </div>
                                <p class="mb-1 small">{{ interaction.notes }}</p>
                                <span v-if="interaction.outcome" class="badge bg-light text-dark border small">{{ interaction.outcome }}</span>
                            </div>
                            
                            <div v-if="!contact.interactions || contact.interactions.length === 0" class="text-center text-muted py-3">
                                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                Aucun historique
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    contact: {
        type: Object,
        required: true
    },
    aiInsight: {
        type: String,
        default: ''
    },
    aiAction: {
        type: String,
        default: ''
    },
    activeDealsCount: {
        type: Number,
        default: 0
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
};

const getInteractionIcon = (type) => {
    const icons = {
        'email': 'bi-envelope',
        'call': 'bi-telephone',
        'meeting': 'bi-people',
        'note': 'bi-sticky'
    };
    return icons[type] || 'bi-record-circle';
};

const getInteractionTypeName = (type) => {
    const types = {
        'email': 'Email',
        'call': 'Appel',
        'meeting': 'Rendez-vous',
        'note': 'Note'
    };
    return types[type] || type;
};
</script>

<style scoped>
.crm-contact-container {
    padding: 20px;
}
.timeline-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
</style>
