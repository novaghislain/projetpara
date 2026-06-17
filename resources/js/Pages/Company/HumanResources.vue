<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

const activeTab = ref('dashboard');
const setTab = (tab) => { activeTab.value = tab; };

const stats = ref(null);
const employees = ref([]);
const leaveRequests = ref([]);
const expenses = ref([]);
const loading = ref(false);
const error = ref(null);

const showEmployeeModal = ref(false);
const showLeaveModal = ref(false);
const showExpenseModal = ref(false);
const editingEmployee = ref(null);
const isSubmitting = ref(false);

const employeeForm = ref({
    first_name: '', last_name: '', email: '', phone: '',
    position: '', department: '', hire_date: '', salary: null,
    contract_type: 'CDI', status: 'active',
});

const leaveForm = ref({
    employee_id: '', type: 'conge', start_date: '', end_date: '', reason: '',
});

const expenseForm = ref({
    employee_id: '', category: '', amount: null, description: '', receipt_path: '',
});

const approvalNotes = ref('');
const searchQuery = ref('');

const filteredEmployees = computed(() => {
    if (!searchQuery.value) return employees.value;
    const q = searchQuery.value.toLowerCase();
    return employees.value.filter(e =>
        e.first_name.toLowerCase().includes(q) ||
        e.last_name.toLowerCase().includes(q) ||
        e.email.toLowerCase().includes(q) ||
        e.position.toLowerCase().includes(q) ||
        e.department.toLowerCase().includes(q)
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

const statusLabelMap = {
    active: 'Actif', suspended: 'Suspendu', left: 'Quitté',
    pending: 'En attente', approved: 'Approuvé', rejected: 'Rejeté',
    conge: 'Congé', maladie: 'Maladie', autre: 'Autre',
};
const statusLabel = (s) => statusLabelMap[s] || s;

async function fetchStats() {
    try {
        const res = await fetch('/api/company/hr/stats');
        if (!res.ok) throw new Error('Erreur chargement statistiques');
        stats.value = await res.json();
    } catch (e) { console.error(e); }
}

async function fetchEmployees() {
    try {
        const res = await fetch('/api/company/hr/employees');
        if (!res.ok) throw new Error('Erreur chargement employés');
        employees.value = await res.json();
    } catch (e) { console.error(e); }
}

async function fetchLeaveRequests() {
    try {
        const res = await fetch('/api/company/hr/leave-requests');
        if (!res.ok) throw new Error('Erreur chargement congés');
        leaveRequests.value = await res.json();
    } catch (e) { console.error(e); }
}

async function fetchExpenses() {
    try {
        const res = await fetch('/api/company/hr/expenses');
        if (!res.ok) throw new Error('Erreur chargement notes de frais');
        expenses.value = await res.json();
    } catch (e) { console.error(e); }
}

function openCreateEmployee() {
    editingEmployee.value = null;
    employeeForm.value = {
        first_name: '', last_name: '', email: '', phone: '',
        position: '', department: '', hire_date: '', salary: null,
        contract_type: 'CDI', status: 'active',
    };
    showEmployeeModal.value = true;
}

function openEditEmployee(emp) {
    editingEmployee.value = emp.id;
    employeeForm.value = {
        first_name: emp.first_name, last_name: emp.last_name, email: emp.email,
        phone: emp.phone || '', position: emp.position, department: emp.department,
        hire_date: emp.hire_date, salary: emp.salary, contract_type: emp.contract_type,
        status: emp.status,
    };
    showEmployeeModal.value = true;
}

async function saveEmployee() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const url = editingEmployee.value ? `/api/company/hr/employees/${editingEmployee.value}` : '/api/company/hr/employees';
        const method = editingEmployee.value ? 'PUT' : 'POST';
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(employeeForm.value),
        });
        if (!res.ok) { const errData = await res.json(); throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', ')); }
        showEmployeeModal.value = false;
        await fetchEmployees(); await fetchStats();
    } catch (e) { error.value = e.message; }
    finally { isSubmitting.value = false; }
}

async function deleteEmployee(id) {
    if (!confirm('Supprimer cet employé ?')) return;
    try {
        const res = await fetch(`/api/company/hr/employees/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchEmployees(); await fetchStats();
    } catch (e) { alert(e.message); }
}

function openCreateLeave() {
    leaveForm.value = { employee_id: '', type: 'conge', start_date: '', end_date: '', reason: '' };
    showLeaveModal.value = true;
}

async function saveLeaveRequest() {
    isSubmitting.value = true; error.value = null;
    try {
        const res = await fetch('/api/company/hr/leave-requests', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(leaveForm.value),
        });
        if (!res.ok) { const errData = await res.json(); throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', ')); }
        showLeaveModal.value = false; await fetchLeaveRequests(); await fetchStats();
    } catch (e) { error.value = e.message; }
    finally { isSubmitting.value = false; }
}

async function approveLeave(id, status) {
    const notes = status === 'rejected' ? (prompt('Motif du rejet :') || '') : '';
    approvalNotes.value = notes;
    try {
        const res = await fetch(`/api/company/hr/leave-requests/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ status, approver_notes: notes }),
        });
        if (!res.ok) throw new Error('Erreur mise à jour');
        await fetchLeaveRequests(); await fetchStats();
    } catch (e) { alert(e.message); }
}

function openCreateExpense() {
    expenseForm.value = { employee_id: '', category: '', amount: null, description: '', receipt_path: '' };
    showExpenseModal.value = true;
}

async function saveExpense() {
    isSubmitting.value = true; error.value = null;
    try {
        const res = await fetch('/api/company/hr/expenses', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(expenseForm.value),
        });
        if (!res.ok) { const errData = await res.json(); throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', ')); }
        showExpenseModal.value = false; await fetchExpenses(); await fetchStats();
    } catch (e) { error.value = e.message; }
    finally { isSubmitting.value = false; }
}

async function approveExpense(id, status) {
    try {
        const res = await fetch(`/api/company/hr/expenses/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ status }),
        });
        if (!res.ok) throw new Error('Erreur mise à jour');
        await fetchExpenses(); await fetchStats();
    } catch (e) { alert(e.message); }
}

onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchEmployees(), fetchLeaveRequests(), fetchExpenses()]);
    loading.value = false;
});
</script>

<template>
    <CompanyLayout page-title="Gestion RH">
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888;font-size:14px;">Chargement…</span>
        </div>

        <template v-else>
            <div class="isup-shell">
                <!-- Header -->
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo"><i class="bi-people" style="font-size:20px;"></i></div>
                        <div>
                            <div class="isup-portal-company">Gestion RH</div>
                            <div class="isup-portal-sub">Employés, congés et notes de frais</div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <!-- Tabs -->
                    <div class="isup-tabs mb-3">
                        <button class="isup-tab" :class="{ active: activeTab === 'dashboard' }" @click="setTab('dashboard')"><i class="bi-speedometer2 me-1"></i> Tableau de bord</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'employees' }" @click="setTab('employees')"><i class="bi-people me-1"></i> Employés</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'leaves' }" @click="setTab('leaves')"><i class="bi-calendar-check me-1"></i> Congés</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'expenses' }" @click="setTab('expenses')"><i class="bi-receipt me-1"></i> Notes de frais</button>
                    </div>

                    <!-- Dashboard -->
                    <div v-if="activeTab === 'dashboard' && stats">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-blue"><i class="bi-people"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Employés</div>
                                        <div class="isup-stat-num">{{ stats.total_employees }}</div>
                                        <small style="color:#2e7d32;">{{ stats.active_employees }} actifs</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-orange"><i class="bi-calendar-check"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Congés en cours</div>
                                        <div class="isup-stat-num">{{ stats.ongoing_leaves }}</div>
                                        <small style="color:#e65100;">{{ stats.pending_leaves }} en attente</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-cyan"><i class="bi-receipt"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Notes de frais</div>
                                        <div class="isup-stat-num">{{ stats.pending_expenses }}</div>
                                        <small style="color:#00838f;">en attente</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-green"><i class="bi-cash-stack"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Frais du mois</div>
                                        <div class="isup-stat-num" style="font-size:15px;">{{ formatCurrency(stats.monthly_expenses) }}</div>
                                        <small style="color:#888;">CFA</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-file-earmark-text me-2" style="color:#FF7900;"></i>Par contrat</div>
                                    <div class="isup-panel-body">
                                        <div v-if="Object.keys(stats.by_contract || {}).length">
                                            <div v-for="(count, type) in stats.by_contract" :key="type" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row">
                                                <span>{{ type }}</span>
                                                <span class="isup-badge-pill">{{ count }}</span>
                                            </div>
                                        </div>
                                        <p v-else style="font-size:12px;color:#888;">Aucun employé</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-building me-2" style="color:#FF7900;"></i>Par département</div>
                                    <div class="isup-panel-body">
                                        <div v-if="Object.keys(stats.by_department || {}).length">
                                            <div v-for="(count, dept) in stats.by_department" :key="dept" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row">
                                                <span>{{ dept }}</span>
                                                <span class="isup-badge-pill">{{ count }}</span>
                                            </div>
                                        </div>
                                        <p v-else style="font-size:12px;color:#888;">Aucun employé</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employés -->
                    <div v-if="activeTab === 'employees'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="max-width:300px;">
                                <input type="text" class="isup-input" placeholder="Rechercher un employé..." v-model="searchQuery">
                            </div>
                            <button class="isup-btn-primary" @click="openCreateEmployee"><i class="bi-plus-lg me-1"></i> Nouvel employé</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-people me-2" style="color:#FF7900;"></i>Liste des employés</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead>
                                            <tr>
                                                <th>Nom</th><th>Email</th><th>Poste</th><th>Département</th>
                                                <th>Contrat</th><th>Salaire</th><th>Statut</th><th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="emp in filteredEmployees" :key="emp.id">
                                                <td style="font-weight:600;color:#163A5E;">{{ emp.first_name }} {{ emp.last_name }}
                                                    <div style="font-size:11px;color:#888;">Embauché le {{ formatDate(emp.hire_date) }}</div>
                                                </td>
                                                <td style="font-size:12px;">{{ emp.email }}<br><small style="color:#888;">{{ emp.phone }}</small></td>
                                                <td style="font-size:12px;">{{ emp.position }}</td>
                                                <td style="font-size:12px;">{{ emp.department }}</td>
                                                <td><span class="isup-badge isup-badge-light">{{ emp.contract_type }}</span></td>
                                                <td style="font-size:12px;">{{ emp.salary ? formatCurrency(emp.salary) : '-' }}</td>
                                                <td><span class="isup-status" :class="emp.status === 'active' ? 'isup-status-green' : 'isup-status-grey'">{{ statusLabel(emp.status) }}</span></td>
                                                <td class="text-center">
                                                    <button class="isup-icon-btn" title="Modifier" @click="openEditEmployee(emp)"><i class="bi-pencil"></i></button>
                                                    <button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteEmployee(emp.id)"><i class="bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="!filteredEmployees.length">
                                                <td colspan="8" class="text-center isup-empty-cell">Aucun employé trouvé</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Congés -->
                    <div v-if="activeTab === 'leaves'">
                        <div class="d-flex justify-content-end mb-3">
                            <button class="isup-btn-primary" @click="openCreateLeave"><i class="bi-plus-lg me-1"></i> Nouvelle demande</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-calendar-check me-2" style="color:#FF7900;"></i>Demandes de congés</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead>
                                            <tr>
                                                <th>Employé</th><th>Type</th><th>Début</th><th>Fin</th>
                                                <th>Motif</th><th>Statut</th><th>Approbateur</th><th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lr in leaveRequests" :key="lr.id">
                                                <td style="font-weight:600;color:#163A5E;">{{ lr.employee_name }}
                                                    <div style="font-size:11px;color:#888;">{{ lr.department }}</div>
                                                </td>
                                                <td><span class="isup-badge isup-badge-light">{{ statusLabel(lr.type) }}</span></td>
                                                <td style="font-size:12px;">{{ formatDate(lr.start_date) }}</td>
                                                <td style="font-size:12px;">{{ formatDate(lr.end_date) }}</td>
                                                <td><span class="text-truncate d-inline-block" style="max-width:150px;font-size:12px;" :title="lr.reason">{{ lr.reason || '-' }}</span></td>
                                                <td><span class="isup-status" :class="lr.status === 'approved' ? 'isup-status-green' : lr.status === 'rejected' ? 'isup-status-red' : 'isup-status-orange'">{{ statusLabel(lr.status) }}</span></td>
                                                <td style="font-size:12px;">{{ lr.approved_by || '-' }}</td>
                                                <td class="text-center">
                                                    <template v-if="lr.status === 'pending'">
                                                        <button class="isup-icon-btn" style="color:#2e7d32;" title="Approuver" @click="approveLeave(lr.id, 'approved')"><i class="bi-check-lg"></i></button>
                                                        <button class="isup-icon-btn isup-icon-danger" title="Rejeter" @click="approveLeave(lr.id, 'rejected')"><i class="bi-x-lg"></i></button>
                                                    </template>
                                                    <span v-else style="font-size:11px;color:#888;">—</span>
                                                </td>
                                            </tr>
                                            <tr v-if="!leaveRequests.length">
                                                <td colspan="8" class="text-center isup-empty-cell">Aucune demande de congé</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes de frais -->
                    <div v-if="activeTab === 'expenses'">
                        <div class="d-flex justify-content-end mb-3">
                            <button class="isup-btn-primary" @click="openCreateExpense"><i class="bi-plus-lg me-1"></i> Nouvelle note de frais</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-receipt me-2" style="color:#FF7900;"></i>Notes de frais</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead>
                                            <tr>
                                                <th>Employé</th><th>Catégorie</th><th>Montant</th>
                                                <th>Description</th><th>Statut</th><th>Approbateur</th><th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="exp in expenses" :key="exp.id">
                                                <td style="font-weight:600;color:#163A5E;">{{ exp.employee_name }}
                                                    <div style="font-size:11px;color:#888;">{{ exp.department }}</div>
                                                </td>
                                                <td><span class="isup-badge isup-badge-light">{{ exp.category }}</span></td>
                                                <td style="font-weight:600;">{{ formatCurrency(exp.amount) }}</td>
                                                <td><span class="text-truncate d-inline-block" style="max-width:180px;font-size:12px;" :title="exp.description">{{ exp.description }}</span></td>
                                                <td><span class="isup-status" :class="exp.status === 'approved' ? 'isup-status-green' : exp.status === 'rejected' ? 'isup-status-red' : 'isup-status-orange'">{{ statusLabel(exp.status) }}</span></td>
                                                <td style="font-size:12px;">{{ exp.approved_by || '-' }}</td>
                                                <td class="text-center">
                                                    <template v-if="exp.status === 'pending'">
                                                        <button class="isup-icon-btn" style="color:#2e7d32;" title="Approuver" @click="approveExpense(exp.id, 'approved')"><i class="bi-check-lg"></i></button>
                                                        <button class="isup-icon-btn isup-icon-danger" title="Rejeter" @click="approveExpense(exp.id, 'rejected')"><i class="bi-x-lg"></i></button>
                                                    </template>
                                                    <span v-else style="font-size:11px;color:#888;">—</span>
                                                </td>
                                            </tr>
                                            <tr v-if="!expenses.length">
                                                <td colspan="7" class="text-center isup-empty-cell">Aucune note de frais</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Employé -->
            <div v-if="showEmployeeModal" class="isup-modal-overlay" @click.self="showEmployeeModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header">
                        <span><i class="bi-person-badge me-2"></i>{{ editingEmployee ? 'Modifier' : 'Nouvel' }} employé</span>
                        <button class="isup-modal-close" @click="showEmployeeModal = false">&times;</button>
                    </div>
                    <form @submit.prevent="saveEmployee">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-md-6"><label class="isup-label">Prénom *</label><input type="text" class="isup-input" v-model="employeeForm.first_name" required></div>
                                <div class="col-md-6"><label class="isup-label">Nom *</label><input type="text" class="isup-input" v-model="employeeForm.last_name" required></div>
                                <div class="col-md-6"><label class="isup-label">Email *</label><input type="email" class="isup-input" v-model="employeeForm.email" required></div>
                                <div class="col-md-6"><label class="isup-label">Téléphone</label><input type="text" class="isup-input" v-model="employeeForm.phone"></div>
                                <div class="col-md-6"><label class="isup-label">Poste *</label><input type="text" class="isup-input" v-model="employeeForm.position" required></div>
                                <div class="col-md-6"><label class="isup-label">Département *</label><input type="text" class="isup-input" v-model="employeeForm.department" required></div>
                                <div class="col-md-4"><label class="isup-label">Date d'embauche *</label><input type="date" class="isup-input" v-model="employeeForm.hire_date" required></div>
                                <div class="col-md-4"><label class="isup-label">Salaire (CFA)</label><input type="number" step="0.01" min="0" class="isup-input" v-model="employeeForm.salary"></div>
                                <div class="col-md-4"><label class="isup-label">Type de contrat *</label>
                                    <select class="isup-select" v-model="employeeForm.contract_type" required>
                                        <option value="CDI">CDI</option><option value="CDD">CDD</option>
                                        <option value="INTERIM">INTERIM</option><option value="STAGE">STAGE</option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="isup-label">Statut *</label>
                                    <select class="isup-select" v-model="employeeForm.status" required>
                                        <option value="active">Actif</option><option value="suspended">Suspendu</option><option value="left">Quitté</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showEmployeeModal = false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>
                                {{ editingEmployee ? 'Enregistrer' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Congé -->
            <div v-if="showLeaveModal" class="isup-modal-overlay" @click.self="showLeaveModal = false">
                <div class="isup-modal">
                    <div class="isup-modal-header">
                        <span><i class="bi-calendar-plus me-2"></i> Nouvelle demande de congé</span>
                        <button class="isup-modal-close" @click="showLeaveModal = false">&times;</button>
                    </div>
                    <form @submit.prevent="saveLeaveRequest">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="isup-label">Employé *</label>
                                    <select class="isup-select" v-model="leaveForm.employee_id" required>
                                        <option value="" disabled>Sélectionner un employé</option>
                                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.first_name }} {{ emp.last_name }} — {{ emp.department }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="isup-label">Type *</label>
                                    <select class="isup-select" v-model="leaveForm.type" required>
                                        <option value="conge">Congé</option><option value="maladie">Maladie</option><option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="isup-label">Début *</label><input type="date" class="isup-input" v-model="leaveForm.start_date" required></div>
                                <div class="col-md-3"><label class="isup-label">Fin *</label><input type="date" class="isup-input" v-model="leaveForm.end_date" required></div>
                                <div class="col-12"><label class="isup-label">Motif</label><textarea class="isup-input" rows="2" v-model="leaveForm.reason"></textarea></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showLeaveModal = false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>Ccréer la demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Note de frais -->
            <div v-if="showExpenseModal" class="isup-modal-overlay" @click.self="showExpenseModal = false">
                <div class="isup-modal">
                    <div class="isup-modal-header">
                        <span><i class="bi-receipt-cutoff me-2"></i> Nouvelle note de frais</span>
                        <button class="isup-modal-close" @click="showExpenseModal = false">&times;</button>
                    </div>
                    <form @submit.prevent="saveExpense">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="isup-label">Employé *</label>
                                    <select class="isup-select" v-model="expenseForm.employee_id" required>
                                        <option value="" disabled>Sélectionner un employé</option>
                                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.first_name }} {{ emp.last_name }} — {{ emp.department }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="isup-label">Catégorie *</label><input type="text" class="isup-input" v-model="expenseForm.category" required placeholder="Transport, repas..."></div>
                                <div class="col-md-6"><label class="isup-label">Montant (CFA) *</label><input type="number" step="0.01" min="0" class="isup-input" v-model="expenseForm.amount" required></div>
                                <div class="col-12"><label class="isup-label">Description *</label><textarea class="isup-input" rows="2" v-model="expenseForm.description" required></textarea></div>
                                <div class="col-12"><label class="isup-label">Reçu (URL)</label><input type="text" class="isup-input" v-model="expenseForm.receipt_path" placeholder="Lien du justificatif"></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showExpenseModal = false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>Créer la note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

