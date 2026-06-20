<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';
import { authStore } from '../../../stores/auth';

const stats = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchStats = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/company/rh/api/stats');
        if (!res.ok) throw new Error('Erreur de chargement');
        stats.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const statCards = computed(() => [
    { icon: 'bi-people', label: 'Employés', value: stats.value?.total_employees || 0, color: 'primary' },
    { icon: 'bi-person-check', label: 'Actifs', value: stats.value?.active_employees || 0, color: 'success' },
    { icon: 'bi-calendar-check', label: 'Congés en attente', value: stats.value?.pending_leaves || 0, color: 'warning' },
    { icon: 'bi-cash-stack', label: 'Paies du mois', value: stats.value?.monthly_payrolls || 0, color: 'info' },
    { icon: 'bi-file-text', label: 'Notes de frais', value: stats.value?.pending_expenses || 0, color: 'danger' },
    { icon: 'bi-clock', label: 'Présents aujourd\'hui', value: stats.value?.present_today || 0, color: 'success' },
]);

const quickLinks = [
    { label: 'Mes employés', href: '/company/rh/employees', icon: 'bi-people' },
    { label: 'Mes congés', href: '/company/rh/leaves', icon: 'bi-calendar-check' },
    { label: 'Notes de frais', href: '/company/rh/expenses', icon: 'bi-file-text' },
    { label: 'Mes paies', href: '/company/rh/payrolls', icon: 'bi-cash-stack' },
    { label: 'Formations', href: '/company/rh/trainings', icon: 'bi-book' },
];

onMounted(fetchStats);
</script>

<template>
    <CompanyLayout page-title="RH">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else>
            <!-- Stats Cards -->
            <div class="row g-2 mb-4">
                <div v-for="(card, idx) in statCards" :key="idx" class="col-6 col-md-4 col-lg-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center py-3 px-3">
                            <div :class="'rounded-2 p-2 me-2 bg-' + card.color + '-subtle flex-shrink-0'" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                <i :class="'bi ' + card.icon + ' fs-5 text-' + card.color"></i>
                            </div>
                            <div class="min-width-0 overflow-hidden">
                                <div class="text-muted small lh-1 mb-1 text-truncate">{{ card.label }}</div>
                                <div class="fw-bold fs-5 lh-1">{{ card.value }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card card-dashboard">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="bi-link-45deg me-2"></i>Accès rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div v-for="link in quickLinks" :key="link.href" class="col-6 col-md-4 col-lg-3">
                            <a :href="link.href" class="text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded-3 bg-light-hover">
                                    <i :class="'bi ' + link.icon + ' fs-4 me-3 text-primary'"></i>
                                    <span class="fw-medium small">{{ link.label }}</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>
