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
    <GelLayout page-title="Gestion du Catalogue">
        <div class="isup-shell">

            <!-- ══ HEADER ══ -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-grid-3x3-gap" style="font-size:20px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="isup-portal-company">Gestion du Catalogue de Services</div>
                        <div class="isup-portal-sub">Catégories et services proposés aux clients</div>
                    </div>
                    <a href="/admin/catalogue/orders" class="isup-btn-link flex-shrink-0">
                        <i class="bi-kanban me-1"></i>Kanban Commandes
                    </a>
                    <button @click="openNewCat" class="isup-btn-primary flex-shrink-0">
                        <i class="bi-plus-lg me-1"></i>Nouvelle catégorie
                    </button>
                </div>
            </div>

            <!-- ══ CONTENU ══ -->
            <div class="p-3">
                <div v-if="!categories.length" class="text-center py-5">
                    <i class="bi-folder-x" style="font-size:40px; color:#dce3ee; display:block; margin-bottom:12px;"></i>
                    <div style="font-size:15px; font-weight:600; color:#888; margin-bottom:6px;">Catalogue vide</div>
                    <p class="text-muted" style="font-size:13px;">Créez votre première catégorie de services.</p>
                    <button @click="openNewCat" class="isup-btn-primary" style="padding:8px 20px;">
                        <i class="bi-plus-lg me-1"></i>Nouvelle catégorie
                    </button>
                </div>

                <div v-for="cat in categories" :key="cat.id" class="mb-4">
                    <!-- Category header -->
                    <div class="isup-cat-header">
                        <div class="d-flex align-items-center gap-3">
                            <span v-if="cat.icone" v-html="cat.icone" class="isup-cat-icon"></span>
                            <div>
                                <div class="isup-cat-title">{{ cat.nom }}</div>
                                <p v-if="cat.description" class="isup-cat-desc">{{ cat.description }}</p>
                            </div>
                            <span class="isup-status" :class="cat.actif ? 'isup-status-green' : 'isup-status-red'">
                                {{ cat.actif ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button @click="openEditCat(cat)" class="isup-btn-outline btn-sm">
                                <i class="bi-pencil me-1"></i>Modifier
                            </button>
                            <button @click="deleteCat(cat.id)" class="isup-btn-outline-red btn-sm">
                                <i class="bi-trash me-1"></i>Supprimer
                            </button>
                            <button @click="openNewService(cat.id)" class="isup-btn-orange btn-sm">
                                <i class="bi-plus-lg me-1"></i>Service
                            </button>
                        </div>
                    </div>

                    <!-- Services grid -->
                    <div v-if="cat.services && cat.services.length" class="row g-3 mt-1">
                        <div v-for="service in cat.services" :key="service.id" class="col-md-6 col-lg-4">
                            <div class="isup-svc-card">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div class="isup-svc-name">{{ service.nom }}</div>
                                    <span class="isup-status" :class="service.actif ? 'isup-status-green' : 'isup-status-grey'">
                                        {{ service.actif ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <p class="isup-svc-desc">{{ service.description || 'Aucune description' }}</p>
                                <div class="isup-svc-meta">
                                    <span class="isup-svc-price">
                                        {{ service.tarif_type === 'fixe' ? `${Number(service.tarif_fcfa).toLocaleString('fr-FR')} FCFA` : 'Sur devis' }}
                                    </span>
                                    <span class="isup-svc-delai">
                                        <i class="bi-clock me-1"></i>{{ service.delai_jours || 'Délai N/A' }}
                                    </span>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button @click="openEditService(service)" class="isup-btn-primary btn-sm flex-grow-1">
                                        <i class="bi-pencil me-1"></i>Modifier
                                    </button>
                                    <button @click="deleteService(service.id)" class="isup-btn-outline-red btn-sm flex-grow-1">
                                        <i class="bi-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-muted" style="font-size:13px; border:1px dashed #dce3ee; border-radius:4px; margin-top:8px;">
                        <i class="bi-inbox" style="font-size:20px; display:block; margin-bottom:4px;"></i>
                        Aucun service. Cliquez sur "Ajouter un service" pour commencer.
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ MODAL CATÉGORIE ══ -->
        <div v-if="showCatModal" class="isup-modal-overlay" @click.self="showCatModal = false">
            <div class="isup-modal">
                <div class="isup-modal-header">
                    <span>{{ editingCat ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</span>
                    <button class="isup-modal-close" @click="showCatModal = false">&times;</button>
                </div>
                <form @submit.prevent="submitCat" class="isup-modal-body">
                    <div class="mb-3">
                        <label class="isup-label">Nom *</label>
                        <input v-model="catForm.nom" type="text" required class="isup-input">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="isup-label">Icône (emoji ou HTML)</label>
                            <input v-model="catForm.icone" type="text" class="isup-input">
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Ordre d'affichage</label>
                            <input v-model="catForm.ordre" type="number" class="isup-input">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="isup-label">Description</label>
                        <textarea v-model="catForm.description" rows="2" class="isup-input"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="d-flex align-items-center gap-2" style="font-size:12px; cursor:pointer;">
                            <input v-model="catForm.actif" type="checkbox" class="isup-checkbox">
                            <span style="font-weight:600; color:#163A5E;">Catégorie active (visible publiquement)</span>
                        </label>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" @click="showCatModal = false" class="isup-btn-grey">Annuler</button>
                        <button type="submit" :disabled="catProcessing" class="isup-btn-primary">
                            <span v-if="catProcessing" class="isup-spinner-sm me-1"></span>
                            {{ editingCat ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ══ MODAL SERVICE ══ -->
        <div v-if="showServiceModal" class="isup-modal-overlay" @click.self="showServiceModal = false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header">
                    <span>{{ editingService ? 'Modifier le service' : 'Nouveau service' }}</span>
                    <button class="isup-modal-close" @click="showServiceModal = false">&times;</button>
                </div>
                <form @submit.prevent="submitService" class="isup-modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="isup-label">Nom du service *</label>
                            <input v-model="serviceForm.nom" type="text" required class="isup-input">
                        </div>
                        <div class="col-md-6">
                            <label class="isup-label">Catégorie *</label>
                            <select v-model="serviceForm.category_id" required class="isup-select">
                                <option value="">-- Choisir --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="isup-label">Description</label>
                        <textarea v-model="serviceForm.description" rows="3" class="isup-input"></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="isup-label">Type de tarif *</label>
                            <select v-model="serviceForm.tarif_type" class="isup-select">
                                <option value="fixe">Tarif fixe</option>
                                <option value="devis">Sur devis</option>
                            </select>
                        </div>
                        <div v-if="serviceForm.tarif_type === 'fixe'" class="col-md-4">
                            <label class="isup-label">Tarif (FCFA)</label>
                            <input v-model="serviceForm.tarif_fcfa" type="number" class="isup-input">
                        </div>
                        <div class="col-md-4">
                            <label class="isup-label">Délai</label>
                            <input v-model="serviceForm.delai_jours" type="text" placeholder="ex: 3 à 5 jours" class="isup-input">
                        </div>
                    </div>

                    <!-- Ce qui est inclus -->
                    <div class="mb-3 p-3" style="background:#f8fafc; border:1px solid #dce3ee; border-radius:4px;">
                        <label class="isup-label mb-2">Ce qui est inclus</label>
                        <div class="d-flex gap-2 mb-2">
                            <input v-model="newInclusItem" type="text" placeholder="Ajouter un élément..." class="isup-input">
                            <button type="button" @click="addInclusItem" class="isup-btn-primary flex-shrink-0">Ajouter</button>
                        </div>
                        <ul class="isup-tag-list">
                            <li v-for="(item, idx) in serviceForm.inclus_json" :key="idx" class="isup-tag">
                                <span>{{ item }}</span>
                                <button type="button" @click="removeInclusItem(idx)" class="isup-tag-remove">&times;</button>
                            </li>
                            <li v-if="!serviceForm.inclus_json.length" class="small text-muted" style="list-style:none; padding:4px 0;">Aucun élément</li>
                        </ul>
                    </div>

                    <!-- Documents requis -->
                    <div class="mb-3 p-3" style="background:#f8fafc; border:1px solid #dce3ee; border-radius:4px;">
                        <label class="isup-label mb-2">Documents requis du client</label>
                        <div class="d-flex gap-2 mb-2">
                            <input v-model="newDocItem" type="text" placeholder="ex: Copie CNI..." class="isup-input">
                            <button type="button" @click="addDocItem" class="isup-btn-orange flex-shrink-0">Ajouter</button>
                        </div>
                        <ul class="isup-tag-list">
                            <li v-for="(doc, idx) in serviceForm.documents_requis_json" :key="idx" class="isup-tag isup-tag-doc">
                                <span>{{ doc }}</span>
                                <button type="button" @click="removeDocItem(idx)" class="isup-tag-remove">&times;</button>
                            </li>
                            <li v-if="!serviceForm.documents_requis_json.length" class="small text-muted" style="list-style:none; padding:4px 0;">Aucun document requis</li>
                        </ul>
                    </div>

                    <label class="d-flex align-items-center gap-2" style="font-size:12px; cursor:pointer;">
                        <input v-model="serviceForm.actif" type="checkbox" class="isup-checkbox">
                        <span style="font-weight:600; color:#163A5E;">Service actif (visible publiquement)</span>
                    </label>
                </form>
                <div class="isup-modal-footer">
                    <button type="button" @click="showServiceModal = false" class="isup-btn-grey">Annuler</button>
                    <button type="button" @click="submitService" :disabled="serviceProcessing" class="isup-btn-primary">
                        <span v-if="serviceProcessing" class="isup-spinner-sm me-1"></span>
                        {{ editingService ? 'Mettre à jour' : 'Créer le service' }}
                    </button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ══ Catalogue Services — unique styles ══ */

/* ── Category ─── */
.isup-cat-header {
    background:#f8fafc; border:1px solid #dce3ee; border-radius:4px;
    padding:12px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px;
    flex-wrap:wrap;
}
.isup-cat-icon { font-size:22px; line-height:1; flex-shrink:0; }
.isup-cat-title { font-family:'Outfit',sans-serif; font-size:16px; font-weight:700; color:#163A5E; }
.isup-cat-desc { font-size:12px; color:#888; margin:0; }

/* ── Service card ─── */
.isup-svc-card {
    border:1px solid #dce3ee; border-radius:4px; padding:14px; background:#fff;
    transition:box-shadow 0.15s, border-color 0.15s; height:100%;
    display:flex; flex-direction:column;
}
.isup-svc-card:hover { box-shadow:0 4px 14px rgba(22,58,94,.08); border-color:#FF7900; }
.isup-svc-name { font-size:14px; font-weight:700; color:#163A5E; }
.isup-svc-desc { font-size:12px; color:#666; margin:6px 0 10px; flex-grow:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.isup-svc-meta {
    display:flex; align-items:center; justify-content:space-between;
    padding-top:8px; border-top:1px solid #f0f4f8;
}
.isup-svc-price { font-size:13px; font-weight:700; color:#FF7900; }
.isup-svc-delai { font-size:11px; color:#888; display:flex; align-items:center; }

/* ── Tag list ─── */
.isup-tag-list { list-style:none; padding:0; margin:0; display:flex; flex-wrap:wrap; gap:6px; }
.isup-tag {
    display:inline-flex; align-items:center; gap:4px;
    background:#eef3f9; color:#163A5E; font-size:12px; font-weight:500;
    padding:4px 10px; border-radius:3px;
}
.isup-tag-doc { background:#fff3e0; color:#e65100; }
.isup-tag-remove { background:none; border:none; color:#888; cursor:pointer; font-size:16px; padding:0; line-height:1; }
.isup-tag-remove:hover { color:#c62828; }

/* ── Unique buttons ─── */
.isup-btn-orange {
    background:rgba(255,121,0,0.1); color:#FF7900; border:1px solid rgba(255,121,0,0.3);
    border-radius:4px; padding:7px 14px; font-size:12px; font-weight:700; cursor:pointer;
    display:inline-flex; align-items:center; transition:all 0.15s; white-space:nowrap;
}
.isup-btn-orange:hover { background:#FF7900; color:#fff; }
.isup-btn-outline {
    background:#fff; color:#163A5E; border:1px solid #dce3ee; border-radius:4px;
    padding:6px 12px; font-size:11px; font-weight:600; cursor:pointer;
    display:inline-flex; align-items:center; transition:all 0.15s; white-space:nowrap;
}
.isup-btn-outline:hover { background:#f5f7fb; border-color:#bbb; }
.isup-btn-outline-red {
    background:#fff; color:#c62828; border:1px solid #f5c6c0; border-radius:4px;
    padding:6px 12px; font-size:11px; font-weight:600; cursor:pointer;
    display:inline-flex; align-items:center; transition:all 0.15s; white-space:nowrap;
}
.isup-btn-outline-red:hover { background:#fdecea; border-color:#e57373; }
.isup-btn-link {
    background:transparent; color:rgba(255,255,255,.8); border:1px solid rgba(255,255,255,.2);
    border-radius:4px; padding:6px 12px; font-size:11px; font-weight:600; cursor:pointer;
    display:inline-flex; align-items:center; transition:all 0.15s; text-decoration:none; white-space:nowrap;
}
.isup-btn-link:hover { background:rgba(255,255,255,.1); color:#fff; }

/* ── Checkbox ─── */
.isup-checkbox { width:16px; height:16px; accent-color:#FF7900; }

/* ── Responsive ─── */
@media (max-width: 767.98px) {
    .isup-cat-header { flex-direction:column; align-items:stretch; }
}
</style>
