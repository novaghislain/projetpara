@extends('layouts.gel-direction')

@section('title', 'Finance & Pilotage')
@section('page_title', 'Rapports Financiers')

@section('content')
<div class="sec-page-header mb-4">
    <p class="text-muted mb-0">Analysez vos indicateurs financiers et le suivi des créances.</p>
</div>

<div class="row g-4 mb-4">
    <!-- CA Annuel -->
    <div class="col-md-4">
        <div class="sec-card h-100" style="background: linear-gradient(135deg, var(--sec-primary) 0%, #3B82F6 100%); color: white;">
            <div class="sec-card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-white-50 mb-0 text-uppercase fw-bold">Chiffre d'Affaires Annuel</h6>
                    <div class="icon-shape bg-white bg-opacity-25 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-chart-line fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-1">{{ number_format($metrics['ca_annuel'], 0, ',', ' ') }} FCFA</h2>
                <span class="badge bg-white bg-opacity-25 text-white"><i class="fas fa-arrow-up me-1"></i> {{ $metrics['croissance_ca'] }} vs N-1</span>
            </div>
        </div>
    </div>

    <!-- CA Mensuel -->
    <div class="col-md-4">
        <div class="sec-card h-100">
            <div class="sec-card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted mb-0 text-uppercase fw-bold">CA du Mois ({{ date('M') }})</h6>
                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-coins fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-1 text-dark">{{ number_format($metrics['ca_mensuel'], 0, ',', ' ') }} FCFA</h2>
                <span class="text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i> Objectif atteint</span>
            </div>
        </div>
    </div>

    <!-- Créances -->
    <div class="col-md-4">
        <div class="sec-card h-100">
            <div class="sec-card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted mb-0 text-uppercase fw-bold">Créances Clients</h6>
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-file-invoice-dollar fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-1 text-dark">{{ number_format($metrics['creances'], 0, ',', ' ') }} FCFA</h2>
                <a href="#" class="text-primary small text-decoration-none fw-semibold">Lancer les relances <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart -->
    <div class="col-md-8">
        <div class="sec-card h-100">
            <div class="sec-card-header pt-4 pb-0 px-4" style="border:none;">
                <h5 class="fw-bold mb-0 text-dark">Évolution des Revenus</h5>
            </div>
            <div class="sec-card-body p-4">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Répartition -->
    <div class="col-md-4">
        <div class="sec-card h-100">
            <div class="sec-card-header pt-4 pb-0 px-4" style="border:none;">
                <h5 class="fw-bold mb-0 text-dark">Répartition du CA</h5>
            </div>
            <div class="sec-card-body p-4 d-flex flex-column justify-content-center align-items-center">
                <div class="position-relative" style="width: 180px; height: 180px;">
                    <!-- Placeholder Chart (Using conic-gradient for demo) -->
                    <div class="w-100 h-100 rounded-circle" style="background: conic-gradient(var(--sec-primary) 0% 60%, #10B981 60% 85%, #F59E0B 85% 100%);"></div>
                    <div class="position-absolute top-50 start-50 translate-middle bg-white rounded-circle" style="width: 120px; height: 120px;"></div>
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <span class="fs-4 fw-bold text-dark">100%</span>
                    </div>
                </div>
                <div class="w-100 mt-4">
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted"><i class="fas fa-circle text-primary me-2"></i>Tenue Comptable</span><span class="fw-bold">60%</span></div>
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted"><i class="fas fa-circle text-success me-2"></i>Conseil Juridique</span><span class="fw-bold">25%</span></div>
                    <div class="d-flex justify-content-between small"><span class="text-muted"><i class="fas fa-circle text-warning me-2"></i>Gestion Sociale</span><span class="fw-bold">15%</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Gradient
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(30, 58, 138, 0.2)');   
        gradient.addColorStop(1, 'rgba(30, 58, 138, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Revenus (FCFA)',
                    data: {!! json_encode($evolutionMensuelle) !!},
                    borderColor: '#1E3A8A',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#1E3A8A',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#E2E8F0', drawBorder: false },
                        ticks: {
                            callback: function(value) {
                                return value / 1000000 + 'M';
                            }
                        }
                    },
                    x: {
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    });
</script>
@endsection
