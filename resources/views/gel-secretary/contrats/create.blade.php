@extends('layouts.gel-secretary')
@section('title', 'Nouveau Contrat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title"><i class="fas fa-file-contract" style="color:var(--sec-primary); margin-right:8px;"></i>Nouveau Contrat</h1>
    <p class="sec-page-sub">Ajouter un nouveau contrat au registre.</p>
  </div>
  <div>
    <a href="{{ route('gel-secretary.contrats.index') }}" class="sec-btn sec-btn-outline">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>
</div>

<div class="sec-card animate-fade delay-2">
  <div class="sec-card-body">
    <form action="{{ route('gel-secretary.contrats.store') }}" method="POST">
      @csrf
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Client Associé</label>
          <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
            <option value="">Sélectionnez un client...</option>
            @foreach($clients as $client)
              <option value="{{ $client->id }}">{{ $client->entreprise_nom_commercial }}</option>
            @endforeach
          </select>
          @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Titre du contrat</label>
          <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror" value="{{ old('titre') }}" required>
          @error('titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Type de contrat</label>
          <input type="text" name="type_contrat" class="form-control @error('type_contrat') is-invalid @enderror" value="{{ old('type_contrat') }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Partie Adverse</label>
          <input type="text" name="partie_adverse" class="form-control @error('partie_adverse') is-invalid @enderror" value="{{ old('partie_adverse') }}" required>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Date de Signature</label>
          <input type="date" name="date_signature" class="form-control" value="{{ old('date_signature') }}">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Date de Début</label>
          <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut') }}">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Date de Fin</label>
          <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}">
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Montant (Optionnel)</label>
          <div class="input-group">
            <input type="number" step="0.01" name="montant" class="form-control" value="{{ old('montant') }}">
            <span class="input-group-text">FCFA</span>
          </div>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Statut</label>
          <select name="statut" class="form-select" required>
            <option value="actif">Actif</option>
            <option value="brouillon">Brouillon</option>
            <option value="termine">Terminé</option>
            <option value="suspendu">Suspendu</option>
          </select>
        </div>
      </div>
      
      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="sec-btn sec-btn-primary">
          <i class="fas fa-save me-2"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
