@extends('layouts.gel-accountant')

@section('title', 'Nouvelle facture')

@section('content')

{{-- â•â•â•â•â•â•â•â•â•â•â• EN-TÀŠTE â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-invoice-dollar" style="color:var(--gel-primary); margin-right:8px;"></i> Nouvelle facture</h1>
        <p class="gel-page-subtitle">Créez une facture client avec les détails de facturation</p>
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

<form method="POST" action="{{ route('gel-accountant.factures.store') }}" id="invoiceForm">
    @csrf

    <div class="invoice-form-grid">
        {{-- â•â•â• Colonne gauche : Infos de la facture â•â•â• --}}
        <div class="invoice-form-left">

            {{-- Client --}}
            <div class="gel-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-user"></i> Client</div>
                <div class="gel-form-group">
                    <label>Client / Tiers *</label>
                    <select name="partner_id" class="gel-form-select" required id="partnerSelect">
                        <option value="">— Sélectionner un client —</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}"
                                    data-name="{{ $partner->company_name ?? ($partner->last_name.' '.$partner->first_name) }}"
                                    data-email="{{ $partner->email }}"
                                    data-address="{{ $partner->address }}"
                                    data-tax="{{ $partner->tax_id }}"
                                    {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->company_name ?? ($partner->last_name.' '.$partner->first_name) }}
                                @if($partner->tax_id) — IFU: {{ $partner->tax_id }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="partnerPreview" class="partner-preview" style="display:none;">
                    <div class="partner-preview-name" id="previewName"></div>
                    <div class="partner-preview-detail" id="previewEmail"></div>
                    <div class="partner-preview-detail" id="previewAddress"></div>
                    <div class="partner-preview-detail" id="previewTax"></div>
                </div>
            </div>

            {{-- Lignes de facturation --}}
            <div class="gel-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-list"></i> Lignes de facturation</div>

                <div class="invoice-lines-header">
                    <div style="flex:3;">Description</div>
                    <div style="flex:1; text-align:center;">Qté</div>
                    <div style="flex:1.5; text-align:right;">Prix unit. HT</div>
                    <div style="flex:1; text-align:center;">TVA %</div>
                    <div style="flex:1.5; text-align:right;">Total TTC</div>
                    <div style="width:36px;"></div>
                </div>

                <div id="invoiceLines">
                    {{-- La première ligne est ajoutée par JS --}}
                </div>

                <button type="button" class="invoice-add-line" onclick="addLine()">
                    <i class="fas fa-plus-circle"></i> Ajouter une ligne
                </button>
            </div>

            {{-- Notes --}}
            <div class="gel-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-sticky-note"></i> Notes & Conditions</div>
                <div class="gel-form-group">
                    <label>Notes (visibles sur la facture)</label>
                    <textarea name="notes" class="gel-form-control" rows="3" placeholder="Ex: Merci pour votre confiance...">{{ old('notes') }}</textarea>
                </div>
                <div class="gel-form-group">
                    <label>Conditions générales</label>
                    <textarea name="terms_conditions" class="gel-form-control" rows="2" placeholder="Ex: Paiement À  30 jours net...">{{ old('terms_conditions', 'Paiement À  Réception de la facture. Pénalités de retard : 1,5% par mois.') }}</textarea>
                </div>
            </div>
        </div>

        {{-- â•â•â• Colonne droite : Résumé â•â•â• --}}
        <div class="invoice-form-right">

            {{-- Détails de la facture --}}
            <div class="gel-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-calendar-alt"></i> Détails</div>
                <div class="gel-form-group">
                    <label>Date de facture *</label>
                    <input type="date" name="invoice_date" class="gel-form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                </div>
                <div class="gel-form-group">
                    <label>Date d'échéance *</label>
                    <input type="date" name="due_date" class="gel-form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                </div>
                <div class="gel-form-group">
                    <label>Conditions de paiement</label>
                    <select name="payment_term" class="gel-form-select">
                        <option value="net_30">Net 30 jours</option>
                        <option value="net_15">Net 15 jours</option>
                        <option value="net_60">Net 60 jours</option>
                        <option value="immediate">À Réception</option>
                    </select>
                </div>
            </div>

            {{-- Résumé des montants --}}
            <div class="gel-card invoice-summary-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-calculator"></i> Résumé</div>

                <div class="invoice-summary-row">
                    <span>Sous-total HT</span>
                    <span id="summarySubtotal">0 FCFA</span>
                </div>
                <div class="invoice-summary-row">
                    <span>TVA</span>
                    <span id="summaryVat">0 FCFA</span>
                </div>
                <div class="invoice-summary-divider"></div>
                <div class="invoice-summary-row invoice-summary-total">
                    <span>Total TTC</span>
                    <span id="summaryTotal">0 FCFA</span>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; flex-direction:column; gap:8px;">
                <button type="submit" class="gel-btn gel-btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="fas fa-save"></i> Enregistrer la facture
                </button>
                <a href="{{ route('gel-accountant.factures.index') }}" class="gel-btn gel-btn-secondary" style="width:100%; justify-content:center;">
                    Annuler
                </a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('styles')
<style>
    .invoice-form-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }
    .invoice-section-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--gel-text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .invoice-section-title i {
        color: var(--gel-primary);
        font-size: 14px;
    }

    /* Partner preview */
    .partner-preview {
        background: var(--gel-primary-light);
        border: 1px solid var(--gel-primary);
        border-radius: 6px;
        padding: 12px 14px;
        margin-top: 8px;
    }
    .partner-preview-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--gel-text-primary);
        margin-bottom: 4px;
    }
    .partner-preview-detail {
        font-size: 12px;
        color: var(--gel-text-secondary);
        line-height: 1.5;
    }

    /* Lines header */
    .invoice-lines-header {
        display: flex;
        gap: 10px;
        padding: 8px 12px;
        background: var(--gel-sidebar-bg);
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: white;
        margin-bottom: 8px;
    }

    /* Line row */
    .invoice-line {
        display: flex;
        gap: 10px;
        align-items: center;
        padding: 10px 12px;
        border: 1px solid var(--gel-border);
        border-radius: 6px;
        margin-bottom: 8px;
        background: white;
        transition: box-shadow 120ms;
    }
    .invoice-line:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .invoice-line input {
        border: 1px solid var(--gel-border);
        border-radius: 4px;
        padding: 6px 10px;
        font-size: 13px;
        font-family: inherit;
        outline: none;
        transition: border-color 120ms;
        width: 100%;
    }
    .invoice-line input:focus {
        border-color: var(--gel-primary);
        box-shadow: 0 0 0 2px rgba(0,91,172,0.1);
    }
    .invoice-line .line-total {
        font-weight: 600;
        font-size: 13px;
        color: var(--gel-text-primary);
        text-align: right;
        white-space: nowrap;
        min-width: 100px;
    }
    .invoice-line-remove {
        width: 30px; height: 30px;
        border: none; background: none;
        color: var(--gel-text-muted);
        cursor: pointer;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        transition: all 120ms;
        flex-shrink: 0;
    }
    .invoice-line-remove:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--gel-danger);
    }

    /* Add line button */
    .invoice-add-line {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        border: 2px dashed var(--gel-border);
        border-radius: 6px;
        background: none;
        color: var(--gel-primary);
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        width: 100%;
        justify-content: center;
        transition: all 150ms;
        margin-top: 4px;
    }
    .invoice-add-line:hover {
        background: var(--gel-primary-light);
        border-color: var(--gel-primary);
    }

    /* Summary card */
    .invoice-summary-card {
        background: var(--gel-sidebar-bg);
        border: 1px solid var(--gel-border);
    }
    .invoice-summary-card .invoice-section-title {
        color: white;
    }
    .invoice-summary-card .invoice-section-title i {
        color: white;
    }
    .invoice-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.9);
    }
    .invoice-summary-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.2);
        margin: 4px 0;
    }
    .invoice-summary-total {
        font-size: 16px;
        font-weight: 700;
        color: white;
        padding-top: 12px;
    }

    @media (max-width: 900px) {
        .invoice-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lineIndex = 0;

    // Prévisualisation du client sélectionné
    const partnerSelect = document.getElementById('partnerSelect');
    const previewBox = document.getElementById('partnerPreview');
    
    if (partnerSelect) {
        partnerSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (!opt.value) {
                previewBox.style.display = 'none';
                return;
            }
            document.getElementById('previewName').textContent = opt.dataset.name || '';
            document.getElementById('previewEmail').textContent = opt.dataset.email ? 'âœ‰ ' + opt.dataset.email : '';
            document.getElementById('previewAddress').textContent = opt.dataset.address ? 'ðŸ“ ' + opt.dataset.address : '';
            document.getElementById('previewTax').textContent = opt.dataset.tax ? 'ðŸ› IFU: ' + opt.dataset.tax : '';
            previewBox.style.display = 'block';
        });
    }

    // Ajouter une ligne de facture
    window.addLine = function() {
        const container = document.getElementById('invoiceLines');
        const html = `
            <div class="invoice-line" id="line-${lineIndex}">
                <div style="flex:3;">
                    <input type="text" name="lines[${lineIndex}][description]" placeholder="Description du produit/service" required>
                </div>
                <div style="flex:1;">
                    <input type="number" name="lines[${lineIndex}][quantity]" placeholder="1" step="0.01" min="0.01" value="1" required class="line-qty" oninput="calcLine(${lineIndex})">
                </div>
                <div style="flex:1.5;">
                    <input type="number" name="lines[${lineIndex}][unit_price]" placeholder="0" step="1" min="0" required class="line-price" oninput="calcLine(${lineIndex})">
                </div>
                <div style="flex:1;">
                    <input type="number" name="lines[${lineIndex}][vat_rate]" placeholder="18" step="0.01" min="0" max="100" value="18" class="line-vat" oninput="calcLine(${lineIndex})">
                </div>
                <div style="flex:1.5;">
                    <div class="line-total" id="lineTotal-${lineIndex}">0 F</div>
                </div>
                <button type="button" class="invoice-line-remove" onclick="removeLine(${lineIndex})" title="Supprimer la ligne">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        lineIndex++;
        recalcTotal();
    };

    // Supprimer une ligne
    window.removeLine = function(idx) {
        const line = document.getElementById('line-' + idx);
        if (line) {
            line.remove();
            recalcTotal();
        }
    };

    // Calculer une ligne
    window.calcLine = function(idx) {
        const line = document.getElementById('line-' + idx);
        if (!line) return;
        const qty = parseFloat(line.querySelector('.line-qty').value) || 0;
        const price = parseFloat(line.querySelector('.line-price').value) || 0;
        const vat = parseFloat(line.querySelector('.line-vat').value) || 0;
        const subtotal = qty * price;
        const vatAmount = subtotal * (vat / 100);
        const total = subtotal + vatAmount;
        document.getElementById('lineTotal-' + idx).textContent = formatMoney(total) + ' F';
        recalcTotal();
    };

    // Recalculer le résumé
    function recalcTotal() {
        let subtotal = 0;
        let vatTotal = 0;

        document.querySelectorAll('.invoice-line').forEach(line => {
            const qty = parseFloat(line.querySelector('.line-qty')?.value) || 0;
            const price = parseFloat(line.querySelector('.line-price')?.value) || 0;
            const vat = parseFloat(line.querySelector('.line-vat')?.value) || 0;
            const lineSub = qty * price;
            subtotal += lineSub;
            vatTotal += lineSub * (vat / 100);
        });

        const total = subtotal + vatTotal;
        document.getElementById('summarySubtotal').textContent = formatMoney(subtotal) + ' FCFA';
        document.getElementById('summaryVat').textContent = formatMoney(vatTotal) + ' FCFA';
        document.getElementById('summaryTotal').textContent = formatMoney(total) + ' FCFA';
    }

    function formatMoney(amount) {
        return Math.round(amount).toLocaleString('fr-FR');
    }

    // Ajouter la première ligne au chargement
    addLine();
});
</script>
@endpush

