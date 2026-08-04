@extends('layouts.gel-direction')
@section('page_title', 'Tableau de bord Exécutif')

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0" style="border-left: 4px solid var(--dir-primary) !important;">
            <div class="card-body">
                <h6 class="text-muted">Total Clients</h6>
                <h2 class="mb-0">{{ $totalClients ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0" style="border-left: 4px solid #EF4444 !important;">
            <div class="card-body">
                <h6 class="text-muted">Validations en attente</h6>
                <h2 class="mb-0 text-danger">{{ $validationsCount ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0" style="border-left: 4px solid #10B981 !important;">
            <div class="card-body">
                <h6 class="text-muted">Tâches globales terminées</h6>
                <h2 class="mb-0 text-success">{{ $completedTasksCount ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Validations Requises -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-file-signature text-warning me-2"></i> À valider en urgence</h5>
            </div>
            <div class="card-body p-0">
                @if(($validationsCount ?? 0) == 0)
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-check-circle" style="font-size:32px; color:#10B981; margin-bottom:10px;"></i>
                    <p class="mb-0">Aucun document en attente de votre signature ou validation.</p>
                </div>
                @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Document / Tâche</th>
                            <th>Demandé par</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Contrat_Prestation_2024.pdf</strong></td>
                            <td>Secrétaire (Sophie)</td>
                            <td>Aujourd'hui</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary">Examiner</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <!-- Activité Récente GEL Intelligence -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-robot text-primary me-2"></i> GEL Intelligence</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <div class="d-flex">
                            <i class="fas fa-magic text-info mt-1 me-2"></i>
                            <div>
                                <strong class="d-block">Workflow Déclenché</strong>
                                <small class="text-muted">Un courrier a été classé automatiquement.</small>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="d-flex">
                            <i class="fas fa-bell text-warning mt-1 me-2"></i>
                            <div>
                                <strong class="d-block">Alerte Proactive</strong>
                                <small class="text-muted">Rappel créé pour 3 factures impayées.</small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
