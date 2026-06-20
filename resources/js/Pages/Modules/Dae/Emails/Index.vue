<template>
    <div class="dae-emails-index">
        <!-- ═══ LOADING STATE ═══ -->
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement des emails...</p>
        </div>

        <!-- ═══ MAIN CONTENT ═══ -->
        <div v-else class="dae-content">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour au tableau de bord">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-envelope-paper me-2 text-primary"></i>Boite email
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-inboxes me-1"></i>Gestion des emails
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} email(s)</span>
                        </p>
                    </div>
                </div>
                <a :href="'/dae/emails/create'" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Nouvel email
                </a>
            </div>

            <div class="row g-0">
                <!-- ═══ LEFT SIDEBAR - DOSSIERS ═══ -->
                <div class="col-12 col-md-3 col-lg-2 pe-md-3 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm">
                        <div class="list-group list-group-flush">
                            <button v-for="f in dossiers" :key="f.key"
                                    class="list-group-item list-group-item-action d-flex align-items-center border-0 py-2 px-3"
                                    :class="{ active: activeDossier === f.key }"
                                    @click="selectDossier(f.key)">
                                <i :class="f.icon + ' me-2'" style="font-size:1.1rem;"></i>
                                <span class="small fw-medium">{{ f.label }}</span>
                                <span v-if="dossierCounts[f.key] !== undefined" class="ms-auto badge bg-secondary rounded-pill">
                                    {{ dossierCounts[f.key] }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ═══ MAIN RIGHT - EMAIL LIST ═══ -->
                <div class="col-12 col-md-9 col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <DaeDataTable
                                :columns="tableColumns"
                                :rows="emails"
                                :actions="tableActions"
                                :currentPage="currentPage"
                                :totalPages="totalPages"
                                :rowClickable="true"
                                @row-click="handleRowClick"
                                @action="handleAction"
                                @page-change="handlePageChange"
                            >
                                <!-- From Slot -->
                                <template #cell-from_address="{ row }">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person me-1 text-muted"></i>
                                        <span class="text-truncate d-inline-block" style="max-width:200px;">
                                            {{ row.from_address || '-' }}
                                        </span>
                                    </div>
                                </template>

                                <!-- Objet Slot -->
                                <template #cell-objet="{ row }">
                                    <span class="fw-medium" :class="{ 'text-muted': row.statut === 'archive' }">
                                        {{ row.objet || '(Sans objet)' }}
                                    </span>
                                </template>

                                <!-- Date Slot -->
                                <template #cell-date="{ row }">
                                    <span class="text-nowrap small">{{ formatDate(row.date_reception || row.date_envoi || row.created_at) }}</span>
                                </template>

                                <!-- Statut Badge Slot -->
                                <template #cell-statut="{ row }">
                                    <span class="badge" :class="statutBadgeClass(row.statut)">
                                        {{ statutLabel(row.statut) }}
                                    </span>
                                </template>
                            </DaeDataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ DETAIL MODAL ═══ -->
        <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true" ref="detailModal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content" v-if="selectedEmail">
                    <div class="modal-header border-bottom-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">{{ selectedEmail.objet }}</h5>
                            <small class="text-muted">
                                {{ statutBadgeInline(selectedEmail.statut) }}
                                <span class="mx-2">|</span>
                                {{ formatDateFull(selectedEmail.date_reception || selectedEmail.date_envoi || selectedEmail.created_at) }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Meta infos -->
                        <div class="mb-3 small bg-light rounded-3 p-3">
                            <div class="row mb-1">
                                <div class="col-3 text-muted fw-medium">De:</div>
                                <div class="col-9">{{ selectedEmail.from_address }}</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-3 text-muted fw-medium">A:</div>
                                <div class="col-9">{{ formatAddresses(selectedEmail.to_addresses) }}</div>
                            </div>
                            <div v-if="selectedEmail.cc_addresses && selectedEmail.cc_addresses.length" class="row mb-1">
                                <div class="col-3 text-muted fw-medium">Cc:</div>
                                <div class="col-9">{{ formatAddresses(selectedEmail.cc_addresses) }}</div>
                            </div>
                            <div v-if="selectedEmail.client" class="row mb-0">
                                <div class="col-3 text-muted fw-medium">Client:</div>
                                <div class="col-9">{{ selectedEmail.client.nom || selectedEmail.client.raison_sociale || selectedEmail.client.email }}</div>
                            </div>
                        </div>

                        <!-- Corps HTML -->
                        <div v-if="selectedEmail.corps_html" class="email-body mb-3 p-3 border rounded-3"
                             v-html="selectedEmail.corps_html">
                        </div>
                        <div v-else-if="selectedEmail.corps_texte" class="email-body mb-3 p-3 border rounded-3">
                            <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit;">{{ selectedEmail.corps_texte }}</pre>
                        </div>
                        <div v-else class="text-muted small mb-3 fst-italic">(Aucun contenu)</div>

                        <!-- Pieces jointes -->
                        <div v-if="hasPiecesJointes" class="mb-3">
                            <small class="text-muted fw-medium d-block mb-2">
                                <i class="bi bi-paperclip me-1"></i>Pieces jointes
                            </small>
                            <div class="d-flex flex-wrap gap-2">
                                <a v-for="(pj, idx) in piecesJointesList" :key="idx"
                                   :href="pj.url || pj.path || '#'"
                                   class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center"
                                   target="_blank">
                                    <i class="bi bi-file-earmark me-1"></i>
                                    {{ pj.nom || pj.name || 'Fichier ' + (idx + 1) }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 d-flex flex-wrap gap-2">
                        <button class="btn btn-primary btn-sm" @click="openReplyModal">
                            <i class="bi bi-reply me-1"></i>Repondre
                        </button>
                        <button v-if="selectedEmail.statut !== 'archive'" class="btn btn-dark btn-sm" @click="archiverEmail(selectedEmail.id)">
                            <i class="bi bi-archive me-1"></i>Archiver
                        </button>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-folder me-1"></i>Classer
                            </button>
                            <ul class="dropdown-menu shadow-sm">
                                <li v-for="d in classerOptions" :key="d.key">
                                    <button class="dropdown-item small" @click="classerEmail(selectedEmail.id, d.key)">
                                        <i :class="d.icon + ' me-2'"></i>{{ d.label }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <button v-if="selectedEmail.statut !== 'lu'" class="btn btn-outline-success btn-sm" @click="marquerLu(selectedEmail.id)">
                            <i class="bi bi-check2-circle me-1"></i>Marquer lu
                        </button>
                        <button class="btn btn-outline-danger btn-sm ms-auto" @click="supprimerEmail(selectedEmail.id)">
                            <i class="bi bi-trash me-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ REPLY MODAL ═══ -->
        <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true" ref="replyModal">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content" v-if="selectedEmail">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-reply me-1 text-primary"></i>
                            Repondre a: {{ selectedEmail.objet }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-medium">A:</label>
                            <input type="text" class="form-control form-control-sm" :value="selectedEmail.from_address" disabled />
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-medium">Objet:</label>
                            <input type="text" class="form-control form-control-sm" :value="'Re: ' + selectedEmail.objet" disabled />
                        </div>
                        <div class="mb-2">
                            <label for="replyCorps" class="form-label small text-muted fw-medium">Votre message</label>
                            <textarea id="replyCorps" v-model="replyBody" class="form-control" rows="8"
                                      placeholder="Ecrivez votre reponse ici..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" :disabled="sendingReply" @click="sendReply">
                            <span v-if="sendingReply" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-send me-1"></i>
                            Envoyer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ TOAST NOTIFICATION ═══ -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
            <div v-if="toast.show" class="toast show align-items-center border-0 shadow-lg"
                 :class="'text-bg-' + toast.type" role="alert">
                <div class="d-flex">
                    <div class="toast-body small">
                        <i :class="toast.icon + ' me-2'"></i>{{ toast.message }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toast.show = false"></button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import DaeDataTable from '../../../../Components/Dae/DaeDataTable.vue';
import { Modal } from 'bootstrap';

const STATUT_MAP = {
    brouillon: { label: 'Brouillon', badge: 'bg-secondary' },
    envoye:    { label: 'Envoye',    badge: 'bg-primary' },
    recu:      { label: 'Recu',      badge: 'bg-info' },
    lu:        { label: 'Lu',        badge: 'bg-success' },
    archive:   { label: 'Archive',   badge: 'bg-dark' },
};

const DOSSIERS = [
    { key: 'reception', label: 'Reception',  icon: 'bi-inbox' },
    { key: 'envoyes',   label: 'Envoyes',    icon: 'bi-send' },
    { key: 'brouillons', label: 'Brouillons', icon: 'bi-pencil' },
    { key: 'archive',   label: 'Archive',    icon: 'bi-archive' },
    { key: 'corbeille', label: 'Corbeille',  icon: 'bi-trash' },
];

export default {
    name: 'dae-emails-index',

    components: {
        DaeDataTable,
    },

    data() {
        return {
            loading: false,
            emails: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            activeDossier: 'reception',
            dossierCounts: {},
            selectedEmail: null,
            detailModal: null,
            replyModal: null,
            replyBody: '',
            sendingReply: false,
            toast: { show: false, type: 'success', icon: 'bi-check-circle', message: '' },
        };
    },

    computed: {
        dossiers() {
            return DOSSIERS;
        },

        tableColumns() {
            return [
                { key: 'from_address', label: 'De',       class: 'text-truncate', width: '25%' },
                { key: 'objet',        label: 'Objet',    class: 'text-truncate', width: '35%' },
                { key: 'date',         label: 'Date',     width: '20%' },
                { key: 'statut',       label: 'Statut',   width: '20%' },
            ];
        },

        tableActions() {
            return [
                { key: 'repondre',  label: 'Repondre',   icon: 'bi-reply' },
                { key: 'archiver',  label: 'Archiver',   icon: 'bi-archive' },
                { key: 'marquer-lu', label: 'Marquer lu', icon: 'bi-check2-circle' },
                { key: 'supprimer', label: 'Supprimer',  icon: 'bi-trash', danger: true },
            ];
        },

        classerOptions() {
            return DOSSIERS.filter(d => d.key !== this.activeDossier);
        },

        hasPiecesJointes() {
            if (!this.selectedEmail) return false;
            const pj = this.selectedEmail.pieces_jointes;
            return pj && ((Array.isArray(pj) && pj.length > 0) || (typeof pj === 'string' && pj.length > 0));
        },

        piecesJointesList() {
            const pj = this.selectedEmail?.pieces_jointes;
            if (!pj) return [];
            if (typeof pj === 'string') {
                try { return JSON.parse(pj); } catch { return []; }
            }
            return Array.isArray(pj) ? pj : [];
        },
    },

    created() {
        this.fetchEmails();
        this.fetchStats();
    },

    mounted() {
        // Initialiser les modals Bootstrap apres le rendu
        this.$nextTick(() => {
            const detailEl = this.$refs.detailModal;
            if (detailEl) {
                this.detailModal = new Modal(detailEl);
                detailEl.addEventListener('hidden.bs.modal', () => {
                    this.selectedEmail = null;
                });
            }
            const replyEl = this.$refs.replyModal;
            if (replyEl) {
                this.replyModal = new Modal(replyEl);
            }
        });
    },

    methods: {
        async fetchEmails() {
            this.loading = true;

            try {
                const params = {
                    page: this.currentPage,
                    dossier: this.activeDossier !== 'corbeille' ? this.activeDossier : undefined,
                };

                // Pour la corbeille, on ne filtre pas par dossier
                if (this.activeDossier === 'corbeille') {
                    delete params.dossier;
                    params.with_trashed = true;
                }

                const response = await axios.get('/dae/emails', { params });
                const data = response.data;

                this.emails = data.data || [];
                this.currentPage = data.current_page || 1;
                this.totalPages = data.last_page || 1;
                this.totalItems = data.total || 0;
            } catch (error) {
                console.error('Erreur lors du chargement des emails:', error);
                this.emails = [];
                this.totalPages = 1;
                this.totalItems = 0;
            } finally {
                this.loading = false;
            }
        },

        async fetchStats() {
            try {
                const response = await axios.get('/dae/emails/stats');
                const data = response.data;
                this.dossierCounts = {
                    reception: data.recus || 0,
                    envoyes: data.envoyes || 0,
                    brouillons: data.brouillons || 0,
                    archive: data.archives || 0,
                };
            } catch (error) {
                console.error('Erreur lors du chargement des stats:', error);
            }
        },

        selectDossier(dossier) {
            if (this.activeDossier === dossier) return;
            this.activeDossier = dossier;
            this.currentPage = 1;
            this.fetchEmails();
        },

        applyFilters() {
            this.currentPage = 1;
            this.fetchEmails();
        },

        handlePageChange(page) {
            this.currentPage = page;
            this.fetchEmails();
        },

        async handleRowClick(row) {
            try {
                const response = await axios.get(`/dae/emails/${row.id}`);
                this.selectedEmail = response.data;
                // Rafraichir la liste si le statut a change (recu -> lu)
                this.fetchEmails();
                this.fetchStats();
                this.$nextTick(() => {
                    this.detailModal?.show();
                });
            } catch (error) {
                console.error('Erreur lors du chargement du detail:', error);
                this.showToast('danger', 'bi-exclamation-triangle', 'Impossible de charger le detail de cet email.');
            }
        },

        async handleAction({ action, row }) {
            switch (action) {
                case 'repondre':
                    await this.prepareReply(row);
                    break;
                case 'archiver':
                    await this.archiverEmail(row.id);
                    break;
                case 'marquer-lu':
                    await this.marquerLu(row.id);
                    break;
                case 'supprimer':
                    await this.supprimerEmail(row.id);
                    break;
            }
        },

        async prepareReply(row) {
            try {
                const response = await axios.get(`/dae/emails/${row.id}`);
                this.selectedEmail = response.data;
                this.replyBody = '';
                this.$nextTick(() => {
                    this.replyModal?.show();
                });
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('danger', 'bi-exclamation-triangle', 'Impossible de preparer la reponse.');
            }
        },

        openReplyModal() {
            this.replyBody = '';
            // Detail modal reste ouvert en arriere-plan
            this.$nextTick(() => {
                this.replyModal?.show();
            });
        },

        async sendReply() {
            if (!this.replyBody.trim()) {
                this.showToast('warning', 'bi-exclamation-circle', 'Veuillez ecrire un message.');
                return;
            }

            this.sendingReply = true;

            try {
                const payload = {
                    corps_texte: this.replyBody,
                    corps_html: null,
                    client_id: this.selectedEmail.client_id,
                };

                await axios.post(`/dae/emails/${this.selectedEmail.id}/repondre`, payload);
                this.showToast('success', 'bi-check-circle', 'Reponse envoyee avec succes.');
                this.replyModal?.hide();
                this.replyBody = '';
                this.fetchEmails();
                this.fetchStats();
            } catch (error) {
                console.error('Erreur lors de l\'envoi de la reponse:', error);
                this.showToast('danger', 'bi-exclamation-triangle', 'Erreur lors de l\'envoi de la reponse.');
            } finally {
                this.sendingReply = false;
            }
        },

        async archiverEmail(id) {
            try {
                const response = await axios.patch(`/dae/emails/${id}/archiver`);
                this.showToast('success', 'bi-archive', 'Email archive avec succes.');
                this.detailModal?.hide();
                this.selectedEmail = null;
                this.fetchEmails();
                this.fetchStats();
            } catch (error) {
                console.error('Erreur lors de l\'archivage:', error);
                this.showToast('danger', 'bi-exclamation-triangle', 'Erreur lors de l\'archivage.');
            }
        },

        async classerEmail(id, dossier) {
            try {
                await axios.patch(`/dae/emails/${id}/classer`, { dossier });
                this.showToast('success', 'bi-folder', 'Email classe dans ' + (DOSSIERS.find(d => d.key === dossier)?.label || dossier) + '.');
                this.detailModal?.hide();
                this.selectedEmail = null;
                this.fetchEmails();
                this.fetchStats();
            } catch (error) {
                console.error('Erreur lors du classement:', error);
                this.showToast('danger', 'bi-exclamation-triangle', 'Erreur lors du classement.');
            }
        },

        async marquerLu(id) {
            try {
                const response = await axios.patch(`/dae/emails/${id}/lu`);
                this.showToast('success', 'bi-check2-circle', 'Email marque comme lu.');
                this.detailModal?.hide();
                this.selectedEmail = null;
                this.fetchEmails();
                this.fetchStats();
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('danger', 'bi-exclamation-triangle', 'Erreur.');
            }
        },

        async supprimerEmail(id) {
            if (!confirm('Voulez-vous vraiment supprimer cet email ?')) return;

            try {
                await axios.delete(`/dae/emails/${id}`);
                this.showToast('success', 'bi-trash', 'Email supprime.');
                this.detailModal?.hide();
                this.selectedEmail = null;
                this.fetchEmails();
                this.fetchStats();
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                this.showToast('danger', 'bi-exclamation-triangle', 'Erreur lors de la suppression.');
            }
        },

        showToast(type, icon, message) {
            this.toast = { show: true, type, icon, message };
            setTimeout(() => {
                this.toast.show = false;
            }, 4000);
        },

        /* ── Helpers ── */
        statutBadgeClass(statut) {
            return STATUT_MAP[statut]?.badge || 'bg-secondary';
        },

        statutLabel(statut) {
            return STATUT_MAP[statut]?.label || statut || '-';
        },

        statutBadgeInline(statut) {
            const info = STATUT_MAP[statut];
            if (!info) return '';
            return `<span class="badge ${info.badge}">${info.label}</span>`;
        },

        formatAddresses(addresses) {
            if (!addresses) return '-';
            if (typeof addresses === 'string') {
                try { addresses = JSON.parse(addresses); } catch { return addresses; }
            }
            return Array.isArray(addresses) ? addresses.join(', ') : String(addresses);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                const now = new Date();
                const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

                if (diffDays === 0) {
                    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                }
                if (diffDays === 1) return 'Hier';
                if (diffDays < 7) {
                    return date.toLocaleDateString('fr-FR', { weekday: 'short' });
                }
                return date.toLocaleDateString('fr-FR', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                });
            } catch {
                return dateStr;
            }
        },

        formatDateFull(dateStr) {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('fr-FR', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                    hour: '2-digit', minute: '2-digit',
                });
            } catch {
                return dateStr;
            }
        },
    },
};
</script>

<style scoped>
.dae-emails-index {
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

/* ── Sidebar ── */
.list-group-item {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.list-group-item.active {
    background-color: rgba(255, 121, 0, 0.08);
    border-color: transparent !important;
    color: #FF7900;
    font-weight: 600;
}
.list-group-item.active i {
    color: #FF7900;
}
.list-group-item:hover:not(.active) {
    background-color: #f8f9fa;
}

/* ── Email body (rendered HTML) ── */
.email-body {
    background-color: #fff;
    font-size: 14px;
    line-height: 1.7;
    max-height: 400px;
    overflow-y: auto;
}
.email-body :deep(img) {
    max-width: 100%;
    height: auto;
}
.email-body :deep(pre) {
    white-space: pre-wrap;
    word-break: break-word;
}

/* ── Toast ── */
.toast {
    min-width: 280px;
}
</style>
