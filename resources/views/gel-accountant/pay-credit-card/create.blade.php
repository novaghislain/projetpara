@extends('layouts.gel-accountant')

@section('title', 'Payer la carte de Crédit')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-credit-card" style="color:var(--gel-primary); margin-right:8px;"></i> Payer la carte de Crédit</h1>
        <p class="gel-page-subtitle">Enregistrez un paiement pour régler le solde de votre carte de Crédit d'entreprise.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="payCreditCardForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:20px; align-items:end;">
        <div class="doc-form-group">
            <label class="doc-label">Compte source (Payer depuis) *</label>
            <select name="bank_account_id" class="doc-input" required>
                <option value="">— Compte bancaire principal —</option>
                @foreach(\App\Models\BankAccount::where('client_id', auth()->user()->client_id ?? 0)->where('is_active', true)->where('type', 'banque')->get() as $ba)
                    <option value="{{ $ba->id }}">{{ $ba->bank_name }} — {{ $ba->account_number }}</option>
                @endforeach
            </select>
        </div>
        <div style="padding-bottom:10px;">
            <i class="fas fa-arrow-right" style="font-size:24px; color:var(--gel-primary);"></i>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Carte de Crédit À  payer *</label>
            <select name="credit_card_id" class="doc-input" required>
                <option value="">— Sélectionner une carte —</option>
                @foreach(\App\Models\BankAccount::where('client_id', auth()->user()->client_id ?? 0)->where('is_active', true)->where('type', 'carte_credit')->get() as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->bank_name }} — {{ $cc->account_number }} (Solde: {{ number_format($cc->current_balance, 0, ',', ' ') }} FCFA)</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Montant du paiement (FCFA) *</label>
            <input type="number" name="amount" class="doc-input" value="0" min="0" required style="font-size:18px; font-weight:600;">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date de paiement *</label>
            <input type="date" name="payment_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">NÂ° de Chèque ou Réf.</label>
            <input type="text" name="reference_number" class="doc-input" placeholder="Réf. virement...">
        </div>
        <div class="doc-form-group" style="grid-column: span 3;">
            <label class="doc-label">Mémo</label>
            <textarea name="memo" class="doc-input" rows="2" placeholder="Ex: Paiement solde relevé mensuel..."></textarea>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer le paiement</button>
</div>
</form>


@endsection

