@extends('layouts.gel-rh')
@section('title', 'Tableau de bord RH')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Tableau de bord RH</h1>
    <div class="sec-page-subtitle">Vue d'ensemble de l'activité du personnel et de la paie</div>
  </div>
</div>

<div class="sec-kpi-grid">
  <div class="sec-kpi-card">
    <div class="sec-kpi-label">Effectif Total Géré</div>
    <div class="sec-kpi-value">{{ $stats['total_salaries'] }}</div>
    <div class="sec-kpi-change up"><i class="fas fa-users me-1"></i> Salariés actifs</div>
  </div>
  <div class="sec-kpi-card">
    <div class="sec-kpi-label">Congés en attente</div>
    <div class="sec-kpi-value">{{ $stats['leaves_pending'] }}</div>
    <div class="sec-kpi-change warning"><i class="fas fa-clock me-1"></i> À valider</div>
  </div>
  <div class="sec-kpi-card">
    <div class="sec-kpi-label">Masse Salariale Cumulée</div>
    <div class="sec-kpi-value">{{ number_format($stats['total_payroll'], 0, ',', ' ') }} F</div>
    <div class="sec-kpi-change"><i class="fas fa-money-bill-wave me-1"></i> Sur les paies générées</div>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-8">
    <div class="sec-card">
      <div class="sec-card-header">
        <h3 class="sec-chart-title m-0">Dernières demandes de congés</h3>
        <a href="{{ route('gel-rh.leaves.index') }}" class="sec-btn sec-btn-secondary sec-btn-sm">Voir tout</a>
      </div>
      <div class="sec-card-body p-0">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Salarié</th>
              <th>Type</th>
              <th>Du</th>
              <th>Au</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recent_leaves as $leave)
            <tr>
              <td>{{ $leave->nom }} {{ $leave->prenom }}</td>
              <td>{{ ucfirst($leave->type_conge) }}</td>
              <td>{{ \Carbon\Carbon::parse($leave->date_debut)->format('d/m/Y') }}</td>
              <td>{{ \Carbon\Carbon::parse($leave->date_fin)->format('d/m/Y') }}</td>
              <td>
                @if($leave->statut == 'en_attente')
                  <span class="sec-badge sec-badge-warning">En attente</span>
                @elseif($leave->statut == 'approuve')
                  <span class="sec-badge sec-badge-success">Approuvé</span>
                @else
                  <span class="sec-badge sec-badge-danger" style="background:#FEF2F2; color:var(--gel-danger)">Rejeté</span>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-3">Aucune demande récente.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="sec-card">
      <div class="sec-card-header">
        <h3 class="sec-chart-title m-0">Actions Rapides</h3>
      </div>
      <div class="sec-card-body">
        <form action="{{ route('gel-rh.payroll.generate') }}" method="POST">
          @csrf
          <div class="sec-form-group">
            <label>Générer la paie du mois</label>
            <input type="month" name="periode" class="sec-form-control" value="{{ date('Y-m') }}" required>
          </div>
          <button type="submit" class="sec-btn sec-btn-primary w-100">
            <i class="fas fa-file-invoice-dollar me-2"></i> Lancer la génération
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
