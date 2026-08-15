@extends('layouts.gel-app')

@section('title', 'Tableau de bord')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Tableau de Bord Général</h1>
    <div class="sec-page-sub">Gérez vos modules et accès</div>
  </div>
</div>

<div class="alert alert-info" style="margin-top: 20px;">
    <i class="fas fa-info-circle"></i> <strong>Avis de migration :</strong> Ce portail classique est déprécié. Veuillez accéder à la nouvelle version SPA via <a href="/company/dashboard">/company/dashboard</a>.
</div>

@if($activeEntrepriseId && isset($activeEntreprise))
  <!-- Dashboard Entreprise Active -->
  <div class="sec-kpi-grid" style="margin-top: 20px;">
    @foreach($activeEntreprise->modules as $module)
      <div class="sec-kpi" style="cursor:pointer;" onclick="alert('Ce module sera implémenté à l\'étape 7 !')">
        <div class="sec-kpi-icon teal"><i class="fas fa-cubes"></i></div>
        <div>
          <div class="sec-kpi-val" style="font-size: 18px;">{{ $module->nom }}</div>
          <div class="sec-kpi-label">Accéder au module</div>
        </div>
      </div>
    @endforeach
    @if($activeEntreprise->modules->isEmpty())
      <div class="alert alert-info w-100">
        <i class="fas fa-info-circle"></i> Cette entreprise n'a encore aucun module activé.
      </div>
    @endif
  </div>

  <div class="sec-card" style="margin-top: 20px;">
    <div class="sec-card-header">
      <div class="sec-card-title">Résumé de {{ $activeEntreprise->raison_sociale }}</div>
    </div>
    <div class="sec-card-body">
      <p>Bienvenue dans l'espace de votre entreprise. Utilisez le menu latéral gauche pour naviguer dans vos modules actifs.</p>
      
      <form action="{{ route('dashboard.switch-entreprise') }}" method="POST" style="margin-top: 20px;">
          @csrf
          <input type="hidden" name="entreprise_id" value=""> <!-- empty value to trigger error or we could have a specific route to clear -->
          <button type="submit" class="sec-btn sec-btn-sm sec-btn-outline" title="Changer d'entreprise en cliquant en haut à gauche">
              <i class="fas fa-exchange-alt"></i> Vous pouvez aussi changer d'entreprise via le menu du haut
          </button>
      </form>
    </div>
  </div>
@else
  <!-- Dashboard Sélection d'entreprise -->
  <div class="alert alert-warning" style="margin-top: 20px;">
      <i class="fas fa-exclamation-triangle"></i> Vous n'avez sélectionné aucune entreprise. Veuillez en choisir une dans le tableau ci-dessous.
  </div>

  <div class="sec-kpi-grid">
    <div class="sec-kpi">
      <div class="sec-kpi-icon teal"><i class="fas fa-building"></i></div>
      <div>
        <div class="sec-kpi-val">{{ $affectations->count() }}</div>
        <div class="sec-kpi-label">Entreprises associées</div>
      </div>
    </div>
  </div>

  <div class="sec-card">
    <div class="sec-card-header">
      <div class="sec-card-title">Mes Entreprises</div>
    </div>
    <div class="sec-card-body p-0">
      <table class="sec-table">
        <thead>
          <tr>
            <th>Entreprise</th>
            <th>Secteur</th>
            <th>Rôle</th>
            <th>Statut Abonnement</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($affectations as $aff)
          <tr>
            <td style="font-weight: 600;">{{ $aff->entreprise->raison_sociale }}</td>
            <td>{{ $aff->entreprise->secteur_activite ?? '-' }}</td>
            <td><span class="sec-badge sec-badge-info">{{ $aff->role->libelle ?? 'Inconnu' }}</span></td>
            <td>
              @if($aff->entreprise->statut_abonnement == 'essai')
                  <span class="sec-badge sec-badge-warning">Essai</span>
              @elseif($aff->entreprise->statut_abonnement == 'actif')
                  <span class="sec-badge sec-badge-success">Actif</span>
              @else
                  <span class="sec-badge sec-badge-danger">{{ ucfirst($aff->entreprise->statut_abonnement) }}</span>
              @endif
            </td>
            <td>
              <form action="{{ route('dashboard.switch-entreprise') }}" method="POST">
                  @csrf
                  <input type="hidden" name="entreprise_id" value="{{ $aff->entreprise->id }}">
                  <button type="submit" class="sec-btn sec-btn-sm sec-btn-secondary">
                      <i class="fas fa-sign-in-alt"></i> Accéder
                  </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif
@endsection
