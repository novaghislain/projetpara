<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

// ─── État actif des onglets ──────────────────────────────────────────────────
const activeTab = ref('dashboard');
const setTab = (tab) => { activeTab.value = tab; };

// ─── Données ─────────────────────────────────────────────────────────────────
const stats = ref(null);
const employees = ref([]);
const leaveRequests = ref([]);
const expenses = ref([]);
const loading = ref(false);
const error = ref(null);

// ─── Modales ─────────────────────────────────────────────────────────────────
const showEmployeeModal = ref(false);
const showLeaveModal = ref(false);
const showExpenseModal = ref(false);
const editingEmployee = ref(null);
const isSubmitting = ref(false);

// ─── Formulaires ─────────────────────────────────────────────────────────────
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

// ─── Recherche ───────────────────────────────────────────────────────────────
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
        active: 'bg-success', suspended: 'bg-warning text-dark', left: 'bg-secondary',
        pending: 'bg-warning text-dark', approved: 'bg-success', rejected: 'bg-danger',
    };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = {
        active: 'Actif', suspended: 'Suspendu', left: 'Quitté',
        pending: 'En attente', approved: 'Approuvé', rejected: 'Rejeté',
        conge: 'Congé', maladie: 'Maladie', autre: 'Autre',
    };
    return map[status] || status;
};

// ─── API Calls ───────────────────────────────────────────────────────────────
async function fetchStats() {
    try {
        const res = await fetch('/api/company/hr/stats');
        if (!res.ok) throw new Error('Erreur chargement statistiques');
        stats.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchEmployees() {
    try {
        const res = await fetch('/api/company/hr/employees');
        if (!res.ok) throw new Error('Erreur chargement employés');
        employees.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchLeaveRequests() {
    try {
        const res = await fetch('/api/company/hr/leave-requests');
        if (!res.ok) throw new Error('Erreur chargement congés');
        leaveRequests.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchExpenses() {
    try {
        const res = await fetch('/api/company/hr/expenses');
        if (!res.ok) throw new Error('Erreur chargement notes de frais');
        expenses.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

// ─── Actions Employés ────────────────────────────────────────────────────────
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
        const url = editingEmployee.value
            ? `/api/company/hr/employees/${editingEmployee.value}`
            : '/api/company/hr/employees';
        const method = editingEmployee.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(employeeForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showEmployeeModal.value = false;
        await fetchEmployees();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function deleteEmployee(id) {
    if (!confirm('Supprimer cet employé ?')) return;
    try {
        const res = await fetch(`/api/company/hr/employees/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchEmployees();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// ─── Actions Congés ──────────────────────────────────────────────────────────
function openCreateLeave() {
    leaveForm.value = { employee_id: '', type: 'conge', start_date: '', end_date: '', reason: '' };
    showLeaveModal.value = true;
}

async function saveLeaveRequest() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/company/hr/leave-requests', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(leaveForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showLeaveModal.value = false;
        await fetchLeaveRequests();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
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
        await fetchLeaveRequests();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// ─── Actions Notes de frais ──────────────────────────────────────────────────
function openCreateExpense() {
    expenseForm.value = { employee_id: '', category: '', amount: null, description: '', receipt_path: '' };
    showExpenseModal.value = true;
}

async function saveExpense() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/company/hr/expenses', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(expenseForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showExpenseModal.value = false;
        await fetchExpenses();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function approveExpense(id, status) {
    try {
        const res = await fetch(`/api/company/hr/expenses/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ status }),
        });
        if (!res.ok) throw new Error('Erreur mise à jour');
        await fetchExpenses();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// ─── Initialisation ──────────────────────────────────────────────────────────
onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchEmployees(), fetchLeaveRequests(), fetchExpenses()]);
    loading.value = false;
});
</script>

<template>
    <CompanyLayout page-title="Gestion RH">
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
                    <button class="nav-link" :class="{ active: activeTab === 'employees' }" @click="setTab('employees')">
                        <i class="bi-people me-1"></i> Employés
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'leaves' }" @click="setTab('leaves')">
                        <i class="bi-calendar-check me-1"></i> Congés
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'expenses' }" @click="setTab('expenses')">
                        <i class="bi-receipt me-1"></i> Notes de frais
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
                                    <i class="bi-people"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Employés</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.total_employees }}</div>
                                    <small class="text-success">{{ stats.active_employees }} actifs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Congés en cours</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.ongoing_leaves }}</div>
                                    <small class="text-warning">{{ stats.pending_leaves }} en attente</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi-receipt"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Notes de frais</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.pending_expenses }}</div>
                                    <small class="text-info">en attente</small>
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
                                    <div class="text-muted small">Frais du mois</div>
                                    <div class="h3 mb-0 fw-bold">{{ formatCurrency(stats.monthly_expenses) }}</div>
                                    <small class="text-success">CFA</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-file-earmark-text me-2"></i>Par contrat</h6>
                            <div v-if="Object.keys(stats.by_contract || {}).length">
                                <div v-for="(count, type) in stats.by_contract" :key="type" class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ type }}</span>
                                    <span class="badge bg-primary rounded-pill">{{ count }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucun employé</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-building me-2"></i>Par département</h6>
                            <div v-if="Object.keys(stats.by_department || {}).length">
                                <div v-for="(count, dept) in stats.by_department" :key="dept" class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ dept }}</span>
                                    <span class="badge bg-secondary rounded-pill">{{ count }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucun employé</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ EMPLOYÉS ══════════════════ -->
            <div v-if="activeTab === 'employees'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="max-width: 350px;">
                        <input type="text" class="form-control form-control-sm" placeholder="Rechercher un employé..."
                               v-model="searchQuery">
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateEmployee">
                        <i class="bi-plus-lg me-1"></i> Nouvel employé
                    </button>
                </div>

                <div class="card card-dashboard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Poste</th>
                                    <th>Département</th>
                                    <th>Contrat</th>
                                    <th>Salaire</th>
                                    <th>Statut</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="emp in filteredEmployees" :key="emp.id">
                                    <td class="fw-semibold">
                                        {{ emp.first_name }} {{ emp.last_name }}
                                        <div class="text-muted small">Embauché le {{ formatDate(emp.hire_date) }}</div>
                                    </td>
                                    <td>{{ emp.email }}<br><small class="text-muted">{{ emp.phone }}</small></td>
                                    <td>{{ emp.position }}</td>
                                    <td>{{ emp.department }}</td>
                                    <td><span class="badge bg-light text-dark">{{ emp.contract_type }}</span></td>
                                    <td>{{ emp.salary ? formatCurrency(emp.salary) : '-' }}</td>
                                    <td><span class="badge" :class="statusBadge(emp.status)">{{ statusLabel(emp.status) }}</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Modifier" @click="openEditEmployee(emp)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteEmployee(emp.id)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!filteredEmployees.length">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi-people fs-4 d-block mb-1"></i>
                                        Aucun employé trouvé
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ CONGÉS ══════════════════ -->
            <div v-if="activeTab === 'leaves'">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary btn-sm" @click="openCreateLeave">
                        <i class="bi-plus-lg me-1"></i> Nouvelle demande
                    </button>
                </div>

                <div class="card card-dashboard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employé</th>
                                    <th>Type</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Motif</th>
                                    <th>Statut</th>
                                    <th>Approbateur</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="lr in leaveRequests" :key="lr.id">
                                    <td class="fw-semibold">
                                        {{ lr.employee_name }}
                                        <div class="text-muted small">{{ lr.department }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ statusLabel(lr.type) }}</span></td>
                                    <td>{{ formatDate(lr.start_date) }}</td>
                                    <td>{{ formatDate(lr.end_date) }}</td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" :title="lr.reason">
                                            {{ lr.reason || '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="statusBadge(lr.status)">{{ statusLabel(lr.status) }}</span>
                                        <div v-if="lr.approver_notes" class="small text-muted mt-1">{{ lr.approver_notes }}</div>
                                    </td>
                                    <td>{{ lr.approved_by || '-' }}</td>
                                    <td class="text-center">
                                        <template v-if="lr.status === 'pending'">
                                            <button class="btn btn-sm btn-outline-success me-1" title="Approuver" @click="approveLeave(lr.id, 'approved')">
                                                <i class="bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Rejeter" @click="approveLeave(lr.id, 'rejected')">
                                                <i class="bi-x-lg"></i>
                                            </button>
                                        </template>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                </tr>
                                <tr v-if="!leaveRequests.length">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi-calendar-check fs-4 d-block mb-1"></i>
                                        Aucune demande de congé
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ NOTES DE FRAIS ══════════════════ -->
            <div v-if="activeTab === 'expenses'">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary btn-sm" @click="openCreateExpense">
                        <i class="bi-plus-lg me-1"></i> Nouvelle note de frais
                    </button>
                </div>

                <div class="card card-dashboard">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employé</th>
                                    <th>Catégorie</th>
                                    <th>Montant</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                    <th>Approbateur</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="exp in expenses" :key="exp.id">
                                    <td class="fw-semibold">
                                        {{ exp.employee_name }}
                                        <div class="text-muted small">{{ exp.department }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ exp.category }}</span></td>
                                    <td class="fw-bold">{{ formatCurrency(exp.amount) }}</td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 180px;" :title="exp.description">
                                            {{ exp.description }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="statusBadge(exp.status)">{{ statusLabel(exp.status) }}</span>
                                    </td>
                                    <td>{{ exp.approved_by || '-' }}</td>
                                    <td class="text-center">
                                        <template v-if="exp.status === 'pending'">
                                            <button class="btn btn-sm btn-outline-success me-1" title="Approuver" @click="approveExpense(exp.id, 'approved')">
                                                <i class="bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Rejeter" @click="approveExpense(exp.id, 'rejected')">
                                                <i class="bi-x-lg"></i>
                                            </button>
                                        </template>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                </tr>
                                <tr v-if="!expenses.length">
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi-receipt fs-4 d-block mb-1"></i>
                                        Aucune note de frais
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ MODAL EMPLOYÉ ══════════════════ -->
            <div class="modal fade" :class="{ show: showEmployeeModal }" :style="{ display: showEmployeeModal ? 'block' : 'none' }"
                 tabindex="-1" @click.self="showEmployeeModal = false">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi-person-badge me-1"></i>
                                {{ editingEmployee ? 'Modifier' : 'Nouvel' }} employé
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="showEmployeeModal = false"></button>
                        </div>
                        <form @submit.prevent="saveEmployee">
                            <div class="modal-body">
                                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="employeeForm.first_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="employeeForm.last_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-sm" v-model="employeeForm.email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Téléphone</label>
                                        <input type="text" class="form-control form-control-sm" v-model="employeeForm.phone">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Poste <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="employeeForm.position" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Département <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="employeeForm.department" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Date d'embauche <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" v-model="employeeForm.hire_date" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Salaire (CFA)</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model="employeeForm.salary">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Type de contrat <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="employeeForm.contract_type" required>
                                            <option value="CDI">CDI</option>
                                            <option value="CDD">CDD</option>
                                            <option value="INTERIM">INTERIM</option>
                                            <option value="STAGE">STAGE</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Statut <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="employeeForm.status" required>
                                            <option value="active">Actif</option>
                                            <option value="suspended">Suspendu</option>
                                            <option value="left">Quitté</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showEmployeeModal = false">Annuler</button>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ editingEmployee ? 'Enregistrer' : 'Créer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="showEmployeeModal" class="modal-backdrop fade show"></div>

            <!-- ══════════════════ MODAL CONGÉ ══════════════════ -->
            <div class="modal fade" :class="{ show: showLeaveModal }" :style="{ display: showLeaveModal ? 'block' : 'none' }"
                 tabindex="-1" @click.self="showLeaveModal = false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi-calendar-plus me-1"></i> Nouvelle demande de congé</h5>
                            <button type="button" class="btn-close btn-close-white" @click="showLeaveModal = false"></button>
                        </div>
                        <form @submit.prevent="saveLeaveRequest">
                            <div class="modal-body">
                                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Employé <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="leaveForm.employee_id" required>
                                            <option value="" disabled>Sélectionner un employé</option>
                                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                                {{ emp.first_name }} {{ emp.last_name }} — {{ emp.department }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="leaveForm.type" required>
                                            <option value="conge">Congé</option>
                                            <option value="maladie">Maladie</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Début <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" v-model="leaveForm.start_date" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Fin <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" v-model="leaveForm.end_date" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Motif</label>
                                        <textarea class="form-control form-control-sm" rows="3" v-model="leaveForm.reason"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showLeaveModal = false">Annuler</button>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                    Créer la demande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="showLeaveModal" class="modal-backdrop fade show"></div>

            <!-- ══════════════════ MODAL NOTE DE FRAIS ══════════════════ -->
            <div class="modal fade" :class="{ show: showExpenseModal }" :style="{ display: showExpenseModal ? 'block' : 'none' }"
                 tabindex="-1" @click.self="showExpenseModal = false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi-receipt-cutoff me-1"></i> Nouvelle note de frais</h5>
                            <button type="button" class="btn-close btn-close-white" @click="showExpenseModal = false"></button>
                        </div>
                        <form @submit.prevent="saveExpense">
                            <div class="modal-body">
                                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Employé <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="expenseForm.employee_id" required>
                                            <option value="" disabled>Sélectionner un employé</option>
                                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                                {{ emp.first_name }} {{ emp.last_name }} — {{ emp.department }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Catégorie <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="expenseForm.category" required
                                               placeholder="Transport, repas, fournitures...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Montant (CFA) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model="expenseForm.amount" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control form-control-sm" rows="3" v-model="expenseForm.description" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Reçu (URL / chemin)</label>
                                        <input type="text" class="form-control form-control-sm" v-model="expenseForm.receipt_path"
                                               placeholder="Lien ou chemin du justificatif">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showExpenseModal = false">Annuler</button>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                    Créer la note
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="showExpenseModal" class="modal-backdrop fade show"></div>
        </template>
    </CompanyLayout>
</template>
