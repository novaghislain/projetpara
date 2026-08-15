@extends('gel-accountant.layouts.app')

@section('title', 'Agenda des Événements')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Agenda & Réunions</h1>
            <a href="{{ route('gel-accountant.secretariat.events.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-calendar-plus fa-sm text-white-50"></i> Planifier un événement
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-2"></i> Liste des événements à venir</h6>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary active">Vue Liste</button>
                <button type="button" class="btn btn-outline-primary" disabled title="Bientôt disponible">Vue Calendrier</button>
            </div>
        </div>
        <div class="card-body">
            <div class="list-group">
                @forelse($events as $event)
                    <div class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div class="mb-2 mb-md-0">
                            <div class="d-flex align-items-center mb-1">
                                <div class="bg-primary text-white text-center rounded mr-3" style="min-width: 50px; padding: 5px;">
                                    <div class="font-weight-bold" style="font-size: 1.2rem;">{{ $event->date_debut->format('d') }}</div>
                                    <div class="small text-uppercase">{{ $event->date_debut->format('M') }}</div>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-primary">{{ $event->titre }}</h5>
                                    <div class="text-muted small">
                                        <i class="far fa-clock mr-1"></i> {{ $event->date_debut->format('H:i') }} - {{ $event->date_fin->format('H:i') }}
                                        @if($event->lieu)
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $event->lieu }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-md-right mt-2 mt-md-0">
                            @if($event->contact)
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-user mr-1 text-primary"></i> {{ $event->contact->nom }}
                                    </span>
                                </div>
                            @endif
                            <a href="{{ route('gel-accountant.secretariat.events.show', $event->id) }}" class="btn btn-sm btn-outline-primary">
                                Détails & PV
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="far fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                        <p class="text-muted mb-0">Aucun événement planifié.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
