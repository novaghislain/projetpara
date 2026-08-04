@extends('layouts.gel-accountant')

@section('title', 'Entrée de journal (OD)')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-book" style="color:var(--gel-primary); margin-right:8px;"></i> Entrée de journal (OD)</h1>
        <p class="gel-page-subtitle">Saisissez une écriture comptable manuelle (opérations diverses). Le total des débits doit égaler le total des Crédits.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="journalEntryForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Journal *</label>
            <select name="journal_id" class="doc-input" required>
                <option value="od">OD — Opérations Diverses</option>
                <option value="an">AN — À Nouveau</option>
                <option value="ext">EXT — Extourne</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date de l'écriture *</label>
            <input type="date" name="entry_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">NÂ° de pièce</label>
            <input type="text" name="reference" class="doc-input" value="OD-{{ date('Ymd') }}-{{ str_pad(rand(1,999), 3, '0', STR_PAD_LEFT) }}" readonly>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table" id="linesTable">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:20%;">Compte (NÂ° SYSCOHADA)</th>
                <th style="width:35%;">Libellé de l'écriture</th>
                <th style="width:15%;">Débit (FCFA)</th>
                <th style="width:15%;">Crédit (FCFA)</th>
                <th style="width:5%;"></th>
            </tr>
        </thead>
        <tbody id="linesBody">
            <tr class="doc-line-row">
                <td class="line-num">1</td>
                <td>
                    <select name="lines[0][account_id]" class="doc-input-sm">
                        <option value="">Compte...</option>
                        @foreach(\App\Models\AccountingAccount::where('client_id', auth()->user()->client_id ?? 0)->where('allow_journal_entry', true)->orderBy('code')->get() as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="lines[0][label]" class="doc-input-sm" placeholder="Libellé..."></td>
                <td><input type="number" name="lines[0][debit]" class="doc-input-sm line-debit" value="0" min="0" style="text-align:right;"></td>
                <td><input type="number" name="lines[0][credit]" class="doc-input-sm line-credit" value="0" min="0" style="text-align:right;"></td>
                <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
            </tr>
            <tr class="doc-line-row">
                <td class="line-num">2</td>
                <td>
                    <select name="lines[1][account_id]" class="doc-input-sm">
                        <option value="">Compte...</option>
                        @foreach(\App\Models\AccountingAccount::where('client_id', auth()->user()->client_id ?? 0)->where('allow_journal_entry', true)->orderBy('code')->get() as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="lines[1][label]" class="doc-input-sm" placeholder="Libellé..."></td>
                <td><input type="number" name="lines[1][debit]" class="doc-input-sm line-debit" value="0" min="0" style="text-align:right;"></td>
                <td><input type="number" name="lines[1][credit]" class="doc-input-sm line-credit" value="0" min="0" style="text-align:right;"></td>
                <td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background:var(--gel-primary-light);">
                <td colspan="3" style="font-weight:700; padding:12px; text-align:right;">Totaux</td>
                <td id="totalDebit" style="font-weight:700; text-align:right; padding:12px;">0 FCFA</td>
                <td id="totalCredit" style="font-weight:700; text-align:right; padding:12px;">0 FCFA</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <div style="padding:12px 16px; border-top:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center;">
        <button type="button" class="gel-btn gel-btn-secondary" onclick="addLine()" style="font-size:13px;"><i class="fas fa-plus"></i> Ajouter une ligne</button>
        <div id="balanceStatus" style="font-size:13px; font-weight:600;"></div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <label class="doc-label">Mémo / Justificatif</label>
    <textarea name="memo" class="doc-input" rows="2" placeholder="Motif de l'écriture..."></textarea>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary" id="btnSubmit"><i class="fas fa-save"></i> Enregistrer l'écriture</button>
</div>
</form>


<script>
let lineIndex=2;
const accountOptions=document.querySelector('select[name="lines[0][account_id]"]').innerHTML;

function addLine(){const t=document.getElementById('linesBody');const r=document.createElement('tr');r.className='doc-line-row';r.innerHTML=`<td class="line-num">${t.children.length+1}</td><td><select name="lines[${lineIndex}][account_id]" class="doc-input-sm">${accountOptions}</select></td><td><input type="text" name="lines[${lineIndex}][label]" class="doc-input-sm" placeholder="Libellé..."></td><td><input type="number" name="lines[${lineIndex}][debit]" class="doc-input-sm line-debit" value="0" min="0" style="text-align:right;"></td><td><input type="number" name="lines[${lineIndex}][credit]" class="doc-input-sm line-credit" value="0" min="0" style="text-align:right;"></td><td><button type="button" class="doc-line-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>`;t.appendChild(r);lineIndex++;}

function removeLine(b){const r=b.closest('tr');if(document.querySelectorAll('.doc-line-row').length>2){r.remove();renumber();recalc();}}
function renumber(){document.querySelectorAll('.doc-line-row').forEach((r,i)=>r.querySelector('.line-num').textContent=i+1);}

function recalc(){
    let d=0,c=0;
    document.querySelectorAll('.doc-line-row').forEach(r=>{
        d+=parseFloat(r.querySelector('.line-debit')?.value||0);
        c+=parseFloat(r.querySelector('.line-credit')?.value||0);
    });
    document.getElementById('totalDebit').textContent=Math.round(d).toLocaleString('fr-FR')+' FCFA';
    document.getElementById('totalCredit').textContent=Math.round(c).toLocaleString('fr-FR')+' FCFA';
    const bs=document.getElementById('balanceStatus');
    const btn=document.getElementById('btnSubmit');
    if(d===0&&c===0){bs.textContent='';bs.style.color='';btn.disabled=false;}
    else if(Math.abs(d-c)<0.01){bs.textContent='âœ“ Écriture équilibrée';bs.style.color='var(--gel-success)';btn.disabled=false;}
    else{bs.textContent='âœ— Écart de '+Math.abs(Math.round(d-c)).toLocaleString('fr-FR')+' FCFA';bs.style.color='var(--gel-danger)';btn.disabled=true;}
}
document.getElementById('linesTable').addEventListener('input',recalc);
</script>
@endsection

