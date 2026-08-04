@php $currentSection = 'banque'; @endphp
@extends('layouts.gel-business')
@section('title', 'Transactions bancaires - GEL Business')
@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Transactions bancaires</h1>
        <p class="gel-page-subtitle">Consultez et catégorisez vos flux bancaires</p>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="gel-btn gel-btn-secondary"><i class="fas fa-upload"></i> Importer un relevé</button>
        <button class="gel-btn gel-btn-primary" onclick="openBankConnectPanel()"><i class="fas fa-link"></i> Connecter une banque</button>
    </div>
</div>
<div class="gel-tabs">
    <a class="gel-tab active" onclick="switchTab('bqTab','transactions')" bqTab="transactions">Transactions</a>
    <a class="gel-tab" onclick="switchTab('bqTab','reconcile')" bqTab="reconcile">Rapprochement</a>
    <a class="gel-tab" onclick="switchTab('bqTab','rules')" bqTab="rules">Règles auto</a>
</div>
<div class="gel-tab-content active" bqTab="transactions">
    <div class="gel-card" style="min-height:350px;display:flex;flex-direction:column;">
        <div class="gel-card-body" style="flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px;">
            <div class="gel-empty" style="text-align:center;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-exchange-alt" style="font-size:24px;color:var(--gel-text-muted);"></i>
                </div>
                <h3 style="font-size:16px;margin:0 0 8px;">Aucune transaction</h3>
                <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Connectez votre banque ou importez un relevé CSV/PDF pour voir vos transactions automatiquement.</p>
                <button class="gel-btn gel-btn-primary" onclick="openBankConnectPanel()"><i class="fas fa-link"></i> Connecter une banque</button>
            </div>
        </div>
    </div>
</div>
<div class="gel-tab-content" bqTab="reconcile">
    <div class="gel-card" style="min-height:350px;display:flex;align-items:center;justify-content:center;">
        <div class="gel-empty" style="text-align:center;">
            <i class="fas fa-handshake" style="font-size:32px;color:var(--gel-text-muted);margin-bottom:16px;"></i>
            <h3 style="font-size:16px;margin:0 0 8px;">Rapprochement bancaire</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto;">Sélectionnez un compte pour commencer à rapprocher vos écritures.</p>
        </div>
    </div>
</div>
<div class="gel-tab-content" bqTab="rules">
    <div class="gel-card" style="min-height:350px;display:flex;align-items:center;justify-content:center;">
        <div class="gel-empty" style="text-align:center;">
            <i class="fas fa-cogs" style="font-size:32px;color:var(--gel-text-muted);margin-bottom:16px;"></i>
            <h3 style="font-size:16px;margin:0 0 8px;">Règles de catégorisation</h3>
            <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto;">Créez des règles pour catégoriser automatiquement vos transactions récurrentes.</p>
        </div>
    </div>
</div>
@endsection
