@extends('layouts.gel-accountant')

@section('title', 'Transfert entre comptes')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-exchange-alt" style="color:var(--gel-primary); margin-right:8px;"></i> Transfert entre comptes</h1>
        <p class="gel-page-subtitle">Transférez des fonds d'un compte bancaire À  un autre.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="transferForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:20px; align-items:end;">
        <div class="doc-form-group">
            <label class="doc-label">Transférer depuis *</label>
            <select name="from_account" class="doc-input" required>
                <option value="">— Compte source —</option>
                @foreach(\App\Models\BankAccount::where('client_id', auth()->user()->client_id ?? 0)->where('is_active', true)->get() as $ba)
                    <option value="{{ $ba->id }}">{{ $ba->bank_name }} — {{ $ba->account_number }}</option>
                @endforeach
            </select>
        </div>
        <div style="padding-bottom:10px;">
            <i class="fas fa-arrow-right" style="font-size:24px; color:var(--gel-primary);"></i>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Vers le compte *</label>
            <select name="to_account" class="doc-input" required>
                <option value="">— Compte destination —</option>
                @foreach(\App\Models\BankAccount::where('client_id', auth()->user()->client_id ?? 0)->where('is_active', true)->get() as $ba)
                    <option value="{{ $ba->id }}">{{ $ba->bank_name }} — {{ $ba->account_number }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Montant du transfert (FCFA) *</label>
            <input type="number" name="amount" class="doc-input" value="0" min="0" required style="font-size:18px; font-weight:600;">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date du transfert *</label>
            <input type="date" name="transfer_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group" style="grid-column: span 2;">
            <label class="doc-label">Mémo / Motif</label>
            <textarea name="memo" class="doc-input" rows="2" placeholder="Ex: Transfert de trésorerie pour couvrir les charges du mois..."></textarea>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-exchange-alt"></i> Exécuter le transfert</button>
</div>
</form>


@endsection

