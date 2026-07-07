@extends('layouts.gel')

@section('title', 'Nouveau journal — Comptabilité')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Nouveau journal</h1>
        <p class="page-subtitle">Créez un journal comptable.</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.journaux.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-4">
        <form action="{{ route('gel.comptabilite.journaux.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                    <select name="code" class="form-select @error('code') is-invalid @enderror" required>
                        <option value="">Sélectionnez...</option>
                        @foreach($codesDisponibles as $code => $lib)
                        <option value="{{ $code }}" {{ old('code') == $code ? 'selected' : '' }}>{{ $code }} — {{ $lib }}</option>
                        @endforeach
                    </select>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror" value="{{ old('libelle') }}" required>
                    @error('libelle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Type</label>
                    <select name="type" class="form-select">
                        <option value="">—</option>
                        <option value="achats" {{ old('type') == 'achats' ? 'selected' : '' }}>Achats</option>
                        <option value="ventes" {{ old('type') == 'ventes' ? 'selected' : '' }}>Ventes</option>
                        <option value="banque" {{ old('type') == 'banque' ? 'selected' : '' }}>Banque</option>
                        <option value="caisse" {{ old('type') == 'caisse' ? 'selected' : '' }}>Caisse</option>
                        <option value="divers" {{ old('type') == 'divers' ? 'selected' : '' }}>Opérations diverses</option>
                        <option value="paie" {{ old('type') == 'paie' ? 'selected' : '' }}>Paie</option>
                        <option value="immobilisations" {{ old('type') == 'immobilisations' ? 'selected' : '' }}>Immobilisations</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Créer</button>
                <a href="{{ route('gel.comptabilite.journaux.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
