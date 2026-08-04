@extends('layouts.gel-accountant')

@section('title', 'Récépissé de vente')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-receipt" style="color:var(--gel-primary); margin-right:8px;"></i> Créer un Récépissé de vente</h1>
        <p class="gel-page-subtitle">Enregistrez une vente pour laquelle vous avez été payé immédiatement.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="salesReceiptForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Client *</label>
            <select name="partner_id" class="doc-input" required>
                <option value="">— Sélectionner un client —</option>
                @foreach(\App\Models\Partner::where('type', 'client')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name ?: $c->first_name.' '.$c->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date du reçu *</label>
            <input type="date" name="receipt_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">NÂ° du reçu</label>
            <input type="text" name="receipt_number" class="doc-input" value="REC-{{ str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT) }}" readonly>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Déposer sur le compte *</label>
            <select name="deposit_to" class="doc-input" required>
                @foreach(\App\Models\BankAccount::whereIn('type', ['banque', 'caisse'])->get() as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table" id="linesTable">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:30%;">Produit / Service vendu</th>
                <th style="width:12%;">Quantité</th>
                <th style="width:15%;">Prix unitaire HT</th>
                <th style="width:10%;">TVA %</th>
                <th style="width:15%;">Total HT</th>
                <th style="width:5%;"></th>
            </tr>
        </thead>
        <tbody id="linesBody">
            <tr class="doc-line-row">
                <td class="line-num">1</td>
                <td><input type="text" name="lines[0][description]" class="doc-input-sm" placeholder="Produit/Service..."></td>
                <td><input type="number" name="lines[0][quantity]" class="doc-input-sm line-qty" value="1" min="1" step="0.01"></td>
                <td><input type="number" name="lines[0][unit_price]" class="doc-input-sm line-price" value="0" min="0"></td>
                <td><select name="lines[0][tax_rate]" class="doc-input-sm line-tax"><option value="0">0%</option><option value="18" selected>18%</option></select></td>
                <td class="line-total" style="font-weight:600; text-align:right;">0 FCFA</td>
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
        <label class="doc-label">Message sur le reçu</label>
        <textarea name="message" class="doc-input" rows="3" placeholder="Merci pour votre achat !"></textarea>
    </div>
    <div class="gel-card p-4 mb-4">
        <div class="doc-summary-row"><span>Sous-total HT</span><span id="subtotal">0 FCFA</span></div>
        <div class="doc-summary-row"><span>Total TVA</span><span id="totalTax">0 FCFA</span></div>
        <div class="doc-summary-row doc-summary-total"><span>Total Reçu</span><span id="grandTotal">0 FCFA</span></div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer le Récépissé</button>
</div>
</form>


<script>
let lineIndex=1;
function addLine(){const t=document.getElementById('linesBody');const r=document.createElement('tr');r.className='doc-line-row';r.innerHTML=`<td class="line-num">${t.children.length+1}</td><td><input type="text" name="lines[${lineIndex}][description]" class="doc-input-sm" placeholder="Produit/Service..."></td><td><input type="number" name="lines[${lineIndex}][quantity]" class="doc-input-sm line-qty" value="1" min="1" step="0.01"></td><td><input type="number" name="lines[${lineIndex}][unit_price]" class="doc-input-sm line-price" value="0" min="0"></td><td><select name="lines[${lineIndex}][tax_rate]" class="doc-input-sm line-tax"><option value="0">0%</option><option value="18" selected>18%</option></select></td><td class="line-total" style="font-weight:600; text-align:right;">0 FCFA</td><td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;t.appendChild(r);lineIndex++;}
function removeLine(b){const r=b.closest('tr');if(document.querySelectorAll('.doc-line-row').length>1){r.remove();renumber();recalc();}}
function renumber(){document.querySelectorAll('.doc-line-row').forEach((r,i)=>r.querySelector('.line-num').textContent=i+1);}
function recalc(){let s=0,t=0;document.querySelectorAll('.doc-line-row').forEach(r=>{const q=parseFloat(r.querySelector('.line-qty')?.value||0);const p=parseFloat(r.querySelector('.line-price')?.value||0);const tx=parseFloat(r.querySelector('.line-tax')?.value||0);s+=q*p;t+=q*p*tx/100;r.querySelector('.line-total').textContent=Math.round(q*p).toLocaleString('fr-FR')+' FCFA';});document.getElementById('subtotal').textContent=Math.round(s).toLocaleString('fr-FR')+' FCFA';document.getElementById('totalTax').textContent=Math.round(t).toLocaleString('fr-FR')+' FCFA';document.getElementById('grandTotal').textContent=Math.round(s+t).toLocaleString('fr-FR')+' FCFA';}
document.getElementById('linesTable').addEventListener('input',recalc);
</script>
@endsection

