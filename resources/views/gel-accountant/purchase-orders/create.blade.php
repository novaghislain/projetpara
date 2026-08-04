@extends('layouts.gel-accountant')

@section('title', 'Créer un Bon de Commande')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-contract" style="color:var(--gel-primary); margin-right:8px;"></i> Nouveau Bon de Commande</h1>
        <p class="gel-page-subtitle">Créez une commande pour un fournisseur</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="{{ route('gel-accountant.purchase-orders.store') }}" id="poForm">
@csrf

<div style="display:grid; grid-template-columns: 1fr 340px; gap:20px; margin-bottom:20px;">
    {{-- Colonne gauche : détails --}}
    <div>
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-info-circle" style="color:var(--gel-primary);"></i> Informations de la commande</h3>
            <div class="doc-form-grid">
                <div class="doc-form-group">
                    <label class="doc-label">Fournisseur *</label>
                    <select name="partner_id" class="doc-input" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($partners as $f)
                            <option value="{{ $f->id }}">{{ $f->company_name ?: $f->first_name.' '.$f->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Date de commande *</label>
                    <input type="date" name="order_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Date de livraison prévue</label>
                    <input type="date" name="delivery_date" class="doc-input">
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Référence fournisseur / Devis</label>
                    <input type="text" name="reference" class="doc-input" placeholder="Ex: DEV-2026-10">
                </div>
            </div>
        </div>

        {{-- Lignes de commande --}}
        <div class="gel-card p-4 mb-4" style="overflow:hidden;">
            <table class="doc-lines-table" id="linesTable">
                <thead>
                    <tr>
                        <th style="width:5%;">#</th>
                        <th style="width:35%;">Désignation</th>
                        <th style="width:10%;">Qté</th>
                        <th style="width:15%;">Prix Unitaire</th>
                        <th style="width:10%;">TVA %</th>
                        <th style="width:15%;">Total HT</th>
                        <th style="width:10%;"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">
                    <tr class="doc-line-row">
                        <td class="line-num">1</td>
                        <td><input type="text" name="lines[0][description]" class="doc-input-sm" placeholder="Description de l'article" required></td>
                        <td><input type="number" name="lines[0][quantity]" class="doc-input-sm line-qty" value="1" min="0.01" step="0.01" required></td>
                        <td><input type="number" name="lines[0][unit_price]" class="doc-input-sm line-price" value="0" min="0" step="1" required></td>
                        <td>
                            <select name="lines[0][tax_rate]" class="doc-input-sm line-tax">
                                <option value="0">0%</option>
                                <option value="18" selected>18%</option>
                            </select>
                        </td>
                        <td class="line-total" style="font-weight:600; text-align:right;">0</td>
                        <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
            </table>
            <div style="padding:12px 16px; border-top:1px solid var(--gel-border);">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="addLine()" style="font-size:13px;">
                    <i class="fas fa-plus"></i> Ajouter une ligne
                </button>
            </div>
        </div>
    </div>

    {{-- Colonne droite : résumé --}}
    <div>
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:14px; font-weight:700; margin-bottom:12px;">Résumé</h3>
            <div class="doc-summary-row"><span>Sous-total HT</span><span id="subtotal">0 FCFA</span></div>
            <div class="doc-summary-row"><span>TVA</span><span id="totalTax">0 FCFA</span></div>
            <div class="doc-summary-row doc-summary-total"><span>Total TTC</span><span id="grandTotal">0 FCFA</span></div>
        </div>

        <div class="gel-card p-4 mb-4">
            <label class="doc-label">Instructions / Notes pour le fournisseur</label>
            <textarea name="notes" class="doc-input" rows="3" placeholder="Notes..."></textarea>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Générer le Bon de Commande</button>
</div>
</form>

<script>
let lineIndex = 1;
function addLine() {
    const tbody = document.getElementById('linesBody');
    const row = document.createElement('tr');
    row.className = 'doc-line-row';
    row.innerHTML = `
        <td class="line-num">${tbody.children.length+1}</td>
        <td><input type="text" name="lines[${lineIndex}][description]" class="doc-input-sm" placeholder="Description de l'article" required></td>
        <td><input type="number" name="lines[${lineIndex}][quantity]" class="doc-input-sm line-qty" value="1" min="0.01" step="0.01" required></td>
        <td><input type="number" name="lines[${lineIndex}][unit_price]" class="doc-input-sm line-price" value="0" min="0" step="1" required></td>
        <td><select name="lines[${lineIndex}][tax_rate]" class="doc-input-sm line-tax"><option value="0">0%</option><option value="18" selected>18%</option></select></td>
        <td class="line-total" style="font-weight:600; text-align:right;">0</td>
        <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;
    tbody.appendChild(row); lineIndex++;
}
function removeLine(btn) { const r = btn.closest('tr'); if(document.querySelectorAll('.doc-line-row').length > 1) { r.remove(); renumber(); recalc(); } }
function renumber() { document.querySelectorAll('.doc-line-row').forEach((r,i) => r.querySelector('.line-num').textContent = i+1); }
function recalc() {
    let sub=0, tax=0;
    document.querySelectorAll('.doc-line-row').forEach(row => {
        const q = parseFloat(row.querySelector('.line-qty')?.value||0);
        const p = parseFloat(row.querySelector('.line-price')?.value||0);
        const t = parseFloat(row.querySelector('.line-tax')?.value||0);
        const lineSub = q * p;
        const lineTax = lineSub * t / 100;
        sub += lineSub; tax += lineTax;
        row.querySelector('.line-total').textContent = Math.round(lineSub).toLocaleString('fr-FR');
    });
    document.getElementById('subtotal').textContent = Math.round(sub).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('totalTax').textContent = Math.round(tax).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('grandTotal').textContent = Math.round(sub+tax).toLocaleString('fr-FR') + ' FCFA';
}
document.getElementById('linesTable').addEventListener('input', recalc);
</script>
@endsection
