<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

// ─── ÉTAT GLOBAL ─────────────────────────────────────────────────
const activeTab = ref('dashboard');
const loading = ref(false);
const error = ref(null);
const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;

const clientId = window.__CLIENT_ID__;

// ─── EMPLOYÉS ────────────────────────────────────────────────────
const employees = ref([]);
const employeesMeta = ref(null);
const employeeSearch = ref('');
const employeeStatusFilter = ref('');
const employeeDepartmentFilter = ref('');
const departments = ref([]);
const currentEmployee = ref(null);
const showEmployeeModal = ref(false);
const editingEmployee = ref(false);
const employeeForm = ref({
    first_name: '', last_name: '', email: '', phone: '',
    position: '', department: '', hire_date: '', salary: null,
    contract_type: 'CDI', status: 'active',
});
const employeePage = ref(1);

// ─── CONGES ──────────────────────────────────────────────────────
const leaves = ref([]);
const leavesMeta = ref(null);
const leaveStatusFilter = ref('');
const leaveTypeFilter = ref('');
const showLeaveModal = ref(false);
const leaveForm = ref({
    employee_id: '', type: 'conge', start_date: '', end_date: '', reason: '',
});
const leavePage = ref(1);

// ─── NOTES DE FRAIS ──────────────────────────────────────────────
const expenses = ref([]);
const expensesMeta = ref(null);
const expenseStatusFilter = ref('');
const expenseCategoryFilter = ref('');
const showExpenseModal = ref(false);
const expenseForm = ref({
    employee_id: '', category: '', amount: null, description: '', receipt: null,
});
const expensePage = ref(1);

// ─── STATISTIQUES ────────────────────────────────────────────────
const stats = ref(null);

// ─── UTILITAIRES ─────────────────────────────────────────────────
const getHeaders = () => ({
    'X-CSRF-TOKEN': csrfToken,
    'Accept': 'application/json',
});

const formatDate = (d) => {
    if (!d) return '';
    return new Date(d).toLocaleDateString('fr-FR');
};

const formatCurrency = (n) => {
    if (n === null || n === undefined) return '';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(n);
};

const statusBadgeClass = (status) => {
    const map = {
        active: 'bg-success',
        suspended: 'bg-warning text-dark',
        left: 'bg-secondary',
        pending: 'bg-warning text-dark',
        approved: 'bg-success',
        rejected: 'bg-danger',
    };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = {
        active: 'Actif', suspended: 'Suspendu', left: 'Quitté',
        pending: 'En attente', approved: 'Approuvé', rejected: 'Rejeté',
        conge: 'Congé', maladie: 'Maladie', autre: 'Autre',
        CDI: 'CDI', CDD: 'CDD', INTERIM: 'Intérim', STAGE: 'Stage',
    };
    return map[status] || status;
};

// ─── API HELPERS ─────────────────────────────────────────────────
const apiFetch = async (url, options = {}) => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch(url, {
            headers: getHeaders(),
            ...options,
        });
        if (!res.ok) {
            const body = await res.json().catch(() => ({}));
            throw new Error(body.message || `Erreur ${res.status}`);
        }
        return await res.json();
    } catch (e) {
        error.value = e.message;
        throw e;
    } finally {
        loading.value = false;
    }
};

// ─── EMPLOYÉS ────────────────────────────────────────────────────
const loadEmployees = async () => {
    const params = new URLSearchParams();
    if (employeeSearch.value) params.set('search', employeeSearch.value);
    if (employeeStatusFilter.value) params.set('status', employeeStatusFilter.value);
    if (employeeDepartmentFilter.value) params.set('department', employeeDepartmentFilter.value);
    params.set('page', employeePage.value);

    const data = await apiFetch(`/api/company/hr/employees?${params}`);
    employees.value = data.data;
    employeesMeta.value = data;
};

const loadDepartments = async () => {
    try {
        departments.value = await apiFetch('/api/company/hr/departments');
    } catch { /* silencieux */ }
};

const openCreateEmployee = () => {
    editingEmployee.value = false;
    employeeForm.value = {
        first_name: '', last_name: '', email: '', phone: '',
        position: '', department: '', hire_date: '', salary: null,
        contract_type: 'CDI', status: 'active',
    };
    showEmployeeModal.value = true;
};

const openEditEmployee = async (emp) => {
    editingEmployee.value = true;
    const data = await apiFetch(`/api/company/hr/employees/${emp.id}`);
    currentEmployee.value = data;
    employeeForm.value = {
        first_name: data.first_name,
        last_name: data.last_name,
        email: data.email || '',
        phone: data.phone || '',
        position: data.position || '',
        department: data.department || '',
        hire_date: data.hire_date ? data.hire_date.split('T')[0] : '',
        salary: data.salary,
        contract_type: data.contract_type,
        status: data.status,
    };
    showEmployeeModal.value = true;
};

const viewEmployee = async (emp) => {
    currentEmployee.value = await apiFetch(`/api/company/hr/employees/${emp.id}`);
};

const saveEmployee = async () => {
    const formData = { ...employeeForm.value };
    if (editingEmployee.value) {
        await apiFetch(`/api/company/hr/employees/${currentEmployee.value.id}`, {
            method: 'PUT',
            headers: { ...getHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify(formData),
        });
    } else {
        await apiFetch('/api/company/hr/employees', {
            method: 'POST',
            headers: { ...getHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify(formData),
        });
    }
    showEmployeeModal.value = false;
    currentEmployee.value = null;
    await loadEmployees();
    await loadDepartments();
};

const deleteEmployee = async (emp) => {
    if (!confirm(`Supprimer ${emp.first_name} ${emp.last_name} ?`)) return;
    await apiFetch(`/api/company/hr/employees/${emp.id}`, { method: 'DELETE' });
    await loadEmployees();
};

// ─── CONGES ──────────────────────────────────────────────────────
const loadLeaves = async () => {
    const params = new URLSearchParams();
    if (leaveStatusFilter.value) params.set('status', leaveStatusFilter.value);
    if (leaveTypeFilter.value) params.set('type', leaveTypeFilter.value);
    params.set('page', leavePage.value);

    const data = await apiFetch(`/api/company/hr/leaves?${params}`);
    leaves.value = data.data;
    leavesMeta.value = data;
};

const openCreateLeave = () => {
    leaveForm.value = {
        employee_id: '', type: 'conge', start_date: '', end_date: '', reason: '',
    };
    showLeaveModal.value = true;
};

const saveLeave = async () => {
    await apiFetch('/api/company/hr/leaves', {
        method: 'POST',
        headers: { ...getHeaders(), 'Content-Type': 'application/json' },
        body: JSON.stringify(leaveForm.value),
    });
    showLeaveModal.value = false;
    await loadLeaves();
};

const approveLeave = async (id, status) => {
    await apiFetch(`/api/company/hr/leaves/${id}/approve`, {
        method: 'PUT',
        headers: { ...getHeaders(), 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
    });
    await loadLeaves();
};

// ─── NOTES DE FRAIS ──────────────────────────────────────────────
const loadExpenses = async () => {
    const params = new URLSearchParams();
    if (expenseStatusFilter.value) params.set('status', expenseStatusFilter.value);
    if (expenseCategoryFilter.value) params.set('category', expenseCategoryFilter.value);
    params.set('page', expensePage.value);

    const data = await apiFetch(`/api/company/hr/expenses?${params}`);
    expenses.value = data.data;
    expensesMeta.value = data;
};

const openCreateExpense = () => {
    expenseForm.value = {
        employee_id: '', category: '', amount: null, description: '', receipt: null,
    };
    showExpenseModal.value = true;
};

const saveExpense = async () => {
    const formData = new FormData();
    formData.append('employee_id', expenseForm.value.employee_id);
    formData.append('category', expenseForm.value.category);
    formData.append('amount', expenseForm.value.amount);
    if (expenseForm.value.description) formData.append('description', expenseForm.value.description);
    if (expenseForm.value.receipt) formData.append('receipt', expenseForm.value.receipt);

    await apiFetch('/api/company/hr/expenses', {
        method: 'POST',
        body: formData,
    });
    showExpenseModal.value = false;
    await loadExpenses();
};

const approveExpense = async (id, status) => {
    await apiFetch(`/api/company/hr/expenses/${id}/approve`, {
        method: 'PUT',
        headers: { ...getHeaders(), 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
    });
    await loadExpenses();
};

const handleReceiptUpload = (e) => {
    expenseForm.value.receipt = e.target.files[0] || null;
};

// ─── STATISTIQUES ────────────────────────────────────────────────
const loadStats = async () => {
    stats.value = await apiFetch('/api/company/hr/stats');
};

// ─── PAGINATION ──────────────────────────────────────────────────
const pageNumbers = (meta) => {
    if (!meta) return [];
    const pages = [];
    const total = meta.last_page || 1;
    const current = meta.current_page || 1;
    const start = Math.max(1, current - 2);
    const end = Math.min(total, current + 2);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
};

// ─── CHANGEMENT D'ONGLET ────────────────────────────────────────
const switchTab = (tab) => {
    activeTab.value = tab;
    error.value = null;
    loadTabData(tab);
};

const loadTabData = (tab) => {
    switch (tab) {
        case 'employees': loadEmployees(); loadDepartments(); break;
        case 'leaves': loadLeaves(); break;
        case 'expenses': loadExpenses(); break;
        case 'dashboard': loadStats(); break;
    }
};

// ─── INIT ────────────────────────────────────────────────────────
onMounted(() => {
    loadTabData(activeTab.value);
});
</script>

<template>
    <CompanyLayout page-title="Ressources Humaines">
        <div v-if="!clientId" class="alert alert-warning rounded-3">
            <i class="bi-exclamation-triangle me-2"></i>Aucune entreprise associée à votre compte.
        </div>

        <template v-else>
            <!-- Error Alert -->
            <div v-if="error" class="alert alert-danger alert-dismissible rounded-3 fade show">
                <i class="bi-exclamation-circle me-2"></i>{{ error }}
                <button type="button" class="btn-close" @click="error = null"></button>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs border-0 mb-4 gap-2" style="border-bottom: 2px solid #e9ecef;">
                <li class="nav-item" v-for="tab in [
                    { key: 'dashboard', label: 'Tableau de bord', icon: 'bi-speedometer2' },
                    { key: 'employees', label: 'Employés', icon: 'bi-people' },
                    { key: 'leaves', label: 'Congés', icon: 'bi-calendar-check' },
                    { key: 'expenses', label: 'Notes de frais', icon: 'bi-receipt' },
                ]" :key="tab.key">
                    <button class="nav-link px-3 py-2 d-flex align-items-center gap-2 rounded-top"
                            :class="activeTab === tab.key ? 'active fw-bold' : ''"
                            :style="activeTab === tab.key ? 'border-bottom: 2px solid #1a237e; color: #1a237e; background: transparent; margin-bottom: -2px; border: none; border-bottom: 2px solid #1a237e;' : 'color: #6c757d; border: none;'"
                            @click="switchTab(tab.key)">
                        <i :class="tab.icon"></i>
                        <span class="d-none d-md-inline">{{ tab.label }}</span>
                    </button>
                </li>
            </ul>

            <!-- Loading Spinner -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <!-- ════════════════════════════════════════════ -->
            <!-- DASHBOARD TAB -->
            <!-- ════════════════════════════════════════════ -->
            <template v-if="!loading && activeTab === 'dashboard' && stats">
                <div class="row g-4 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card card-dashboard h-100">
                            <div class="card-body p-3 p-md-4 text-center">
                                <div class="stat-icon mx-auto mb-2" style="background: #e3f2fd; color: #1565c0;">
                                    <i class="bi-people"></i>
                                </div>
                                <h3 class="fw-bold mb-0">{{ stats.total_employees }}</h3>
                                <small class="text-muted">Employés</small>
                                <div class="mt-1 small">
                                    <span class="text-success">{{ stats.active_employees }} actifs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card card-dashboard h-100">
                            <div class="card-body p-3 p-md-4 text-center">
                                <div class="stat-icon mx-auto mb-2" style="background: #fff3e0; color: #e65100;">
                                    <i class="bi-calendar-check"></i>
                                </div>
                                <h3 class="fw-bold mb-0">{{ stats.pending_leaves }}</h3>
                                <small class="text-muted">Congés en attente</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card card-dashboard h-100">
                            <div class="card-body p-3 p-md-4 text-center">
                                <div class="stat-icon mx-auto mb-2" style="background: #fce4ec; color: #c62828;">
                                    <i class="bi-receipt"></i>
                                </div>
                                <h3 class="fw-bold mb-0">{{ stats.pending_expenses }}</h3>
                                <small class="text-muted">Frais en attente</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card card-dashboard h-100">
                            <div class="card-body p-3 p-md-4 text-center">
                                <div class="stat-icon mx-auto mb-2" style="background: #e8f5e9; color: #2e7d32;">
                                    <i class="bi-currency-euro"></i>
                                </div>
                                <h3 class="fw-bold mb-0">{{ formatCurrency(stats.total_salary) }}</h3>
                                <small class="text-muted">Masse salariale</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0"><i class="bi-file-spreadsheet me-2 text-primary"></i>Répartition des contrats</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div v-if="!stats.contracts_breakdown?.length" class="text-muted small py-3 text-center">
                                    Aucune donnée.
                                </div>
                                <div v-else v-for="item in stats.contracts_breakdown" :key="item.contract_type"
                                     class="d-flex align-items-center justify-content-between py-2 border-bottom border-light">
                                    <span class="fw-medium">{{ statusLabel(item.contract_type) }}</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0"><i class="bi-building me-2 text-primary"></i>Répartition par département</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div v-if="!stats.departments_count?.length" class="text-muted small py-3 text-center">
                                    Aucune donnée.
                                </div>
                                <div v-else v-for="item in stats.departments_count" :key="item.department"
                                     class="d-flex align-items-center justify-content-between py-2 border-bottom border-light">
                                    <span class="fw-medium">{{ item.department }}</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ════════════════════════════════════════════ -->
            <!-- EMPLOYEES TAB -->
            <!-- ════════════════════════════════════════════ -->
            <template v-if="!loading && activeTab === 'employees'">
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <h5 class="fw-bold mb-0 font-heading">
                                <i class="bi-people me-2 text-primary"></i>Employés
                            </h5>
                            <button class="btn btn-primary rounded-pill" @click="openCreateEmployee">
                                <i class="bi-plus-lg me-1"></i>Nouvel employé
                            </button>
                        </div>

                        <!-- Filters -->
                        <div class="row g-2 mt-3">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Rechercher..."
                                           v-model="employeeSearch" @input="employeePage = 1; loadEmployees()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" v-model="employeeStatusFilter" @change="employeePage = 1; loadEmployees()">
                                    <option value="">Tous les statuts</option>
                                    <option value="active">Actif</option>
                                    <option value="suspended">Suspendu</option>
                                    <option value="left">Quitté</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" v-model="employeeDepartmentFilter" @change="employeePage = 1; loadEmployees()">
                                    <option value="">Tous les départements</option>
                                    <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div v-if="!employees.length" class="text-center py-4 text-muted">
                            <i class="bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucun employé trouvé.</p>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Poste</th>
                                        <th>Département</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="emp in employees" :key="emp.id">
                                        <td>
                                            <a href="#" class="text-decoration-none fw-medium" @click.prevent="viewEmployee(emp)"
                                               data-bs-toggle="modal" data-bs-target="#employeeDetailModal">
                                                {{ emp.first_name }} {{ emp.last_name }}
                                            </a>
                                        </td>
                                        <td class="small text-muted">{{ emp.email || '-' }}</td>
                                        <td>{{ emp.position || '-' }}</td>
                                        <td>{{ emp.department || '-' }}</td>
                                        <td><span class="badge bg-light text-dark rounded-pill">{{ statusLabel(emp.contract_type) }}</span></td>
                                        <td><span class="badge rounded-pill" :class="statusBadgeClass(emp.status)">{{ statusLabel(emp.status) }}</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" @click="openEditEmployee(emp)" title="Modifier">
                                                <i class="bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="deleteEmployee(emp)" title="Supprimer">
                                                <i class="bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="employeesMeta && employeesMeta.last_page > 1" class="d-flex justify-content-center mt-3">
                            <nav>
                                <ul class="pagination pagination-sm">
                                    <li class="page-item" :class="{ disabled: employeePage <= 1 }">
                                        <button class="page-link" @click="employeePage--; loadEmployees()">«</button>
                                    </li>
                                    <li v-for="p in pageNumbers(employeesMeta)" :key="p" class="page-item" :class="{ active: p === employeePage }">
                                        <button class="page-link" @click="employeePage = p; loadEmployees()">{{ p }}</button>
                                    </li>
                                    <li class="page-item" :class="{ disabled: employeePage >= employeesMeta.last_page }">
                                        <button class="page-link" @click="employeePage++; loadEmployees()">»</button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Employee Detail Modal -->
                <div class="modal fade" id="employeeDetailModal" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi-person-badge me-2 text-primary"></i>Fiche employé
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" v-if="currentEmployee">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="text-muted small">Nom complet</label>
                                        <p class="fw-medium">{{ currentEmployee.first_name }} {{ currentEmployee.last_name }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">Statut</label>
                                        <p><span class="badge rounded-pill" :class="statusBadgeClass(currentEmployee.status)">{{ statusLabel(currentEmployee.status) }}</span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">Type de contrat</label>
                                        <p><span class="badge bg-light text-dark rounded-pill">{{ statusLabel(currentEmployee.contract_type) }}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Email</label>
                                        <p>{{ currentEmployee.email || '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Téléphone</label>
                                        <p>{{ currentEmployee.phone || '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Poste</label>
                                        <p>{{ currentEmployee.position || '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Département</label>
                                        <p>{{ currentEmployee.department || '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Date d'embauche</label>
                                        <p>{{ formatDate(currentEmployee.hire_date) || '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Salaire</label>
                                        <p class="fw-bold">{{ formatCurrency(currentEmployee.salary) }}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center g-3">
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 bg-light">
                                            <div class="fw-bold h4 mb-0 text-primary">{{ currentEmployee.leave_requests_count || 0 }}</div>
                                            <small class="text-muted">Demandes de congé</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 bg-light">
                                            <div class="fw-bold h4 mb-0 text-primary">{{ currentEmployee.expenses_count || 0 }}</div>
                                            <small class="text-muted">Notes de frais</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Form Modal (Create/Edit) -->
                <div class="modal fade" id="employeeFormModal" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi-person-plus me-2 text-primary"></i>
                                    {{ editingEmployee ? 'Modifier' : 'Nouvel' }} employé
                                </h5>
                                <button type="button" class="btn-close" @click="showEmployeeModal = false"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="employeeForm.first_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="employeeForm.last_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Email</label>
                                        <input type="email" class="form-control" v-model="employeeForm.email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Téléphone</label>
                                        <input type="text" class="form-control" v-model="employeeForm.phone">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Poste</label>
                                        <input type="text" class="form-control" v-model="employeeForm.position">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Département</label>
                                        <input type="text" class="form-control" v-model="employeeForm.department" list="departmentList">
                                        <datalist id="departmentList">
                                            <option v-for="d in departments" :key="d" :value="d">
                                        </datalist>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-medium">Date d'embauche</label>
                                        <input type="date" class="form-control" v-model="employeeForm.hire_date">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-medium">Salaire (EUR)</label>
                                        <input type="number" step="0.01" min="0" class="form-control" v-model="employeeForm.salary">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-medium">Type de contrat <span class="text-danger">*</span></label>
                                        <select class="form-select" v-model="employeeForm.contract_type">
                                            <option value="CDI">CDI</option>
                                            <option value="CDD">CDD</option>
                                            <option value="INTERIM">Intérim</option>
                                            <option value="STAGE">Stage</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-medium">Statut <span class="text-danger">*</span></label>
                                        <select class="form-select" v-model="employeeForm.status">
                                            <option value="active">Actif</option>
                                            <option value="suspended">Suspendu</option>
                                            <option value="left">Quitté</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light rounded-pill" @click="showEmployeeModal = false">Annuler</button>
                                <button type="button" class="btn btn-primary rounded-pill" @click="saveEmployee">
                                    <i class="bi-check-lg me-1"></i>{{ editingEmployee ? 'Modifier' : 'Créer' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ════════════════════════════════════════════ -->
            <!-- LEAVES TAB -->
            <!-- ════════════════════════════════════════════ -->
            <template v-if="!loading && activeTab === 'leaves'">
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <h5 class="fw-bold mb-0 font-heading">
                                <i class="bi-calendar-check me-2 text-primary"></i>Demandes de congé
                            </h5>
                            <button class="btn btn-primary rounded-pill" @click="openCreateLeave">
                                <i class="bi-plus-lg me-1"></i>Nouvelle demande
                            </button>
                        </div>
                        <div class="row g-2 mt-3">
                            <div class="col-md-4">
                                <select class="form-select" v-model="leaveStatusFilter" @change="leavePage = 1; loadLeaves()">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending">En attente</option>
                                    <option value="approved">Approuvé</option>
                                    <option value="rejected">Rejeté</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" v-model="leaveTypeFilter" @change="leavePage = 1; loadLeaves()">
                                    <option value="">Tous les types</option>
                                    <option value="conge">Congé</option>
                                    <option value="maladie">Maladie</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div v-if="!leaves.length" class="text-center py-4 text-muted">
                            <i class="bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucune demande trouvée.</p>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employé</th>
                                        <th>Type</th>
                                        <th>Du</th>
                                        <th>Au</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="leave in leaves" :key="leave.id">
                                        <td class="fw-medium">{{ leave.employee?.first_name }} {{ leave.employee?.last_name }}</td>
                                        <td><span class="badge bg-light text-dark rounded-pill">{{ statusLabel(leave.type) }}</span></td>
                                        <td>{{ formatDate(leave.start_date) }}</td>
                                        <td>{{ formatDate(leave.end_date) }}</td>
                                        <td class="text-muted small text-truncate" style="max-width: 200px;">{{ leave.reason || '-' }}</td>
                                        <td><span class="badge rounded-pill" :class="statusBadgeClass(leave.status)">{{ statusLabel(leave.status) }}</span></td>
                                        <td class="text-end">
                                            <template v-if="leave.status === 'pending'">
                                                <button class="btn btn-sm btn-outline-success me-1" @click="approveLeave(leave.id, 'approved')" title="Approuver">
                                                    <i class="bi-check-lg"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" @click="approveLeave(leave.id, 'rejected')" title="Rejeter">
                                                    <i class="bi-x-lg"></i>
                                                </button>
                                            </template>
                                            <span v-else class="text-muted small">—</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="leavesMeta && leavesMeta.last_page > 1" class="d-flex justify-content-center mt-3">
                            <nav>
                                <ul class="pagination pagination-sm">
                                    <li class="page-item" :class="{ disabled: leavePage <= 1 }">
                                        <button class="page-link" @click="leavePage--; loadLeaves()">«</button>
                                    </li>
                                    <li v-for="p in pageNumbers(leavesMeta)" :key="p" class="page-item" :class="{ active: p === leavePage }">
                                        <button class="page-link" @click="leavePage = p; loadLeaves()">{{ p }}</button>
                                    </li>
                                    <li class="page-item" :class="{ disabled: leavePage >= leavesMeta.last_page }">
                                        <button class="page-link" @click="leavePage++; loadLeaves()">»</button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Leave Form Modal -->
                <div class="modal fade" id="leaveFormModal" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold"><i class="bi-calendar-plus me-2 text-primary"></i>Nouvelle demande de congé</h5>
                                <button type="button" class="btn-close" @click="showLeaveModal = false"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Employé <span class="text-danger">*</span></label>
                                    <select class="form-select" v-model="leaveForm.employee_id">
                                        <option value="">Sélectionner...</option>
                                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                            {{ emp.first_name }} {{ emp.last_name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Type <span class="text-danger">*</span></label>
                                        <select class="form-select" v-model="leaveForm.type">
                                            <option value="conge">Congé</option>
                                            <option value="maladie">Maladie</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Statut</label>
                                        <input type="text" class="form-control" value="En attente" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Date de début <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" v-model="leaveForm.start_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Date de fin <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" v-model="leaveForm.end_date" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Motif</label>
                                    <textarea class="form-control" rows="3" v-model="leaveForm.reason"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light rounded-pill" @click="showLeaveModal = false">Annuler</button>
                                <button type="button" class="btn btn-primary rounded-pill" @click="saveLeave">
                                    <i class="bi-send me-1"></i>Soumettre
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ════════════════════════════════════════════ -->
            <!-- EXPENSES TAB -->
            <!-- ════════════════════════════════════════════ -->
            <template v-if="!loading && activeTab === 'expenses'">
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <h5 class="fw-bold mb-0 font-heading">
                                <i class="bi-receipt me-2 text-primary"></i>Notes de frais
                            </h5>
                            <button class="btn btn-primary rounded-pill" @click="openCreateExpense">
                                <i class="bi-plus-lg me-1"></i>Nouvelle note de frais
                            </button>
                        </div>
                        <div class="row g-2 mt-3">
                            <div class="col-md-4">
                                <select class="form-select" v-model="expenseStatusFilter" @change="expensePage = 1; loadExpenses()">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending">En attente</option>
                                    <option value="approved">Approuvé</option>
                                    <option value="rejected">Rejeté</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Catégorie..."
                                       v-model="expenseCategoryFilter" @input="expensePage = 1; loadExpenses()">
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div v-if="!expenses.length" class="text-center py-4 text-muted">
                            <i class="bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucune note de frais trouvée.</p>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employé</th>
                                        <th>Catégorie</th>
                                        <th>Montant</th>
                                        <th>Description</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="exp in expenses" :key="exp.id">
                                        <td class="fw-medium">{{ exp.employee?.first_name }} {{ exp.employee?.last_name }}</td>
                                        <td><span class="badge bg-light text-dark rounded-pill">{{ exp.category }}</span></td>
                                        <td class="fw-bold">{{ formatCurrency(exp.amount) }}</td>
                                        <td class="text-muted small text-truncate" style="max-width: 200px;">{{ exp.description || '-' }}</td>
                                        <td><span class="badge rounded-pill" :class="statusBadgeClass(exp.status)">{{ statusLabel(exp.status) }}</span></td>
                                        <td class="text-end">
                                            <template v-if="exp.status === 'pending'">
                                                <button class="btn btn-sm btn-outline-success me-1" @click="approveExpense(exp.id, 'approved')" title="Approuver">
                                                    <i class="bi-check-lg"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" @click="approveExpense(exp.id, 'rejected')" title="Rejeter">
                                                    <i class="bi-x-lg"></i>
                                                </button>
                                            </template>
                                            <span v-else class="text-muted small">—</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="expensesMeta && expensesMeta.last_page > 1" class="d-flex justify-content-center mt-3">
                            <nav>
                                <ul class="pagination pagination-sm">
                                    <li class="page-item" :class="{ disabled: expensePage <= 1 }">
                                        <button class="page-link" @click="expensePage--; loadExpenses()">«</button>
                                    </li>
                                    <li v-for="p in pageNumbers(expensesMeta)" :key="p" class="page-item" :class="{ active: p === expensePage }">
                                        <button class="page-link" @click="expensePage = p; loadExpenses()">{{ p }}</button>
                                    </li>
                                    <li class="page-item" :class="{ disabled: expensePage >= expensesMeta.last_page }">
                                        <button class="page-link" @click="expensePage++; loadExpenses()">»</button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Expense Form Modal -->
                <div class="modal fade" id="expenseFormModal" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold"><i class="bi-plus-circle me-2 text-primary"></i>Nouvelle note de frais</h5>
                                <button type="button" class="btn-close" @click="showExpenseModal = false"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Employé <span class="text-danger">*</span></label>
                                    <select class="form-select" v-model="expenseForm.employee_id">
                                        <option value="">Sélectionner...</option>
                                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                            {{ emp.first_name }} {{ emp.last_name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Catégorie <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="expenseForm.category"
                                               placeholder="Ex: Transport, Repas...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Montant (EUR) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control" v-model="expenseForm.amount">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Description</label>
                                    <textarea class="form-control" rows="3" v-model="expenseForm.description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Reçu (optionnel)</label>
                                    <input type="file" class="form-control" @change="handleReceiptUpload" accept="image/*,.pdf">
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light rounded-pill" @click="showExpenseModal = false">Annuler</button>
                                <button type="button" class="btn btn-primary rounded-pill" @click="saveExpense">
                                    <i class="bi-send me-1"></i>Soumettre
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </CompanyLayout>
</template>
