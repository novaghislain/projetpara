@extends('layouts.gel-accountant')

@section('title', 'Prévisions de trésorerie (IA)')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-chart-line" style="color:var(--gel-primary); margin-right:8px;"></i>
            Prévisions de trésorerie
        </h1>
        <p class="gel-page-subtitle">Projections IA sur 90 jours basées sur l'historique et les créances/dettes.</p>
    </div>
</div>

@if(empty($cashflowData))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Aucune donnée de prévision disponible pour ce client. Assurez-vous d'avoir sélectionné un client avec un historique d'écritures bancaires.
    </div>
@else
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="gel-card p-4 text-center">
                <p style="color:var(--gel-text-secondary); margin-bottom:5px; font-weight:600;">Solde actuel estimé</p>
                <h3 style="font-size:28px; font-weight:700; color:var(--gel-text-primary); margin:0;">
                    {{ number_format($cashflowData['solde_actuel'] ?? 0, 2, ',', ' ') }} €
                </h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="gel-card p-4 text-center">
                <p style="color:var(--gel-text-secondary); margin-bottom:5px; font-weight:600;">Solde à 90 jours (Prévu)</p>
                <h3 style="font-size:28px; font-weight:700; color: {{ ($cashflowData['solde_final'] ?? 0) >= 0 ? '#16a34a' : '#e11d48' }}; margin:0;">
                    {{ number_format($cashflowData['solde_final'] ?? 0, 2, ',', ' ') }} €
                </h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="gel-card p-4 text-center">
                <p style="color:var(--gel-text-secondary); margin-bottom:5px; font-weight:600;">Jours dans le rouge</p>
                <h3 style="font-size:28px; font-weight:700; color: {{ ($cashflowData['jours_negatifs'] ?? 0) > 0 ? '#e11d48' : '#16a34a' }}; margin:0;">
                    {{ $cashflowData['jours_negatifs'] ?? 0 }}
                </h3>
            </div>
        </div>
    </div>

    <div class="gel-card p-4 mb-4">
        <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px;">Évolution projetée de la trésorerie</h3>
        <canvas id="cashflowChart" style="width: 100%; height: 350px;"></canvas>
    </div>

    <div class="gel-card p-4">
        <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px;">Analyse de l'IA</h3>
        <p style="line-height: 1.6; color: var(--gel-text-secondary);">
            {{ $cashflowData['resume'] ?? 'L\'IA analyse actuellement vos tendances de trésorerie.' }}
        </p>
    </div>

    @php
        $labels = [];
        $dataPoints = [];
        if(isset($cashflowData['historique'])) {
            foreach($cashflowData['historique'] as $point) {
                $labels[] = \Carbon\Carbon::parse($point['date'])->format('d/m');
                $dataPoints[] = $point['solde_estime'];
            }
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('cashflowChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Solde de trésorerie (€)',
                        data: {!! json_encode($dataPoints) !!},
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
@endif

@endsection