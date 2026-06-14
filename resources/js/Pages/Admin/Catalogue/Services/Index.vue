<script setup>
import { ref, reactive } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    categories: { type: Array, required: true },
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// ── Modals state ─────────────────────────────────────────────────────────
const showCatModal     = ref(false);
const showServiceModal = ref(false);
const editingCat       = ref(null);
const editingService   = ref(null);

const catProcessing = ref(false);
const serviceProcessing = ref(false);

// ── Formulaire Catégorie ──────────────────────────────────────────────────
const catForm = reactive({
    nom:         '',
    icone:       '',
    description: '',
    couleur:     '',
    ordre:       0,
    actif:       true,
});

function resetCatForm() {
    catForm.nom = '';
    catForm.icone = '';
    catForm.description = '';
    catForm.couleur = '';
    catForm.ordre = 0;
    catForm.actif = true;
}

function openNewCat() {
    editingCat.value = null;
    resetCatForm();
    showCatModal.value = true;
}

function openEditCat(cat) {
    editingCat.value = cat;
    catForm.nom         = cat.nom;
    catForm.icone       = cat.icone || '';
    catForm.description = cat.description || '';
    catForm.couleur     = cat.couleur || '';
    catForm.ordre       = cat.ordre || 0;
    catForm.actif       = cat.actif;
    showCatModal.value  = true;
}

async function submitCat() {
    catProcessing.value = true;
    try {
        const url = editingCat.value 
            ? `/admin/catalogue/categories/${editingCat.value.id}` 
            : '/admin/catalogue/categories';
        
        const method = editingCat.value ? 'PUT' : 'POST';

        await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(catForm)
        });
        window.location.reload();
    } catch (e) {
        console.error(e);
        catProcessing.value = false;
    }
}

async function deleteCat(id) {
    if (confirm('Supprimer cette catégorie et tous ses services ?')) {
        try {
            await fetch(`/admin/catalogue/categories/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            window.location.reload();
        } catch (e) { console.error(e); }
    }
}

// ── Formulaire Service ────────────────────────────────────────────────────
const serviceForm = reactive({
    category_id:            '',
    nom:                    '',
    description:            '',
    inclus_json:            [],
    delai_jours:            '',
    tarif_fcfa:             '',
    tarif_type:             'fixe',
    documents_requis_json:  [],
    champs_formulaire_json: [],
    ordre_affichage:        0,
    actif:                  true,
});

function resetServiceForm() {
    serviceForm.category_id = '';
    serviceForm.nom = '';
    serviceForm.description = '';
    serviceForm.inclus_json = [];
    serviceForm.delai_jours = '';
    serviceForm.tarif_fcfa = '';
    serviceForm.tarif_type = 'fixe';
    serviceForm.documents_requis_json = [];
    serviceForm.champs_formulaire_json = [];
    serviceForm.ordre_affichage = 0;
    serviceForm.actif = true;
}

// Items temporaires (listes dynamiques)
const newInclusItem     = ref('');
const newDocItem        = ref('');

function addInclusItem() {
    if (newInclusItem.value.trim()) {
        serviceForm.inclus_json.push(newInclusItem.value.trim());
        newInclusItem.value = '';
    }
}
function removeInclusItem(idx) { serviceForm.inclus_json.splice(idx, 1); }

function addDocItem() {
    if (newDocItem.value.trim()) {
        serviceForm.documents_requis_json.push(newDocItem.value.trim());
        newDocItem.value = '';
    }
}
function removeDocItem(idx) { serviceForm.documents_requis_json.splice(idx, 1); }

function openNewService(categoryId) {
    editingService.value = null;
    resetServiceForm();
    serviceForm.category_id = categoryId;
    showServiceModal.value = true;
}

function openEditService(service) {
    editingService.value = service;
    serviceForm.category_id            = service.category_id;
    serviceForm.nom                    = service.nom;
    serviceForm.description            = service.description || '';
    serviceForm.inclus_json            = service.inclus_json || [];
    serviceForm.delai_jours            = service.delai_jours || '';
    serviceForm.tarif_fcfa             = service.tarif_fcfa || '';
    serviceForm.tarif_type             = service.tarif_type;
    serviceForm.documents_requis_json  = service.documents_requis_json || [];
    serviceForm.champs_formulaire_json = service.champs_formulaire_json || [];
    serviceForm.ordre_affichage        = service.ordre_affichage || 0;
    serviceForm.actif                  = service.actif;
    showServiceModal.value = true;
}

async function submitService() {
    serviceProcessing.value = true;
    try {
        const url = editingService.value 
            ? `/admin/catalogue/services/${editingService.value.id}` 
            : '/admin/catalogue/services';
        
        const method = editingService.value ? 'PUT' : 'POST';

        await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(serviceForm)
        });
        window.location.reload();
    } catch (e) {
        console.error(e);
        serviceProcessing.value = false;
    }
}

async function deleteService(id) {
    if (confirm('Supprimer ce service ?')) {
        try {
            await fetch(`/admin/catalogue/services/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            window.location.reload();
        } catch (e) { console.error(e); }
    }
}
</script>

<template>
    <GelLayout page-title="Gestion Catalogue">
        <div class="py-4 px-2">
            <!-- Navbar -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <h1 class="h3 fw-bold mb-0 text-dark font-heading">Gestion du Catalogue de Services</h1>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="/admin/catalogue/orders" class="btn btn-link text-decoration-none">
                        Kanban Commandes
                    </a>
                    <button @click="openNewCat" class="btn btn-primary d-inline-flex align-items-center rounded-3">
                        <i class="bi-plus-lg me-2"></i> Nouvelle catégorie
                    </button>
                </div>
            </div>

            <!-- Contenu -->
            <div class="row g-4">
                <div v-for="cat in categories" :key="cat.id" class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        
                        <!-- En-tête catégorie -->
                        <div class="card-header bg-light border-0 px-4 py-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <span v-if="cat.icone" v-html="cat.icone" class="fs-4"></span>
                                <div>
                                    <h2 class="h5 fw-bold text-dark mb-0 font-heading">{{ cat.nom }}</h2>
                                    <p v-if="cat.description" class="text-muted small mb-0">{{ cat.description }}</p>
                                </div>
                                <span class="badge rounded-pill ms-2" :class="cat.actif ? 'bg-success' : 'bg-danger'">
                                    {{ cat.actif ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button @click="openEditCat(cat)" class="btn btn-sm btn-outline-primary rounded-3">Modifier</button>
                                <button @click="deleteCat(cat.id)" class="btn btn-sm btn-outline-danger rounded-3">Supprimer</button>
                                <button @click="openNewService(cat.id)" class="btn btn-sm btn-primary d-inline-flex align-items-center rounded-3">
                                    <i class="bi-plus-lg me-1"></i> Ajouter un service
                                </button>
                            </div>
                        </div>

                        <!-- Grille services -->
                        <div class="card-body p-4 bg-white">
                            <div v-if="cat.services.length > 0" class="row g-3">
                                <div v-for="service in cat.services" :key="service.id" class="col-md-6 col-lg-4">
                                    <div class="card h-100 border rounded-4 hover-shadow transition-all group-hover-container">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <h3 class="fw-semibold text-dark h6 mb-0">{{ service.nom }}</h3>
                                                <span class="badge rounded-pill" :class="service.actif ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'">
                                                    {{ service.actif ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </div>
                                            <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ service.description }}</p>
                                            <div class="d-flex align-items-center justify-content-between text-muted small">
                                                <span class="fw-semibold text-primary">
                                                    {{ service.tarif_type === 'fixe' ? `${Number(service.tarif_fcfa).toLocaleString('fr-FR')} FCFA` : 'Sur devis' }}
                                                </span>
                                                <span>{{ service.delai_jours || 'Délai N/A' }}</span>
                                            </div>
                                            <div class="mt-3 d-flex gap-2">
                                                <button @click="openEditService(service)" class="btn btn-sm btn-primary flex-grow-1 rounded-3">Modifier</button>
                                                <button @click="deleteService(service.id)" class="btn btn-sm btn-danger bg-opacity-10 text-danger border-0 flex-grow-1 rounded-3 hover-danger">Supprimer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-5 text-muted small fst-italic border border-2 border-dashed rounded-4">
                                Aucun service. Cliquez sur "Ajouter un service" pour commencer.
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!categories.length" class="col-12 text-center py-5">
                    <i class="bi-folder-x display-1 text-muted opacity-50"></i>
                    <h3 class="mt-4 h5 fw-medium text-dark">Catalogue vide</h3>
                    <p class="mt-2 text-muted">Créez votre première catégorie de services.</p>
                    <button @click="openNewCat" class="mt-3 btn btn-primary px-4 py-2 rounded-3">Nouvelle catégorie</button>
                </div>
            </div>
        </div>

        <!-- ── Modal Catégorie ──────────────────────────────────────────────── -->
        <div v-if="showCatModal" class="modal-backdrop fade show" style="z-index: 1040;"></div>
        <div v-if="showCatModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">{{ editingCat ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h5>
                        <button type="button" class="btn-close" @click="showCatModal = false"></button>
                    </div>
                    <form @submit.prevent="submitCat" class="modal-body pt-3 pb-4">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nom *</label>
                            <input v-model="catForm.nom" type="text" required class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-medium">Icône (emoji ou HTML)</label>
                                <input v-model="catForm.icone" type="text" class="form-control form-control-sm rounded-3">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-medium">Ordre d'affichage</label>
                                <input v-model="catForm.ordre" type="number" class="form-control form-control-sm rounded-3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Description</label>
                            <textarea v-model="catForm.description" rows="2" class="form-control form-control-sm rounded-3"></textarea>
                        </div>
                        <div class="form-check mb-4">
                            <input id="cat_actif" v-model="catForm.actif" type="checkbox" class="form-check-input">
                            <label for="cat_actif" class="form-check-label small">Catégorie active (visible publiquement)</label>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" @click="showCatModal = false" class="btn btn-light rounded-3">Annuler</button>
                            <button type="submit" :disabled="catProcessing" class="btn btn-primary rounded-3">
                                <span v-if="catProcessing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                {{ editingCat ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── Modal Service ──────────────────────────────────────────────────── -->
        <div v-if="showServiceModal" class="modal-backdrop fade show" style="z-index: 1040;"></div>
        <div v-if="showServiceModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                        <h5 class="modal-title fw-bold text-dark">{{ editingService ? 'Modifier le service' : 'Nouveau service' }}</h5>
                        <button type="button" class="btn-close" @click="showServiceModal = false"></button>
                    </div>
                    <form @submit.prevent="submitService" class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Nom du service *</label>
                                <input v-model="serviceForm.nom" type="text" required class="form-control form-control-sm rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Catégorie *</label>
                                <select v-model="serviceForm.category_id" required class="form-select form-select-sm rounded-3">
                                    <option value="">-- Choisir --</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-medium">Description</label>
                            <textarea v-model="serviceForm.description" rows="3" class="form-control form-control-sm rounded-3"></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-medium">Type de tarif *</label>
                                <select v-model="serviceForm.tarif_type" class="form-select form-select-sm rounded-3">
                                    <option value="fixe">Tarif fixe</option>
                                    <option value="devis">Sur devis</option>
                                </select>
                            </div>
                            <div v-if="serviceForm.tarif_type === 'fixe'" class="col-md-4">
                                <label class="form-label small fw-medium">Tarif (FCFA)</label>
                                <input v-model="serviceForm.tarif_fcfa" type="number" class="form-control form-control-sm rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-medium">Délai</label>
                                <input v-model="serviceForm.delai_jours" type="text" placeholder="ex: 3 à 5 jours" class="form-control form-control-sm rounded-3">
                            </div>
                        </div>

                        <!-- Ce qui est inclus -->
                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-bold text-dark mb-2">Ce qui est inclus</label>
                            <div class="input-group input-group-sm mb-2">
                                <input v-model="newInclusItem" type="text" placeholder="Ajouter un élément..." class="form-control rounded-start-3">
                                <button type="button" @click="addInclusItem" class="btn btn-primary rounded-end-3 px-3">Ajouter</button>
                            </div>
                            <ul class="list-group list-group-flush rounded-3 bg-white">
                                <li v-for="(item, idx) in serviceForm.inclus_json" :key="idx" class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                                    {{ item }}
                                    <button type="button" @click="removeInclusItem(idx)" class="btn btn-link text-danger p-0 text-decoration-none">
                                        <i class="bi-x-circle-fill"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Documents requis -->
                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-bold text-dark mb-2">Documents requis du client</label>
                            <div class="input-group input-group-sm mb-2">
                                <input v-model="newDocItem" type="text" placeholder="ex: Copie CNI..." class="form-control rounded-start-3">
                                <button type="button" @click="addDocItem" class="btn btn-warning rounded-end-3 px-3 text-dark fw-medium">Ajouter</button>
                            </div>
                            <ul class="list-group list-group-flush rounded-3 bg-white">
                                <li v-for="(doc, idx) in serviceForm.documents_requis_json" :key="idx" class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                                    {{ doc }}
                                    <button type="button" @click="removeDocItem(idx)" class="btn btn-link text-danger p-0 text-decoration-none">
                                        <i class="bi-x-circle-fill"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="form-check mb-2">
                            <input id="svc_actif" v-model="serviceForm.actif" type="checkbox" class="form-check-input">
                            <label for="svc_actif" class="form-check-label small fw-medium text-dark">Service actif (visible publiquement)</label>
                        </div>
                    </form>
                    <div class="modal-footer border-top bg-light rounded-bottom-4 px-4 py-3 d-flex justify-content-end gap-2">
                        <button type="button" @click="showServiceModal = false" class="btn btn-outline-secondary rounded-3">Annuler</button>
                        <button type="button" @click="submitService" :disabled="serviceProcessing" class="btn btn-primary rounded-3 px-4">
                            <span v-if="serviceProcessing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            {{ editingService ? 'Mettre à jour' : 'Créer le service' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
