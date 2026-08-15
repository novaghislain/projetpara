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