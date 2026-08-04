@extends('layouts.gel-accountant')

@section('title', 'Crédit fournisseur')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-hand-holding-usd" style="color:var(--gel-primary); margin-right:8px;"></i> Crédit fournisseur</h1>
        <p class="gel-page-subtitle">Enregistrez un avoir ou un Crédit accordé par votre fournisseur pour réduire vos futures factures.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="vendorCreditForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Fournisseur *</label>
            <select name="partner_id" class="doc-input" required>
                <option value="">— Sélectionner un fournisseur —</option>
                @foreach(\App\Models\Partner::where('type', 'fournisseur')->get() as $f)
                    <option value="{{ $f->id }}">{{ $f->company_name ?: $f->first_name.' '.$f->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date du Crédit *</label>
            <input type="date" name="credit_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">NÂ° de l'avoir fournisseur</label>
            <input type="text" name="credit_number" class="doc-input" placeholder="Ex: AV-2026-004">
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table" id="linesTable">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:25%;">Catégorie</th>
                <th style="width:35%;">Description de l'avoir</th>
                <th style="width:15%;">Montant HT</th>
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
                        <option value="achat_marchandises">Achat de marchandises</option>
                        <option value="sous_traitance">Sous-traitance</option>
                        <option value="autre">Autre</option>
                    </select>
                </td>
                <td><input type="text" name="lines[0][description]" class="doc-input-sm" placeholder="Ex: Remise sur commande 123..."></td>
                <td><input type="number" name="lines[0][amount]" class="doc-input-sm line-amount" value="0" min="0"></td>
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
        <button type="button" class="gel-btn gel-btn-secondary" onclick="addLine()" style="font-size:13px;"><i class="fas fa-plus"></i> Ajouter une ligne</button>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 360px; gap:20px; margin-bottom:20px;">
    <div class="gel-card p-4 mb-4">
        <label class="doc-label">Mémo interne</label>
        <textarea name="memo" class="doc-input" rows="3" placeholder="Notes pour vos dossiers..."></textarea>
    </div>
    <div class="gel-card p-4 mb-4">
        <div class="doc-summary-row"><span>Sous-total HT</span><span id="subtotal">0 FCFA</span></div>
        <div class="doc-summary-row"><span>TVA (À  déduire)</span><span id="totalTax">0 FCFA</span></div>
        <div class="doc-summary-row doc-summary-total"><span>Total Crédit Fournisseur</span><span id="grandTotal">0 FCFA</span></div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer le Crédit</button>
</div>
</form>


<script>
let lineIndex=1;
function addLine(){const t=document.getElementById('linesBody');const r=document.createElement('tr');r.className='doc-line-row';r.innerHTML=`<td class="line-num">${t.children.length+1}</td><td><select name="lines[${lineIndex}][account_id]" class="doc-input-sm"><option value="">Catégorie...</option><option value="achat_marchandises">Achat de marchandises</option><option value="sous_traitance">Sous-traitance</option><option value="autre">Autre</option></select></td><td><input type="text" name="lines[${lineIndex}][description]" class="doc-input-sm" placeholder="Ex: Remise sur commande 123..."></td><td><input type="number" name="lines[${lineIndex}][amount]" class="doc-input-sm line-amount" value="0" min="0"></td><td><select name="lines[${lineIndex}][tax_rate]" class="doc-input-sm line-tax"><option value="0">0%</option><option value="18" selected>18%</option></select></td><td class="line-total" style="font-weight:600; text-align:right;">0</td><td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;t.appendChild(r);lineIndex++;}
function removeLine(b){const r=b.closest('tr');if(document.querySelectorAll('.doc-line-row').length>1){r.remove();renumber();recalc();}}
function renumber(){document.querySelectorAll('.doc-line-row').forEach((r,i)=>r.querySelector('.line-num').textContent=i+1);}
function recalc(){let s=0,t=0;document.querySelectorAll('.doc-line-row').forEach(r=>{const a=parseFloat(r.querySelector('.line-amount')?.value||0);const tx=parseFloat(r.querySelector('.line-tax')?.value||0);s+=a;t+=a*tx/100;r.querySelector('.line-total').textContent=Math.round(a+(a*tx/100)).toLocaleString('fr-FR');});document.getElementById('subtotal').textContent=Math.round(s).toLocaleString('fr-FR')+' FCFA';document.getElementById('totalTax').textContent=Math.round(t).toLocaleString('fr-FR')+' FCFA';document.getElementById('grandTotal').textContent=Math.round(s+t).toLocaleString('fr-FR')+' FCFA';}
document.getElementById('linesTable').addEventListener('input',recalc);
</script>
@endsection

