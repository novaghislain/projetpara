@extends('layouts.gel-accountant')

@section('title', 'Abonnement Expiré')

@section('content')
<style>
    .blocked-container {
        max-width: 600px;
        margin: 50px auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid var(--gel-border);
        overflow: hidden;
    }
    .blocked-header {
        background: #FEF2F2;
        color: var(--gel-danger);
        padding: 30px;
        text-align: center;
        border-bottom: 1px solid #FCA5A5;
    }
    .blocked-header i {
        font-size: 48px;
        margin-bottom: 15px;
    }
    .blocked-body {
        padding: 40px;
        text-align: center;
    }
</style>

<div class="blocked-container">
    <div class="blocked-header">
        <i class="bi bi-shield-lock-fill"></i>
        <h2 class="h4 mb-0 fw-bold">Accès Suspendu</h2>
    </div>
    
    <div class="blocked-body">
        @if(auth()->user()->subscription_status === 'trial')
            <h5 class="fw-bold mb-3">Votre période d'essai est terminée.</h5>
            <p class="text-muted mb-4">
                Nous espérons que vous avez apprécié les fonctionnalités de GEL-ACCOUNTANT pour votre cabinet.
                Pour continuer à gérer vos clients et accéder à toutes vos données, veuillez souscrire à un abonnement.
            </p>
        @else
            <h5 class="fw-bold mb-3">Votre abonnement a expiré.</h5>
            <p class="text-muted mb-4">
                Le renouvellement de votre abonnement est nécessaire pour retrouver l'accès complet à votre espace.
            </p>
        @endif

        <div class="alert alert-info d-flex align-items-start mb-4" style="text-align: left;">
            <i class="bi bi-info-circle-fill me-3 fs-5 mt-1"></i>
            <div>
                <strong>Ne vous inquiétez pas !</strong><br>
                Toutes vos données, écritures et fiches clients sont conservées en toute sécurité. 
                Elles seront immédiatement disponibles dès la réactivation de votre compte.
            </div>
        </div>

        <a href="{{ route('gel-accountant.independant.subscription.index') }}" class="gel-btn gel-btn-primary w-100 py-3 d-inline-flex justify-content-center" style="font-size: 15px;">
            <i class="bi bi-credit-card me-2"></i> Voir les offres et s'abonner
        </a>
    </div>
</div>
@endsection
