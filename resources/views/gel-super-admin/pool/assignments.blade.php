@extends('layouts.gel-super-admin')

@section('title', 'Affectations du Personnel')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Affectation du Personnel</h2>
            <p class="text-muted">Affectez manuellement des secrétaires et comptables aux entreprises souscrites au Service Géré.</p>
        </div>
        <div>
            <a href="{{ route('gel-super-admin.pool.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-users me-2"></i> Voir le Pool
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Entreprises en attente -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-warning" style="border-top: 4px solid var(--bs-warning);">
                <div class="card-header bg-white pb-0">
                    <h5 class="mb-0"><i class="fas fa-hourglass-half text-warning me-2"></i> En attente d'affectation ({{ $waitingClients->count() }})</h5>
                </div>
                <div class="card-body">
                    @forelse($waitingClients as $client)
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="text-primary mb-1">{{ $client->company_name }}</h6>
                            <p class="text-muted small mb-2">Besoins : 
                                @if($client->wants_secretary) <span class="badge bg-info">Secrétariat</span> @endif
                                @if($client->wants_accounting) <span class="badge bg-success">Comptabilité</span> @endif
                            </p>
                            
                            <form action="{{ route('gel-super-admin.pool.assign') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                                
                                @if($client->wants_secretary && !$client->assigned_secretary_id)
                                <div class="mb-2">
                                    <label class="form-label small">Affecter un(e) Secrétaire</label>
                                    <select name="secretary_id" class="form-select form-select-sm">
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($secretaries as $sec)
                                            <option value="{{ $sec->id }}">{{ $sec->prenom }} {{ $sec->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                @if($client->wants_accounting && !$client->assigned_accountant_id)
                                <div class="mb-3">
                                    <label class="form-label small">Affecter un(e) Comptable</label>
                                    <select name="accountant_id" class="form-select form-select-sm">
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($accountants as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->prenom }} {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-sm btn-primary">Valider l'affectation</button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fs-1 text-success mb-3"></i>
                            <p>Toutes les entreprises ont leur personnel affecté.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Entreprises affectées -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-success" style="border-top: 4px solid var(--bs-success);">
                <div class="card-header bg-white pb-0">
                    <h5 class="mb-0"><i class="fas fa-link text-success me-2"></i> Affectations actives ({{ $assignedClients->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <tbody>
                                @forelse($assignedClients as $client)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0">{{ $client->company_name }}</h6>
                                            <small class="text-muted">ID: {{ $client->id }}</small>
                                        </td>
                                        <td>
                                            @if($client->assignedSecretary)
                                                <div class="small mb-1">
                                                    <span class="badge bg-info">S</span> {{ $client->assignedSecretary->prenom }} {{ $client->assignedSecretary->name }}
                                                </div>
                                            @endif
                                            @if($client->assignedAccountant)
                                                <div class="small">
                                                    <span class="badge bg-success">C</span> {{ $client->assignedAccountant->prenom }} {{ $client->assignedAccountant->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary" title="Réaffecter" onclick="alert('Module de réaffectation en cours de développement')">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            Aucune affectation active.
                                        </td>
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
