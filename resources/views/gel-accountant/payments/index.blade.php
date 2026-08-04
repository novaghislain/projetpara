@extends('layouts.gel-accountant')

@section('title', 'Paiements Reçus')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-hand-holding-usd" style="color:var(--gel-primary); margin-right:8px;"></i> Paiements Reçus</h1>
        <p class="gel-page-subtitle">Suivi des encaissements sur vos factures clients.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="gel-btn gel-btn-primary"><i class="fas fa-plus"></i> Nouveau paiement</button>
    </div>
</div>

<div class="gel-card gel-p-0 p-4">
    <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
        <i class="fas fa-check-circle" style="font-size:48px; margin-bottom:16px; opacity:0.5; color:var(--gel-success);"></i>
        <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucun paiement récent</h3>
        <p style="margin-bottom:20px;">Les paiements enregistrés sur vos factures s'afficheront ici.</p>
    </div>
</div>

@endsection
