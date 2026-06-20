<template>
    <div class="dae-conformite-index">
        <div v-if="loading && !items.length" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement de la conformité...</p>
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
                            <i class="bi bi-shield-check me-2 text-primary"></i>Conformité
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-check2-square me-1"></i>Gestion de la conformité DAE
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} élément(s)</span>
                        </p>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm mt-2 mt-md-0" @click="openCreateModal">
                    <i class="bi bi-plus-lg me-1"></i>Nouvel élément
                </button>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Type</label>
                            <select v-model="filters.type" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Tous</option>
                                <option value="reglementaire">Réglementaire</option>
                                <option value="fiscal">Fiscal</option>
                                <option value="social">Social</option>
                                <option value="juridique">Juridique</option>
                                <option value="qualite">Qualité</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Statut</label>
                            <select v-model="filters.statut" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Tous</option>
                                <option value="a_faire">À faire</option>
                                <option value="en_cours">En cours</option>
                                <option value="valide">Valide</option>
                                <option value="non_conforme">Non conforme</option>
                                <option value="expire">Expiré</option>
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
                        <template #cell-statut="{ row }">
                            <span class="badge" :class="statutBadge(row.statut)">{{ statutLabel(row.statut) }}</span>
                        </template>
                        <template #cell-type_conformite="{ row }">
                            {{ typeLabel(row.type_conformite) }}
                        </template>
                        <template #cell-date_expiration="{ row }">
                            <span v-if="row.date_expiration" :class="dateExpiree(row.date_expiration) ? 'text-danger fw-semibold' : ''">
                                {{ formatDate(row.date_expiration) }}
                            </span>
                            <span v-else class="text-muted">—</span>
                        </template>
                    </DaeDataTable>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════
             MODAL: Create
             ════════════════════════════════════════════════ -->
        <div ref="createModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-shield-plus me-2 text-primary"></i>Nouvel élément de conformité
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="submitCreate">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Type <span class="text-danger">*</span></label>
                                    <select v-model="form.type_conformite" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="reglementaire">Réglementaire</option>
                                        <option value="fiscal">Fiscal</option>
                                        <option value="social">Social</option>
                                        <option value="juridique">Juridique</option>
                                        <option value="qualite">Qualité</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Titre <span class="text-danger">*</span></label>
                                    <input v-model="form.titre" class="form-control form-control-sm" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Autorité compétente</label>
                                    <input v-model="form.autorite_competente" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Client <span class="text-danger">*</span></label>
                                    <select v-model="form.client_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom || c.email }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Date de soumission</label>
                                    <input v-model="form.date_soumission" type="date" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Date d'expiration</label>
                                    <input v-model="form.date_expiration" type="date" class="form-control form-control-sm" />
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Exigence réglementaire</label>
                                    <textarea v-model="form.exigence_reglementaire" class="form-control form-control-sm" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Notes</label>
                                    <textarea v-model="form.notes" class="form-control form-control-sm" rows="2"></textarea>
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
                                    <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                                </span>
                                <span v-else><i class="bi bi-check-lg me-1"></i>Ajouter</span>
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
    a_faire:       { label: 'À faire',       badge: 'bg-secondary' },
    en_cours:      { label: 'En cours',      badge: 'bg-primary' },
    valide:        { label: 'Valide',        badge: 'bg-success' },
    non_conforme:  { label: 'Non conforme',  badge: 'bg-danger' },
    expire:        { label: 'Expiré',        badge: 'bg-dark' },
};

export default {
    name: 'DaeConformiteIndex',
    components: { DaeDataTable },
    data() {
        return {
            loading: true,
            items: [],
            clients: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            filters: { type: '', statut: '', client_id: '', recherche: '' },
            // Create form
            createModalInstance: null,
            form: {
                client_id: '', type_conformite: '', titre: '',
                autorite_competente: '', date_soumission: '',
                date_expiration: '', exigence_reglementaire: '', notes: '',
            },
            formLoading: false,
            formError: '',
        };
    },
    computed: {
        tableColumns() {
            return [
                { key: 'type_conformite', label: 'Type' },
                { key: 'titre', label: 'Titre', class: 'text-truncate', width: '30%' },
                { key: 'autorite_competente', label: 'Autorité' },
                { key: 'statut', label: 'Statut' },
                { key: 'date_expiration', label: 'Échéance' },
            ];
        },
        tableActions() {
            return [
                { key: 'voir', label: 'Voir', icon: 'bi-eye' },
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
                const r = await axios.get('/dae/conformite', { params });
                const d = r.data;
                this.items = d.data || [];
                this.currentPage = d.current_page || 1;
                this.totalPages = d.last_page || 1;
                this.totalItems = d.total || 0;
            } catch (e) {
                console.error('Erreur conformité:', e);
                this.items = []; this.totalPages = 1; this.totalItems = 0;
            }
        },
        applyFilters() { this.currentPage = 1; this.fetchItems(); },
        changePage(p) { this.currentPage = p; this.fetchItems(); },
        handleRowClick(row) { window.location.href = `/dae/conformite/${row.id}`; },
        handleAction({ action, row }) {
            if (action === 'voir') window.location.href = `/dae/conformite/${row.id}`;
            if (action === 'supprimer') this.deleteItem(row);
        },
        async deleteItem(row) {
            if (!confirm(`Supprimer "${row.titre}" ?`)) return;
            try { await axios.delete(`/dae/conformite/${row.id}`); this.fetchItems(); }
            catch (e) { alert('Impossible de supprimer.'); }
        },

        // Create modal
        openCreateModal() {
            this.formError = '';
            this.form = {
                client_id: '', type_conformite: '', titre: '',
                autorite_competente: '', date_soumission: '',
                date_expiration: '', exigence_reglementaire: '', notes: '',
            };
            this.createModalInstance = new Modal(this.$refs.createModal);
            this.createModalInstance.show();
        },
        async submitCreate() {
            this.formLoading = true;
            this.formError = '';
            try {
                await axios.post('/dae/conformite', this.form);
                this.createModalInstance.hide();
                this.fetchItems();
            } catch (e) {
                this.formError = e.response?.data?.message || "Erreur lors de l'enregistrement.";
            } finally { this.formLoading = false; }
        },

        // Helpers
        statutBadge(s) { return STATUT_MAP[s]?.badge || 'bg-secondary'; },
        statutLabel(s) { return STATUT_MAP[s]?.label || s || '-'; },
        typeLabel(t) {
            const map = {
                reglementaire: 'Réglementaire', fiscal: 'Fiscal',
                social: 'Social', juridique: 'Juridique', qualite: 'Qualité',
            };
            return map[t] || t || '-';
        },
        dateExpiree(d) { if (!d) return false; return new Date(d) < new Date(); },
        formatDate(d) {
            if (!d) return '-';
            try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }); }
            catch { return d; }
        },
    },
};
</script>

<style scoped>
.dae-conformite-index { padding: 20px; min-height: 80vh; }
.dae-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 20px; }
.dae-spinner { width: 48px; height: 48px; border: 4px solid rgba(255,121,0,0.1); border-top-color: #FF7900; border-radius: 50%; animation: dae-spin 0.7s linear infinite; }
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
.form-label { font-weight: 500; }
</style>
