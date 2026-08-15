@extends('layouts.gel-accountant')

@section('title', 'Tableau de Bord Comptable')

@section('content')
<div class="gel-page-header">
  <div>
    <h1 class="gel-page-title">Tableau de Bord</h1>
    <div class="gel-page-subtitle">
        @if($activeClient)
            Vue d'ensemble pour le dossier : <strong>{{ $activeClient->nom_entreprise }}</strong>
        @else
            Vue globale du cabinet
        @endif
    </div>
  </div>
  <div>
    <div class="dropdown d-inline-block">
        <button class="gel-btn gel-btn-secondary dropdown-toggle" type="button" id="clientSelectDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-folder-open text-primary me-2"></i> 
            {{ $activeClient ? $activeClient->nom_entreprise : 'Sélectionner un dossier' }}
        </button>
        <ul class="dropdown-menu" aria-labelledby="clientSelectDropdown" style="max-height: 300px; overflow-y: auto;">
            <li><h6 class="dropdown-header">Dossiers Récents</h6></li>
            @forelse($allClients as $client)
                <li><a class="dropdown-item" href="{{ route('gel-accountant.client.select', ['clientId' => $client->id]) }}">{{ $client->raison_sociale ?: $client->nom_entreprise }}</a></li>
            @empty
                <li><span class="dropdown-item text-muted">Aucun client</span></li>
            @endforelse
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-primary" href="{{ route('gel-accountant.clients') }}"><i class="fas fa-users me-2"></i> Voir tous les dossiers</a></li>
        </ul>
    </div>
  </div>
</div>

<!-- KPIs -->
<div class="gel-kpi-grid">
  <div class="gel-kpi-card">
    <div class="gel-kpi-label">Trésorerie Actuelle</div>
    <div class="gel-kpi-value text-primary">{{ number_format($kpis['tresorerie'], 0, ',', ' ') }} F</div>
    <div class="gel-kpi-change up"><i class="fas fa-arrow-up"></i> +2.4% vs mois dernier</div>
  </div>
  
  <div class="gel-kpi-card">
    <div class="gel-kpi-label">TVA Estimée (à décaisser)</div>
    <div class="gel-kpi-value text-warning">{{ number_format($kpis['tva_estimee'], 0, ',', ' ') }} F</div>
    <div class="gel-kpi-change"><span class="text-muted">Échéance : 15/{{ date('m/Y', strtotime('+1 month')) }}</span></div>
  </div>
  
  <div class="gel-kpi-card">
    <div class="gel-kpi-label">Factures non lettrées</div>
    <div class="gel-kpi-value text-danger">{{ $kpis['factures_non_lettrees'] }}</div>
    <div class="gel-kpi-change down"><i class="fas fa-exclamation-circle"></i> Action requise</div>
  </div>

  <div class="gel-kpi-card">
    <div class="gel-kpi-label">Documents en attente (GED)</div>
    <div class="gel-kpi-value text-info">{{ $kpis['docs_attente'] }}</div>
    <div class="gel-kpi-change"><span class="text-muted">À comptabiliser</span></div>
  </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="gel-card mb-4">
            <div class="gel-card-header">
                <div class="font-weight-bold" style="font-size: 15px;">Dernières écritures saisies</div>
                <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="btn btn-sm text-primary p-0">Voir tout</a>
            </div>
            <div class="gel-card-body p-0">
                <table class="gel-table">
                    <thead style="background: #F9FAFB;">
                        <tr>
                            <th>Date</th>
                            <th>Journal</th>
                            <th>Pièce</th>
                            <th>Libellé</th>
                            <th class="text-end">Débit</th>
                            <th class="text-end">Crédit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kpis['dernieres_ecritures'] as $ecriture)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') }}</td>
                            <td>{{ $ecriture->journal_code ?? '—' }}</td>
                            <td>{{ $ecriture->numero_piece ?? '—' }}</td>
                            <td>{{ Str::limit($ecriture->libelle, 50) }}</td>
                            <td class="text-end">{{ $ecriture->total_debit > 0 ? number_format($ecriture->total_debit, 0, ',', ' ') : '—' }}</td>
                            <td class="text-end">{{ $ecriture->total_credit > 0 ? number_format($ecriture->total_credit, 0, ',', ' ') : '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                Aucune écriture saisie pour le moment.<br>
                                <a href="{{ route('gel-accountant.comptabilite.ecritures.create') }}" class="text-primary">Saisir la première écriture</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="gel-card">
            <div class="gel-card-header">
                <div class="font-weight-bold" style="font-size: 15px;">Tâches & Alertes</div>
            </div>
            <div class="gel-card-body">
                <div class="gel-alertes">
                    @if($kpis['factures_non_lettrees'] > 0)
                    <div class="gel-alerte-item alerte-jaune">
                        <i class="fas fa-file-alt"></i>
                        <div>
                            <strong>Écritures en brouillon</strong><br>
                            <span class="text-muted">{{ $kpis['factures_non_lettrees'] }} écriture(s) à valider</span>
                        </div>
                    </div>
                    @endif
                    @if($allClients->isEmpty())
                    <div class="gel-alerte-item alerte-bleue">
                        <i class="fas fa-users"></i>
                        <div>
                            <strong>Aucun dossier client</strong><br>
                            <a href="{{ route('gel-accountant.clients') }}" class="text-primary">Créer votre premier dossier</a>
                        </div>
                    </div>
                    @else
                    <div class="gel-alerte-item alerte-verte">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>{{ $allClients->count() }} dossier(s) actif(s)</strong><br>
                            <span class="text-muted">Cabinet opérationnel</span>
                        </div>
                    </div>
                    @endif
                    @if($kpis['tresorerie'] == 0 && $allClients->isNotEmpty())
                    <div class="gel-alerte-item alerte-rouge">
                        <i class="fas fa-university"></i>
                        <div>
                            <strong>Comptes bancaires</strong><br>
                            <span class="text-muted">Aucun compte bancaire configuré</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

