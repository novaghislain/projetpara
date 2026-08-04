@extends('layouts.gel-accountant')

@section('title', 'Payer les factures fournisseurs')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-credit-card" style="color:var(--gel-primary); margin-right:8px;"></i> Payer les factures fournisseurs</h1>
        <p class="gel-page-subtitle">Sélectionnez les factures en attente pour les régler groupées ou individuellement.</p>
    </div>
</div>

<form method="POST" action="{{ route('gel-accountant.pay-bills.store') }}" id="payBillsForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid" style="align-items:end;">
        <div class="doc-form-group">
            <label class="doc-label">Compte de paiement *</label>
            <select name="payment_account" class="doc-input" required>
                @foreach(\App\Models\BankAccount::whereIn('type', ['banque','caisse'])->get() as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date de paiement *</label>
            <input type="date" name="payment_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Filtrer par fournisseur</label>
            <select class="doc-input">
                <option value="">Tous les fournisseurs</option>
                @foreach(\App\Models\Partner::where('type', 'fournisseur')->get() as $f)
                    <option value="{{ $f->id }}">{{ $f->company_name ?: $f->first_name.' '.$f->last_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table">
        <thead>
            <tr>
                <th style="width:5%;"><input type="checkbox" id="selectAll"></th>
                <th style="width:15%;">Date d'échéance</th>
                <th style="width:20%;">Fournisseur</th>
                <th style="width:15%;">N° Facture</th>
                <th style="width:15%; text-align:right;">Montant Net</th>
                <th style="width:15%; text-align:right;">Solde Dû</th>
                <th style="width:15%; text-align:right;">Montant À payer</th>
            </tr>
        </thead>
        <tbody id="billsBody">
            @forelse($bills as $bill)
            <tr class="doc-line-row">
                <td><input type="checkbox" class="bill-check" onchange="updateRow(this)"></td>
                <td>
                    {{ \Carbon\Carbon::parse($bill->due_date)->format('d/m/Y') }}
                    @if($bill->due_date < now())
                        <br><span style="color:var(--gel-danger); font-size:11px; font-weight:700;">(En retard)</span>
                    @endif
                </td>
                <td>{{ $bill->partner->company_name ?: ($bill->partner->first_name.' '.$bill->partner->last_name) }}</td>
                <td>{{ $bill->invoice_number }}</td>
                <td style="text-align:right;">{{ number_format($bill->total, 0, ',', ' ') }}</td>
                <td style="text-align:right; font-weight:600;">{{ number_format($bill->balance_due, 0, ',', ' ') }}</td>
                <td style="text-align:right;">
                    <input type="number" name="payments[{{ $bill->id }}]" class="doc-input-sm line-pay" value="0" min="0" max="{{ $bill->balance_due }}" disabled oninput="recalc()">
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:40px; color:var(--gel-text-muted);">
                    <i class="fas fa-check-circle" style="font-size:32px; color:var(--gel-success); margin-bottom:12px; display:block;"></i>
                    Aucune facture fournisseur en attente de paiement.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="display:flex; justify-content:flex-end; align-items:center; gap:20px; padding-bottom:30px;">
    <div style="font-size:16px;">Total À payer : <strong id="totalToPay" style="color:var(--gel-primary); font-size:20px;">0 FCFA</strong></div>
    <button type="submit" class="gel-btn gel-btn-primary" id="btnSubmit" disabled style="color:white; font-weight:700;"><i class="fas fa-check"></i> Enregistrer les paiements</button>
</div>
</form>


<script>
document.getElementById('selectAll').addEventListener('change', function() {
    const checks = document.querySelectorAll('.bill-check');
    checks.forEach(c => { c.checked = this.checked; updateRow(c); });
});

function updateRow(checkbox) {
    const row = checkbox.closest('tr');
    const input = row.querySelector('.line-pay');
    if(checkbox.checked) {
        row.classList.add('selected');
        input.disabled = false;
        if(input.value == 0) input.value = input.max;
    } else {
        row.classList.remove('selected');
        input.disabled = true;
        input.value = 0;
    }
    recalc();
}

function recalc() {
    let t = 0;
    document.querySelectorAll('.line-pay').forEach(i => {
        if(!i.disabled) t += parseFloat(i.value || 0);
    });
    document.getElementById('totalToPay').textContent = Math.round(t).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('btnSubmit').disabled = (t === 0);
}
</script>
@endsection

