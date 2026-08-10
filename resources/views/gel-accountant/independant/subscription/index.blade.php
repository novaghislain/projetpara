@extends('layouts.gel-accountant')

@section('title', 'Mon Abonnement')

@section('content')
<style>
    .plan-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border-color: var(--gel-primary);
    }
    .plan-card.active-plan {
        border-color: var(--gel-primary);
        box-shadow: 0 0 0 2px var(--gel-primary);
    }
    .active-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--gel-primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .plan-name {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--gel-text-primary);
    }
    .plan-price {
        font-size: 32px;
        font-weight: 800;
        color: var(--gel-primary);
        margin-bottom: 5px;
    }
    .plan-period {
        font-size: 13px;
        color: var(--gel-text-secondary);
        margin-bottom: 20px;
    }
    .plan-desc {
        font-size: 14px;
        color: var(--gel-text-secondary);
        margin-bottom: 25px;
        min-height: 40px;
    }
    .plan-features {
        text-align: left;
        margin-bottom: 30px;
        flex: 1;
    }
    .plan-features li {
        list-style: none;
        margin-bottom: 10px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .plan-features li i {
        color: var(--gel-success);
    }
</style>

<div class="gel-page-header text-center d-block mb-5">
    <h1 class="gel-page-title mb-2">Offres d'Abonnement</h1>
    <p class="gel-page-subtitle">Sélectionnez le forfait adapté à la taille de votre cabinet.</p>
</div>

<div class="row justify-content-center">
    @foreach($plans as $plan)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="plan-card {{ $user->plan_id == $plan->id ? 'active-plan' : '' }}">
                @if($user->plan_id == $plan->id)
                    <div class="active-badge">Offre Actuelle</div>
                @endif
                
                <div class="plan-name">{{ $plan->name }}</div>
                <div class="plan-price">{{ $plan->price > 0 ? number_format($plan->price, 0, ',', ' ') . ' FCFA' : 'Sur Devis' }}</div>
                <div class="plan-period">par mois</div>
                
                <div class="plan-desc">{{ $plan->description }}</div>
                
                <ul class="plan-features">
                    <li><i class="bi bi-check-circle-fill"></i> Saisie comptable avancée</li>
                    <li><i class="bi bi-check-circle-fill"></i> États financiers (SYSCOHADA)</li>
                    <li><i class="bi bi-check-circle-fill"></i> Gestion électronique de documents</li>
                    @if($plan->name === 'Starter')
                        <li><i class="bi bi-check-circle-fill"></i> Jusqu'à 3 clients</li>
                    @elseif($plan->name === 'Professionnel')
                        <li><i class="bi bi-check-circle-fill"></i> Jusqu'à 10 clients</li>
                    @else
                        <li><i class="bi bi-check-circle-fill"></i> Clients illimités</li>
                    @endif
                </ul>
                
                @if($user->plan_id == $plan->id)
                    <button class="gel-btn gel-btn-outline w-100" disabled>Déjà souscrit</button>
                @else
                    <form action="{{ route('gel-accountant.independant.subscription.subscribe') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <input type="hidden" name="payment_method" value="momo">
                        <button type="submit" class="gel-btn gel-btn-primary w-100" onclick="return confirm('Confirmez-vous la souscription à l\'offre {{ $plan->name }} ?');">Sélectionner cette offre</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
