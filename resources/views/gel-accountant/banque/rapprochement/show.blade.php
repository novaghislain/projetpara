@extends('layouts.gel-accountant')

@section('title', 'Pointage - Rapprochement Bancaire')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-check-double" style="color:var(--gel-primary); margin-right:8px;"></i> Pointage : {{ $compte->name }}
        </h1>
        <p class="gel-page-subtitle">Relevé du {{ \Carbon\Carbon::parse($reconciliation->statement_date)->format('d/m/Y') }}</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.banque.rapprochement.index') }}" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Quitter
        </a>
        <form action="{{ route('gel-accountant.banque.rapprochement.automatch', $reconciliation->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="gel-btn gel-btn-secondary" style="border-color:var(--gel-primary); color:var(--gel-primary);">
                <i class="fas fa-magic"></i> Auto-Match IA
            </button>
        </form>
        <button form="reconciliationForm" name="action" value="save" class="gel-btn gel-btn-secondary">
            <i class="fas fa-save"></i> Enregistrer
        </button>
        <button form="reconciliationForm" name="action" value="finish" class="gel-btn gel-btn-primary" id="btnFinishRecon" {{ abs($reconciliation->difference) > 0.01 ? 'disabled' : '' }}>
            <i class="fas fa-flag-checkered"></i> Terminer
        </button>
    </div>
</div>

<div class="row">
    {{-- BARRE SUPÉRIEURE DE RÉSUMÉ --}}
    <div class="col-12 mb-4">
        <div class="gel-card" style="display:flex; padding:20px; border-top: 4px solid var(--gel-primary);">
            <div style="flex:1; border-right:1px solid var(--gel-border); padding-right:20px;">
                <div style="font-size:12px; color:var(--gel-text-secondary); text-transform:uppercase;">Solde final du relevé</div>
                <div style="font-size:24px; font-weight:700; color:var(--gel-text-primary);">
                    {{ number_format($reconciliation->statement_balance, 0, ',', ' ') }} <small style="font-size:14px;">{{ $compte->currency }}</small>
                </div>
            </div>
            <div style="flex:1; border-right:1px solid var(--gel-border); padding:0 20px;">
                <div style="font-size:12px; color:var(--gel-text-secondary); text-transform:uppercase;">Solde d'ouverture pointé</div>
                <div style="font-size:20px; font-weight:600; color:var(--gel-text-primary);">
                    {{ number_format($compte->reconciled_balance, 0, ',', ' ') }} <small style="font-size:14px;">{{ $compte->currency }}</small>
                </div>
            </div>
            <div style="flex:1; border-right:1px solid var(--gel-border); padding:0 20px;">
                <div style="font-size:12px; color:var(--gel-text-secondary); text-transform:uppercase;">Transactions pointées</div>
                <div style="font-size:20px; font-weight:600; color:var(--gel-success);" id="pointedAmount">
                    0 <small style="font-size:14px;">{{ $compte->currency }}</small>
                </div>
            </div>
            <div style="flex:1; padding-left:20px; display:flex; flex-direction:column; justify-content:center;">
                <div style="font-size:12px; color:var(--gel-text-secondary); text-transform:uppercase;">Différence</div>
                <div style="font-size:28px; font-weight:700; color: {{ abs($reconciliation->difference) > 0.01 ? 'var(--gel-danger)' : 'var(--gel-success)' }};" id="differenceAmount">
                    {{ number_format($reconciliation->difference, 0, ',', ' ') }} <small style="font-size:14px;">{{ $compte->currency }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- LISTE DES TRANSACTIONS --}}
    <div class="col-12">
        <div class="gel-card p-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px;">Transactions à pointer</h3>
            
            <form id="reconciliationForm" action="{{ route('gel-accountant.banque.rapprochement.process', $reconciliation->id) }}" method="POST">
                @csrf
                <table class="doc-lines-table">
                    <thead>
                        <tr>
                            <th style="width:40px; text-align:center;">
                                <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                            </th>
                            <th>Date</th>
                            <th>Description</th>
                            <th style="text-align:right;">Débit</th>
                            <th style="text-align:right;">Crédit</th>
                            <th style="text-align:center;">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" name="transactions[]" value="{{ $tx->id }}" class="tx-checkbox" 
                                    data-amount="{{ $tx->credit - $tx->debit }}"
                                    @if(session('matched_ids') && in_array($tx->id, session('matched_ids'))) checked @endif
                                    onchange="calculatePointage()">
                            </td>
                            <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                            <td>
                                {{ $tx->description }}
                                @if($tx->reference) <br><small style="color:var(--gel-text-muted);">{{ $tx->reference }}</small> @endif
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
                                @if($tx->is_imported)
                                    <span class="gel-badge" style="background:#e0f2fe; color:#0284c7;"><i class="fas fa-file-import"></i></span>
                                @else
                                    <span class="gel-badge" style="background:#f1f5f9; color:#475569;"><i class="fas fa-keyboard"></i></span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color:var(--gel-text-muted);">
                                <i class="fas fa-check-circle" style="font-size:32px; color:var(--gel-success); margin-bottom:12px; display:block;"></i>
                                Aucune transaction en attente de pointage jusqu'au {{ \Carbon\Carbon::parse($reconciliation->statement_date)->format('d/m/Y') }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const statementBalance = {{ $reconciliation->statement_balance }};
    const openingBalance = {{ $compte->reconciled_balance }};
    
    // Si on vient d'un auto-match, on recalcule le pointage au chargement
    document.addEventListener("DOMContentLoaded", function() {
        calculatePointage();
    });
    
    function calculatePointage() {
        const checkboxes = document.querySelectorAll('.tx-checkbox');
        let pointedSum = 0;
        
        checkboxes.forEach(cb => {
            if (cb.checked) {
                pointedSum += parseFloat(cb.getAttribute('data-amount'));
            }
        });
        
        const clearedBalance = openingBalance + pointedSum;
        const difference = statementBalance - clearedBalance;
        
        // Update UI
        document.getElementById('pointedAmount').innerHTML = formatMoney(pointedSum) + ' <small style="font-size:14px;">{{ $compte->currency }}</small>';
        
        const diffEl = document.getElementById('differenceAmount');
        diffEl.innerHTML = formatMoney(difference) + ' <small style="font-size:14px;">{{ $compte->currency }}</small>';
        
        const btnFinish = document.getElementById('btnFinishRecon');
        if (Math.abs(difference) > 0.01) {
            diffEl.style.color = 'var(--gel-danger)';
            btnFinish.disabled = true;
        } else {
            diffEl.style.color = 'var(--gel-success)';
            btnFinish.disabled = false;
        }
    }
    
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.tx-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = source.checked;
        });
        calculatePointage();
    }
    
    function formatMoney(amount) {
        // Un format basique
        return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    }
</script>
@endpush
