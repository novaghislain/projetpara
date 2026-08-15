@extends('gel-accountant.layouts.app')

@section('title', 'Liste des Procès-Verbaux')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Procès-Verbaux et Comptes-Rendus</h1>
            <a href="{{ route('gel-accountant.secretariat.pvs.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Rédiger un PV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Tous les comptes-rendus</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Titre du PV</th>
                            <th>Événement Lié</th>
                            <th>Date du PV</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pvs as $pv)
                            <tr>
                                <td>{{ $pv->titre ?? 'Sans titre' }}</td>
                                <td>
                                    @if($pv->event)
                                        <a href="{{ route('gel-accountant.secretariat.events.show', $pv->event->id) }}">
                                            {{ $pv->event->titre }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $pv->event->date_debut->format('d/m/Y') }}</small>
                                    @else
                                        <span class="text-danger">Événement introuvable</span>
                                    @endif
                                </td>
                                <td>{{ $pv->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge {{ $pv->statut == 'valide' ? 'bg-success' : 'bg-warning text-dark' }} text-white">
                                        {{ ucfirst($pv->statut) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('gel-accountant.secretariat.pvs.show', $pv->id) }}" class="btn btn-sm btn-info" title="Consulter">
                                        <i class="fas fa-eye"></i> Lire
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-file-signature fa-3x text-gray-300 mb-3 d-block"></i>
                                    Aucun procès-verbal n'a été rédigé pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $pvs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
