@php $currentSection = 'rapports'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Rapports - GEL Accountant')
@section('content')
<div class="gel-page-header animate-fade" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 class="gel-page-title" style="font-size: 18px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">Rapports</h1>
        <p class="gel-page-subtitle" style="font-size: 12px; color: var(--gel-text-secondary);">Tous vos rapports financiers</p>
    </div>
    <button class="gel-btn gel-btn-primary gel-btn-sm" style="font-weight:600;display:flex;align-items:center;gap:6px;border-radius:6px;padding:8px 14px;">
        <i class="fas fa-plus"></i> Nouveau rapport
    </button>
</div>
<div class="gel-kpi-grid animate-fade delay-1">
    <div class="gel-kpi-card"><div class="gel-kpi-label">Rapports sauvegardés</div><div class="gel-kpi-value">0</div></div>
</div>
<div class="gel-tabs animate-fade delay-1">
    <a class="gel-tab active" onclick="switchTab('rptTab','standards')" rptTab="standards">Standards</a>
    <a class="gel-tab" onclick="switchTab('rptTab','personnalises')" rptTab="personnalises">Personnalisés</a>
    <a class="gel-tab" onclick="switchTab('rptTab','centre')" rptTab="centre">Centre de performance</a>
</div>
<div class="gel-tab-content active animate-fade delay-1" rptTab="standards">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers') ?? '#' }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-balance-scale" style="font-size:24px;"></i> Bilan
        </a>
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers') ?? '#' }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-chart-line" style="font-size:24px;"></i> Résultat
        </a>
        <a href="{{ route('gel-accountant.comptabilite.grand-livre') ?? '#' }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
            <i class="fas fa-book" style="font-size:24px;"></i> Grand livre
        </a>
        <a href="{{ route('gel-accountant.comptabilite.balance') ?? '#' }}" class="gel-btn gel-btn-secondary" style="justify-content:flex-start;padding:16px;">
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
<div class="gel-tab-content animate-fade delay-1" rptTab="personnalises">
    <div class="pro-panel mb-4"><div class="panel-body p-4">
        <div class="gel-empty"><i class="fas fa-sliders-h"></i><h3>Rapports personnalisés</h3><p>Créez vos propres rapports avec le générateur.</p></div>
    </div></div>
</div>
<div class="gel-tab-content animate-fade delay-1" rptTab="centre">
    <div class="pro-panel mb-4"><div class="panel-body p-4">
        <div class="gel-empty"><i class="fas fa-tachometer-alt"></i><h3>Centre de performance</h3><p>Dashboard personnalisable avec widgets.</p></div>
    </div></div>
</div>
@endsection
