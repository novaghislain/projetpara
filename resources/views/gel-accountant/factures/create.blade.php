@extends('layouts.gel-accountant')

@section('title', 'Nouvelle Facture')

@push('styles')
<style>
/* ==========================================================================
   CREATE FACTURE - BENTO GRID DESIGN
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
.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0;
    border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s;
    background: #F8FAFC;
}
.form-control:focus, .form-select:focus { background: white; border-color: var(--gel-primary); }

.lines-table { width: 100%; border-collapse: collapse; }
.lines-table th { background: #F1F5F9; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; padding: 12px; text-align: left; border-bottom: 2px solid #E2E8F0; }
.lines-table td { padding: 12px; border-bottom: 1px solid #E2E8F0; vertical-align: top; }
.line-input { width: 100%; padding: 8px 12px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 13px; outline: none; background: white; }
.line-input:focus { border-color: var(--gel-primary); }

.totals-area { display: flex; flex-direction: column; gap: 12px; width: 300px; margin-left: auto; margin-top: 24px; background: #F8FAFC; padding: 20px; border-radius: 8px; border: 1px solid #E2E8F0; }
.total-line { display: flex; justify-content: space-between; font-size: 14px; color: #475569; }
.total-line.grand-total { font-size: 18px; font-weight: 700; color: #1E293B; border-top: 2px solid #E2E8F0; padding-top: 12px; margin-top: 4px; }

.btn-add-line { background: #EFF6FF; color: #3B82F6; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; margin-top: 16px; }
.btn-add-line:hover { background: #DBEAFE; }

.form-actions { display: flex; justify-content: flex-end; gap: 16px; margin-top: 32px; }
.btn-cancel { background: white; color: #475569; border: 1px solid #E2E8F0; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; }
.btn-submit { background: var(--gel-primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-submit:hover { background: var(--gel-primary-hover); }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <a href="{{ route('gel-accountant.factures.index') }}" style="font-size:13px; color:#64748B; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux factures</a>
        <h1 class="gel-page-title">Nouvelle Facture</h1>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    {{ $errors->first() }}
</div>
@endif

<form action="{{ route('gel-accountant.factures.store') }}" method="POST">
    @csrf

    <div class="form-section">
        <div class="section-title"><i class="fas fa-user-tie"></i> Client et Dates</div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Client <span class="text-danger">*</span></label>
                <select name="partner_id" class="form-select" required>
                    <option value="">Sélectionnez un client...</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                            {{ $partner->company_name ?? ($partner->first_name . ' ' . $partner->last_name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date de facturation <span class="text-danger">*</span></label>
                <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title"><i class="fas fa-list"></i> Lignes de Facture</div>
        <table class="lines-table" id="linesTable">
            <thead>
                <tr>
                    <th style="width:40%;">Description <span class="text-danger">*</span></th>
                    <th style="width:15%;">Quantité <span class="text-danger">*</span></th>
                    <th style="width:15%;">Prix Unitaire <span class="text-danger">*</span></th>
                    <th style="width:15%;">TVA (%)</th>
                    <th style="width:10%;">Total</th>
                    <th style="width:5%;"></th>
                </tr>
            </thead>
            <tbody id="linesContainer">
                <tr>
                    <td><input type="text" name="lines[0][description]" class="line-input" required placeholder="Description de l'article"></td>
                    <td><input type="number" name="lines[0][quantity]" class="line-input qty" value="1" min="0.01" step="0.01" required oninput="calcTotals()"></td>
                    <td><input type="number" name="lines[0][unit_price]" class="line-input price" value="0" min="0" step="0.01" required oninput="calcTotals()"></td>
                    <td><input type="number" name="lines[0][vat_rate]" class="line-input vat" value="18" min="0" max="100" step="0.1" oninput="calcTotals()"></td>
                    <td class="line-total font-monospace text-end" style="padding-top:18px;">0</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn-add-line" onclick="addLine()"><i class="fas fa-plus"></i> Ajouter une ligne</button>

        <div class="totals-area">
            <div class="total-line">
                <span>Sous-total HT</span>
                <span id="subtotalDisplay" class="font-monospace">0 F</span>
            </div>
            <div class="total-line">
                <span>TVA</span>
                <span id="vatDisplay" class="font-monospace">0 F</span>
            </div>
            <div class="total-line grand-total">
                <span>Total TTC</span>
                <span id="totalDisplay" class="font-monospace">0 F</span>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('gel-accountant.factures.index') }}" class="btn-cancel">Annuler</a>
        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer la facture</button>
    </div>
</form>

@push('scripts')
<script>
    let lineIdx = 1;
    function addLine() {
        const container = document.getElementById('linesContainer');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="lines[${lineIdx}][description]" class="line-input" required placeholder="Description"></td>
            <td><input type="number" name="lines[${lineIdx}][quantity]" class="line-input qty" value="1" min="0.01" step="0.01" required oninput="calcTotals()"></td>
            <td><input type="number" name="lines[${lineIdx}][unit_price]" class="line-input price" value="0" min="0" step="0.01" required oninput="calcTotals()"></td>
            <td><input type="number" name="lines[${lineIdx}][vat_rate]" class="line-input vat" value="18" min="0" max="100" step="0.1" oninput="calcTotals()"></td>
            <td class="line-total font-monospace text-end" style="padding-top:18px;">0</td>
            <td><button type="button" class="btn-action delete" onclick="this.closest('tr').remove(); calcTotals();" style="margin-top:8px;"><i class="fas fa-times"></i></button></td>
        `;
        container.appendChild(tr);
        lineIdx++;
        calcTotals();
    }

    function calcTotals() {
        let subtotal = 0;
        let totalVat = 0;

        document.querySelectorAll('#linesContainer tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.qty').value) || 0;
            const price = parseFloat(tr.querySelector('.price').value) || 0;
            const vatRate = parseFloat(tr.querySelector('.vat').value) || 0;

            const lineSub = qty * price;
            const lineVat = lineSub * (vatRate / 100);
            
            tr.querySelector('.line-total').innerText = new Intl.NumberFormat('fr-FR').format(lineSub + lineVat);
            
            subtotal += lineSub;
            totalVat += lineVat;
        });

        document.getElementById('subtotalDisplay').innerText = new Intl.NumberFormat('fr-FR').format(subtotal) + ' F';
        document.getElementById('vatDisplay').innerText = new Intl.NumberFormat('fr-FR').format(totalVat) + ' F';
        document.getElementById('totalDisplay').innerText = new Intl.NumberFormat('fr-FR').format(subtotal + totalVat) + ' F';
    }
</script>
@endpush
@endsection
