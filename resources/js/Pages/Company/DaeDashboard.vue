<template>
    <CompanyLayout>
        <div class="container-fluid py-4">
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
            <template v-else>
                <h4 class="mb-4"><i class="bi bi-file-text me-2"></i>Secrétariat DAE</h4>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-lg-3" v-for="card in statCards" :key="card.label">
                        <DaeStatCard :icon="card.icon" :label="card.label" :value="card.value" :color="card.color" />
                    </div>
                </div>

                <!-- Quick links -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent fw-semibold"><i class="bi bi-link-45deg me-2"></i>Accès rapide</div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6" v-for="link in quickLinks" :key="link.label">
                                        <a :href="link.href" class="btn btn-outline-secondary w-100 text-start">
                                            <i :class="`bi ${link.icon} me-2`"></i>{{ link.label }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent fw-semibold"><i class="bi bi-activity me-2"></i>Activité récente</div>
                            <div class="card-body">
                                <DaeActivityFeed :activities="activite" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </CompanyLayout>
</template>

<script>
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import DaeStatCard from '../../Components/Dae/DaeStatCard.vue';
import DaeActivityFeed from '../../Components/Dae/DaeActivityFeed.vue';
import axios from 'axios';

export default {
    components: { CompanyLayout, DaeStatCard, DaeActivityFeed },
    data() {
        return {
            loading: true,
            stats: {},
            activite: [],
            statCards: [],
        };
    },
    computed: {
        quickLinks() {
            return [
                { label: 'Courriers', icon: 'bi-envelope', href: '/company/dae/courriers' },
                { label: 'Documents', icon: 'bi-file-earmark', href: '/company/dae/documents' },
                { label: 'Contrats',   icon: 'bi-file-text', href: '/company/dae/contrats' },
                { label: 'Tâches',    icon: 'bi-check-list', href: '/company/dae/taches' },
            ];
        },
    },
    created() {
        this.fetchStats();
    },
    methods: {
        fetchStats() {
            axios.get('/company/dae/api/stats').then(res => {
                this.stats = res.data.stats || {};
                this.activite = res.data.activite || [];
                this.buildCards();
            }).catch(() => {
                this.loading = false;
            });
        },
        buildCards() {
            const s = this.stats;
            this.statCards = [
                { icon: 'bi-envelope', label: 'Courriers', value: s.courriers ?? '—', color: 'primary' },
                { icon: 'bi-exclamation-triangle', label: 'Urgents', value: s.courriers_urgents ?? '—', color: 'danger' },
                { icon: 'bi-file-earmark', label: 'Documents', value: s.documents ?? '—', color: 'info' },
                { icon: 'bi-file-text', label: 'Contrats', value: s.contrats ?? '—', color: 'success' },
                { icon: 'bi-check-circle', label: 'Contrats actifs', value: s.contrats_actifs ?? '—', color: 'primary' },
                { icon: 'bi-list-task', label: 'Tâches en cours', value: s.taches ?? '—', color: 'warning' },
                { icon: 'bi-check-all', label: 'Tâches terminées', value: s.taches_terminees ?? '—', color: 'success' },
                { icon: 'bi-calendar-event', label: 'Événements', value: s.evenements ?? '—', color: 'secondary' },
            ];
            this.loading = false;
        },
    },
};
</script>
