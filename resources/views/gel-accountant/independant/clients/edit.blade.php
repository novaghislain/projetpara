@extends('layouts.gel-accountant')

@section('title', 'Modifier ' . $client->nom_entreprise)

@section('content')
<style>
  .pro-panel {
    background: white; border-radius: 8px; border: 1px solid var(--gel-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;
  }
  .panel-body { padding: 24px; }
  .form-label { font-weight: 500; font-size: 13px; color: var(--gel-text-secondary); }
</style>

<div class="gel-page-header" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <a href="{{ route('gel-accountant.independant.clients.show', $client->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Annuler</a>
        <h1 class="gel-page-title" style="font-size: 18px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">Éditer le dossier client</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="pro-panel">
            <div class="panel-body">
                <form action="{{ route('gel-accountant.independant.clients.update', $client->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <h5 class="mb-4" style="font-size: 15px; font-weight: 600; color: var(--gel-text-primary); border-bottom: 1px solid #eee; padding-bottom: 10px;">Informations de l'entreprise</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                            <input type="text" name="nom_entreprise" class="form-control @error('nom_entreprise') is-invalid @enderror" value="{{ old('nom_entreprise', $client->nom_entreprise) }}" required>
                            @error('nom_entreprise') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secteur d'activité</label>
                            <input type="text" name="secteur" class="form-control" value="{{ old('secteur', $client->secteur) }}">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Numéro IFU</label>
                            <input type="text" name="ifu" class="form-control" value="{{ old('ifu', $client->ifu) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RCCM</label>
                            <input type="text" name="rccm" class="form-control" value="{{ old('rccm', $client->rccm) }}">
                        </div>
                    </div>

                    <h5 class="mt-5 mb-4" style="font-size: 15px; font-weight: 600; color: var(--gel-text-primary); border-bottom: 1px solid #eee; padding-bottom: 10px;">Contact principal</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="contact_nom" class="form-control" value="{{ old('contact_nom', $client->contact_nom) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $client->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $client->telephone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse complète</label>
                            <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $client->adresse) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notes ou particularités du dossier</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $client->notes) }}</textarea>
                    </div>
                    
                    <div class="text-end mt-4 pt-3" style="border-top: 1px solid #eee;">
                        <button type="submit" class="gel-btn gel-btn-primary" style="font-weight: 600; padding: 8px 24px;">Mettre à jour le client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
