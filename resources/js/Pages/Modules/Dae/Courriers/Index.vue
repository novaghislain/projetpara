<template>
<GelLayout>
    <div class="dae-courriers-index">
        <!-- ═══ LOADING STATE ═══ -->
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement des courriers...</p>
        </div>

        <!-- ═══ MAIN CONTENT ═══ -->
        <div v-else class="dae-content">
            <!-- Page Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour au tableau de bord">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-envelope me-2 text-primary"></i>Courriers
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-inboxes me-1"></i>Gestion du courrier DAE
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} courrier(s)</span>
                        </p>
                    </div>
                </div>
                <div>
                    <a :href="'/dae/courriers/create'" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Nouveau courrier
                    </a>
                </div>
            </div>

            <!-- ═══ FILTER BAR ═══ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <!-- Type -->
                        <div class="col-6 col-md-2">
                            <label class="form-label small text-muted mb-1">Type</label>
                            <select v-model="filters.type" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <option value="entrant">Entrant</option>
                                <option value="sortant">Sortant</option>
                                <option value="interne">Interne</option>
                            </select>
                        </div>

                        <!-- Statut -->
                        <div class="col-6 col-md-2">
                            <label class="form-label small text-muted mb-1">Statut</label>
                            <select v-model="filters.statut" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <option value="brouillon">Brouillon</option>
                                <option value="envoye">Envoye</option>
                                <option value="recu">Recu</option>
                                <option value="traite">Traite</option>
                                <option value="archive">Archive</option>
                            </select>
                        </div>

                        <!-- Urgence -->
                        <div class="col-6 col-md-2">
                            <label class="form-label small text-muted mb-1">Urgence</label>
                            <select v-model="filters.urgence" class="form-select form-select-sm">
                                <option value="">Toutes</option>
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                                <option value="tre_urgent">Tres urgent</option>
                            </select>
                        </div>

                        <!-- Date debut -->
                        <div class="col-6 col-md-2">
                            <label class="form-label small text-muted mb-1">Date debut</label>
                            <input type="date" v-model="filters.date_debut" class="form-control form-control-sm" />
                        </div>

                        <!-- Date fin -->
                        <div class="col-6 col-md-2">
                            <label class="form-label small text-muted mb-1">Date fin</label>
                            <input type="date" v-model="filters.date_fin" class="form-control form-control-sm" />
                        </div>

                        <!-- Search + Filter button -->
                        <div class="col-6 col-md-2">
                            <div class="input-group input-group-sm">
                                <input type="text" v-model="filters.search" class="form-control" placeholder="Rechercher..."
                                       @keyup.enter="applyFilters" />
                                <button class="btn btn-primary" type="button" @click="applyFilters" title="Filtrer">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ DATA TABLE ═══ -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <DaeDataTable
                        :columns="tableColumns"
                        :rows="courriers"
                        :actions="tableActions"
                        :currentPage="currentPage"
                        :totalPages="totalPages"
                        :rowClickable="true"
                        @row-click="handleRowClick"
                        @action="handleAction"
                        @page-change="handlePageChange"
                    >
                        <!-- Statut Badge Slot -->
                        <template #cell-statut="{ row }">
                            <span class="badge" :class="statutBadgeClass(row.statut)">
                                {{ statutLabel(row.statut) }}
                            </span>
                        </template>

                        <!-- Urgence Badge Slot -->
                        <template #cell-urgence="{ row }">
                            <span class="badge" :class="urgenceBadgeClass(row.urgence)">
                                {{ urgenceLabel(row.urgence) }}
                            </span>
                        </template>

                        <!-- Type Slot (traduit) -->
                        <template #cell-type="{ row }">
                            {{ typeLabel(row.type) }}
                        </template>

                        <!-- Expediteur / Destinataire Slot -->
                        <template #cell-expediteur="{ row }">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person me-1 text-muted"></i>
                                <span>{{ row.expediteur || row.destinataire || '-' }}</span>
                            </div>
                        </template>

                        <!-- Date Slot (format francais) -->
                        <template #cell-date_courrier="{ row }">
                            <span class="text-nowrap">{{ formatDate(row.date_courrier) }}</span>
                        </template>
                    </DaeDataTable>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ASSIGNER MODAL ═══ -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus text-primary me-2"></i>Assigner le courrier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div v-if="assignModal.loading" class="text-center py-3">
                        <div class="dae-spinner small"></div>
                        <p class="text-muted small mt-2">Chargement des utilisateurs...</p>
                    </div>
                    <div v-else>
                        <p class="small text-muted mb-2">
                            Courrier : <strong>{{ assignModal.courrier?.reference || '-' }}</strong>
                        </p>
                        <label class="form-label small fw-medium">Assigner à :</label>
                        <select v-model="assignModal.selectedUserId" class="form-select form-select-sm">
                            <option value="">-- Selectionner un utilisateur --</option>
                            <option v-for="user in assignModal.users" :key="user.id" :value="user.id">
                                {{ user.name || user.email }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-sm btn-primary" :disabled="assignModal.loading || !assignModal.selectedUserId" @click="confirmAssign">
                        <i class="bi bi-check-lg me-1"></i>Assigner
                    </button>
                </div>
            </div>
        </div>
    </div>
</GelLayout>
</template>

<script>
import axios from 'axios';
import DaeDataTable from '../../../../Components/Dae/DaeDataTable.vue';

const STATUT_MAP = {
    brouillon: { label: 'Brouillon',   badge: 'bg-secondary' },
    envoye:    { label: 'Envoye',      badge: 'bg-primary' },
    recu:      { label: 'Recu',        badge: 'bg-info' },
    traite:    { label: 'Traite',      badge: 'bg-success' },
    archive:   { label: 'Archive',     badge: 'bg-dark' },
};

const URGENCE_MAP = {
    normal:     { label: 'Normal',     badge: 'bg-success' },
    urgent:     { label: 'Urgent',     badge: 'bg-warning text-dark' },
    tre_urgent: { label: 'Tres urgent', badge: 'bg-danger' },
};

const TYPE_MAP = {
    entrant: 'Entrant',
    sortant: 'Sortant',
    interne: 'Interne',
};

export default {
    name: 'DaeCourriersIndex',

    components: {
        DaeDataTable,
    },

    data() {
        const params = new URLSearchParams(window.location.search);

        return {
            loading: false,
            courriers: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            filters: {
                type:       params.get('type') || '',
                statut:     params.get('statut') || '',
                urgence:    params.get('urgence') || '',
                date_debut: params.get('date_debut') || '',
                date_fin:   params.get('date_fin') || '',
                search:     params.get('search') || '',
            },
            // Assigner modal data
            assignModal: { show: false, courrier: null, users: [], selectedUserId: '', loading: false },
        };
    },

    computed: {
        tableColumns() {
            return [
                { key: 'reference',       label: 'Reference' },
                { key: 'type',            label: 'Type' },
                { key: 'objet',           label: 'Objet', class: 'text-truncate', width: '30%' },
                { key: 'expediteur',      label: 'Expediteur / Destinataire' },
                { key: 'urgence',         label: 'Urgence' },
                { key: 'statut',          label: 'Statut' },
                { key: 'date_courrier',   label: 'Date' },
            ];
        },

        tableActions() {
            return [
                { key: 'voir',      label: 'Voir',       icon: 'bi-eye' },
                { key: 'traiter',   label: 'Traiter',    icon: 'bi-check2' },
                { key: 'repondre',  label: 'Repondre',   icon: 'bi-reply' },
                { key: 'assigner',  label: 'Assigner',   icon: 'bi-person-plus' },
                { key: 'archiver',  label: 'Archiver',   icon: 'bi-archive' },
                { key: 'dupliquer', label: 'Dupliquer',  icon: 'bi-files' },
                { key: 'supprimer', label: 'Supprimer',  icon: 'bi-trash', danger: true },
            ];
        },
    },

    created() {
        this.fetchCourriers();
    },

    methods: {
        async fetchCourriers() {
            this.loading = true;

            try {
                const params = {
                    page: this.currentPage,
                    ...this.filters,
                };

                // Remove empty filter values
                Object.keys(params).forEach(key => {
                    if (params[key] === '' || params[key] === null || params[key] === undefined) {
                        delete params[key];
                    }
                });

                const response = await axios.get('/dae/courriers', { params });
                const data = response.data;

                this.courriers = data.data || [];
                this.currentPage = data.current_page || 1;
                this.totalPages = data.last_page || 1;
                this.totalItems = data.total || 0;

                // Sync query string
                this.syncQueryString();
            } catch (error) {
                console.error('Erreur lors du chargement des courriers:', error);
                this.courriers = [];
                this.totalPages = 1;
                this.totalItems = 0;
            } finally {
                this.loading = false;
            }
        },

        applyFilters() {
            this.currentPage = 1;
            this.fetchCourriers();
        },

        handlePageChange(page) {
            this.currentPage = page;
            this.fetchCourriers();
        },

        handleRowClick(row) {
            window.location.href = `/dae/courriers/${row.id}`;
        },

        async handleAction({ action, row }) {
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;

            switch (action) {
                case 'voir':
                    window.location.href = `/dae/courriers/${row.id}`;
                    break;
                case 'traiter':
                    window.location.href = `/dae/courriers/${row.id}/edit`;
                    break;
                case 'repondre':
                    window.location.href = `/dae/courriers/${row.id}#repondre`;
                    break;
                case 'assigner':
                    await this.openAssignModal(row);
                    break;
                case 'archiver':
                    if (!confirm('Archiver ce courrier ?')) return;
                    try {
                        await axios.patch(`/dae/courriers/${row.id}/archiver`, {}, {
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        await this.fetchCourriers();
                    } catch (e) {
                        alert('Erreur lors de l\'archivage.');
                    }
                    break;
                case 'dupliquer':
                    try {
                        const resp = await axios.post(`/dae/courriers/${row.id}/dupliquer`, {}, {
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        const newId = resp.data?.id;
                        if (newId) window.location.href = `/dae/courriers/${newId}`;
                        else await this.fetchCourriers();
                    } catch (e) {
                        alert('Erreur lors de la duplication.');
                    }
                    break;
                case 'supprimer':
                    if (!confirm('Voulez-vous vraiment supprimer ce courrier ?')) return;
                    try {
                        await axios.delete(`/dae/courriers/${row.id}`, {
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        await this.fetchCourriers();
                    } catch (e) {
                        alert('Erreur lors de la suppression.');
                    }
                    break;
            }
        },

        // ─── Assigner modal ────────────────────────────
        async openAssignModal(courrier) {
            this.assignModal.courrier = courrier;
            this.assignModal.selectedUserId = '';
            this.assignModal.show = true;
            this.assignModal.loading = true;
            try {
                const resp = await axios.get('/api/users');
                this.assignModal.users = Array.isArray(resp.data) ? resp.data : (resp.data.data || []);
            } catch (e) {
                console.error('Erreur chargement utilisateurs:', e);
                this.assignModal.users = [];
            } finally {
                this.assignModal.loading = false;
            }
            // Show modal via Bootstrap
            const el = document.getElementById('assignModal');
            if (el && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(el);
                modal.show();
            }
        },

        async confirmAssign() {
            if (!this.assignModal.selectedUserId) {
                alert('Veuillez selectionner un utilisateur.');
                return;
            }
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
            try {
                await axios.post(`/dae/courriers/${this.assignModal.courrier.id}/assigner`, {
                    assigned_to: this.assignModal.selectedUserId
                }, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                // Close modal
                const el = document.getElementById('assignModal');
                if (el && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getInstance(el)?.hide();
                }
                this.assignModal.show = false;
                await this.fetchCourriers();
            } catch (e) {
                alert('Erreur lors de l\'assignation.');
            }
        },

        syncQueryString() {
            const params = new URLSearchParams();

            Object.entries(this.filters).forEach(([key, value]) => {
                if (value !== '' && value !== null && value !== undefined) {
                    params.set(key, value);
                }
            });

            if (this.currentPage > 1) {
                params.set('page', this.currentPage);
            }

            const qs = params.toString();
            const url = qs ? `/dae/courriers?${qs}` : '/dae/courriers';
            window.history.replaceState({}, '', url);
        },

        statutBadgeClass(statut) {
            return STATUT_MAP[statut]?.badge || 'bg-secondary';
        },

        statutLabel(statut) {
            return STATUT_MAP[statut]?.label || statut || '-';
        },

        urgenceBadgeClass(urgence) {
            return URGENCE_MAP[urgence]?.badge || 'bg-success';
        },

        urgenceLabel(urgence) {
            return URGENCE_MAP[urgence]?.label || urgence || '-';
        },

        typeLabel(type) {
            return TYPE_MAP[type] || type || '-';
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
.dae-courriers-index {
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

/* ── Filter bar ── */
.form-label {
    font-weight: 500;
}
</style>
