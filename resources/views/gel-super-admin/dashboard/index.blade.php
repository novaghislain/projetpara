@extends('layouts.gel-super-admin')

@section('title', 'Vue d\'ensemble')

@section('content')
<h1 class="page-title">Supervision Globale</h1>
<p class="page-subtitle">Bienvenue sur le centre de contrôle de la plateforme GEL SABINET.</p>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 p-4" style="border-top: 4px solid var(--gel-primary);">
            <div class="text-uppercase mb-2" style="font-size: 0.8rem; color: var(--gel-text-muted); font-weight: 600; letter-spacing: 1px;">
                Revenu Mensuel (MRR)
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">{{ number_format($mrr, 0, ',', ' ') }} <small class="fs-6 fw-normal">FCFA</small></h2>
                <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center; color: var(--gel-primary);">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="mt-3 {{ $mrrVariation >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.85rem; font-weight: 500;">
                <i class="fas fa-arrow-{{ $mrrVariation >= 0 ? 'up' : 'down' }} me-1"></i> {{ $mrrVariation > 0 ? '+' : '' }}{{ $mrrVariation }}% ce mois
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 p-4" style="border-top: 4px solid #10B981;">
            <div class="text-uppercase mb-2" style="font-size: 0.8rem; color: var(--gel-text-muted); font-weight: 600; letter-spacing: 1px;">
                Cabinets Actifs
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">{{ $activeEntreprises }} <small class="fs-6 fw-normal text-muted">/ {{ $totalEntreprises }}</small></h2>
                <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10B981;">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <div class="mt-3 text-success" style="font-size: 0.85rem; font-weight: 500;">
                <i class="fas fa-arrow-up me-1"></i> +{{ $newEntreprisesThisMonth }} nouveau(x)
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 p-4" style="border-top: 4px solid var(--gel-accent);">
            <div class="text-uppercase mb-2" style="font-size: 0.8rem; color: var(--gel-text-muted); font-weight: 600; letter-spacing: 1px;">
                Contacts Inscrits (Espace Client)
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">{{ $totalContacts }}</h2>
                <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: var(--gel-accent);">
                    <i class="fas fa-address-book"></i>
                </div>
            </div>
            <div class="mt-3 text-success" style="font-size: 0.85rem; font-weight: 500;">
                <i class="fas fa-arrow-up me-1"></i> +{{ $newContactsThisMonth }} nouveau(x)
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 p-4" style="border-top: 4px solid #3B82F6;">
            <div class="text-uppercase mb-2" style="font-size: 0.8rem; color: var(--gel-text-muted); font-weight: 600; letter-spacing: 1px;">
                Utilisateurs Totaux
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">{{ $totalUsers }}</h2>
                <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #3B82F6;">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-3 text-muted" style="font-size: 0.75rem;">
                Cab: {{ $usersByType['entreprise'] + $usersByType['cabinet'] }} | Contacts: {{ $usersByType['client'] }} | Adm: {{ $usersByType['super_admin'] }}
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4">Évolution de la plateforme (6 derniers mois)</h5>
            <div style="height: 300px; position: relative;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 p-4 h-100">
            <h5 class="fw-bold mb-4">Système</h5>
            
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $systemStatus['database']['status'] == 'Opérationnel' ? '#10B981' : '#F59E0B' }};"></div>
                <div>
                    <div class="fw-bold">API Base de Données</div>
                    <div style="font-size: 0.8rem; color: var(--gel-text-muted);">{{ $systemStatus['database']['status'] }} ({{ $systemStatus['database']['latency'] }})</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $systemStatus['mailing'] == 'Opérationnel' ? '#10B981' : '#F59E0B' }};"></div>
                <div>
                    <div class="fw-bold">Service Mailing</div>
                    <div style="font-size: 0.8rem; color: var(--gel-text-muted);">{{ $systemStatus['mailing'] }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $systemStatus['anthropic'] == 'Opérationnel' ? '#10B981' : '#EF4444' }};"></div>
                <div>
                    <div class="fw-bold">API Anthropic (IA)</div>
                    <div style="font-size: 0.8rem; color: var(--gel-text-muted);">{{ $systemStatus['anthropic'] }}</div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $systemStatus['momo'] == 'Opérationnel' ? '#10B981' : '#F59E0B' }};"></div>
                <div>
                    <div class="fw-bold">Mobile Money (MTN/Moov)</div>
                    <div style="font-size: 0.8rem; color: var(--gel-text-muted);">{{ $systemStatus['momo'] }}</div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $systemStatus['disk_space']['status'] == 'OK' ? '#10B981' : '#3B82F6' }};"></div>
                <div>
                    <div class="fw-bold">Espace Disque Serveur</div>
                    <div style="font-size: 0.8rem; color: var(--gel-text-muted);">Libre : {{ $systemStatus['disk_space']['free'] }}</div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 h-100 p-4">
            <h5 class="fw-bold mb-4">Activité Récente de la Plateforme</h5>
            <div class="table-responsive">
                <table class="table table table-hover align-middle table-borderless">
                    <thead>
                        <tr>
                            <th>Horodatage</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Entité</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $activity)
                        <tr>
                            <td class="text-muted" style="font-size: 0.85rem;">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($activity->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-dark" style="width:24px; height:24px; font-size:0.7rem;">
                                            {{ substr($activity->user->name, 0, 1) }}
                                        </div>
                                        <span>{{ $activity->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Système</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary opacity-75">{{ $activity->event ?? 'ACTION' }}</span>
                            </td>
                            <td>
                                {{ class_basename($activity->auditable_type ?? 'Système') }}
                            </td>
                            <td class="text-muted" style="font-size: 0.85rem;">
                                {{ Str::limit($activity->description ?? '-', 50) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucune activité récente enregistrée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('growthChart').getContext('2d');
        const chartData = @json($chartData);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'MRR (FCFA)',
                        data: chartData.mrr,
                        borderColor: '#8B5CF6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Cabinets Actifs',
                        data: chartData.entreprises,
                        borderColor: '#10B981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#94A3B8'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: '#e3e8ee'
                        },
                        ticks: {
                            color: '#697386'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: '#e3e8ee'
                        },
                        ticks: {
                            color: '#697386'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            color: '#94A3B8',
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
