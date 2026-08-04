@php $currentSection = 'bons-commande'; @endphp
@extends('layouts.gel-business')
@section('title', 'Bons de commande - GEL Business')
@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Bons de commande</h1>
        <p class="gel-page-subtitle">Gérez vos commandes auprès des fournisseurs</p>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="gel-btn gel-btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
        <button class="gel-btn gel-btn-primary" onclick="openNewBcPanel()"><i class="fas fa-plus"></i> Nouveau bon de commande</button>
    </div>
</div>

@if(session('success'))
    <div class="gel-alert gel-alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

@if(isset($bons) && $bons->count() > 0)
<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Date</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Fournisseur</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">N° BC</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Statut</th>
                    <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bons as $bc)
                <tr style="border-bottom:1px solid var(--gel-border-light);">
                    <td style="padding:12px 16px;font-size:13px;">{{ $bc->date_commande->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $bc->fournisseur_nom }}</td>
                    <td style="padding:12px 16px;font-size:13px;">{{ $bc->numero ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:13px;">
                        <span class="gel-badge {{ $bc->statut === 'Livré' ? 'gel-badge-success' : 'gel-badge-warning' }}">{{ $bc->statut }}</span>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;text-align:right;font-weight:600;">
                        {{ number_format($bc->montant, 0, ',', ' ') }} CFA
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="gel-card" style="min-height:400px; display:flex; flex-direction:column;">
    <div class="gel-card-body" style="flex:1; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:40px;">
        <div class="gel-empty" style="text-align:center;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-shopping-cart" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucun bon de commande</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Créez des bons de commande pour structurer vos achats et garder une trace.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewBcPanel()">Créer un bon de commande</button>
        </div>
    </div>
</div>
@endif

<template id="newBcTemplate">
    <form id="newBcForm" action="{{ route('gel-business.depenses.bons-commande.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Fournisseur</label>
            <input type="text" name="fournisseur_nom" class="gel-form-control" placeholder="Nom du fournisseur" required>
        </div>
        <div class="gel-form-group">
            <label>Date de commande</label>
            <input type="date" name="date_commande" class="gel-form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="gel-form-group">
            <label>Montant total (CFA)</label>
            <input type="number" name="montant" class="gel-form-control" placeholder="0" required>
        </div>
    </form>
</template>
<template id="newBcFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newBcForm').requestSubmit()">Créer le bon</button>
</template>
<script>
function openNewBcPanel() {
    openPanel('Nouveau Bon de Commande', document.getElementById('newBcTemplate').innerHTML, document.getElementById('newBcFooter').innerHTML);
}
</script>
@endsection
