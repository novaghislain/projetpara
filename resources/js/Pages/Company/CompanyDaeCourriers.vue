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
                        <h4 class="fw-bold mb-1"><i class="bi bi-envelope me-2 text-primary"></i>Courriers</h4>
                        <p class="text-muted small mb-0">Gestion des courriers entrants, sortants et internes</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select v-model="filterStatut" class="form-select form-select-sm" style="width:auto;" @change="fetchData(1)">
                            <option value="">Tous les statuts</option>
                            <option value="brouillon">Brouillon</option>
                            <option value="envoye">Envoyé</option>
                            <option value="recu">Reçu</option>
                            <option value="traite">Traité</option>
                            <option value="archive">Archivé</option>
                        </select>
                        <a href="/company/dae/courriers?action=create" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau courrier
                        </a>
                    </div>
                </div>

                <!-- Stats cards -->
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
                            <template #cell-urgence="{ row }">
                                <span class="badge" :class="urgenceClass(row.urgence)">{{ row.urgence?.replace('_', ' ') }}</span>
                            </template>
                            <template #cell-date_courrier="{ row }">
                                <small>{{ formatDate(row.date_courrier) }}</small>
                            </template>
                        </DaeDataTable>
                    </div>
                </div>
            </template>
        </div>

        <!-- Toast de notification -->
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
                recu: { label: 'Non traités', count: 0 },
                urgent: { label: 'Urgents', count: 0 },
            },
            toast: null,
            columns: [
                { key: 'reference', label: 'Réf.', width: '120px' },
                { key: 'objet', label: 'Objet' },
                { key: 'expediteur', label: 'Expéditeur' },
                { key: 'date_courrier', label: 'Date' },
                { key: 'statut', label: 'Statut', width: '110px' },
                { key: 'urgence', label: 'Urgence', width: '100px' },
            ],
            actions: [
                { key: 'view', label: 'Voir le détail', icon: 'bi-eye' },
                { key: 'traiter', label: 'Marquer traité', icon: 'bi-check2' },
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
                const res = await fetch(`/company/dae/courriers?${params}`, {
                    headers: { Accept: 'application/json' },
                });
                const json = await res.json();
                this.rows = json.data || [];
                this.currentPage = json.current_page || 1;
                this.totalPages = json.last_page || 1;
                this.stats.total.count = json.total || 0;
                this.stats.recu.count = json.data?.filter(r => r.statut === 'recu' || r.statut === 'brouillon').length || 0;
                this.stats.urgent.count = json.data?.filter(r => r.urgence === 'urgent' || r.urgence === 'tre_urgent').length || 0;
            } catch (e) {
                this.toast = { type: 'error', message: 'Erreur de chargement: ' + e.message };
            } finally {
                this.loading = false;
            }
        },
        async handleAction({ action, row }) {
            if (action === 'view') {
                window.location.href = `/company/dae/courriers/${row.id}`;
            } else if (action === 'traiter') {
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content;
                    const res = await fetch(`/company/dae/courriers/${row.id}/traiter`, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    });
                    if (res.ok) {
                        this.toast = { type: 'success', message: 'Courrier marqué comme traité' };
                        this.fetchData(this.currentPage);
                    }
                } catch (e) {
                    this.toast = { type: 'error', message: e.message };
                }
            }
        },
        statutClass(s) {
            const map = { brouillon: 'bg-secondary', envoye: 'bg-info', recu: 'bg-warning text-dark', traite: 'bg-success', archive: 'bg-light text-dark' };
            return map[s] || 'bg-secondary';
        },
        statutLabel(s) {
            const map = { brouillon: 'Brouillon', envoye: 'Envoyé', recu: 'Reçu', traite: 'Traité', archive: 'Archivé' };
            return map[s] || s;
        },
        urgenceClass(u) {
            const map = { normal: 'bg-light text-dark', urgent: 'bg-warning text-dark', tre_urgent: 'bg-danger' };
            return map[u] || 'bg-light text-dark';
        },
        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
        },
    },
};
</script>
