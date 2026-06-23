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
                        <h4 class="fw-bold mb-1"><i class="bi bi-list-task me-2 text-primary"></i>Tâches</h4>
                        <p class="text-muted small mb-0">Suivi des tâches et activités</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select v-model="filterStatut" class="form-select form-select-sm" style="width:auto;" @change="fetchData(1)">
                            <option value="">Tous les statuts</option>
                            <option value="a_faire">À faire</option>
                            <option value="en_cours">En cours</option>
                            <option value="en_revision">En révision</option>
                            <option value="terminee">Terminée</option>
                            <option value="annulee">Annulée</option>
                        </select>
                        <select v-model="filterPriorite" class="form-select form-select-sm" style="width:auto;" @change="fetchData(1)">
                            <option value="">Toutes priorités</option>
                            <option value="basse">Basse</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                            <option value="critique">Critique</option>
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
                            <template #cell-priorite="{ row }">
                                <span class="badge" :class="prioriteClass(row.priorite)">{{ row.priorite }}</span>
                            </template>
                            <template #cell-echeance="{ row }">
                                <small :class="row.echeance && new Date(row.echeance) < new Date() && row.statut !== 'terminee' && row.statut !== 'annulee' ? 'text-danger fw-bold' : ''">
                                    {{ formatDate(row.echeance) }}
                                </small>
                            </template>
                            <template #cell-assigned_to="{ row }">
                                <small>{{ row.assigned_to?.name || '—' }}</small>
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
            filterPriorite: '',
            stats: {
                total: { label: 'En cours', count: 0 },
                terminee: { label: 'Terminées', count: 0 },
                en_retard: { label: 'En retard', count: 0 },
            },
            toast: null,
            columns: [
                { key: 'titre', label: 'Tâche' },
                { key: 'priorite', label: 'Priorité', width: '100px' },
                { key: 'statut', label: 'Statut', width: '110px' },
                { key: 'echeance', label: 'Échéance', width: '110px' },
                { key: 'assigned_to', label: 'Assigné à', width: '150px' },
            ],
            actions: [
                { key: 'a_faire', label: 'Marquer À faire', icon: 'bi-circle' },
                { key: 'en_cours', label: 'Marquer En cours', icon: 'bi-arrow-repeat' },
                { key: 'terminee', label: 'Marquer Terminée', icon: 'bi-check2' },
                { key: 'annulee', label: 'Annuler', icon: 'bi-x', danger: true },
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
                if (this.filterPriorite) params.append('priorite', this.filterPriorite);
                const res = await fetch(`/company/dae/taches?${params}`, {
                    headers: { Accept: 'application/json' },
                });
                const json = await res.json();
                this.rows = json.data || [];
                this.currentPage = json.current_page || 1;
                this.totalPages = json.last_page || 1;
                const now = new Date();
                this.stats.total.count = json.data?.filter(r => r.statut === 'a_faire' || r.statut === 'en_cours').length || 0;
                this.stats.terminee.count = json.data?.filter(r => r.statut === 'terminee').length || 0;
                this.stats.en_retard.count = json.data?.filter(r => r.echeance && new Date(r.echeance) < now && r.statut !== 'terminee' && r.statut !== 'annulee').length || 0;
            } catch (e) {
                this.toast = { type: 'error', message: 'Erreur: ' + e.message };
            } finally {
                this.loading = false;
            }
        },
        async handleAction({ action, row }) {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            try {
                const res = await fetch(`/company/dae/taches/${row.id}/statut`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({ statut: action }),
                });
                if (res.ok) {
                    this.toast = { type: 'success', message: `Tâche mise à jour : ${this.statutLabel(action)}` };
                    this.fetchData(this.currentPage);
                }
            } catch (e) {
                this.toast = { type: 'error', message: e.message };
            }
        },
        statutClass(s) {
            const map = { a_faire: 'bg-secondary', en_cours: 'bg-info', en_revision: 'bg-warning text-dark', terminee: 'bg-success', annulee: 'bg-light text-dark' };
            return map[s] || 'bg-secondary';
        },
        statutLabel(s) {
            const map = { a_faire: 'À faire', en_cours: 'En cours', en_revision: 'En révision', terminee: 'Terminée', annulee: 'Annulée' };
            return map[s] || s;
        },
        prioriteClass(p) {
            const map = { basse: 'bg-light text-dark', moyenne: 'bg-info', haute: 'bg-warning text-dark', critique: 'bg-danger' };
            return map[p] || 'bg-light text-dark';
        },
        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
        },
    },
};
</script>
