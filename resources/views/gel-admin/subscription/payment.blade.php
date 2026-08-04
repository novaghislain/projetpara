@extends('layouts.gel-admin')

@section('title', 'Paiement de l\'abonnement')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Paiement</h1>
        <p class="admin-page-sub">Réglez votre abonnement en toute sécurité via Mobile Money.</p>
    </div>
    <a href="{{ route('gel-admin.subscription.index') }}" class="admin-btn admin-btn-secondary">Annuler</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="admin-card">
            <div class="admin-card-header bg-light">
                <div class="admin-card-title text-center w-100">Détails de la facturation</div>
            </div>
            <div class="admin-card-body">
                <div class="text-center mb-4">
                    <h5 class="text-muted mb-1">Forfait {{ ucfirst($plan_id) }}</h5>
                    <div class="display-5 fw-bold text-dark">{{ number_format($amount, 0, ',', ' ') }} FCFA</div>
                </div>

                <form action="{{ route('gel-admin.subscription.payment.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan_id }}">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Numéro de téléphone (Mobile Money)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-mobile-alt text-muted"></i></span>
                            <input type="text" name="phone" class="form-control" placeholder="Ex: 229 90 00 00 00" required>
                        </div>
                        <div class="form-text mt-2">
                            Entrez votre numéro Mobile Money. Vous recevrez un prompt de validation sur votre téléphone.
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/14/MTN_Logo.svg/1024px-MTN_Logo.svg.png" style="height:40px; object-fit:contain;">
                        <img src="https://upload.wikimedia.org/wikipedia/fr/thumb/a/a2/Moov_Africa_logo.png/800px-Moov_Africa_logo.png" style="height:40px; object-fit:contain;">
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary w-100 justify-content-center py-2" style="font-size: 16px;">
                        <i class="fas fa-lock me-2"></i> Confirmer et Payer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
