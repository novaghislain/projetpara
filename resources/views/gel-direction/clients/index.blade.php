@extends('layouts.gel-direction')

@section('title', 'Clients & CA')
@section('page_title', 'Gestion des Clients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Retrouvez ici la liste de vos clients et partenaires d'affaires.</p>
    </div>
    <button type="button" class="sec-btn sec-btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
        <i class="fas fa-plus"></i> Nouveau Client
    </button>
</div>

@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="sec-card shadow-sm">
    <div class="sec-card-body p-0">
        <div class="table-responsive">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Nom / Entreprise</th>
                        <th>Contact</th>
                        <th>Adresse / NIF</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td class="align-middle">
                            <strong>{{ $client->nom_entreprise }}</strong>
                        </td>
                        <td class="align-middle">
                            @if($client->email)<div><i class="fas fa-envelope text-muted"></i> {{ $client->email }}</div>@endif
                            @if($client->telephone)<div><i class="fas fa-phone text-muted"></i> {{ $client->telephone }}</div>@endif
                        </td>
                        <td class="align-middle text-muted">
                            {{ $client->adresse ?? '-' }}<br>
                            @if($client->nif)<small>NIF: {{ $client->nif }}</small>@endif
                        </td>
                        <td class="align-middle">
                            @if($client->statut == 'actif')
                                <span class="sec-badge sec-badge-success">Actif</span>
                            @else
                                <span class="sec-badge sec-badge-muted">{{ ucfirst($client->statut) }}</span>
                            @endif
                        </td>
                        <td class="align-middle text-end">
                            <a href="#" class="sec-btn sec-btn-sm" style="background:transparent;"><i class="fas fa-edit text-muted"></i></a>
                            <a href="#" class="sec-btn sec-btn-sm" style="background:transparent;"><i class="fas fa-trash text-danger"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3" style="color: #CBD5E1;"></i>
                            <p>Vous n'avez pas encore ajouté de clients.</p>
                            <button type="button" class="sec-btn sec-btn-primary sec-btn-sm" data-bs-toggle="modal" data-bs-target="#addClientModal">Ajouter mon premier client</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($clients->hasPages())
    <div class="sec-card-footer bg-white border-0 py-3">
        {{ $clients->links() }}
    </div>
    @endif
</div>

<!-- Modal Ajouter Client -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('gel-direction.clients.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addClientModalLabel">Ajouter un Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                    <input type="text" name="nom_entreprise" class="form-control" required placeholder="Ex: ABC Corp">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="contact@abccorp.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" placeholder="+229 ...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Numéro IFU / NIF</label>
                    <input type="text" name="nif" class="form-control" placeholder="Numéro d'immatriculation">
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="2" placeholder="Adresse complète"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer le client</button>
            </div>
        </form>
    </div>
</div>
@endsection
