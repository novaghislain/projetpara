@extends('layouts.gel')

@section('title', "Modifier {$compte->code} — Plan comptable")

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Modifier {{ $compte->code }} — {{ $compte->intitule }}</h1>
        <p class="page-subtitle">Mettez à jour les informations du compte.</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.plan-comptable.show', $compte->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-4">
        <form action="{{ route('gel.comptabilite.plan-comptable.update', $compte->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $compte->code) }}" maxlength="10" required>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Intitulé <span class="text-danger">*</span></label>
                    <input type="text" name="intitule" class="form-control @error('intitule') is-invalid @enderror"
                           value="{{ old('intitule', $compte->intitule) }}" required>
                    @error('intitule') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Classe</label>
                    <select name="classe" class="form-select" required>
                        @foreach(['1'=>'Capitaux','2'=>'Immobilisations','3'=>'Stocks','4'=>'Tiers','5'=>'Trésorerie','6'=>'Charges','7'=>'Produits','8'=>'Résultats'] as $val => $lib)
                        <option value="{{ $val }}" {{ old('classe', $compte->classe) == $val ? 'selected' : '' }}>{{ $val }} — {{ $lib }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Niveau</label>
                    <select name="niveau" class="form-select">
                        @for($i=1;$i<=5;$i++)
                        <option value="{{ $i }}" {{ old('niveau', $compte->niveau) == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Compte parent</label>
                    <select name="compte_parent_id" class="form-select">
                        <option value="">Aucun</option>
                        @foreach($comptesParents ?? [] as $parent)
                        <option value="{{ $parent['id'] }}" {{ old('compte_parent_id', $compte->compte_parent_id) == $parent['id'] ? 'selected' : '' }}>
                            {{ $parent['label'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nature</label>
                    <div class="pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="solde_debiteur" value="1" {{ old('solde_debiteur', $compte->solde_debiteur) ? 'checked' : '' }} id="soldeDeb">
                            <label class="form-check-label" for="soldeDeb">Débiteur</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="solde_debiteur" value="0" {{ !old('solde_debiteur', $compte->solde_debiteur) ? 'checked' : '' }} id="soldeCred">
                            <label class="form-check-label" for="soldeCred">Créditeur</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Actif</label>
                    <div class="pt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="actif" value="1"
                                   {{ old('actif', $compte->actif) ? 'checked' : '' }} id="actifSwitch">
                            <label class="form-check-label" for="actifSwitch">Compte actif</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Enregistrer
                </button>
                <a href="{{ route('gel.comptabilite.plan-comptable.show', $compte->id) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
