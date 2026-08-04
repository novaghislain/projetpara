@extends('layouts.gel-accountant')

@section('title', 'Transactions Bancaires')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-list-ul" style="color:var(--gel-primary); margin-right:8px;"></i> Transactions Bancaires
        </h1>
        <p class="gel-page-subtitle">Consultez, importez et catégorisez les mouvements de vos comptes.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="gel-btn gel-btn-secondary" onclick="document.getElementById('importModal').style.display='flex'">
            <i class="fas fa-file-import"></i> Importer un relevé
        </button>
        <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newTransactionModal').style.display='flex'">
            <i class="fas fa-plus"></i> Nouvelle Transaction
        </button>
    </div>
</div>

<div class="gel-card p-4">
    <div style="display:flex; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:16px;">
        <form method="GET" action="{{ route('gel-accountant.banque.transactions.index') }}" style="display:flex; gap:12px; flex:1;">
            <div class="gel-form-group" style="margin:0; width:250px;">
                <select name="account_id" class="gel-form-control" onchange="this.form.submit()">
                    <option value="">Tous les comptes</option>
                    @foreach($comptes as $c)
                        <option value="{{ $c->id }}" {{ $selectedAccountId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ number_format($c->current_balance, 0, ',', ' ') }} {{ $c->currency }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="gel-form-group" style="margin:0; width:300px; display:flex;">
                <input type="text" name="search" class="gel-form-control" placeholder="Rechercher (description, référence)..." value="{{ request('search') }}" style="border-radius: 6px 0 0 6px;">
                <button type="submit" class="gel-btn gel-btn-secondary" style="border-radius: 0 6px 6px 0; border-left:none;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <table class="doc-lines-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Compte</th>
                <th>Description</th>
                <th style="text-align:right;">Débit</th>
                <th style="text-align:right;">Crédit</th>
                <th style="text-align:center;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $tx->bankAccount->name ?? 'N/A' }}</strong><br>
                    <small style="color:var(--gel-text-muted);">{{ $tx->bankAccount->account_number ?? '' }}</small>
                </td>
                <td>
                    {{ $tx->description }}
                    @if($tx->reference)
                        <br><span style="font-size:11px; color:var(--gel-text-secondary);"><i class="fas fa-tag"></i> {{ $tx->reference }}</span>
                    @endif
                </td>
                <td style="text-align:right;">
                    @if($tx->debit > 0)
                        <span style="color:var(--gel-danger); font-weight:600;">{{ number_format($tx->debit, 0, ',', ' ') }}</span>
                    @else
                        -
                    @endif
                </td>
                <td style="text-align:right;">
                    @if($tx->credit > 0)
                        <span style="color:var(--gel-success); font-weight:600;">{{ number_format($tx->credit, 0, ',', ' ') }}</span>
                    @else
                        -
                    @endif
                </td>
                <td style="text-align:center;">
                    @if($tx->is_reconciled)
                        <span class="gel-badge" style="background:#dcfce7; color:#166534;"><i class="fas fa-check-double"></i> Rapproché</span>
                    @else
                        @if($tx->is_imported)
                            <span class="gel-badge" style="background:#e0f2fe; color:#0284c7;"><i class="fas fa-file-import"></i> Importé</span>
                        @else
                            <span class="gel-badge" style="background:#f1f5f9; color:#475569;"><i class="fas fa-keyboard"></i> Saisi</span>
                        @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:40px;">
                    <div class="gel-empty" style="padding:0;">
                        <i class="fas fa-search-dollar"></i>
                        <h3>Aucune transaction trouvée</h3>
                        <p>Sélectionnez un autre compte ou ajustez vos critères de recherche.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $transactions->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- MODAL NOUVELLE TRANSACTION --}}
<div id="newTransactionModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="gel-card" style="width:100%; max-width:600px; padding:0; animation: fadeIn 0.2s ease-out;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; margin:0;"><i class="fas fa-plus"></i> Nouvelle Transaction</h3>
            <button onclick="document.getElementById('newTransactionModal').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:var(--gel-text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('gel-accountant.banque.transactions.store') }}" method="POST">
            @csrf
            <div style="padding:20px;">
                <div class="doc-form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="doc-form-group" style="grid-column: 1 / -1;">
                        <label class="doc-label">Compte Bancaire *</label>
                        <select name="bank_account_id" class="doc-input" required>
                            @foreach($comptes as $c)
                                <option value="{{ $c->id }}" {{ $selectedAccountId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ number_format($c->current_balance, 0, ',', ' ') }} {{ $c->currency }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Date *</label>
                        <input type="date" name="transaction_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Sens *</label>
                        <select name="type" class="doc-input" required>
                            <option value="debit">Débit (Sortie d'argent / Dépense)</option>
                            <option value="credit">Crédit (Entrée d'argent / Recette)</option>
                        </select>
                    </div>
                    <div class="doc-form-group" style="grid-column: 1 / -1;">
                        <label class="doc-label">Libellé / Description *</label>
                        <input type="text" name="description" class="doc-input" placeholder="Ex: Paiement fournisseur XYZ" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Montant *</label>
                        <input type="number" name="amount" class="doc-input" min="0.01" step="0.01" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Référence (Chèque, Virement...)</label>
                        <input type="text" name="reference" class="doc-input">
                    </div>
                </div>
            </div>
            <div style="padding:16px 20px; border-top:1px solid var(--gel-border); display:flex; justify-content:flex-end; gap:8px; background:#f9fafb; border-radius:0 0 8px 8px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="document.getElementById('newTransactionModal').style.display='none'">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL IMPORT CSV --}}
<div id="importModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="gel-card" style="width:100%; max-width:500px; padding:0; animation: fadeIn 0.2s ease-out;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; margin:0;"><i class="fas fa-file-import"></i> Importer un relevé (CSV)</h3>
            <button onclick="document.getElementById('importModal').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:var(--gel-text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('gel-accountant.banque.transactions.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="padding:20px;">
                <div class="doc-form-group">
                    <label class="doc-label">Compte de destination *</label>
                    <select name="bank_account_id" class="doc-input" required>
                        @foreach($comptes as $c)
                            <option value="{{ $c->id }}" {{ $selectedAccountId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="doc-form-group" style="margin-top:20px;">
                    <label class="doc-label">Fichier CSV *</label>
                    <input type="file" name="file" class="doc-input" accept=".csv, .txt" required style="padding:8px;">
                </div>
                
                <div style="background:#f8fafc; border-left:3px solid var(--gel-primary); padding:12px; margin-top:20px; font-size:12px; color:var(--gel-text-secondary);">
                    <strong>Format attendu (avec séparateur ; ou ,) :</strong><br>
                    Colonne 1 : Date (AAAA-MM-JJ ou JJ/MM/AAAA)<br>
                    Colonne 2 : Description<br>
                    Colonne 3 : Débit (montant sortant)<br>
                    Colonne 4 : Crédit (montant entrant)
                </div>
            </div>
            <div style="padding:16px 20px; border-top:1px solid var(--gel-border); display:flex; justify-content:flex-end; gap:8px; background:#f9fafb; border-radius:0 0 8px 8px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="document.getElementById('importModal').style.display='none'">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-upload"></i> Lancer l'import</button>
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
