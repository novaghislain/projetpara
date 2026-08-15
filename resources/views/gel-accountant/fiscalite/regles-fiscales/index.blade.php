@extends('layouts.gel-accountant')

@section('title', 'Moteur Fiscal - Règles')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Paramétrage du Moteur Fiscal</h1>
            <a href="{{ route('gel-accountant.fiscalite.regles-fiscales.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nouvelle Règle
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Référentiel des taux et règles (SYSCOHADA)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Pays</th>
                            <th>Impôt</th>
                            <th>Taux</th>
                            <th>Conditions spécifiques</th>
                            <th>Validité (Début - Fin)</th>
                            <th>Version</th>
                            <th>Statut</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($regles as $regle)
                            <tr>
                                <td class="text-center font-weight-bold">{{ $regle->code_pays }}</td>
                                <td>{{ $regle->type_impot }}</td>
                                <td>{{ number_format($regle->taux * 100, 2) }} %</td>
                                <td>
                                    @if($regle->conditions)
                                        <pre class="mb-0" style="font-size: 0.8rem; background: #f8f9fa; padding: 5px; border-radius: 4px;">{{ json_encode($regle->conditions, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        <span class="text-muted small">Règle générale</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $regle->date_debut_validite->format('d/m/Y') }} 
                                    - 
                                    {{ $regle->date_fin_validite ? $regle->date_fin_validite->format('d/m/Y') : 'À ce jour' }}
                                </td>
                                <td class="text-center">v{{ $regle->version }}</td>
                                <td>
                                    @if($regle->statut == 'active')
                                        <span class="badge bg-success text-white">Active</span>
                                    @else
                                        <span class="badge bg-secondary text-white">Obsolète</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $regle->source_reglementaire ?? '-' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucune règle fiscale définie dans le moteur.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $regles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
