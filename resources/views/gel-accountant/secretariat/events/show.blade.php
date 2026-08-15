@extends('gel-accountant.layouts.app')

@section('title', 'Détails de l\'événement')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Détails de l'événement</h1>
            <div>
                <a href="{{ route('gel-accountant.secretariat.events.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à l'agenda
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Event Details -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary">
                    <h6 class="m-0 font-weight-bold text-white"><i class="far fa-calendar-alt mr-2"></i> {{ $event->titre }}</h6>
                    @if($event->date_debut > now())
                        <span class="badge bg-success text-white">À venir</span>
                    @else
                        <span class="badge bg-secondary text-white">Passé</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-3 rounded mr-3 text-center border">
                                    <div class="text-primary font-weight-bold" style="font-size: 1.5rem;">{{ $event->date_debut->format('d') }}</div>
                                    <div class="text-uppercase small">{{ $event->date_debut->format('M Y') }}</div>
                                </div>
                                <div>
                                    <h5 class="mb-1">Horaires</h5>
                                    <div class="text-muted">
                                        De {{ $event->date_debut->format('H:i') }} à {{ $event->date_fin->format('H:i') }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Durée: {{ $event->date_debut->diffInHours($event->date_fin) }}h{{ $event->date_debut->diff($event->date_fin)->format('%I') != '00' ? $event->date_debut->diff($event->date_fin)->format('%I') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 border-left-md pl-md-4">
                            <h5 class="mb-2"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Lieu</h5>
                            <p class="text-muted">{{ $event->lieu ?? 'Non spécifié' }}</p>
                            
                            @if($event->contact)
                                <h5 class="mb-2 mt-3"><i class="fas fa-user-tie text-primary mr-2"></i> Contact Lié</h5>
                                <a href="{{ route('gel-accountant.secretariat.contacts.show', $event->contact->id) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $event->contact->nom }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <h5 class="border-bottom pb-2 mb-3">Ordre du jour / Description</h5>
                    @if($event->description)
                        <div class="p-3 bg-light rounded border">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    @else
                        <p class="text-muted font-italic">Aucune description fournie.</p>
                    @endif

                    <div class="mt-4 pt-3 border-top text-muted small">
                        Planifié par {{ $event->creePar->nom ?? $event->creePar->email ?? 'Système' }} le {{ $event->created_at->format('d/m/Y à H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- PV Section -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 border-left-success">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-file-signature mr-2"></i> Procès-Verbaux (PV)</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Gérez les comptes-rendus et décisions prises lors de cet événement.</p>
                    
                    @if($event->pvs && $event->pvs->count() > 0)
                        <div class="list-group mb-4">
                            @foreach($event->pvs as $pv)
                                <a href="{{ route('gel-accountant.secretariat.pvs.show', $pv->id) }}" class="list-group-item list-group-item-action border-left-success">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $pv->titre ?? 'Compte-rendu de réunion' }}</h6>
                                        <small>{{ $pv->created_at->format('d/m/Y') }}</small>
                                    </div>
                                    <span class="badge {{ $pv->statut == 'valide' ? 'bg-success' : 'bg-warning text-dark' }} text-white">
                                        {{ ucfirst($pv->statut) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 mb-4 bg-light rounded border">
                            <i class="fas fa-file-alt fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted mb-0 small">Aucun PV rédigé pour le moment.</p>
                        </div>
                    @endif

                    <div class="text-center">
                        <a href="{{ route('gel-accountant.secretariat.pvs.create', ['event_id' => $event->id]) }}" class="btn btn-success w-100 shadow-sm">
                            <i class="fas fa-plus-circle"></i> Rédiger un PV
                        </a>
                        <div class="mt-3 text-left">
                            <span class="badge bg-info text-white mb-2"><i class="fas fa-robot"></i> SEC-PV-01 / IA</span>
                            <p class="small text-muted mb-0">La rédaction assistée par IA (transcription ou résumé automatique) sera bientôt disponible lors de la rédaction du PV.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
