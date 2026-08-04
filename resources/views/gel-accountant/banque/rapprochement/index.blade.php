@extends('layouts.gel-accountant')

@section('title', 'Rapprochement Bancaire')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-handshake" style="color:var(--gel-primary); margin-right:8px;"></i> Rapprochement Bancaire
        </h1>
        <p class="gel-page-subtitle">Pointez vos transactions pour aligner votre comptabilité avec vos relevés bancaires.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="gel-btn gel-btn-primary" onclick="document.getElementById('newReconciliationModal').style.display='flex'">
            <i class="fas fa-play"></i> Démarrer un rapprochement
        </button>
    </div>
</div>

<div class="gel-card p-4">
    <table class="doc-lines-table">
        <thead>
            <tr>
                <th>Compte</th>
                <th>Date du relevé</th>
                <th style="text-align:right;">Solde du relevé</th>
                <th style="text-align:center;">Statut</th>
                <th style="text-align:right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rapprochements as $rap)
            <tr>
                <td>
                    <strong>{{ $rap->bankAccount->name ?? 'Compte inconnu' }}</strong>
                </td>
                <td>{{ \Carbon\Carbon::parse($rap->statement_date)->format('d/m/Y') }}</td>
                <td style="text-align:right; font-weight:600;">
                    {{ number_format($rap->statement_balance, 0, ',', ' ') }} {{ $rap->bankAccount->currency ?? 'FCFA' }}
                </td>
                <td style="text-align:center;">
                    @if($rap->status === 'completed')
                        <span class="gel-badge" style="background:#dcfce7; color:#166534;"><i class="fas fa-check-circle"></i> Terminé</span>
                    @elseif($rap->status === 'in_progress')
                        <span class="gel-badge" style="background:#fef9c3; color:#854d0e;"><i class="fas fa-spinner fa-spin"></i> En cours</span>
                    @else
                        <span class="gel-badge" style="background:#f1f5f9; color:#475569;">Brouillon</span>
                    @endif
                </td>
                <td style="text-align:right;">
                    @if($rap->status !== 'completed')
                        <a href="{{ route('gel-accountant.banque.rapprochement.show', $rap->id) }}" class="gel-btn gel-btn-primary" style="padding: 4px 10px; font-size:12px;">Reprendre</a>
                    @else
                        <a href="{{ route('gel-accountant.banque.rapprochement.show', $rap->id) }}" class="gel-btn gel-btn-secondary" style="padding: 4px 10px; font-size:12px;">Consulter</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:40px;">
                    <div class="gel-empty" style="padding:0;">
                        <i class="fas fa-balance-scale"></i>
                        <h3>Aucun rapprochement</h3>
                        <p>Il n'y a pas encore d'historique de rapprochement bancaire.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $rapprochements->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- MODAL NOUVEAU RAPPROCHEMENT --}}
<div id="newReconciliationModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="gel-card" style="width:100%; max-width:500px; padding:0; animation: fadeIn 0.2s ease-out;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; margin:0;"><i class="fas fa-play"></i> Démarrer un rapprochement</h3>
            <button onclick="document.getElementById('newReconciliationModal').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:var(--gel-text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('gel-accountant.banque.rapprochement.store') }}" method="POST">
            @csrf
            <div style="padding:20px;">
                <div class="doc-form-group">
                    <label class="doc-label">Compte Bancaire *</label>
                    <select name="bank_account_id" class="doc-input" required id="selectBankAccount" onchange="updateLastBalance()">
                        <option value="">Sélectionnez un compte</option>
                        @foreach($comptes as $c)
                            <option value="{{ $c->id }}" data-last-date="{{ $c->last_reconciliation_date ?? '' }}" data-last-balance="{{ $c->reconciled_balance }}">
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div id="lastReconInfo" style="display:none; background:#f8fafc; padding:10px 12px; border-radius:6px; margin-bottom:14px; font-size:12px; color:var(--gel-text-secondary);">
                    <strong>Dernier rapprochement :</strong> <span id="lastReconDate">--</span><br>
                    <strong>Solde d'ouverture :</strong> <span id="lastReconBalance">0</span>
                </div>

                <div class="doc-form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="doc-form-group">
                        <label class="doc-label">Date du relevé *</label>
                        <input type="date" name="statement_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="doc-form-group">
                        <label class="doc-label">Solde final du relevé *</label>
                        <input type="number" name="statement_balance" class="doc-input" step="0.01" required placeholder="0.00">
                    </div>
                </div>
            </div>
            <div style="padding:16px 20px; border-top:1px solid var(--gel-border); display:flex; justify-content:flex-end; gap:8px; background:#f9fafb; border-radius:0 0 8px 8px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="document.getElementById('newReconciliationModal').style.display='none'">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-primary">Démarrer</button>
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

@push('scripts')
<script>
function updateLastBalance() {
    const select = document.getElementById('selectBankAccount');
    const option = select.options[select.selectedIndex];
    const infoBox = document.getElementById('lastReconInfo');
    
    if (select.value === "") {
        infoBox.style.display = 'none';
        return;
    }
    
    const lastDate = option.getAttribute('data-last-date');
    const lastBalance = option.getAttribute('data-last-balance');
    
    document.getElementById('lastReconDate').textContent = lastDate ? new Date(lastDate).toLocaleDateString() : 'Aucun';
    document.getElementById('lastReconBalance').textContent = parseFloat(lastBalance).toLocaleString() + ' ' + (lastDate ? '' : '(Solde initial)');
    
    infoBox.style.display = 'block';
}
</script>
@endpush
