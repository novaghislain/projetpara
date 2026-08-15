@extends('layouts.gel-accountant')

@section('title', 'Payer les factures')

@push('styles')
<style>
/* ==========================================================================
   PAY BILLS - DESIGN
   ========================================================================== */
.form-section {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; padding: 24px; margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0;
    border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s;
    background: #F8FAFC;
}
.form-control:focus, .form-select:focus { background: white; border-color: var(--gel-primary); }

.bills-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
.bills-table th { background: #F1F5F9; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; padding: 12px; text-align: left; border-bottom: 2px solid #E2E8F0; }
.bills-table td { padding: 12px; border-bottom: 1px solid #E2E8F0; vertical-align: middle; }
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
        <h1 class="gel-page-title">Payer les factures</h1>
        <div class="gel-page-subtitle">Sélectionnez les factures fournisseurs à payer en lot.</div>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    {{ $errors->first() }}
</div>
@endif

<form action="{{ route('gel-accountant.pay-bills.store') }}" method="POST">
    @csrf

    <div class="form-section">
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Compte de paiement <span class="text-danger">*</span></label>
                <select name="payment_account" class="form-select" required>
                    <option value="521000">Banque Principale</option>
                    <option value="571000">Caisse</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date du paiement <span class="text-danger">*</span></label>
                <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
            </div>
        </div>
    </div>

    <div class="form-section">
        <table class="bills-table">
            <thead>
                <tr>
                    <th>Fournisseur</th>
                    <th>N° Facture</th>
                    <th>Échéance</th>
                    <th class="text-end">Montant Facture</th>
                    <th class="text-end">Reste à payer</th>
                    <th class="text-end">Paiement</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                <tr>
                    <td>{{ $bill->partner->company_name ?? ($bill->partner->first_name . ' ' . $bill->partner->last_name) }}</td>
                    <td style="font-weight:600;">{{ $bill->invoice_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($bill->due_date)->format('d/m/Y') }}</td>
                    <td class="text-end font-monospace">{{ number_format($bill->total, 0, ',', ' ') }} F</td>
                    <td class="text-end font-monospace" style="color:#EF4444;">{{ number_format($bill->balance_due, 0, ',', ' ') }} F</td>
                    <td class="text-end">
                        <input type="number" name="payments[{{ $bill->id }}]" class="form-control apply-input bill-pay" value="0" min="0" max="{{ $bill->balance_due }}" step="0.01" oninput="calcTotalBills()">
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding:40px; color:#94A3B8;">Aucune facture fournisseur à payer.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals-area">
            <div class="totals-box">
                <div class="total-line grand">
                    <span>Total à payer</span>
                    <span id="displayTotalBills" class="font-monospace">0 F</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('gel-accountant.dashboard') }}" class="btn-cancel">Annuler</a>
        <button type="submit" class="btn-submit"><i class="fas fa-money-bill-wave"></i> Enregistrer les paiements</button>
    </div>
</form>

@push('scripts')
<script>
    function calcTotalBills() {
        let total = 0;
        document.querySelectorAll('.bill-pay').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('displayTotalBills').innerText = new Intl.NumberFormat('fr-FR').format(total) + ' F';
    }
</script>
@endpush
@endsection
