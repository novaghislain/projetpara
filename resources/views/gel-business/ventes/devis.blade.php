@php $currentSection = 'ventes'; @endphp
@extends('layouts.gel-business')
@section('title', 'Devis - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Devis</h1><p class="gel-page-subtitle">Gestion des devis</p></div>
    <button class="gel-btn gel-btn-primary" onclick="openNewDevisPanel()"><i class="fas fa-plus"></i> Nouveau devis</button>
</div>
<div class="gel-card" style="min-height: 400px; display: flex; flex-direction: column;">
    <div class="gel-card-body" style="padding:0; flex: 1; display: flex; flex-direction: column;">
        @if(session('success'))
            <div class="gel-alert gel-alert-success" style="margin:16px;">{{ session('success') }}</div>
        @endif
        @if(isset($devisList) && $devisList->count() > 0)
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Date</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Client</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Statut</th>
                    <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devisList as $d)
                <tr style="border-bottom:1px solid var(--gel-border-light);">
                    <td style="padding:12px 16px;font-size:13px;">{{ $d->date_devis->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $d->client_nom }}</td>
                    <td style="padding:12px 16px;font-size:13px;">
                        <span class="gel-badge {{ $d->statut === 'Accepté' ? 'gel-badge-success' : ($d->statut === 'Refusé' ? 'gel-badge-danger' : 'gel-badge-warning') }}">{{ $d->statut }}</span>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;text-align:right;font-weight:600;">
                        {{ number_format($d->montant, 0, ',', ' ') }} CFA
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="gel-empty" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-file-signature" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucun devis</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Créez vos premiers devis pour vos clients.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewDevisPanel()">Créer un devis</button>
        </div>
        @endif
    </div>
</div>

<template id="newDevisTemplate">
    <form id="newDevisForm" action="{{ route('gel-business.ventes.devis.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Client prospect</label>
            <input type="text" name="client_nom" class="gel-form-control" placeholder="Nom du prospect" required>
        </div>
        <div class="gel-form-group">
            <label>Date de validité</label>
            <input type="date" name="date_devis" class="gel-form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
        </div>
        <div class="gel-form-group">
            <label>Montant total (CFA)</label>
            <input type="number" name="montant" class="gel-form-control" placeholder="0" required>
        </div>
        <div class="gel-form-group">
            <label>Produits / Services proposés</label>
            <div style="border:1px solid var(--gel-border); border-radius:var(--gel-radius-md); padding:12px; margin-bottom:8px;">
                <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <input type="text" class="gel-form-control" placeholder="Désignation" style="flex:2;">
                    <input type="number" class="gel-form-control" placeholder="Qté" value="1" style="flex:1;">
                    <input type="number" class="gel-form-control" placeholder="Prix unitaire" style="flex:1;">
                </div>
                <button type="button" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-plus"></i> Ajouter une ligne</button>
            </div>
        </div>
        <div class="gel-form-group">
            <label>Conditions de paiement</label>
            <textarea class="gel-form-control" rows="2" placeholder="Ex: 50% à la commande, 50% à la livraison..."></textarea>
        </div>
    </form>
</template>

<template id="newDevisFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newDevisForm').requestSubmit()">Enregistrer le devis</button>
</template>

<script>
function openNewDevisPanel() {
    var bodyHtml = document.getElementById('newDevisTemplate').innerHTML;
    var footerHtml = document.getElementById('newDevisFooter').innerHTML;
    openPanel('Nouveau Devis', bodyHtml, footerHtml);
}
</script>
@endsection
