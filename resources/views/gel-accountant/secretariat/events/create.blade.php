@extends('gel-accountant.layouts.app')

@section('title', 'Planifier un événement')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Planifier un événement</h1>
            <a href="{{ route('gel-accountant.secretariat.events.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à l'agenda
            </a>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détails de l'événement</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('gel-accountant.secretariat.events.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-8 form-group mb-3">
                        <label for="titre">Titre de l'événement / Objet de la réunion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titre" name="titre" required value="{{ old('titre') }}">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="lieu">Lieu / Lien de visioconférence</label>
                        <input type="text" class="form-control" id="lieu" name="lieu" value="{{ old('lieu') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="date_debut">Date et heure de début <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" required value="{{ old('date_debut') }}">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="date_fin">Date et heure de fin <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" required value="{{ old('date_fin') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 form-group mb-3">
                        <label for="description">Description / Ordre du jour</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    </div>
                </div>

                <h5 class="mb-3 mt-4 border-bottom pb-2">Liaison (Optionnelle)</h5>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="contact_id">Lier à un contact existant</label>
                        <select name="contact_id" id="contact_id" class="form-control">
                            <option value="">-- Ne pas lier --</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}" {{ old('contact_id') == $contact->id ? 'selected' : '' }}>
                                    {{ $contact->nom }} ({{ ucfirst($contact->type) }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">L'événement apparaîtra dans la fiche 360° du contact sélectionné.</small>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Enregistrer l'événement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
