@extends('gel-accountant.layouts.app')

@section('title', 'Rédiger un PV')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Rédiger un Procès-Verbal</h1>
            @if($event)
                <a href="{{ route('gel-accountant.secretariat.events.show', $event->id) }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à l'événement
                </a>
            @else
                <a href="{{ route('gel-accountant.secretariat.pvs.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour aux PV
                </a>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Main Form -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contenu du compte-rendu</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('gel-accountant.secretariat.pvs.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="event_id">Événement rattaché <span class="text-danger">*</span></label>
                            <select name="event_id" id="event_id" class="form-control" required>
                                <option value="">-- Sélectionner une réunion --</option>
                                @foreach($events as $evt)
                                    <option value="{{ $evt->id }}" {{ (old('event_id') == $evt->id) || ($event && $event->id == $evt->id) ? 'selected' : '' }}>
                                        {{ $evt->titre }} ({{ $evt->date_debut->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="titre">Titre du PV (Optionnel)</label>
                            <input type="text" class="form-control" id="titre" name="titre" value="{{ old('titre') }}" placeholder="Ex: Compte-rendu de la réunion du Q3">
                        </div>

                        <div class="form-group mb-3">
                            <label for="contenu">Contenu complet du PV <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="contenu" name="contenu" rows="15" required placeholder="Saisissez les notes, décisions et prochaines étapes ici...">{{ old('contenu') }}</textarea>
                            <small class="form-text text-muted">Vous pouvez structurer votre PV (Points abordés, Décisions, Tâches attribuées).</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="statut">Statut du document <span class="text-danger">*</span></label>
                            <select name="statut" id="statut" class="form-control">
                                <option value="brouillon" {{ old('statut') == 'brouillon' ? 'selected' : '' }}>Brouillon (en cours de rédaction)</option>
                                <option value="valide" {{ old('statut') == 'valide' ? 'selected' : '' }}>Validé (définitif)</option>
                            </select>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer le PV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assistant IA -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-info">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-robot mr-2"></i> Assistant IA (SEC-PV-01)</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-magic fa-3x text-info mb-3"></i>
                        <h5>Rédiger avec l'IA</h5>
                        <p class="text-muted small">L'IA de Gel peut structurer automatiquement vos notes brutes ou générer un résumé de la réunion.</p>
                    </div>
                    
                    <button class="btn btn-outline-info w-100 mb-2" disabled title="Fonctionnalité IA à venir">
                        <i class="fas fa-microphone"></i> Transcrire un enregistrement audio
                    </button>
                    <button class="btn btn-outline-info w-100 mb-4" disabled title="Fonctionnalité IA à venir">
                        <i class="fas fa-align-left"></i> Structurer mes notes brutes
                    </button>

                    <hr>
                    <div class="text-left mt-3">
                        <span class="badge bg-secondary text-white mb-2">SEC-PV-02</span>
                        <p class="small text-muted mb-0">Une fois le PV validé, l'IA pourra identifier et assigner automatiquement les tâches (SEC-TACHE) aux collaborateurs concernés.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
