{{-- ============================================ --}}
{{-- PAGE : Catalogue produits et services         --}}
{{-- ============================================ --}}

@php $currentSection = 'ventes'; @endphp

{{-- === HERITAGE === --}}
@extends('layouts.gel-business')

{{-- === TITRE === --}}
@section('title', 'Produits & services - GEL Business')

{{-- === CONTENU === --}}
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Produits & services</h1><p class="gel-page-subtitle">Gérez votre catalogue de produits et services</p></div>
    <div class="gel-flex gel-gap-sm">
        <button class="gel-btn gel-btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
        <button class="gel-btn gel-btn-primary" onclick="openNewProduitPanel()"><i class="fas fa-plus"></i> Nouveau produit</button>
    </div>
</div>
<div class="gel-card" style="min-height: 400px; display: flex; flex-direction: column;">
    <div class="gel-card-body" style="padding:0; flex: 1; display: flex; flex-direction: column;">
        @if(session('success'))
            <div class="gel-alert gel-alert-success" style="margin:16px;">{{ session('success') }}</div>
        @endif
        @if(isset($produits) && $produits->count() > 0)
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Nom</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Description</th>
                    <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Prix unitaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produits as $p)
                <tr style="border-bottom:1px solid var(--gel-border-light);">
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $p->nom }}</td>
                    <td style="padding:12px 16px;font-size:13px;color:var(--gel-text-secondary);">{{ Str::limit($p->description, 50) }}</td>
                    <td style="padding:12px 16px;font-size:13px;text-align:right;font-weight:600;">
                        {{ number_format($p->prix, 0, ',', ' ') }} CFA
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="gel-empty" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-box" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucun produit</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Ajoutez des articles à votre catalogue pour les inclure dans vos factures.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewProduitPanel()">Ajouter un produit</button>
        </div>
        @endif
    </div>
</div>

<template id="newProduitTemplate">
    <form id="newProduitForm" action="{{ route('gel-business.ventes.produits.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Type</label>
            <select class="gel-form-control">
                <option value="produit">Produit physique</option>
                <option value="service">Service (Prestation)</option>
            </select>
        </div>
        <div class="gel-form-group">
            <label>Nom de l'article</label>
            <input type="text" name="nom" class="gel-form-control" placeholder="Ex: Consultation web" required>
        </div>
        <div class="gel-form-group">
            <label>Référence (Optionnel)</label>
            <input type="text" class="gel-form-control" placeholder="Ex: REF-001">
        </div>
        <div class="gel-form-group">
            <label>Prix unitaire HT</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="number" name="prix" class="gel-form-control" placeholder="0" required>
                <span style="font-weight:600;">CFA</span>
            </div>
        </div>
        <div class="gel-form-group">
            <label>Taux de TVA par défaut</label>
            <select class="gel-form-control">
                <option value="0">Exonéré (0%)</option>
                <option value="18" selected>Standard (18%)</option>
            </select>
        </div>
        <div class="gel-form-group">
            <label>Description</label>
            <textarea name="description" class="gel-form-control" rows="3" placeholder="Description affichée sur les factures..."></textarea>
        </div>
    </form>
</template>

<template id="newProduitFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newProduitForm').requestSubmit()">Ajouter le produit</button>
</template>

<script>
function openNewProduitPanel() {
    var bodyHtml = document.getElementById('newProduitTemplate').innerHTML;
    var footerHtml = document.getElementById('newProduitFooter').innerHTML;
    openPanel('Nouveau Produit / Service', bodyHtml, footerHtml);
}
</script>
@endsection

