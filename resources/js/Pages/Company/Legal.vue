<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

// ─── État actif des onglets ──────────────────────────────────────────────────
const activeTab = ref('dashboard');
const setTab = (tab) => { activeTab.value = tab; };

// ─── Données ─────────────────────────────────────────────────────────────────
const stats = ref(null);
const contracts = ref([]);
const cases = ref([]);
const loading = ref(false);
const error = ref(null);

// ─── Modales ─────────────────────────────────────────────────────────────────
const showContractModal = ref(false);
const showCaseModal = ref(false);
const editingContract = ref(null);
const editingCase = ref(null);
const isSubmitting = ref(false);

// ─── Formulaires ─────────────────────────────────────────────────────────────
const contractForm = ref({
    title: '', reference: '', type: 'prestation', party_name: '',
    party_contact: '', description: '', start_date: '', end_date: '',
    value: null, status: 'draft', file_path: '', signed_by: '', signed_at: '',
});

const caseForm = ref({
    title: '', reference: '', type: 'contentieux', status: 'open',
    description: '', assigned_to: '', priority: 'medium',
    start_date: '', resolution_date: '', notes: '',
});

// ─── Recherche ───────────────────────────────────────────────────────────────
const searchContract = ref('');
const searchCase = ref('');

const filteredContracts = computed(() => {
    if (!searchContract.value) return contracts.value;
    const q = searchContract.value.toLowerCase();
    return contracts.value.filter(c =>
        c.title.toLowerCase().includes(q) ||
        c.reference.toLowerCase().includes(q) ||
        c.party_name.toLowerCase().includes(q) ||
        c.type.toLowerCase().includes(q)
    );
});

const filteredCases = computed(() => {
    if (!searchCase.value) return cases.value;
    const q = searchCase.value.toLowerCase();
    return cases.value.filter(c =>
        c.title.toLowerCase().includes(q) ||
        c.reference.toLowerCase().includes(q) ||
        c.type.toLowerCase().includes(q) ||
        (c.assigned_to && c.assigned_to.toLowerCase().includes(q))
    );
});

// ─── Helpers ─────────────────────────────────────────────────────────────────
const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR');
};

const statusBadge = (status) => {
    const map = {
        draft: 'bg-secondary',
        active: 'bg-success',
        expired: 'bg-warning text-dark',
        terminated: 'bg-danger',
        open: 'bg-info text-dark',
        in_progress: 'bg-primary',
        closed: 'bg-success',
        archived: 'bg-secondary',
    };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = {
        draft: 'Brouillon',
        active: 'Actif',
        expired: 'Expiré',
        terminated: 'Résilié',
        open: 'Ouvert',
        in_progress: 'En cours',
        closed: 'Clos',
        archived: 'Archivé',
        prestation: 'Prestation',
        nda: 'NDA',
        licence: 'Licence',
        emploi: 'Emploi',
        autre: 'Autre',
        contentieux: 'Contentieux',
        consultation: 'Consultation',
        conseil: 'Conseil',
        low: 'Basse',
        medium: 'Moyenne',
        high: 'Haute',
        critical: 'Critique',
    };
    return map[status] || status;
};

const priorityBadge = (priority) => {
    const map = {
        low: 'bg-success',
        medium: 'bg-info text-dark',
        high: 'bg-warning text-dark',
        critical: 'bg-danger',
    };
    return map[priority] || 'bg-secondary';
};

const contractTypeIcon = (type) => {
    const map = {
        prestation: 'bi-gear',
        nda: 'bi-shield-check',
        licence: 'bi-key',
        emploi: 'bi-person-badge',
        autre: 'bi-file-earmark',
    };
    return map[type] || 'bi-file-earmark';
};

const caseTypeIcon = (type) => {
    const map = {
        contentieux: 'bi-exclamation-triangle',
        consultation: 'bi-chat-dots',
        conseil: 'bi-lightbulb',
        autre: 'bi-folder',
    };
    return map[type] || 'bi-folder';
};

// ─── API Calls ───────────────────────────────────────────────────────────────
async function fetchStats() {
    try {
        const res = await fetch('/api/company/legal/stats');
        if (!res.ok) throw new Error('Erreur chargement statistiques');
        stats.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchContracts() {
    try {
        const res = await fetch('/api/company/legal/contracts');
        if (!res.ok) throw new Error('Erreur chargement contrats');
        contracts.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchCases() {
    try {
        const res = await fetch('/api/company/legal/cases');
        if (!res.ok) throw new Error('Erreur chargement contentieux');
        cases.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

// ─── Actions Contrats ────────────────────────────────────────────────────────
function openCreateContract() {
    editingContract.value = null;
    contractForm.value = {
        title: '', reference: '', type: 'prestation', party_name: '',
        party_contact: '', description: '', start_date: '', end_date: '',
        value: null, status: 'draft', file_path: '', signed_by: '', signed_at: '',
    };
    showContractModal.value = true;
}

function openEditContract(c) {
    editingContract.value = c.id;
    contractForm.value = {
        title: c.title, reference: c.reference, type: c.type,
        party_name: c.party_name, party_contact: c.party_contact || '',
        description: c.description || '', start_date: c.start_date,
        end_date: c.end_date || '', value: c.value, status: c.status,
        file_path: c.file_path || '', signed_by: c.signed_by || '',
        signed_at: c.signed_at || '',
    };
    showContractModal.value = true;
}

async function saveContract() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const url = editingContract.value
            ? `/api/company/legal/contracts/${editingContract.value}`
            : '/api/company/legal/contracts';
        const method = editingContract.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(contractForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showContractModal.value = false;
        await fetchContracts();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function deleteContract(id) {
    if (!confirm('Supprimer ce contrat ?')) return;
    try {
        const res = await fetch(`/api/company/legal/contracts/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchContracts();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// ─── Actions Cas juridiques ─────────────────────────────────────────────────
function openCreateCase() {
    editingCase.value = null;
    caseForm.value = {
        title: '', reference: '', type: 'contentieux', status: 'open',
        description: '', assigned_to: '', priority: 'medium',
        start_date: '', resolution_date: '', notes: '',
    };
    showCaseModal.value = true;
}

function openEditCase(c) {
    editingCase.value = c.id;
    caseForm.value = {
        title: c.title, reference: c.reference, type: c.type,
        status: c.status, description: c.description || '',
        assigned_to: c.assigned_to || '', priority: c.priority,
        start_date: c.start_date, resolution_date: c.resolution_date || '',
        notes: c.notes || '',
    };
    showCaseModal.value = true;
}

async function saveCase() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const url = editingCase.value
            ? `/api/company/legal/cases/${editingCase.value}`
            : '/api/company/legal/cases';
        const method = editingCase.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(caseForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showCaseModal.value = false;
        await fetchCases();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function deleteCase(id) {
    if (!confirm('Supprimer ce cas juridique ?')) return;
    try {
        const res = await fetch(`/api/company/legal/cases/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchCases();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// ─── Initialisation ──────────────────────────────────────────────────────────
onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchContracts(), fetchCases()]);
    loading.value = false;
});
</script>

<template>
    <CompanyLayout page-title="Juridique">
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
                    <button class="nav-link" :class="{ active: activeTab === 'contracts' }" @click="setTab('contracts')">
                        <i class="bi-file-earmark-text me-1"></i> Contrats
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'cases' }" @click="setTab('cases')">
                        <i class="bi-exclamation-triangle me-1"></i> Contentieux
                    </button>
                </li>
            </ul>

            <!-- ══════════════════ DASHBOARD ══════════════════ -->
            <div v-if="activeTab === 'dashboard' && stats">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi-file-earmark-text"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Contrats</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.total_contracts }}</div>
                                    <small class="text-success">{{ stats.active_contracts }} actifs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Contentieux</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.total_cases }}</div>
                                    <small class="text-warning">{{ stats.open_cases }} ouverts</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="bi-shield-exclamation"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Cas critiques</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.critical_cases }}</div>
                                    <small class="text-danger">priorité haute</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi-cash-stack"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Valeur contrats</div>
                                    <div class="h5 mb-0 fw-bold">{{ formatCurrency(stats.total_contract_value) }}</div>
                                    <small class="text-success">CFA</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-file-earmark-text me-2"></i>Par type de contrat</h6>
                            <div v-if="Object.keys(stats.by_contract_type || {}).length">
                                <div v-for="(count, type) in stats.by_contract_type" :key="type" class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <i :class="contractTypeIcon(type) + ' me-2 text-muted'"></i>
                                        {{ statusLabel(type) }}
                                    </span>
                                    <span class="badge bg-primary rounded-pill">{{ count }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucun contrat</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-exclamation-triangle me-2"></i>Par type de contentieux</h6>
                            <div v-if="Object.keys(stats.by_case_type || {}).length">
                                <div v-for="(count, type) in stats.by_case_type" :key="type" class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <i :class="caseTypeIcon(type) + ' me-2 text-muted'"></i>
                                        {{ statusLabel(type) }}
                                    </span>
                                    <span class="badge bg-warning rounded-pill">{{ count }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucun contentieux</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ CONTRATS ══════════════════ -->
            <div v-if="activeTab === 'contracts'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="max-width: 350px;">
                        <input type="text" class="form-control form-control-sm" placeholder="Rechercher un contrat..."
                               v-model="searchContract">
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateContract">
                        <i class="bi-plus-lg me-1"></i> Nouveau contrat
                    </button>
                </div>

                <div class="card card-dashboard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Réf.</th>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Partie adverse</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Valeur</th>
                                    <th>Statut</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in filteredContracts" :key="c.id">
                                    <td class="fw-semibold small">{{ c.reference }}</td>
                                    <td>
                                        {{ c.title }}
                                        <div v-if="c.signed_by" class="text-muted small">
                                            <i class="bi-pen me-1"></i>Signé par {{ c.signed_by }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i :class="contractTypeIcon(c.type) + ' me-1'"></i>
                                            {{ statusLabel(c.type) }}
                                        </span>
                                    </td>
                                    <td>{{ c.party_name }}</td>
                                    <td>{{ formatDate(c.start_date) }}</td>
                                    <td>{{ c.end_date ? formatDate(c.end_date) : '-' }}</td>
                                    <td>{{ c.value ? formatCurrency(c.value) : '-' }}</td>
                                    <td>
                                        <span class="badge" :class="statusBadge(c.status)">{{ statusLabel(c.status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Modifier" @click="openEditContract(c)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteContract(c.id)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!filteredContracts.length">
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi-file-earmark-text fs-4 d-block mb-1"></i>
                                        Aucun contrat trouvé
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ CAS JURIDIQUES ══════════════════ -->
            <div v-if="activeTab === 'cases'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="max-width: 350px;">
                        <input type="text" class="form-control form-control-sm" placeholder="Rechercher un cas..."
                               v-model="searchCase">
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateCase">
                        <i class="bi-plus-lg me-1"></i> Nouveau cas
                    </button>
                </div>

                <div class="card card-dashboard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Réf.</th>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Assigné à</th>
                                    <th>Début</th>
                                    <th>Priorité</th>
                                    <th>Statut</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in filteredCases" :key="c.id">
                                    <td class="fw-semibold small">{{ c.reference }}</td>
                                    <td>
                                        {{ c.title }}
                                        <div v-if="c.description" class="text-muted small text-truncate" style="max-width: 200px;">
                                            {{ c.description }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i :class="caseTypeIcon(c.type) + ' me-1'"></i>
                                            {{ statusLabel(c.type) }}
                                        </span>
                                    </td>
                                    <td>{{ c.assigned_to || '-' }}</td>
                                    <td>{{ formatDate(c.start_date) }}</td>
                                    <td>
                                        <span class="badge" :class="priorityBadge(c.priority)">{{ statusLabel(c.priority) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="statusBadge(c.status)">{{ statusLabel(c.status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Modifier" @click="openEditCase(c)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteCase(c.id)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!filteredCases.length">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi-exclamation-triangle fs-4 d-block mb-1"></i>
                                        Aucun cas juridique trouvé
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ MODAL CONTRAT ══════════════════ -->
            <div class="modal fade" :class="{ show: showContractModal }" :style="{ display: showContractModal ? 'block' : 'none' }"
                 tabindex="-1" @click.self="showContractModal = false">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi-file-earmark-text me-1"></i>
                                {{ editingContract ? 'Modifier' : 'Nouveau' }} contrat
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="showContractModal = false"></button>
                        </div>
                        <form @submit.prevent="saveContract">
                            <div class="modal-body">
                                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label small fw-semibold">Titre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="contractForm.title" required
                                               placeholder="Objet du contrat">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Référence <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="contractForm.reference" required
                                               placeholder="REF-001">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="contractForm.type" required>
                                            <option value="prestation">Prestation</option>
                                            <option value="nda">NDA (Confidentialité)</option>
                                            <option value="licence">Licence</option>
                                            <option value="emploi">Emploi</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Statut <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="contractForm.status" required>
                                            <option value="draft">Brouillon</option>
                                            <option value="active">Actif</option>
                                            <option value="expired">Expiré</option>
                                            <option value="terminated">Résilié</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Valeur (CFA)</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model="contractForm.value">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Partie adverse <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="contractForm.party_name" required
                                               placeholder="Nom de l'autre partie">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Contact partie adverse</label>
                                        <input type="text" class="form-control form-control-sm" v-model="contractForm.party_contact"
                                               placeholder="Email, téléphone...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Date début <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" v-model="contractForm.start_date" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Date fin</label>
                                        <input type="date" class="form-control form-control-sm" v-model="contractForm.end_date">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Signé par</label>
                                        <input type="text" class="form-control form-control-sm" v-model="contractForm.signed_by"
                                               placeholder="Nom du signataire">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Description</label>
                                        <textarea class="form-control form-control-sm" rows="3" v-model="contractForm.description"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Fichier (chemin)</label>
                                        <input type="text" class="form-control form-control-sm" v-model="contractForm.file_path"
                                               placeholder="URL ou chemin du document">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Date de signature</label>
                                        <input type="date" class="form-control form-control-sm" v-model="contractForm.signed_at">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showContractModal = false">Annuler</button>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ editingContract ? 'Enregistrer' : 'Créer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="showContractModal" class="modal-backdrop fade show"></div>

            <!-- ══════════════════ MODAL CAS JURIDIQUE ══════════════════ -->
            <div class="modal fade" :class="{ show: showCaseModal }" :style="{ display: showCaseModal ? 'block' : 'none' }"
                 tabindex="-1" @click.self="showCaseModal = false">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi-exclamation-triangle me-1"></i>
                                {{ editingCase ? 'Modifier' : 'Nouveau' }} cas juridique
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="showCaseModal = false"></button>
                        </div>
                        <form @submit.prevent="saveCase">
                            <div class="modal-body">
                                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label small fw-semibold">Titre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="caseForm.title" required
                                               placeholder="Objet du cas">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Référence <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="caseForm.reference" required
                                               placeholder="CAS-001">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="caseForm.type" required>
                                            <option value="contentieux">Contentieux</option>
                                            <option value="consultation">Consultation</option>
                                            <option value="conseil">Conseil</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Statut <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="caseForm.status" required>
                                            <option value="open">Ouvert</option>
                                            <option value="in_progress">En cours</option>
                                            <option value="closed">Clos</option>
                                            <option value="archived">Archivé</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Priorité <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="caseForm.priority" required>
                                            <option value="low">Basse</option>
                                            <option value="medium">Moyenne</option>
                                            <option value="high">Haute</option>
                                            <option value="critical">Critique</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Assigné à</label>
                                        <input type="text" class="form-control form-control-sm" v-model="caseForm.assigned_to"
                                               placeholder="Nom du responsable">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Date début <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" v-model="caseForm.start_date" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Date résolution</label>
                                        <input type="date" class="form-control form-control-sm" v-model="caseForm.resolution_date">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Description</label>
                                        <textarea class="form-control form-control-sm" rows="3" v-model="caseForm.description"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Notes</label>
                                        <textarea class="form-control form-control-sm" rows="3" v-model="caseForm.notes" placeholder="Notes internes..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showCaseModal = false">Annuler</button>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ editingCase ? 'Enregistrer' : 'Créer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="showCaseModal" class="modal-backdrop fade show"></div>
        </template>
    </CompanyLayout>
</template>
