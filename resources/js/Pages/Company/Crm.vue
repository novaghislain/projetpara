<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

// --- Etat actif des onglets -------------------------------------------------------
const activeTab = ref('dashboard');
const setTab = (tab) => { activeTab.value = tab; };

// --- Donnees ----------------------------------------------------------------------
const stats = ref(null);
const contacts = ref([]);
const deals = ref([]);
const interactions = ref([]);
const loading = ref(false);
const error = ref(null);

// --- Modales ----------------------------------------------------------------------
const showContactModal = ref(false);
const showDealModal = ref(false);
const showInteractionModal = ref(false);
const editingContact = ref(null);
const editingDeal = ref(null);
const isSubmitting = ref(false);

// --- Formulaires ------------------------------------------------------------------
const contactForm = ref({
    first_name: '', last_name: '', email: '', phone: '',
    company: '', position: '', category: 'prospect', notes: '', tags: [],
});

const dealForm = ref({
    contact_id: '', title: '', description: '', amount: null,
    stage: 'prospection', status: 'open', probability: 50,
    expected_close_date: '', notes: '',
});

const interactionForm = ref({
    contact_id: '', deal_id: '', type: 'call', subject: '',
    description: '', scheduled_at: '', outcome: '',
});

const tagInput = ref('');

// --- Recherche --------------------------------------------------------------------
const searchQuery = ref('');

const filteredContacts = computed(() => {
    if (!searchQuery.value) return contacts.value;
    const q = searchQuery.value.toLowerCase();
    return contacts.value.filter(c =>
        c.first_name.toLowerCase().includes(q) ||
        c.last_name.toLowerCase().includes(q) ||
        c.email.toLowerCase().includes(q) ||
        (c.company || '').toLowerCase().includes(q) ||
        c.category.toLowerCase().includes(q)
    );
});

const filteredDeals = computed(() => {
    if (!searchQuery.value) return deals.value;
    const q = searchQuery.value.toLowerCase();
    return deals.value.filter(d =>
        d.title.toLowerCase().includes(q) ||
        (d.contact_name || '').toLowerCase().includes(q) ||
        d.stage.toLowerCase().includes(q) ||
        d.status.toLowerCase().includes(q)
    );
});

// --- Helpers ----------------------------------------------------------------------
const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR');
};

const categoryBadge = (cat) => {
    const map = {
        client: 'bg-success', partner: 'bg-info text-dark',
        prospect: 'bg-warning text-dark', lead: 'bg-primary',
        supplier: 'bg-secondary',
    };
    return map[cat] || 'bg-secondary';
};

const categoryLabel = (cat) => {
    const map = {
        client: 'Client', partner: 'Partenaire',
        prospect: 'Prospect', lead: 'Lead',
        supplier: 'Fournisseur',
    };
    return map[cat] || cat;
};

const stageBadge = (stage) => {
    const map = {
        prospection: 'bg-secondary', qualification: 'bg-info text-dark',
        proposition: 'bg-primary', negociation: 'bg-warning text-dark',
        finalise: 'bg-success',
    };
    return map[stage] || 'bg-secondary';
};

const stageLabel = (stage) => {
    const map = {
        prospection: 'Prospection', qualification: 'Qualification',
        proposition: 'Proposition', negociation: 'Negociation',
        finalise: 'Finalise',
    };
    return map[stage] || stage;
};

const statusBadge = (status) => {
    const map = {
        open: 'bg-primary', won: 'bg-success',
        lost: 'bg-danger', abandoned: 'bg-secondary',
    };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = {
        open: 'Ouverte', won: 'Gagnee',
        lost: 'Perdue', abandoned: 'Abandonnee',
    };
    return map[status] || status;
};

const typeBadge = (type) => {
    const map = {
        call: 'bg-success', email: 'bg-primary',
        meeting: 'bg-warning text-dark', note: 'bg-info text-dark',
        other: 'bg-secondary',
    };
    return map[type] || 'bg-secondary';
};

const typeLabel = (type) => {
    const map = {
        call: 'Appel', email: 'Email',
        meeting: 'Reunion', note: 'Note',
        other: 'Autre',
    };
    return map[type] || type;
};

const typeIcon = (type) => {
    const map = {
        call: 'bi-telephone', email: 'bi-envelope',
        meeting: 'bi-people', note: 'bi-sticky',
        other: 'bi-three-dots',
    };
    return map[type] || 'bi-three-dots';
};

// --- Tags helpers -----------------------------------------------------------------
function addTag() {
    const tag = tagInput.value.trim();
    if (tag && !contactForm.value.tags.includes(tag)) {
        contactForm.value.tags.push(tag);
    }
    tagInput.value = '';
}

function removeTag(index) {
    contactForm.value.tags.splice(index, 1);
}

// --- API Calls --------------------------------------------------------------------
async function fetchStats() {
    try {
        const res = await fetch('/api/company/crm/stats');
        if (!res.ok) throw new Error('Erreur chargement statistiques');
        stats.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchContacts() {
    try {
        const res = await fetch('/api/company/crm/contacts');
        if (!res.ok) throw new Error('Erreur chargement contacts');
        contacts.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchDeals() {
    try {
        const res = await fetch('/api/company/crm/deals');
        if (!res.ok) throw new Error('Erreur chargement affaires');
        deals.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchInteractions() {
    try {
        const res = await fetch('/api/company/crm/interactions');
        if (!res.ok) throw new Error('Erreur chargement interactions');
        interactions.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

// --- Actions Contacts ------------------------------------------------------------
function openCreateContact() {
    editingContact.value = null;
    contactForm.value = {
        first_name: '', last_name: '', email: '', phone: '',
        company: '', position: '', category: 'prospect', notes: '', tags: [],
    };
    tagInput.value = '';
    showContactModal.value = true;
}

function openEditContact(c) {
    editingContact.value = c.id;
    contactForm.value = {
        first_name: c.first_name, last_name: c.last_name, email: c.email,
        phone: c.phone || '', company: c.company || '', position: c.position || '',
        category: c.category, notes: c.notes || '', tags: c.tags || [],
    };
    showContactModal.value = true;
}

async function saveContact() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const url = editingContact.value
            ? `/api/company/crm/contacts/${editingContact.value}`
            : '/api/company/crm/contacts';
        const method = editingContact.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(contactForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showContactModal.value = false;
        await fetchContacts();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function deleteContact(id) {
    if (!confirm('Supprimer ce contact ?')) return;
    try {
        const res = await fetch(`/api/company/crm/contacts/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchContacts();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// --- Actions Affaires -----------------------------------------------------------
function openCreateDeal() {
    editingDeal.value = null;
    dealForm.value = {
        contact_id: '', title: '', description: '', amount: null,
        stage: 'prospection', status: 'open', probability: 50,
        expected_close_date: '', notes: '',
    };
    showDealModal.value = true;
}

function openEditDeal(d) {
    editingDeal.value = d.id;
    dealForm.value = {
        contact_id: d.contact_id || '', title: d.title, description: d.description || '',
        amount: d.amount, stage: d.stage, status: d.status,
        probability: d.probability, expected_close_date: d.expected_close_date || '',
        notes: d.notes || '',
    };
    showDealModal.value = true;
}

async function saveDeal() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const url = editingDeal.value
            ? `/api/company/crm/deals/${editingDeal.value}`
            : '/api/company/crm/deals';
        const method = editingDeal.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(dealForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showDealModal.value = false;
        await fetchDeals();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function deleteDeal(id) {
    if (!confirm('Supprimer cette affaire ?')) return;
    try {
        const res = await fetch(`/api/company/crm/deals/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchDeals();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// --- Actions Interactions -------------------------------------------------------
function openCreateInteraction() {
    interactionForm.value = {
        contact_id: '', deal_id: '', type: 'call', subject: '',
        description: '', scheduled_at: '', outcome: '',
    };
    showInteractionModal.value = true;
}

async function saveInteraction() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/company/crm/interactions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(interactionForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showInteractionModal.value = false;
        await fetchInteractions();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

// --- Pipeline helpers -----------------------------------------------------------
const pipelineStages = ['prospection', 'qualification', 'proposition', 'negociation', 'finalise'];

const dealsByStage = computed(() => {
    const map = {};
    pipelineStages.forEach(s => { map[s] = []; });
    filteredDeals.value.forEach(d => {
        if (map[d.stage]) map[d.stage].push(d);
    });
    return map;
});

const stageTotal = (stage) => {
    const items = dealsByStage.value[stage] || [];
    return items.reduce((sum, d) => sum + d.amount, 0);
};

// --- Initialisation ------------------------------------------------------------
onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchContacts(), fetchDeals(), fetchInteractions()]);
    loading.value = false;
});
</script>

<template>
    <CompanyLayout page-title="CRM">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <template v-else>
            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'dashboard' }" @click="setTab('dashboard')">
                        <i class="bi-speedometer2 me-1"></i> Tableau de bord
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'contacts' }" @click="setTab('contacts')">
                        <i class="bi-people me-1"></i> Contacts
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'deals' }" @click="setTab('deals')">
                        <i class="bi-graph-up-arrow me-1"></i> Affaires
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'interactions' }" @click="setTab('interactions')">
                        <i class="bi-chat-dots me-1"></i> Interactions
                    </button>
                </li>
            </ul>

            <!-- ============= DASHBOARD ============= -->
            <div v-if="activeTab === 'dashboard' && stats">
                <!-- Stats cles -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi-people"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Contacts</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.contacts.total }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi-graph-up-arrow"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Affaires ouvertes</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.deals.open }}</div>
                                    <small class="text-success">{{ stats.deals.total }} total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi-cash-stack"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Pipeline</div>
                                    <div class="h3 mb-0 fw-bold">{{ formatCurrency(stats.deals.pipeline) }}</div>
                                    <small class="text-warning">en cours</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi-trophy"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Conversion</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.deals.conversion }}%</div>
                                    <small class="text-info">{{ stats.deals.won }} gagnees</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Repartition par categorie -->
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-tags me-2"></i>Contacts par categorie</h6>
                            <div v-if="Object.keys(stats.contacts.by_category || {}).length">
                                <div v-for="(count, cat) in stats.contacts.by_category" :key="cat"
                                     class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <span class="badge" :class="categoryBadge(cat)">{{ categoryLabel(cat) }}</span>
                                    </span>
                                    <span class="badge bg-primary rounded-pill">{{ count }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucun contact</p>
                        </div>
                    </div>

                    <!-- Pipeline par etape -->
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-diagram-3 me-2"></i>Pipeline par etape</h6>
                            <div v-if="Object.keys(stats.deals.by_stage || {}).length">
                                <div v-for="(data, stage) in stats.deals.by_stage" :key="stage"
                                     class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <span class="badge" :class="stageBadge(stage)">{{ stageLabel(stage) }}</span>
                                        <small class="text-muted ms-1">({{ data.count }})</small>
                                    </span>
                                    <span class="fw-semibold">{{ formatCurrency(data.amount) }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucune affaire</p>
                        </div>
                    </div>
                </div>

                <!-- Interactions recentes -->
                <div class="card card-dashboard p-3 mt-3">
                    <h6 class="fw-bold mb-3"><i class="bi-clock-history me-2"></i>Interactions recentes</h6>
                    <div v-if="stats.recent_interactions && stats.recent_interactions.length">
                        <div v-for="ri in stats.recent_interactions" :key="ri.id"
                             class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom border-light">
                            <span class="badge" :class="typeBadge(ri.type)">
                                <i :class="typeIcon(ri.type)"></i>
                            </span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ ri.subject }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    {{ ri.contact_name || 'Sans contact' }}
                                </div>
                            </div>
                            <small class="text-muted">{{ ri.created_at }}</small>
                        </div>
                    </div>
                    <p v-else class="text-muted small mb-0">Aucune interaction recente</p>
                </div>
            </div>

            <!-- ============= CONTACTS ============= -->
            <div v-if="activeTab === 'contacts'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="max-width: 350px;">
                        <input type="text" class="form-control form-control-sm" placeholder="Rechercher un contact..."
                               v-model="searchQuery">
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateContact">
                        <i class="bi-plus-lg me-1"></i> Nouveau contact
                    </button>
                </div>

                <div class="card card-dashboard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Email / Telephone</th>
                                    <th>Societe</th>
                                    <th>Categorie</th>
                                    <th>Tags</th>
                                    <th class="text-center">Affaires</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in filteredContacts" :key="c.id">
                                    <td class="fw-semibold">
                                        {{ c.first_name }} {{ c.last_name }}
                                        <div class="text-muted small">{{ c.position || '-' }}</div>
                                    </td>
                                    <td>
                                        {{ c.email }}<br>
                                        <small class="text-muted">{{ c.phone || '-' }}</small>
                                    </td>
                                    <td>{{ c.company || '-' }}</td>
                                    <td>
                                        <span class="badge" :class="categoryBadge(c.category)">
                                            {{ categoryLabel(c.category) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="c.tags && c.tags.length">
                                            <span v-for="(tag, ti) in c.tags.slice(0, 3)" :key="ti"
                                                  class="badge bg-light text-dark me-1">{{ tag }}</span>
                                            <span v-if="c.tags.length > 3" class="badge bg-secondary">+{{ c.tags.length - 3 }}</span>
                                        </span>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill">{{ c.deals_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Modifier" @click="openEditContact(c)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteContact(c.id)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!filteredContacts.length">
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi-people fs-4 d-block mb-1"></i>
                                        Aucun contact trouve
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============= AFFAIRES ============= -->
            <div v-if="activeTab === 'deals'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="max-width: 350px;">
                        <input type="text" class="form-control form-control-sm" placeholder="Rechercher une affaire..."
                               v-model="searchQuery">
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateDeal">
                        <i class="bi-plus-lg me-1"></i> Nouvelle affaire
                    </button>
                </div>

                <!-- Pipeline visuel (Kanban-like) -->
                <div class="row g-2 mb-4 flex-nowrap overflow-auto" style="min-height: 200px;">
                    <div v-for="stage in pipelineStages" :key="stage" class="col" style="min-width: 220px;">
                        <div class="card card-dashboard h-100">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center"
                                 :class="'bg-' + stageBadge(stage).replace('bg-', '') + ' bg-opacity-10 border-0'">
                                <span class="fw-semibold small">
                                    <span class="badge me-1" :class="stageBadge(stage)">{{ stageLabel(stage) }}</span>
                                </span>
                                <small class="text-muted">{{ formatCurrency(stageTotal(stage)) }}</small>
                            </div>
                            <div class="card-body p-2">
                                <div v-for="deal in (dealsByStage[stage] || [])" :key="deal.id"
                                     class="border rounded p-2 mb-2 bg-white" style="cursor: pointer;"
                                     @click="openEditDeal(deal)">
                                    <div class="fw-semibold small">{{ deal.title }}</div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">{{ formatCurrency(deal.amount) }}</small>
                                        <span class="badge" :class="statusBadge(deal.status)" style="font-size: 0.65rem;">
                                            {{ statusLabel(deal.status) }}
                                        </span>
                                    </div>
                                    <div v-if="deal.contact_name" class="text-muted mt-1" style="font-size: 0.7rem;">
                                        <i class="bi-person me-1"></i>{{ deal.contact_name }}
                                    </div>
                                    <div class="mt-1">
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-success" :style="{ width: deal.probability + '%' }"></div>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ deal.probability }}%</small>
                                    </div>
                                </div>
                                <div v-if="!(dealsByStage[stage] || []).length" class="text-center text-muted py-3" style="font-size: 0.8rem;">
                                    <i class="bi-inbox fs-5 d-block mb-1"></i>
                                    Aucune affaire
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des affaires -->
                <div class="card card-dashboard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Titre</th>
                                    <th>Contact</th>
                                    <th>Montant</th>
                                    <th>Etape</th>
                                    <th>Statut</th>
                                    <th>Probabilite</th>
                                    <th>Cloture prevue</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in filteredDeals" :key="d.id">
                                    <td class="fw-semibold">{{ d.title }}</td>
                                    <td>
                                        <template v-if="d.contact_name">
                                            {{ d.contact_name }}
                                            <div class="text-muted small">{{ d.contact_company || '' }}</div>
                                        </template>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                    <td class="fw-bold">{{ formatCurrency(d.amount) }}</td>
                                    <td>
                                        <span class="badge" :class="stageBadge(d.stage)">{{ stageLabel(d.stage) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="statusBadge(d.status)">{{ statusLabel(d.status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 80px;">
                                                <div class="progress-bar bg-success" :style="{ width: d.probability + '%' }"></div>
                                            </div>
                                            <small>{{ d.probability }}%</small>
                                        </div>
                                    </td>
                                    <td>{{ formatDate(d.expected_close_date) || '-' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Modifier" @click="openEditDeal(d)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteDeal(d.id)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!filteredDeals.length">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi-graph-up-arrow fs-4 d-block mb-1"></i>
                                        Aucune affaire trouvee
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============= INTERACTIONS ============= -->
            <div v-if="activeTab === 'interactions'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="text-muted small">{{ interactions.length }} interaction(s)</span>
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateInteraction">
                        <i class="bi-plus-lg me-1"></i> Nouvelle interaction
                    </button>
                </div>

                <!-- Timeline -->
                <div class="card card-dashboard p-3">
                    <div v-if="interactions.length" class="position-relative">
                        <!-- Ligne verticale -->
                        <div class="position-absolute start-3 top-0 bottom-0 border-start border-2 border-light"></div>

                        <div v-for="(inter, idx) in interactions" :key="inter.id"
                             class="d-flex gap-3 mb-4 position-relative">
                            <!-- Icone ronde -->
                            <div class="position-relative z-1 mt-1">
                                <span class="badge rounded-circle p-2 d-flex align-items-center justify-content-center"
                                      :class="typeBadge(inter.type)"
                                      style="width: 32px; height: 32px;">
                                    <i :class="typeIcon(inter.type)" class="small"></i>
                                </span>
                            </div>
                            <!-- Contenu -->
                            <div class="flex-grow-1 bg-light rounded p-3" style="margin-left: 0.5rem;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge me-1" :class="typeBadge(inter.type)">{{ typeLabel(inter.type) }}</span>
                                        <span class="fw-semibold">{{ inter.subject }}</span>
                                    </div>
                                    <small class="text-muted ms-2 text-nowrap">{{ inter.created_at }}</small>
                                </div>
                                <p v-if="inter.description" class="mb-1 mt-1 small">{{ inter.description }}</p>
                                <div class="d-flex gap-3 mt-1 small text-muted">
                                    <span v-if="inter.contact_name">
                                        <i class="bi-person me-1"></i>{{ inter.contact_name }}
                                    </span>
                                    <span v-if="inter.deal_title">
                                        <i class="bi-graph-up-arrow me-1"></i>{{ inter.deal_title }}
                                        <span class="badge ms-1" :class="stageBadge(inter.deal_stage)" style="font-size: 0.6rem;">
                                            {{ stageLabel(inter.deal_stage) }}
                                        </span>
                                    </span>
                                    <span v-if="inter.scheduled_at">
                                        <i class="bi-calendar me-1"></i>{{ inter.scheduled_at }}
                                    </span>
                                    <span v-if="inter.outcome">
                                        <i class="bi-check-circle me-1"></i>{{ inter.outcome }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-center text-muted py-4 mb-0">
                        <i class="bi-chat-dots fs-4 d-block mb-1"></i>
                        Aucune interaction enregistree
                    </p>
                </div>
            </div>
        </template>

        <!-- ============= MODAL CONTACT ============= -->
        <div class="modal fade" :class="{ show: showContactModal }"
             :style="{ display: showContactModal ? 'block' : 'none' }"
             tabindex="-1" @click.self="showContactModal = false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi-person-badge me-1"></i>
                            {{ editingContact ? 'Modifier' : 'Nouveau' }} contact
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="showContactModal = false"></button>
                    </div>
                    <form @submit.prevent="saveContact">
                        <div class="modal-body">
                            <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Prenom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" v-model="contactForm.first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" v-model="contactForm.last_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-sm" v-model="contactForm.email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Telephone</label>
                                    <input type="text" class="form-control form-control-sm" v-model="contactForm.phone">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Societe</label>
                                    <input type="text" class="form-control form-control-sm" v-model="contactForm.company">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Fonction</label>
                                    <input type="text" class="form-control form-control-sm" v-model="contactForm.position">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Categorie <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" v-model="contactForm.category" required>
                                        <option value="client">Client</option>
                                        <option value="partner">Partenaire</option>
                                        <option value="prospect">Prospect</option>
                                        <option value="lead">Lead</option>
                                        <option value="supplier">Fournisseur</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Tags</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" v-model="tagInput"
                                               placeholder="Ajouter un tag" @keyup.enter.prevent="addTag">
                                        <button class="btn btn-outline-secondary" type="button" @click="addTag">
                                            <i class="bi-plus-lg"></i>
                                        </button>
                                    </div>
                                    <div class="mt-1">
                                        <span v-for="(tag, ti) in contactForm.tags" :key="ti"
                                              class="badge bg-light text-dark me-1 mb-1">
                                            {{ tag }}
                                            <i class="bi-x ms-1" style="cursor: pointer;" @click="removeTag(ti)"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Notes</label>
                                    <textarea class="form-control form-control-sm" rows="3" v-model="contactForm.notes"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showContactModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                {{ editingContact ? 'Enregistrer' : 'Creer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showContactModal" class="modal-backdrop fade show"></div>

        <!-- ============= MODAL AFFAIRE ============= -->
        <div class="modal fade" :class="{ show: showDealModal }"
             :style="{ display: showDealModal ? 'block' : 'none' }"
             tabindex="-1" @click.self="showDealModal = false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi-graph-up-arrow me-1"></i>
                            {{ editingDeal ? 'Modifier' : 'Nouvelle' }} affaire
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="showDealModal = false"></button>
                    </div>
                    <form @submit.prevent="saveDeal">
                        <div class="modal-body">
                            <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" v-model="dealForm.title" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Montant (CFA) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model="dealForm.amount" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Contact</label>
                                    <select class="form-select form-select-sm" v-model="dealForm.contact_id">
                                        <option value="">Sans contact</option>
                                        <option v-for="c in contacts" :key="c.id" :value="c.id">
                                            {{ c.first_name }} {{ c.last_name }} {{ c.company ? '- ' + c.company : '' }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Etape <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" v-model="dealForm.stage" required>
                                        <option value="prospection">Prospection</option>
                                        <option value="qualification">Qualification</option>
                                        <option value="proposition">Proposition</option>
                                        <option value="negociation">Negociation</option>
                                        <option value="finalise">Finalise</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Statut <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" v-model="dealForm.status" required>
                                        <option value="open">Ouverte</option>
                                        <option value="won">Gagnee</option>
                                        <option value="lost">Perdue</option>
                                        <option value="abandoned">Abandonnee</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Probabilite (%)</label>
                                    <input type="number" min="0" max="100" class="form-control form-control-sm" v-model="dealForm.probability">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Cloture prevue</label>
                                    <input type="date" class="form-control form-control-sm" v-model="dealForm.expected_close_date">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Description</label>
                                    <textarea class="form-control form-control-sm" rows="3" v-model="dealForm.description"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Notes</label>
                                    <textarea class="form-control form-control-sm" rows="2" v-model="dealForm.notes"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showDealModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                {{ editingDeal ? 'Enregistrer' : 'Creer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showDealModal" class="modal-backdrop fade show"></div>

        <!-- ============= MODAL INTERACTION ============= -->
        <div class="modal fade" :class="{ show: showInteractionModal }"
             :style="{ display: showInteractionModal ? 'block' : 'none' }"
             tabindex="-1" @click.self="showInteractionModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi-chat-plus me-1"></i> Nouvelle interaction</h5>
                        <button type="button" class="btn-close btn-close-white" @click="showInteractionModal = false"></button>
                    </div>
                    <form @submit.prevent="saveInteraction">
                        <div class="modal-body">
                            <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" v-model="interactionForm.type" required>
                                        <option value="call">Appel</option>
                                        <option value="email">Email</option>
                                        <option value="meeting">Reunion</option>
                                        <option value="note">Note</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Sujet <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" v-model="interactionForm.subject" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Contact</label>
                                    <select class="form-select form-select-sm" v-model="interactionForm.contact_id">
                                        <option value="">Sans contact</option>
                                        <option v-for="c in contacts" :key="c.id" :value="c.id">
                                            {{ c.first_name }} {{ c.last_name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Affaire liee</label>
                                    <select class="form-select form-select-sm" v-model="interactionForm.deal_id">
                                        <option value="">Sans affaire</option>
                                        <option v-for="d in deals" :key="d.id" :value="d.id">
                                            {{ d.title }} - {{ formatCurrency(d.amount) }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Planifie le</label>
                                    <input type="datetime-local" class="form-control form-control-sm" v-model="interactionForm.scheduled_at">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Resultat</label>
                                    <input type="text" class="form-control form-control-sm" v-model="interactionForm.outcome"
                                           placeholder="RDV confirme, envoi doc...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Description</label>
                                    <textarea class="form-control form-control-sm" rows="3" v-model="interactionForm.description"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showInteractionModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showInteractionModal" class="modal-backdrop fade show"></div>
    </CompanyLayout>
</template>
