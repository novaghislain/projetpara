@extends('layouts.gel-admin')

@section('title', 'Abonnement & Facturation')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Mon Abonnement</h1>
        <p class="admin-page-sub">Gérez votre formule et accédez à vos factures.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="admin-card h-100 border-primary shadow-sm" style="border-width: 2px;">
            <div class="admin-card-header bg-primary text-white border-0">
                <div class="admin-card-title text-white">Forfait Actuel : <strong>Standard</strong></div>
            </div>
            <div class="admin-card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="display-6 fw-bold text-dark mb-0">50 000 FCFA</h2>
                        <div class="text-muted">/ mois</div>
                    </div>
                    <span class="badge bg-success py-2 px-3">Actif</span>
                </div>
                
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Accès Secrétariat (3 utilisateurs)</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Portail Client personnalisé</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Gestion de 50 clients</li>
                    <li class="mb-2 text-muted"><i class="fas fa-times text-danger me-2"></i> Module Comptabilité Avancée</li>
                </ul>

                <button class="admin-btn btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#changePlanModal">Changer d'offre</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Changer de plan -->
<div class="modal fade" id="changePlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Évoluer vers une offre supérieure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm text-center p-4 h-100">
                            <h4 class="fw-bold">Premium</h4>
                            <div class="display-6 fw-bold text-primary my-3">100 000 <small class="fs-6">FCFA/mois</small></div>
                            <ul class="list-unstyled text-start mb-4 mx-auto" style="max-width: 200px;">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Utilisateurs illimités</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Clients illimités</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Comptabilité intégrée</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Support prioritaire</li>
                            </ul>
                            <a href="{{ route('gel-admin.subscription.payment.show', ['plan_id' => 'premium']) }}" class="admin-btn admin-btn-primary w-100 justify-content-center">Choisir cette offre</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
