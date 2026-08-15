@extends('gel-accountant.layouts.app')

@section('title', 'Détails du PV')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Procès-Verbal de Réunion</h1>
            <div>
                <a href="{{ route('gel-accountant.secretariat.events.show', $pv->event->id) }}" class="btn btn-secondary shadow-sm mr-2">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la réunion
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Contenu du PV -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $pv->titre ?? 'Compte-rendu de réunion' }}</h6>
                    <span class="badge {{ $pv->statut == 'valide' ? 'bg-success' : 'bg-warning text-dark' }} text-white p-2">
                        {{ ucfirst($pv->statut) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="bg-light p-4 rounded border mb-4" style="min-height: 400px;">
                        {!! nl2br(e($pv->contenu)) !!}
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-4 pt-3 border-top">
                        <div>
                            Rédigé par {{ $pv->creePar->nom ?? $pv->creePar->email ?? 'Système' }}
                        </div>
                        <div>
                            Dernière modification : {{ $pv->updated_at->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contexte et Actions -->
        <div class="col-lg-4 mb-4">
            <!-- Contexte Réunion -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-calendar-check mr-2"></i> Événement rattaché</h6>
                </div>
                <div class="card-body">
                    <h5 class="font-weight-bold text-primary mb-2">{{ $pv->event->titre }}</h5>
                    <div class="mb-3">
                        <i class="far fa-clock text-muted mr-2"></i> {{ $pv->event->date_debut->format('d/m/Y H:i') }}
                    </div>
                    @if($pv->event->lieu)
                        <div class="mb-3">
                            <i class="fas fa-map-marker-alt text-muted mr-2"></i> {{ $pv->event->lieu }}
                        </div>
                    @endif
                    
                    <a href="{{ route('gel-accountant.secretariat.events.show', $pv->event->id) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                        Voir les détails de l'événement
                    </a>
                </div>
            </div>

            <!-- Export -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-export mr-2"></i> Actions & Export</h6>
                </div>
                <div class="card-body text-center">
                    <button class="btn btn-danger w-100 mb-3 shadow-sm" disabled title="Fonctionnalité d'export PDF à venir">
                        <i class="fas fa-file-pdf"></i> Exporter en PDF
                    </button>
                    
                    @if($pv->statut == 'valide')
                        <button class="btn btn-info w-100 shadow-sm" disabled title="Liaison avec SEC-DOC et SEC-COURRIER à venir">
                            <i class="fas fa-paper-plane"></i> Diffuser (GED / Courrier)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
