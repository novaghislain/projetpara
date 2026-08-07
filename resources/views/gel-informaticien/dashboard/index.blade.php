@extends('layouts.gel-informaticien')

@section('title', 'Tableau de bord - Informatique')

@section('content')
<style>
    /* VARIABLES & THEME (Sober & Professional) */
    :root {
        --dash-bg: #f8f9fa;
        --card-bg: #ffffff;
        --border-color: #e9ecef;
        
        --brand-primary: #0a2540;   /* GEL Navy */
        --brand-accent: #635bff;    /* GEL Purple/Blue Accent */
        --brand-secondary: #00d4aa; /* GEL Teal/Cyan */
        
        --text-main: #212529;
        --text-muted: #6c757d;
        
        --stat-blue: #0d6efd;
        --stat-purple: #6f42c1;
        --stat-teal: #20c997;
        --stat-red: #dc3545;
    }

    .pro-dashboard {
        background: var(--dash-bg);
        min-height: calc(100vh - 64px);
    }

    /* CLEAN CARDS */
    .clean-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: box-shadow 0.2s ease;
        height: 100%;
        overflow: hidden;
    }
    .clean-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    /* HEADER BANNER */
    .dash-header {
        background: white;
        border-bottom: 1px solid var(--border-color);
        padding: 24px 32px;
        margin: -1.5rem -1.5rem 2rem -1.5rem;
    }

    /* STATS WIDGETS */
    .stat-box {
        padding: 24px;
        display: flex;
        align-items: center;
    }
    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 16px;
        flex-shrink: 0;
    }
    .stat-info h3 {
        margin: 0 0 4px 0;
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
    }
    .stat-info p {
        margin: 0;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* SPECIFIC STAT COLORS */
    .bg-light-blue { background: rgba(13, 110, 253, 0.1); color: var(--stat-blue); }
    .bg-light-purple { background: rgba(111, 66, 193, 0.1); color: var(--stat-purple); }
    .bg-light-teal { background: rgba(32, 201, 151, 0.1); color: var(--stat-teal); }
    .bg-light-red { background: rgba(220, 53, 69, 0.1); color: var(--stat-red); }

    /* ACTION BUTTON */
    .btn-brand {
        background: var(--brand-primary);
        color: white;
        border: none;
        padding: 8px 20px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 6px;
        transition: background 0.2s;
    }
    .btn-brand:hover {
        background: var(--brand-accent);
        color: white;
    }

    /* TABLES */
    .table-health th {
        font-size: 12px;
        text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 2px solid var(--border-color);
        padding: 12px 24px;
    }
    .table-health td {
        padding: 16px 24px;
        vertical-align: middle;
        color: var(--text-main);
        border-bottom: 1px solid var(--border-color);
    }
    .table-health tr:last-child td { border-bottom: none; }
</style>

<div class="pro-dashboard p-4">
    
    <!-- HEADER -->
    <div class="dash-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="fs-4 fw-bold mb-1" style="color: var(--brand-primary);">Tableau de Bord IT</h1>
            <p class="mb-0 text-muted" style="font-size: 14px;">Bonjour {{ Auth::user()->prenom ?? Auth::user()->name ?? 'Informaticien' }}, voici un aperçu de l'infrastructure.</p>
        </div>
        
        <div class="dropdown">
            <button class="btn-brand dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus me-2"></i> Nouvelle Action
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border: 1px solid var(--border-color) !important; border-radius: 8px;">
                <li><a class="dropdown-item py-2" href="{{ route('gel-informaticien.tickets.create') }}"><i class="fas fa-ticket-alt text-muted me-2 w-15px"></i> Nouveau Ticket</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('gel-informaticien.dev-requests.create') }}"><i class="fas fa-code text-muted me-2 w-15px"></i> Demande de Dev</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="{{ route('gel-informaticien.maintenance.index') }}"><i class="fas fa-server text-muted me-2 w-15px"></i> Alerte Maintenance</a></li>
            </ul>
        </div>
    </div>

    <!-- METRICS ROW -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="clean-card">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-light-blue"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-info">
                        <h3>{{ $openTickets }}</h3>
                        <p>Tickets Ouverts</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="clean-card">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-light-purple"><i class="fas fa-user-check"></i></div>
                    <div class="stat-info">
                        <h3>{{ $myTickets }}</h3>
                        <p>Assignés à moi</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="clean-card">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-light-red"><i class="fas fa-shield-alt"></i></div>
                    <div class="stat-info">
                        <h3>{{ $securityAlerts }}</h3>
                        <p>Alertes Sécurité</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="clean-card">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-light-teal"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3>{{ $avgResolutionTime }}h</h3>
                        <p>Temps de Résolution</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DETAILS ROW -->
    <div class="row g-4">
        <!-- SYSTEM HEALTH -->
        <div class="col-lg-8">
            <div class="clean-card d-flex flex-column">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" style="font-size: 16px;"><i class="fas fa-server text-muted me-2"></i> Santé de la Plateforme</h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Opérationnel</span>
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-borderless table-health mb-0">
                        <tbody>
                            <tr>
                                <td style="width: 40px; padding-right: 0;"><i class="fas fa-network-wired text-muted"></i></td>
                                <td><span class="fw-bold">Latence API Moyenne</span><br><small class="text-muted">Temps de réponse du serveur</small></td>
                                <td class="text-end fw-bold">{{ $systemHealth['api_latency'] }}</td>
                            </tr>
                            <tr>
                                <td style="width: 40px; padding-right: 0;"><i class="fas fa-microchip text-muted"></i></td>
                                <td><span class="fw-bold">Charge CPU Principale</span><br><small class="text-muted">Utilisation des ressources</small></td>
                                <td class="text-end fw-bold">{{ $systemHealth['cpu_load'] }}</td>
                            </tr>
                            <tr>
                                <td style="width: 40px; padding-right: 0;"><i class="fas fa-database text-muted"></i></td>
                                <td><span class="fw-bold">Connexions BDD Actives</span><br><small class="text-muted">Requêtes simultanées</small></td>
                                <td class="text-end fw-bold">{{ $systemHealth['db_connections'] }}</td>
                            </tr>
                            <tr>
                                <td style="width: 40px; padding-right: 0;"><i class="fas fa-save text-muted"></i></td>
                                <td><span class="fw-bold">Dernière Sauvegarde DB</span><br><small class="text-muted">Générée automatiquement</small></td>
                                <td class="text-end">
                                    @if($systemHealth['last_backup'])
                                        <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($systemHealth['last_backup'])->diffForHumans() }}</span>
                                    @else
                                        <span class="badge bg-danger">Inconnue</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECURITY REMINDER -->
        <div class="col-lg-4">
            <div class="clean-card" style="background-color: var(--brand-primary); color: white;">
                <div class="p-4 h-100 d-flex flex-column">
                    <div class="mb-4">
                        <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.1); border-radius: 8px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-lock text-white fs-5"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-3">Rappel de Confidentialité</h5>
                    <p style="color: rgba(255,255,255,0.7); font-size: 14px; line-height: 1.6; flex-grow:1;">
                        Vos accès administrateur technique sont strictement tracés via le journal d'Audit.<br><br>
                        Vous ne pouvez intervenir sur le périmètre métier d'une entreprise que si une mission IT vous a été formellement affectée.
                    </p>
                    <div class="mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                        <a href="{{ route('gel-informaticien.missions.index') }}" class="text-white text-decoration-none fw-bold" style="font-size: 13px;">
                            Voir mes missions <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
