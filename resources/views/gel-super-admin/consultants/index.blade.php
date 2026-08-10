@extends('layouts.gel-super-admin')

@section('title', 'Gestion des Consultants')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Registre des Consultants</h1>
        <p class="page-subtitle">Gérez les consultants externes et leurs missions au sein des entreprises clientes.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createConsultantModal">
        <i class="fas fa-plus me-2"></i> Ajouter un Consultant
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100 p-4">
            <h5 class="fw-bold mb-4">Liste des Consultants</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle table-borderless">
                    <thead>
                        <tr>
                            <th>Nom & Email</th>
                            <th>Spécialité</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultants as $consultant)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $consultant->name }}</div>
                                <div class="text-muted" style="font-size:0.85rem">{{ $consultant->email }}</div>
                            </td>
                            <td>{{ $consultant->fonction ?? 'Généraliste' }}</td>
                            <td>
                                @if($consultant->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal{{ $consultant->id }}" title="Affecter à une mission">
                                    <i class="fas fa-briefcase"></i> Affecter
                                </button>
                            </td>
                        </tr>

                        <!-- Assign Modal -->
                        <div class="modal fade" id="assignModal{{ $consultant->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Affecter {{ $consultant->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('gel-super-admin.consultants.assign', $consultant->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Entreprise Cliente</label>
                                                <select name="entreprise_id" class="form-select" required>
                                                    <option value="">Sélectionnez une entreprise...</option>
                                                    @foreach($entreprises as $entreprise)
                                                        <option value="{{ $entreprise->id }}">{{ $entreprise->nom }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Titre de la mission</label>
                                                <input type="text" name="title" class="form-control" required placeholder="Ex: Audit financier, Déploiement logiciel...">
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Date de début</label>
                                                    <input type="date" name="start_date" class="form-control" required>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Date de fin</label>
                                                    <input type="date" name="end_date" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer l'affectation</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucun consultant enregistré.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100 p-4">
            <h5 class="fw-bold mb-4">Missions en cours</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle table-borderless">
                    <thead>
                        <tr>
                            <th>Mission</th>
                            <th>Consultant</th>
                            <th>Expiration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($missions as $mission)
                        <tr>
                            <td>
                                <div class="fw-bold" style="font-size:0.9rem">{{ $mission->title }}</div>
                                <div class="text-muted" style="font-size:0.8rem">Chez: {{ $mission->entreprise->nom ?? 'N/A' }}</div>
                            </td>
                            <td>{{ $mission->consultant->name ?? 'N/A' }}</td>
                            <td>
                                @if($mission->isExpired())
                                    <span class="badge bg-danger">Expirée ({{ $mission->end_date->format('d/m/Y') }})</span>
                                @else
                                    <span class="badge bg-success">Active ({{ $mission->end_date->format('d/m/Y') }})</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Aucune mission en cours.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Consultant Modal -->
<div class="modal fade" id="createConsultantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau Consultant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gel-super-admin.consultants.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adresse email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Spécialité (Domaine)</label>
                        <input type="text" name="specialty" class="form-control" placeholder="Ex: Audit Qualité ISO, Cybersécurité...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le compte</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
