@extends('layouts.gel-informaticien')

@section('title', 'Mes Missions Commerciales')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 mb-1"><i class="fas fa-briefcase text-primary"></i> Mes Missions Affectées</h1>
            <p class="text-muted mb-0">Contrats de Sécurité, Maintenance et Développement.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Sujet</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($missions as $mission)
                        <tr>
                            <td><strong>{{ $mission->client->company_name ?? 'Inconnu' }}</strong></td>
                            <td>
                                @if($mission->type == 'securite') <span class="badge bg-danger">Sécurité</span>
                                @elseif($mission->type == 'maintenance') <span class="badge bg-warning text-dark">Maintenance</span>
                                @else <span class="badge bg-info text-white">Développement</span>
                                @endif
                            </td>
                            <td>{{ $mission->subject }}</td>
                            <td>
                                @if($mission->status == 'en_attente') <span class="badge bg-secondary">En attente</span>
                                @elseif($mission->status == 'en_cours') <span class="badge bg-primary">En cours</span>
                                @else <span class="badge bg-success">Terminée</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('gel-informaticien.missions.show', $mission->id) }}" class="btn btn-sm btn-outline-primary">Gérer</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Vous n'avez aucune mission affectée actuellement.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($missions->hasPages())
                <div class="p-3 border-top">
                    {{ $missions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
