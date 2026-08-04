@php $currentSection = 'comptabilite'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Comptabilisation des produits - GEL Accountant')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Comptabilisation des produits</h1><p class="gel-page-subtitle">Revenus différés et reconnaissance</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="openPanel('revPanel')"><i class="fas fa-plus"></i> Nouveau plan</button>
</div>
<div class="gel-card p-4 mb-4"><div class="gel-card-body p-4 mb-4">
    <div class="gel-empty">
        <i class="fas fa-calendar-alt"></i>
        <h3>Aucun plan de reconnaissance</h3>
        <p>Créez des plans pour les revenus différés.</p>
    </div>
</div></div>
<div class="gel-panel-overlay" id="revPanelOverlay" onclick="closePanel('revPanel')"></div>
<div class="gel-panel" id="revPanel">
    <div class="gel-panel-header"><span class="gel-panel-title">Nouveau plan</span><button class="gel-panel-close" onclick="closePanel('revPanel')">&times;</button></div>
    <div class="gel-panel-body">
        <form>
            <div class="gel-form-group"><label>Libellé</label><input class="gel-form-control" placeholder="Abonnement annuel"></div>
            <div class="gel-form-group"><label>Modèle</label>
                <select class="gel-form-select"><option>Service Interval</option><option>Abonnement</option><option>Étapes</option></select>
            </div>
            <div class="gel-form-group"><label>Montant total (CFA)</label><input class="gel-form-control" type="number"></div>
            <div class="gel-form-group"><label>Date début</label><input class="gel-form-control" type="date"></div>
            <div class="gel-form-group"><label>Date fin</label><input class="gel-form-control" type="date"></div>
        </form>
    </div>
    <div class="gel-panel-footer">
        <button class="gel-btn gel-btn-secondary" onclick="closePanel('revPanel')">Annuler</button>
        <button class="gel-btn gel-btn-primary">Créer</button>
    </div>
</div>
@endsection
