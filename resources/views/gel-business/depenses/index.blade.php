@php $currentSection = 'depenses'; @endphp
@extends('layouts.gel-business')
@section('title', 'Dépenses - GEL Business')
@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Dépenses</h1>
        <p class="gel-page-subtitle">Suivez et catégorisez toutes vos dépenses</p>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="gel-btn gel-btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
        <button class="gel-btn gel-btn-primary" onclick="openNewDepensePanel()"><i class="fas fa-plus"></i> Nouvelle dépense</button>
    </div>
</div>

@if(session('success'))
    <div class="gel-alert gel-alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

@if(isset($depenses) && $depenses->count() > 0)
<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        <table class="gel-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gel-body-bg);">
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Date</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Description</th>
                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Catégorie</th>
                    <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:var(--gel-text-secondary);text-transform:uppercase;">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($depenses as $dep)
                <tr style="border-bottom:1px solid var(--gel-border-light);">
                    <td style="padding:12px 16px;font-size:13px;">{{ $dep->date_depense->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;">{{ $dep->libelle }}</td>
                    <td style="padding:12px 16px;font-size:13px;">
                        @if($dep->categorie)
                            <span class="gel-badge gel-badge-info">{{ $dep->categorie }}</span>
                        @else
                            <span style="color:var(--gel-text-muted);">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;font-size:13px;text-align:right;font-weight:600;color:var(--gel-danger);">
                        {{ number_format($dep->montant, 0, ',', ' ') }} CFA
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
                <i class="fas fa-money-bill-wave" style="font-size:24px;color:var(--gel-text-muted);"></i>
            </div>
            <h3 style="font-size:16px;margin:0 0 8px;">Aucune dépense enregistrée</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Enregistrez vos dépenses pour mieux gérer votre trésorerie et simplifier la comptabilité.</p>
            <button class="gel-btn gel-btn-primary" onclick="openNewDepensePanel()">Enregistrer une dépense</button>
        </div>
    </div>
</div>
@endif

<template id="newDepenseTemplate">
    <form id="newDepenseForm" action="{{ route('gel-business.depenses.store') }}" method="POST">
        @csrf
        <div class="gel-form-group">
            <label>Description</label>
            <input type="text" name="libelle" class="gel-form-control" placeholder="Ex: Achat de papier A4" required>
        </div>
        <div class="gel-form-group">
            <label>Montant (CFA)</label>
            <input type="number" name="montant" class="gel-form-control" placeholder="0" required>
        </div>
        <div class="gel-form-group">
            <label>Date</label>
            <input type="date" name="date_depense" class="gel-form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="gel-form-group">
            <label>Mode de paiement</label>
            <select name="mode_paiement" class="gel-form-control">
                <option>Espèces</option>
                <option>Mobile Money</option>
                <option>Virement bancaire</option>
                <option>Chèque</option>
            </select>
        </div>
        <div class="gel-form-group">
            <label>Justificatif (Optionnel)</label>
            <input type="file" class="gel-form-control">
        </div>
    </form>
</template>
<template id="newDepenseFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newDepenseForm').requestSubmit()">Enregistrer</button>
</template>
<script>
function openNewDepensePanel() {
    openPanel('Nouvelle Dépense', document.getElementById('newDepenseTemplate').innerHTML, document.getElementById('newDepenseFooter').innerHTML);
}
</script>
@endsection
