@php $currentSection = 'banque'; @endphp
@extends('layouts.gel-business')
@section('title', 'Rapprochement bancaire - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Rapprochement bancaire</h1><p class="gel-page-subtitle">Rapprochez vos relevés bancaires</p></div>
    <button class="gel-btn gel-btn-primary" onclick="openNewRapproPanel()"><i class="fas fa-plus"></i> Nouveau rapprochement</button>
</div>
<div class="gel-card" style="min-height:400px; display:flex; flex-direction:column;">
    <div class="gel-card-body" style="flex:1; display:flex; flex-direction:column;">
        @if(session('success'))
            <div class="gel-alert gel-alert-success" style="margin:16px;">{{ session('success') }}</div>
        @endif
        @if(isset($rapprochements) && $rapprochements->count() > 0)
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Date</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Compte</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Statut</th>
                    <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Solde Bancaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rapprochements as $r)
                <tr style="border-bottom:1px solid var(--gel-border-light);">
                    <td style="padding:12px 16px;font-size:13px;">{{ $r->date_rapprochement->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $r->compte_id ?? 'Compte Principal' }}</td>
                    <td style="padding:12px 16px;font-size:13px;">
                        <span class="gel-badge {{ $r->statut === 'Validé' ? 'gel-badge-success' : 'gel-badge-warning' }}">{{ $r->statut ?? 'En cours' }}</span>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;text-align:right;font-weight:600;">
                        {{ number_format($r->solde_bancaire, 0, ',', ' ') }} CFA
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="gel-empty" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-handshake" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucun rapprochement</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Rapprochez vos relevés bancaires avec vos écritures comptables pour détecter les écarts.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewRapproPanel()">Commencer un rapprochement</button>
        </div>
        @endif
    </div>
</div>

<template id="newRapproTemplate">
    <form id="newRapproForm" action="{{ route('gel-business.banque.rapprochement.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Compte bancaire</label>
            <select name="compte_id" class="gel-form-control">
                <option value="">Sélectionnez un compte...</option>
                <option>Compte courant principal</option>
                <option>Compte épargne</option>
            </select>
        </div>
        <div class="gel-form-group">
            <label>Date de rapprochement</label>
            <input type="date" name="date_rapprochement" class="gel-form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="gel-form-group">
            <label>Solde du relevé bancaire (CFA)</label>
            <input type="number" name="solde_bancaire" class="gel-form-control" placeholder="0" required>
        </div>
        <div class="gel-form-group">
            <label>Relevé bancaire (PDF)</label>
            <input type="file" class="gel-form-control" accept=".pdf,.csv">
        </div>
    </form>
</template>
<template id="newRapproFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newRapproForm').requestSubmit()">Démarrer</button>
</template>
<script>
function openNewRapproPanel() {
    openPanel('Nouveau Rapprochement', document.getElementById('newRapproTemplate').innerHTML, document.getElementById('newRapproFooter').innerHTML);
}
</script>
@endsection
