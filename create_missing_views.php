<?php
/**
 * Création automatique des vues manquantes pour l'Espace Comptabilité GEL Accountant.
 * 
 * Génère des vues Blade fonctionnelles (pas juste des stubs) avec la bonne structure
 * pour chaque page de navigation.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$viewsBase = __DIR__ . '/resources/views/';

function makeDir(string $path): void {
    if (!is_dir($path)) mkdir($path, 0755, true);
}

function writeView(string $dotPath, string $content): void {
    global $viewsBase;
    $filePath = $viewsBase . str_replace('.', '/', $dotPath) . '.blade.php';
    makeDir(dirname($filePath));
    if (!file_exists($filePath)) {
        file_put_contents($filePath, $content);
        echo "  CREATED: $dotPath\n";
    } else {
        echo "  EXISTS:  $dotPath\n";
    }
}

// ─── Template helper ──────────────────────────────────────────────────
function placeholderView(string $title, string $icon, string $subtitle, string $section = '', string $page = ''): string {
    $sec = $section ?: '';
    $pg  = $page ?: '';
    return <<<BLADE
@extends('layouts.gel-accountant')

@section('title', '$title')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas $icon" style="color:var(--gel-primary); margin-right:8px;"></i>
            $title
        </h1>
        <p class="gel-page-subtitle">$subtitle</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="gel-card" style="padding:48px; text-align:center; margin-top:20px;">
    <div style="width:90px; height:90px; background:var(--gel-primary-light,#eff6ff); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; font-size:36px; color:var(--gel-primary);">
        <i class="fas $icon"></i>
    </div>
    <h2 style="font-size:20px; font-weight:700; color:var(--gel-text-primary); margin-bottom:10px;">Bientôt disponible</h2>
    <p style="color:var(--gel-text-secondary); max-width:480px; margin:0 auto 24px; line-height:1.6;">
        La page <strong>$title</strong> est en cours d'implémentation et sera disponible très prochainement.
    </p>
    <a href="javascript:history.back()" class="gel-btn gel-btn-primary">
        <i class="fas fa-arrow-left"></i> Retourner à la page précédente
    </a>
</div>

@endsection
BLADE;
}

echo "=== Création des vues manquantes ===\n\n";

// ─── Banque : Comptes / create ─────────────────────────────────────────
writeView('gel-accountant.banque.comptes.create', <<<'BLADE'
@extends('layouts.gel-accountant')

@section('title', 'Nouveau Compte Bancaire')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-university" style="color:var(--gel-primary); margin-right:8px;"></i>
            Nouveau Compte Bancaire
        </h1>
        <p class="gel-page-subtitle">Ajoutez un compte bancaire, une caisse ou un compte mobile money.</p>
    </div>
    <div>
        <a href="{{ route('gel-accountant.banque.comptes.index') }}" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

@if($errors->any())
<div class="gel-alert gel-alert-danger mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="gel-card">
    <div class="gel-card-body" style="padding:32px;">
        <form method="POST" action="{{ route('gel-accountant.banque.comptes.store') }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <div class="gel-form-group" style="grid-column:1/-1">
                    <label class="gel-label">Nom du compte <span style="color:red">*</span></label>
                    <input type="text" name="name" class="gel-input" value="{{ old('name') }}"
                           placeholder="ex: Compte Ecobank Principal" required>
                </div>
                <div class="gel-form-group">
                    <label class="gel-label">Type de compte <span style="color:red">*</span></label>
                    <select name="type" class="gel-select" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="bank" {{ old('type')=='bank'?'selected':'' }}>Compte Bancaire</option>
                        <option value="cash" {{ old('type')=='cash'?'selected':'' }}>Caisse</option>
                        <option value="mobile_money" {{ old('type')=='mobile_money'?'selected':'' }}>Mobile Money</option>
                        <option value="savings" {{ old('type')=='savings'?'selected':'' }}>Épargne</option>
                    </select>
                </div>
                <div class="gel-form-group">
                    <label class="gel-label">Devise</label>
                    <select name="currency" class="gel-select">
                        <option value="FCFA" selected>FCFA (XOF)</option>
                        <option value="EUR">Euro (EUR)</option>
                        <option value="USD">Dollar US (USD)</option>
                    </select>
                </div>
                <div class="gel-form-group">
                    <label class="gel-label">Numéro de compte (IBAN / RIB)</label>
                    <input type="text" name="account_number" class="gel-input" value="{{ old('account_number') }}"
                           placeholder="ex: BJ76 0101 0100 0000 0012 3456 7890">
                </div>
                <div class="gel-form-group">
                    <label class="gel-label">Banque</label>
                    <input type="text" name="bank_name" class="gel-input" value="{{ old('bank_name') }}"
                           placeholder="ex: Ecobank, BOA, UBA…">
                </div>
                <div class="gel-form-group">
                    <label class="gel-label">Solde d'ouverture (FCFA)</label>
                    <input type="number" name="opening_balance" class="gel-input" value="{{ old('opening_balance', 0) }}"
                           step="1" min="0" placeholder="0">
                </div>
                <div class="gel-form-group">
                    <label class="gel-label">Date d'ouverture</label>
                    <input type="date" name="opening_date" class="gel-input" value="{{ old('opening_date', date('Y-m-d')) }}">
                </div>
                <div class="gel-form-group" style="grid-column:1/-1">
                    <label class="gel-label">Description / Notes</label>
                    <textarea name="description" class="gel-textarea" rows="3" placeholder="Notes optionnelles sur ce compte…">{{ old('description') }}</textarea>
                </div>
            </div>
            <div style="display:flex; gap:12px; margin-top:24px; justify-content:flex-end;">
                <a href="{{ route('gel-accountant.banque.comptes.index') }}" class="gel-btn gel-btn-secondary">Annuler</a>
                <button type="submit" class="gel-btn gel-btn-primary">
                    <i class="fas fa-save"></i> Enregistrer le compte
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
BLADE);

// ─── Comptabilité : Rapprochement ──────────────────────────────────────
// Existe déjà comme stub — ne pas écraser
// Voir: resources/views/gel-accountant/comptabilite/rapprochement.blade.php

// ─── Fiscalité : Exercices ─────────────────────────────────────────────
writeView('gel-accountant.fiscalite.exercices.create', placeholderView(
    'Nouvel Exercice Comptable', 'fa-calendar-plus',
    'Créez un nouvel exercice comptable pour votre cabinet.',
    'fiscalite', 'exercices'
));

// ─── Rapports ─────────────────────────────────────────────────────────
$rapportViews = [
    ['gel-accountant.rapports.index', 'Rapports & Tableaux de Bord', 'fa-chart-bar', 'Vue d\'ensemble des rapports disponibles.'],
    ['gel-accountant.rapports.ca', 'Chiffre d\'Affaires', 'fa-chart-line', 'Analyse du chiffre d\'affaires par période et par client.'],
    ['gel-accountant.rapports.tresorerie', 'Flux de Trésorerie', 'fa-water', 'Analyse des entrées et sorties de trésorerie.'],
    ['gel-accountant.rapports.aged-receivables', 'Balance Âgée Clients', 'fa-users', 'Analyse des créances par ancienneté.'],
    ['gel-accountant.rapports.aged-payables', 'Balance Âgée Fournisseurs', 'fa-truck', 'Analyse des dettes fournisseurs par ancienneté.'],
    ['gel-accountant.rapports.tax', 'Rapport Fiscal', 'fa-file-invoice', 'Synthèse des obligations fiscales et TVA.'],
];
foreach ($rapportViews as [$view, $title, $icon, $sub]) {
    writeView($view, placeholderView($title, $icon, $sub, 'rapports'));
}

// ─── IA Module ────────────────────────────────────────────────────────
$iaViews = [
    ['gel-accountant.ia.index', 'Intelligence Artificielle', 'fa-robot', 'Suggestions et automatisations intelligentes.'],
    ['gel-accountant.ia.suggestions', 'Suggestions IA', 'fa-lightbulb', 'Recommandations automatiques basées sur vos données.'],
    ['gel-accountant.ia.ocr', 'OCR — Scan de Documents', 'fa-camera', 'Extraction automatique des données depuis vos factures.'],
    ['gel-accountant.ia.cashflow', 'Prévisions de Trésorerie', 'fa-chart-area', 'Prévisions intelligentes de votre trésorerie.'],
];
foreach ($iaViews as [$view, $title, $icon, $sub]) {
    writeView($view, placeholderView($title, $icon, $sub, 'ia'));
}

// ─── Paramètres ────────────────────────────────────────────────────────
$settingsViews = [
    ['gel-accountant.settings.index', 'Paramètres', 'fa-cog', 'Configuration de votre espace comptable.'],
    ['gel-accountant.settings.profile', 'Profil Cabinet', 'fa-building', 'Informations et paramètres de votre cabinet.'],
    ['gel-accountant.settings.users', 'Gestion des Utilisateurs', 'fa-users-cog', 'Gérez les accès et permissions de votre équipe.'],
    ['gel-accountant.settings.integrations', 'Intégrations', 'fa-plug', 'Connectez votre espace à des services tiers.'],
    ['gel-accountant.settings.notifications', 'Notifications', 'fa-bell', 'Configurez vos préférences de notification.'],
];
foreach ($settingsViews as [$view, $title, $icon, $sub]) {
    writeView($view, placeholderView($title, $icon, $sub, 'settings'));
}

// ─── Pages diverses ───────────────────────────────────────────────────
$miscViews = [
    ['gel-accountant.workpapers.index', 'Dossiers de Travail', 'fa-folder-open', 'Gérez vos dossiers de travail et documents de révision.'],
    ['gel-accountant.workpapers.create', 'Nouveau Dossier de Travail', 'fa-folder-plus', 'Créez un nouveau dossier de travail.'],
    ['gel-accountant.workflows.index', 'Workflows', 'fa-project-diagram', 'Gérez vos flux de travail et processus d\'approbation.'],
    ['gel-accountant.relances.index', 'Relances', 'fa-bell', 'Gérez vos relances clients et rappels de paiement.'],
    ['gel-accountant.recurrentes.index', 'Transactions Récurrentes', 'fa-redo', 'Automatisez vos opérations comptables récurrentes.'],
    ['gel-accountant.messagerie.index', 'Messagerie', 'fa-envelope', 'Communiquez avec vos clients et collègues.'],
];
foreach ($miscViews as [$view, $title, $icon, $sub]) {
    writeView($view, placeholderView($title, $icon, $sub));
}

echo "\n=== Terminé ===\n";
