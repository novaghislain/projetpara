@extends('layouts.gel-accountant')

@section('title', 'Nouvelle Règle Fiscale')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Ajouter une Règle Fiscale</h1>
            <a href="{{ route('gel-accountant.fiscalite.regles-fiscales.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour
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
            <h6 class="m-0 font-weight-bold text-primary">Paramètres de la règle</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('gel-accountant.fiscalite.regles-fiscales.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="code_pays">Code Pays (ISO 2) <span class="text-danger">*</span></label>
                        <select name="code_pays" id="code_pays" class="form-control" required>
                            <option value="BJ" {{ old('code_pays') == 'BJ' ? 'selected' : '' }}>BJ - Bénin</option>
                            <option value="CI" {{ old('code_pays') == 'CI' ? 'selected' : '' }}>CI - Côte d'Ivoire</option>
                            <option value="SN" {{ old('code_pays') == 'SN' ? 'selected' : '' }}>SN - Sénégal</option>
                            <option value="TG" {{ old('code_pays') == 'TG' ? 'selected' : '' }}>TG - Togo</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="type_impot">Type d'Impôt <span class="text-danger">*</span></label>
                        <select name="type_impot" id="type_impot" class="form-control" required>
                            <option value="TVA" {{ old('type_impot') == 'TVA' ? 'selected' : '' }}>TVA</option>
                            <option value="AIB" {{ old('type_impot') == 'AIB' ? 'selected' : '' }}>AIB (Acompte sur Impôt)</option>
                            <option value="IS" {{ old('type_impot') == 'IS' ? 'selected' : '' }}>IS (Impôt Sociétés)</option>
                            <option value="ITS" {{ old('type_impot') == 'ITS' ? 'selected' : '' }}>ITS (Impôt sur les Traitements)</option>
                            <option value="CNSS" {{ old('type_impot') == 'CNSS' ? 'selected' : '' }}>CNSS (Cotisations)</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="taux">Taux décimal (ex: 0.18 pour 18%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0" max="1" class="form-control" id="taux" name="taux" required value="{{ old('taux') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="date_debut_validite">Date de début de validité <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_debut_validite" name="date_debut_validite" required value="{{ old('date_debut_validite') }}">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="date_fin_validite">Date de fin de validité</label>
                        <input type="date" class="form-control" id="date_fin_validite" name="date_fin_validite" value="{{ old('date_fin_validite') }}">
                        <small class="form-text text-muted">Laisser vide si la règle est toujours en vigueur à ce jour.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="source_reglementaire">Source réglementaire</label>
                        <input type="text" class="form-control" id="source_reglementaire" name="source_reglementaire" value="{{ old('source_reglementaire') }}" placeholder="Ex: Loi de finances 2026 Art. 12">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="conditions_json">Conditions Spécifiques (JSON)</label>
                        <textarea class="form-control text-monospace" id="conditions_json" name="conditions_json" rows="2" placeholder='Ex: {"centre_impots": "DGE"}'>{{ old('conditions_json') }}</textarea>
                        <small class="form-text text-muted">Laissez vide pour une règle générale s'appliquant à tous.</small>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle mr-2"></i> <strong>Note de versioning :</strong> Si une règle avec les mêmes critères (Pays, Impôt, Conditions) existe déjà et couvre cette période, elle sera automatiquement passée en statut <em>Obsolète</em> et cette nouvelle règle deviendra la version active supérieure (v+1).
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer la règle fiscale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
