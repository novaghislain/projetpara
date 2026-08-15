@extends('layouts.gel-secretary')
@section('title', 'Modifier le Contrat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title"><i class="fas fa-file-contract" style="color:var(--sec-primary); margin-right:8px;"></i>Modifier Contrat</h1>
  </div>
  <div>
    <a href="{{ route('gel-secretary.contrats.index') }}" class="sec-btn sec-btn-outline">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>
</div>

<div class="sec-card animate-fade delay-2">
  <div class="sec-card-body">
    <form action="{{ route('gel-secretary.contrats.update', $contrat->id) }}" method="POST">
      @csrf
      @method('PUT')
      
      <div class="row">
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Titre du contrat</label>
          <input type="text" name="titre" class="form-control" value="{{ $contrat->titre }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Type de contrat</label>
          <input type="text" name="type_contrat" class="form-control" value="{{ $contrat->type_contrat }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Partie Adverse</label>
          <input type="text" name="partie_adverse" class="form-control" value="{{ $contrat->partie_adverse }}" required>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Date de Signature</label>
          <input type="date" name="date_signature" class="form-control" value="{{ $contrat->date_signature ? $contrat->date_signature->format('Y-m-d') : '' }}">
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Statut</label>
          <select name="statut" class="form-select" required>
            <option value="actif" {{ $contrat->statut == 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="brouillon" {{ $contrat->statut == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
            <option value="termine" {{ $contrat->statut == 'termine' ? 'selected' : '' }}>Terminé</option>
          </select>
        </div>
      </div>
      
      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="sec-btn sec-btn-primary">
          <i class="fas fa-save me-2"></i> Mettre à jour
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
