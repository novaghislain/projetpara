@php $currentSection = 'workflows'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Workflow Automation - GEL Accountant')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Workflow Automation</h1><p class="gel-page-subtitle">Automatisez vos tâches répétitives</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="openPanel('wfPanel')"><i class="fas fa-plus"></i> Nouveau workflow</button>
</div>
<div class="gel-kpi-grid">
    <div class="gel-kpi-card"><div class="gel-kpi-label">Workflows actifs</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Exécutés (mois)</div><div class="gel-kpi-value">0</div></div>
</div>
<div class="gel-card"><div class="gel-card-body">
    <div class="gel-empty">
        <i class="fas fa-robot"></i>
        <h3>Aucun workflow</h3>
        <p>Créez des workflows pour automatiser : rappels factures, alertes dépôts, approbations.</p>
    </div>
</div></div>
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
