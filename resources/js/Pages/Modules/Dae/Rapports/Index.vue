<template>
    <div class="dae-rapports-index">
        <div v-if="loading && !items.length" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement des rapports...</p>
        </div>

        <div v-else class="dae-content">
            <!-- Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour au tableau de bord">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                            <i class="bi bi-graph-up me-2 text-primary"></i>Rapports
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i>Génération et gestion des rapports DAE
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} rapport(s)</span>
                        </p>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm mt-2 mt-md-0" @click="openGenererModal">
                    <i class="bi bi-plus-lg me-1"></i>Générer un rapport
                </button>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Type</label>
                            <select v-model="filters.type_rapport" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Tous</option>
                                <option value="activite">Activité</option>
                                <option value="financier">Financier</option>
                                <option value="rh">Ressources humaines</option>
                                <option value="conformite">Conformité</option>
                                <option value="mission">Mission</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Statut</label>
                            <select v-model="filters.statut" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Tous</option>
                                <option value="brouillon">Brouillon</option>
                                <option value="genere">Généré</option>
                                <option value="finalise">Finalisé</option>
                                <option value="envoye">Envoyé</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Client</label>
                            <select v-model="filters.client_id" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Tous</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom || c.email }}</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <input type="text" v-model="filters.recherche" class="form-control"
                                       placeholder="Rechercher..." @keyup.enter="applyFilters" />
                                <button class="btn btn-primary" @click="applyFilters"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <DaeDataTable
                        :columns="tableColumns"
                        :rows="items"
                        :actions="tableActions"
                        :currentPage="currentPage"
                        :totalPages="totalPages"
                        :rowClickable="true"
                        @row-click="handleRowClick"
                        @action="handleAction"
                        @page-change="changePage"
                    >
                        <template #cell-type_rapport="{ row }">
                            {{ typeLabel(row.type_rapport) }}
                        </template>
                        <template #cell-statut="{ row }">
                            <span class="badge" :class="statutBadge(row.statut)">{{ statutLabel(row.statut) }}</span>
                        </template>
                        <template #cell-created_at="{ row }">
                            {{ formatDate(row.created_at) }}
                        </template>
                    </DaeDataTable>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════
             MODAL: Générer un rapport
             ════════════════════════════════════════════════ -->
        <div ref="genererModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-file-earmark-plus me-2 text-primary"></i>Générer un rapport
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="submitGenerer">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small text-muted">Titre <span class="text-danger">*</span></label>
                                    <input v-model="form.titre" class="form-control form-control-sm" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Type <span class="text-danger">*</span></label>
                                    <select v-model="form.type_rapport" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="activite">Activité</option>
                                        <option value="financier">Financier</option>
                                        <option value="rh">Ressources humaines</option>
                                        <option value="conformite">Conformité</option>
                                        <option value="mission">Mission</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Client <span class="text-danger">*</span></label>
                                    <select v-model="form.client_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom || c.email }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Période début</label>
                                    <input v-model="form.periode_debut" type="date" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Période fin</label>
                                    <input v-model="form.periode_fin" type="date" class="form-control form-control-sm" />
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Description</label>
                                    <textarea v-model="form.description" class="form-control form-control-sm" rows="3"></textarea>
                                </div>
                            </div>
                            <div v-if="formError" class="alert alert-danger small mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ formError }}
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="formLoading">
                                <span v-if="formLoading">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Génération...
                                </span>
                                <span v-else><i class="bi bi-check-lg me-1"></i>Générer</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';
import DaeDataTable from '../../../../Components/Dae/DaeDataTable.vue';

const STATUT_MAP = {
    brouillon: { label: 'Brouillon', badge: 'bg-secondary' },
    genere:    { label: 'Généré',    badge: 'bg-primary' },
    finalise:  { label: 'Finalisé',  badge: 'bg-success' },
    envoye:    { label: 'Envoyé',    badge: 'bg-info' },
};

export default {
    name: 'DaeRapportsIndex',
    components: { DaeDataTable },
    data() {
        return {
            loading: true,
            items: [],
            clients: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            filters: { type_rapport: '', statut: '', client_id: '', recherche: '' },
            // Generer form
            genererModalInstance: null,
            form: {
                client_id: '', titre: '', type_rapport: '',
                description: '', periode_debut: '', periode_fin: '',
            },
            formLoading: false,
            formError: '',
        };
    },
    computed: {
        tableColumns() {
            return [
                { key: 'titre', label: 'Titre', class: 'text-truncate', width: '30%' },
                { key: 'type_rapport', label: 'Type' },
                { key: 'statut', label: 'Statut' },
                { key: 'created_at', label: 'Date' },
            ];
        },
        tableActions() {
            return [
                { key: 'voir', label: 'Voir', icon: 'bi-eye' },
                { key: 'telecharger', label: 'Télécharger', icon: 'bi-download' },
                { key: 'supprimer', label: 'Supprimer', icon: 'bi-trash', danger: true },
            ];
        },
    },
    created() {
        Promise.all([this.fetchClients(), this.fetchItems()]).finally(() => { this.loading = false; });
    },
    methods: {
        async fetchClients() {
            try { const r = await axios.get('/api/clients'); this.clients = Array.isArray(r.data) ? r.data : (r.data.data || []); }
            catch (e) { console.error('Erreur clients:', e); }
        },
        async fetchItems() {
            try {
                const params = { page: this.currentPage, ...this.filters };
                Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
                const r = await axios.get('/dae/rapports', { params });
                const d = r.data;
                this.items = d.data || [];
                this.currentPage = d.current_page || 1;
                this.totalPages = d.last_page || 1;
                this.totalItems = d.total || 0;
            } catch (e) {
                console.error('Erreur rapports:', e);
                this.items = []; this.totalPages = 1; this.totalItems = 0;
            }
        },
        applyFilters() { this.currentPage = 1; this.fetchItems(); },
        changePage(p) { this.currentPage = p; this.fetchItems(); },
        handleRowClick(row) { window.location.href = `/dae/rapports/${row.id}`; },
        handleAction({ action, row }) {
            if (action === 'voir') window.location.href = `/dae/rapports/${row.id}`;
            if (action === 'telecharger') window.location.href = `/dae/rapports/${row.id}/telecharger`;
            if (action === 'supprimer') this.deleteItem(row);
        },
        async deleteItem(row) {
            if (!confirm(`Supprimer "${row.titre}" ?`)) return;
            try { await axios.delete(`/dae/rapports/${row.id}`); this.fetchItems(); }
            catch (e) { alert('Impossible de supprimer.'); }
        },

        // Generer modal
        openGenererModal() {
            this.formError = '';
            this.form = { client_id: '', titre: '', type_rapport: '', description: '', periode_debut: '', periode_fin: '' };
            this.genererModalInstance = new Modal(this.$refs.genererModal);
            this.genererModalInstance.show();
        },
        async submitGenerer() {
            this.formLoading = true;
            this.formError = '';
            try {
                await axios.post('/dae/rapports/generer', this.form);
                this.genererModalInstance.hide();
                this.fetchItems();
            } catch (e) {
                this.formError = e.response?.data?.message || "Erreur lors de la génération.";
            } finally { this.formLoading = false; }
        },

        // Helpers
        typeLabel(t) { const m = { activite: 'Activité', financier: 'Financier', rh: 'Ressources humaines', conformite: 'Conformité', mission: 'Mission' }; return m[t] || t || '-'; },
        statutBadge(s) { return STATUT_MAP[s]?.badge || 'bg-secondary'; },
        statutLabel(s) { return STATUT_MAP[s]?.label || s || '-'; },
        formatDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }); } catch { return d; } },
    },
};
</script>

<style scoped>
.dae-rapports-index { padding: 20px; min-height: 80vh; }
.dae-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 20px; }
.dae-spinner { width: 48px; height: 48px; border: 4px solid rgba(255,121,0,0.1); border-top-color: #FF7900; border-radius: 50%; animation: dae-spin 0.7s linear infinite; }
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
.form-label { font-weight: 500; }
</style>
