@extends('layouts.gel-informaticien')

@section('title', 'Centre de Support Informatique')

@section('content')
<div class="p-4">
    <div class="animate-fade" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--sec-text-primary); margin-bottom: 4px;">Centre de Support Informatique</h1>
            <p style="font-size: 12px; color: var(--sec-text-muted);">Gestion des tickets techniques et de l'assistance utilisateurs.</p>
        </div>
    </div>

    <div class="pro-panel animate-fade delay-1 mb-4" style="border-top: 4px solid var(--sec-primary);">
        <div class="sec-card-header d-flex justify-content-between align-items-center" style="padding:16px 20px; border-bottom:1px solid var(--sec-border); flex-wrap: wrap; gap: 10px;">
            <h5 class="mb-0" style="font-weight:600; font-size:15px;"><i class="fas fa-ticket-alt me-2" style="color:var(--sec-warning);"></i> File des Tickets Techniques</h5>
            
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('gel-informaticien.tickets.index') }}" method="GET" class="d-flex" style="max-width: 250px;">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Rechercher un ticket...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary ms-1"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="btn-group">
                    <a href="{{ route('gel-informaticien.tickets.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-primary' }}">Tous</a>
                    <a href="{{ route('gel-informaticien.tickets.index', ['status' => 'nouveau']) }}" class="btn btn-sm {{ $status == 'nouveau' ? 'btn-primary' : 'btn-outline-primary' }}">Nouveaux</a>
                    <a href="{{ route('gel-informaticien.tickets.index', ['status' => 'en_cours']) }}" class="btn btn-sm {{ $status == 'en_cours' ? 'btn-primary' : 'btn-outline-primary' }}">En Cours</a>
                    <a href="{{ route('gel-informaticien.tickets.index', ['status' => 'resolu']) }}" class="btn btn-sm {{ $status == 'resolu' ? 'btn-primary' : 'btn-outline-primary' }}">Résolus</a>
                </div>
            </div>
        </div>
        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="sec-table" style="margin-bottom:0;">
                    <thead style="background:#f8fafc;">
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
                                <td style="font-weight:600;">#{{ $ticket->id }}</td>
                                <td>
                                    @if($ticket->client)
                                        <span class="fw-bold">{{ $ticket->client->company_name }}</span>
                                    @else
                                        <span class="text-muted">Interne</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->subject }}</td>
                                <td>
                                    @if($ticket->priority == 'urgente')
                                        <span class="sec-badge sec-badge-danger">Urgente</span>
                                    @elseif($ticket->priority == 'haute')
                                        <span class="sec-badge sec-badge-warning">Haute</span>
                                    @else
                                        <span class="sec-badge sec-badge-muted">{{ ucfirst($ticket->priority) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->status == 'nouveau')
                                        <span class="sec-badge sec-badge-info">Nouveau</span>
                                    @elseif($ticket->status == 'en_cours')
                                        <span class="sec-badge" style="background:#DBEAFE;color:#2563EB;">En Cours</span>
                                    @elseif($ticket->status == 'resolu')
                                        <span class="sec-badge sec-badge-success">Résolu</span>
                                    @else
                                        <span class="sec-badge" style="background:#1F2937;color:white;">Fermé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->assignedTo)
                                        {{ $ticket->assignedTo->prenom }}
                                    @else
                                        <span class="text-muted fst-italic">Non assigné</span>
                                    @endif
                                </td>
                                <td style="color:var(--sec-text-muted); font-size:12px;">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('gel-informaticien.tickets.show', $ticket->id) }}" class="btn btn-sm" style="background:var(--sec-primary-light); color:var(--sec-primary); font-weight:600;">
                                        <i class="fas fa-eye"></i> Traiter
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3" style="opacity:0.2;"></i><br>
                                    Aucun ticket trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($tickets->hasPages())
            <div class="p-3 border-top">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Echo) {
            window.Echo.private('it.tickets')
                .listen('ItTicketCreated', (e) => {
                    const ticket = e.ticket;
                    
                    // Show a toast notification
                    const container = document.getElementById('sec-toast-container');
                    if (container) {
                        const toast = document.createElement('div');
                        toast.className = 'sec-toast';
                        toast.innerHTML = `
                            <div class="sec-toast-icon-wrapper" style="background:#3B82F6;">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;">Nouveau Ticket #${ticket.id}</div>
                                <div style="font-size:11px; opacity:0.9;">${ticket.subject}</div>
                            </div>
                            <div class="sec-toast-progress"></div>
                        `;
                        container.appendChild(toast);
                        
                        // Remove toast after 4 seconds
                        setTimeout(() => {
                            if (toast.parentNode) {
                                toast.remove();
                            }
                        }, 4000);
                        
                        // If we are viewing "nouveaux" or "tous", reload the page to show the new ticket
                        // For a better UX we could inject the row, but reload is safer for now.
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                });
        }
    });
</script>
@endpush
