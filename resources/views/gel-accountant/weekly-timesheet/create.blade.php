@extends('layouts.gel-accountant')

@section('title', 'Feuille de temps hebdomadaire')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-calendar-alt" style="color:var(--gel-primary); margin-right:8px;"></i> Feuille de temps hebdomadaire</h1>
        <p class="gel-page-subtitle">Saisissez les heures travaillées par collaborateur pour la semaine en cours.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button type="button" class="gel-btn gel-btn-secondary" onclick="prevWeek()"><i class="fas fa-chevron-left"></i></button>
        <span class="gel-btn gel-btn-secondary" id="weekLabel" style="min-width:200px; text-align:center; cursor:default;"></span>
        <button type="button" class="gel-btn gel-btn-secondary" onclick="nextWeek()"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<form method="POST" action="#" id="timesheetForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div style="display:flex; gap:16px; align-items:center;">
        <div class="doc-form-group" style="flex:1;">
            <label class="doc-label">Collaborateur</label>
            <select name="employee_id" class="doc-input">
                <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Moi-même)</option>
            </select>
        </div>
        <div class="doc-form-group" style="flex:1;">
            <label class="doc-label">Client</label>
            <select name="partner_id" class="doc-input">
                <option value="">— Tous les clients —</option>
                @foreach(\App\Models\Partner::where('type', 'client')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name ?: $c->first_name.' '.$c->last_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow-x:auto;">
    <table class="ts-table" id="timesheetTable">
        <thead>
            <tr>
                <th style="width:25%; min-width:180px;">Service / Activité</th>
                <th class="day-col" id="d0">Lun</th>
                <th class="day-col" id="d1">Mar</th>
                <th class="day-col" id="d2">Mer</th>
                <th class="day-col" id="d3">Jeu</th>
                <th class="day-col" id="d4">Ven</th>
                <th class="day-col" id="d5">Sam</th>
                <th class="day-col" id="d6">Dim</th>
                <th class="total-col">Total</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody id="tsBody">
            <tr class="ts-row">
                <td>
                    <select name="rows[0][service]" class="doc-input-sm">
                        <option value="comptabilite">Saisie comptable</option>
                        <option value="consultation">Consultation</option>
                        <option value="audit">Audit / révision</option>
                        <option value="fiscal">Déclaration fiscale</option>
                        <option value="formation">Formation</option>
                        <option value="autre">Autre</option>
                    </select>
                </td>
                <td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td>
                <td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td>
                <td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td>
                <td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td>
                <td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td>
                <td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td>
                <td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td>
                <td class="row-total" style="font-weight:700; text-align:center;">0h</td>
                <td><button type="button" class="doc-line-remove" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background:var(--gel-primary-light);">
                <td style="font-weight:700; padding:12px;">Total journalier</td>
                <td class="day-total" id="dt0" style="font-weight:700; text-align:center;">0h</td>
                <td class="day-total" id="dt1" style="font-weight:700; text-align:center;">0h</td>
                <td class="day-total" id="dt2" style="font-weight:700; text-align:center;">0h</td>
                <td class="day-total" id="dt3" style="font-weight:700; text-align:center;">0h</td>
                <td class="day-total" id="dt4" style="font-weight:700; text-align:center;">0h</td>
                <td class="day-total" id="dt5" style="font-weight:700; text-align:center;">0h</td>
                <td class="day-total" id="dt6" style="font-weight:700; text-align:center;">0h</td>
                <td id="weekTotal" style="font-weight:700; text-align:center; color:var(--gel-primary); font-size:16px;">0h</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <div style="padding:12px 16px; border-top:1px solid var(--gel-border);">
        <button type="button" class="gel-btn gel-btn-secondary" onclick="addRow()" style="font-size:13px;"><i class="fas fa-plus"></i> Ajouter une ligne</button>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" name="action" value="draft" class="gel-btn gel-btn-secondary"><i class="fas fa-save"></i> Brouillon</button>
    <button type="submit" name="action" value="submit" class="gel-btn gel-btn-primary"><i class="fas fa-paper-plane"></i> Soumettre pour approbation</button>
</div>
</form>


<script>
let rowIndex=1;
let weekOffset=0;

function getWeekDates(offset){
    const now=new Date();const d=now.getDate()-now.getDay()+1+(offset*7);
    const mon=new Date(now.setDate(d));const days=[];
    const dayNames=['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
    for(let i=0;i<7;i++){const dd=new Date(mon);dd.setDate(mon.getDate()+i);days.push({name:dayNames[i],date:dd});}
    return days;
}

function renderWeek(){
    const days=getWeekDates(weekOffset);
    days.forEach((d,i)=>{document.getElementById('d'+i).innerHTML=d.name+'<br><span style="font-size:10px;font-weight:400;">'+d.date.toLocaleDateString('fr-FR',{day:'2-digit',month:'short'})+'</span>';});
    const first=days[0].date.toLocaleDateString('fr-FR',{day:'2-digit',month:'short'});
    const last=days[6].date.toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'});
    document.getElementById('weekLabel').textContent='Semaine du '+first+' au '+last;
}

function prevWeek(){weekOffset--;renderWeek();}
function nextWeek(){weekOffset++;renderWeek();}

function addRow(){
    const t=document.getElementById('tsBody');const r=document.createElement('tr');r.className='ts-row';
    r.innerHTML=`<td><select name="rows[${rowIndex}][service]" class="doc-input-sm"><option value="comptabilite">Saisie comptable</option><option value="consultation">Consultation</option><option value="audit">Audit / révision</option><option value="fiscal">Déclaration fiscale</option><option value="formation">Formation</option><option value="autre">Autre</option></select></td><td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td><td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td><td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td><td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td><td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td><td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td><td><input type="number" class="ts-input ts-hour" value="0" min="0" max="24" step="0.25"></td><td class="row-total" style="font-weight:700; text-align:center;">0h</td><td><button type="button" class="doc-line-remove" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>`;
    t.appendChild(r);rowIndex++;
}

function removeRow(b){const r=b.closest('tr');if(document.querySelectorAll('.ts-row').length>1){r.remove();recalc();}}

function recalc(){
    const rows=document.querySelectorAll('.ts-row');
    const dayTotals=[0,0,0,0,0,0,0];let weekTotal=0;
    rows.forEach(r=>{
        const inputs=r.querySelectorAll('.ts-hour');let rowT=0;
        inputs.forEach((inp,i)=>{const v=parseFloat(inp.value||0);dayTotals[i]+=v;rowT+=v;});
        r.querySelector('.row-total').textContent=rowT+'h';weekTotal+=rowT;
    });
    dayTotals.forEach((t,i)=>document.getElementById('dt'+i).textContent=t+'h');
    document.getElementById('weekTotal').textContent=weekTotal+'h';
}

document.getElementById('timesheetTable').addEventListener('input',recalc);
renderWeek();
</script>
@endsection

