@extends('layouts.gel-accountant')

@section('title', 'Recevoir un Paiement')

@push('styles')
<style>
/* ==========================================================================
   CREATE PAYMENT - DESIGN
   ========================================================================== */
.form-section {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; padding: 24px; margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.section-title {
    font-size: 15px; font-weight: 700; color: #1E293B; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;
}
.section-title i { color: var(--gel-primary); }

.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0;
    border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s;
    background: #F8FAFC;
}
.form-control:focus, .form-select:focus { background: white; border-color: var(--gel-primary); }

.amount-input { font-size: 24px; font-weight: 700; color: #10B981; text-align: right; font-family: monospace; }

.invoices-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
.invoices-table th { background: #F1F5F9; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; padding: 12px; text-align: left; border-bottom: 2px solid #E2E8F0; }
.invoices-table td { padding: 12px; border-bottom: 1px solid #E2E8F0; vertical-align: middle; }
.apply-input { width: 120px; text-align: right; font-family: monospace; }

.totals-area { display: flex; justify-content: flex-end; margin-top: 24px; }
.totals-box { width: 300px; background: #F8FAFC; padding: 20px; border-radius: 8px; border: 1px solid #E2E8F0; }
.total-line { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #475569; }
.total-line.grand { border-top: 2px solid #CBD5E1; padding-top: 12px; margin-top: 4px; font-size: 16px; font-weight: 700; color: #1E293B; }

.form-actions { display: flex; justify-content: flex-end; gap: 16px; margin-top: 32px; }
.btn-cancel { background: white; color: #475569; border: 1px solid #E2E8F0; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; }
.btn-submit { background: var(--gel-primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-submit:hover { background: var(--gel-primary-hover); }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <a href="{{ route('gel-accountant.payments.index') }}" style="font-size:13px; color:#64748B; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux paiements</a>
        <h1 class="gel-page-title">Recevoir un Paiement</h1>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    {{ $errors->first() }}
</div>
@endif

<form action="{{ route('gel-accountant.payments.store') }}" method="POST" id="paymentForm">
    @csrf

    <div class="form-section">
        <div class="section-title"><i class="fas fa-info-circle"></i> Détails du paiement</div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Client <span class="text-danger">*</span></label>
                <select name="partner_id" id="partner_id" class="form-select" required onchange="fetchUnpaidInvoices(this.value)">
                    <option value="">Sélectionnez un client...</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ (old('partner_id') == $partner->id || ($selectedInvoice && $selectedInvoice->partner_id == $partner->id)) ? 'selected' : '' }}>
                            {{ $partner->company_name ?? ($partner->first_name . ' ' . $partner->last_name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date du paiement <span class="text-danger">*</span></label>
                <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Montant Reçu (F CFA) <span class="text-danger">*</span></label>
                <input type="number" name="amount_received" id="amount_received" class="form-control amount-input" value="{{ old('amount_received') }}" min="0.01" step="0.01" required oninput="distributeAmount()">
            </div>
            <div class="form-group">
                <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select" required>
                    <option value="virement">Virement bancaire</option>
                    <option value="especes">Espèces</option>
                    <option value="cheque">Chèque</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="carte">Carte bancaire</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">N° de Référence (Chèque/Virement)</label>
                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title"><i class="fas fa-file-invoice"></i> Factures Imputables</div>
        
        <table class="invoices-table" id="invoicesTable">
            <thead>
                <tr>
                    <th>Sélectionner</th>
                    <th>N° Facture</th>
                    <th>Date d'échéance</th>
                    <th class="text-end">Montant Total</th>
                    <th class="text-end">Reste à payer</th>
                    <th class="text-end">Paiement Appliqué</th>
                </tr>
            </thead>
            <tbody id="invoicesContainer">
                @if(isset($unpaidInvoices) && $unpaidInvoices->count() > 0)
                    @foreach($unpaidInvoices as $index => $inv)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="invoice-cb" data-index="{{ $index }}" data-balance="{{ $inv->balance_due }}" onchange="handleCbChange(this, {{ $index }})" {{ ($selectedInvoice && $selectedInvoice->id == $inv->id) ? 'checked' : '' }}>
                                <input type="hidden" name="invoices[{{ $index }}][id]" value="{{ $inv->id }}">
                            </td>
                            <td style="font-weight:600; color:var(--gel-primary);">{{ $inv->invoice_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}</td>
                            <td class="text-end font-monospace">{{ number_format($inv->total, 0, ',', ' ') }} F</td>
                            <td class="text-end font-monospace balance-val" style="color:#EF4444;">{{ $inv->balance_due }}</td>
                            <td class="text-end">
                                <input type="number" name="invoices[{{ $index }}][amount_applied]" id="apply_{{ $index }}" class="form-control apply-input" value="{{ ($selectedInvoice && $selectedInvoice->id == $inv->id) ? $inv->balance_due : 0 }}" min="0" max="{{ $inv->balance_due }}" step="0.01" oninput="calcTotals()">
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center" style="padding:40px; color:#94A3B8;">Sélectionnez un client pour voir ses factures impayées.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="totals-area">
            <div class="totals-box">
                <div class="total-line">
                    <span>Montant Reçu</span>
                    <span id="displayReceived" class="font-monospace">0 F</span>
                </div>
                <div class="total-line">
                    <span>Total Appliqué</span>
                    <span id="displayApplied" class="font-monospace">0 F</span>
                </div>
                <div class="total-line grand">
                    <span>Reste à Appliquer</span>
                    <span id="displayDiff" class="font-monospace">0 F</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('gel-accountant.payments.index') }}" class="btn-cancel">Annuler</a>
        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer le paiement</button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($selectedInvoice)
            document.getElementById('amount_received').value = {{ $selectedInvoice->balance_due }};
            calcTotals();
        @endif
    });

    function handleCbChange(cb, index) {
        const applyInput = document.getElementById('apply_' + index);
        if (cb.checked) {
            applyInput.value = cb.dataset.balance;
        } else {
            applyInput.value = 0;
        }
        calcTotals();
    }

    function calcTotals() {
        let received = parseFloat(document.getElementById('amount_received').value) || 0;
        let applied = 0;

        document.querySelectorAll('.apply-input').forEach(input => {
            applied += parseFloat(input.value) || 0;
        });

        document.getElementById('displayReceived').innerText = new Intl.NumberFormat('fr-FR').format(received) + ' F';
        document.getElementById('displayApplied').innerText = new Intl.NumberFormat('fr-FR').format(applied) + ' F';
        
        let diff = received - applied;
        let diffEl = document.getElementById('displayDiff');
        diffEl.innerText = new Intl.NumberFormat('fr-FR').format(diff) + ' F';
        
        if (diff < 0) {
            diffEl.style.color = '#EF4444'; // Error: applied more than received
        } else {
            diffEl.style.color = '#1E293B';
        }
    }

    function distributeAmount() {
        // Optionnel : auto-répartition du montant reçu sur les factures les plus anciennes
        calcTotals();
    }

    function fetchUnpaidInvoices(partnerId) {
        if (!partnerId) {
            document.getElementById('invoicesContainer').innerHTML = '<tr><td colspan="6" class="text-center" style="padding:40px; color:#94A3B8;">Sélectionnez un client pour voir ses factures impayées.</td></tr>';
            return;
        }

        fetch('/gel-accountant/payments/api/unpaid-invoices/' + partnerId)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center" style="padding:40px; color:#10B981;">Aucune facture impayée pour ce client.</td></tr>';
                } else {
                    data.forEach((inv, index) => {
                        let date = new Date(inv.due_date).toLocaleDateString('fr-FR');
                        html += `
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="invoice-cb" data-index="${index}" data-balance="${inv.balance_due}" onchange="handleCbChange(this, ${index})">
                                    <input type="hidden" name="invoices[${index}][id]" value="${inv.id}">
                                </td>
                                <td style="font-weight:600; color:var(--gel-primary);">${inv.invoice_number}</td>
                                <td>${date}</td>
                                <td class="text-end font-monospace">${new Intl.NumberFormat('fr-FR').format(inv.total)} F</td>
                                <td class="text-end font-monospace balance-val" style="color:#EF4444;">${inv.balance_due}</td>
                                <td class="text-end">
                                    <input type="number" name="invoices[${index}][amount_applied]" id="apply_${index}" class="form-control apply-input" value="0" min="0" max="${inv.balance_due}" step="0.01" oninput="calcTotals()">
                                </td>
                            </tr>
                        `;
                    });
                }
                document.getElementById('invoicesContainer').innerHTML = html;
                calcTotals();
            });
    }
</script>
@endpush
@endsection
