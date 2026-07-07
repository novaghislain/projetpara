@php $currentSection = 'comptabilite'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Immobilisations - GEL Accountant')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Immobilisations</h1><p class="gel-page-subtitle">Gestion des actifs immobilisés</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="openPanel('assetPanel')"><i class="fas fa-plus"></i> Nouvel actif</button>
</div>
<div class="gel-tabs">
    <a class="gel-tab active" onclick="switchTab('faTab','list')" faTab="list">Liste des actifs</a>
    <a class="gel-tab" onclick="switchTab('faTab','amort')" faTab="amort">Amortissements</a>
    <a class="gel-tab" onclick="switchTab('faTab','report')" faTab="report">Rapport</a>
</div>
<div class="gel-tab-content active" faTab="list">
    <div class="gel-card"><div class="gel-card-body" style="padding:0;">
        @php $assets = $assets ?? []; @endphp
        @if(count($assets) > 0)
        <table class="gel-table">
            <tr><th>Nom</th><th>Date acq.</th><th>Coût</th><th>VNC</th><th>Statut</th><th></th></tr>
            @foreach($assets as $a)
            <tr>
                <td><strong>{{ $a->nom }}</strong></td>
                <td>{{ $a->date_acquisition->format('d/m/Y') }}</td>
                <td class="gel-text-right">{{ number_format($a->cout_acquisition, 0, ',', ' ') }} CFA</td>
                <td class="gel-text-right">{{ number_format($a->vnc, 0, ',', ' ') }} CFA</td>
                <td><span class="gel-badge gel-badge-{{ $a->statut === 'actif' ? 'success' : 'warning' }}">{{ $a->statut }}</span></td>
                <td><i class="fas fa-ellipsis-v" style="color:var(--gel-text-muted);cursor:pointer;"></i></td>
            </tr>
            @endforeach
        </table>
        @else
        <div class="gel-empty">
            <i class="fas fa-building"></i>
            <h3>Aucune immobilisation</h3>
            <p>Ajoutez vos actifs pour calculer les amortissements.</p>
        </div>
        @endif
    </div></div>
</div>
<div class="gel-tab-content" faTab="amort">
    <div class="gel-card"><div class="gel-card-body">
        <div class="gel-empty"><i class="fas fa-chart-line"></i><h3>Amortissements</h3><p>Les amortissements seront calculés automatiquement.</p></div>
    </div></div>
</div>
<div class="gel-tab-content" faTab="report">
    <div class="gel-card"><div class="gel-card-body">
        <div class="gel-empty"><i class="fas fa-file-alt"></i><h3>Rapport</h3><p>Générez le rapport des immobilisations.</p></div>
    </div></div>
</div>

{{-- Sliding Panel --}}
<div class="gel-panel-overlay" id="assetPanelOverlay" onclick="closePanel('assetPanel')"></div>
<div class="gel-panel" id="assetPanel">
    <div class="gel-panel-header">
        <span class="gel-panel-title">Nouvel actif</span>
        <button class="gel-panel-close" onclick="closePanel('assetPanel')">&times;</button>
    </div>
    <div class="gel-panel-body">
        <form>
            <div class="gel-form-group"><label>Nom de l'actif</label><input class="gel-form-control" placeholder="Ex: Véhicule Toyota"></div>
            <div class="gel-form-group"><label>Date d'acquisition</label><input class="gel-form-control" type="date"></div>
            <div class="gel-form-group"><label>Coût d'acquisition (CFA)</label><input class="gel-form-control" type="number" placeholder="0"></div>
            <div class="gel-form-group"><label>Valeur résiduelle (CFA)</label><input class="gel-form-control" type="number" placeholder="0"></div>
            <div class="gel-form-group"><label>Durée de vie (années)</label><input class="gel-form-control" type="number" value="5"></div>
            <div class="gel-form-group"><label>Méthode d'amortissement</label>
                <select class="gel-form-select"><option value="lineaire">Linéaire</option><option value="degressif">Dégressif</option></select>
            </div>
        </form>
    </div>
    <div class="gel-panel-footer">
        <button class="gel-btn gel-btn-secondary" onclick="closePanel('assetPanel')">Annuler</button>
        <button class="gel-btn gel-btn-primary">Créer l'actif</button>
    </div>
</div>
@endsection
