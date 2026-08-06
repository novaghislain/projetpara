@extends('layouts.gel-informaticien')

@section('title', 'Santé Globale & KPI')

@section('content')
<div class="row g-4 mb-4">
    <!-- Tickets Ouverts -->
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-label">Tickets Non Assignés</div>
            <div class="kpi-value text-danger">{{ $openTickets }}</div>
            <div class="mt-2 text-muted small"><a href="{{ route('gel-informaticien.tickets.index', ['status' => 'nouveau']) }}">Voir la file d'attente →</a></div>
        </div>
    </div>
    
    <!-- Mes Tickets -->
    <div class="col-md-4">
        <div class="kpi-card border-start border-4 border-primary">
            <div class="kpi-label">Mes Tickets Actifs</div>
            <div class="kpi-value text-primary">{{ $myTickets }}</div>
            <div class="mt-2 text-muted small"><a href="{{ route('gel-informaticien.tickets.index') }}">Gérer mes dossiers →</a></div>
        </div>
    </div>

    <!-- Alertes Sécurité -->
    <div class="col-md-4">
        <div class="kpi-card border-start border-4 border-warning">
            <div class="kpi-label">Alertes Sécurité (24h)</div>
            <div class="kpi-value text-warning">{{ $securityAlerts }}</div>
            <div class="mt-2 text-muted small"><a href="{{ route('gel-informaticien.security.index') }}">Vérifier le journal →</a></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white pb-0 pt-3 border-0">
                <h5 class="mb-0"><i class="fas fa-server text-success me-2"></i> Santé de la Plateforme</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Latence API Moyenne</span>
                        <span class="badge bg-light text-dark">{{ $systemHealth['api_latency'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Charge CPU Serveur</span>
                        <span class="badge bg-light text-dark">{{ $systemHealth['cpu_load'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Connexions BDD Actives</span>
                        <span class="badge bg-light text-dark">{{ $systemHealth['db_connections'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Dernière Sauvegarde DB</span>
                        @if($systemHealth['last_backup'])
                            <span class="badge bg-success">{{ \Carbon\Carbon::parse($systemHealth['last_backup'])->diffForHumans() }}</span>
                        @else
                            <span class="badge bg-danger">Inconnue</span>
                        @endif
                    </li>
                </ul>
                <div class="mt-3 text-end">
                    <a href="{{ route('gel-informaticien.maintenance.index') }}" class="btn btn-sm btn-outline-secondary">Détails de maintenance</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100 bg-primary text-white">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-5">
                <i class="fas fa-shield-alt fa-4x mb-4 opacity-50"></i>
                <h5>Rappel de Sécurité</h5>
                <p class="opacity-75 mb-0">En tant qu'informaticien, vos accès sont tracés. Vous n'avez pas accès aux données des entreprises sans autorisation temporaire explicite.</p>
            </div>
        </div>
    </div>
</div>
@endsection
