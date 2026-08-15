@extends('layouts.gel-direction')

@section('title', 'Validations & Approbations')
@section('page_title', 'Centre d\'Approbation')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Tâches Critiques -->
        <div class="col-md-6">
            <div class="sec-card h-100">
                <div class="sec-card-header pt-4 pb-0 px-4" style="border:none;">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks text-primary me-2"></i>Tâches Nécessitant Validation</h5>
                </div>
                <div class="sec-card-body p-4">
                    @forelse($tasksToApprove as $task)
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 mb-3" style="background:var(--sec-bg);">
                            <div>
                                <h6 class="mb-1 fw-bold">{{ $task->titre }}</h6>
                                <p class="mb-0 small text-muted">Priorité : {{ ucfirst($task->priorite) }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('gel-direction.validations.approve', $task->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="tache">
                                    <button type="submit" class="sec-btn sec-btn-sm" style="background:var(--sec-success); color:white; border-radius:50%; width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center;" title="Approuver"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="{{ route('gel-direction.validations.reject', $task->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="tache">
                                    <button type="submit" class="sec-btn sec-btn-sm" style="background:var(--sec-danger); color:white; border-radius:50%; width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center;" title="Rejeter"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-double fa-2x mb-2 text-light"></i>
                            <p class="mb-0">Aucune tâche en attente</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Demandes de Congés -->
        <div class="col-md-6">
            <div class="sec-card h-100">
                <div class="sec-card-header pt-4 pb-0 px-4" style="border:none;">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-calendar-alt text-warning me-2"></i>Demandes de Congés (RH)</h5>
                </div>
                <div class="sec-card-body p-4">
                    @forelse($leaveRequests as $leave)
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center fw-bold" style="width:40px; height:40px; background:var(--sec-primary-light); color:var(--sec-primary);">
                                    {{ substr($leave->prenom, 0, 1) }}{{ substr($leave->nom, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ $leave->prenom }} {{ $leave->nom }}</h6>
                                    <p class="mb-0 small text-muted">{{ ucfirst($leave->type_conge) }} : du {{ \Carbon\Carbon::parse($leave->date_debut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($leave->date_fin)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('gel-direction.validations.approve', $leave->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="conge">
                                    <button type="submit" class="sec-btn sec-btn-sm" style="color:var(--sec-success); border:1px solid var(--sec-success); background:transparent;"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="{{ route('gel-direction.validations.reject', $leave->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="conge">
                                    <button type="submit" class="sec-btn sec-btn-sm" style="color:var(--sec-danger); border:1px solid var(--sec-danger); background:transparent;"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-umbrella-beach fa-2x mb-2 text-light"></i>
                            <p class="mb-0">Aucune demande en attente</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Déclarations Fiscales -->
        <div class="col-md-12">
            <div class="sec-card">
                <div class="sec-card-header pt-4 pb-0 px-4" style="border:none;">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Déclarations Fiscales à Valider</h5>
                </div>
                <div class="sec-card-body p-4">
                    <div class="table-responsive">
                        <table class="sec-table align-middle">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Période</th>
                                    <th>Montant à payer</th>
                                    <th>Généré le</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($declarationsToApprove as $decl)
                                <tr>
                                    <td class="fw-bold">{{ $decl->client_nom }}</td>
                                    <td><span class="sec-badge sec-badge-muted">{{ $decl->type }}</span></td>
                                    <td>{{ $decl->periode }}</td>
                                    <td class="fw-bold text-dark">{{ number_format($decl->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($decl->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <button class="sec-btn sec-btn-sm sec-btn-primary" title="Visualiser"><i class="fas fa-eye"></i></button>
                                        <form action="{{ route('gel-direction.validations.approve', $decl->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="type" value="fiscal">
                                            <button type="submit" class="sec-btn sec-btn-sm" style="background:var(--sec-success); color:white;" title="Valider & Transmettre"><i class="fas fa-paper-plane"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Aucune déclaration fiscale en attente.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
