<template>
<GelLayout>
    <div class="dae-taches-index">
        <!-- Loading State -->
        <div v-if="loading && !taches.length" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement des taches...</p>
        </div>

        <!-- Main Content -->
        <div v-else class="dae-content">
            <!-- Page Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour au tableau de bord">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-list-task me-2 text-primary"></i>Taches
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-kanban me-1"></i>Gestion des taches DAE
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} tache(s)</span>
                        </p>
                    </div>
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" @click="openCreateModal">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle tache
                    </button>
                </div>
            </div>

            <!-- View Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: viewMode === 'liste' }" @click="switchView('liste')">
                        <i class="bi bi-list-ul me-1"></i>Liste
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: viewMode === 'kanban' }" @click="switchView('kanban')">
                        <i class="bi bi-columns-gap me-1"></i>Kanban
                    </button>
                </li>
            </ul>

            <!-- ============================================================
                 LISTE VIEW
                 ============================================================ -->
            <template v-if="viewMode === 'liste'">
                <!-- Filter Bar -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <!-- Statut -->
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Statut</label>
                                <select v-model="filters.statut" class="form-select form-select-sm">
                                    <option value="">Tous</option>
                                    <option value="a_faire">A faire</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="en_revision">En revision</option>
                                    <option value="terminee">Terminee</option>
                                    <option value="annulee">Annulee</option>
                                </select>
                            </div>

                            <!-- Priorite -->
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Priorite</label>
                                <select v-model="filters.priorite" class="form-select form-select-sm">
                                    <option value="">Toutes</option>
                                    <option value="basse">Basse</option>
                                    <option value="moyenne">Moyenne</option>
                                    <option value="haute">Haute</option>
                                    <option value="critique">Critique</option>
                                </select>
                            </div>

                            <!-- Client -->
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Client (ID)</label>
                                <input type="text" v-model="filters.client_id" class="form-control form-control-sm" placeholder="ID client" />
                            </div>

                            <!-- Urgent checkbox -->
                            <div class="col-6 col-md-2 d-flex align-items-center pt-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="filterUrgentes" v-model="filters.urgentes" />
                                    <label class="form-check-label small" for="filterUrgentes">Urgentes seulement</label>
                                </div>
                            </div>

                            <!-- Search + button -->
                            <div class="col-6 col-md-4">
                                <div class="input-group input-group-sm">
                                    <input type="text" v-model="filters.recherche" class="form-control" placeholder="Rechercher..."
                                           @keyup.enter="applyFilters" />
                                    <button class="btn btn-primary" type="button" @click="applyFilters" title="Filtrer">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" @click="resetFilters" title="Reinitialiser">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <DaeDataTable
                            :columns="tableColumns"
                            :rows="taches"
                            :actions="tableActions"
                            :currentPage="currentPage"
                            :totalPages="totalPages"
                            @action="handleAction"
                            @page-change="handlePageChange"
                        >
                            <!-- Titre Slot -->
                            <template #cell-titre="{ row }">
                                <span class="fw-medium">{{ row.titre }}</span>
                            </template>

                            <!-- Priorite Badge Slot -->
                            <template #cell-priorite="{ row }">
                                <span class="badge" :class="prioriteBadgeClass(row.priorite)">
                                    {{ prioriteLabel(row.priorite) }}
                                </span>
                            </template>

                            <!-- Statut Badge Slot -->
                            <template #cell-statut="{ row }">
                                <span class="badge" :class="statutBadgeClass(row.statut)">
                                    {{ statutLabel(row.statut) }}
                                </span>
                            </template>

                            <!-- Echeance Slot -->
                            <template #cell-echeance="{ row }">
                                <span class="text-nowrap">{{ formatDate(row.echeance) }}</span>
                            </template>

                            <!-- Assigned To Slot -->
                            <template #cell-assigned_to="{ row }">
                                <span>{{ assignedToName(row) }}</span>
                            </template>

                            <!-- Client Slot -->
                            <template #cell-client="{ row }">
                                <span>{{ clientName(row) }}</span>
                            </template>
                        </DaeDataTable>
                    </div>
                </div>
            </template>

            <!-- ============================================================
                 KANBAN VIEW
                 ============================================================ -->
            <template v-if="viewMode === 'kanban'">
                <div class="kanban-board">
                    <div class="row g-3">
                        <div v-for="(col, colKey) in displayedKanbanColumns" :key="colKey" class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-transparent border-bottom py-2 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 small fw-bold text-uppercase">
                                        <span class="badge bg-secondary me-1">{{ col.tasks.length }}</span>
                                        {{ col.title }}
                                    </h6>
                                </div>
                                <div class="card-body p-2" style="min-height: 320px; max-height: 65vh; overflow-y: auto;">
                                    <div v-if="!col.tasks.length" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        <small>Aucune tache</small>
                                    </div>
                                    <div v-for="task in col.tasks" :key="task.id"
                                         class="kanban-card p-3 mb-2 rounded border-start border-4"
                                         :class="prioriteBorderClass(task.priorite)"
                                         style="background:#fff;cursor:default;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <small class="fw-semibold text-wrap" style="word-break:break-word;">{{ task.titre }}</small>
                                            <span class="badge ms-1 flex-shrink-0" :class="prioriteBadgeClass(task.priorite)" style="font-size:10px;">
                                                {{ prioriteLabel(task.priorite) }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>{{ formatDate(task.echeance) }}
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>{{ assignedToName(task) }}
                                            </small>
                                        </div>
                                        <div class="mt-2 d-flex gap-1 justify-content-end">
                                            <button v-if="canReculer(task.statut)" class="btn btn-sm btn-outline-secondary py-0 px-2"
                                                    @click="deplacerTache(task, 'reculer')" title="Reculer (statut precedent)">
                                                <i class="bi bi-arrow-left"></i>
                                            </button>
                                            <button v-if="canAvancer(task.statut)" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    @click="deplacerTache(task, 'avancer')" title="Avancer (statut suivant)">
                                                <i class="bi bi-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ================================================================
             MODAL: Nouvelle tache
             ================================================================ -->
        <div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true" ref="createModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle tache
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Titre -->
                            <div class="col-12">
                                <label class="form-label small fw-medium">Titre <span class="text-danger">*</span></label>
                                <input type="text" v-model="form.titre" class="form-control form-control-sm" placeholder="Titre de la tache" />
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label small fw-medium">Description</label>
                                <textarea v-model="form.description" class="form-control form-control-sm" rows="3" placeholder="Description (optionnelle)"></textarea>
                            </div>

                            <!-- Priorite -->
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-medium">Priorite</label>
                                <select v-model="form.priorite" class="form-select form-select-sm">
                                    <option value="basse">Basse</option>
                                    <option value="moyenne">Moyenne</option>
                                    <option value="haute">Haute</option>
                                    <option value="critique">Critique</option>
                                </select>
                            </div>

                            <!-- Echeance -->
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-medium">Echeance</label>
                                <input type="date" v-model="form.echeance" class="form-control form-control-sm" />
                            </div>

                            <!-- Assigned To -->
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-medium">Assigner a (ID utilisateur)</label>
                                <input type="number" v-model="form.assigned_to" class="form-control form-control-sm" placeholder="ID user" min="1" />
                            </div>

                            <!-- Client ID -->
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-medium">Client <span class="text-danger">*</span></label>
                                <input type="number" v-model="form.client_id" class="form-control form-control-sm" placeholder="ID client" min="1" />
                            </div>

                            <!-- Tags -->
                            <div class="col-12">
                                <label class="form-label small fw-medium">Tags</label>
                                <input type="text" v-model="form.tags" class="form-control form-control-sm" placeholder="tag1, tag2, tag3" />
                                <small class="text-muted">Separez les tags par des virgules</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" @click="submitCreateTask" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-check-lg me-1"></i>Creer la tache
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================
             MODAL: Changer statut (from liste view)
             ================================================================ -->
        <div class="modal fade" id="changeStatutModal" tabindex="-1" aria-hidden="true" ref="statutModal">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light border-bottom">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-arrow-left-right me-2 text-primary"></i>Changer le statut
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">
                            Tache : <span class="fw-medium">{{ statutModalTask?.titre }}</span>
                        </p>
                        <label class="form-label small fw-medium">Nouveau statut</label>
                        <select v-model="statutModalValue" class="form-select form-select-sm">
                            <option value="a_faire">A faire</option>
                            <option value="en_cours">En cours</option>
                            <option value="en_revision">En revision</option>
                            <option value="terminee">Terminee</option>
                            <option value="annulee">Annulee</option>
                        </select>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" @click="submitStatutChange">
                            <i class="bi bi-check-lg me-1"></i>Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================
             MODAL: Assigner
             ================================================================ -->
        <div class="modal fade" id="assignTaskModal" tabindex="-1" aria-hidden="true" ref="assignModal">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light border-bottom">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-person-plus me-2 text-primary"></i>Assigner la tache
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">
                            Tache : <span class="fw-medium">{{ assignModalTask?.titre }}</span>
                        </p>
                        <label class="form-label small fw-medium">ID utilisateur</label>
                        <input type="number" v-model="assignModalValue" class="form-control form-control-sm" placeholder="ID user" min="1" />
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" @click="submitAssigner">
                            <i class="bi bi-check-lg me-1"></i>Assigner
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================
             MODAL: Supprimer confirmation
             ================================================================ -->
        <div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-hidden="true" ref="deleteModal">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white border-bottom">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-trash me-2"></i>Supprimer la tache
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small mb-0">
                            Voulez-vous vraiment supprimer la tache
                            <span class="fw-medium">{{ deleteModalTask?.titre }}</span> ?
                        </p>
                        <p class="small text-danger mt-1 mb-0">Cette action est irreversible.</p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-danger" @click="submitDelete">
                            <i class="bi bi-trash me-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</GelLayout>
</template>

<script>
import axios from 'axios';
import DaeDataTable from '../../../../Components/Dae/DaeDataTable.vue';
import { Modal } from 'bootstrap';

// ─── Constants ──────────────────────────────────────────────────────────────

const PRIORITE_MAP = {
    basse:    { label: 'Basse',    badge: 'bg-success' },
    moyenne:  { label: 'Moyenne',  badge: 'bg-info' },
    haute:    { label: 'Haute',    badge: 'bg-warning text-dark' },
    critique: { label: 'Critique', badge: 'bg-danger' },
};

const STATUT_MAP = {
    a_faire:     { label: 'A faire',     badge: 'bg-secondary' },
    en_cours:    { label: 'En cours',    badge: 'bg-primary' },
    en_revision: { label: 'En revision', badge: 'bg-info' },
    terminee:    { label: 'Terminee',    badge: 'bg-success' },
    annulee:     { label: 'Annulee',     badge: 'bg-dark' },
};

const STATUT_ORDER = ['a_faire', 'en_cours', 'en_revision', 'terminee'];

// ─── Component ──────────────────────────────────────────────────────────────

export default {
    name: 'DaeTachesIndex',

    components: {
        DaeDataTable,
    },

    data() {
        return {
            loading: false,
            viewMode: 'liste',

            // Liste
            taches: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            filters: {
                statut: '',
                priorite: '',
                client_id: '',
                recherche: '',
                urgentes: false,
            },

            // Kanban
            kanbanColumns: {},

            // Create form
            form: {
                titre: '',
                description: '',
                priorite: 'moyenne',
                echeance: '',
                assigned_to: '',
                client_id: '',
                tags: '',
            },
            submitting: false,

            // Statut modal
            statutModalTask: null,
            statutModalValue: 'a_faire',
            statutModalInstance: null,

            // Assign modal
            assignModalTask: null,
            assignModalValue: '',
            assignModalInstance: null,

            // Delete modal
            deleteModalTask: null,
            deleteModalInstance: null,

            // Create modal
            createModalInstance: null,
        };
    },

    computed: {
        tableColumns() {
            return [
                { key: 'titre',       label: 'Titre', class: 'text-truncate', width: '30%' },
                { key: 'priorite',    label: 'Priorite' },
                { key: 'statut',      label: 'Statut' },
                { key: 'echeance',    label: 'Echeance' },
                { key: 'assigned_to', label: 'Assigné a' },
                { key: 'client',      label: 'Client' },
            ];
        },

        tableActions() {
            return [
                { key: 'changer-statut', label: 'Changer statut', icon: 'bi-arrow-left-right' },
                { key: 'assigner',       label: 'Assigner',       icon: 'bi-person-plus' },
                { key: 'supprimer',      label: 'Supprimer',      icon: 'bi-trash', danger: true },
            ];
        },

        displayedKanbanColumns() {
            // Only show the 4 main columns (exclude annulee)
            const keys = ['a_faire', 'en_cours', 'en_revision', 'terminee'];
            const result = {};
            for (const key of keys) {
                if (this.kanbanColumns[key]) {
                    result[key] = this.kanbanColumns[key];
                } else {
                    result[key] = { title: this.statutLabel(key), tasks: [] };
                }
            }
            return result;
        },
    },

    created() {
        this.fetchTaches();
    },

    methods: {
        // ─── FETCH ──────────────────────────────────────────────────────────

        async fetchTaches() {
            this.loading = true;

            try {
                if (this.viewMode === 'liste') {
                    await this.fetchListe();
                } else {
                    await this.fetchKanban();
                }
            } catch (error) {
                console.error('Erreur lors du chargement des taches:', error);
                this.taches = [];
                this.kanbanColumns = {};
                this.totalPages = 1;
                this.totalItems = 0;
            } finally {
                this.loading = false;
            }
        },

        async fetchListe() {
            const params = { page: this.currentPage };

            if (this.filters.statut) params.statut = this.filters.statut;
            if (this.filters.priorite) params.priorite = this.filters.priorite;
            if (this.filters.client_id) params.client_id = this.filters.client_id;
            if (this.filters.recherche) params.recherche = this.filters.recherche;
            if (this.filters.urgentes) params.urgentes = '1';

            const response = await axios.get('/dae/taches', { params });
            const data = response.data;

            this.taches = data.data || [];
            this.currentPage = data.current_page || 1;
            this.totalPages = data.last_page || 1;
            this.totalItems = data.total || 0;
            this.syncQueryString();
        },

        async fetchKanban() {
            const params = {};
            if (this.filters.client_id) params.client_id = this.filters.client_id;

            const response = await axios.get('/dae/taches/kanban', { params });
            this.kanbanColumns = response.data || {};
            this.totalItems = Object.values(this.kanbanColumns).reduce((sum, col) => sum + (col.tasks?.length || 0), 0);
        },

        // ─── VIEW SWITCHING ────────────────────────────────────────────────

        switchView(mode) {
            if (this.viewMode === mode) return;
            this.viewMode = mode;
            this.currentPage = 1;
            this.fetchTaches();
        },

        // ─── FILTERS ────────────────────────────────────────────────────────

        applyFilters() {
            this.currentPage = 1;
            this.fetchTaches();
        },

        resetFilters() {
            this.filters = {
                statut: '',
                priorite: '',
                client_id: '',
                recherche: '',
                urgentes: false,
            };
            this.currentPage = 1;
            this.fetchTaches();
        },

        syncQueryString() {
            const params = new URLSearchParams();
            if (this.filters.statut) params.set('statut', this.filters.statut);
            if (this.filters.priorite) params.set('priorite', this.filters.priorite);
            if (this.filters.client_id) params.set('client_id', this.filters.client_id);
            if (this.filters.recherche) params.set('recherche', this.filters.recherche);
            if (this.filters.urgentes) params.set('urgentes', '1');
            if (this.currentPage > 1) params.set('page', this.currentPage);

            const qs = params.toString();
            const url = qs ? `/dae/taches?${qs}` : '/dae/taches';
            window.history.replaceState({}, '', url);
        },

        handlePageChange(page) {
            this.currentPage = page;
            this.fetchTaches();
        },

        // ─── TABLE ACTIONS ─────────────────────────────────────────────────

        handleAction({ action, row }) {
            switch (action) {
                case 'changer-statut':
                    this.openStatutModal(row);
                    break;
                case 'assigner':
                    this.openAssignModal(row);
                    break;
                case 'supprimer':
                    this.openDeleteModal(row);
                    break;
            }
        },

        // ─── CREATE TASK ────────────────────────────────────────────────────

        openCreateModal() {
            this.form = {
                titre: '',
                description: '',
                priorite: 'moyenne',
                echeance: '',
                assigned_to: '',
                client_id: '',
                tags: '',
            };
            this.showModal('createModal');
        },

        async submitCreateTask() {
            if (!this.form.titre || !this.form.client_id) {
                alert('Veuillez remplir le titre et le client.');
                return;
            }

            this.submitting = true;

            try {
                const payload = {
                    titre: this.form.titre,
                    description: this.form.description || null,
                    priorite: this.form.priorite,
                    echeance: this.form.echeance || null,
                    assigned_to: this.form.assigned_to ? Number(this.form.assigned_to) : null,
                    client_id: Number(this.form.client_id),
                };

                // Convert tags string to JSON array
                if (this.form.tags) {
                    const tagsArray = this.form.tags.split(',').map(t => t.trim()).filter(Boolean);
                    payload.tags = JSON.stringify(tagsArray);
                }

                await axios.post('/dae/taches', payload);

                this.hideModal('createModal');
                this.fetchTaches();
            } catch (error) {
                console.error('Erreur lors de la creation:', error);
                const msg = error.response?.data?.message || error.message || 'Erreur inconnue';
                alert('Erreur : ' + msg);
            } finally {
                this.submitting = false;
            }
        },

        // ─── STATUT CHANGE (from actions dropdown) ─────────────────────────

        openStatutModal(task) {
            this.statutModalTask = task;
            this.statutModalValue = task.statut || 'a_faire';
            this.showModal('statutModal');
        },

        async submitStatutChange() {
            if (!this.statutModalTask || !this.statutModalValue) return;

            try {
                await axios.patch(`/dae/taches/${this.statutModalTask.id}/statut`, {
                    statut: this.statutModalValue,
                });

                this.hideModal('statutModal');
                this.statutModalTask = null;
                this.fetchTaches();
            } catch (error) {
                console.error('Erreur lors du changement de statut:', error);
                alert('Erreur : ' + (error.response?.data?.message || error.message));
            }
        },

        // ─── KANBAN AVANCER / RECULER ──────────────────────────────────────

        canAvancer(statut) {
            const idx = STATUT_ORDER.indexOf(statut);
            return idx >= 0 && idx < STATUT_ORDER.length - 1;
        },

        canReculer(statut) {
            const idx = STATUT_ORDER.indexOf(statut);
            return idx > 0 && idx < STATUT_ORDER.length;
        },

        async deplacerTache(task, direction) {
            const idx = STATUT_ORDER.indexOf(task.statut);
            if (idx === -1) return;

            let newStatut;
            if (direction === 'avancer' && idx < STATUT_ORDER.length - 1) {
                newStatut = STATUT_ORDER[idx + 1];
            } else if (direction === 'reculer' && idx > 0) {
                newStatut = STATUT_ORDER[idx - 1];
            } else {
                return;
            }

            try {
                await axios.patch(`/dae/taches/${task.id}/statut`, {
                    statut: newStatut,
                });
                // Move task locally for immediate feedback
                this.moveTaskLocally(task, newStatut);
            } catch (error) {
                console.error('Erreur lors du deplacement:', error);
                alert('Erreur : ' + (error.response?.data?.message || error.message));
            }
        },

        moveTaskLocally(task, newStatut) {
            // Remove from old column
            for (const colKey of Object.keys(this.kanbanColumns)) {
                const tasks = this.kanbanColumns[colKey].tasks;
                const idx = tasks.findIndex(t => t.id === task.id);
                if (idx !== -1) {
                    tasks.splice(idx, 1);
                    break;
                }
            }

            // Add to new column
            task.statut = newStatut;
            if (this.kanbanColumns[newStatut]) {
                this.kanbanColumns[newStatut].tasks.push(task);
            }
        },

        // ─── ASSIGNER ──────────────────────────────────────────────────────

        openAssignModal(task) {
            this.assignModalTask = task;
            this.assignModalValue = task.assigned_to || '';
            this.showModal('assignModal');
        },

        async submitAssigner() {
            if (!this.assignModalTask || !this.assignModalValue) {
                alert('Veuillez entrer un ID utilisateur.');
                return;
            }

            try {
                await axios.patch(`/dae/taches/${this.assignModalTask.id}/assigner`, {
                    assigned_to: Number(this.assignModalValue),
                });

                this.hideModal('assignModal');
                this.assignModalTask = null;
                this.fetchTaches();
            } catch (error) {
                console.error('Erreur lors de l\'assignation:', error);
                alert('Erreur : ' + (error.response?.data?.message || error.message));
            }
        },

        // ─── SUPPRIMER ─────────────────────────────────────────────────────

        openDeleteModal(task) {
            this.deleteModalTask = task;
            this.showModal('deleteModal');
        },

        async submitDelete() {
            if (!this.deleteModalTask) return;

            try {
                await axios.delete(`/dae/taches/${this.deleteModalTask.id}`);

                this.hideModal('deleteModal');
                this.deleteModalTask = null;
                this.fetchTaches();
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                alert('Erreur : ' + (error.response?.data?.message || error.message));
            }
        },

        // ─── MODAL HELPERS ─────────────────────────────────────────────────

        showModal(refName) {
            const el = this.$refs[refName];
            if (!el) return;
            const modal = Modal.getOrCreateInstance(el);
            modal.show();
        },

        hideModal(refName) {
            const el = this.$refs[refName];
            if (!el) return;
            const modal = Modal.getInstance(el);
            if (modal) modal.hide();
        },

        // ─── LABELS / BADGES ──────────────────────────────────────────────

        prioriteBadgeClass(priorite) {
            return PRIORITE_MAP[priorite]?.badge || 'bg-secondary';
        },

        prioriteLabel(priorite) {
            return PRIORITE_MAP[priorite]?.label || priorite || '-';
        },

        prioriteBorderClass(priorite) {
            const map = {
                basse: 'border-success',
                moyenne: 'border-info',
                haute: 'border-warning',
                critique: 'border-danger',
            };
            return map[priorite] || 'border-secondary';
        },

        statutBadgeClass(statut) {
            return STATUT_MAP[statut]?.badge || 'bg-secondary';
        },

        statutLabel(statut) {
            return STATUT_MAP[statut]?.label || statut || '-';
        },

        assignedToName(task) {
            if (!task.assigned_to) return '-';
            if (typeof task.assigned_to === 'object' && task.assigned_to?.name) {
                return task.assigned_to.name;
            }
            if (task.assigned_to_name) return task.assigned_to_name;
            if (task.assignedTo?.name) return task.assignedTo.name;
            return '#' + (task.assigned_to_id || task.assigned_to);
        },

        clientName(task) {
            if (!task.client_id) return '-';
            if (typeof task.client === 'object' && task.client) {
                return task.client.nom || task.client.raison_sociale || task.client.name || '#' + task.client_id;
            }
            return '#' + task.client_id;
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                });
            } catch {
                return dateStr;
            }
        },
    },
};
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════
   DAE Taches Index Styles
   ═══════════════════════════════════════════════════════ */

.dae-taches-index {
    padding: 20px;
    min-height: 80vh;
}

/* ── Loading State ── */
.dae-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
}
.dae-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(255, 121, 0, 0.1);
    border-top-color: #FF7900;
    border-radius: 50%;
    animation: dae-spin 0.7s linear infinite;
}
@keyframes dae-spin {
    to { transform: rotate(360deg); }
}
.dae-loading-text {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

/* ── Filter bar label ── */
.form-label {
    font-weight: 500;
}

/* ── Kanban Cards ── */
.kanban-card {
    background: #fff;
    transition: box-shadow 0.15s ease, transform 0.15s ease;
}
.kanban-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}
</style>
