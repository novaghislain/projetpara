<template>
<GelLayout>
    <div class="dae-contrats-index">
        <!-- ═══ LOADING STATE ═══ -->
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement des contrats...</p>
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
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Contrats
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-briefcase me-1"></i>Gestion des contrats DAE
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} contrat(s)</span>
                        </p>
                    </div>
                </div>
                <div>
                    <a :href="'/dae/contrats/create'" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Nouveau contrat
                    </a>
                </div>
            </div>

            <!-- ═══ FILTER BAR ═══ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <!-- Statut -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Statut</label>
                            <select v-model="filters.statut" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <option value="brouillon">Brouillon</option>
                                <option value="actif">Actif</option>
                                <option value="expire">Expiré</option>
                                <option value="resilie">Résilié</option>
                                <option value="renouvele">Renouvelé</option>
                            </select>
                        </div>

                        <!-- Type de contrat -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Type de contrat</label>
                            <select v-model="filters.type_contrat" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <option value="prestation">Prestation</option>
                                <option value="bail">Bail</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="consulting">Consulting</option>
                                <option value="fourniture">Fourniture</option>
                                <option value="partenariat">Partenariat</option>
                                <option value="autres">Autres</option>
                            </select>
                        </div>

                        <!-- Client -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Client</label>
                            <select v-model="filters.client_id" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.nom || client.raison_sociale || client.email }}
                                </option>
                            </select>
                        </div>

                        <!-- Search + Filter button -->
                        <div class="col-6 col-md-3">
                            <div class="input-group input-group-sm">
                                <input type="text" v-model="filters.recherche" class="form-control" placeholder="Rechercher..."
                                       @keyup.enter="applyFilters" />
                                <button class="btn btn-primary" type="button" @click="applyFilters" title="Filtrer">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ EXPIRATION WARNING BANNER ═══ -->
            <div v-if="expiringContracts.length > 0" class="alert alert-warning d-flex align-items-center mb-3 py-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span class="small">
                    <strong>{{ expiringContracts.length }} contrat(s)</strong> arrivent à expiration dans les 30 prochains jours.
                </span>
            </div>

            <!-- ═══ DATA TABLE ═══ -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <DaeDataTable
                        :columns="tableColumns"
                        :rows="contrats"
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
                            <span :class="rowExpiring(row) ? 'badge bg-warning text-dark' : statutBadgeClass(row.statut)">
                                {{ statutLabel(row.statut) }}
                            </span>
                        </template>

                        <!-- Type Slot -->
                        <template #cell-type_contrat="{ row }">
                            {{ typeContratLabel(row.type_contrat) }}
                        </template>

                        <!-- Date début Slot -->
                        <template #cell-date_debut="{ row }">
                            <span class="text-nowrap">{{ formatDate(row.date_debut) }}</span>
                        </template>

                        <!-- Date fin Slot -->
                        <template #cell-date_fin="{ row }">
                            <span class="text-nowrap" :class="{ 'text-danger fw-semibold': rowExpiring(row) }">
                                {{ formatDate(row.date_fin) }}
                                <i v-if="rowExpiring(row)" class="bi bi-exclamation-circle ms-1"></i>
                            </span>
                        </template>

                        <!-- Montant Slot -->
                        <template #cell-montant="{ row }">
                            <span class="text-nowrap">{{ formatCurrency(row.montant) }}</span>
                        </template>
                    </DaeDataTable>
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
    brouillon: { label: 'Brouillon', badge: 'bg-secondary' },
    actif:     { label: 'Actif',     badge: 'bg-success' },
    expire:    { label: 'Expiré',    badge: 'bg-secondary' },
    resilie:   { label: 'Résilié',   badge: 'bg-danger' },
    renouvele: { label: 'Renouvelé', badge: 'bg-info' },
};

const TYPE_CONTRAT_MAP = {
    prestation:  'Prestation',
    bail:        'Bail',
    maintenance: 'Maintenance',
    consulting:  'Consulting',
    fourniture:  'Fourniture',
    partenariat: 'Partenariat',
    autres:      'Autres',
};

export default {
    name: 'DaeContratsIndex',

    components: {
        DaeDataTable,
    },

    data() {
        const params = new URLSearchParams(window.location.search);

        return {
            loading: false,
            contrats: [],
            clients: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            filters: {
                statut:       params.get('statut') || '',
                type_contrat: params.get('type_contrat') || '',
                client_id:    params.get('client_id') || '',
                recherche:    params.get('recherche') || '',
            },
        };
    },

    computed: {
        tableColumns() {
            return [
                { key: 'reference',      label: 'Référence' },
                { key: 'titre',          label: 'Titre', class: 'text-truncate', width: '20%' },
                { key: 'type_contrat',   label: 'Type' },
                { key: 'partie_adverse', label: 'Partie adverse' },
                { key: 'date_debut',     label: 'Début' },
                { key: 'date_fin',       label: 'Fin' },
                { key: 'montant',        label: 'Montant' },
                { key: 'statut',         label: 'Statut' },
            ];
        },

        tableActions() {
            return [
                { key: 'voir',        label: 'Voir',        icon: 'bi-eye' },
                { key: 'renouveler',  label: 'Renouveler',  icon: 'bi-arrow-repeat' },
                { key: 'telecharger', label: 'Télécharger', icon: 'bi-download' },
                { key: 'supprimer',   label: 'Supprimer',   icon: 'bi-trash', danger: true },
            ];
        },

        expiringContracts() {
            const now = new Date();
            const thirtyDays = 30 * 24 * 60 * 60 * 1000;

            return this.contrats.filter(row =>
                row.statut === 'actif' &&
                row.date_fin &&
                (new Date(row.date_fin) - now) <= thirtyDays &&
                (new Date(row.date_fin) - now) > 0
            );
        },
    },

    created() {
        this.fetchContrats();
        this.fetchClients();
    },

    methods: {
        async fetchContrats() {
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

                const response = await axios.get('/dae/contrats', { params });
                const data = response.data;

                this.contrats = data.data || [];
                this.currentPage = data.current_page || 1;
                this.totalPages = data.last_page || 1;
                this.totalItems = data.total || 0;

                // Sync query string
                this.syncQueryString();
            } catch (error) {
                console.error('Erreur lors du chargement des contrats:', error);
                this.contrats = [];
                this.totalPages = 1;
                this.totalItems = 0;
            } finally {
                this.loading = false;
            }
        },

        async fetchClients() {
            try {
                const response = await axios.get('/api/clients/list');
                this.clients = response.data || [];
            } catch (error) {
                console.error('Erreur lors du chargement des clients:', error);
                this.clients = [];
            }
        },

        applyFilters() {
            this.currentPage = 1;
            this.fetchContrats();
        },

        handlePageChange(page) {
            this.currentPage = page;
            this.fetchContrats();
        },

        handleRowClick(row) {
            this.$inertia.visit(`/dae/contrats/${row.id}`);
        },

        handleAction({ action, row }) {
            switch (action) {
                case 'voir':
                    this.$inertia.visit(`/dae/contrats/${row.id}`);
                    break;
                case 'renouveler':
                    this.$inertia.visit(`/dae/contrats/${row.id}/renouveler`, {
                        method: 'post',
                    });
                    break;
                case 'telecharger':
                    this.$inertia.visit(`/dae/contrats/${row.id}/telecharger`);
                    break;
                case 'supprimer':
                    if (confirm('Voulez-vous vraiment supprimer ce contrat ?')) {
                        this.$inertia.visit(`/dae/contrats/${row.id}`, {
                            method: 'delete',
                        });
                    }
                    break;
            }
        },

        rowExpiring(row) {
            if (row.statut !== 'actif' || !row.date_fin) return false;

            const now = new Date();
            const dateFin = new Date(row.date_fin);
            const diff = dateFin - now;
            const thirtyDays = 30 * 24 * 60 * 60 * 1000;

            return diff > 0 && diff <= thirtyDays;
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
            const url = qs ? `/dae/contrats?${qs}` : '/dae/contrats';
            window.history.replaceState({}, '', url);
        },

        statutBadgeClass(statut) {
            return STATUT_MAP[statut]?.badge || 'bg-secondary';
        },

        statutLabel(statut) {
            return STATUT_MAP[statut]?.label || statut || '-';
        },

        typeContratLabel(type) {
            return TYPE_CONTRAT_MAP[type] || type || '-';
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

        formatCurrency(amount) {
            if (amount === null || amount === undefined || amount === '') return '-';
            try {
                return new Intl.NumberFormat('fr-FR', {
                    style: 'currency',
                    currency: 'EUR',
                }).format(amount);
            } catch {
                return `${amount}`;
            }
        },
    },
};
</script>

<style scoped>
.dae-contrats-index {
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
