@extends('layouts.gel-accountant')

@section('title', 'Créer une estimation (Devis)')

@section('content')

{{-- â•â•â•â•â•â•â•â•â•â•â• EN-TÀŠTE â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-calculator" style="color:var(--gel-primary); margin-right:8px;"></i> Créer une estimation</h1>
        <p class="gel-page-subtitle">Envoyez un devis professionnel À  votre client avant de le convertir en facture.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

@if($errors->any())
<div style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.3); border-radius:8px; padding:14px 18px; margin-bottom:20px;">
    <div style="font-weight:600; color:var(--gel-danger); margin-bottom:6px;"><i class="fas fa-exclamation-circle"></i> Erreurs de validation</div>
    <ul style="margin:0; padding-left:20px; font-size:13px; color:var(--gel-danger);">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<form method="POST" action="#" id="estimationForm">
@csrf
<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        {{-- Client --}}
        <div class="doc-form-group">
            <label class="doc-label">Client *</label>
            <select name="partner_id" class="doc-input" required>
                <option value="">— Sélectionner un client —</option>
                @foreach(\App\Models\Partner::where('type','client')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name ?: $c->first_name.' '.$c->last_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Date estimation --}}
        <div class="doc-form-group">
            <label class="doc-label">Date de l'estimation *</label>
            <input type="date" name="estimate_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>

        {{-- Date d'expiration --}}
        <div class="doc-form-group">
            <label class="doc-label">Date d'expiration</label>
            <input type="date" name="expiry_date" class="doc-input" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
        </div>

        {{-- Numéro --}}
        <div class="doc-form-group">
            <label class="doc-label">NÂ° du devis</label>
            <input type="text" name="estimate_number" class="doc-input" value="EST-{{ str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT) }}" readonly>
        </div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• LIGNES DU DEVIS â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table" id="linesTable">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:30%;">Description du service / produit</th>
                <th style="width:12%;">Quantité</th>
                <th style="width:15%;">Prix unitaire (FCFA)</th>
                <th style="width:10%;">TVA %</th>
                <th style="width:15%;">Montant</th>
                <th style="width:5%;"></th>
            </tr>
        </thead>
        <tbody id="linesBody">
            <tr class="doc-line-row">
                <td class="line-num">1</td>
                <td><input type="text" name="lines[0][description]" class="doc-input-sm" placeholder="Description..."></td>
                <td><input type="number" name="lines[0][quantity]" class="doc-input-sm line-qty" value="1" min="1" step="0.01"></td>
                <td><input type="number" name="lines[0][unit_price]" class="doc-input-sm line-price" value="0" min="0" step="1"></td>
                <td>
                    <select name="lines[0][tax_rate]" class="doc-input-sm line-tax">
                        <option value="0">0%</option>
                        <option value="18" selected>18%</option>
                        <option value="19.25">19.25%</option>
                    </select>
                </td>
                <td class="line-total" style="font-weight:600; text-align:right;">0 FCFA</td>
                <td><button type="button" class="doc-line-remove" onclick="removeLine(this)" title="Supprimer"><i class="fas fa-times"></i></button></td>
            </tr>
        </tbody>
    </table>
    <div style="padding:12px 16px; border-top:1px solid var(--gel-border);">
        <button type="button" class="gel-btn gel-btn-secondary" onclick="addLine()" style="font-size:13px;">
            <i class="fas fa-plus"></i> Ajouter une ligne
        </button>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• RÉSUMÉ + NOTES â•â•â•â•â•â•â•â•â•â•â• --}}
<div style="display:grid; grid-template-columns:1fr 360px; gap:20px; margin-bottom:20px;">
    <div class="gel-card p-4 mb-4">
        <label class="doc-label">Message au client (visible sur le devis)</label>
        <textarea name="message" class="doc-input" rows="3" placeholder="Merci pour votre confiance. Ce devis est valable 30 jours..."></textarea>
        <label class="doc-label" style="margin-top:12px;">Mémo interne (privé)</label>
        <textarea name="memo" class="doc-input" rows="2" placeholder="Notes internes..."></textarea>
    </div>
    <div class="gel-card p-4 mb-4">
        <div class="doc-summary-row"><span>Sous-total</span><span id="subtotal">0 FCFA</span></div>
        <div class="doc-summary-row"><span>TVA</span><span id="totalTax">0 FCFA</span></div>
        <div class="doc-summary-row doc-summary-total"><span>Total TTC</span><span id="grandTotal">0 FCFA</span></div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• ACTIONS â•â•â•â•â•â•â•â•â•â•â• --}}
<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" name="action" value="draft" class="gel-btn gel-btn-secondary"><i class="fas fa-save"></i> Enregistrer comme brouillon</button>
    <button type="submit" name="action" value="send" class="gel-btn gel-btn-primary"><i class="fas fa-paper-plane"></i> Envoyer au client</button>
</div>
</form>



<script>
let lineIndex = 1;
function addLine() {
    const tbody = document.getElementById('linesBody');
    const row = document.createElement('tr');
    row.className = 'doc-line-row';
    row.innerHTML = `
        <td class="line-num">${tbody.children.length + 1}</td>
        <td><input type="text" name="lines[${lineIndex}][description]" class="doc-input-sm" placeholder="Description..."></td>
        <td><input type="number" name="lines[${lineIndex}][quantity]" class="doc-input-sm line-qty" value="1" min="1" step="0.01"></td>
        <td><input type="number" name="lines[${lineIndex}][unit_price]" class="doc-input-sm line-price" value="0" min="0" step="1"></td>
        <td><select name="lines[${lineIndex}][tax_rate]" class="doc-input-sm line-tax"><option value="0">0%</option><option value="18" selected>18%</option><option value="19.25">19.25%</option></select></td>
        <td class="line-total" style="font-weight:600; text-align:right;">0 FCFA</td>
        <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(row);
    lineIndex++;
    recalc();
}
function removeLine(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('.doc-line-row').length > 1) { row.remove(); renumber(); recalc(); }
}
function renumber() {
    document.querySelectorAll('.doc-line-row').forEach((r, i) => r.querySelector('.line-num').textContent = i + 1);
}
function recalc() {
    let sub = 0, tax = 0;
    document.querySelectorAll('.doc-line-row').forEach(row => {
        const q = parseFloat(row.querySelector('.line-qty')?.value || 0);
        const p = parseFloat(row.querySelector('.line-price')?.value || 0);
        const t = parseFloat(row.querySelector('.line-tax')?.value || 0);
        const lineTotal = q * p;
        const lineTax = lineTotal * t / 100;
        sub += lineTotal; tax += lineTax;
        row.querySelector('.line-total').textContent = Math.round(lineTotal).toLocaleString('fr-FR') + ' FCFA';
    });
    document.getElementById('subtotal').textContent = Math.round(sub).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('totalTax').textContent = Math.round(tax).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('grandTotal').textContent = Math.round(sub + tax).toLocaleString('fr-FR') + ' FCFA';
}
document.getElementById('linesTable').addEventListener('input', recalc);
</script>
@endsection

