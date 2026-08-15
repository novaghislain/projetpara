@extends('gel-accountant.layouts.app')

@section('title', 'Nouveau Contact')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Ajouter un Contact</h1>
            <a href="{{ route('gel-accountant.secretariat.contacts.index') }}" class="btn btn-secondary shadow-sm">
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
            <h6 class="m-0 font-weight-bold text-primary">Informations du Contact</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('gel-accountant.secretariat.contacts.store') }}" method="POST">
                @csrf
                
                <h5 class="mb-3 border-bottom pb-2">Identité & Coordonnées</h5>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="nom">Nom / Raison Sociale <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" required value="{{ old('nom') }}">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="type">Type de Contact <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="client" {{ old('type') == 'client' ? 'selected' : '' }}>Client</option>
                            <option value="fournisseur" {{ old('type') == 'fournisseur' ? 'selected' : '' }}>Fournisseur</option>
                            <option value="partenaire" {{ old('type') == 'partenaire' ? 'selected' : '' }}>Partenaire</option>
                            <option value="administration" {{ old('type') == 'administration' ? 'selected' : '' }}>Administration (Impôts, CNSS, etc.)</option>
                            <option value="autre" {{ old('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="telephone">Téléphone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone" value="{{ old('telephone') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 form-group mb-3">
                        <label for="site_web">Site Web</label>
                        <input type="url" class="form-control" id="site_web" name="site_web" value="{{ old('site_web') }}">
                    </div>
                </div>

                <h5 class="mb-3 mt-4 border-bottom pb-2">Adresse</h5>
                <div class="row">
                    <div class="col-md-12 form-group mb-3">
                        <label for="adresse">Adresse complète</label>
                        <input type="text" class="form-control" id="adresse" name="adresse" value="{{ old('adresse') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="code_postal">Boîte / Code Postal</label>
                        <input type="text" class="form-control" id="code_postal" name="code_postal" value="{{ old('code_postal') }}">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="ville">Ville</label>
                        <input type="text" class="form-control" id="ville" name="ville" value="{{ old('ville') ?? 'Cotonou' }}">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="pays">Pays</label>
                        <input type="text" class="form-control" id="pays" name="pays" value="{{ old('pays') ?? 'Bénin' }}">
                    </div>
                </div>

                <h5 class="mb-3 mt-4 border-bottom pb-2">Informations Commerciales & Financières</h5>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="ifu">IFU (Numéro Fiscal)</label>
                        <input type="text" class="form-control" id="ifu" name="ifu" value="{{ old('ifu') }}">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="devise_facturation">Devise</label>
                        <select name="devise_facturation" id="devise_facturation" class="form-control">
                            <option value="XOF" {{ old('devise_facturation') == 'XOF' ? 'selected' : '' }}>FCFA (XOF)</option>
                            <option value="EUR" {{ old('devise_facturation') == 'EUR' ? 'selected' : '' }}>Euro (€)</option>
                            <option value="USD" {{ old('devise_facturation') == 'USD' ? 'selected' : '' }}>US Dollar ($)</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="delai_paiement">Délai de paiement par défaut</label>
                        <select name="delai_paiement" id="delai_paiement" class="form-control">
                            <option value="Comptant" {{ old('delai_paiement') == 'Comptant' ? 'selected' : '' }}>Comptant</option>
                            <option value="Net 30 jours" {{ old('delai_paiement') == 'Net 30 jours' ? 'selected' : '' }}>Net 30 jours</option>
                            <option value="Net 60 jours" {{ old('delai_paiement') == 'Net 60 jours' ? 'selected' : '' }}>Net 60 jours</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="methode_paiement">Méthode de paiement</label>
                        <input type="text" class="form-control" id="methode_paiement" name="methode_paiement" value="{{ old('methode_paiement') ?? 'Virement Bancaire' }}">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="iban">IBAN / RIB</label>
                        <input type="text" class="form-control" id="iban" name="iban" value="{{ old('iban') }}" placeholder="BJ06...">
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="swift">Code SWIFT</label>
                        <input type="text" class="form-control" id="swift" name="swift" value="{{ old('swift') }}">
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer le contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
