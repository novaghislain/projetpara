@extends('layouts.gel')

@section('title', 'Nouveau compte — Plan comptable')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Nouveau compte comptable</h1>
        <p class="page-subtitle">Ajoutez un compte au plan comptable SYSCOHADA.</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.plan-comptable.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-4">
        <form action="{{ route('gel.comptabilite.plan-comptable.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code') }}" placeholder="ex: 4011" maxlength="10" required>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Code numérique unique selon le plan SYSCOHADA.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Intitulé <span class="text-danger">*</span></label>
                    <input type="text" name="intitule" class="form-control @error('intitule') is-invalid @enderror"
                           value="{{ old('intitule') }}" placeholder="Nom du compte" required>
                    @error('intitule') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                    <select name="classe" class="form-select @error('classe') is-invalid @enderror" required>
                        <option value="">Sélectionnez...</option>
                        <option value="1" {{ old('classe') == '1' ? 'selected' : '' }}>1 — Capitaux</option>
                        <option value="2" {{ old('classe') == '2' ? 'selected' : '' }}>2 — Immobilisations</option>
                        <option value="3" {{ old('classe') == '3' ? 'selected' : '' }}>3 — Stocks</option>
                        <option value="4" {{ old('classe') == '4' ? 'selected' : '' }}>4 — Tiers</option>
                        <option value="5" {{ old('classe') == '5' ? 'selected' : '' }}>5 — Trésorerie</option>
                        <option value="6" {{ old('classe') == '6' ? 'selected' : '' }}>6 — Charges</option>
                        <option value="7" {{ old('classe') == '7' ? 'selected' : '' }}>7 — Produits</option>
                        <option value="8" {{ old('classe') == '8' ? 'selected' : '' }}>8 — Résultats</option>
                    </select>
                    @error('classe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Niveau</label>
                    <select name="niveau" class="form-select">
                        <option value="1" {{ old('niveau') == '1' ? 'selected' : '' }}>1 — Classe</option>
                        <option value="2" {{ old('niveau') == '2' ? 'selected' : '' }}>2 — Rubrique</option>
                        <option value="3" {{ old('niveau') == '3' ? 'selected' : '' }}>3 — Sous-rubrique</option>
                        <option value="4" {{ old('niveau') == '4' ? 'selected' : '' }}>4 — Compte</option>
                        <option value="5" {{ old('niveau') == '5' ? 'selected' : '' }}>5 — Sous-compte</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Compte parent</label>
                    <select name="compte_parent_id" class="form-select">
                        <option value="">Aucun</option>
                        @foreach($comptesParents ?? [] as $parent)
                        <option value="{{ $parent['id'] }}" {{ old('compte_parent_id') == $parent['id'] ? 'selected' : '' }}>
                            {{ $parent['label'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nature du solde</label>
                    <div class="pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="solde_debiteur" value="1"
                                   {{ old('solde_debiteur', '1') == '1' ? 'checked' : '' }} id="soldeDeb">
                            <label class="form-check-label" for="soldeDeb">Débiteur</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="solde_debiteur" value="0"
                                   {{ old('solde_debiteur') === '0' ? 'checked' : '' }} id="soldeCred">
                            <label class="form-check-label" for="soldeCred">Créditeur</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Actif</label>
                    <div class="pt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="actif" value="1"
                                   {{ old('actif', '1') == '1' ? 'checked' : '' }} id="actifSwitch" checked>
                            <label class="form-check-label" for="actifSwitch">Compte actif</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Créer le compte
                </button>
                <a href="{{ route('gel.comptabilite.plan-comptable.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
