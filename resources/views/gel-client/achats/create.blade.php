@extends('layouts.gel-client')

@section('title', 'Soumettre une Dépense')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Soumettre une Dépense</h1>
            <p class="text-muted mt-1">Enregistrez un achat et transmettez le justificatif à votre cabinet.</p>
        </div>
        <a href="{{ route('gel-client.achats.depenses.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Retour</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <form action="{{ route('gel-client.achats.depenses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fournisseur</label>
                                <input type="text" class="form-control" name="fournisseur" placeholder="Nom du commerçant ou fournisseur" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date de la dépense</label>
                                <input type="date" class="form-control" name="date_depense" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant TTC (FCFA)</label>
                                <input type="number" class="form-control" name="montant_ttc" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catégorie (Optionnel)</label>
                                <select class="form-select" name="categorie">
                                    <option value="">Sélectionner...</option>
                                    <option value="fournitures">Fournitures de bureau</option>
                                    <option value="deplacement">Frais de déplacement</option>
                                    <option value="repas">Repas d'affaires</option>
                                    <option value="materiel">Matériel informatique</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Justificatif (Facture / Reçu) <span class="text-danger">*</span></label>
                            <input class="form-control" type="file" name="file" required>
                            <div class="form-text">Formats acceptés : PDF, JPG, PNG (Max: 5 Mo)</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description / Motif</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Ex: Déjeuner client M. Dupont"></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-danger px-5">Soumettre la dépense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="fas fa-info-circle me-2"></i> À savoir</h5>
                    <p class="small text-muted mb-2">Toute dépense soumise sera automatiquement classée dans la GED et notifiée à votre cabinet comptable pour saisie.</p>
                    <p class="small text-muted mb-0">Assurez-vous que le justificatif est lisible (montant, date, et TVA si applicable bien visibles).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
