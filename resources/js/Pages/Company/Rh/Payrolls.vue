<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';
import { authStore } from '../../../stores/auth';

const payrolls = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchPayrolls = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/company/rh/api/payrolls');
        if (!res.ok) throw new Error('Erreur de chargement');
        payrolls.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const statusBadgeClass = (status) => {
    const map = {
        brouillon: 'bg-secondary',
        valide: 'bg-success',
        paye: 'bg-primary',
        annule: 'bg-danger',
    };
    return map[status] || 'bg-secondary';
};

const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA';
};

const totalNetAPayer = computed(() => {
    return payrolls.value.reduce((s, p) => s + (parseFloat(p.net_a_payer) || 0), 0);
});

onMounted(fetchPayrolls);
</script>

<template>
    <CompanyLayout page-title="Mes Paies">
        <div class="mb-3">
            <span class="small text-muted">{{ payrolls.length }} fiche(s) de paie</span>
        </div>

        <!-- Summary -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total fiches</div>
                        <div class="fw-bold fs-5">{{ payrolls.length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Validées/Payées</div>
                        <div class="fw-bold fs-5 text-success">{{ payrolls.filter(p => p.statut === 'valide' || p.statut === 'paye').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Net total perçu</div>
                        <div class="fw-bold fs-6 text-primary">{{ formatCurrency(totalNetAPayer) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Période</th>
                            <th class="text-end">Salaire base</th>
                            <th class="text-end">Primes</th>
                            <th class="text-end">Retenues</th>
                            <th class="text-end">Net à payer</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!payrolls.length">
                            <td colspan="6" class="text-center py-4 text-muted">Aucune fiche de paie disponible.</td>
                        </tr>
                        <tr v-for="p in payrolls" :key="p.id">
                            <td class="fw-medium">{{ p.period || p.periode }}</td>
                            <td class="text-end">{{ formatCurrency(p.salaire_base || p.base_salary) }}</td>
                            <td class="text-end text-success">{{ formatCurrency(p.primes || p.bonuses || 0) }}</td>
                            <td class="text-end text-danger">{{ formatCurrency(p.retenues || p.deductions || 0) }}</td>
                            <td class="text-end fw-bold">{{ formatCurrency(p.net_a_payer || p.net_amount) }}</td>
                            <td><span class="badge" :class="statusBadgeClass(p.statut)">{{ p.statut || 'brouillon' }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CompanyLayout>
</template>
