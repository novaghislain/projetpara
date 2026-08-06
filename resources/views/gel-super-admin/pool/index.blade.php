@extends('layouts.gel-super-admin')

@section('title', 'Pool de Personnel GEL SABINET')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Registre du Personnel (Pool GEL)</h2>
            <p class="text-muted">Gérez les secrétaires et comptables affectables aux entreprises.</p>
        </div>
        <div>
            <a href="{{ route('gel-super-admin.pool.assignments') }}" class="btn btn-primary">
                <i class="fas fa-tasks me-2"></i> Gérer les affectations
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Membre</th>
                            <th>Rôle</th>
                            <th>Contact</th>
                            <th>Charge de travail (Affectations)</th>
                            <th>Disponibilité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($personnel as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($member->prenom, 0, 1) . substr($member->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $member->prenom }} {{ $member->name }}</h6>
                                            <small class="text-muted">ID: {{ $member->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($member->role === 'secretaire' || $member->role_secretaire)
                                        <span class="badge bg-info">Secrétaire</span>
                                    @endif
                                    @if(in_array($member->role, ['comptable', 'chef_comptable']))
                                        <span class="badge bg-success">Comptable</span>
                                    @endif
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope text-muted me-2"></i>{{ $member->email }}</div>
                                    @if($member->phone)
                                        <div><i class="fas fa-phone text-muted me-2"></i>{{ $member->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            @php
                                                $percentage = $member->pool_max_capacity > 0 ? ($member->active_assignments_count / $member->pool_max_capacity) * 100 : 0;
                                                $colorClass = $percentage > 80 ? 'bg-danger' : ($percentage > 50 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-bold">{{ $member->active_assignments_count }} / {{ $member->pool_max_capacity }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($member->is_active)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Actif</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" title="Éditer la capacité">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-users-slash fs-2 mb-3"></i>
                                    <p class="mb-0">Aucun personnel enregistré dans le pool GEL.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
