@extends('gel-accountant.layouts.app')

@section('title', 'Annuaire des Contacts')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Annuaire des Contacts</h1>
            <a href="{{ route('gel-accountant.secretariat.contacts.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nouveau Contact
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des contacts</h6>
            <form action="{{ route('gel-accountant.secretariat.contacts.index') }}" method="GET" class="form-inline">
                <select name="type" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    <option value="client" {{ request('type') == 'client' ? 'selected' : '' }}>Client</option>
                    <option value="fournisseur" {{ request('type') == 'fournisseur' ? 'selected' : '' }}>Fournisseur</option>
                    <option value="partenaire" {{ request('type') == 'partenaire' ? 'selected' : '' }}>Partenaire</option>
                    <option value="administration" {{ request('type') == 'administration' ? 'selected' : '' }}>Administration</option>
                </select>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                            <tr>
                                <td>{{ $contact->nom }}</td>
                                <td>
                                    @php
                                        $badge = 'secondary';
                                        if($contact->type == 'client') $badge = 'primary';
                                        if($contact->type == 'fournisseur') $badge = 'warning text-dark';
                                        if($contact->type == 'partenaire') $badge = 'info';
                                        if($contact->type == 'administration') $badge = 'danger';
                                    @endphp
                                    <span class="badge bg-{{ $badge }} text-white">{{ ucfirst($contact->type) }}</span>
                                </td>
                                <td>{{ $contact->email ?? '-' }}</td>
                                <td>{{ $contact->telephone ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('gel-accountant.secretariat.contacts.show', $contact->id) }}" class="btn btn-sm btn-info" title="Voir la fiche 360°">
                                        <i class="fas fa-eye"></i> Fiche 360°
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun contact trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
