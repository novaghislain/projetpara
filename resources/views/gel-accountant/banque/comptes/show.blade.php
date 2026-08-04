@extends('layouts.gel-accountant')

@section('title', 'Détails du Compte')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-credit-card" style="color:var(--gel-primary); margin-right:8px;"></i> {{ $compte->name }}
        </h1>
        <p class="gel-page-subtitle">{{ $compte->bank_name }} — {{ $compte->account_number }}</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.banque.comptes.index') }}" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <button class="gel-btn gel-btn-primary" onclick="document.getElementById('editAccountModal').style.display='flex'">
            <i class="fas fa-edit"></i> Modifier
        </button>
        <form action="{{ route('gel-accountant.banque.comptes.destroy', $compte->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce compte bancaire ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="gel-btn gel-btn-danger">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </div>
</div>

<div class="row">
    {{-- Colonne gauche : infos --}}
    <div class="col-md-4">
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:14px; font-weight:700; margin-bottom:16px; border-bottom:1px solid var(--gel-border); padding-bottom:8px;">
                Soldes
            </h3>
            <div style="margin-bottom:12px;">
                <div style="font-size:12px; color:var(--gel-text-secondary);">Solde courant (Comptable)</div>
                <div style="font-size:24px; font-weight:700; color:var(--gel-primary);">
                    {{ number_format($compte->current_balance, 0, ',', ' ') }} {{ $compte->currency }}
                </div>
            </div>
            <div>
                <div style="font-size:12px; color:var(--gel-text-secondary);">Solde rapproché (Banque)</div>
                <div style="font-size:18px; font-weight:600; color:var(--gel-success);">
                    {{ number_format($compte->reconciled_balance, 0, ',', ' ') }} {{ $compte->currency }}
                </div>
                @if($compte->last_reconciliation_date)
                    <div style="font-size:11px; color:var(--gel-text-muted);">Au {{ \Carbon\Carbon::parse($compte->last_reconciliation_date)->format('d/m/Y') }}</div>
                @else
                    <div style="font-size:11px; color:var(--gel-text-muted);">Aucun rapprochement</div>
                @endif
            </div>
        </div>

        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:14px; font-weight:700; margin-bottom:16px; border-bottom:1px solid var(--gel-border); padding-bottom:8px;">
                Informations du compte
            </h3>
            
            <div style="margin-bottom:12px;">
                <span style="font-size:11px; color:var(--gel-text-muted); display:block; text-transform:uppercase;">Établissement</span>
                <span style="font-weight:600; font-size:13px;">{{ $compte->bank_name }}</span>
            </div>
            <div style="margin-bottom:12px;">
                <span style="font-size:11px; color:var(--gel-text-muted); display:block; text-transform:uppercase;">Type</span>
                <span style="font-weight:600; font-size:13px;">{{ ucfirst(str_replace('_', ' ', $compte->type)) }}</span>
            </div>
            <div style="margin-bottom:12px;">
                <span style="font-size:11px; color:var(--gel-text-muted); display:block; text-transform:uppercase;">Numéro (RIB / Tel)</span>
                <span style="font-weight:600; font-size:14px; font-family:monospace;">{{ $compte->account_number }}</span>
            </div>
            @if($compte->iban)
            <div style="margin-bottom:12px;">
                <span style="font-size:11px; color:var(--gel-text-muted); display:block; text-transform:uppercase;">IBAN</span>
                <span style="font-weight:600; font-size:14px; font-family:monospace;">{{ $compte->iban }}</span>
            </div>
            @endif
            @if($compte->swift)
            <div style="margin-bottom:12px;">
                <span style="font-size:11px; color:var(--gel-text-muted); display:block; text-transform:uppercase;">SWIFT/BIC</span>
                <span style="font-weight:600; font-size:14px; font-family:monospace;">{{ $compte->swift }}</span>
            </div>
            @endif
            <div style="margin-bottom:12px;">
                <span style="font-size:11px; color:var(--gel-text-muted); display:block; text-transform:uppercase;">Compte Comptable Lié</span>
                @if($compte->accountingAccount)
                    <span style="font-weight:600; font-size:13px;" class="gel-badge" style="background:#e0f2fe; color:#0284c7;">
                        {{ $compte->accountingAccount->account_number }} - {{ $compte->accountingAccount->name }}
                    </span>
                @else
                    <span style="font-size:12px; color:var(--gel-text-secondary); font-style:italic;">Aucun compte lié</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Colonne droite : Transactions récentes --}}
    <div class="col-md-8">
        <div class="gel-card p-4">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:15px; font-weight:700; margin:0;">Transactions récentes</h3>
                <a href="{{ route('gel-accountant.banque.transactions.index', ['account_id' => $compte->id]) }}" style="font-size:12px; color:var(--gel-primary); font-weight:600; text-decoration:none;">Voir toutes →</a>
            </div>
            
            <table class="doc-lines-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th style="text-align:right;">Débit</th>
                        <th style="text-align:right;">Crédit</th>
                        <th style="text-align:center;">Pointé</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $tx)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $tx->description }}</strong>
                            @if($tx->reference) <br><small style="color:var(--gel-text-muted);">Réf: {{ $tx->reference }}</small> @endif
                        </td>
                        <td style="text-align:right;">
                            @if($tx->debit > 0)
                                <span style="color:var(--gel-danger);">{{ number_format($tx->debit, 0, ',', ' ') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($tx->credit > 0)
                                <span style="color:var(--gel-success);">{{ number_format($tx->credit, 0, ',', ' ') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($tx->is_reconciled)
                                <i class="fas fa-check-circle" style="color:var(--gel-success);" title="Rapproché"></i>
                            @else
                                <i class="far fa-circle" style="color:var(--gel-text-muted);" title="Non rapproché"></i>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px; color:var(--gel-text-muted);">
                            Aucune transaction récente.<br>
                            <a href="{{ route('gel-accountant.banque.transactions.index') }}" style="color:var(--gel-primary); font-size:13px; font-weight:600;">Aller au registre pour ajouter</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL EDITION COMPTE --}}
<div id="editAccountModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="gel-card" style="width:100%; max-width:600px; padding:0; animation: fadeIn 0.2s ease-out;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; margin:0;"><i class="fas fa-edit"></i> Modifier le Compte</h3>
            <button onclick="document.getElementById('editAccountModal').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:var(--gel-text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('gel-accountant.banque.comptes.update', $compte->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="padding:20px;">
                <div class="doc-form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="doc-form-group">
                        <label class="doc-label">Type de compte *</label>
                        <select name="type" class="doc-input" required>
                            <option value="banque" {{ $compte->type === 'banque' ? 'selected' : '' }}>Banque</option>
                            <option value="caisse" {{ $compte->type === 'caisse' ? 'selected' : '' }}>Caisse</option>
                            <option value="mobile_money" {{ $compte->type === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        </select>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Devise *</label>
                        <select name="currency" class="doc-input" required>
                            <option value="FCFA" {{ $compte->currency === 'FCFA' ? 'selected' : '' }}>FCFA</option>
                            <option value="EUR" {{ $compte->currency === 'EUR' ? 'selected' : '' }}>Euro (€)</option>
                            <option value="USD" {{ $compte->currency === 'USD' ? 'selected' : '' }}>Dollar ($)</option>
                        </select>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Nom interne du compte *</label>
                        <input type="text" name="name" class="doc-input" value="{{ $compte->name }}" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Établissement (Banque/Opérateur) *</label>
                        <input type="text" name="bank_name" class="doc-input" value="{{ $compte->bank_name }}" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Numéro de compte / RIB / N° Tel *</label>
                        <input type="text" name="account_number" class="doc-input" value="{{ $compte->account_number }}" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">IBAN</label>
                        <input type="text" name="iban" class="doc-input" value="{{ $compte->iban }}">
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">SWIFT/BIC</label>
                        <input type="text" name="swift" class="doc-input" value="{{ $compte->swift }}">
                    </div>
                    
                    <div class="doc-form-group">
                        <label class="doc-label">Compte comptable associé</label>
                        @php
                            $comptesComptables = \App\Models\AccountingAccount::where('client_id', $compte->client_id)->where('account_number', 'LIKE', '5%')->get();
                        @endphp
                        <select name="accounting_account_id" class="doc-input">
                            <option value="">— Aucun —</option>
                            @foreach($comptesComptables as $cc)
                                <option value="{{ $cc->id }}" {{ $compte->accounting_account_id == $cc->id ? 'selected' : '' }}>{{ $cc->account_number }} - {{ $cc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div style="padding:16px 20px; border-top:1px solid var(--gel-border); display:flex; justify-content:flex-end; gap:8px; background:#f9fafb; border-radius:0 0 8px 8px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="document.getElementById('editAccountModal').style.display='none'">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-primary">Enregistrer les modifications</button>
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
