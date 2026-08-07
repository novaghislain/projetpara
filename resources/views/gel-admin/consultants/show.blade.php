@extends('layouts.gel-admin')
@section('content')

<div class="d-flex align-items-start justify-content-between mb-4">
  <div>
    <a href="{{ route('gel-admin.consultants.index') }}" class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1 mb-2">
      <i class="fas fa-arrow-left"></i> Retour aux consultants
    </a>
    <h1 class="h4 fw-bold mb-0">{{ $mission->title }}</h1>
    <p class="text-muted small mt-1">Spécialité : <strong>{{ $mission->specialty }}</strong></p>
  </div>
  <div class="d-flex gap-2">
    @if(!$mission->isExpired() && $mission->status !== 'cloture')
      <!-- Renouveler -->
      <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#renewModal">
        <i class="fas fa-calendar-plus me-1"></i> Prolonger l'accès
      </button>
    @endif
    @if($mission->status !== 'cloture')
      <!-- Révoquer -->
      <form action="{{ route('gel-admin.consultants.revoke', $mission->id) }}" method="POST"
            onsubmit="return confirm('Êtes-vous sûr de vouloir révoquer immédiatement l\'accès du consultant ? Cette action est irréversible.');">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-danger">
          <i class="fas fa-ban me-1"></i> Révoquer l'accès
        </button>
      </form>
    @endif
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
@endif

<div class="row g-4">
  <!-- Gauche : Infos mission -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent border-bottom">
        <h6 class="fw-bold mb-0"><i class="fas fa-clipboard-list text-primary me-2"></i>Périmètre Confié</h6>
      </div>
      <div class="card-body">
        <p class="mb-3">{{ $mission->description }}</p>
        <div class="row g-3">
          <div class="col-6">
            <div class="text-muted small">Consultant</div>
            <div class="fw-bold">{{ $mission->consultant?->name ?? '⏳ En attente d\'acceptation' }}</div>
            @if($mission->consultant)
              <div class="text-muted small">{{ $mission->consultant->email }}</div>
            @endif
          </div>
          <div class="col-6">
            <div class="text-muted small">Accès jusqu'au</div>
            <div class="fw-bold {{ $mission->isExpired() ? 'text-danger' : 'text-success' }}">
              {{ $mission->end_date->format('d/m/Y') }}
              @if($mission->isExpired()) <span class="badge bg-danger ms-1">Expiré</span> @endif
            </div>
          </div>
          <div class="col-6">
            <div class="text-muted small">Statut</div>
            @if($mission->status === 'en_attente') <span class="badge bg-secondary">En attente</span>
            @elseif($mission->status === 'en_cours') <span class="badge bg-success">En cours</span>
            @elseif($mission->status === 'livre') <span class="badge bg-info">Livrable déposé</span>
            @elseif($mission->status === 'valide') <span class="badge bg-primary">Validé</span>
            @elseif($mission->status === 'cloture') <span class="badge bg-dark">Clôturé</span>
            @endif
          </div>
          <div class="col-6">
            <div class="text-muted small">Livrables déposés</div>
            <div class="fw-bold">{{ $mission->deliverables->count() }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Livrables déposés -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-bottom">
        <h6 class="fw-bold mb-0"><i class="fas fa-folder-open text-info me-2"></i>Livrables du Consultant</h6>
      </div>
      <div class="card-body {{ $mission->deliverables->isEmpty() ? '' : 'p-0' }}">
        @forelse($mission->deliverables as $d)
          <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <div class="flex-shrink-0" style="width:38px;height:38px;background:#eef2ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-file-alt text-primary"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-medium" style="font-size:13px;">{{ $d->original_name }}</div>
              <div class="text-muted" style="font-size:11px;">{{ $d->created_at->format('d/m/Y à H:i') }}</div>
              @if($d->notes)
                <div class="text-muted" style="font-size:11px;">{{ $d->notes }}</div>
              @endif
            </div>
            <a href="{{ asset('storage/' . $d->file_path) }}" target="_blank" class="btn btn-sm btn-light border">
              <i class="fas fa-download text-muted"></i>
            </a>
          </div>
        @empty
          <p class="text-muted small text-center py-4 mb-0">Aucun livrable déposé pour l'instant.</p>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Droite : Historique d'audit -->
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-bottom">
        <h6 class="fw-bold mb-0"><i class="fas fa-history text-muted me-2"></i>Historique d'Audit</h6>
      </div>
      <div class="card-body p-0" style="max-height:500px; overflow-y:auto;">
        @forelse($mission->audits->sortByDesc('created_at') as $audit)
          <div class="d-flex gap-3 p-3 border-bottom">
            <div class="flex-shrink-0 text-muted" style="font-size:11px; padding-top:2px; width:80px;">
              {{ $audit->created_at->format('d/m H:i') }}
            </div>
            <div>
              <div style="font-size:13px;">{{ $audit->action }}</div>
              <div class="text-muted" style="font-size:11px;">
                Par {{ $audit->user?->name ?? 'Système' }} — {{ $audit->ip_address }}
              </div>
            </div>
          </div>
        @empty
          <p class="text-muted small text-center py-4 mb-0">Aucune action enregistrée.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Modal Renouvellement -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Prolonger l'accès</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('gel-admin.consultants.renew', $mission->id) }}" method="POST">
          @csrf
          <label class="form-label fw-semibold">Nouvelle date de fin d'accès *</label>
          <input type="date" name="end_date" class="form-control" required
                 min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                 value="{{ $mission->end_date->format('Y-m-d') }}">
          <div class="form-text text-muted">Le consultant sera notifié par email de ce renouvellement.</div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer la prolongation</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
