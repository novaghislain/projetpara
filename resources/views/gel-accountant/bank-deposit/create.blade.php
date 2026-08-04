@extends('layouts.gel-accountant')

@section('title', 'Dépôt bancaire')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-university" style="color:var(--gel-primary); margin-right:8px;"></i> Dépôt bancaire</h1>
        <p class="gel-page-subtitle">Regroupez vos encaissements clients en un seul Dépôt sur votre compte bancaire.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="bankDepositForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Déposer dans le compte *</label>
            <select name="bank_account_id" class="doc-input" required>
                @foreach(\App\Models\BankAccount::where('client_id', auth()->user()->client_id ?? 0)->where('is_active', true)->get() as $ba)
                    <option value="{{ $ba->id }}">{{ $ba->bank_name }} — {{ $ba->account_number }}</option>
                @endforeach
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date du Dépôt *</label>
            <input type="date" name="deposit_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Mémo / Référence</label>
            <input type="text" name="memo" class="doc-input" placeholder="Ex: Dépôt Chèques semaine 28">
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table" id="linesTable">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:25%;">Reçu de / Client</th>
                <th style="width:20%;">Provenance</th>
                <th style="width:15%;">Référence du paiement</th>
                <th style="width:10%;">Méthode</th>
                <th style="width:15%;">Montant (FCFA)</th>
                <th style="width:5%;"></th>
            </tr>
        </thead>
        <tbody id="linesBody">
            <tr class="doc-line-row">
                <td class="line-num">1</td>
                <td><input type="text" name="lines[0][from]" class="doc-input-sm" placeholder="Nom du client..."></td>
                <td>
                    <select name="lines[0][source]" class="doc-input-sm">
                        <option value="paiement_client">Paiement client</option>
                        <option value="autre_encaissement">Autre encaissement</option>
                    </select>
                </td>
                <td><input type="text" name="lines[0][reference]" class="doc-input-sm" placeholder="NÂ° Chèque / réf."></td>
                <td>
                    <select name="lines[0][method]" class="doc-input-sm">
                        <option value="cheque">Chèque</option>
                        <option value="especes">Espèces</option>
                        <option value="virement">Virement</option>
                    </select>
                </td>
                <td><input type="number" name="lines[0][amount]" class="doc-input-sm line-amount" value="0" min="0" style="text-align:right;"></td>
                <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
            </tr>
        </tbody>
    </table>
    <div style="padding:12px 16px; border-top:1px solid var(--gel-border);">
        <button type="button" class="gel-btn gel-btn-secondary" onclick="addLine()" style="font-size:13px;"><i class="fas fa-plus"></i> Ajouter un encaissement</button>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; align-items:center; gap:20px; padding-bottom:30px;">
    <div style="font-size:16px;">Total du Dépôt : <strong id="totalDeposit" style="color:var(--gel-primary); font-size:20px;">0 FCFA</strong></div>
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer le Dépôt</button>
</div>
</form>


<script>
let lineIndex=1;
function addLine(){const t=document.getElementById('linesBody');const r=document.createElement('tr');r.className='doc-line-row';r.innerHTML=`<td class="line-num">${t.children.length+1}</td><td><input type="text" name="lines[${lineIndex}][from]" class="doc-input-sm" placeholder="Nom du client..."></td><td><select name="lines[${lineIndex}][source]" class="doc-input-sm"><option value="paiement_client">Paiement client</option><option value="autre_encaissement">Autre encaissement</option></select></td><td><input type="text" name="lines[${lineIndex}][reference]" class="doc-input-sm" placeholder="NÂ° Chèque / réf."></td><td><select name="lines[${lineIndex}][method]" class="doc-input-sm"><option value="cheque">Chèque</option><option value="especes">Espèces</option><option value="virement">Virement</option></select></td><td><input type="number" name="lines[${lineIndex}][amount]" class="doc-input-sm line-amount" value="0" min="0" style="text-align:right;"></td><td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;t.appendChild(r);lineIndex++;}
function removeLine(b){const r=b.closest('tr');if(document.querySelectorAll('.doc-line-row').length>1){r.remove();renumber();recalc();}}
function renumber(){document.querySelectorAll('.doc-line-row').forEach((r,i)=>r.querySelector('.line-num').textContent=i+1);}
function recalc(){let t=0;document.querySelectorAll('.line-amount').forEach(i=>t+=parseFloat(i.value||0));document.getElementById('totalDeposit').textContent=Math.round(t).toLocaleString('fr-FR')+' FCFA';}
document.getElementById('linesTable').addEventListener('input',recalc);
</script>
@endsection

