@extends('layouts.gel-accountant')

@section('title', "Réception d'un article")

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-box-open" style="color:var(--gel-primary); margin-right:8px;"></i> Réception d'articles</h1>
        <p class="gel-page-subtitle">Confirmez la Réception physique d'articles commandés auprès d'un fournisseur.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="receiveItemForm">
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
            <label class="doc-label">Date de Réception *</label>
            <input type="date" name="receipt_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Bon de commande lié (Optionnel)</label>
            <select name="purchase_order_id" class="doc-input">
                <option value="">— Aucun —</option>
                <option value="1">PO-0012</option>
                <option value="2">PO-0015</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">NÂ° Bon de Livraison</label>
            <input type="text" name="delivery_note" class="doc-input" placeholder="Ex: BL-2026-112">
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table" id="linesTable">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:45%;">Article reçu</th>
                <th style="width:20%;">Emplacement (Dépôt)</th>
                <th style="width:15%;">Quantité reçue</th>
                <th style="width:10%;">Unité</th>
                <th style="width:5%;"></th>
            </tr>
        </thead>
        <tbody id="linesBody">
            <tr class="doc-line-row">
                <td class="line-num">1</td>
                <td><input type="text" name="lines[0][description]" class="doc-input-sm" placeholder="Nom de l'article..."></td>
                <td>
                    <select name="lines[0][location]" class="doc-input-sm">
                        <option value="principal">Entrepôt principal</option>
                        <option value="secondaire">Magasin secondaire</option>
                    </select>
                </td>
                <td><input type="number" name="lines[0][quantity]" class="doc-input-sm" value="1" min="0" step="0.01"></td>
                <td>
                    <select name="lines[0][unit]" class="doc-input-sm">
                        <option value="pcs">Pièces</option>
                        <option value="kg">Kg</option>
                        <option value="l">Litres</option>
                        <option value="m">Mètres</option>
                    </select>
                </td>
                <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
            </tr>
        </tbody>
    </table>
    <div style="padding:12px 16px; border-top:1px solid var(--gel-border);">
        <button type="button" class="gel-btn gel-btn-secondary" onclick="addLine()" style="font-size:13px;"><i class="fas fa-plus"></i> Ajouter un article</button>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 360px; gap:20px; margin-bottom:20px;">
    <div class="gel-card p-4 mb-4">
        <label class="doc-label">Notes de Réception</label>
        <textarea name="memo" class="doc-input" rows="3" placeholder="État de la marchandise, commentaires éventuels..."></textarea>
    </div>
    <div class="gel-card p-4 mb-4">
        <label class="doc-label">Joindre le bordereau</label>
        <div style="border:2px dashed var(--gel-border); border-radius:8px; padding:20px; text-align:center; cursor:pointer;" onclick="document.getElementById('fileInput').click()">
            <i class="fas fa-file-pdf" style="font-size:24px; color:var(--gel-text-muted); margin-bottom:8px;"></i>
            <p style="font-size:12px; color:var(--gel-text-secondary);">Ajouter le BL scanné</p>
            <input type="file" id="fileInput" name="attachment" style="display:none;" accept="image/*,.pdf">
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-check-circle"></i> Valider la Réception</button>
</div>
</form>


<script>
let lineIndex=1;
function addLine(){const t=document.getElementById('linesBody');const r=document.createElement('tr');r.className='doc-line-row';r.innerHTML=`<td class="line-num">${t.children.length+1}</td><td><input type="text" name="lines[${lineIndex}][description]" class="doc-input-sm" placeholder="Nom de l'article..."></td><td><select name="lines[${lineIndex}][location]" class="doc-input-sm"><option value="principal">Entrepôt principal</option><option value="secondaire">Magasin secondaire</option></select></td><td><input type="number" name="lines[${lineIndex}][quantity]" class="doc-input-sm" value="1" min="0" step="0.01"></td><td><select name="lines[${lineIndex}][unit]" class="doc-input-sm"><option value="pcs">Pièces</option><option value="kg">Kg</option><option value="l">Litres</option><option value="m">Mètres</option></select></td><td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;t.appendChild(r);lineIndex++;}
function removeLine(b){const r=b.closest('tr');if(document.querySelectorAll('.doc-line-row').length>1){r.remove();renumber();}}
function renumber(){document.querySelectorAll('.doc-line-row').forEach((r,i)=>r.querySelector('.line-num').textContent=i+1);}
</script>
@endsection

