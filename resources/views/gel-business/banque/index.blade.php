@php $currentSection = 'banque'; @endphp
@extends('layouts.gel-business')
@section('title', 'Transactions bancaires - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Transactions bancaires</h1><p class="gel-page-subtitle">Flux bancaires</p></div>
</div>
<div class="gel-tabs">
    <a class="gel-tab active" onclick="switchTab('bqTab','transactions')" bqTab="transactions">Bank transactions</a>
    <a class="gel-tab" onclick="switchTab('bqTab','reconcile')" bqTab="reconcile">Reconcile</a>
    <a class="gel-tab" onclick="switchTab('bqTab','rules')" bqTab="rules">Rules</a>
</div>
<div class="gel-tab-content active" bqTab="transactions">
    <div class="gel-card"><div class="gel-card-body">
        <div class="gel-empty">
            <i class="fas fa-exchange-alt"></i>
            <h3>Aucune transaction</h3>
            <p>Connectez votre banque pour importer vos transactions.</p>
            <div class="gel-bank-connect" style="display:inline-flex;">Connecter une banque</div>
        </div>
    </div></div>
</div>
<div class="gel-tab-content" bqTab="reconcile">
    <div class="gel-card"><div class="gel-card-body">
        <div class="gel-empty">
            <i class="fas fa-handshake"></i>
            <h3>Rapprochement bancaire</h3>
            <p>Sélectionnez un compte pour commencer le rapprochement.</p>
        </div>
    </div></div>
</div>
<div class="gel-tab-content" bqTab="rules">
    <div class="gel-card"><div class="gel-card-body">
        <div class="gel-empty">
            <i class="fas fa-rule"></i>
            <h3>Règles bancaires</h3>
            <p>Créez des règles pour catégoriser automatiquement les transactions.</p>
        </div>
    </div></div>
</div>
@endsection
