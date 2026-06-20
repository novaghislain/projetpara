<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const payrolls = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');
const statusFilter = ref('');
const monthFilter = ref('');

const fetchPayrolls = async () => {
    loading.value = true;
    error.value = null;
    try {
        const params = new URLSearchParams();
        if (monthFilter.value) params.append('month', monthFilter.value);
        const url = '/rh/payrolls' + (params.toString() ? '?' + params.toString() : '');
        const res = await fetch(url);
        if (!res.ok) throw new Error('Erreur de chargement');
        payrolls.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredPayrolls = computed(() => {
    let list = payrolls.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(p =>
            (p.employe_nom && p.employe_nom.toLowerCase().includes(q)) ||
            (p.period && p.period.toLowerCase().includes(q))
        );
    }
    if (statusFilter.value) {
        list = list.filter(p => p.statut === statusFilter.value);
    }
    return list;
});

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
    <GelLayout page-title="Paies">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:260px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher employé...">
                </div>
                <input v-model="monthFilter" type="month" class="form-control form-control-sm" style="width:160px;" @change="fetchPayrolls">
                <select v-model="statusFilter" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="brouillon">Brouillon</option>
                    <option value="valide">Validé</option>
                    <option value="paye">Payé</option>
                    <option value="annule">Annulé</option>
                </select>
            </div>
            <a href="/rh/payrolls/create" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouvelle paie
            </a>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Summary -->
        <div v-else class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total paies</div>
                        <div class="fw-bold fs-5">{{ payrolls.length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Validées/Payées</div>
                        <div class="fw-bold fs-5 text-success">{{ payrolls.filter(p => p.statut === 'valide' || p.statut === 'paye').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Brouillons</div>
                        <div class="fw-bold fs-5 text-secondary">{{ payrolls.filter(p => p.statut === 'brouillon').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total net à payer</div>
                        <div class="fw-bold fs-6 text-primary">{{ formatCurrency(totalNetAPayer) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div v-if="!loading && !error" class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Employé</th>
                            <th>Période</th>
                            <th class="text-end">Salaire base</th>
                            <th class="text-end">Primes</th>
                            <th class="text-end">Retenues</th>
                            <th class="text-end">Net à payer</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredPayrolls.length">
                            <td colspan="8" class="text-center py-4 text-muted">Aucune paie trouvée.</td>
                        </tr>
                        <tr v-for="p in filteredPayrolls" :key="p.id">
                            <td class="fw-medium">{{ p.employe_nom || p.employe?.nom }} {{ p.employe_prenom || p.employe?.prenom }}</td>
                            <td class="small">{{ p.period || p.periode }}</td>
                            <td class="text-end">{{ formatCurrency(p.salaire_base || p.base_salary) }}</td>
                            <td class="text-end text-success">{{ formatCurrency(p.primes || p.bonuses || 0) }}</td>
                            <td class="text-end text-danger">{{ formatCurrency(p.retenues || p.deductions || 0) }}</td>
                            <td class="text-end fw-bold">{{ formatCurrency(p.net_a_payer || p.net_amount) }}</td>
                            <td><span class="badge" :class="statusBadgeClass(p.statut)">{{ p.statut || 'brouillon' }}</span></td>
                            <td class="text-end">
                                <a v-if="p.id" :href="'/rh/payrolls/' + p.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi-pencil"></i></a>
                                <a v-if="p.id" :href="'/rh/payrolls/' + p.id + '/print'" class="btn btn-sm btn-outline-info me-1" title="Imprimer"><i class="bi-printer"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                <span>{{ filteredPayrolls.length }} paie(s) sur {{ payrolls.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
