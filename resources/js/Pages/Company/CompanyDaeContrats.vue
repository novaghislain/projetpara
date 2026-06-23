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
                        <h4 class="fw-bold mb-1"><i class="bi bi-file-text me-2 text-primary"></i>Contrats</h4>
                        <p class="text-muted small mb-0">Gestion des contrats et conventions</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select v-model="filterStatut" class="form-select form-select-sm" style="width:auto;" @change="fetchData(1)">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="brouillon">Brouillon</option>
                            <option value="expire">Expiré</option>
                            <option value="resilie">Résilié</option>
                            <option value="renouvele">Renouvelé</option>
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
                                <span class="badge" :class="statutClass(row.statut)">{{ statutLabel(row.statut) }}</span>
                            </template>
                            <template #cell-montant="{ row }">
                                <span class="fw-medium">{{ formatMontant(row.montant) }}</span>
                            </template>
                            <template #cell-date_fin="{ row }">
                                <small :class="row.date_fin && new Date(row.date_fin) < new Date() && row.statut === 'actif' ? 'text-danger fw-bold' : ''">
                                    {{ formatDate(row.date_fin) }}
                                </small>
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
            filterStatut: '',
            stats: {
                total: { label: 'Total', count: 0 },
                actif: { label: 'Actifs', count: 0 },
                expire: { label: 'Expirés', count: 0 },
            },
            toast: null,
            columns: [
                { key: 'reference', label: 'Réf.', width: '120px' },
                { key: 'titre', label: 'Titre' },
                { key: 'partie_adverse', label: 'Partie adverse' },
                { key: 'montant', label: 'Montant', width: '130px' },
                { key: 'date_fin', label: 'Échéance', width: '110px' },
                { key: 'statut', label: 'Statut', width: '110px' },
            ],
            actions: [
                { key: 'view', label: 'Voir le détail', icon: 'bi-eye' },
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
                if (this.filterStatut) params.append('statut', this.filterStatut);
                const res = await fetch(`/company/dae/contrats?${params}`, {
                    headers: { Accept: 'application/json' },
                });
                const json = await res.json();
                this.rows = json.data || [];
                this.currentPage = json.current_page || 1;
                this.totalPages = json.last_page || 1;
                this.stats.total.count = json.total || 0;
                this.stats.actif.count = json.data?.filter(r => r.statut === 'actif').length || 0;
                this.stats.expire.count = json.data?.filter(r => r.statut === 'expire').length || 0;
            } catch (e) {
                this.toast = { type: 'error', message: 'Erreur: ' + e.message };
            } finally {
                this.loading = false;
            }
        },
        async handleAction({ action, row }) {
            if (action === 'view') {
                window.location.href = `/company/dae/contrats/${row.id}`;
            }
        },
        statutClass(s) {
            const map = { brouillon: 'bg-secondary', actif: 'bg-success', expire: 'bg-danger', resilie: 'bg-warning text-dark', renouvele: 'bg-info' };
            return map[s] || 'bg-secondary';
        },
        statutLabel(s) {
            const map = { brouillon: 'Brouillon', actif: 'Actif', expire: 'Expiré', resilie: 'Résilié', renouvele: 'Renouvelé' };
            return map[s] || s;
        },
        formatMontant(m) {
            if (!m) return '—';
            return Number(m).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' F';
        },
        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
        },
    },
};
</script>
