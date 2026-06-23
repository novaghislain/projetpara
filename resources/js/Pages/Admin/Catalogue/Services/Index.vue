<script setup>
import { ref, reactive, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    categories: { type: Array, required: true },
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// ── Search ──
const searchQuery = ref('');

const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) return props.categories;
    const q = searchQuery.value.toLowerCase();
    return props.categories
        .map(cat => {
            const filtered = (cat.services || []).filter(s =>
                s.nom?.toLowerCase().includes(q) ||
                s.description?.toLowerCase().includes(q) ||
                s.type?.toLowerCase().includes(q)
            );
            return { ...cat, services: filtered };
        })
        .filter(cat => cat.services.length > 0);
});

// ── Accordion ──
const expandedCats = reactive({});

function toggleCat(id) {
    expandedCats[id] = !expandedCats[id];
}

function isExpanded(id) {
    return expandedCats[id] !== false; // default: expanded
}

// Stats
const stats = computed(() => {
    let totalServices = 0, totalModeles = 0, totalCats = 0;
    props.categories.forEach(cat => {
        if (!cat.actif) return;
        totalCats++;
        (cat.services || []).forEach(s => {
            if (s.type === 'modele') totalModeles++;
            else totalServices++;
        });
    });
    return { totalServices, totalModeles, totalCats };
});

// ── Modals state ──
const showCatModal = ref(false);
const showServiceModal = ref(false);
const editingCat = ref(null);
const editingService = ref(null);
const catProcessing = ref(false);
const serviceProcessing = ref(false);

// ── Category Form ──
const catForm = reactive({
    nom: '', icone: '', description: '', couleur: '', ordre: 0, actif: true,
});

function resetCatForm() {
    catForm.nom = ''; catForm.icone = ''; catForm.description = '';
    catForm.couleur = ''; catForm.ordre = 0; catForm.actif = true;
}

function openNewCat() {
    editingCat.value = null; resetCatForm(); showCatModal.value = true;
}

function openEditCat(cat) {
    editingCat.value = cat;
    catForm.nom = cat.nom; catForm.icone = cat.icone || '';
    catForm.description = cat.description || ''; catForm.couleur = cat.couleur || '';
    catForm.ordre = cat.ordre || 0; catForm.actif = cat.actif;
    showCatModal.value = true;
}

async function submitCat() {
    catProcessing.value = true;
    try {
        const url = editingCat.value
            ? `/admin/catalogue/categories/${editingCat.value.id}`
            : '/admin/catalogue/categories';
        await fetch(url, {
            method: editingCat.value ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(catForm)
        });
        window.location.reload();
    } catch (e) { console.error(e); catProcessing.value = false; }
}

async function deleteCat(id) {
    if (confirm('Supprimer cette catégorie et tous ses éléments ?')) {
        try {
            await fetch(`/admin/catalogue/categories/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            window.location.reload();
        } catch (e) { console.error(e); }
    }
}

// ── Service/Model Form ──
const serviceForm = reactive({
    category_id: '', nom: '', type: 'service', description: '',
    inclus_json: [], delai_jours: '', tarif_fcfa: '', tarif_type: 'fixe',
    documents_requis_json: [], champs_formulaire_json: [],
    ordre_affichage: 0, actif: true,
});

function resetServiceForm(type = 'service') {
    serviceForm.category_id = ''; serviceForm.nom = '';
    serviceForm.type = type; serviceForm.description = '';
    serviceForm.inclus_json = []; serviceForm.delai_jours = '';
    serviceForm.tarif_fcfa = ''; serviceForm.tarif_type = 'fixe';
    serviceForm.documents_requis_json = [];
    serviceForm.champs_formulaire_json = [];
    serviceForm.ordre_affichage = 0; serviceForm.actif = true;
}

const newInclusItem = ref('');
const newDocItem = ref('');

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

function openNewService(categoryId, type = 'service') {
    editingService.value = null;
    resetServiceForm(type);
    serviceForm.category_id = categoryId;
    showServiceModal.value = true;
}

function openEditService(svc) {
    editingService.value = svc;
    serviceForm.category_id = svc.category_id;
    serviceForm.nom = svc.nom;
    serviceForm.type = svc.type || 'service';
    serviceForm.description = svc.description || '';
    serviceForm.inclus_json = svc.inclus_json || [];
    serviceForm.delai_jours = svc.delai_jours || '';
    serviceForm.tarif_fcfa = svc.tarif_fcfa || '';
    serviceForm.tarif_type = svc.tarif_type;
    serviceForm.documents_requis_json = svc.documents_requis_json || [];
    serviceForm.champs_formulaire_json = svc.champs_formulaire_json || [];
    serviceForm.ordre_affichage = svc.ordre_affichage || 0;
    serviceForm.actif = svc.actif;
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
    } catch (e) { console.error(e); serviceProcessing.value = false; }
}

async function deleteService(id) {
    if (confirm('Supprimer cet élément ?')) {
        try {
            await fetch(`/admin/catalogue/services/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            window.location.reload();
        } catch (e) { console.error(e); }
    }
}

function typeIcon(type) {
    return type === 'modele' ? 'bi-box-seam' : 'bi-gear-wide-connected';
}

function typeLabel(type) {
    return type === 'modele' ? 'Modèle' : 'Service';
}

function typeBadgeClass(type) {
    return type === 'modele'
        ? 'cat-badge cat-badge--modele'
        : 'cat-badge cat-badge--service';
}
</script>

<template>
    <GelLayout page-title="Catalogue">
        <div class="cat-shell">

            <!-- ══ HEADER ══ -->
            <div class="cat-header">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="cat-header-logo">
                        <i class="bi-grid-3x3-gap"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="cat-header-title">Catalogue — Services &amp; Modèles</div>
                        <div class="cat-header-sub">Gérez vos offres classées par catégorie</div>
                    </div>
                    <div class="cat-header-actions d-flex align-items-center gap-2">
                        <div class="cat-search-wrap">
                            <i class="bi-search cat-search-icon"></i>
                            <input v-model="searchQuery" type="text" class="cat-search" placeholder="Rechercher...">
                        </div>
                        <a href="/admin/catalogue/orders" class="cat-btn cat-btn-outline">
                            <i class="bi-kanban me-1"></i>Commandes
                        </a>
                        <button @click="openNewCat" class="cat-btn cat-btn-primary">
                            <i class="bi-plus-lg me-1"></i>Catégorie
                        </button>
                    </div>
                </div>
                <!-- Stats bar -->
                <div class="cat-stats-bar">
                    <span class="cat-stat"><i class="bi-folder me-1"></i>{{ stats.totalCats }} catégories</span>
                    <span class="cat-stat"><i class="bi-gear-wide-connected me-1"></i>{{ stats.totalServices }} services</span>
                    <span class="cat-stat"><i class="bi-box-seam me-1"></i>{{ stats.totalModeles }} modèles</span>
                </div>
            </div>

            <!-- ══ CONTENU ══ -->
            <div class="p-3">
                <!-- Empty -->
                <div v-if="!filteredCategories.length" class="cat-empty">
                    <i class="bi-folder-x cat-empty-icon"></i>
                    <div class="cat-empty-title">Aucun résultat</div>
                    <p class="cat-empty-desc" v-if="searchQuery">Essayez d'autres termes de recherche.</p>
                    <p class="cat-empty-desc" v-else>Créez votre première catégorie pour commencer.</p>
                    <button v-if="!searchQuery" @click="openNewCat" class="cat-btn cat-btn-primary">
                        <i class="bi-plus-lg me-1"></i>Nouvelle catégorie
                    </button>
                </div>

                <!-- Categories -->
                <div v-for="cat in filteredCategories" :key="cat.id" class="cat-category">
                    <!-- Category header -->
                    <div class="cat-cat-header" :style="cat.couleur ? { borderLeftColor: cat.couleur } : {}"
                         @click="toggleCat(cat.id)">
                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                            <span v-if="cat.icone" class="cat-cat-icon" v-html="cat.icone"></span>
                            <div class="min-w-0">
                                <div class="cat-cat-name">{{ cat.nom }}</div>
                                <p v-if="cat.description" class="cat-cat-desc">{{ cat.description }}</p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="cat-badge" :class="cat.actif ? 'cat-badge--active' : 'cat-badge--inactive'">
                                    {{ cat.actif ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="cat-count-badge">{{ (cat.services || []).length }} élém.</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <button @click.stop="openEditCat(cat)" class="cat-btn-icon" title="Modifier la catégorie">
                                <i class="bi-pencil"></i>
                            </button>
                            <button @click.stop="deleteCat(cat.id)" class="cat-btn-icon cat-btn-icon--danger" title="Supprimer">
                                <i class="bi-trash"></i>
                            </button>
                            <i class="bi-chevron-down cat-chevron" :class="{ 'cat-chevron--open': isExpanded(cat.id) }"></i>
                        </div>
                    </div>

                    <!-- Expanded content -->
                    <div v-if="isExpanded(cat.id)" class="cat-body">
                        <!-- Empty state -->
                        <div v-if="!cat.services?.length" class="cat-empty-small">
                            <i class="bi-inbox"></i>
                            <span>Aucun élément dans cette catégorie.</span>
                        </div>

                        <template v-else>
                            <!-- SERVICES section -->
                            <div v-if="cat.services.filter(s => s.type !== 'modele').length" class="cat-section">
                                <div class="cat-section-title">
                                    <i class="bi-gear-wide-connected me-1"></i>Services
                                    <span class="cat-section-count">{{ cat.services.filter(s => s.type !== 'modele').length }}</span>
                                    <button @click.stop="openNewService(cat.id, 'service')" class="cat-btn-sm">
                                        <i class="bi-plus me-1"></i>Ajouter
                                    </button>
                                </div>
                                <div class="row g-3">
                                    <div v-for="svc in cat.services.filter(s => s.type !== 'modele')" :key="svc.id"
                                         class="col-md-6 col-lg-4">
                                        <div class="cat-card">
                                            <div class="cat-card-head">
                                                <div class="cat-card-name">
                                                    <i class="bi-gear-wide-connected cat-type-icon cat-type-icon--service"></i>
                                                    {{ svc.nom }}
                                                </div>
                                                <span class="cat-badge" :class="svc.actif ? 'cat-badge--active' : 'cat-badge--grey'">
                                                    {{ svc.actif ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </div>
                                            <p class="cat-card-desc">{{ svc.description || 'Aucune description' }}</p>
                                            <div class="cat-card-meta">
                                                <span class="cat-card-price">
                                                    {{ svc.tarif_type === 'fixe' ? `${Number(svc.tarif_fcfa).toLocaleString('fr-FR')} FCFA` : 'Sur devis' }}
                                                </span>
                                                <span class="cat-card-delai">
                                                    <i class="bi-clock me-1"></i>{{ svc.delai_jours || 'Délai N/A' }}
                                                </span>
                                            </div>
                                            <div class="d-flex gap-2 mt-3">
                                                <button @click.stop="openEditService(svc)" class="cat-btn cat-btn-primary btn-sm flex-grow-1">
                                                    <i class="bi-pencil me-1"></i>Modifier
                                                </button>
                                                <button @click.stop="deleteService(svc.id)" class="cat-btn cat-btn-danger btn-sm flex-grow-1">
                                                    <i class="bi-trash me-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODÈLES section -->
                            <div v-if="cat.services.filter(s => s.type === 'modele').length" class="cat-section">
                                <div class="cat-section-title cat-section-title--modele">
                                    <i class="bi-box-seam me-1"></i>Modèles
                                    <span class="cat-section-count">{{ cat.services.filter(s => s.type === 'modele').length }}</span>
                                    <button @click.stop="openNewService(cat.id, 'modele')" class="cat-btn-sm">
                                        <i class="bi-plus me-1"></i>Ajouter
                                    </button>
                                </div>
                                <div class="row g-3">
                                    <div v-for="mdl in cat.services.filter(s => s.type === 'modele')" :key="mdl.id"
                                         class="col-md-6 col-lg-4">
                                        <div class="cat-card cat-card--modele">
                                            <div class="cat-card-head">
                                                <div class="cat-card-name">
                                                    <i class="bi-box-seam cat-type-icon cat-type-icon--modele"></i>
                                                    {{ mdl.nom }}
                                                </div>
                                                <span class="cat-badge" :class="mdl.actif ? 'cat-badge--active' : 'cat-badge--grey'">
                                                    {{ mdl.actif ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </div>
                                            <p class="cat-card-desc">{{ mdl.description || 'Aucune description' }}</p>
                                            <div class="cat-card-meta">
                                                <span class="cat-card-price">
                                                    {{ mdl.tarif_type === 'fixe' ? `${Number(mdl.tarif_fcfa).toLocaleString('fr-FR')} FCFA` : 'Sur devis' }}
                                                </span>
                                                <span class="cat-card-delai">
                                                    <i class="bi-clock me-1"></i>{{ mdl.delai_jours || 'Délai N/A' }}
                                                </span>
                                            </div>
                                            <div class="d-flex gap-2 mt-3">
                                                <button @click.stop="openEditService(mdl)" class="cat-btn cat-btn-primary btn-sm flex-grow-1">
                                                    <i class="bi-pencil me-1"></i>Modifier
                                                </button>
                                                <button @click.stop="deleteService(mdl.id)" class="cat-btn cat-btn-danger btn-sm flex-grow-1">
                                                    <i class="bi-trash me-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inline add buttons when section is empty -->
                            <div v-if="!cat.services.filter(s => s.type !== 'modele').length && cat.services.filter(s => s.type === 'modele').length"
                                 class="cat-section">
                                <div class="cat-section-title">
                                    <i class="bi-gear-wide-connected me-1"></i>Services
                                    <button @click.stop="openNewService(cat.id, 'service')" class="cat-btn-sm">
                                        <i class="bi-plus me-1"></i>Ajouter un service
                                    </button>
                                </div>
                            </div>
                            <div v-if="!cat.services.filter(s => s.type === 'modele').length && cat.services.filter(s => s.type !== 'modele').length"
                                 class="cat-section">
                                <div class="cat-section-title cat-section-title--modele">
                                    <i class="bi-box-seam me-1"></i>Modèles
                                    <button @click.stop="openNewService(cat.id, 'modele')" class="cat-btn-sm">
                                        <i class="bi-plus me-1"></i>Ajouter un modèle
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Add buttons when category is empty -->
                        <div v-if="!cat.services?.length" class="d-flex gap-2 mt-3">
                            <button @click.stop="openNewService(cat.id, 'service')" class="cat-btn cat-btn-outline btn-sm">
                                <i class="bi-plus me-1"></i>Nouveau service
                            </button>
                            <button @click.stop="openNewService(cat.id, 'modele')" class="cat-btn cat-btn-outline btn-sm">
                                <i class="bi-plus me-1"></i>Nouveau modèle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ MODAL CATÉGORIE ══ -->
        <div v-if="showCatModal" class="cat-overlay" @click.self="showCatModal = false">
            <div class="cat-modal">
                <div class="cat-modal-header">
                    <span><i class="bi-folder me-2"></i>{{ editingCat ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</span>
                    <button class="cat-modal-close" @click="showCatModal = false">&times;</button>
                </div>
                <form @submit.prevent="submitCat" class="cat-modal-body">
                    <div class="mb-3">
                        <label class="cat-label">Nom *</label>
                        <input v-model="catForm.nom" type="text" required class="cat-input">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="cat-label">Icône (HTML/emoji)</label>
                            <input v-model="catForm.icone" type="text" class="cat-input">
                        </div>
                        <div class="col-3">
                            <label class="cat-label">Couleur (hex)</label>
                            <input v-model="catForm.couleur" type="color" class="cat-input cat-input--color">
                        </div>
                        <div class="col-3">
                            <label class="cat-label">Ordre</label>
                            <input v-model="catForm.ordre" type="number" class="cat-input">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="cat-label">Description</label>
                        <textarea v-model="catForm.description" rows="2" class="cat-input"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="d-flex align-items-center gap-2" style="font-size:12px; cursor:pointer;">
                            <input v-model="catForm.actif" type="checkbox" class="cat-checkbox">
                            <span style="font-weight:600; color:#163A5E;">Catégorie active (visible publiquement)</span>
                        </label>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" @click="showCatModal = false" class="cat-btn cat-btn-grey">Annuler</button>
                        <button type="submit" :disabled="catProcessing" class="cat-btn cat-btn-primary">
                            <span v-if="catProcessing" class="cat-spinner-sm me-1"></span>
                            {{ editingCat ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ══ MODAL SERVICE/MODÈLE ══ -->
        <div v-if="showServiceModal" class="cat-overlay" @click.self="showServiceModal = false">
            <div class="cat-modal cat-modal-lg">
                <div class="cat-modal-header">
                    <span>
                        <i :class="serviceForm.type === 'modele' ? 'bi-box-seam' : 'bi-gear-wide-connected'" class="me-2"></i>
                        {{ editingService ? 'Modifier' : 'Nouveau' }}
                        {{ serviceForm.type === 'modele' ? 'modèle' : 'service' }}
                    </span>
                    <button class="cat-modal-close" @click="showServiceModal = false">&times;</button>
                </div>
                <form @submit.prevent="submitService" class="cat-modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <label class="cat-label">Nom *</label>
                            <input v-model="serviceForm.nom" type="text" required class="cat-input">
                        </div>
                        <div class="col-md-3">
                            <label class="cat-label">Type</label>
                            <select v-model="serviceForm.type" class="cat-select">
                                <option value="service">Service</option>
                                <option value="modele">Modèle</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="cat-label">Catégorie *</label>
                            <select v-model="serviceForm.category_id" required class="cat-select">
                                <option value="">-- Choisir --</option>
                                <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="cat-label">Description</label>
                        <textarea v-model="serviceForm.description" rows="3" class="cat-input"></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="cat-label">Type de tarif *</label>
                            <select v-model="serviceForm.tarif_type" class="cat-select">
                                <option value="fixe">Tarif fixe</option>
                                <option value="devis">Sur devis</option>
                            </select>
                        </div>
                        <div v-if="serviceForm.tarif_type === 'fixe'" class="col-md-4">
                            <label class="cat-label">Tarif (FCFA)</label>
                            <input v-model="serviceForm.tarif_fcfa" type="number" class="cat-input">
                        </div>
                        <div class="col-md-4">
                            <label class="cat-label">Délai</label>
                            <input v-model="serviceForm.delai_jours" type="text" placeholder="ex: 3 à 5 jours" class="cat-input">
                        </div>
                    </div>

                    <!-- Included items -->
                    <div class="cat-section-box">
                        <label class="cat-label mb-2">Ce qui est inclus</label>
                        <div class="d-flex gap-2 mb-2">
                            <input v-model="newInclusItem" type="text" placeholder="Ajouter un élément..." class="cat-input">
                            <button type="button" @click="addInclusItem" class="cat-btn cat-btn-primary cat-btn-sm flex-shrink-0">Ajouter</button>
                        </div>
                        <ul class="cat-tag-list">
                            <li v-for="(item, idx) in serviceForm.inclus_json" :key="idx" class="cat-tag">
                                <span>{{ item }}</span>
                                <button type="button" @click="removeInclusItem(idx)" class="cat-tag-remove">&times;</button>
                            </li>
                            <li v-if="!serviceForm.inclus_json.length" class="small text-muted" style="list-style:none; padding:4px 0;">Aucun élément</li>
                        </ul>
                    </div>

                    <!-- Required docs -->
                    <div class="cat-section-box">
                        <label class="cat-label mb-2">Documents requis du client</label>
                        <div class="d-flex gap-2 mb-2">
                            <input v-model="newDocItem" type="text" placeholder="ex: Copie CNI..." class="cat-input">
                            <button type="button" @click="addDocItem" class="cat-btn cat-btn-outline cat-btn-sm flex-shrink-0">Ajouter</button>
                        </div>
                        <ul class="cat-tag-list">
                            <li v-for="(doc, idx) in serviceForm.documents_requis_json" :key="idx" class="cat-tag cat-tag-doc">
                                <span>{{ doc }}</span>
                                <button type="button" @click="removeDocItem(idx)" class="cat-tag-remove">&times;</button>
                            </li>
                            <li v-if="!serviceForm.documents_requis_json.length" class="small text-muted" style="list-style:none; padding:4px 0;">Aucun document</li>
                        </ul>
                    </div>

                    <label class="d-flex align-items-center gap-2" style="font-size:12px; cursor:pointer; margin-top:12px;">
                        <input v-model="serviceForm.actif" type="checkbox" class="cat-checkbox">
                        <span style="font-weight:600; color:#163A5E;">Actif (visible publiquement)</span>
                    </label>
                </form>
                <div class="cat-modal-footer">
                    <button type="button" @click="showServiceModal = false" class="cat-btn cat-btn-grey">Annuler</button>
                    <button type="button" @click="submitService" :disabled="serviceProcessing" class="cat-btn cat-btn-primary">
                        <span v-if="serviceProcessing" class="cat-spinner-sm me-1"></span>
                        {{ editingService ? 'Mettre à jour' : 'Créer' }}
                    </button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════
   Catalogue — Organisation par catégorie
   ═══════════════════════════════════════════════════════ */

/* ── Shell ── */
.cat-shell { min-height: 100%; }

/* ── Header ── */
.cat-header {
    background: #163A5E; color: #fff; padding: 16px 20px 10px;
    display: flex; flex-direction: column; gap: 10px;
}
.cat-header-logo {
    width: 44px; height: 44px; background: rgba(255,255,255,0.12);
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.cat-header-title { font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; }
.cat-header-sub { font-size: 12px; color: rgba(255,255,255,.65); }
.cat-header-actions { flex-wrap: wrap; }

.cat-search-wrap { position: relative; }
.cat-search-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: rgba(255,255,255,.5); font-size: 13px;
}
.cat-search {
    background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px; padding: 8px 12px 8px 34px; color: #fff;
    font-size: 13px; min-width: 220px; outline: none;
}
.cat-search::placeholder { color: rgba(255,255,255,.4); }
.cat-search:focus { border-color: rgba(255,255,255,.4); background: rgba(255,255,255,0.18); }

.cat-stats-bar {
    display: flex; gap: 16px; font-size: 12px; color: rgba(255,255,255,.65);
    padding: 6px 0 0; border-top: 1px solid rgba(255,255,255,0.1);
}
.cat-stat { display: flex; align-items: center; gap: 4px; }

/* ── Category section ── */
.cat-category { margin-bottom: 12px; }

.cat-cat-header {
    background: #f8fafc; border: 1px solid #dce3ee;
    border-left: 4px solid #FF7900; border-radius: 6px;
    padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;
    gap: 12px; cursor: pointer; transition: background 0.15s, box-shadow 0.15s;
    flex-wrap: wrap;
}
.cat-cat-header:hover { background: #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
.cat-cat-icon { font-size: 24px; line-height: 1; flex-shrink: 0; }
.cat-cat-name { font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 700; color: #163A5E; }
.cat-cat-desc { font-size: 12px; color: #888; margin: 0; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.cat-count-badge {
    background: #eef3f9; color: #64748b; font-size: 11px; font-weight: 600;
    padding: 2px 10px; border-radius: 12px; white-space: nowrap;
}

.cat-chevron { font-size: 16px; color: #94a3b8; transition: transform 0.2s; margin-left: 4px; }
.cat-chevron--open { transform: rotate(180deg); }

.cat-btn-icon {
    width: 30px; height: 30px; border: none; border-radius: 6px;
    background: transparent; color: #64748b; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 14px; transition: all 0.15s;
}
.cat-btn-icon:hover { background: #eef3f9; color: #163A5E; }
.cat-btn-icon--danger:hover { background: #fef2f2; color: #dc2626; }

/* ── Category body ── */
.cat-body {
    border: 1px solid #dce3ee; border-top: none; border-radius: 0 0 6px 6px;
    padding: 16px; background: #fff;
}

.cat-section { margin-bottom: 20px; }
.cat-section:last-child { margin-bottom: 0; }

.cat-section-title {
    display: flex; align-items: center; gap: 6px;
    font-size: 14px; font-weight: 700; color: #163A5E;
    margin-bottom: 10px; padding-bottom: 6px;
    border-bottom: 2px solid #FF7900;
}
.cat-section-title--modele { border-bottom-color: #8b5cf6; }
.cat-section-count {
    background: #eef3f9; color: #64748b; font-size: 11px; font-weight: 600;
    padding: 0 8px; border-radius: 10px; line-height: 20px;
}

/* ── Cards ── */
.cat-card {
    border: 1px solid #dce3ee; border-radius: 8px; padding: 14px; background: #fff;
    transition: box-shadow 0.15s, border-color 0.15s, transform 0.15s;
    display: flex; flex-direction: column; height: 100%;
}
.cat-card:hover {
    box-shadow: 0 4px 16px rgba(22,58,94,.08);
    border-color: #FF7900; transform: translateY(-1px);
}
.cat-card--modele { border-left: 3px solid #8b5cf6; }
.cat-card--modele:hover { border-color: #8b5cf6; border-left-color: #8b5cf6; }
.cat-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.cat-card-name {
    font-size: 14px; font-weight: 700; color: #163A5E;
    display: flex; align-items: center; gap: 6px;
}
.cat-card-desc { font-size: 12px; color: #666; margin: 6px 0 10px; flex-grow: 1; }
.cat-card-meta {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: 8px; border-top: 1px solid #f0f4f8;
}
.cat-card-price { font-size: 13px; font-weight: 700; color: #FF7900; }
.cat-card-delai { font-size: 11px; color: #888; display: flex; align-items: center; }

.cat-type-icon { font-size: 13px; }
.cat-type-icon--service { color: #FF7900; }
.cat-type-icon--modele { color: #8b5cf6; }

/* ── Empty ── */
.cat-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 60px 20px; gap: 8px;
}
.cat-empty-icon { font-size: 48px; color: #dce3ee; }
.cat-empty-title { font-size: 16px; font-weight: 700; color: #888; }
.cat-empty-desc { font-size: 13px; color: #aaa; }
.cat-empty-small {
    display: flex; flex-direction: column; align-items: center;
    padding: 30px; gap: 6px; color: #94a3b8;
}
.cat-empty-small i { font-size: 28px; }

/* ── Badges ── */
.cat-badge {
    display: inline-block; padding: 2px 10px; font-size: 11px; font-weight: 600;
    border-radius: 50px; white-space: nowrap;
}
.cat-badge--active { background: #d1fae5; color: #065f46; }
.cat-badge--inactive,
.cat-badge--grey { background: #f3f4f6; color: #6b7280; }
.cat-badge--service { background: #fff7ed; color: #c2410c; }
.cat-badge--modele { background: #f3e8ff; color: #6b21a8; }

/* ── Tag list ── */
.cat-tag-list { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 6px; }
.cat-tag {
    display: inline-flex; align-items: center; gap: 4px;
    background: #eef3f9; color: #163A5E; font-size: 12px; font-weight: 500;
    padding: 4px 10px; border-radius: 3px;
}
.cat-tag-doc { background: #fff3e0; color: #e65100; }
.cat-tag-remove { background: none; border: none; color: #888; cursor: pointer; font-size: 16px; padding: 0; line-height: 1; }
.cat-tag-remove:hover { color: #c62828; }

/* ── Buttons ── */
.cat-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 8px 16px; font-size: 12.5px; font-weight: 700; border-radius: 6px;
    border: none; cursor: pointer; text-decoration: none;
    transition: all 0.15s; white-space: nowrap;
}
.cat-btn-primary { background: #FF7900; color: #fff; }
.cat-btn-primary:hover { background: #e06700; color: #fff; }
.cat-btn-outline { background: transparent; color: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.25); }
.cat-btn-outline:hover { background: rgba(255,255,255,.1); color: #fff; border-color: rgba(255,255,255,.4); }
.cat-btn-grey { background: #f3f4f6; color: #374151; }
.cat-btn-grey:hover { background: #e5e7eb; }
.cat-btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.cat-btn-danger:hover { background: #fee2e2; }
.cat-btn-sm { padding: 4px 10px; font-size: 11px; font-weight: 600; border-radius: 4px; }

.cat-btn-icon {
    background: transparent; border: 1px solid transparent; border-radius: 6px;
    padding: 6px; cursor: pointer; color: #64748b; transition: all 0.15s;
    display: inline-flex; align-items: center;
}
.cat-btn-icon:hover { background: #eef3f9; color: #163A5E; }

.cat-spinner-sm {
    width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff; border-radius: 50%; animation: catSpin 0.6s linear infinite;
    display: inline-block;
}
@keyframes catSpin { to { transform: rotate(360deg); } }

/* ── Section box (in modal) ── */
.cat-section-box {
    background: #f8fafc; border: 1px solid #dce3ee; border-radius: 6px;
    padding: 12px; margin-bottom: 12px;
}

/* ── Modal ── */
.cat-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1060;
    display: flex; align-items: center; justify-content: center; padding: 20px;
}
.cat-modal {
    background: #fff; border-radius: 12px; width: 100%; max-width: 500px;
    max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.15);
}
.cat-modal-lg { max-width: 720px; }
.cat-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid #dce3ee;
    font-size: 15px; font-weight: 700; color: #163A5E;
}
.cat-modal-close { background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; }
.cat-modal-close:hover { color: #163A5E; }
.cat-modal-body { padding: 20px; }
.cat-modal-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding: 12px 20px; border-top: 1px solid #dce3ee;
}

/* ── Form ── */
.cat-label { display: block; font-size: 11.5px; font-weight: 600; color: #163A5E; margin-bottom: 4px; }
.cat-input {
    width: 100%; padding: 8px 12px; font-size: 13px;
    border: 1.5px solid #dce3ee; border-radius: 6px; outline: none;
    background: #fff; transition: border-color 0.15s; color: #1e293b;
}
.cat-input:focus { border-color: #FF7900; box-shadow: 0 0 0 3px rgba(255,121,0,.1); }
.cat-input--color { min-height: 38px; padding: 2px 4px; cursor: pointer; }
.cat-select {
    width: 100%; padding: 8px 12px; font-size: 13px;
    border: 1.5px solid #dce3ee; border-radius: 6px; outline: none;
    background: #fff; color: #1e293b; cursor: pointer;
}
.cat-select:focus { border-color: #FF7900; box-shadow: 0 0 0 3px rgba(255,121,0,.1); }
.cat-checkbox { width: 16px; height: 16px; accent-color: #FF7900; }

/* ── Responsive ── */
@media (max-width: 767.98px) {
    .cat-cat-header { flex-direction: column; align-items: stretch; }
    .cat-search { min-width: 140px; }
    .cat-header-actions { width: 100%; }
    .cat-stats-bar { flex-wrap: wrap; gap: 8px; }
}
</style>
