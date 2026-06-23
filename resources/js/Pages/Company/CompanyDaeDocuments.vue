<template>
    <CompanyLayout>
        <div class="container-fluid py-4">
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
            <template v-else>
                <!-- Header -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-folder me-2 text-primary"></i>Documents</h4>
                        <p class="text-muted small mb-0">Gestion documentaire et archivage</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select v-model="filterType" class="form-select form-select-sm" style="width:auto;" @change="fetchData(1)">
                            <option value="">Tous les types</option>
                            <option value="rapport">Rapport</option>
                            <option value="pv">PV</option>
                            <option value="commercial">Commercial</option>
                            <option value="modele">Modèle</option>
                            <option value="inventaire">Inventaire</option>
                            <option value="interne">Interne</option>
                            <option value="contrat">Contrat</option>
                            <option value="facture">Facture</option>
                        </select>
                        <select v-model="filterCategorie" class="form-select form-select-sm" style="width:auto;" @change="fetchData(1)">
                            <option value="">Toutes catégories</option>
                            <option value="Comptabilité">Comptabilité</option>
                            <option value="Juridique">Juridique</option>
                            <option value="Marketing">Marketing</option>
                            <option value="IT">IT</option>
                            <option value="Direction">Direction</option>
                            <option value="RH">RH</option>
                        </select>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row g-2 mb-4">
                    <div class="col-auto" v-for="(stat, key) in stats" :key="key">
                        <div class="card border-0 shadow-sm px-3 py-2">
                            <small class="text-muted">{{ stat.label }}</small>
                            <span class="fw-bold fs-5">{{ stat.count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <DaeDataTable
                            :columns="columns"
                            :rows="rows"
                            :currentPage="currentPage"
                            :totalPages="totalPages"
                            :actions="actions"
                            @page-change="fetchData"
                            @action="handleAction"
                        >
                            <template #cell-statut="{ row }">
                                <span class="badge" :class="row.statut === 'final' ? 'bg-success' : 'bg-secondary'">{{ row.statut }}</span>
                            </template>
                            <template #cell-type_document="{ row }">
                                <span class="badge bg-light text-dark">{{ row.type_document }}</span>
                            </template>
                            <template #cell-taille_fichier="{ row }">
                                <small>{{ formatTaille(row.taille_fichier) }}</small>
                            </template>
                        </DaeDataTable>
                    </div>
                </div>
            </template>
        </div>

        <div v-if="toast" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
            <div class="toast show align-items-center border-0" :class="toast.type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi me-2" :class="toast.type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'"></i>{{ toast.message }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toast = null"></button>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<script>
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import DaeDataTable from '../../Components/Dae/DaeDataTable.vue';

export default {
    components: { CompanyLayout, DaeDataTable },
    data() {
        return {
            loading: true,
            rows: [],
            currentPage: 1,
            totalPages: 1,
            filterType: '',
            filterCategorie: '',
            stats: {
                total: { label: 'Total', count: 0 },
                final: { label: 'Finalisés', count: 0 },
                brouillon: { label: 'Brouillons', count: 0 },
            },
            toast: null,
            columns: [
                { key: 'reference', label: 'Réf.', width: '120px' },
                { key: 'titre', label: 'Titre' },
                { key: 'type_document', label: 'Type', width: '110px' },
                { key: 'categorie', label: 'Catégorie', width: '120px' },
                { key: 'version', label: 'Version', width: '80px', class: 'text-center' },
                { key: 'taille_fichier', label: 'Taille', width: '90px' },
                { key: 'statut', label: 'Statut', width: '100px' },
            ],
            actions: [
                { key: 'download', label: 'Télécharger', icon: 'bi-download' },
            ],
        };
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        async fetchData(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page });
                if (this.filterType) params.append('type_document', this.filterType);
                if (this.filterCategorie) params.append('categorie', this.filterCategorie);
                const res = await fetch(`/company/dae/documents?${params}`, {
                    headers: { Accept: 'application/json' },
                });
                const json = await res.json();
                this.rows = json.data || [];
                this.currentPage = json.current_page || 1;
                this.totalPages = json.last_page || 1;
                this.stats.total.count = json.total || 0;
                this.stats.final.count = json.data?.filter(r => r.statut === 'final').length || 0;
                this.stats.brouillon.count = json.data?.filter(r => r.statut === 'brouillon').length || 0;
            } catch (e) {
                this.toast = { type: 'error', message: 'Erreur: ' + e.message };
            } finally {
                this.loading = false;
            }
        },
        async handleAction({ action, row }) {
            if (action === 'download' && row.id) {
                window.open(`/company/dae/documents/${row.id}/download`, '_blank');
            }
        },
        formatTaille(t) {
            if (!t) return '—';
            if (t < 1024) return t + ' o';
            if (t < 1048576) return (t / 1024).toFixed(0) + ' Ko';
            return (t / 1048576).toFixed(1) + ' Mo';
        },
    },
};
</script>
