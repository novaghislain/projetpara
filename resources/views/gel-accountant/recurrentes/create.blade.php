@extends('layouts.gel-accountant')
@section('title', 'Créer une Transaction Récurrente')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-sync-alt" style="color:var(--gel-primary); margin-right:8px;"></i> Nouveau Modèle Récurrent</h1>
        <p class="gel-page-subtitle">Configurez une écriture ou une action à répéter automatiquement.</p>
    </div>
    <a href="{{ route('gel-accountant.recurrentes.index') }}" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<form method="POST" action="{{ route('gel-accountant.recurrentes.store') }}">
@csrf

<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px;">
    <div>
        <div class="gel-card p-4 mb-4">
            <div class="invoice-section-title"><i class="fas fa-info-circle"></i> Informations du modèle</div>

            <div class="gel-form-group">
                <label>Titre *</label>
                <input type="text" name="title" class="gel-form-control" placeholder="Ex: Loyer bureau mensuel" required>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="gel-form-group">
                    <label>Type de récurrence *</label>
                    <select name="type" class="gel-form-select" required id="typeSelect" onchange="toggleFields()">
                        <option value="scheduled">📅 Programmée (auto)</option>
                        <option value="reminder">🔔 Rappel (manuel)</option>
                        <option value="template">📋 Template (ponctuel)</option>
                    </select>
                </div>
                <div class="gel-form-group">
                    <label>Type de transaction *</label>
                    <select name="transaction_type" class="gel-form-select" required>
                        <option value="journal_entry">Écriture de journal</option>
                        <option value="invoice">Facture</option>
                        <option value="expense">Dépense</option>
                        <option value="credit_note">Note de crédit</option>
                    </select>
                </div>
            </div>

            <div class="gel-form-group">
                <label>Description</label>
                <textarea name="description" class="gel-form-control" rows="2" placeholder="Ex: Loyer mensuel bureau principal — Propriétaire: M. Kofi"></textarea>
            </div>
        </div>

        <div class="gel-card p-4 mb-4">
            <div class="invoice-section-title"><i class="fas fa-book"></i> Données du modèle d'écriture</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                <div class="gel-form-group">
                    <label>Montant (FCFA)</label>
                    <input type="number" name="amount" class="gel-form-control" placeholder="0" min="0" step="1">
                </div>
                <div class="gel-form-group">
                    <label>Compte débit</label>
                    <input type="text" name="account_debit" class="gel-form-control" placeholder="Ex: 613" maxlength="10">
                </div>
                <div class="gel-form-group">
                    <label>Compte crédit</label>
                    <input type="text" name="account_credit" class="gel-form-control" placeholder="Ex: 521" maxlength="10">
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="gel-card p-4 mb-4" id="scheduleOptions">
            <div class="invoice-section-title"><i class="fas fa-calendar"></i> Planification</div>

            <div class="gel-form-group">
                <label>Fréquence</label>
                <select name="frequency" class="gel-form-select">
                    <option value="">— Aucune —</option>
                    <option value="daily">Quotidienne</option>
                    <option value="weekly">Hebdomadaire</option>
                    <option value="biweekly">Bimensuelle</option>
                    <option value="monthly" selected>Mensuelle</option>
                    <option value="quarterly">Trimestrielle</option>
                    <option value="yearly">Annuelle</option>
                </select>
            </div>

            <div class="gel-form-group">
                <label>Première occurrence</label>
                <input type="date" name="next_occurrence" class="gel-form-control" value="{{ date('Y-m-d', strtotime('+1 month')) }}">
            </div>

            <div class="gel-form-group">
                <label>Date de fin (optionnel)</label>
                <input type="date" name="end_date" class="gel-form-control">
            </div>

            <div class="gel-form-group">
                <label>Nb max d'occurrences (optionnel)</label>
                <input type="number" name="max_occurrences" class="gel-form-control" placeholder="Ex: 12 pour un an" min="1">
            </div>
        </div>

        <button type="submit" class="gel-btn gel-btn-primary" style="width:100%;">
            <i class="fas fa-save"></i> Créer le modèle récurrent
        </button>
    </div>
</div>
</form>

<script>
function toggleFields() {
    const type = document.getElementById('typeSelect').value;
    const schedOpts = document.getElementById('scheduleOptions');
    schedOpts.style.opacity = type === 'template' ? '0.4' : '1';
    schedOpts.querySelectorAll('select, input').forEach(el => el.disabled = type === 'template');
}
</script>
@endsection
