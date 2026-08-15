@extends('layouts.gel-accountant')

@section('title', 'Flux de Trésorerie')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-water" style="color:var(--gel-primary); margin-right:8px;"></i>
            Flux de Trésorerie
        </h1>
        <p class="gel-page-subtitle">Analyse des entrées et sorties de trésorerie.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="gel-card" style="padding:48px; text-align:center; margin-top:20px;">
    <div style="width:90px; height:90px; background:var(--gel-primary-light,#eff6ff); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; font-size:36px; color:var(--gel-primary);">
        <i class="fas fa-water"></i>
    </div>
    <h2 style="font-size:20px; font-weight:700; color:var(--gel-text-primary); margin-bottom:10px;">Bientôt disponible</h2>
    <p style="color:var(--gel-text-secondary); max-width:480px; margin:0 auto 24px; line-height:1.6;">
        La page <strong>Flux de Trésorerie</strong> est en cours d'implémentation et sera disponible très prochainement.
    </p>
    <a href="javascript:history.back()" class="gel-btn gel-btn-primary">
        <i class="fas fa-arrow-left"></i> Retourner à la page précédente
    </a>
</div>

@endsection