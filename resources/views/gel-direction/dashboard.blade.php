@extends('layouts.gel-direction')

@section('title', 'Tableau de bord')
@section('page_title', 'Vue d\'ensemble - ' . ($entreprise->raison_sociale ?? 'Entreprise'))

@section('page_actions')
    <a href="{{ route('gel-direction.clients.index') }}" class="sec-btn sec-btn-secondary">
        <i class="fas fa-users"></i> Voir les clients
    </a>
    <a href="{{ route('gel-direction.finance.index') }}" class="sec-btn sec-btn-primary" style="margin-left: 8px;">
        <i class="fas fa-plus"></i> Nouvelle Facture
    </a>
@endsection

@section('content')

<!-- KPIs -->
<div class="sec-kpi-grid">
    <div class="sec-kpi">
        <div class="sec-kpi-icon teal">
            <i class="fas fa-building"></i>
        </div>
        <div>
            <div class="sec-kpi-val">{{ number_format($totalClients, 0, ',', ' ') }}</div>
            <div class="sec-kpi-label">Total Clients</div>
        </div>
    </div>

    <div class="sec-kpi">
        <div class="sec-kpi-icon blue">
            <i class="fas fa-chart-line"></i>
        </div>
        <div>
            <div class="sec-kpi-val">{{ number_format($chiffreAffaires, 0, ',', ' ') }}</div>
            <div class="sec-kpi-label">CA Réalisé (FCFA)</div>
        </div>
    </div>

    <div class="sec-kpi">
        <div class="sec-kpi-icon amber">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <div class="sec-kpi-val">{{ number_format($facturesEnAttente, 0, ',', ' ') }}</div>
            <div class="sec-kpi-label">Créances (FCFA)</div>
        </div>
    </div>

    <div class="sec-kpi">
        <div class="sec-kpi-icon violet">
            <i class="fas fa-users-cog"></i>
        </div>
        <div>
            <div class="sec-kpi-val">{{ number_format($totalTeam, 0, ',', ' ') }}</div>
            <div class="sec-kpi-label">Membres Équipe</div>
        </div>
    </div>

    <a href="{{ route('gel-direction.validations.index') }}" style="text-decoration: none; color: inherit;">
        <div class="sec-kpi" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="sec-kpi-icon rose">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <div class="sec-kpi-val text-danger">{{ number_format($totalValidations, 0, ',', ' ') }}</div>
                <div class="sec-kpi-label">Approbations requises</div>
            </div>
        </div>
    </a>
</div>

<h3 class="sec-page-title" style="margin-bottom: 16px; font-size:16px;">Mes Départements</h3>
<div class="sec-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 30px;">
    <a href="{{ route('gel-secretary.dashboard') }}" class="sec-kpi" style="text-decoration:none; cursor:pointer; transition:all 0.2s; align-items:flex-start;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
        <div class="sec-kpi-icon blue" style="width:48px; height:48px; font-size:22px;">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <div>
            <div class="sec-kpi-val" style="font-size:17px; margin-bottom:4px;">Secrétariat</div>
            <div class="sec-kpi-label" style="white-space:normal; line-height:1.4;">Courriers, dossiers, agenda et contacts.</div>
        </div>
    </a>

    <a href="{{ route('gel-accountant.dashboard') }}" class="sec-kpi" style="text-decoration:none; cursor:pointer; transition:all 0.2s; align-items:flex-start;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
        <div class="sec-kpi-icon amber" style="width:48px; height:48px; font-size:22px;">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div>
            <div class="sec-kpi-val" style="font-size:17px; margin-bottom:4px;">Comptabilité</div>
            <div class="sec-kpi-label" style="white-space:normal; line-height:1.4;">Saisie, facturation, états et déclarations.</div>
        </div>
    </a>

    <a href="{{ route('gel-rh.dashboard') }}" class="sec-kpi" style="text-decoration:none; cursor:pointer; transition:all 0.2s; align-items:flex-start;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
        <div class="sec-kpi-icon violet" style="width:48px; height:48px; font-size:22px;">
            <i class="fas fa-user-friends"></i>
        </div>
        <div>
            <div class="sec-kpi-val" style="font-size:17px; margin-bottom:4px;">Ressources Hum.</div>
            <div class="sec-kpi-label" style="white-space:normal; line-height:1.4;">Paie, employés, congés et contrats.</div>
        </div>
    </a>

    <a href="{{ route('gel-legal.dashboard') }}" class="sec-kpi" style="text-decoration:none; cursor:pointer; transition:all 0.2s; align-items:flex-start;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
        <div class="sec-kpi-icon rose" style="width:48px; height:48px; font-size:22px;">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div>
            <div class="sec-kpi-val" style="font-size:17px; margin-bottom:4px;">Juridique</div>
            <div class="sec-kpi-label" style="white-space:normal; line-height:1.4;">Assemblées générales, statuts et contrats.</div>
        </div>
    </a>
</div>

<div class="row">
    <!-- Dernières factures -->
    <div class="col-lg-7 mb-4">
        <div class="sec-card h-100">
            <div class="sec-card-header">
                <h3 class="sec-card-title">Dernières factures émises</h3>
                <a href="{{ route('gel-direction.finance.index') }}" class="sec-btn sec-btn-sm sec-btn-secondary">Voir tout</a>
            </div>
            <div class="sec-card-body p-0">
                <div class="table-responsive">
                    <table class="sec-table">
                        <thead>
                            <tr>
                                <th>Facture</th>
                                <th>Client</th>
                                <th>Montant TTC</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFactures as $facture)
                                <tr>
                                    <td><strong>{{ $facture->numero }}</strong></td>
                                    <td>{{ $facture->client->nom_entreprise }}</td>
                                    <td>{{ number_format($facture->montant_ttc, 0, ',', ' ') }} F</td>
                                    <td>
                                        @if($facture->statut == 'payee')
                                            <span class="sec-badge sec-badge-success">Payée</span>
                                        @elseif($facture->statut == 'envoyee')
                                            <span class="sec-badge sec-badge-info">Envoyée</span>
                                        @elseif($facture->statut == 'brouillon')
                                            <span class="sec-badge sec-badge-muted">Brouillon</span>
                                        @elseif($facture->statut == 'en_retard')
                                            <span class="sec-badge sec-badge-danger">En retard</span>
                                        @else
                                            <span class="sec-badge sec-badge-muted">{{ ucfirst($facture->statut) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                                        <br>Aucune facture récente
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Nouveaux Clients -->
    <div class="col-lg-5 mb-4">
        <div class="sec-card h-100">
            <div class="sec-card-header">
                <h3 class="sec-card-title">Nouveaux Clients</h3>
                <a href="{{ route('gel-direction.clients.index') }}" class="sec-btn sec-btn-sm sec-btn-secondary">Gérer</a>
            </div>
            <div class="sec-card-body p-0">
                <div class="table-responsive">
                    <table class="sec-table">
                        <thead>
                            <tr>
                                <th>Entreprise</th>
                                <th>Email</th>
                                <th>Ajouté le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentClients as $client)
                                <tr>
                                    <td><strong>{{ $client->nom_entreprise }}</strong></td>
                                    <td>{{ $client->email ?? '-' }}</td>
                                    <td>{{ $client->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-users mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                                        <br>Aucun client enregistré
                                    </td>
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
