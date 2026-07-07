@php $currentSection = 'tasks'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Tâches - GEL Accountant')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Tâches</h1><p class="gel-page-subtitle">Suivi des tâches et échéances</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="openPanel('taskPanel')"><i class="fas fa-plus"></i> Nouvelle tâche</button>
</div>
<div class="gel-kpi-grid">
    <div class="gel-kpi-card"><div class="gel-kpi-label">À faire</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">En cours</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Terminées</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Échues</div><div class="gel-kpi-value">0</div></div>
</div>
<div class="gel-card"><div class="gel-card-body">
    <div class="gel-empty">
        <i class="fas fa-tasks"></i>
        <h3>Aucune tâche</h3>
        <p>Créez votre première tâche pour commencer.</p>
    </div>
</div></div>
<div class="gel-panel-overlay" id="taskPanelOverlay" onclick="closePanel('taskPanel')"></div>
<div class="gel-panel" id="taskPanel">
    <div class="gel-panel-header"><span class="gel-panel-title">Nouvelle tâche</span><button class="gel-panel-close" onclick="closePanel('taskPanel')">&times;</button></div>
    <div class="gel-panel-body">
        <form>
            <div class="gel-form-group"><label>Titre</label><input class="gel-form-control" placeholder="Clôture mensuelle"></div>
            <div class="gel-form-group"><label>Description</label><textarea class="gel-form-control" rows="3"></textarea></div>
            <div class="gel-form-group"><label>Assigné à</label><select class="gel-form-select"></select></div>
            <div class="gel-form-group"><label>Priorité</label>
                <select class="gel-form-select"><option>Basse</option><option selected>Moyenne</option><option>Haute</option><option>Critique</option></select>
            </div>
            <div class="gel-form-group"><label>Date d'échéance</label><input class="gel-form-control" type="date"></div>
        </form>
    </div>
    <div class="gel-panel-footer">
        <button class="gel-btn gel-btn-secondary" onclick="closePanel('taskPanel')">Annuler</button>
        <button class="gel-btn gel-btn-primary">Créer</button>
    </div>
</div>
@endsection
