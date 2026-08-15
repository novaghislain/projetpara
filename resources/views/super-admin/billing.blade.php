@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Abonnement & Facturation</h2>
            <p class="text-muted">Gérez votre plan SaaS, vos quotas et vos factures GEL Cabinet.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <h5 class="fw-bold text-uppercase text-muted mb-3">Essentiel</h5>
                <h2 class="display-5 fw-bolder mb-3">25.000 <small class="fs-6 text-muted">FCFA/mois</small></h2>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Jusqu'à 10 Dossiers Clients</li>
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> 3 Utilisateurs</li>
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Comptabilité de base</li>
                </ul>
                <button class="btn btn-outline-primary w-100 rounded-pill">Plan Actuel</button>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-primary shadow rounded-4 text-center p-4 position-relative">
                <span class="badge bg-primary position-absolute top-0 start-50 translate-middle rounded-pill px-3 py-2">Le plus populaire</span>
                <h5 class="fw-bold text-uppercase text-primary mb-3 mt-2">Pro</h5>
                <h2 class="display-5 fw-bolder mb-3">50.000 <small class="fs-6 text-muted">FCFA/mois</small></h2>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Jusqu'à 50 Dossiers Clients</li>
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> 10 Utilisateurs</li>
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Certification e-MECeF DGI</li>
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Paiements Mobile Money</li>
                </ul>
                <button class="btn btn-primary w-100 rounded-pill" onclick="initiatePayment(50000)">Passer au Pro</button>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <h5 class="fw-bold text-uppercase text-muted mb-3">Entreprise</h5>
                <h2 class="display-5 fw-bolder mb-3">100.000 <small class="fs-6 text-muted">FCFA/mois</small></h2>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Dossiers Clients Illimités</li>
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Utilisateurs Illimités</li>
                    <li class="mb-2"><i class="bi-check-circle-fill text-success me-2"></i> Marque Blanche (Logo personnalisé)</li>
                </ul>
                <button class="btn btn-outline-primary w-100 rounded-pill" onclick="initiatePayment(100000)">Souscrire</button>
            </div>
        </div>
    </div>

    <!-- Interface Simulation Paiement MoMo -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Paiement Mobile Money</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted mb-4">Veuillez renseigner votre numéro MTN MoMo pour valider l'abonnement.</p>
                    <input type="text" id="momoPhone" class="form-control form-control-lg text-center mb-3 rounded-3" placeholder="Ex: 22997000000" />
                    <button id="btnPay" class="btn btn-warning w-100 py-2 rounded-3 fw-bold shadow-sm">
                        Confirmer le Paiement
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedAmount = 0;

    function initiatePayment(amount) {
        selectedAmount = amount;
        let myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        myModal.show();
    }

    document.getElementById('btnPay').addEventListener('click', function() {
        const phone = document.getElementById('momoPhone').value;
        const btn = this;
        
        if(!phone) {
            alert("Veuillez entrer un numéro valide.");
            return;
        }

        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Traitement...';
        btn.disabled = true;

        fetch('/api/payments/momo/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                phone: phone,
                amount: selectedAmount,
                client_id: 1 // Test value
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert("Demande envoyée ! Veuillez valider sur votre téléphone.");
            } else {
                alert("Erreur: " + data.message);
            }
        })
        .finally(() => {
            btn.innerHTML = 'Confirmer le Paiement';
            btn.disabled = false;
        });
    });
</script>
@endsection
