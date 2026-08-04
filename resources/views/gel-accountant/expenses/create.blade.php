@extends('layouts.gel-accountant')

@section('title', 'Saisir une dépense')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-money-bill-wave" style="color:var(--gel-primary); margin-right:8px;"></i> Saisir une dépense</h1>
        <p class="gel-page-subtitle">Enregistrez les Dépenses de votre entreprise pour un suivi comptable précis.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="{{ route('gel-accountant.expenses.store') }}" id="expenseForm" enctype="multipart/form-data">
@csrf

<div style="display:grid; grid-template-columns: 1fr 340px; gap:20px; margin-bottom:20px;">
    {{-- Colonne gauche : détails dépense --}}
    <div>
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-info-circle" style="color:var(--gel-primary);"></i> Informations de la dépense</h3>
            <div class="doc-form-grid">
                <div class="doc-form-group">
                    <label class="doc-label">Fournisseur / Bénéficiaire *</label>
                    <select name="partner_id" class="doc-input">
                        <option value="">— Sélectionner —</option>
                        @foreach(\App\Models\Partner::where('type','fournisseur')->get() as $f)
                            <option value="{{ $f->id }}">{{ $f->company_name ?: $f->first_name.' '.$f->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Compte de paiement *</label>
                    <select name="payment_account" class="doc-input" required>
                        <option value="">— Sélectionner —</option>
                        @foreach(\App\Models\BankAccount::whereIn('type', ['banque','caisse'])->get() as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Date de la dépense *</label>
                    <input type="date" name="expense_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Date d'échéance</label>
                    <input type="date" name="due_date" class="doc-input" value="{{ date('Y-m-d') }}">
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Statut *</label>
                    <select name="status" class="doc-input" required>
                        <option value="unpaid">À payer</option>
                        <option value="paid" selected>Payée</option>
                        <option value="disputed">En litige</option>
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Mode de paiement</label>
                    <select name="payment_method" class="doc-input">
                        <option value="cash">Espèces</option>
                        <option value="bank_transfer">Virement bancaire</option>
                        <option value="check">Chèque</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="credit_card">Carte de Crédit</option>
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">NÂ° de référence</label>
                    <input type="text" name="reference" class="doc-input" placeholder="Ex: REC-00123">
                </div>
            </div>
        </div>

        {{-- e-MECeF / Sygmef --}}
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-qrcode" style="color:var(--gel-success);"></i> Facture Normalisée (e-MECeF / Sygmef)</h3>
            <p style="font-size:12px; color:var(--gel-text-secondary); margin-bottom:16px;">Renseignez ces informations si la facture du fournisseur est normalisée, afin de pouvoir récupérer la TVA (obligatoire selon SYSCOHADA / DGI).</p>
            <div class="doc-form-grid">
                <div class="doc-form-group">
                    <label class="doc-label">NIM (Numéro d'Identité Machine)</label>
                    <input type="text" name="emecef_nim" class="doc-input" placeholder="Ex: XX12345678">
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Compteur</label>
                    <input type="text" name="emecef_compteur" class="doc-input" placeholder="Ex: 1234/5678">
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Date e-MECeF</label>
                    <input type="datetime-local" name="emecef_datetime" class="doc-input">
                </div>
            </div>
        </div>

        {{-- Lignes de dépense --}}
        <div class="gel-card p-4 mb-4" style="overflow:hidden;">
            <table class="doc-lines-table" id="linesTable">
                <thead>
                    <tr>
                        <th style="width:5%;">#</th>
                        <th style="width:25%;">Catégorie / Compte</th>
                        <th style="width:30%;">Description</th>
                        <th style="width:15%;">Montant (FCFA)</th>
                        <th style="width:10%;">TVA %</th>
                        <th style="width:10%;">Total</th>
                        <th style="width:5%;"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">
                    <tr class="doc-line-row">
                        <td class="line-num">1</td>
                        <td>
                            <select name="lines[0][account_id]" class="doc-input-sm">
                                <option value="">Catégorie...</option>
                                <option value="loyer">Loyer</option>
                                <option value="fournitures">Fournitures de bureau</option>
                                <option value="transport">Transport</option>
                                <option value="telecom">Télécom / Internet</option>
                                <option value="salaires">Salaires</option>
                                <option value="publicite">Publicité / Marketing</option>
                                <option value="autre">Autre</option>
                            </select>
                        </td>
                        <td><input type="text" name="lines[0][description]" class="doc-input-sm" placeholder="Description..."></td>
                        <td><input type="number" name="lines[0][amount]" class="doc-input-sm line-amount" value="0" min="0" step="1"></td>
                        <td>
                            <select name="lines[0][tax_rate]" class="doc-input-sm line-tax">
                                <option value="0" selected>0%</option>
                                <option value="18">18%</option>
                                <option value="19.25">19.25%</option>
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
            <div class="doc-summary-row"><span>Sous-total</span><span id="subtotal">0 FCFA</span></div>
            <div class="doc-summary-row"><span>TVA</span><span id="totalTax">0 FCFA</span></div>
            <div class="doc-summary-row">
                <span>Retenue AIB</span>
                <div style="display:flex; gap:10px; align-items:center; justify-content:flex-end;">
                    <select name="aib_rate" id="aibRate" class="doc-input-sm" style="width:80px; padding:4px;" onchange="recalc()">
                        <option value="0">0%</option>
                        <option value="1">1%</option>
                        <option value="5">5%</option>
                    </select>
                    <span id="totalAib" style="color:var(--gel-danger); font-weight:600;">- 0 FCFA</span>
                </div>
            </div>
            <div class="doc-summary-row doc-summary-total"><span>Total Net à payer</span><span id="grandTotal">0 FCFA</span></div>
        </div>

        <div class="gel-card p-4 mb-4">
            <label class="doc-label">Mémo / Notes</label>
            <textarea name="memo" class="doc-input" rows="3" placeholder="Ajouter un mémo..."></textarea>
        </div>

        <div class="gel-card p-4 mb-4">
            <label class="doc-label">Pièce jointe (reçu, facture...)</label>
            <div style="border:2px dashed var(--gel-border); border-radius:8px; padding:30px; text-align:center; cursor:pointer;" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size:28px; color:var(--gel-text-muted); margin-bottom:8px;"></i>
                <p style="font-size:13px; color:var(--gel-text-secondary);">Glissez ou cliquez pour importer</p>
                <input type="file" id="fileInput" name="attachment" style="display:none;" accept="image/*,.pdf">
            </div>
        </div>
    </div>
</div>

{{-- Actions --}}
<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer la dépense</button>
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
        <td><select name="lines[${lineIndex}][account_id]" class="doc-input-sm"><option value="">Catégorie...</option><option value="loyer">Loyer</option><option value="fournitures">Fournitures de bureau</option><option value="transport">Transport</option><option value="telecom">Télécom / Internet</option><option value="salaires">Salaires</option><option value="publicite">Publicité / Marketing</option><option value="autre">Autre</option></select></td>
        <td><input type="text" name="lines[${lineIndex}][description]" class="doc-input-sm" placeholder="Description..."></td>
        <td><input type="number" name="lines[${lineIndex}][amount]" class="doc-input-sm line-amount" value="0" min="0" step="1"></td>
        <td><select name="lines[${lineIndex}][tax_rate]" class="doc-input-sm line-tax"><option value="0" selected>0%</option><option value="18">18%</option><option value="19.25">19.25%</option></select></td>
        <td class="line-total" style="font-weight:600; text-align:right;">0</td>
        <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;
    tbody.appendChild(row); lineIndex++;
}
function removeLine(btn) { const r = btn.closest('tr'); if(document.querySelectorAll('.doc-line-row').length > 1) { r.remove(); renumber(); recalc(); } }
function renumber() { document.querySelectorAll('.doc-line-row').forEach((r,i) => r.querySelector('.line-num').textContent = i+1); }
function recalc() {
    let sub=0, tax=0;
    document.querySelectorAll('.doc-line-row').forEach(row => {
        const a = parseFloat(row.querySelector('.line-amount')?.value||0);
        const t = parseFloat(row.querySelector('.line-tax')?.value||0);
        const lineTax = a * t / 100;
        sub += a; tax += lineTax;
        row.querySelector('.line-total').textContent = Math.round(a+lineTax).toLocaleString('fr-FR');
    });
    const aibRate = parseFloat(document.getElementById('aibRate').value || 0);
    const aibAmount = sub * (aibRate / 100);

    document.getElementById('subtotal').textContent = Math.round(sub).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('totalTax').textContent = Math.round(tax).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('totalAib').textContent = '- ' + Math.round(aibAmount).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('grandTotal').textContent = Math.round(sub + tax - aibAmount).toLocaleString('fr-FR') + ' FCFA';
}
document.getElementById('linesTable').addEventListener('input', recalc);
</script>
@endsection

