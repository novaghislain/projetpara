@extends('layouts.gel-accountant')

@section('title', 'Factures Récurrentes')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-sync-alt" style="color:var(--gel-primary); margin-right:8px;"></i> Factures Récurrentes</h1>
        <p class="gel-page-subtitle">Gérez vos abonnements et facturations périodiques.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="gel-btn gel-btn-primary"><i class="fas fa-plus"></i> Nouveau modèle</button>
    </div>
</div>

<div class="gel-card gel-p-0 p-4">
    <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
        <i class="fas fa-calendar-alt" style="font-size:48px; margin-bottom:16px; opacity:0.5;"></i>
        <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucune facture récurrente</h3>
        <p style="margin-bottom:20px;">Créez un modèle pour générer automatiquement vos factures régulières.</p>
    </div>
</div>

@endsection
