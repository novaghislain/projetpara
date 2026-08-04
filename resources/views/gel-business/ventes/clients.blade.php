@php $currentSection = 'ventes'; @endphp
@extends('layouts.gel-business')
@section('title', 'Clients - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Clients</h1><p class="gel-page-subtitle">Votre portefeuille clients</p></div>
    <button class="gel-btn gel-btn-primary" onclick="openNewClientPanel()"><i class="fas fa-plus"></i> Nouveau client</button>
</div>
<div class="gel-card" style="min-height: 400px; display: flex; flex-direction: column;">
    <div class="gel-card-body" style="padding:0; flex: 1; display: flex; flex-direction: column;">
        @if(session('success'))
            <div class="gel-alert gel-alert-success" style="margin:16px;">{{ session('success') }}</div>
        @endif
        @if(isset($contacts) && $contacts->count() > 0)
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Nom / Raison Sociale</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Email</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Téléphone</th>
                    <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $c)
                <tr style="border-bottom:1px solid var(--gel-border-light);">
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $c->name }}</td>
                    <td style="padding:12px 16px;font-size:13px;color:var(--gel-text-secondary);">{{ $c->email ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:13px;">{{ $c->phone ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:13px;text-align:center;">
                        <div class="gel-dropdown" style="position:relative; display:inline-block;">
                            <button type="button" style="background:none;border:none;cursor:pointer;padding:4px 8px;" onclick="toggleDropdown('action-client-{{ $c->id }}')">
                                <i class="fas fa-ellipsis-v" style="color:var(--gel-text-muted);"></i>
                            </button>
                            <div class="gel-dropdown-menu" id="action-client-{{ $c->id }}" style="position:absolute; right:0; top:100%; min-width:140px; background:white; border:1px solid var(--gel-border); border-radius:4px; box-shadow:0 4px 6px rgba(0,0,0,0.1); z-index:10; flex-direction:column; padding:4px 0;">
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
                <i class="fas fa-users" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucun client</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Ajoutez vos clients pour pouvoir créer des factures et devis.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewClientPanel()">Ajouter un client</button>
        </div>
        @endif
    </div>
</div>

<template id="newClientTemplate">
    <form id="newClientForm" action="{{ route('gel-business.ventes.clients.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Type de client</label>
            <select class="gel-form-control">
                <option value="entreprise">Entreprise</option>
                <option value="particulier">Particulier</option>
            </select>
        </div>
        <div class="gel-form-group">
            <label>Nom ou Raison sociale</label>
            <input type="text" name="name" class="gel-form-control" placeholder="Ex: Acme Corp" required>
        </div>
        <div class="gel-form-group">
            <label>Email de contact</label>
            <input type="email" name="email" class="gel-form-control" placeholder="Ex: contact@acme.com">
        </div>
        <div class="gel-form-group">
            <label>Téléphone</label>
            <input type="text" name="phone" class="gel-form-control" placeholder="Ex: +229 00 00 00 00">
        </div>
        <div class="gel-form-group">
            <label>Adresse complète</label>
            <textarea class="gel-form-control" rows="3" placeholder="Adresse du client..."></textarea>
        </div>
    </form>
</template>

<template id="newClientFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newClientForm').requestSubmit()">Enregistrer le client</button>
</template>

<script>
function openNewClientPanel() {
    var bodyHtml = document.getElementById('newClientTemplate').innerHTML;
    var footerHtml = document.getElementById('newClientFooter').innerHTML;
    openPanel('Nouveau Client', bodyHtml, footerHtml);
}
</script>
@endsection
