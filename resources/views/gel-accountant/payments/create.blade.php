@extends('layouts.gel-accountant')

@section('title', 'Enregistrer un paiement')

@section('content')

{{-- â•â•â•â•â•â•â•â•â•â•â• EN-TÀŠTE â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-hand-holding-usd" style="color:var(--gel-success); margin-right:8px;"></i> Enregistrer un paiement</h1>
        <p class="gel-page-subtitle">Recevez un paiement et appliquez-le À  une ou plusieurs factures clients</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.factures.index') }}" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

@if($errors->any())
<div style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.3); border-radius:8px; padding:14px 18px; margin-bottom:20px;">
    <div style="font-weight:600; color:var(--gel-danger); margin-bottom:6px;"><i class="fas fa-exclamation-circle"></i> Erreurs de validation</div>
    <ul style="margin:0; padding-left:20px; font-size:13px; color:var(--gel-danger);">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('gel-accountant.payments.store') }}" id="paymentForm">
    @csrf

    <div class="gel-card p-4 mb-4">
        <div class="payment-grid-top">
            {{-- Client --}}
            <div class="gel-form-group">
                <label>Client *</label>
                <select name="partner_id" class="gel-form-select" required id="partnerSelect">
                    <option value="">— Sélectionner un client —</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ (old('partner_id') == $partner->id || (isset($selectedInvoice) && $selectedInvoice->partner_id == $partner->id)) ? 'selected' : '' }}>
                            {{ $partner->company_name ?? ($partner->last_name.' '.$partner->first_name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date de paiement --}}
            <div class="gel-form-group">
                <label>Date du paiement *</label>
                <input type="date" name="payment_date" class="gel-form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
            </div>

            {{-- Méthode de paiement --}}
            <div class="gel-form-group">
                <label>Mode de paiement *</label>
                <select name="payment_method" class="gel-form-select" required>
                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Virement bancaire</option>
                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Chèque</option>
                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Espèces</option>
                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Carte bancaire</option>
                </select>
            </div>

            {{-- Numéro de référence --}}
            <div class="gel-form-group">
                <label>NÂ° de référence</label>
                <input type="text" name="reference" class="gel-form-control" placeholder="Ex: CHQ-001234" value="{{ old('reference') }}">
            </div>
            
            {{-- Compte de Dépôt (Dummy pour le visuel) --}}
            <div class="gel-form-group">
                <label>Déposer sur</label>
                <select class="gel-form-select">
                    <option value="1">Compte principal - Ecobank</option>
                    <option value="2">Caisse (Espèces)</option>
                </select>
            </div>
            
            {{-- Montant reçu --}}
            <div class="gel-form-group">
                <label>Montant reçu (FCFA) *</label>
                <input type="number" name="amount_received" id="amountReceived" class="gel-form-control" placeholder="0" value="{{ old('amount_received') }}" required style="font-size:18px; font-weight:700; color:var(--gel-success);">
            </div>
        </div>
    </div>

    {{-- Factures impayées --}}
    <div class="gel-card p-4 mb-4">
        <h3 style="font-size:16px; margin-bottom:16px; font-weight:700; color:var(--gel-text-primary);">Transactions ouvertes (Factures impayées)</h3>
        
        <table class="gel-table" id="invoicesTable">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                    <th>Description</th>
                    <th>Date d'échéance</th>
                    <th style="text-align:right;">Montant original</th>
                    <th style="text-align:right;">Solde ouvert</th>
                    <th style="text-align:right; width: 150px;">Paiement</th>
                </tr>
            </thead>
            <tbody id="invoicesTbody">
                @if(isset($unpaidInvoices) && count($unpaidInvoices) > 0)
                    @foreach($unpaidInvoices as $index => $inv)
                        <tr class="invoice-row" data-id="{{ $inv->id }}" data-balance="{{ $inv->balance_due }}">
                            <td>
                                <input type="checkbox" class="invoice-check" {{ (isset($selectedInvoice) && $selectedInvoice->id == $inv->id) ? 'checked' : '' }}>
                                <input type="hidden" name="invoices[{{ $index }}][id]" value="{{ $inv->id }}" class="input-id" {{ (isset($selectedInvoice) && $selectedInvoice->id == $inv->id) ? '' : 'disabled' }}>
                            </td>
                            <td>
                                <strong>Facture #{{ $inv->invoice_number }}</strong>
                            </td>
                            <td style="{{ $inv->isOverdue() ? 'color:var(--gel-danger);font-weight:600;' : '' }}">
                                {{ $inv->due_date->format('d/m/Y') }}
                            </td>
                            <td style="text-align:right;">{{ number_format($inv->total, 0, ',', ' ') }}</td>
                            <td style="text-align:right;">{{ number_format($inv->balance_due, 0, ',', ' ') }}</td>
                            <td>
                                <input type="number" name="invoices[{{ $index }}][amount_applied]" class="gel-form-control input-amount" 
                                       style="text-align:right;" 
                                       value="{{ (isset($selectedInvoice) && $selectedInvoice->id == $inv->id) ? $inv->balance_due : '' }}" 
                                       {{ (isset($selectedInvoice) && $selectedInvoice->id == $inv->id) ? '' : 'disabled' }}
                                       max="{{ $inv->balance_due }}" min="0" step="1">
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr id="emptyRow">
                        <td colspan="6" style="text-align:center; padding:30px; color:var(--gel-text-muted);">
                            Veuillez sélectionner un client pour afficher ses factures ouvertes.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div style="display:flex; justify-content:flex-end; margin-top:20px;">
            <div style="width:300px;">
                <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--gel-border);">
                    <span style="font-weight:600;">Montant appliqué</span>
                    <span id="totalApplied" style="font-weight:700;">0 FCFA</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:8px 0; color:var(--gel-text-muted);">
                    <span>Montant À  Créditer</span>
                    <span id="amountToCredit">0 FCFA</span>
                </div>
            </div>
        </div>
        
        <div style="margin-top:24px;">
            <label style="font-size:13px; font-weight:600; display:block; margin-bottom:6px;">Mémo / Notes</label>
            <textarea name="notes" class="gel-form-control" rows="2" placeholder="Ajouter un mémo privé pour ce paiement...">{{ old('notes') }}</textarea>
        </div>

    </div>

    {{-- Actions --}}
    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
        <a href="{{ route('gel-accountant.factures.index') }}" class="gel-btn gel-btn-secondary">Annuler</a>
        <button type="submit" class="gel-btn gel-btn-success" style="padding:10px 24px; font-size:14px;">
            <i class="fas fa-save"></i> Enregistrer et fermer
        </button>
    </div>

</form>

@endsection

@push('styles')
<style>
    .payment-grid-top {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    
    @media (max-width: 900px) {
        .payment-grid-top { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .payment-grid-top { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const partnerSelect = document.getElementById('partnerSelect');
    const invoicesTbody = document.getElementById('invoicesTbody');
    const amountReceived = document.getElementById('amountReceived');
    const selectAll = document.getElementById('selectAll');
    
    // Initialiser les calculs si on arrive d'une facture spécifique
    if (document.querySelectorAll('.invoice-check:checked').length > 0) {
        if (!amountReceived.value || amountReceived.value === "0") {
            let sum = 0;
            document.querySelectorAll('.input-amount').forEach(el => {
                if(!el.disabled) sum += parseFloat(el.value || 0);
            });
            amountReceived.value = sum;
        }
        calculateTotals();
    }

    // Changement de client : appel AJAX pour récupérer ses factures
    if (partnerSelect) {
        partnerSelect.addEventListener('change', function() {
            const partnerId = this.value;
            if (!partnerId) {
                invoicesTbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--gel-text-muted);">Veuillez sélectionner un client pour afficher ses factures ouvertes.</td></tr>`;
                calculateTotals();
                return;
            }
            
            invoicesTbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px;"><i class="fas fa-spinner fa-spin"></i> Chargement...</td></tr>`;

            // Simuler l'appel API ou faire le vrai appel
            fetch(`/gel-accountant/payments/api/unpaid-invoices/${partnerId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        invoicesTbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--gel-text-muted);">Aucune facture impayée pour ce client.</td></tr>`;
                    } else {
                        invoicesTbody.innerHTML = '';
                        data.forEach((inv, index) => {
                            const date = new Date(inv.due_date).toLocaleDateString('fr-FR');
                            const html = `
                                <tr class="invoice-row" data-id="${inv.id}" data-balance="${inv.balance_due}">
                                    <td>
                                        <input type="checkbox" class="invoice-check">
                                        <input type="hidden" name="invoices[${index}][id]" value="${inv.id}" class="input-id" disabled>
                                    </td>
                                    <td><strong>Facture #${inv.invoice_number}</strong></td>
                                    <td>${date}</td>
                                    <td style="text-align:right;">${formatMoney(inv.total)}</td>
                                    <td style="text-align:right;">${formatMoney(inv.balance_due)}</td>
                                    <td>
                                        <input type="number" name="invoices[${index}][amount_applied]" class="gel-form-control input-amount" 
                                               style="text-align:right;" value="" disabled max="${inv.balance_due}" min="0" step="1">
                                    </td>
                                </tr>
                            `;
                            invoicesTbody.insertAdjacentHTML('beforeend', html);
                        });
                        attachRowEvents();
                    }
                    calculateTotals();
                })
                .catch(err => {
                    invoicesTbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:red;">Erreur de chargement.</td></tr>`;
                });
        });
    }

    // Attacher les événements aux nouvelles lignes du tableau
    function attachRowEvents() {
        document.querySelectorAll('.invoice-check').forEach(chk => {
            chk.addEventListener('change', function() {
                const tr = this.closest('tr');
                const idInput = tr.querySelector('.input-id');
                const amountInput = tr.querySelector('.input-amount');
                
                if (this.checked) {
                    idInput.disabled = false;
                    amountInput.disabled = false;
                    // Si on coche, on pré-remplit avec le solde s'il n'y a pas déjÀ  une valeur
                    if (!amountInput.value) {
                        amountInput.value = tr.dataset.balance;
                    }
                } else {
                    idInput.disabled = true;
                    amountInput.disabled = true;
                    amountInput.value = ''; // On vide quand on décoche
                }
                
                autoUpdateReceivedAmount();
                calculateTotals();
            });
        });

        document.querySelectorAll('.input-amount').forEach(inp => {
            inp.addEventListener('input', function() {
                autoUpdateReceivedAmount();
                calculateTotals();
            });
        });
    }
    
    // Appeler une fois pour les lignes déjÀ  rendues côté serveur
    attachRowEvents();

    // Select All
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.invoice-check').forEach(chk => {
                if (chk.checked !== isChecked) {
                    chk.checked = isChecked;
                    chk.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    // Si on change le montant reçu À  la main, recalculer le Crédit
    if (amountReceived) {
        amountReceived.addEventListener('input', calculateTotals);
    }

    // Met À  jour le champ "Montant reçu" automatiquement s'il est vide 
    // ou si on vient de cocher/décocher des factures
    function autoUpdateReceivedAmount() {
        let sum = 0;
        document.querySelectorAll('.input-amount').forEach(inp => {
            if (!inp.disabled) sum += parseFloat(inp.value || 0);
        });
        amountReceived.value = sum;
    }

    function calculateTotals() {
        let applied = 0;
        document.querySelectorAll('.input-amount').forEach(inp => {
            if (!inp.disabled) applied += parseFloat(inp.value || 0);
        });
        
        const received = parseFloat(amountReceived.value || 0);
        const credit = Math.max(0, received - applied);
        
        document.getElementById('totalApplied').textContent = formatMoney(applied) + ' FCFA';
        document.getElementById('amountToCredit').textContent = formatMoney(credit) + ' FCFA';
    }

    function formatMoney(amount) {
        return Math.round(amount).toLocaleString('fr-FR');
    }
});
</script>
@endpush

