@php $currentSection = 'workflows'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Workflow Automation - GEL Accountant')
@section('content')
<div class="gel-page-header animate-fade" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 class="gel-page-title" style="font-size: 18px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">Workflow Automation</h1>
        <p class="gel-page-subtitle" style="font-size: 12px; color: var(--gel-text-secondary);">Automatisez vos tâches répétitives</p>
    </div>
    <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="openPanel('wfPanel')" style="font-weight:600;display:flex;align-items:center;gap:6px;border-radius:6px;padding:8px 14px;">
        <i class="fas fa-plus"></i> Nouveau workflow
    </button>
</div>
<div class="gel-kpi-grid animate-fade delay-1">
    <div class="gel-kpi-card"><div class="gel-kpi-label">Workflows actifs</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Exécutés (mois)</div><div class="gel-kpi-value">0</div></div>
</div>
<div class="pro-panel animate-fade delay-1 mb-4" style="margin-top:20px;">
    <div class="panel-body p-4">
        <div class="gel-empty">
            <i class="fas fa-robot"></i>
            <h3>Aucun workflow</h3>
            <p>Créez des workflows pour automatiser : rappels factures, alertes dépôts, approbations.</p>
        </div>
    </div>
</div>
<div class="gel-panel-overlay" id="wfPanelOverlay" onclick="closePanel('wfPanel')"></div>
<div class="gel-panel" id="wfPanel">
    <div class="gel-panel-header"><span class="gel-panel-title">Nouveau workflow</span><button class="gel-panel-close" onclick="closePanel('wfPanel')">&times;</button></div>
    <div class="gel-panel-body">
        <form>
            <div class="gel-form-group"><label>Nom</label><input class="gel-form-control" placeholder="Rappel facture J+3"></div>
            <div class="gel-form-group"><label>Type</label>
                <select class="gel-form-select">
                    <option>Rappel facture en retard</option>
                    <option>Rappel paiement fournisseur</option>
                    <option>Alerte dépôt non effectué</option>
                    <option>Flux d'approbation</option>
                </select>
            </div>
            <div class="gel-form-group"><label>Fréquence</label>
                <select class="gel-form-select"><option>Immédiate</option><option>Quotidienne</option><option>Hebdomadaire</option></select>
            </div>
            <div class="gel-form-group"><label>Client</label>
                <select class="gel-form-select"><option>Tous les clients</option></select>
            </div>
        </form>
    </div>
    <div class="gel-panel-footer">
        <button class="gel-btn gel-btn-secondary" onclick="closePanel('wfPanel')">Annuler</button>
        <button class="gel-btn gel-btn-primary">Créer</button>
    </div>
</div>
@endsection
