@extends('layouts.gel-client')

@section('title', 'Nouvelle Facture')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Nouvelle Facture</h1>
            <p class="text-muted mt-1">Générez une facture pour votre client.</p>
        </div>
        <a href="{{ route('gel-client.ventes.factures.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Retour</a>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <form action="{{ route('gel-client.ventes.factures.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Client</label>
                        <input type="text" class="form-control" name="client_nom" placeholder="Nom du client" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date de facturation</label>
                        <input type="date" class="form-control" name="date_facture" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Échéance</label>
                        <input type="date" class="form-control" name="date_echeance">
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">Lignes de facture</h5>
                    <!-- Placeholder pour les lignes -->
                    <div class="row align-items-end mb-2">
                        <div class="col-md-5">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" name="lignes[0][description]" placeholder="Description du service/produit">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantité</label>
                            <input type="number" class="form-control" name="lignes[0][qte]" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prix unitaire (FCFA)</label>
                            <input type="number" class="form-control" name="lignes[0][prix]">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i> Ajouter une ligne</button>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Notes (Visibles par le client)</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <strong>0 FCFA</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>TVA (18%):</span>
                                <strong>0 FCFA</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-primary fs-5">
                                <strong>Total TTC:</strong>
                                <strong>0 FCFA</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-5">Enregistrer la facture</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
