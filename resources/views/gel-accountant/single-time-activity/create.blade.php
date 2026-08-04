@extends('layouts.gel-accountant')

@section('title', 'Activité À  durée unique')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-stopwatch" style="color:var(--gel-primary); margin-right:8px;"></i> Activité À  durée unique</h1>
        <p class="gel-page-subtitle">Saisissez une Activité ponctuelle facturable ou non-facturable pour un employé.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="singleTimeForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Employé / Collaborateur *</label>
            <select name="employee_id" class="doc-input" required>
                <option value="">— Sélectionner —</option>
                <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Moi-même)</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date de l'Activité *</label>
            <input type="date" name="activity_date" class="doc-input" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Client associé</label>
            <select name="partner_id" class="doc-input">
                <option value="">— Aucun client —</option>
                @foreach(\App\Models\Partner::where('type', 'client')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name ?: $c->first_name.' '.$c->last_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-clock" style="color:var(--gel-primary);"></i> Détails du temps</h3>
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Type de service *</label>
            <select name="service_type" class="doc-input" required>
                <option value="consultation">Consultation</option>
                <option value="comptabilite">Saisie comptable</option>
                <option value="audit">Audit / révision</option>
                <option value="fiscal">Déclaration fiscale</option>
                <option value="juridique">Conseil juridique</option>
                <option value="formation">Formation</option>
                <option value="autre">Autre</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Heure de début</label>
            <input type="time" name="start_time" class="doc-input" id="startTime">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Heure de fin</label>
            <input type="time" name="end_time" class="doc-input" id="endTime">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">durée (heures)</label>
            <input type="number" name="duration_hours" class="doc-input" id="durationHours" value="1" min="0.25" step="0.25">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Taux horaire (FCFA/h)</label>
            <input type="number" name="hourly_rate" class="doc-input" id="hourlyRate" value="25000" min="0">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Facturable ?</label>
            <select name="billable" class="doc-input" id="billable">
                <option value="1" selected>Oui — Facturable</option>
                <option value="0">Non — Non-facturable</option>
            </select>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 360px; gap:20px; margin-bottom:20px;">
    <div class="gel-card p-4 mb-4">
        <label class="doc-label">Description de l'Activité *</label>
        <textarea name="description" class="doc-input" rows="4" required placeholder="Ex: révision des écritures comptables du mois de juin, rapprochement bancaire..."></textarea>
    </div>
    <div class="gel-card p-4 mb-4">
        <div style="text-align:center; padding:16px 0;">
            <p style="font-size:12px; color:var(--gel-text-secondary); margin-bottom:4px;">Montant estimé</p>
            <p id="estimatedAmount" style="font-size:28px; font-weight:700; color:var(--gel-primary);">25 000 FCFA</p>
            <p id="billableTag" style="font-size:11px; padding:3px 10px; background:var(--gel-success); color:white; border-radius:20px; display:inline-block; margin-top:8px;">FACTURABLE</p>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer l'Activité</button>
</div>
</form>


<script>
function calcAmount(){
    const h=parseFloat(document.getElementById('durationHours').value||0);
    const r=parseFloat(document.getElementById('hourlyRate').value||0);
    const b=document.getElementById('billable').value;
    document.getElementById('estimatedAmount').textContent=Math.round(h*r).toLocaleString('fr-FR')+' FCFA';
    const tag=document.getElementById('billableTag');
    if(b==='1'){tag.textContent='FACTURABLE';tag.style.background='var(--gel-success)';}
    else{tag.textContent='NON-FACTURABLE';tag.style.background='var(--gel-text-muted)';}
}
function calcDuration(){
    const s=document.getElementById('startTime').value;
    const e=document.getElementById('endTime').value;
    if(s&&e){const[sh,sm]=s.split(':').map(Number);const[eh,em]=e.split(':').map(Number);let d=(eh*60+em-sh*60-sm)/60;if(d<0)d+=24;document.getElementById('durationHours').value=Math.round(d*4)/4;calcAmount();}
}
document.getElementById('startTime').addEventListener('change',calcDuration);
document.getElementById('endTime').addEventListener('change',calcDuration);
document.getElementById('durationHours').addEventListener('input',calcAmount);
document.getElementById('hourlyRate').addEventListener('input',calcAmount);
document.getElementById('billable').addEventListener('change',calcAmount);
</script>
@endsection

