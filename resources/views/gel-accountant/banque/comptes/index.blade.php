@extends('layouts.gel-accountant')

@section('title', 'Comptes Bancaires & Caisse')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-credit-card" style="color:var(--gel-primary); margin-right:8px;"></i> Comptes Bancaires & Caisse
        </h1>
        <p class="gel-page-subtitle">Gérez vos comptes bancaires, caisses et comptes mobile money.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newAccountModal').style.display='flex'">
            <i class="fas fa-plus"></i> Nouveau Compte
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    @forelse($comptes as $compte)
        <div class="gel-card" style="position: relative; overflow: hidden;">
            <div style="height:4px; background:var(--gel-primary); width:100%; position:absolute; top:0; left:0;"></div>
            <div style="padding: 20px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                    <div>
                        <div style="font-weight:700; font-size:16px; color:var(--gel-text-primary);">
                            {{ $compte->name }}
                        </div>
                        <div style="font-size:12px; color:var(--gel-text-secondary);">
                            {{ $compte->bank_name }}
                            @if($compte->is_default) <span class="gel-badge" style="margin-left:4px;">Par défaut</span> @endif
                        </div>
                    </div>
                    <div>
                        @if($compte->type === 'banque')
                            <i class="fas fa-university" style="color:var(--gel-primary); font-size:24px;"></i>
                        @elseif($compte->type === 'caisse')
                            <i class="fas fa-cash-register" style="color:var(--gel-success); font-size:24px;"></i>
                        @else
                            <i class="fas fa-mobile-alt" style="color:var(--gel-warning); font-size:24px;"></i>
                        @endif
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-size:11px; color:var(--gel-text-muted); text-transform:uppercase; font-weight:700; margin-bottom:4px;">Numéro de compte</div>
                    <div style="font-size:14px; font-family:monospace;">{{ $compte->account_number }}</div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:flex-end; border-top:1px solid var(--gel-border); padding-top:16px;">
                    <div>
                        <div style="font-size:11px; color:var(--gel-text-muted); text-transform:uppercase; font-weight:700; margin-bottom:2px;">Solde comptable</div>
                        <div style="font-size:18px; font-weight:700; color:var(--gel-primary);">
                            {{ number_format($compte->current_balance, 0, ',', ' ') }} {{ $compte->currency }}
                        </div>
                    </div>
                    <a href="{{ route('gel-accountant.banque.comptes.show', $compte->id) }}" class="gel-btn gel-btn-secondary" style="font-size:12px; padding: 6px 12px;">
                        Gérer
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="gel-empty" style="grid-column: 1 / -1;">
            <i class="fas fa-university"></i>
            <h3>Aucun compte configuré</h3>
            <p>Ajoutez votre premier compte bancaire ou caisse pour commencer à gérer votre trésorerie.</p>
            <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newAccountModal').style.display='flex'" style="margin-top:16px;">
                <i class="fas fa-plus"></i> Nouveau Compte
            </button>
        </div>
    @endforelse
</div>

{{-- MODAL NOUVEAU COMPTE --}}
<div id="newAccountModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="gel-card" style="width:100%; max-width:600px; padding:0; animation: fadeIn 0.2s ease-out;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; margin:0;"><i class="fas fa-plus"></i> Nouveau Compte</h3>
            <button onclick="document.getElementById('newAccountModal').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:var(--gel-text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('gel-accountant.banque.comptes.store') }}" method="POST">
            @csrf
            <div style="padding:20px;">
                <div class="doc-form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="doc-form-group">
                        <label class="doc-label">Type de compte *</label>
                        <select name="type" class="doc-input" required>
                            <option value="banque">Banque (Courant, Épargne...)</option>
                            <option value="caisse">Caisse (Espèces)</option>
                            <option value="mobile_money">Mobile Money (MoMo, Flooz...)</option>
                        </select>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Devise *</label>
                        <select name="currency" class="doc-input" required>
                            <option value="FCFA">FCFA</option>
                            <option value="EUR">Euro (€)</option>
                            <option value="USD">Dollar ($)</option>
                        </select>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Nom interne du compte *</label>
                        <input type="text" name="name" class="doc-input" placeholder="Ex: BOA Courant" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Établissement (Banque/Opérateur) *</label>
                        <input type="text" name="bank_name" class="doc-input" placeholder="Ex: BOA Bénin" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Numéro de compte / RIB / N° Tel *</label>
                        <input type="text" name="account_number" class="doc-input" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">IBAN (Optionnel)</label>
                        <input type="text" name="iban" class="doc-input">
                    </div>
                    
                    <div class="doc-form-group" style="grid-column: 1 / -1;">
                        <label class="doc-label">Compte comptable associé (SYSCOHADA)</label>
                        <select name="accounting_account_id" class="doc-input">
                            <option value="">— Sélectionner —</option>
                            @foreach($comptesComptables as $cc)
                                <option value="{{ $cc->id }}">{{ $cc->account_number }} - {{ $cc->name }}</option>
                            @endforeach
                        </select>
                        <small style="color:var(--gel-text-secondary); margin-top:4px;">Recommandé : lier à un compte 521 (Banque) ou 571 (Caisse)</small>
                    </div>
                    
                    <div class="doc-form-group">
                        <label class="doc-label">Solde d'ouverture *</label>
                        <input type="number" name="opening_balance" class="doc-input" value="0" step="1" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Date d'ouverture / reprise *</label>
                        <input type="date" name="opening_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>
            <div style="padding:16px 20px; border-top:1px solid var(--gel-border); display:flex; justify-content:flex-end; gap:8px; background:#f9fafb; border-radius:0 0 8px 8px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="document.getElementById('newAccountModal').style.display='none'">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush
