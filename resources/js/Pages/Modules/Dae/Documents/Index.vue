<template>
<GelLayout>
    <div class="dae-documents-index">
        <!-- ═══ LOADING ═══ -->
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement des documents...</p>
        </div>

        <!-- ═══ CONTENT ═══ -->
        <div v-else class="dae-content">
            <!-- ── Header ── -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour tableau de bord">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Documents
                        </h1>
                        <p class="text-muted small mb-0">
                            Gestion documentaire
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} document(s)</span>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <button class="btn btn-outline-primary btn-sm" @click="openDossierModal(null)">
                        <i class="bi bi-folder-plus me-1"></i>Nouveau dossier
                    </button>
                    <button class="btn btn-primary btn-sm" @click="openUploadModal">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter un document
                    </button>
                </div>
            </div>

            <!-- ── 2-Column Layout ── -->
            <div class="row g-3">
                <!-- ═══ SIDEBAR — Arbre des dossiers ═══ -->
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm dae-folder-sidebar">
                        <div class="card-body p-0">
                            <!-- Racine "Tous les documents" -->
                            <div class="dossier-item p-2 border-bottom"
                                 :class="{ 'dossier-actif': !dossierActif }"
                                 @click="selectDossier(null)">
                                <i class="bi bi-folder2-open text-primary me-2"></i>
                                <span class="fw-medium small">Tous les documents</span>
                                <span class="badge bg-light text-dark ms-auto">{{ totalItems }}</span>
                            </div>

                            <!-- Arbre récursif -->
                            <div class="dossier-arbre">
                                <div v-for="d in dossiers" :key="d.id" class="dossier-arbre-noeud">
                                    <DossierTreeItem
                                        :dossier="d"
                                        :actif="dossierActif"
                                        :niveau="0"
                                        @select="selectDossier"
                                        @edit="openDossierModal"
                                        @delete="deleteDossier"
                                    />
                                </div>
                            </div>

                            <div v-if="dossiers.length === 0" class="text-center text-muted py-4 small">
                                <i class="bi bi-folder d-block mb-1" style="font-size:1.5rem;"></i>
                                Aucun dossier
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ MAIN CONTENT ═══ -->
                <div class="col-12 col-md-8 col-lg-9">
                    <!-- ── Breadcrumb ── -->
                    <div v-if="breadcrumb.length" class="d-flex align-items-center gap-1 mb-3 small text-muted flex-wrap">
                        <a href="#" class="text-decoration-none" @click.prevent="selectDossier(null)">Tous</a>
                        <span v-for="(anc, i) in breadcrumb" :key="anc.id" class="d-flex align-items-center gap-1">
                            <i class="bi bi-chevron-right" style="font-size:0.6rem;"></i>
                            <a v-if="i < breadcrumb.length - 1" href="#" class="text-decoration-none"
                               @click.prevent="selectDossier(anc.id)">{{ anc.nom }}</a>
                            <span v-else class="fw-semibold text-dark">{{ anc.nom }}</span>
                        </span>
                    </div>

                    <!-- ── Filtres ── -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body py-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">Type</label>
                                    <select v-model="filters.type_document" class="form-select form-select-sm" @change="applyFilters">
                                        <option value="">Tous</option>
                                        <option value="contrat">Contrat</option>
                                        <option value="rapport">Rapport</option>
                                        <option value="facture">Facture</option>
                                        <option value="courrier">Courrier</option>
                                        <option value="note">Note</option>
                                        <option value="devis">Devis</option>
                                        <option value="proces_verbal">Procès-verbal</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">Catégorie</label>
                                    <select v-model="filters.categorie" class="form-select form-select-sm" @change="applyFilters">
                                        <option value="">Toutes</option>
                                        <option value="administratif">Administratif</option>
                                        <option value="juridique">Juridique</option>
                                        <option value="financier">Financier</option>
                                        <option value="technique">Technique</option>
                                        <option value="rh">Ressources humaines</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">Statut</label>
                                    <select v-model="filters.statut" class="form-select form-select-sm" @change="applyFilters">
                                        <option value="">Tous</option>
                                        <option value="brouillon">Brouillon</option>
                                        <option value="final">Final</option>
                                        <option value="archive">Archivé</option>
                                        <option value="supprime">Supprimé</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group input-group-sm">
                                        <input type="text" v-model="filters.recherche" class="form-control"
                                               placeholder="Rechercher..." @keyup.enter="applyFilters" />
                                        <button class="btn btn-primary" type="button" @click="applyFilters">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Table ── -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <DaeDataTable
                                :columns="tableColumns"
                                :rows="documents"
                                :actions="tableActions"
                                :currentPage="currentPage"
                                :totalPages="totalPages"
                                :rowClickable="true"
                                @row-click="handleRowClick"
                                @action="handleAction"
                                @page-change="handlePageChange"
                            >
                                <template #cell-statut="{ row }">
                                    <span class="badge" :class="statutBadgeClass(row.statut)">
                                        {{ statutLabel(row.statut) }}
                                    </span>
                                </template>
                                <template #cell-taille_fichier="{ row }">
                                    <span>{{ formatSize(row.taille_fichier) }}</span>
                                </template>
                                <template #cell-date_expiration="{ row }">
                                    <span v-if="row.date_expiration" class="text-nowrap"
                                          :class="{ 'text-danger fw-semibold': isExpired(row.date_expiration) }">
                                        {{ formatDate(row.date_expiration) }}
                                        <i v-if="isExpired(row.date_expiration)" class="bi bi-exclamation-triangle-fill ms-1 text-danger"></i>
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </template>
                                <template #cell-dossier="{ row }">
                                    <span v-if="row.dossier" class="small text-muted">
                                        <i class="bi bi-folder me-1"></i>{{ row.dossier.nom }}
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </template>
                            </DaeDataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════ -->
        <!-- ═══ DOSSIER MODAL (créer / modifier) ═══ -->
        <!-- ════════════════════════════════════════════════════ -->
        <div ref="dossierModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi me-2 text-primary" :class="dossierEdition ? 'bi-pencil' : 'bi-folder-plus'"></i>
                            {{ dossierEdition ? 'Modifier le dossier' : 'Nouveau dossier' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <form @submit.prevent="submitDossier">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Nom <span class="text-danger">*</span></label>
                                <input type="text" v-model="dossierForm.nom" class="form-control form-control-sm"
                                       placeholder="Nom du dossier" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Dossier parent</label>
                                <select v-model="dossierForm.parent_id" class="form-select form-select-sm">
                                    <option :value="null">Aucun (dossier racine)</option>
                                    <option v-for="d in flatDossierList" :key="d.id"
                                            :value="d.id" :disabled="d.id === dossierEdition?.id">
                                        <span v-for="i in d.niveau" :key="i" class="ms-2"></span>
                                        {{ d.nom }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Couleur</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" v-for="c in couleurs" :key="c"
                                            class="btn btn-sm rounded-circle p-2 border-0"
                                            :class="{ 'border border-2 border-dark': dossierForm.couleur === c }"
                                            :style="{ backgroundColor: c, width: '32px', height: '32px' }"
                                            @click="dossierForm.couleur = (dossierForm.couleur === c ? null : c)">
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label small text-muted">Description</label>
                                <textarea v-model="dossierForm.description" class="form-control form-control-sm" rows="2"
                                          placeholder="Optionnelle"></textarea>
                            </div>
                            <div v-if="dossierError" class="alert alert-danger small mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ dossierError }}
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="dossierLoading">
                                <span v-if="dossierLoading">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Enregistrement...
                                </span>
                                <span v-else>
                                    <i class="bi bi-check-lg me-1"></i>{{ dossierEdition ? 'Modifier' : 'Créer' }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════ -->
        <!-- ═══ UPLOAD MODAL ═══ -->
        <!-- ════════════════════════════════════════════════════ -->
        <div ref="uploadModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-upload me-2 text-primary"></i>Ajouter un document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <form @submit.prevent="submitUpload" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small text-muted">Titre <span class="text-danger">*</span></label>
                                    <input type="text" v-model="uploadForm.titre" class="form-control form-control-sm" placeholder="Titre du document" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Type <span class="text-danger">*</span></label>
                                    <select v-model="uploadForm.type_document" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="contrat">Contrat</option>
                                        <option value="rapport">Rapport</option>
                                        <option value="facture">Facture</option>
                                        <option value="courrier">Courrier</option>
                                        <option value="note">Note</option>
                                        <option value="devis">Devis</option>
                                        <option value="proces_verbal">Procès-verbal</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Catégorie <span class="text-danger">*</span></label>
                                    <select v-model="uploadForm.categorie" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="administratif">Administratif</option>
                                        <option value="juridique">Juridique</option>
                                        <option value="financier">Financier</option>
                                        <option value="technique">Technique</option>
                                        <option value="rh">Ressources humaines</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Dossier</label>
                                    <select v-model="uploadForm.dossier_id" class="form-select form-select-sm">
                                        <option :value="null">Aucun dossier</option>
                                        <option v-for="d in flatDossierList" :key="d.id" :value="d.id">
                                            <span v-for="i in d.niveau" :key="i" class="ms-2"></span>
                                            {{ d.nom }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Date d'expiration</label>
                                    <input type="date" v-model="uploadForm.date_expiration" class="form-control form-control-sm" />
                                </div>
                                <div class="col-12">
                                    <textarea v-model="uploadForm.description" class="form-control form-control-sm" rows="2"
                                              placeholder="Description optionnelle..."></textarea>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small text-muted">Fichier <span class="text-danger">*</span></label>
                                    <input type="file" ref="fileInput" class="form-control form-control-sm" @change="onFileChange" required />
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" v-model="uploadForm.alerte_expiration" class="form-check-input" id="uploadAlerte" />
                                        <label class="form-check-label small" for="uploadAlerte">Alerte expiration</label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="uploadError" class="alert alert-danger small mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ uploadError }}
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="uploading">
                                <span v-if="uploading">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Envoi...
                                </span>
                                <span v-else><i class="bi bi-cloud-upload me-1"></i>Uploader</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════ -->
        <!-- ═══ VERSION MODAL ═══ -->
        <!-- ════════════════════════════════════════════════════ -->
        <div ref="versionModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-arrow-up-circle me-2 text-primary"></i>Nouvelle version</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <form @submit.prevent="submitVersion">
                        <div class="modal-body">
                            <p class="small text-muted mb-3">
                                Nouvelle version pour : <strong>{{ versionDocument?.titre }}</strong>
                            </p>
                            <input type="file" ref="versionFileInput" class="form-control form-control-sm" @change="onVersionFileChange" required />
                            <div v-if="versionError" class="alert alert-danger small mt-3 mb-0">{{ versionError }}</div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="versioning">
                                <span v-if="versioning"><span class="spinner-border spinner-border-sm me-1"></span>Envoi...</span>
                                <span v-else><i class="bi bi-arrow-up-circle me-1"></i>Ajouter la version</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════ -->
        <!-- ═══ DEPLACER MODAL ═══ -->
        <!-- ════════════════════════════════════════════════════ -->
        <div ref="deplacerModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-folder-symlink me-2 text-primary"></i>Déplacer le document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Déplacer : <strong>{{ deplacerDocument?.titre }}</strong>
                        </p>
                        <select v-model="deplacerDossierId" class="form-select">
                            <option :value="null">Aucun dossier (racine)</option>
                            <option v-for="d in flatDossierList" :key="d.id" :value="d.id">
                                <span v-for="i in d.niveau" :key="i" class="ms-2"></span>
                                {{ d.nom }}
                            </option>
                        </select>
                        <div v-if="deplacerError" class="alert alert-danger small mt-3 mb-0">{{ deplacerError }}</div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="deplacerLoading" @click="confirmDeplacer">
                            <span v-if="deplacerLoading"><span class="spinner-border spinner-border-sm me-1"></span>...</span>
                            <span v-else><i class="bi bi-check-lg me-1"></i>Déplacer</span>
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
import { Modal } from 'bootstrap';
import DaeDataTable from '../../../../Components/Dae/DaeDataTable.vue';
import DossierTreeItem from '../../../../Components/Dae/DossierTreeItem.vue';

const STATUT_MAP = {
    brouillon: { label: 'Brouillon', badge: 'bg-secondary' },
    final:     { label: 'Final',     badge: 'bg-success' },
    archive:   { label: 'Archivé',   badge: 'bg-dark' },
    supprime:  { label: 'Supprimé',  badge: 'bg-danger' },
};

const COULEURS = ['#0d6efd', '#198754', '#dc3545', '#fd7e14', '#ffc107', '#6f42c1', '#d63384', '#6c757d'];

export default {
    name: 'DaeDocumentsIndex',

    components: { DaeDataTable, DossierTreeItem },

    data() {
        return {
            // Loading
            loading: true,

            // Documents
            documents: [],
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            filters: {
                type_document: '',
                categorie: '',
                statut: '',
                recherche: '',
            },

            // Dossier arbre
            dossiers: [],
            dossierActif: null,          // id du dossier sélectionné ou null
            flatDossierList: [],         // liste plate pour les selects

            // Breadcrumb
            dossiersMap: {},

            // ── Dossier modal ──
            dossierModal: null,
            dossierEdition: null,
            dossierForm: { nom: '', parent_id: null, couleur: null, description: '' },
            dossierLoading: false,
            dossierError: '',

            // ── Upload modal ──
            uploadModal: null,
            uploadForm: {
                titre: '', type_document: '', categorie: '', description: '',
                dossier_id: null, date_expiration: '', alerte_expiration: false,
            },
            uploadFile: null,
            uploading: false,
            uploadError: '',

            // ── Version modal ──
            versionModal: null,
            versionDocument: null,
            versionFile: null,
            versioning: false,
            versionError: '',

            // ── Déplacer modal ──
            deplacerModal: null,
            deplacerDocument: null,
            deplacerDossierId: null,
            deplacerLoading: false,
            deplacerError: '',

            // Couleurs
            couleurs: COULEURS,
        };
    },

    computed: {
        tableColumns() {
            const cols = [
                { key: 'reference',          label: 'Référence' },
                { key: 'titre',              label: 'Titre', class: 'text-truncate', width: '20%' },
                { key: 'type_document',      label: 'Type' },
                { key: 'categorie',          label: 'Catégorie' },
                { key: 'version',            label: 'Version' },
                { key: 'taille_fichier',     label: 'Taille' },
            ];
            // Show dossier column only if folders exist
            if (this.flatDossierList.length > 0) {
                cols.push({ key: 'dossier', label: 'Dossier' });
            }
            cols.push({ key: 'statut', label: 'Statut' });
            cols.push({ key: 'date_expiration', label: 'Expiration' });
            return cols;
        },

        tableActions() {
            const acts = [
                { key: 'voir',            label: 'Voir',              icon: 'bi-eye' },
                { key: 'telecharger',     label: 'Télécharger',       icon: 'bi-download' },
                { key: 'nouvelle_version', label: 'Nouvelle version', icon: 'bi-arrow-up-circle' },
            ];
            if (this.flatDossierList.length > 0) {
                acts.push({ key: 'deplacer', label: 'Déplacer', icon: 'bi-folder-symlink' });
            }
            acts.push({ key: 'supprimer',   label: 'Supprimer',       icon: 'bi-trash', danger: true });
            return acts;
        },

        breadcrumb() {
            const crumbs = [];
            let id = this.dossierActif;
            const visited = new Set();
            while (id) {
                if (visited.has(id)) break;
                visited.add(id);
                const d = this.dossiersMap[id];
                if (!d) break;
                crumbs.unshift({ id: d.id, nom: d.nom });
                id = d.parent_id;
            }
            return crumbs;
        },
    },

    created() {
        this.loadAll();
    },

    methods: {
        // ── Init ──

        async loadAll() {
            this.loading = true;
            await Promise.all([this.fetchDossiers(), this.fetchDocuments()]);
            this.loading = false;
        },

        // ── Dossiers ──

        async fetchDossiers() {
            try {
                const response = await axios.get('/dae/documents/dossiers/arbre');
                this.dossiers = response.data || [];

                // Build flat list + map for breadcrumb
                this.flatDossierList = [];
                this.dossiersMap = {};
                this.flattenDossiers(this.dossiers, 0);

                // If active dossier still exists in map, keep it; otherwise reset
                if (this.dossierActif && !this.dossiersMap[this.dossierActif]) {
                    this.dossierActif = null;
                }
            } catch (e) {
                console.error('Erreur chargement dossiers:', e);
                this.dossiers = [];
                this.flatDossierList = [];
                this.dossiersMap = {};
            }
        },

        flattenDossiers(liste, niveau) {
            for (const d of liste) {
                this.flatDossierList.push({
                    id: d.id, nom: d.nom, parent_id: d.parent_id, couleur: d.couleur, niveau,
                });
                this.dossiersMap[d.id] = { id: d.id, nom: d.nom, parent_id: d.parent_id, couleur: d.couleur };
                if (d.enfants?.length) {
                    this.flattenDossiers(d.enfants, niveau + 1);
                }
            }
        },

        selectDossier(id) {
            this.dossierActif = id;
            this.currentPage = 1;
            this.fetchDocuments();
        },

        openDossierModal(dossier) {
            this.dossierError = '';
            if (dossier) {
                this.dossierEdition = dossier;
                this.dossierForm = {
                    nom: dossier.nom || '',
                    parent_id: dossier.parent_id || null,
                    couleur: dossier.couleur || null,
                    description: '',
                };
            } else {
                this.dossierEdition = null;
                this.dossierForm = { nom: '', parent_id: this.dossierActif, couleur: null, description: '' };
            }
            this.dossierModal = new Modal(this.$refs.dossierModal);
            this.dossierModal.show();
        },

        async submitDossier() {
            if (!this.dossierForm.nom.trim()) return;
            this.dossierLoading = true;
            this.dossierError = '';

            try {
                if (this.dossierEdition) {
                    await axios.put(`/dae/documents/dossiers/${this.dossierEdition.id}`, this.dossierForm);
                } else {
                    await axios.post('/dae/documents/dossiers', {
                        ...this.dossierForm,
                        client_id: 1, // TODO: use actual client context
                    });
                }
                this.dossierModal.hide();
                await this.fetchDossiers();
            } catch (e) {
                this.dossierError = e.response?.data?.message || 'Erreur lors de la sauvegarde.';
            } finally {
                this.dossierLoading = false;
            }
        },

        async deleteDossier(dossier) {
            if (!confirm(`Supprimer le dossier "${dossier.nom}" ?\nLes sous-dossiers remonteront d'un niveau et les documents perdront leur dossier.`)) {
                return;
            }
            try {
                await axios.delete(`/dae/documents/dossiers/${dossier.id}`);
                if (this.dossierActif === dossier.id) this.dossierActif = null;
                await this.fetchDossiers();
                await this.fetchDocuments();
            } catch (e) {
                console.error('Erreur suppression dossier:', e);
                alert('Impossible de supprimer le dossier.');
            }
        },

        // ── Documents ──

        async fetchDocuments() {
            try {
                const params = {
                    page: this.currentPage,
                    ...this.filters,
                    dossier_id: this.dossierActif || undefined,
                };
                Object.keys(params).forEach(k => {
                    if (params[k] === '' || params[k] === null || params[k] === undefined) delete params[k];
                });

                const response = await axios.get('/dae/documents', { params });
                const data = response.data;
                this.documents = data.data || [];
                this.currentPage = data.current_page || 1;
                this.totalPages = data.last_page || 1;
                this.totalItems = data.total || 0;

                this.syncQueryString();
            } catch (e) {
                console.error('Erreur chargement documents:', e);
                this.documents = [];
                this.totalPages = 1;
                this.totalItems = 0;
            }
        },

        applyFilters() {
            this.currentPage = 1;
            this.fetchDocuments();
        },

        handlePageChange(page) {
            this.currentPage = page;
            this.fetchDocuments();
        },

        handleRowClick(row) {
            window.location.href = `/dae/documents/${row.id}`;
        },

        handleAction({ action, row }) {
            switch (action) {
                case 'voir':
                    window.location.href = `/dae/documents/${row.id}`;
                    break;
                case 'telecharger':
                    window.open(`/dae/documents/${row.id}/download`, '_blank');
                    break;
                case 'nouvelle_version':
                    this.openVersionModal(row);
                    break;
                case 'deplacer':
                    this.openDeplacerModal(row);
                    break;
                case 'supprimer':
                    this.deleteDocument(row);
                    break;
            }
        },

        syncQueryString() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => {
                if (v !== '' && v !== null && v !== undefined) params.set(k, v);
            });
            if (this.currentPage > 1) params.set('page', this.currentPage);
            if (this.dossierActif) params.set('dossier_id', this.dossierActif);
            const qs = params.toString();
            window.history.replaceState({}, '', qs ? `/dae/documents?${qs}` : '/dae/documents');
        },

        async deleteDocument(row) {
            if (!confirm(`Supprimer "${row.titre}" ?`)) return;
            try {
                await axios.delete(`/dae/documents/${row.id}`);
                this.fetchDocuments();
            } catch (e) {
                console.error('Erreur suppression:', e);
                alert('Impossible de supprimer.');
            }
        },

        // ── Upload ──

        openUploadModal() {
            this.resetUploadForm();
            this.uploadForm.dossier_id = this.dossierActif;
            this.uploadModal = new Modal(this.$refs.uploadModal);
            this.uploadModal.show();
        },

        onFileChange(e) { this.uploadFile = e.target.files[0] || null; },

        resetUploadForm() {
            this.uploadForm = {
                titre: '', type_document: '', categorie: '', description: '',
                dossier_id: this.dossierActif, date_expiration: '', alerte_expiration: false,
            };
            this.uploadFile = null;
            this.uploadError = '';
            this.uploading = false;
        },

        async submitUpload() {
            if (!this.uploadFile) { this.uploadError = 'Sélectionnez un fichier.'; return; }
            this.uploading = true;
            this.uploadError = '';
            try {
                const fd = new FormData();
                fd.append('client_id', 1);
                fd.append('titre', this.uploadForm.titre);
                fd.append('type_document', this.uploadForm.type_document);
                fd.append('categorie', this.uploadForm.categorie);
                fd.append('description', this.uploadForm.description);
                if (this.uploadForm.dossier_id) fd.append('dossier_id', this.uploadForm.dossier_id);
                fd.append('fichier', this.uploadFile);
                if (this.uploadForm.date_expiration) fd.append('date_expiration', this.uploadForm.date_expiration);
                fd.append('alerte_expiration', this.uploadForm.alerte_expiration ? '1' : '0');

                await axios.post('/dae/documents', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                this.uploadModal.hide();
                this.fetchDocuments();
            } catch (e) {
                this.uploadError = e.response?.data?.message || "Erreur lors de l'upload.";
            } finally { this.uploading = false; }
        },

        // ── Version ──

        openVersionModal(row) {
            this.versionDocument = row;
            this.versionFile = null;
            this.versionError = '';
            this.versioning = false;
            if (this.$refs.versionFileInput) this.$refs.versionFileInput.value = '';
            this.versionModal = new Modal(this.$refs.versionModal);
            this.versionModal.show();
        },

        onVersionFileChange(e) { this.versionFile = e.target.files[0] || null; },

        async submitVersion() {
            if (!this.versionFile) { this.versionError = 'Sélectionnez un fichier.'; return; }
            this.versioning = true;
            this.versionError = '';
            try {
                const fd = new FormData();
                fd.append('fichier', this.versionFile);
                await axios.post(`/dae/documents/${this.versionDocument.id}/version`, fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.versionModal.hide();
                this.fetchDocuments();
            } catch (e) {
                this.versionError = e.response?.data?.message || "Erreur lors de l'ajout de version.";
            } finally { this.versioning = false; }
        },

        // ── Déplacer ──

        openDeplacerModal(row) {
            this.deplacerDocument = row;
            this.deplacerDossierId = row.dossier_id || null;
            this.deplacerError = '';
            this.deplacerLoading = false;
            this.deplacerModal = new Modal(this.$refs.deplacerModal);
            this.deplacerModal.show();
        },

        async confirmDeplacer() {
            this.deplacerLoading = true;
            this.deplacerError = '';
            try {
                await axios.patch(`/dae/documents/${this.deplacerDocument.id}/dossier`, {
                    dossier_id: this.deplacerDossierId,
                });
                this.deplacerModal.hide();
                this.fetchDocuments();
            } catch (e) {
                this.deplacerError = e.response?.data?.message || 'Erreur lors du déplacement.';
            } finally { this.deplacerLoading = false; }
        },

        // ── Helpers ──

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
            try { return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }); }
            catch { return dateStr; }
        },

        isExpired(dateStr) {
            if (!dateStr) return false;
            return new Date(dateStr) < new Date(new Date().setHours(0, 0, 0, 0));
        },
    },
};
</script>

<style scoped>
.dae-documents-index {
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

/* ── Sidebar ── */
.dae-folder-sidebar .dossier-item {
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.15s;
    font-size: 0.875rem;
}
.dae-folder-sidebar .dossier-item:hover {
    background: rgba(255, 121, 0, 0.05);
}
.dae-folder-sidebar .dossier-item.dossier-actif {
    background: rgba(255, 121, 0, 0.1);
    font-weight: 600;
    border-left: 3px solid #FF7900;
}

/* ── Breadcrumb ── */
.dae-content a { color: #0d6efd; }
.dae-content a:hover { text-decoration: underline !important; }
</style>
