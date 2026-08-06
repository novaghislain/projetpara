@extends('layouts.gel-informaticien')

@section('title', 'Demandes de Développement Web/App')

@section('content')
<div class="card shadow-sm border-0 mb-4 border-top border-4 border-info">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0 text-info"><i class="fas fa-code me-2"></i> File de Projets de Digitalisation</h5>
                <p class="text-muted small mb-0 mt-1">Demandes des clients (sites web, applications métiers, etc.) à deviser et développer.</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('gel-informaticien.dev-requests.index') }}" class="btn {{ !$status ? 'btn-info text-white' : 'btn-outline-info' }}">Toutes</a>
                <a href="{{ route('gel-informaticien.dev-requests.index', ['status' => 'recue']) }}" class="btn {{ $status == 'recue' ? 'btn-info text-white' : 'btn-outline-info' }}">Reçues</a>
                <a href="{{ route('gel-informaticien.dev-requests.index', ['status' => 'devis_en_cours']) }}" class="btn {{ $status == 'devis_en_cours' ? 'btn-info text-white' : 'btn-outline-info' }}">En Devis</a>
                <a href="{{ route('gel-informaticien.dev-requests.index', ['status' => 'en_developpement']) }}" class="btn {{ $status == 'en_developpement' ? 'btn-info text-white' : 'btn-outline-info' }}">En Développement</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Réf</th>
                        <th>Client demandeur</th>
                        <th>Type de projet</th>
                        <th>Statut</th>
                        <th>Lien Devis</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>#DEV-{{ $req->id }}</td>
                            <td><span class="fw-bold">{{ $req->client->nom_entreprise ?? 'Client Inconnu' }}</span></td>
                            <td>{{ $req->subject }}</td>
                            <td>
                                @if($req->status == 'recue')
                                    <span class="badge bg-primary">Nouvelle demande</span>
                                @elseif($req->status == 'devis_en_cours')
                                    <span class="badge bg-warning text-dark">Devis en préparation</span>
                                @elseif($req->status == 'en_developpement')
                                    <span class="badge bg-info">En développement</span>
                                @elseif($req->status == 'livre')
                                    <span class="badge bg-success">Projet Livré</span>
                                @else
                                    <span class="badge bg-secondary">{{ $req->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($req->devis_url)
                                    <a href="{{ $req->devis_url }}" target="_blank" class="text-info"><i class="fas fa-file-invoice"></i> Voir Devis</a>
                                @else
                                    <span class="text-muted fst-italic small">Aucun devis lié</span>
                                @endif
                            </td>
                            <td>{{ $req->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('gel-informaticien.dev-requests.show', $req->id) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-edit"></i> Gérer
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <h5>Aucune demande de projet digital</h5>
                                <p>Les demandes des clients apparaîtront ici.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
