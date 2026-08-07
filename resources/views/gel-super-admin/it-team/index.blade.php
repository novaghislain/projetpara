@extends('layouts.gel-super-admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-network-wired text-primary me-2"></i>Équipe Informatique (GEL SABINET)</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createItModal">
            <i class="fas fa-plus me-2"></i>Ajouter un Informaticien
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Informaticiens</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Dernière connexion</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($informaticiens as $info)
                            <tr>
                                <td>{{ $info->name }}</td>
                                <td>{{ $info->email }}</td>
                                <td>{{ $info->phone ?? 'Non renseigné' }}</td>
                                <td>{{ $info->last_login_at ? $info->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white me-2" data-bs-toggle="modal" data-bs-target="#permissionsModal{{ $info->id }}" title="Gérer les permissions">
                                        <i class="fas fa-shield-alt"></i> Droits
                                    </button>
                                    <form action="{{ route('gel-super-admin.it-team.destroy', $info->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet informaticien ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Aucun informaticien n'a été créé pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createItModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau Compte Informaticien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gel-super-admin.it-team.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email professionnel</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="alert alert-info">
                        Un mot de passe sécurisé sera généré et affiché après la création.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le compte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Permissions -->
@foreach($informaticiens as $info)
<div class="modal fade" id="permissionsModal{{ $info->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Permissions pour {{ $info->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gel-super-admin.it-team.permissions', $info->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Note : L'informaticien n'a <strong>jamais</strong> accès aux données métier (documents, écritures comptables, messagerie entreprise).
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Module Technique</th>
                                    @foreach($actions as $action)
                                        <th class="text-center">{{ ucfirst($action) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $slug => $name)
                                    <tr>
                                        <td class="fw-bold">{{ $name }}</td>
                                        @foreach($actions as $action)
                                            <td class="text-center align-middle">
                                                <div class="form-check d-flex justify-content-center mb-0">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $slug }}.{{ $action }}" 
                                                        {{ $info->hasPermissionTo("$slug.$action") ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
