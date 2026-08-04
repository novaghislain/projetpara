@php $currentSection = 'fournisseurs'; @endphp
@extends('layouts.gel-business')
@section('title', 'Fournisseurs - GEL Business')
@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Fournisseurs</h1>
        <p class="gel-page-subtitle">Gérez votre carnet de fournisseurs</p>
    </div>
    <button class="gel-btn gel-btn-primary" onclick="openNewFournisseurPanel()"><i class="fas fa-plus"></i> Nouveau fournisseur</button>
</div>
<div class="gel-card" style="min-height:400px; display:flex; flex-direction:column;">
    <div class="gel-card-body" style="flex:1; display:flex; flex-direction:column;">
        @if(session('success'))
            <div class="gel-alert gel-alert-success" style="margin:16px;">{{ session('success') }}</div>
        @endif
        @if(isset($fournisseurs) && $fournisseurs->count() > 0)
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Raison Sociale / Nom</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Email</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Téléphone</th>
                    <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fournisseurs as $f)
                <tr style="border-bottom:1px solid var(--gel-border-light);">
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $f->name }}</td>
                    <td style="padding:12px 16px;font-size:13px;color:var(--gel-text-secondary);">{{ $f->email ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:13px;">{{ $f->phone ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:13px;text-align:center;">
                        <div class="gel-dropdown" style="position:relative; display:inline-block;">
                            <button type="button" style="background:none;border:none;cursor:pointer;padding:4px 8px;" onclick="toggleDropdown('action-fournisseur-{{ $f->id }}')">
                                <i class="fas fa-ellipsis-v" style="color:var(--gel-text-muted);"></i>
                            </button>
                            <div class="gel-dropdown-menu" id="action-fournisseur-{{ $f->id }}" style="position:absolute; right:0; top:100%; min-width:140px; background:white; border:1px solid var(--gel-border); border-radius:4px; box-shadow:0 4px 6px rgba(0,0,0,0.1); z-index:10; flex-direction:column; padding:4px 0;">
                                <a href="#" onclick="showToast('Fonctionnalité de modification en cours de développement', 'info'); return false;" style="padding:8px 16px; text-decoration:none; color:var(--gel-text); font-size:13px; display:flex; align-items:center; gap:8px;"><i class="fas fa-edit"></i> Modifier</a>
                                <a href="#" onclick="showToast('Fonctionnalité de suppression en cours de développement', 'info'); return false;" style="padding:8px 16px; text-decoration:none; color:var(--gel-danger); font-size:13px; display:flex; align-items:center; gap:8px;"><i class="fas fa-trash"></i> Supprimer</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="gel-empty" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-truck" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucun fournisseur</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Ajoutez vos fournisseurs pour les retrouver facilement dans vos achats et bons de commande.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewFournisseurPanel()">Ajouter un fournisseur</button>
        </div>
        @endif
    </div>
</div>

<template id="newFournisseurTemplate">
    <form id="newFournisseurForm" action="{{ route('gel-business.depenses.fournisseurs.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Raison sociale / Nom</label>
            <input type="text" name="name" class="gel-form-control" placeholder="Ex: Société XYZ" required>
        </div>
        <div class="gel-form-group">
            <label>Numéro IFU (Optionnel)</label>
            <input type="text" class="gel-form-control" placeholder="Ex: 1234567890123">
        </div>
        <div class="gel-form-group">
            <label>Email de contact</label>
            <input type="email" name="email" class="gel-form-control" placeholder="Ex: contact@xyz.com">
        </div>
        <div class="gel-form-group">
            <label>Téléphone</label>
            <input type="text" name="phone" class="gel-form-control" placeholder="Ex: +229 01 23 45 67">
        </div>
        <div class="gel-form-group">
            <label>Adresse</label>
            <textarea class="gel-form-control" rows="2" placeholder="Adresse complète du fournisseur..."></textarea>
        </div>
        <div class="gel-form-group">
            <label>Conditions de paiement habituelles</label>
            <select class="gel-form-control">
                <option>Comptant à la livraison</option>
                <option>30 jours net</option>
                <option>60 jours net</option>
                <option>Acompte 50%</option>
            </select>
        </div>
    </form>
</template>
<template id="newFournisseurFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newFournisseurForm').requestSubmit()">Enregistrer</button>
</template>
<script>
function openNewFournisseurPanel() {
    openPanel('Nouveau Fournisseur', document.getElementById('newFournisseurTemplate').innerHTML, document.getElementById('newFournisseurFooter').innerHTML);
}
</script>
@endsection
