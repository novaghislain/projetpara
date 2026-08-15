<template>
    <GelLayout pageTitle="Tableau de bord Analytique">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Analytique & Reporting</h2>
                    <p class="text-muted mb-0">Vision consolidée 360° : Finances, Ventes et Ressources Humaines</p>
                </div>
                <div>
                    <select class="form-select form-select-sm d-inline-block w-auto me-2">
                        <option value="2026">Année 2026</option>
                        <option value="2025">Année 2025</option>
                    </select>
                    <button class="btn btn-outline-primary btn-sm me-2">
                        <i class="bi bi-download me-1"></i> Exporter PDF
                    </button>
                </div>
            </div>

            <!-- KPI Transversaux -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.1em; opacity: 0.8;">Chiffre d'Affaires</h6>
                                    <h3 class="mb-0 fw-bold tabular-nums">45.2M FCFA</h3>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-graph-up-arrow fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3 small">
                                <span class="text-success bg-white bg-opacity-25 px-2 py-1 rounded me-2"><i class="bi bi-arrow-up"></i> +12%</span> vs N-1
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-gradient-success text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.1em; opacity: 0.8;">Masse Salariale</h6>
                                    <h3 class="mb-0 fw-bold tabular-nums">12.5M FCFA</h3>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-people fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3 small">
                                <span class="text-white bg-white bg-opacity-25 px-2 py-1 rounded me-2"><i class="bi bi-dash"></i> +2%</span> vs N-1
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-gradient-warning text-dark" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.1em; opacity: 0.8;">Créances Clients</h6>
                                    <h3 class="mb-0 fw-bold tabular-nums">8.4M FCFA</h3>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-exclamation-triangle fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3 small">
                                <span class="text-danger bg-white bg-opacity-25 px-2 py-1 rounded me-2"><i class="bi bi-arrow-up"></i> +18%</span> Attention
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-gradient-info text-white" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.1em; opacity: 0.8;">Trésorerie Nette</h6>
                                    <h3 class="mb-0 fw-bold tabular-nums">24.1M FCFA</h3>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-wallet2 fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3 small">
                                <span class="text-success bg-white bg-opacity-25 px-2 py-1 rounded me-2"><i class="bi bi-check-circle"></i> Sain</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Evolution CA / Dépenses (Chart) -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-gray-800">Évolution Trésorerie (Recettes vs Dépenses)</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="evolutionChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Répartition des charges -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800">Répartition des Charges</h6>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <canvas id="chargesChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Focus CRM & Ventes -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800">Top 5 Clients (CA Généré)</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center p-3" v-for="client in topClients" :key="client.id">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded text-primary p-2 me-3 fw-bold">{{ client.initials }}</div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ client.name }}</h6>
                                            <small class="text-muted">{{ client.sector }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill tabular-nums fs-6">{{ formatCurrency(client.ca) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800">Pipeline Commercial (Opportunités)</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-gray-700">Prospection (Nouveau)</span>
                                    <span class="tabular-nums fw-bold">12.5M</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 40%"></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-gray-700">Qualification & Devis</span>
                                    <span class="tabular-nums fw-bold">8.2M</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 25%"></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-gray-700">Négociation</span>
                                    <span class="tabular-nums fw-bold">15.0M</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 50%"></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-gray-700">Gagné (Fermé)</span>
                                    <span class="tabular-nums fw-bold">45.2M</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import Chart from 'chart.js/auto';

const topClients = ref([
    { id: 1, name: 'Ministère du Plan', initials: 'MP', sector: 'Secteur Public', ca: 15200000 },
    { id: 2, name: 'Tech Soluce SARL', initials: 'TS', sector: 'Informatique', ca: 8500000 },
    { id: 3, name: 'Groupe BTP Alpha', initials: 'GB', sector: 'Construction', ca: 6200000 },
    { id: 4, name: 'Clinique de la Grâce', initials: 'CG', sector: 'Santé', ca: 4100000 },
    { id: 5, name: 'Super U Cotonou', initials: 'SU', sector: 'Grande Distribution', ca: 3800000 },
]);

const formatCurrency = (value) => {
    if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + 'M FCFA';
    }
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value).replace('XOF', 'FCFA');
};

onMounted(() => {
    // Évolution CA / Dépenses (Line/Bar Chart)
    const ctxEvol = document.getElementById('evolutionChart').getContext('2d');
    new Chart(ctxEvol, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: [
                {
                    label: 'Recettes',
                    data: [4.2, 3.8, 5.1, 4.9, 6.2, 5.8, 7.1, 8.5, 0, 0, 0, 0],
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderRadius: 4
                },
                {
                    label: 'Dépenses',
                    data: [2.1, 2.5, 2.3, 3.1, 2.8, 2.9, 3.5, 3.0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(231, 74, 59, 0.8)',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return value + 'M'; } }
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) { return context.dataset.label + ': ' + context.parsed.y + 'M FCFA'; }
                    }
                }
            }
        }
    });

    // Répartition des charges (Doughnut Chart)
    const ctxCharges = document.getElementById('chargesChart').getContext('2d');
    new Chart(ctxCharges, {
        type: 'doughnut',
        data: {
            labels: ['Masse Salariale', 'Achats', 'Impôts & Taxes', 'Frais Généraux'],
            datasets: [{
                data: [45, 25, 15, 15],
                backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#36b9cc'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

<style scoped>
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
.list-group-item {
    transition: background-color 0.2s;
}
.list-group-item:hover {
    background-color: #f8f9fc;
}
</style>
