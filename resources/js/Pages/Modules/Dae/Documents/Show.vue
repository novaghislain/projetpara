<template>
<GelLayout>
    <div class="dae-documents-show">
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement du document...</p>
        </div>

        <div v-else-if="!document" class="dae-loading">
            <i class="bi bi-exclamation-circle text-muted" style="font-size:3rem;"></i>
            <p class="mt-3 text-muted">Document introuvable</p>
            <a href="/dae/documents" class="btn btn-outline-secondary btn-sm mt-2">
                <i class="bi bi-arrow-left me-1"></i>Retour aux documents
            </a>
        </div>

        <div v-else class="dae-content">
            <!-- Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae/documents" class="btn btn-outline-secondary btn-sm" title="Retour aux documents">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="mb-0 fw-bold">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ document.titre }}
                        </h4>
                        <p class="text-muted small mb-0 mt-1">
                            <span class="badge" :class="statutBadgeClass(document.statut)">
                                {{ statutLabel(document.statut) }}
                            </span>
                            <span class="ms-2">Réf. {{ document.reference }}</span>
                            <span class="ms-2">v{{ document.version }}</span>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <a :href="`/dae/documents/${document.id}/download`" class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                    <button class="btn btn-primary btn-sm" @click="openVersionModal">
                        <i class="bi bi-arrow-up-circle me-1"></i>Nouvelle version
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <!-- Main info -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 px-4">
                            <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6" v-if="document.type_document">
                                    <label class="text-muted small mb-1">Type de document</label>
                                    <p class="mb-0 fw-medium">{{ typeLabel(document.type_document) }}</p>
                                </div>
                                <div class="col-md-6" v-if="document.categorie">
                                    <label class="text-muted small mb-1">Catégorie</label>
                                    <p class="mb-0 fw-medium">{{ categorieLabel(document.categorie) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Version</label>
                                    <p class="mb-0 fw-medium">{{ document.version }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Taille</label>
                                    <p class="mb-0 fw-medium">{{ formatSize(document.taille_fichier) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Type MIME</label>
                                    <p class="mb-0 fw-medium"><code>{{ document.mime_type || '-' }}</code></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Client</label>
                                    <p class="mb-0 fw-medium">{{ document.client?.nom || document.client?.email || '—' }}</p>
                                </div>
                                <div class="col-12" v-if="document.description">
                                    <label class="text-muted small mb-1">Description</label>
                                    <p class="mb-0">{{ document.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar info -->
                <div class="col-12 col-lg-4">
                    <!-- Dates -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-calendar me-2 text-primary"></i>Dates</h6>
                            <div class="mb-2">
                                <label class="text-muted small mb-0">Créé le</label>
                                <p class="mb-0">{{ formatDate(document.created_at) }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small mb-0">Modifié le</label>
                                <p class="mb-0">{{ formatDate(document.updated_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted small mb-0">Expiration</label>
                                <p class="mb-0" :class="{ 'text-danger fw-bold': isExpired(document.date_expiration) }">
                                    <template v-if="document.date_expiration">
                                        {{ formatDate(document.date_expiration) }}
                                        <i v-if="isExpired(document.date_expiration)" class="bi bi-exclamation-triangle-fill ms-1 text-danger" title="Expiré"></i>
                                    </template>
                                    <span v-else class="text-muted">—</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status flags -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-check2-square me-2 text-primary"></i>Statut</h6>
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" :checked="document.valide" @change="toggleValide" />
                                <label class="form-check-label small">Document valide</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" :checked="document.signe" @change="toggleSigne" />
                                <label class="form-check-label small">Document signé</label>
                            </div>
                            <div class="mt-3" v-if="document.alerte_expiration">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-bell me-1"></i>Alerte expiration active
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mots-clés -->
                    <div class="card border-0 shadow-sm mb-3" v-if="document.mots_cles && document.mots_cles.length">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-tags me-2 text-primary"></i>Mots-clés</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark" v-for="tag in document.mots_cles" :key="tag">
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Version Modal -->
        <div ref="versionModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-arrow-up-circle me-2 text-primary"></i>Nouvelle version
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <form @submit.prevent="submitVersion" enctype="multipart/form-data">
                        <div class="modal-body">
                            <p class="small text-muted mb-3">
                                Nouvelle version pour : <strong>{{ document?.titre }}</strong>
                                <br><span class="text-info">Version actuelle : v{{ document?.version }}</span>
                            </p>
                            <label class="form-label small text-muted">Fichier <span class="text-danger">*</span></label>
                            <input type="file" ref="versionFileInput" class="form-control form-control-sm"
                                   @change="onVersionFileChange" required />
                            <div v-if="versionError" class="alert alert-danger small mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ versionError }}
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="versioning">
                                <span v-if="versioning">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    Envoi...
                                </span>
                                <span v-else>
                                    <i class="bi bi-arrow-up-circle me-1"></i>Ajouter v{{ (document?.version || 0) + 1 }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</GelLayout>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';

const TYPE_MAP = {
    contrat: 'Contrat', rapport: 'Rapport', facture: 'Facture',
    courrier: 'Courrier', note: 'Note', devis: 'Devis',
    proces_verbal: 'Procès-verbal', autre: 'Autre',
};

const CATEGORIE_MAP = {
    administratif: 'Administratif', juridique: 'Juridique',
    financier: 'Financier', technique: 'Technique',
    rh: 'Ressources humaines', commercial: 'Commercial', autre: 'Autre',
};

const STATUT_MAP = {
    brouillon: { label: 'Brouillon', badge: 'bg-secondary' },
    final:     { label: 'Final',     badge: 'bg-success' },
    archive:   { label: 'Archivé',   badge: 'bg-dark' },
    supprime:  { label: 'Supprimé',  badge: 'bg-danger' },
};

export default {
    name: 'DaeDocumentsShow',

    data() {
        return {
            loading: true,
            document: null,

            // Version modal
            versionModal: null,
            versionFile: null,
            versioning: false,
            versionError: '',
        };
    },

    created() {
        this.fetchDocument();
    },

    methods: {
        async fetchDocument() {
            this.loading = true;
            const id = window.location.pathname.split('/').pop();

            try {
                const response = await axios.get(`/dae/documents/${id}`);
                this.document = response.data;
            } catch (error) {
                console.error('Erreur chargement document:', error);
                this.document = null;
            } finally {
                this.loading = false;
            }
        },

        async toggleValide() {
            try {
                const response = await axios.put(`/dae/documents/${this.document.id}`, {
                    valide: !this.document.valide,
                });
                this.document.valide = response.data.valide;
            } catch (error) {
                console.error('Erreur mise à jour:', error);
            }
        },

        async toggleSigne() {
            try {
                const response = await axios.put(`/dae/documents/${this.document.id}`, {
                    signe: !this.document.signe,
                });
                this.document.signe = response.data.signe;
            } catch (error) {
                console.error('Erreur mise à jour:', error);
            }
        },

        // ── Version modal ──

        openVersionModal() {
            this.versionFile = null;
            this.versionError = '';
            this.versioning = false;
            if (this.$refs.versionFileInput) this.$refs.versionFileInput.value = '';
            this.versionModal = new Modal(this.$refs.versionModal);
            this.versionModal.show();
        },

        onVersionFileChange(event) {
            this.versionFile = event.target.files[0] || null;
        },

        async submitVersion() {
            if (!this.versionFile) {
                this.versionError = 'Veuillez sélectionner un fichier.';
                return;
            }
            this.versioning = true;
            this.versionError = '';

            try {
                const formData = new FormData();
                formData.append('fichier', this.versionFile);

                const response = await axios.post(`/dae/documents/${this.document.id}/version`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });

                this.versionModal.hide();
                this.document = response.data;
            } catch (error) {
                this.versionError = error.response?.data?.message || 'Erreur lors de l\'ajout de la version.';
                console.error('Erreur version:', error);
            } finally {
                this.versioning = false;
            }
        },

        // ── Helpers ──

        typeLabel(type) { return TYPE_MAP[type] || type || '-'; },
        categorieLabel(cat) { return CATEGORIE_MAP[cat] || cat || '-'; },
        statutBadgeClass(s) { return STATUT_MAP[s]?.badge || 'bg-secondary'; },
        statutLabel(s) { return STATUT_MAP[s]?.label || s || '-'; },

        formatSize(bytes) {
            if (!bytes && bytes !== 0) return '-';
            const sizes = ['o', 'Ko', 'Mo', 'Go'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            if (i === 0) return `${bytes} ${sizes[i]}`;
            return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            try {
                return new Date(dateStr).toLocaleDateString('fr-FR', {
                    day: '2-digit', month: 'long', year: 'numeric',
                    hour: '2-digit', minute: '2-digit',
                });
            } catch { return dateStr; }
        },

        isExpired(dateStr) {
            if (!dateStr) return false;
            return new Date(dateStr) < new Date(new Date().setHours(0, 0, 0, 0));
        },
    },
};
</script>

<style scoped>
.dae-documents-show {
    padding: 20px;
    min-height: 80vh;
}
.dae-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
}
.dae-spinner {
    width: 48px; height: 48px;
    border: 4px solid rgba(255, 121, 0, 0.1);
    border-top-color: #FF7900;
    border-radius: 50%;
    animation: dae-spin 0.7s linear infinite;
}
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
.form-switch .form-check-input { cursor: pointer; }
</style>
