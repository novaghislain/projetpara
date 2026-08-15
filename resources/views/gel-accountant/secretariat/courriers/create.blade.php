@extends('gel-accountant.layouts.app')

@section('title', 'Nouveau Courrier - Secrétariat')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Enregistrer un Courrier</h1>
            <a href="{{ route('gel-accountant.secretariat.courriers.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour au Registre
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
            <h6 class="m-0 font-weight-bold text-primary">Informations du Courrier</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('gel-accountant.secretariat.courriers.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="">Sélectionner</option>
                            <option value="entrant" {{ old('type') == 'entrant' ? 'selected' : '' }}>Entrant</option>
                            <option value="sortant" {{ old('type') == 'sortant' ? 'selected' : '' }}>Sortant</option>
                            <option value="interne" {{ old('type') == 'interne' ? 'selected' : '' }}>Interne</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="priorite" class="form-label">Priorité <span class="text-danger">*</span></label>
                        <select class="form-control" id="priorite" name="priorite" required>
                            <option value="basse" {{ old('priorite') == 'basse' ? 'selected' : '' }}>Basse</option>
                            <option value="normale" {{ old('priorite', 'normale') == 'normale' ? 'selected' : '' }}>Normale</option>
                            <option value="haute" {{ old('priorite') == 'haute' ? 'selected' : '' }}>Haute</option>
                            <option value="urgente" {{ old('priorite') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="date_reception_envoi" class="form-label">Date (Réception / Envoi)</label>
                        <input type="date" class="form-control" id="date_reception_envoi" name="date_reception_envoi" value="{{ old('date_reception_envoi', date('Y-m-d')) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="expediteur_destinataire" class="form-label">Expéditeur / Destinataire <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="expediteur_destinataire" name="expediteur_destinataire" value="{{ old('expediteur_destinataire') }}" required placeholder="Nom de l'entité ou de la personne">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="categorie" class="form-label">Catégorie</label>
                        <input type="text" class="form-control" id="categorie" name="categorie" value="{{ old('categorie') }}" placeholder="Ex: Facture, Relance, Administratif">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="objet" class="form-label">Objet du Courrier <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="objet" name="objet" value="{{ old('objet') }}" required>
                </div>
                
                <div class="mb-3">
                    <label for="assigne_a" class="form-label">Affecter à (Optionnel)</label>
                    <select class="form-control" id="assigne_a" name="assigne_a">
                        <option value="">-- Ne pas affecter pour le moment --</option>
                        @foreach($utilisateurs as $user)
                            <option value="{{ $user->id }}" {{ old('assigne_a') == $user->id ? 'selected' : '' }}>{{ $user->nom ?? $user->email }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Vous pourrez affecter ce courrier plus tard dans le workflow.</small>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer et démarrer le Workflow
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
