@extends('layouts.gel-informaticien')

@section('title', 'Centre de Support Informatique')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Tickets Techniques</h5>
            <div class="btn-group">
                <a href="{{ route('gel-informaticien.tickets.index') }}" class="btn {{ !$status ? 'btn-primary' : 'btn-outline-primary' }}">Tous</a>
                <a href="{{ route('gel-informaticien.tickets.index', ['status' => 'nouveau']) }}" class="btn {{ $status == 'nouveau' ? 'btn-primary' : 'btn-outline-primary' }}">Nouveaux</a>
                <a href="{{ route('gel-informaticien.tickets.index', ['status' => 'en_cours']) }}" class="btn {{ $status == 'en_cours' ? 'btn-primary' : 'btn-outline-primary' }}">En Cours</a>
                <a href="{{ route('gel-informaticien.tickets.index', ['status' => 'resolu']) }}" class="btn {{ $status == 'resolu' ? 'btn-primary' : 'btn-outline-primary' }}">Résolus</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Sujet</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Assigné à</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>#{{ $ticket->id }}</td>
                            <td>
                                @if($ticket->client)
                                    <span class="fw-bold">{{ $ticket->client->nom_entreprise }}</span>
                                @else
                                    <span class="text-muted">Interne</span>
                                @endif
                            </td>
                            <td>{{ $ticket->subject }}</td>
                            <td>
                                @if($ticket->priority == 'urgente')
                                    <span class="badge bg-danger">Urgente</span>
                                @elseif($ticket->priority == 'haute')
                                    <span class="badge bg-warning text-dark">Haute</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($ticket->priority) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->status == 'nouveau')
                                    <span class="badge bg-primary">Nouveau</span>
                                @elseif($ticket->status == 'en_cours')
                                    <span class="badge bg-info">En Cours</span>
                                @elseif($ticket->status == 'resolu')
                                    <span class="badge bg-success">Résolu</span>
                                @else
                                    <span class="badge bg-dark">Fermé</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->assignedTo)
                                    {{ $ticket->assignedTo->prenom }}
                                @else
                                    <span class="text-muted fst-italic">Non assigné</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('gel-informaticien.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Traiter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Aucun ticket trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection
