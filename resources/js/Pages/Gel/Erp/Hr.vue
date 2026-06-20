<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const employees = ref([]);
const payrolls = ref([]);
const loading = ref(true);
const error = ref(null);
const activeTab = ref('employees');
const submitting = ref(false);

// Employee modal
const showEmpModal = ref(false);
const empForm = ref({
    first_name: '', last_name: '', email: '', phone: '', position: '',
    salary: '', hire_date: '', status: 'active',
});

// Payroll modal
const showPayModal = ref(false);
const payForm = ref({ employee_id: '', period: '', base_salary: '', bonuses: '', deductions: '', net_amount: '' });

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [empRes, payRes] = await Promise.all([
            fetch('/api/erp/employees'),
            fetch('/api/erp/payrolls'),
        ]);
        if (empRes.ok) employees.value = await empRes.json();
        if (payRes.ok) payrolls.value = await payRes.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const submitEmployee = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/hr/employees', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(empForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showEmpModal.value = false;
        empForm.value = { first_name: '', last_name: '', email: '', phone: '', position: '', salary: '', hire_date: '', status: 'active' };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const generatePayroll = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/hr/payrolls', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showPayModal.value = false;
        payForm.value = { employee_id: '', period: '', base_salary: '', bonuses: '', deductions: '', net_amount: '' };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const calcNet = () => {
    const base = parseFloat(payForm.value.base_salary) || 0;
    const bonus = parseFloat(payForm.value.bonuses) || 0;
    const ded = parseFloat(payForm.value.deductions) || 0;
    payForm.value.net_amount = (base + bonus - ded).toFixed(2);
};

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="RH & Paie">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <ul class="nav nav-pills">
                <li class="nav-item"><button class="nav-link" :class="{ active: activeTab === 'employees' }" @click="activeTab = 'employees'">Employés ({{ employees.length }})</button></li>
                <li class="nav-item"><button class="nav-link" :class="{ active: activeTab === 'payrolls' }" @click="activeTab = 'payrolls'">Paies</button></li>
            </ul>
            <div class="d-flex gap-2">
                <button v-if="activeTab === 'payrolls'" class="btn btn-primary btn-sm" @click="showPayModal = true"><i class="bi-cash me-1"></i>Générer paie</button>
                <button v-if="activeTab === 'employees'" class="btn btn-primary btn-sm" @click="showEmpModal = true"><i class="bi-plus-lg me-1"></i>Employé</button>
            </div>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Employees -->
        <div v-else-if="activeTab === 'employees'" class="bg-white rounded-lg shadow p-6">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted"><tr><th>Nom</th><th>Email</th><th>Poste</th><th class="text-end">Salaire</th><th>Date embauche</th><th>Statut</th></tr></thead>
                    <tbody>
                        <tr v-if="!employees.length"><td colspan="6" class="text-center py-4 text-muted">Aucun employé.</td></tr>
                        <tr v-for="emp in employees" :key="emp.id">
                            <td class="fw-medium">{{ emp.first_name }} {{ emp.last_name }}</td>
                            <td class="small">{{ emp.email }}</td>
                            <td class="small">{{ emp.position || '-' }}</td>
                            <td class="text-end">{{ emp.salary ? $formatCurrency(emp.salary) : '-' }}</td>
                            <td class="small">{{ emp.hire_date ? $formatDate(emp.hire_date) : '-' }}</td>
                            <td><span class="badge" :class="emp.status === 'active' ? 'bg-success' : 'bg-secondary'">{{ emp.status }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payrolls -->
        <div v-else class="bg-white rounded-lg shadow p-6">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted"><tr><th>Employé</th><th>Période</th><th class="text-end">Base</th><th class="text-end">Primes</th><th class="text-end">Retenues</th><th class="text-end">Net</th></tr></thead>
                    <tbody>
                        <tr v-if="!payrolls.length"><td colspan="6" class="text-center py-4 text-muted">Aucune paie générée.</td></tr>
                        <tr v-for="pay in payrolls" :key="pay.id">
                            <td class="small">{{ pay.employee?.first_name }} {{ pay.employee?.last_name }}</td>
                            <td class="small">{{ pay.period }}</td>
                            <td class="text-end">{{ $formatCurrency(pay.base_salary) }}</td>
                            <td class="text-end">{{ $formatCurrency(pay.bonuses || 0) }}</td>
                            <td class="text-end text-danger">{{ $formatCurrency(pay.deductions || 0) }}</td>
                            <td class="text-end fw-bold">{{ $formatCurrency(pay.net_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Employee Modal -->
        <div v-if="showEmpModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h6 class="fw-bold">Nouvel employé</h6><button class="btn-close" @click="showEmpModal = false"></button></div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label small">Prénom *</label><input v-model="empForm.first_name" class="form-control form-control-sm" required></div>
                            <div class="col-6"><label class="form-label small">Nom *</label><input v-model="empForm.last_name" class="form-control form-control-sm" required></div>
                            <div class="col-6"><label class="form-label small">Email</label><input v-model="empForm.email" type="email" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Téléphone</label><input v-model="empForm.phone" type="tel" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Poste</label><input v-model="empForm.position" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Salaire (FCFA)</label><input v-model="empForm.salary" type="number" step="0.01" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Date embauche</label><input v-model="empForm.hire_date" type="date" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Statut</label>
                                <select v-model="empForm.status" class="form-select form-select-sm">
                                    <option value="active">Actif</option><option value="inactive">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showEmpModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitEmployee">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Modal -->
        <div v-if="showPayModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h6 class="fw-bold">Générer une paie</h6><button class="btn-close" @click="showPayModal = false"></button></div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small">Employé</label>
                                <select v-model="payForm.employee_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.first_name }} {{ emp.last_name }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Période</label>
                                <input v-model="payForm.period" class="form-control form-control-sm" placeholder="ex: 2024-01">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Salaire de base</label>
                                <input v-model="payForm.base_salary" type="number" step="0.01" class="form-control form-control-sm" @input="calcNet">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Primes</label>
                                <input v-model="payForm.bonuses" type="number" step="0.01" class="form-control form-control-sm" @input="calcNet">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Retenues</label>
                                <input v-model="payForm.deductions" type="number" step="0.01" class="form-control form-control-sm" @input="calcNet">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Net à payer</label>
                                <input v-model="payForm.net_amount" type="number" step="0.01" class="form-control form-control-sm fw-bold" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showPayModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="generatePayroll">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Générer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
