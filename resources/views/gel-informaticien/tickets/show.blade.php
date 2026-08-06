@extends('layouts.gel-informaticien')

@section('title', 'Ticket #' . $ticket->id)

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <!-- Messages -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $ticket->subject }}</h5>
            </div>
            <div class="card-body bg-light" style="max-height: 500px; overflow-y: auto;">
                @foreach($ticket->messages as $msg)
                    <div class="mb-4 d-flex {{ $msg->author_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                        <div class="mx-3 text-center">
                            <div class="avatar bg-{{ $msg->author->account_type == 'informaticien' ? 'primary' : 'secondary' }} mb-1" style="width: 40px; height: 40px;">
                                {{ substr($msg->author->prenom, 0, 1) }}
                            </div>
                            <small class="text-muted" style="font-size: 10px;">{{ $msg->created_at->format('H:i') }}</small>
                        </div>
                        <div class="card shadow-sm border-0 w-75 {{ $msg->is_internal ? 'bg-warning bg-opacity-10 border-warning' : '' }}">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong class="small">{{ $msg->author->prenom }} {{ $msg->author->name }}</strong>
                                    @if($msg->is_internal)
                                        <span class="badge bg-warning text-dark" style="font-size: 10px;">Note Interne</span>
                                    @endif
                                </div>
                                <p class="mb-0 text-break">{!! nl2br(e($msg->message)) !!}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                @if($ticket->messages->count() === 0)
                    <div class="text-center text-muted py-5">
                        <p>Aucun message. Répondez pour commencer le traitement.</p>
                    </div>
                @endif
            </div>
            
            <div class="card-footer bg-white border-0 py-3">
                <form action="{{ route('gel-informaticien.tickets.message.store', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Votre Réponse</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_internal" id="isInternal" value="1">
                            <label class="form-check-label text-warning fw-bold" for="isInternal">Note interne (invisible pour le client)</label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Envoyer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Infos Ticket -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0">Détails du Ticket</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('gel-informaticien.tickets.update', $ticket->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Client</label>
                        <div>{{ $ticket->client ? $ticket->client->nom_entreprise : 'Support Interne' }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Auteur</label>
                        <div>{{ $ticket->author->prenom }} {{ $ticket->author->name }} ({{ $ticket->author->email }})</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Statut</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="nouveau" {{ $ticket->status == 'nouveau' ? 'selected' : '' }}>Nouveau</option>
                            <option value="en_cours" {{ $ticket->status == 'en_cours' ? 'selected' : '' }}>En Cours</option>
                            <option value="resolu" {{ $ticket->status == 'resolu' ? 'selected' : '' }}>Résolu</option>
                            <option value="ferme" {{ $ticket->status == 'ferme' ? 'selected' : '' }}>Fermé</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Priorité</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="basse" {{ $ticket->priority == 'basse' ? 'selected' : '' }}>Basse</option>
                            <option value="normale" {{ $ticket->priority == 'normale' ? 'selected' : '' }}>Normale</option>
                            <option value="haute" {{ $ticket->priority == 'haute' ? 'selected' : '' }}>Haute</option>
                            <option value="urgente" {{ $ticket->priority == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm w-100">Mettre à jour</button>
                </form>
            </div>
        </div>
        
        <!-- Accès au client -->
        @if($ticket->client)
            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="text-warning fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Accès Exceptionnel</h6>
                    <p class="small text-muted mb-3">Si ce ticket requiert que vous accédiez aux données privées du client, vous devez déclarer un accès temporaire tracé.</p>
                    
                    <form action="{{ route('gel-informaticien.temporary_access.request', $ticket->client_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="reason" value="Résolution du Ticket #{{ $ticket->id }} - {{ $ticket->subject }}">
                        <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold" onclick="return confirm('Confirmez-vous vouloir accéder aux données privées de cette entreprise ? Cette action sera tracée dans le journal d\'audit avec votre identité.')">
                            Demander l'Accès (1h)
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
