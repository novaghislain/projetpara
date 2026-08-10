@extends('layouts.gel-direction')

@section('title', 'Tableau de bord — Direction')

@section('page_title', 'Vue d\'ensemble du Cabinet')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:var(--dir-primary); margin:0;">Tableau de Bord Exécutif</h1>
        <p style="color:var(--dir-text-muted); font-size:14px; margin:4px 0 0 0;">Analyse globale de l'activité, des finances et des équipes.</p>
    </div>
    <div>
        <button class="btn btn-primary" style="background:var(--dir-primary); border:none; padding:8px 16px; font-weight:600; border-radius:8px;">
            <i class="fas fa-download"></i> Rapport Mensuel
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:30px;">
    <!-- MRR -->
    <div style="background:white; padding:20px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:12px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Revenus Récurrents (MRR)</span>
            <div style="width:32px; height:32px; border-radius:8px; background:#EFF6FF; color:#3B82F6; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-euro-sign"></i>
            </div>
        </div>
        <div style="font-size:28px; font-weight:700; color:var(--dir-text);">
            {{ number_format($mrr, 0, ',', ' ') }} €
        </div>
        <div style="font-size:12px; font-weight:600; color:#10B981; display:flex; align-items:center; gap:4px;">
            <i class="fas fa-arrow-up"></i> +{{ $croissance }}% vs mois dernier
        </div>
    </div>

    <!-- Nouveaux Clients -->
    <div style="background:white; padding:20px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:12px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Nouveaux Clients</span>
            <div style="width:32px; height:32px; border-radius:8px; background:#ECFDF5; color:#10B981; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-handshake"></i>
            </div>
        </div>
        <div style="font-size:28px; font-weight:700; color:var(--dir-text);">
            +{{ $nouveauxClientsMois }}
        </div>
        <div style="font-size:12px; font-weight:500; color:var(--dir-text-muted);">
            Total actifs : <strong>{{ $totalClients }}</strong> clients
        </div>
    </div>

    <!-- Productivité -->
    <div style="background:white; padding:20px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:12px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Tâches Terminées</span>
            <div style="width:32px; height:32px; border-radius:8px; background:#F5F3FF; color:#8B5CF6; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-check-double"></i>
            </div>
        </div>
        <div style="font-size:28px; font-weight:700; color:var(--dir-text);">
            {{ $completedTasksCount }}
        </div>
        <div style="font-size:12px; font-weight:500; color:var(--dir-text-muted);">
            Sur l'ensemble des équipes du cabinet.
        </div>
    </div>

    <!-- Validations -->
    <div style="background:white; padding:20px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:12px; position:relative; overflow:hidden;">
        @if($validationsCount > 0)
        <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#EF4444;"></div>
        @endif
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">À Valider</span>
            <div style="width:32px; height:32px; border-radius:8px; background:#FEF2F2; color:#EF4444; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-file-signature"></i>
            </div>
        </div>
        <div style="font-size:28px; font-weight:700; color:{{ $validationsCount > 0 ? '#EF4444' : 'var(--dir-text)' }};">
            {{ $validationsCount }}
        </div>
        <div style="font-size:12px; font-weight:500; color:var(--dir-text-muted);">
            @if($validationsCount > 0)
                <a href="{{ route('gel-direction.validations.index') }}" style="color:#EF4444; font-weight:600; text-decoration:none;">Action requise <i class="fas fa-arrow-right"></i></a>
            @else
                Aucune validation en attente.
            @endif
        </div>
    </div>
</div>

<!-- Charts Section -->
<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:30px;">
    
    <!-- Graphique Financier -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin-bottom:20px;">Évolution Financière (6 derniers mois)</h3>
        <canvas id="financeChart" height="100"></canvas>
    </div>

    <!-- Activité Récente ou Répartition -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin-bottom:20px;">Répartition des Revenus</h3>
        <canvas id="revenuePieChart" height="200"></canvas>
    </div>

</div>

<!-- Scripts pour Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Line Chart (Évolution Financière)
    const ctx = document.getElementById('financeChart').getContext('2d');
    
    // Data from Controller
    const labels = {!! json_encode($chartData['labels']) !!};
    const revenus = {!! json_encode($chartData['revenus']) !!};
    const depenses = {!! json_encode($chartData['depenses']) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenus (€)',
                    data: revenus,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Dépenses (€)',
                    data: depenses,
                    borderColor: '#EF4444',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', align: 'end' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // 2. Doughnut Chart (Répartition)
    const ctxPie = document.getElementById('revenuePieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Comptabilité', 'Secrétariat', 'Digitalisation', 'Conseil'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: [
                    '#3B82F6', // Blue
                    '#10B981', // Green
                    '#8B5CF6', // Purple
                    '#F59E0B'  // Yellow
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '75%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
@endsection
