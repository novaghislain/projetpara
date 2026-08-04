@extends('layouts.gel-accountant')

@section('title', 'Relances Automatiques')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-bell" style="color:var(--gel-primary); margin-right:8px;"></i> Relances Automatiques</h1>
        <p class="gel-page-subtitle">Configurez des rappels pour les factures impayées.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="gel-btn gel-btn-primary"><i class="fas fa-plus"></i> Nouvelle règle</button>
    </div>
</div>

<div class="gel-card gel-p-0 p-4">
    <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
        <i class="fas fa-envelope-open-text" style="font-size:48px; margin-bottom:16px; opacity:0.5;"></i>
        <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucune règle de relance</h3>
        <p style="margin-bottom:20px;">Créez des règles pour automatiser les rappels d'échéance à vos clients.</p>
    </div>
</div>

@endsection
