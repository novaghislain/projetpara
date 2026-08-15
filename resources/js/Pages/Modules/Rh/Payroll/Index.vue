<template>
  <div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-gray-800">
                <i class="bi bi-receipt text-success me-2"></i>Paie
            </h2>
            <p class="text-muted mb-0">Gestion des fiches de paie et salaires</p>
        </div>
        <div>
            <button class="btn btn-outline-success me-2"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Export Comptable</button>
            <button class="btn btn-success"><i class="bi bi-magic me-2"></i>Générer les paies du mois</button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Rechercher un employé..." v-model="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filterYear">
                        <option value="">Année (Toutes)</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filterMonth">
                        <option value="">Mois (Tous)</option>
                        <option value="1">Janvier</option>
                        <option value="2">Février</option>
                        <option value="3">Mars</option>
                        <option value="4">Avril</option>
                        <option value="5">Mai</option>
                        <option value="6">Juin</option>
                        <option value="7">Juillet</option>
                        <option value="8">Août</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filterStatus">
                        <option value="">Tous les statuts</option>
                        <option value="Brouillon">Brouillon</option>
                        <option value="Validé">Validé</option>
                        <option value="Payé">Payé</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- FilterChips (Data Density P0 V2.2) -->
    <FilterChips :filters="activeFilters" @remove="removeFilter" @clearAll="clearAllFilters" />

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dense align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4">Employé</th>
                            <th class="border-0">Période</th>
                            <th class="border-0 text-end">Salaire Brut</th>
                            <th class="border-0 text-end">Retenues (ITS/CNSS)</th>
                            <th class="border-0 text-end fw-bold">Net à Payer</th>
                            <th class="border-0 text-center">Statut</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!payslips || !payslips.data || payslips.data.length === 0">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet2 display-4 d-block mb-3 opacity-25"></i>
                                Aucune fiche de paie trouvée.
                            </td>
                        </tr>
                        <tr v-for="payslip in payslips?.data" :key="payslip.id">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary-subtle text-primary me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; border-radius: 50%;">
                                        {{ getInitials(payslip.employee?.prenom, payslip.employee?.nom) }}
                                    </div>
                                    <div class="fw-bold">{{ payslip.employee?.prenom }} {{ payslip.employee?.nom }}</div>
                                </div>
                            </td>
                            <td>
                                <div>{{ getMonthName(payslip.month) }} {{ payslip.year }}</div>
                            </td>
                            <td class="text-end text-muted tabular-nums">{{ formatCurrency(payslip.gross_salary) }}</td>
                            <td class="text-end text-danger tabular-nums">- {{ formatCurrency(payslip.its_amount) }}</td>
                            <td class="text-end fw-bold text-success tabular-nums">{{ formatCurrency(payslip.net_salary) }}</td>
                            <td class="text-center">
                                <span class="badge" :class="getStatusClass(payslip.status)">
                                    {{ payslip.status || 'Brouillon' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-primary me-1" title="Voir la fiche">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-success" title="Télécharger PDF">
                                    <i class="bi bi-download"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import FilterChips from '../../../../Components/FilterChips.vue';

const props = defineProps({
    payslips: {
        type: Object,
        default: () => ({ data: [] })
    }
});

const search = ref('');
const filterYear = ref('');
const filterMonth = ref('');
const filterStatus = ref('');

const activeFilters = computed(() => {
    const filters = [];
    if (search.value) filters.push({ key: 'search', label: 'Employé', value: search.value });
    if (filterYear.value) filters.push({ key: 'year', label: 'Année', value: filterYear.value });
    if (filterMonth.value) filters.push({ key: 'month', label: 'Mois', value: getMonthName(filterMonth.value) });
    if (filterStatus.value) filters.push({ key: 'status', label: 'Statut', value: filterStatus.value });
    return filters;
});

const removeFilter = (key) => {
    if (key === 'search') search.value = '';
    if (key === 'year') filterYear.value = '';
    if (key === 'month') filterMonth.value = '';
    if (key === 'status') filterStatus.value = '';
};

const clearAllFilters = () => {
    search.value = '';
    filterYear.value = '';
    filterMonth.value = '';
    filterStatus.value = '';
};

const getInitials = (prenom, nom) => {
    let initials = '';
    if (prenom) initials += prenom.charAt(0);
    if (nom) initials += nom.charAt(0);
    return initials.toUpperCase() || 'EMP';
};

const getMonthName = (monthNum) => {
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    return months[monthNum - 1] || 'Inconnu';
};

const formatCurrency = (amount) => {
    if (!amount) return '0 FCFA';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(amount);
};

const getStatusClass = (status) => {
    switch(status?.toLowerCase()) {
        case 'payé': return 'bg-success-subtle text-success border border-success-subtle';
        case 'validé': return 'bg-primary-subtle text-primary border border-primary-subtle';
        case 'brouillon':
        default: return 'bg-secondary-subtle text-secondary border border-secondary-subtle';
    }
};
</script>

<style scoped>
.avatar-circle {
    font-size: 1rem;
}
.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
}
.bg-success-subtle {
    background-color: #d1e7dd !important;
}
.bg-primary-subtle {
    background-color: #cce5ff !important;
}
.bg-secondary-subtle {
    background-color: #e2e3e5 !important;
}
</style>