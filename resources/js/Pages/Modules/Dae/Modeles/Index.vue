<template>
<GelLayout>
    <div class="dae-modeles-index">
        <div v-if="loading && !items.length" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement des modèles...</p>
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
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Modèles de courriers
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-files me-1"></i>Gabarits et modèles de courriers DAE
                            <span class="mx-2">|</span>
                            <span v-if="totalItems > 0">{{ totalItems }} modèle(s)</span>
                        </p>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm mt-2 mt-md-0" @click="openCreateModal">
                    <i class="bi bi-plus-lg me-1"></i>Nouveau modèle
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
                                <option value="courrier">Courrier</option>
                                <option value="note">Note</option>
                                <option value="compte-rendu">Compte-rendu</option>
                                <option value="convocation">Convocation</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1">Catégorie</label>
                            <select v-model="filters.categorie" class="form-select form-select-sm" @change="applyFilters">
                                <option value="">Toutes</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
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
                        @action="handleAction"
                        @page-change="changePage"
                    />
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════
             MODAL: Create / Edit
             ════════════════════════════════════════════════ -->
        <div ref="formModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi me-2 text-primary" :class="editing ? 'bi-pencil' : 'bi-file-earmark-plus'"></i>
                            {{ editing ? 'Modifier le modèle' : 'Nouveau modèle' }}
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
                                    <label class="form-label small text-muted">Type <span class="text-danger">*</span></label>
                                    <select v-model="form.type" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="courrier">Courrier</option>
                                        <option value="note">Note</option>
                                        <option value="compte-rendu">Compte-rendu</option>
                                        <option value="convocation">Convocation</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Catégorie</label>
                                    <input v-model="form.categorie" class="form-control form-control-sm" placeholder="Ex: RH, Juridique, Général" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Client</label>
                                    <select v-model="form.client_id" class="form-select form-select-sm">
                                        <option value="">Général (tous clients)</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom || c.email }}</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Objet par défaut</label>
                                    <input v-model="form.objet_defaut" class="form-control form-control-sm" />
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Corps du modèle</label>
                                    <textarea v-model="form.corps" class="form-control form-control-sm" rows="6"></textarea>
                                    <small class="text-muted">Utilisez {{ variable }} pour les champs dynamiques</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Variables (séparées par des virgules)</label>
                                    <input v-model="variablesInput" class="form-control form-control-sm" placeholder="nom_client, date, montant" />
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
</GelLayout>
</template>

<script>
/*
 * Composant : DaeModelesIndex
 * Role : Page de gestion des modeles de courriers du module DAE.
 * Affiche la liste des modeles avec filtres (type, categorie, client, recherche).
 * Permet la creation, modification, apercu et suppression de modeles.
 * Les modeles servent de gabarits pour les courriers et notes.
 * Props : aucune
 * Evenements : action, page-change (via DaeDataTable)
 */

// Importation du client HTTP et des composants
import axios from 'axios';
import { Modal } from 'bootstrap';
import DaeDataTable from '../../../../Components/Dae/DaeDataTable.vue';

export default {
    name: 'DaeModelesIndex',
    components: { DaeDataTable },
    data() {
        return {
            loading: true,         // Indicateur de chargement
            items: [],             // Liste des modeles
            clients: [],           // Liste des clients pour les filtres
            currentPage: 1,        // Page courante de la pagination
            totalPages: 1,         // Nombre total de pages
            totalItems: 0,         // Nombre total de modeles
            categories: [],        // Liste des categories extraites des modeles
            filters: { type: '', categorie: '', client_id: '', recherche: '' }, // Filtres actifs
            // ─── Modale formulaire ──────────────────────────────
            formModalInstance: null, // Instance de la modale
            editing: null,           // Mode edition (null = creation, objet = modification)
            form: { client_id: '', nom: '', type: '', objet_defaut: '', corps: '', categorie: '' }, // Donnees du formulaire
            variablesInput: '',      // Saisie brute des variables (sep. par virgules)
            formLoading: false,      // Indicateur d'envoi du formulaire
            formError: '',           // Message d'erreur du formulaire
        };
    },
    computed: {
        /* Definition des colonnes de la table des modeles */
        tableColumns() {
            return [
                { key: 'nom', label: 'Nom', class: 'fw-semibold' },
                { key: 'type', label: 'Type' },
                { key: 'objet_defaut', label: 'Objet par défaut', class: 'text-truncate', width: '30%' },
                { key: 'categorie', label: 'Catégorie' },
            ];
        },
        tableActions() {
            return [
                { key: 'apercu', label: 'Aperçu', icon: 'bi-eye' },
                { key: 'modifier', label: 'Modifier', icon: 'bi-pencil' },
                { key: 'supprimer', label: 'Supprimer', icon: 'bi-trash', danger: true },
            ];
        },
    },
    created() {
        /* Chargement initial : clients et modeles en parallele */
        Promise.all([this.fetchClients(), this.fetchItems()]).finally(() => { this.loading = false; });
    },
    methods: {
        /* Recupere la liste des clients depuis l'API */
        async fetchClients() {
            try { const r = await axios.get('/api/clients'); this.clients = Array.isArray(r.data) ? r.data : (r.data.data || []); }
            catch (e) { console.error('Erreur clients:', e); }
        },
        /* Recupere la liste paginee des modeles selon les filtres */
        async fetchItems() {
            try {
                const params = { page: this.currentPage, ...this.filters };
                Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
                const r = await axios.get('/dae/modeles', { params });
                const d = r.data;
                this.items = d.data || [];
                this.currentPage = d.current_page || 1;
                this.totalPages = d.last_page || 1;
                this.totalItems = d.total || 0;
                this.extractCategories();
            } catch (e) {
                console.error('Erreur modèles:', e);
                this.items = []; this.totalPages = 1; this.totalItems = 0;
            }
        },
        /* Extrait la liste unique des categories a partir des modeles charges */
        extractCategories() {
            const cats = new Set();
            this.items.forEach(i => { if (i.categorie) cats.add(i.categorie); });
            this.categories = [...cats].sort();
        },
        /* Applique les filtres et recharge la liste */
        applyFilters() { this.currentPage = 1; this.fetchItems(); },
        /* Change de page dans la pagination */
        changePage(p) { this.currentPage = p; this.fetchItems(); },
        /* Aiguille les actions de la table (apercu, modifier, supprimer) */
        handleAction({ action, row }) {
            if (action === 'apercu') this.apercuModele(row);
            if (action === 'modifier') this.openEditModal(row);
            if (action === 'supprimer') this.deleteItem(row);
        },
        /* Affiche un apercu rapide du modele (objet et debut du corps) */
        apercuModele(row) {
            alert(`Objet: ${row.objet_defaut || '(aucun)'}\n\n${row.corps ? row.corps.substring(0, 200) + '...' : '(corps vide)'}`);
        },
        /* Supprime un modele apres confirmation */
        async deleteItem(row) {
            if (!confirm(`Supprimer le modèle "${row.nom}" ?`)) return;
            try { await axios.delete(`/dae/modeles/${row.id}`); this.fetchItems(); }
            catch (e) { alert('Impossible de supprimer.'); }
        },

        // ─── MODALE FORMULAIRE ────────────────────────────────────

        /* Ouvre la modale de creation d'un nouveau modele */
        openCreateModal() {
            this.formError = '';
            this.editing = null;
            this.form = { client_id: '', nom: '', type: '', objet_defaut: '', corps: '', categorie: '' };
            this.variablesInput = '';
            this.formModalInstance = new Modal(this.$refs.formModal);
            this.formModalInstance.show();
        },
        /* Ouvre la modale d'edition d'un modele existant */
        openEditModal(row) {
            this.formError = '';
            this.editing = row;
            this.form = {
                client_id: row.client_id || '',
                nom: row.nom || '',
                type: row.type || '',
                objet_defaut: row.objet_defaut || '',
                corps: row.corps || '',
                categorie: row.categorie || '',
            };
            this.variablesInput = Array.isArray(row.variables) ? row.variables.join(', ') : '';
            this.formModalInstance = new Modal(this.$refs.formModal);
            this.formModalInstance.show();
        },
        /* Soumet le formulaire de creation ou modification */
        async submitForm() {
            this.formLoading = true;
            this.formError = '';
            try {
                const payload = { ...this.form };
                if (this.variablesInput) {
                    payload.variables = JSON.stringify(
                        this.variablesInput.split(',').map(v => v.trim()).filter(Boolean)
                    );
                }
                if (this.editing) {
                    await axios.put(`/dae/modeles/${this.editing.id}`, payload);
                } else {
                    await axios.post('/dae/modeles', payload);
                }
                this.formModalInstance.hide();
                this.fetchItems();
            } catch (e) {
                this.formError = e.response?.data?.message || "Erreur lors de l'enregistrement.";
            } finally { this.formLoading = false; }
        },
    },
};
</script>

<style scoped>
.dae-modeles-index { padding: 20px; min-height: 80vh; }
.dae-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 20px; }
.dae-spinner { width: 48px; height: 48px; border: 4px solid rgba(255,121,0,0.1); border-top-color: #FF7900; border-radius: 50%; animation: dae-spin 0.7s linear infinite; }
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
.form-label { font-weight: 500; }
</style>
