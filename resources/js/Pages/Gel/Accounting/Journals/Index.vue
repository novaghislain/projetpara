<template>
    <GelLayout pageTitle="Journaux Comptables">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Journaux Comptables</h2>
                    <p class="text-muted mb-0">Gestion des journaux auxiliaires</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Nouveau Journal
                </button>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4" v-for="journal in journals" :key="journal.id">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <span class="text-primary me-2">{{ journal.code }}</span>
                                    {{ journal.name }}
                                </h5>
                                <span class="badge" :class="getBadgeClass(journal.type)">{{ journal.type }}</span>
                            </div>
                            <p class="text-muted small mb-3">Dernière écriture le {{ journal.last_entry }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ journal.entries_count }} écritures ce mois</span>
                                <a :href="`/accounting/entries?journal=${journal.code}`" class="btn btn-sm btn-outline-primary">Saisir</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const journals = ref([
    { id: 1, code: 'ACH', name: 'Achats', type: 'Achats', last_entry: '14/08/2026', entries_count: 42 },
    { id: 2, code: 'VTE', name: 'Ventes', type: 'Ventes', last_entry: '15/08/2026', entries_count: 128 },
    { id: 3, code: 'BQ1', name: 'Banque - BGFIBank', type: 'Trésorerie', last_entry: '15/08/2026', entries_count: 15 },
    { id: 4, code: 'OD', name: 'Opérations Diverses', type: 'Opérations Diverses', last_entry: '31/07/2026', entries_count: 5 },
    { id: 5, code: 'RAN', name: 'A-Nouveaux', type: 'Général', last_entry: '01/01/2026', entries_count: 1 },
]);

const getBadgeClass = (type) => {
    switch (type) {
        case 'Achats': return 'bg-danger';
        case 'Ventes': return 'bg-success';
        case 'Trésorerie': return 'bg-info text-dark';
        case 'Opérations Diverses': return 'bg-secondary';
        default: return 'bg-dark';
    }
};
</script>
