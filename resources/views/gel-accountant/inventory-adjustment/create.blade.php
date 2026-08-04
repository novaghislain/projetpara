@extends('layouts.gel-accountant')

@section('title', "Ajustement d'inventaire")

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-boxes" style="color:var(--gel-primary); margin-right:8px;"></i> Ajustement d'inventaire</h1>
        <p class="gel-page-subtitle">Ajustez la quantité ou la valeur de vos articles en stock.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="inventoryAdjustmentForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Date de l'ajustement *</label>
            <input type="date" name="adjustment_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Compte d'ajustement *</label>
            <select name="adjustment_account" class="doc-input" required>
                <option value="">— Sélectionner le compte de perte/gain —</option>
                <option value="603">603 — Variations des stocks de biens et services</option>
                <option value="654">654 — Pertes sur créances et pertes exceptionnelles</option>
                <option value="79">79 — Reprises de provisions</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">NÂ° de référence</label>
            <input type="text" name="reference_number" class="doc-input" value="ADJ-{{ str_pad(rand(1,999), 3, '0', STR_PAD_LEFT) }}" readonly>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table" id="linesTable">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:30%;">Article en stock</th>
                <th style="width:20%;">Description</th>
                <th style="width:12%;">Qté actuelle</th>
                <th style="width:12%;">Nouvelle qté</th>
                <th style="width:16%;">Variation de qté</th>
                <th style="width:5%;"></th>
            </tr>
        </thead>
        <tbody id="linesBody">
            <tr class="doc-line-row">
                <td class="line-num">1</td>
                <td>
                    <select name="lines[0][item]" class="doc-input-sm" onchange="setItemData(this)">
                        <option value="">Sélectionner un article...</option>
                        <option value="item1" data-qty="50" data-desc="Ordinateur Portable Dell">Ordinateur Portable Dell</option>
                        <option value="item2" data-qty="120" data-desc="Souris sans fil Logitech">Souris sans fil Logitech</option>
                        <option value="item3" data-qty="15" data-desc="Imprimante Laser HP">Imprimante Laser HP</option>
                    </select>
                </td>
                <td><input type="text" name="lines[0][description]" class="doc-input-sm line-desc" readonly></td>
                <td><input type="number" class="doc-input-sm line-current-qty" value="0" readonly style="background:#f3f4f6;"></td>
                <td><input type="number" name="lines[0][new_qty]" class="doc-input-sm line-new-qty" value="0" min="0"></td>
                <td><input type="number" class="doc-input-sm line-diff" value="0" readonly style="background:#f3f4f6; font-weight:600;"></td>
                <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
            </tr>
        </tbody>
    </table>
    <div style="padding:12px 16px; border-top:1px solid var(--gel-border);">
        <button type="button" class="gel-btn gel-btn-secondary" onclick="addLine()" style="font-size:13px;"><i class="fas fa-plus"></i> Ajouter un article</button>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div class="doc-form-group">
        <label class="doc-label">Mémo / Motif de l'ajustement</label>
        <textarea name="memo" class="doc-input" rows="3" placeholder="Ex: Inventaire physique de fin de mois, articles endommagés..."></textarea>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer l'ajustement</button>
</div>
</form>


<script>
let lineIndex=1;
const itemOptions=`<option value="">Sélectionner un article...</option><option value="item1" data-qty="50" data-desc="Ordinateur Portable Dell">Ordinateur Portable Dell</option><option value="item2" data-qty="120" data-desc="Souris sans fil Logitech">Souris sans fil Logitech</option><option value="item3" data-qty="15" data-desc="Imprimante Laser HP">Imprimante Laser HP</option>`;

function addLine(){const t=document.getElementById('linesBody');const r=document.createElement('tr');r.className='doc-line-row';r.innerHTML=`<td class="line-num">${t.children.length+1}</td><td><select name="lines[${lineIndex}][item]" class="doc-input-sm" onchange="setItemData(this)">${itemOptions}</select></td><td><input type="text" name="lines[${lineIndex}][description]" class="doc-input-sm line-desc" readonly></td><td><input type="number" class="doc-input-sm line-current-qty" value="0" readonly style="background:#f3f4f6;"></td><td><input type="number" name="lines[${lineIndex}][new_qty]" class="doc-input-sm line-new-qty" value="0" min="0"></td><td><input type="number" class="doc-input-sm line-diff" value="0" readonly style="background:#f3f4f6; font-weight:600;"></td><td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;t.appendChild(r);lineIndex++;}

function removeLine(b){const r=b.closest('tr');if(document.querySelectorAll('.doc-line-row').length>1){r.remove();renumber();}}
function renumber(){document.querySelectorAll('.doc-line-row').forEach((r,i)=>r.querySelector('.line-num').textContent=i+1);}

function setItemData(sel){
    const opt=sel.options[sel.selectedIndex];const row=sel.closest('tr');
    if(opt.value){
        row.querySelector('.line-current-qty').value=opt.dataset.qty;
        row.querySelector('.line-new-qty').value=opt.dataset.qty;
        row.querySelector('.line-desc').value=opt.dataset.desc;
    }else{
        row.querySelector('.line-current-qty').value=0;
        row.querySelector('.line-new-qty').value=0;
        row.querySelector('.line-desc').value='';
    }
    calcDiff(row);
}

function calcDiff(row){
    const curr=parseFloat(row.querySelector('.line-current-qty').value||0);
    const newVal=parseFloat(row.querySelector('.line-new-qty').value||0);
    const diffField=row.querySelector('.line-diff');
    const diff=newVal-curr;
    diffField.value=diff;
    if(diff>0) diffField.style.color='var(--gel-success)';
    else if(diff<0) diffField.style.color='var(--gel-danger)';
    else diffField.style.color='';
}

document.getElementById('linesTable').addEventListener('input',function(e){
    if(e.target.classList.contains('line-new-qty')) calcDiff(e.target.closest('tr'));
});
</script>
@endsection

