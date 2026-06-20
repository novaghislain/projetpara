<template>
    <div class="dae-personnel-index">
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement du personnel...</p>
        </div>

        <div v-else class="dae-content">
            <!-- Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour tableau de bord">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                            <i class="bi bi-people me-2 text-primary"></i>Personnel RH
                        </h1>
                        <p class="text-muted small mb-0">
                            Gestion des dossiers du personnel
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} membre(s)</span>
                        </p>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm mt-2 mt-md-0" @click="openFormModal(null)">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter un membre
                </button>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Statut</label>
                            <select v-model="filters.statut" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Tous</option>
                                <option value="actif">Actif</option>
                                <option value="conge">Congé</option>
                                <option value="suspendu">Suspendu</option>
                                <option value="sorti">Sorti</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Département</label>
                            <select v-model="filters.departement" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Tous</option>
                                <option value="Administration">Administration</option>
                                <option value="Comptabilité">Comptabilité</option>
                                <option value="Juridique">Juridique</option>
                                <option value="RH">Ressources humaines</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Technique">Technique</option>
                                <option value="Direction">Direction</option>
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
                        :rows="personnel"
                        :actions="tableActions"
                        :currentPage="currentPage"
                        :totalPages="totalPages"
                        :rowClickable="true"
                        @row-click="handleRowClick"
                        @action="handleAction"
                        @page-change="handlePageChange"
                    >
                        <template #cell-nom_complet="{ row }">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                     style="width:32px;height:32px;font-size:0.8rem;font-weight:600;color:#FF7900;">
                                    {{ (row.prenom?.[0] || '') + (row.nom?.[0] || '') }}
                                </div>
                                <div>
                                    <div class="fw-medium small">{{ row.prenom }} {{ row.nom }}</div>
                                    <div class="text-muted" style="font-size:0.7rem;">{{ row.email }}</div>
                                </div>
                            </div>
                        </template>
                        <template #cell-statut="{ row }">
                            <span class="badge" :class="statutBadge(row.statut)">
                                {{ statutLabel(row.statut) }}
                            </span>
                        </template>
                        <template #cell-salaire="{ row }">
                            <span v-if="row.salaire !== null && row.salaire !== undefined">
                                {{ formatCurrency(row.salaire) }}
                            </span>
                            <span v-else class="text-muted">—</span>
                        </template>
                        <template #cell-date_embauche="{ row }">
                            <span v-if="row.date_embauche" class="text-nowrap">{{ formatDate(row.date_embauche) }}</span>
                            <span v-else class="text-muted">—</span>
                        </template>
                    </DaeDataTable>
                </div>
            </div>
        </div>

        <!-- ═══ FORM MODAL (create / edit) ═══ -->
        <div ref="formModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi me-2 text-primary" :class="editing ? 'bi-pencil' : 'bi-person-plus'"></i>
                            {{ editing ? 'Modifier le membre' : 'Ajouter un membre' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Nom <span class="text-danger">*</span></label>
                                    <input v-model="form.nom" class="form-control form-control-sm" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Prénom <span class="text-danger">*</span></label>
                                    <input v-model="form.prenom" class="form-control form-control-sm" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Email</label>
                                    <input v-model="form.email" type="email" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Téléphone</label>
                                    <input v-model="form.telephone" type="tel" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Poste</label>
                                    <input v-model="form.poste" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Département</label>
                                    <select v-model="form.departement" class="form-select form-select-sm">
                                        <option value="">Sélectionner...</option>
                                        <option value="Administration">Administration</option>
                                        <option value="Comptabilité">Comptabilité</option>
                                        <option value="Juridique">Juridique</option>
                                        <option value="RH">Ressources humaines</option>
                                        <option value="Commercial">Commercial</option>
                                        <option value="Technique">Technique</option>
                                        <option value="Direction">Direction</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Statut</label>
                                    <select v-model="form.statut" class="form-select form-select-sm">
                                        <option value="actif">Actif</option>
                                        <option value="conge">Congé</option>
                                        <option value="suspendu">Suspendu</option>
                                        <option value="sorti">Sorti</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Type de contrat</label>
                                    <select v-model="form.type_contrat" class="form-select form-select-sm">
                                        <option value="">Sélectionner...</option>
                                        <option value="CDI">CDI</option>
                                        <option value="CDD">CDD</option>
                                        <option value="Stage">Stage</option>
                                        <option value="Freelance">Freelance</option>
                                        <option value="Intérim">Intérim</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Salaire (FCFA)</label>
                                    <input v-model.number="form.salaire" type="number" min="0" step="1000" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">N° Sécurité sociale</label>
                                    <input v-model="form.numero_securite_sociale" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Date d'embauche</label>
                                    <input v-model="form.date_embauche" type="date" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Date de départ</label>
                                    <input v-model="form.date_depart" type="date" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Client</label>
                                    <select v-model="form.client_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom || c.email }}</option>
                                    </select>
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
                                <span v-else><i class="bi bi-check-lg me-1"></i>{{ editing ? 'Modifier' : 'Ajouter' }}</span>
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
    actif:     { label: 'Actif',     badge: 'bg-success' },
    conge:     { label: 'Congé',     badge: 'bg-warning text-dark' },
    suspendu:  { label: 'Suspendu',  badge: 'bg-secondary' },
    sorti:     { label: 'Sorti',    badge: 'bg-danger' },
};

export default {
    name: 'DaePersonnelIndex',
    components: { DaeDataTable },

    data() {
        return {
            loading: true,
            personnel: [],
            clients: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            filters: { statut: '', departement: '', client_id: '', recherche: '' },

            // Modal form
            formModal: null,
            editing: null,
            form: {
                client_id: '', nom: '', prenom: '', email: '', telephone: '',
                poste: '', departement: '', statut: 'actif', type_contrat: '',
                salaire: null, numero_securite_sociale: '', date_embauche: '',
                date_depart: '', notes: '',
            },
            formLoading: false,
            formError: '',
        };
    },

    computed: {
        tableColumns() {
            return [
                { key: 'nom_complet', label: 'Membre', width: '25%' },
                { key: 'poste',       label: 'Poste' },
                { key: 'departement', label: 'Département' },
                { key: 'statut',      label: 'Statut' },
                { key: 'salaire',     label: 'Salaire' },
                { key: 'date_embauche', label: 'Embauche' },
            ];
        },
        tableActions() {
            return [
                { key: 'voir',      label: 'Voir',        icon: 'bi-eye' },
                { key: 'modifier',  label: 'Modifier',    icon: 'bi-pencil' },
                { key: 'supprimer', label: 'Supprimer',   icon: 'bi-trash', danger: true },
            ];
        },
    },

    created() {
        Promise.all([this.fetchClients(), this.fetchPersonnel()]).finally(() => { this.loading = false; });
    },

    methods: {
        async fetchClients() {
            try {
                const r = await axios.get('/api/clients');
                this.clients = Array.isArray(r.data) ? r.data : (r.data.data || []);
            } catch (e) { console.error('Erreur clients:', e); }
        },

        async fetchPersonnel() {
            try {
                const params = { page: this.currentPage, ...this.filters };
                Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
                const r = await axios.get('/dae/personnel', { params });
                const d = r.data;
                this.personnel = d.data || [];
                this.currentPage = d.current_page || 1;
                this.totalPages = d.last_page || 1;
                this.totalItems = d.total || 0;
            } catch (e) {
                console.error('Erreur personnel:', e);
                this.personnel = [];
                this.totalPages = 1;
                this.totalItems = 0;
            }
        },

        applyFilters() { this.currentPage = 1; this.fetchPersonnel(); },
        handlePageChange(p) { this.currentPage = p; this.fetchPersonnel(); },
        handleRowClick(row) { window.location.href = `/dae/personnel/${row.id}`; },

        handleAction({ action, row }) {
            switch (action) {
                case 'voir': window.location.href = `/dae/personnel/${row.id}`; break;
                case 'modifier': this.openFormModal(row); break;
                case 'supprimer': this.deletePersonne(row); break;
            }
        },

        // ── Form modal ──

        openFormModal(personne) {
            this.formError = '';
            if (personne) {
                this.editing = personne;
                this.form = {
                    client_id: personne.client_id || '',
                    nom: personne.nom || '',
                    prenom: personne.prenom || '',
                    email: personne.email || '',
                    telephone: personne.telephone || '',
                    poste: personne.poste || '',
                    departement: personne.departement || '',
                    statut: personne.statut || 'actif',
                    type_contrat: personne.type_contrat || '',
                    salaire: personne.salaire,
                    numero_securite_sociale: personne.numero_securite_sociale || '',
                    date_embauche: personne.date_embauche || '',
                    date_depart: personne.date_depart || '',
                    notes: personne.notes || '',
                };
            } else {
                this.editing = null;
                this.form = {
                    client_id: '', nom: '', prenom: '', email: '', telephone: '',
                    poste: '', departement: '', statut: 'actif', type_contrat: '',
                    salaire: null, numero_securite_sociale: '', date_embauche: '',
                    date_depart: '', notes: '',
                };
            }
            this.formModal = new Modal(this.$refs.formModal);
            this.formModal.show();
        },

        async submitForm() {
            this.formLoading = true;
            this.formError = '';
            try {
                if (this.editing) {
                    await axios.put(`/dae/personnel/${this.editing.id}`, this.form);
                } else {
                    await axios.post('/dae/personnel', this.form);
                }
                this.formModal.hide();
                this.fetchPersonnel();
            } catch (e) {
                this.formError = e.response?.data?.message || "Erreur lors de l'enregistrement.";
            } finally { this.formLoading = false; }
        },

        async deletePersonne(row) {
            if (!confirm(`Supprimer ${row.prenom} ${row.nom} ?`)) return;
            try {
                await axios.delete(`/dae/personnel/${row.id}`);
                this.fetchPersonnel();
            } catch (e) {
                console.error('Erreur suppression:', e);
                alert('Impossible de supprimer.');
            }
        },

        // ── Helpers ──
        statutBadge(s) { return STATUT_MAP[s]?.badge || 'bg-secondary'; },
        statutLabel(s) { return STATUT_MAP[s]?.label || s || '-'; },
        formatDate(d) {
            if (!d) return '-';
            try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }); }
            catch { return d; }
        },
        formatCurrency(v) {
            if (v === null || v === undefined) return '-';
            return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(v);
        },
    },
};
</script>

<style scoped>
.dae-personnel-index { padding: 20px; min-height: 80vh; }
.dae-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 20px; }
.dae-spinner { width: 48px; height: 48px; border: 4px solid rgba(255,121,0,0.1); border-top-color: #FF7900; border-radius: 50%; animation: dae-spin 0.7s linear infinite; }
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
.form-label { font-weight: 500; }
</style>
