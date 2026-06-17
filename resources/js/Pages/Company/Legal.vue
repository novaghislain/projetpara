<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

const activeTab = ref('dashboard');
const setTab = (tab) => { activeTab.value = tab; };

const stats = ref(null);
const contracts = ref([]);
const cases = ref([]);
const loading = ref(false);
const error = ref(null);
const successMsg = ref('');

const showContractModal = ref(false);
const showCaseModal = ref(false);
const editingContract = ref(null);
const editingCase = ref(null);
const isSubmitting = ref(false);

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

const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR');
};

const statusLabel = (status) => {
    const map = {
        draft: 'Brouillon', active: 'Actif', expired: 'Expiré', terminated: 'Résilié',
        open: 'Ouvert', in_progress: 'En cours', closed: 'Clos', archived: 'Archivé',
        prestation: 'Prestation', nda: 'NDA', licence: 'Licence', emploi: 'Emploi', autre: 'Autre',
        contentieux: 'Contentieux', consultation: 'Consultation', conseil: 'Conseil',
        low: 'Basse', medium: 'Moyenne', high: 'Haute', critical: 'Critique',
    };
    return map[status] || status;
};

const contractTypeIcon = (type) => {
    const map = { prestation: 'bi-gear', nda: 'bi-shield-check', licence: 'bi-key', emploi: 'bi-person-badge', autre: 'bi-file-earmark' };
    return map[type] || 'bi-file-earmark';
};

const caseTypeIcon = (type) => {
    const map = { contentieux: 'bi-exclamation-triangle', consultation: 'bi-chat-dots', conseil: 'bi-lightbulb', autre: 'bi-folder' };
    return map[type] || 'bi-folder';
};

const isStatusGreen = (s) => ['active', 'closed'].includes(s);
const isStatusRed = (s) => ['terminated', 'expired'].includes(s);
const isStatusOrange = (s) => ['draft', 'open', 'in_progress'].includes(s) || ['critical'].includes(s);

async function fetchStats() {
    try { const res = await fetch('/api/company/legal/stats'); if (res.ok) stats.value = await res.json(); } catch (e) { console.error(e); }
}
async function fetchContracts() {
    try { const res = await fetch('/api/company/legal/contracts'); if (res.ok) contracts.value = await res.json(); } catch (e) { console.error(e); }
}
async function fetchCases() {
    try { const res = await fetch('/api/company/legal/cases'); if (res.ok) cases.value = await res.json(); } catch (e) { console.error(e); }
}

function openCreateContract() {
    editingContract.value = null;
    contractForm.value = { title: '', reference: '', type: 'prestation', party_name: '', party_contact: '', description: '', start_date: '', end_date: '', value: null, status: 'draft', file_path: '', signed_by: '', signed_at: '' };
    showContractModal.value = true;
}
function openEditContract(c) {
    editingContract.value = c.id;
    contractForm.value = { title: c.title, reference: c.reference, type: c.type, party_name: c.party_name, party_contact: c.party_contact || '', description: c.description || '', start_date: c.start_date, end_date: c.end_date || '', value: c.value, status: c.status, file_path: c.file_path || '', signed_by: c.signed_by || '', signed_at: c.signed_at || '' };
    showContractModal.value = true;
}
async function saveContract() {
    isSubmitting.value = true; error.value = null;
    try {
        const url = editingContract.value ? `/api/company/legal/contracts/${editingContract.value}` : '/api/company/legal/contracts';
        const method = editingContract.value ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }, body: JSON.stringify(contractForm.value) });
        if (!res.ok) { const d = await res.json(); throw new Error(d.message || Object.values(d.errors || {}).flat().join(', ')); }
        successMsg.value = 'Contrat enregistré'; showContractModal.value = false; await fetchContracts(); await fetchStats();
        setTimeout(() => successMsg.value = '', 3000);
    } catch (e) { error.value = e.message; } finally { isSubmitting.value = false; }
}
async function deleteContract(id) {
    if (!confirm('Supprimer ce contrat ?')) return;
    try { const res = await fetch(`/api/company/legal/contracts/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } }); if (!res.ok) throw new Error('Erreur'); successMsg.value = 'Contrat supprimé'; await fetchContracts(); await fetchStats(); setTimeout(() => successMsg.value = '', 3000); } catch (e) { alert(e.message); }
}

function openCreateCase() {
    editingCase.value = null; caseForm.value = { title: '', reference: '', type: 'contentieux', status: 'open', description: '', assigned_to: '', priority: 'medium', start_date: '', resolution_date: '', notes: '' }; showCaseModal.value = true;
}
function openEditCase(c) {
    editingCase.value = c.id; caseForm.value = { title: c.title, reference: c.reference, type: c.type, status: c.status, description: c.description || '', assigned_to: c.assigned_to || '', priority: c.priority, start_date: c.start_date, resolution_date: c.resolution_date || '', notes: c.notes || '' }; showCaseModal.value = true;
}
async function saveCase() {
    isSubmitting.value = true; error.value = null;
    try {
        const url = editingCase.value ? `/api/company/legal/cases/${editingCase.value}` : '/api/company/legal/cases';
        const method = editingCase.value ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }, body: JSON.stringify(caseForm.value) });
        if (!res.ok) { const d = await res.json(); throw new Error(d.message || Object.values(d.errors || {}).flat().join(', ')); }
        successMsg.value = 'Cas enregistré'; showCaseModal.value = false; await fetchCases(); await fetchStats();
        setTimeout(() => successMsg.value = '', 3000);
    } catch (e) { error.value = e.message; } finally { isSubmitting.value = false; }
}
async function deleteCase(id) {
    if (!confirm('Supprimer ce cas juridique ?')) return;
    try { const res = await fetch(`/api/company/legal/cases/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } }); if (!res.ok) throw new Error('Erreur'); successMsg.value = 'Cas supprimé'; await fetchCases(); await fetchStats(); setTimeout(() => successMsg.value = '', 3000); } catch (e) { alert(e.message); }
}

onMounted(async () => { loading.value = true; await Promise.all([fetchStats(), fetchContracts(), fetchCases()]); loading.value = false; });
</script>

<template>
    <CompanyLayout page-title="Juridique">
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888;font-size:14px;">Chargement…</span>
        </div>

        <template v-else>
            <div v-if="successMsg" class="isup-alert-success mb-2">{{ successMsg }}</div>

            <div class="isup-shell">
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo"><i class="bi-shield-check" style="font-size:20px;"></i></div>
                        <div>
                            <div class="isup-portal-company">Juridique</div>
                            <div class="isup-portal-sub">Contrats et contentieux</div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <!-- Tabs -->
                    <div class="isup-tabs mb-3">
                        <button class="isup-tab" :class="{ active: activeTab === 'dashboard' }" @click="setTab('dashboard')"><i class="bi-speedometer2 me-1"></i> Tableau de bord</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'contracts' }" @click="setTab('contracts')"><i class="bi-file-earmark-text me-1"></i> Contrats</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'cases' }" @click="setTab('cases')"><i class="bi-exclamation-triangle me-1"></i> Contentieux</button>
                    </div>

                    <!-- Dashboard -->
                    <div v-if="activeTab === 'dashboard' && stats">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-blue"><i class="bi-file-earmark-text"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Contrats</div>
                                        <div class="isup-stat-num">{{ stats.total_contracts }}</div>
                                        <small style="color:#2e7d32;">{{ stats.active_contracts }} actifs</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-orange"><i class="bi-exclamation-triangle"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Contentieux</div>
                                        <div class="isup-stat-num">{{ stats.total_cases }}</div>
                                        <small style="color:#e65100;">{{ stats.open_cases }} ouverts</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-red"><i class="bi-shield-exclamation"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Cas critiques</div>
                                        <div class="isup-stat-num">{{ stats.critical_cases }}</div>
                                        <small style="color:#c62828;">priorité haute</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-green"><i class="bi-cash-stack"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Valeur contrats</div>
                                        <div class="isup-stat-num" style="font-size:15px;">{{ formatCurrency(stats.total_contract_value) }}</div>
                                        <small style="color:#888;">CFA</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-file-earmark-text me-2" style="color:#FF7900;"></i>Par type de contrat</div>
                                    <div class="isup-panel-body">
                                        <div v-if="Object.keys(stats.by_contract_type || {}).length">
                                            <div v-for="(count, type) in stats.by_contract_type" :key="type" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row">
                                                <span><i :class="contractTypeIcon(type) + ' me-2'" style="color:#888;"></i>{{ statusLabel(type) }}</span>
                                                <span class="isup-badge-pill">{{ count }}</span>
                                            </div>
                                        </div>
                                        <p v-else style="font-size:12px;color:#888;">Aucun contrat</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-exclamation-triangle me-2" style="color:#FF7900;"></i>Par type de contentieux</div>
                                    <div class="isup-panel-body">
                                        <div v-if="Object.keys(stats.by_case_type || {}).length">
                                            <div v-for="(count, type) in stats.by_case_type" :key="type" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row">
                                                <span><i :class="caseTypeIcon(type) + ' me-2'" style="color:#888;"></i>{{ statusLabel(type) }}</span>
                                                <span class="isup-badge-pill">{{ count }}</span>
                                            </div>
                                        </div>
                                        <p v-else style="font-size:12px;color:#888;">Aucun contentieux</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contrats -->
                    <div v-if="activeTab === 'contracts'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="max-width:300px;"><input type="text" class="isup-input" placeholder="Rechercher un contrat..." v-model="searchContract"></div>
                            <button class="isup-btn-primary" @click="openCreateContract"><i class="bi-plus-lg me-1"></i> Nouveau contrat</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-file-earmark-text me-2" style="color:#FF7900;"></i>Liste des contrats</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead>
                                            <tr><th>Réf.</th><th>Titre</th><th>Type</th><th>Partie adverse</th><th>Début</th><th>Fin</th><th>Valeur</th><th>Statut</th><th class="text-center">Actions</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="c in filteredContracts" :key="c.id">
                                                <td class="fw-semibold" style="font-family:monospace;font-size:11px;">{{ c.reference }}</td>
                                                <td style="font-weight:600;color:#163A5E;">{{ c.title }}
                                                    <div v-if="c.signed_by" style="font-size:11px;color:#888;"><i class="bi-pen me-1"></i>Signé par {{ c.signed_by }}</div>
                                                </td>
                                                <td><span class="isup-badge isup-badge-light"><i :class="contractTypeIcon(c.type) + ' me-1'"></i>{{ statusLabel(c.type) }}</span></td>
                                                <td style="font-size:12px;">{{ c.party_name }}</td>
                                                <td style="font-size:12px;">{{ formatDate(c.start_date) }}</td>
                                                <td style="font-size:12px;">{{ c.end_date ? formatDate(c.end_date) : '-' }}</td>
                                                <td style="font-size:12px;">{{ c.value ? formatCurrency(c.value) : '-' }}</td>
                                                <td><span class="isup-status" :class="isStatusGreen(c.status) ? 'isup-status-green' : isStatusRed(c.status) ? 'isup-status-red' : 'isup-status-grey'">{{ statusLabel(c.status) }}</span></td>
                                                <td class="text-center">
                                                    <button class="isup-icon-btn" title="Modifier" @click="openEditContract(c)"><i class="bi-pencil"></i></button>
                                                    <button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteContract(c.id)"><i class="bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="!filteredContracts.length"><td colspan="9" class="text-center isup-empty-cell">Aucun contrat trouvé</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cas juridiques -->
                    <div v-if="activeTab === 'cases'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="max-width:300px;"><input type="text" class="isup-input" placeholder="Rechercher un cas..." v-model="searchCase"></div>
                            <button class="isup-btn-primary" @click="openCreateCase"><i class="bi-plus-lg me-1"></i> Nouveau cas</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-exclamation-triangle me-2" style="color:#FF7900;"></i>Contentieux</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead>
                                            <tr><th>Réf.</th><th>Titre</th><th>Type</th><th>Assigné à</th><th>Début</th><th>Priorité</th><th>Statut</th><th class="text-center">Actions</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="c in filteredCases" :key="c.id">
                                                <td class="fw-semibold" style="font-family:monospace;font-size:11px;">{{ c.reference }}</td>
                                                <td style="font-weight:600;color:#163A5E;">{{ c.title }}
                                                    <div v-if="c.description" style="font-size:11px;color:#888;max-width:180px;" class="text-truncate">{{ c.description }}</div>
                                                </td>
                                                <td><span class="isup-badge isup-badge-light"><i :class="caseTypeIcon(c.type) + ' me-1'"></i>{{ statusLabel(c.type) }}</span></td>
                                                <td style="font-size:12px;">{{ c.assigned_to || '-' }}</td>
                                                <td style="font-size:12px;">{{ formatDate(c.start_date) }}</td>
                                                <td><span class="isup-status" :class="c.priority === 'critical' ? 'isup-status-red' : c.priority === 'high' ? 'isup-status-orange' : c.priority === 'low' ? 'isup-status-green' : 'isup-status-grey'">{{ statusLabel(c.priority) }}</span></td>
                                                <td><span class="isup-status" :class="isStatusGreen(c.status) ? 'isup-status-green' : c.status === 'in_progress' ? 'isup-status-orange' : 'isup-status-grey'">{{ statusLabel(c.status) }}</span></td>
                                                <td class="text-center">
                                                    <button class="isup-icon-btn" title="Modifier" @click="openEditCase(c)"><i class="bi-pencil"></i></button>
                                                    <button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteCase(c.id)"><i class="bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="!filteredCases.length"><td colspan="8" class="text-center isup-empty-cell">Aucun cas juridique trouvé</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Contrat -->
            <div v-if="showContractModal" class="isup-modal-overlay" @click.self="showContractModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header">
                        <span><i class="bi-file-earmark-text me-2"></i>{{ editingContract ? 'Modifier' : 'Nouveau' }} contrat</span>
                        <button class="isup-modal-close" @click="showContractModal = false">&times;</button>
                    </div>
                    <form @submit.prevent="saveContract">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-md-8"><label class="isup-label">Titre *</label><input type="text" class="isup-input" v-model="contractForm.title" required></div>
                                <div class="col-md-4"><label class="isup-label">Référence *</label><input type="text" class="isup-input" v-model="contractForm.reference" required></div>
                                <div class="col-md-4"><label class="isup-label">Type *</label><select class="isup-select" v-model="contractForm.type" required><option value="prestation">Prestation</option><option value="nda">NDA</option><option value="licence">Licence</option><option value="emploi">Emploi</option><option value="autre">Autre</option></select></div>
                                <div class="col-md-4"><label class="isup-label">Statut *</label><select class="isup-select" v-model="contractForm.status" required><option value="draft">Brouillon</option><option value="active">Actif</option><option value="expired">Expiré</option><option value="terminated">Résilié</option></select></div>
                                <div class="col-md-4"><label class="isup-label">Valeur (CFA)</label><input type="number" step="0.01" class="isup-input" v-model="contractForm.value"></div>
                                <div class="col-md-6"><label class="isup-label">Partie adverse *</label><input type="text" class="isup-input" v-model="contractForm.party_name" required></div>
                                <div class="col-md-6"><label class="isup-label">Contact</label><input type="text" class="isup-input" v-model="contractForm.party_contact"></div>
                                <div class="col-md-4"><label class="isup-label">Date début *</label><input type="date" class="isup-input" v-model="contractForm.start_date" required></div>
                                <div class="col-md-4"><label class="isup-label">Date fin</label><input type="date" class="isup-input" v-model="contractForm.end_date"></div>
                                <div class="col-md-4"><label class="isup-label">Signé par</label><input type="text" class="isup-input" v-model="contractForm.signed_by"></div>
                                <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="3" v-model="contractForm.description"></textarea></div>
                                <div class="col-md-6"><label class="isup-label">Fichier (chemin)</label><input type="text" class="isup-input" v-model="contractForm.file_path"></div>
                                <div class="col-md-6"><label class="isup-label">Date de signature</label><input type="date" class="isup-input" v-model="contractForm.signed_at"></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showContractModal = false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>{{ editingContract ? 'Enregistrer' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Cas -->
            <div v-if="showCaseModal" class="isup-modal-overlay" @click.self="showCaseModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header">
                        <span><i class="bi-exclamation-triangle me-2"></i>{{ editingCase ? 'Modifier' : 'Nouveau' }} cas juridique</span>
                        <button class="isup-modal-close" @click="showCaseModal = false">&times;</button>
                    </div>
                    <form @submit.prevent="saveCase">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-md-8"><label class="isup-label">Titre *</label><input type="text" class="isup-input" v-model="caseForm.title" required></div>
                                <div class="col-md-4"><label class="isup-label">Référence *</label><input type="text" class="isup-input" v-model="caseForm.reference" required></div>
                                <div class="col-md-4"><label class="isup-label">Type *</label><select class="isup-select" v-model="caseForm.type" required><option value="contentieux">Contentieux</option><option value="consultation">Consultation</option><option value="conseil">Conseil</option><option value="autre">Autre</option></select></div>
                                <div class="col-md-4"><label class="isup-label">Statut *</label><select class="isup-select" v-model="caseForm.status" required><option value="open">Ouvert</option><option value="in_progress">En cours</option><option value="closed">Clos</option><option value="archived">Archivé</option></select></div>
                                <div class="col-md-4"><label class="isup-label">Priorité *</label><select class="isup-select" v-model="caseForm.priority" required><option value="low">Basse</option><option value="medium">Moyenne</option><option value="high">Haute</option><option value="critical">Critique</option></select></div>
                                <div class="col-md-6"><label class="isup-label">Assigné à</label><input type="text" class="isup-input" v-model="caseForm.assigned_to"></div>
                                <div class="col-md-3"><label class="isup-label">Date début *</label><input type="date" class="isup-input" v-model="caseForm.start_date" required></div>
                                <div class="col-md-3"><label class="isup-label">Date résolution</label><input type="date" class="isup-input" v-model="caseForm.resolution_date"></div>
                                <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="3" v-model="caseForm.description"></textarea></div>
                                <div class="col-12"><label class="isup-label">Notes</label><textarea class="isup-input" rows="3" v-model="caseForm.notes"></textarea></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showCaseModal = false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>{{ editingCase ? 'Enregistrer' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

