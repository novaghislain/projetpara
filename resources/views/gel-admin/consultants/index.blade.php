@extends('layouts.gel-admin')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-bold mb-0">Consultants Externes</h1>
    <p class="text-muted small mt-1">Gérez les dossiers confiés aux consultants et leur accès temporaire.</p>
  </div>
  <a href="{{ route('gel-admin.consultants.create') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-plus me-1"></i> Inviter un consultant
  </a>
</div>

@if(session('success'))
  <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
@endif

@if($missions->isEmpty())
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
      <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
      <p class="fw-bold">Aucun consultant invité</p>
      <p class="text-muted small">Invitez un consultant pour lui confier un dossier limité et temporaire.</p>
    </div>
  </div>
@else
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Dossier</th>
              <th>Consultant</th>
              <th>Spécialité</th>
              <th>Fin d'accès</th>
              <th>Statut</th>
              <th>Livrables</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($missions as $mission)
              @php $expired = $mission->isExpired(); @endphp
              <tr class="{{ $expired ? 'opacity-50' : '' }}">
                <td>
                  <div class="fw-bold">{{ $mission->title }}</div>
                  <div class="text-muted small">{{ Str::limit($mission->description, 60) }}</div>
                </td>
                <td>
                  @if($mission->consultant)
                    <span class="fw-medium">{{ $mission->consultant->name }}</span>
                    <div class="text-muted small">{{ $mission->consultant->email }}</div>
                  @else
                    <span class="badge bg-warning text-dark">En attente d'acceptation</span>
                  @endif
                </td>
                <td><span class="badge bg-light text-dark border">{{ $mission->specialty }}</span></td>
                <td>
                  <span class="{{ $expired ? 'text-danger' : 'text-dark' }} fw-medium">
                    {{ $mission->end_date->format('d/m/Y') }}
                  </span>
                  @if($expired)
                    <div class="text-danger small"><i class="fas fa-lock"></i> Expiré</div>
                  @endif
                </td>
                <td>
                  @if($mission->status === 'en_attente') <span class="badge bg-secondary">En attente</span>
                  @elseif($mission->status === 'en_cours') <span class="badge bg-success">En cours</span>
                  @elseif($mission->status === 'livre') <span class="badge bg-info">Livrable déposé</span>
                  @elseif($mission->status === 'valide') <span class="badge bg-primary">Validé</span>
                  @elseif($mission->status === 'cloture') <span class="badge bg-dark">Clôturé</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-light text-dark border">{{ $mission->deliverables->count() }} fichier(s)</span>
                </td>
                <td class="text-end">
                  <a href="{{ route('gel-admin.consultants.show', $mission->id) }}" class="btn btn-sm btn-light border me-1">
                    <i class="fas fa-eye text-muted"></i>
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endif

@endsection
