@php $currentSection = 'rapports'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Rapports - GEL Accountant')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Rapports</h1><p class="gel-page-subtitle">Tous vos rapports financiers</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouveau rapport</button>
</div>
<div class="gel-kpi-grid">
    <div class="gel-kpi-card"><div class="gel-kpi-label">Rapports sauvegardés</div><div class="gel-kpi-value">0</div></div>
</div>
<div class="gel-tabs">
    <a class="gel-tab active" onclick="switchTab('rptTab','standards')" rptTab="standards">Standards</a>
    <a class="gel-tab" onclick="switchTab('rptTab','personnalises')" rptTab="personnalises">Personnalisés</a>
    <a class="gel-tab" onclick="switchTab('rptTab','centre')" rptTab="centre">Centre de performance</a>
</div>
<div class="gel-tab-content active" rptTab="standards">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-balance-scale" style="font-size:24px;"></i> Bilan
        </a>
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-chart-line" style="font-size:24px;"></i> Résultat
        </a>
        <a href="{{ route('gel-accountant.comptabilite.grand-livre') }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-book" style="font-size:24px;"></i> Grand livre
        </a>
        <a href="{{ route('gel-accountant.comptabilite.balance') }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-balance-scale" style="font-size:24px;"></i> Balance
        </a>
        <a href="#" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-clock" style="font-size:24px;"></i> A/R Aging
        </a>
        <a href="#" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-truck" style="font-size:24px;"></i> A/P Aging
        </a>
    </div>
</div>
<div class="gel-tab-content" rptTab="personnalises">
    <div class="gel-card"><div class="gel-card-body">
        <div class="gel-empty"><i class="fas fa-sliders-h"></i><h3>Rapports personnalisés</h3><p>Créez vos propres rapports avec le générateur.</p></div>
    </div></div>
</div>
<div class="gel-tab-content" rptTab="centre">
    <div class="gel-card"><div class="gel-card-body">
        <div class="gel-empty"><i class="fas fa-tachometer-alt"></i><h3>Centre de performance</h3><p>Dashboard personnalisable avec widgets.</p></div>
    </div></div>
</div>
@endsection
