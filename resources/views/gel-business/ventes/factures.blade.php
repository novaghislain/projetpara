@php $currentSection = 'ventes'; @endphp
@extends('layouts.gel-business')
@section('title', 'Factures - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Factures</h1><p class="gel-page-subtitle">Toutes vos factures clients</p></div>
    <div style="display:flex;gap:8px;">
        <button class="gel-btn gel-btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
        <button class="gel-btn gel-btn-primary" onclick="openNewFacturePanel()"><i class="fas fa-plus"></i> Nouvelle facture</button>
    </div>
</div>
<div class="gel-card" style="min-height: 400px; display: flex; flex-direction: column;">
    <div class="gel-card-body" style="padding:0; flex: 1; display: flex; flex-direction: column;">
        @if(session('success'))
            <div class="gel-alert gel-alert-success" style="margin:16px;">{{ session('success') }}</div>
        @endif
        @if(isset($factures) && count($factures) > 0)
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">N°</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Client</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Date</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Statut</th>
                    <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Montant</th>
                </tr>
            </thead>
            <tbody>
            @foreach($factures as $f)
            <tr style="border-bottom:1px solid var(--gel-border-light);">
                <td style="padding:12px 16px;font-size:13px;">{{ $f->numero ?? '—' }}</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $f->client_nom ?? '—' }}</td>
                <td style="padding:12px 16px;font-size:13px;">{{ $f->date_facture ? $f->date_facture->format('d/m/Y') : '—' }}</td>
                <td style="padding:12px 16px;font-size:13px;">
                    <span class="gel-badge {{ $f->statut === 'Payée' ? 'gel-badge-success' : ($f->statut === 'En retard' ? 'gel-badge-danger' : 'gel-badge-info') }}">{{ $f->statut ?? 'Brouillon' }}</span>
                </td>
                <td style="padding:12px 16px;font-size:13px;text-align:right;font-weight:600;">{{ number_format($f->montant ?? 0, 0, ',', ' ') }} CFA</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <div class="gel-empty" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-file-invoice" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucune facture</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Créez votre première facture pour commencer à facturer vos clients.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewFacturePanel()">Créer une facture</button>
        </div>
        @endif
    </div>
</div>

<template id="newFactureTemplate">
    <form id="newFactureForm" action="{{ route('gel-business.ventes.factures.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Client</label>
            <input type="text" name="client_nom" class="gel-form-control" placeholder="Nom du client" required>
        </div>
        <div class="gel-form-group">
            <label>Numéro de facture</label>
            <input type="text" name="numero" class="gel-form-control" placeholder="Ex: F-2026-001">
        </div>
        <div class="gel-form-group">
            <label>Date de facture</label>
            <input type="date" name="date_facture" class="gel-form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="gel-form-group">
            <label>Montant total (CFA)</label>
            <input type="number" name="montant" class="gel-form-control" placeholder="0" required>
        </div>
        <div class="gel-form-group">
            <label>Produits / Services</label>
            <div style="border:1px solid var(--gel-border); border-radius:var(--gel-radius-md); padding:12px; margin-bottom:8px;">
                <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <input type="text" class="gel-form-control" placeholder="Désignation" style="flex:2;">
                    <input type="number" class="gel-form-control" placeholder="Qté" value="1" style="flex:1;">
                    <input type="number" class="gel-form-control" placeholder="Prix" style="flex:1;">
                </div>
                <button type="button" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-plus"></i> Ajouter une ligne</button>
            </div>
        </div>
        <div class="gel-form-group">
            <label>TVA</label>
            <select class="gel-form-control">
                <option value="0">0%</option>
                <option value="18">18%</option>
            </select>
        </div>
        <div class="gel-form-group">
            <label>Notes (optionnel)</label>
            <textarea class="gel-form-control" rows="3" placeholder="Notes pour le client..."></textarea>
        </div>
    </form>
</template>

<template id="newFactureFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newFactureForm').requestSubmit()">Créer la facture</button>
</template>

<script>
function openNewFacturePanel() {
    var bodyHtml = document.getElementById('newFactureTemplate').innerHTML;
    var footerHtml = document.getElementById('newFactureFooter').innerHTML;
    openPanel('Nouvelle Facture', bodyHtml, footerHtml);
}
</script>
@endsection
