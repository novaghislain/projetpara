@extends('layouts.gel-accountant')

@section('title', 'Nouvelle Déclaration TVA - GEL Accountant')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-calculator" style="color:var(--gel-primary); margin-right:8px;"></i> Nouvelle Déclaration TVA</h1>
        <p class="gel-page-subtitle">Générez la déclaration de TVA pour une période donnée.</p>
    </div>
    <div>
        <a href="{{ route('gel-accountant.fiscalite.tva.index') }}" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <form action="{{ route('gel-accountant.fiscalite.tva.create') }}" method="GET" style="display:flex; gap:12px; align-items:flex-end;">
        <div style="flex:1; max-width:300px;">
            <label style="font-weight:600; font-size:12px; margin-bottom:4px; display:block;">Période (YYYY-MM)</label>
            <input type="month" name="period" value="{{ request('period') }}" class="gel-input" required>
        </div>
        <button type="submit" class="gel-btn gel-btn-primary">
            <i class="fas fa-sync"></i> Calculer
        </button>
    </form>
</div>

@if($result)
    <div class="gel-card p-4">
        <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--gel-border);">Résultat du calcul pour {{ $result['period'] }}</h3>
        
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:30px;">
            <div style="background:#f3f4f6; padding:20px; border-radius:8px; text-align:center;">
                <p style="font-size:12px; color:var(--gel-text-secondary); margin-bottom:4px; font-weight:600;">TVA Collectée (Ventes)</p>
                <h4 style="font-size:24px; font-weight:700; margin:0;">{{ number_format($result['tva_collected'], 2, ',', ' ') }} <span style="font-size:14px; font-weight:normal;">FCFA</span></h4>
            </div>
            
            <div style="background:#f3f4f6; padding:20px; border-radius:8px; text-align:center;">
                <p style="font-size:12px; color:var(--gel-text-secondary); margin-bottom:4px; font-weight:600;">TVA Déductible (Achats)</p>
                <h4 style="font-size:24px; font-weight:700; margin:0;">{{ number_format($result['tva_deductible'], 2, ',', ' ') }} <span style="font-size:14px; font-weight:normal;">FCFA</span></h4>
            </div>
            
            <div style="background:var(--gel-primary); color:white; padding:20px; border-radius:8px; text-align:center;">
                <p style="font-size:12px; margin-bottom:4px; font-weight:600;">TVA Nette à Payer</p>
                <h4 style="font-size:24px; font-weight:700; margin:0;">{{ number_format($result['tva_net'], 2, ',', ' ') }} <span style="font-size:14px; font-weight:normal;">FCFA</span></h4>
            </div>
        </div>

        <form action="{{ route('gel-accountant.fiscalite.tva.store') }}" method="POST" style="text-align:right;">
            @csrf
            <input type="hidden" name="period" value="{{ $result['period'] }}">
            <input type="hidden" name="tva_collected" value="{{ $result['tva_collected'] }}">
            <input type="hidden" name="tva_deductible" value="{{ $result['tva_deductible'] }}">
            <input type="hidden" name="tva_net" value="{{ $result['tva_net'] }}">
            
            <button type="submit" class="gel-btn gel-btn-primary" style="padding:10px 24px; font-size:14px;">
                <i class="fas fa-save"></i> Enregistrer la déclaration (Brouillon)
            </button>
        </form>
    </div>
@endif

@endsection
