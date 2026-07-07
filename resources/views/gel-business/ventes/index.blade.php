@php $currentSection = 'ventes'; @endphp
@extends('layouts.gel-business')
@section('title', 'Ventes - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Ventes</h1><p class="gel-page-subtitle">Aperçu des ventes</p></div>
</div>
<div class="gel-kpi-grid">
    <div class="gel-kpi-card"><div class="gel-kpi-label">Factures</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Clients</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">CA total</div><div class="gel-kpi-value">CFA 0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Impayés</div><div class="gel-kpi-value">CFA 0</div></div>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        <div class="gel-empty">
            <i class="fas fa-shopping-cart"></i>
            <h3>Aucune vente</h3>
            <p>Les ventes apparaîtront ici.</p>
        </div>
    </div>
</div>
@endsection
