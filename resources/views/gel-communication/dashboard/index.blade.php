@extends('layouts.gel-communication')

@section('title', 'Tableau de bord Marketing')

@section('content')
<div class="p-4">
    <div class="sec-page-header">
        <div>
            <h1 class="sec-page-title">Tableau de bord Communication</h1>
            <div class="sec-page-sub">Vue d'ensemble de vos campagnes marketing et statistiques</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('gel-communication.campaigns.index') }}" class="sec-btn sec-btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Campagne
            </a>
        </div>
    </div>

    <div class="sec-kpi-grid">
        <div class="sec-kpi">
            <div class="sec-kpi-icon violet"><i class="fas fa-bullhorn"></i></div>
            <div>
                <div class="sec-kpi-val">{{ $activeCampaignsCount }}</div>
                <div class="sec-kpi-label">Campagnes Actives</div>
            </div>
        </div>
        <div class="sec-kpi">
            <div class="sec-kpi-icon amber"><i class="fas fa-envelope-open-text"></i></div>
            <div>
                <div class="sec-kpi-val">{{ $pendingBriefsCount }}</div>
                <div class="sec-kpi-label">Briefs en attente</div>
            </div>
        </div>
        <div class="sec-kpi">
            <div class="sec-kpi-icon teal"><i class="fas fa-paint-brush"></i></div>
            <div>
                <div class="sec-kpi-val">{{ $pendingContentsCount }}</div>
                <div class="sec-kpi-label">Visuels à valider</div>
            </div>
        </div>
        <div class="sec-kpi">
            <div class="sec-kpi-icon blue"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="sec-kpi-val">{{ number_format($totalSpend, 0, ',', ' ') }} F</div>
                <div class="sec-kpi-label">Dépense Média (Actives)</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Briefs Récents -->
        <div class="col-lg-6">
            <div class="sec-card">
                <div class="sec-card-header">
                    <div class="sec-card-title">Briefs Récents</div>
                    <a href="{{ route('gel-communication.briefs.index') }}" style="font-size:12px; color:var(--sec-primary);">Voir tout</a>
                </div>
                <div class="sec-card-body p-0">
                    <table class="sec-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Budget Est.</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBriefs as $brief)
                                <tr>
                                    <td style="font-weight:600;">{{ $brief->client->company_name ?? 'Inconnu' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $brief->type)) }}</td>
                                    <td>{{ $brief->budget_estimation ? number_format($brief->budget_estimation, 0, ',', ' ') . ' F' : '-' }}</td>
                                    <td>
                                        <span class="sec-badge sec-badge-warning">En attente</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aucun brief en attente</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Campagnes en cours -->
        <div class="col-lg-6">
            <div class="sec-card">
                <div class="sec-card-header">
                    <div class="sec-card-title">Campagnes Actives</div>
                    <a href="{{ route('gel-communication.campaigns.index') }}" style="font-size:12px; color:var(--sec-primary);">Voir tout</a>
                </div>
                <div class="sec-card-body p-0">
                    <table class="sec-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Début</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeCampaigns as $campaign)
                                <tr>
                                    <td style="font-weight:600;">{{ $campaign->client->company_name ?? 'Inconnu' }}</td>
                                    <td>{{ $campaign->name }}</td>
                                    <td><span class="sec-badge sec-badge-muted">{{ ucfirst(str_replace('_', ' ', $campaign->type)) }}</span></td>
                                    <td>{{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aucune campagne active</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
