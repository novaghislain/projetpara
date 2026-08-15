@extends('layouts.gel-legal')
@section('title', 'Tableau de bord Juridique')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Tableau de bord Juridique</h1>
    <div class="sec-page-subtitle">Gestion des actes, sociétés et contrats</div>
  </div>
</div>

<div class="sec-kpi-grid">
  <div class="sec-kpi-card">
    <div class="sec-kpi-label">Dossiers en cours</div>
    <div class="sec-kpi-value">{{ $stats['dossiers_actifs'] }}</div>
    <div class="sec-kpi-change"><i class="fas fa-folder-open me-1"></i> Créations & Modifications</div>
  </div>
  <div class="sec-kpi-card">
    <div class="sec-kpi-label">Contrats Actifs</div>
    <div class="sec-kpi-value">{{ $stats['contrats_actifs'] }}</div>
    <div class="sec-kpi-change up"><i class="fas fa-file-signature me-1"></i> Actes sous seing privé</div>
  </div>
  <div class="sec-kpi-card">
    <div class="sec-kpi-label">Assemblées Planifiées</div>
    <div class="sec-kpi-value">{{ $stats['assemblees_planifiees'] }}</div>
    <div class="sec-kpi-change warning"><i class="fas fa-users-cog me-1"></i> À venir</div>
  </div>
</div>

<div class="sec-card">
  <div class="sec-card-header">
    <h3 class="sec-chart-title m-0">Derniers Contrats Enregistrés</h3>
    <a href="{{ route('gel-legal.contracts.index') }}" class="sec-btn sec-btn-secondary sec-btn-sm">Voir tout</a>
  </div>
  <div class="sec-card-body p-0">
    <table class="sec-table">
      <thead>
        <tr>
          <th>Intitulé</th>
          <th>Type</th>
          <th>Expiration</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recent_contracts as $contract)
        <tr>
          <td>{{ $contract->intitule }}</td>
          <td>{{ ucfirst($contract->type) }}</td>
          <td>{{ $contract->date_expiration ? \Carbon\Carbon::parse($contract->date_expiration)->format('d/m/Y') : '-' }}</td>
          <td><span class="sec-badge sec-badge-success">Actif</span></td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center text-muted py-3">Aucun contrat récent.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
