@extends('layouts.gel-accountant')

@section('title', 'Temps de révision')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-history" style="color:var(--gel-primary); margin-right:8px;"></i> Temps de révision</h1>
        <p class="gel-page-subtitle">Examinez, approuvez ou rejetez les feuilles de temps soumises par les collaborateurs.</p>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div style="display:flex; gap:16px; align-items:end;">
        <div class="doc-form-group" style="flex:1;">
            <label class="doc-label">Filtrer par collaborateur</label>
            <select class="doc-input" id="filterEmployee">
                <option value="">Tous les collaborateurs</option>
                <option value="{{ auth()->id() }}">{{ auth()->user()->name }}</option>
            </select>
        </div>
        <div class="doc-form-group" style="flex:1;">
            <label class="doc-label">Période</label>
            <select class="doc-input" id="filterPeriod">
                <option value="week">Cette semaine</option>
                <option value="month" selected>Ce mois</option>
                <option value="last_month">Mois dernier</option>
            </select>
        </div>
        <div class="doc-form-group" style="flex:1;">
            <label class="doc-label">Statut</label>
            <select class="doc-input" id="filterStatus">
                <option value="all">Tous</option>
                <option value="pending" selected>En attente d'approbation</option>
                <option value="approved">Approuvé</option>
                <option value="rejected">Rejeté</option>
            </select>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="overflow:hidden;">
    <table class="doc-lines-table">
        <thead>
            <tr>
                <th style="width:5%;"><input type="checkbox" id="selectAll"></th>
                <th style="width:20%;">Collaborateur</th>
                <th style="width:15%;">Semaine</th>
                <th style="width:12%;">Client</th>
                <th style="width:10%; text-align:right;">Heures</th>
                <th style="width:10%; text-align:right;">Montant</th>
                <th style="width:10%;">Statut</th>
                <th style="width:18%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="checkbox" class="ts-check"></td>
                <td style="font-weight:600;">{{ auth()->user()->name }}</td>
                <td>{{ date('d/m') }} — {{ date('d/m/Y', strtotime('+6 days')) }}</td>
                <td>Client A</td>
                <td style="text-align:right; font-weight:600;">32h</td>
                <td style="text-align:right;">800 000 FCFA</td>
                <td><span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#FEF3C7; color:#92400E;">En attente</span></td>
                <td>
                    <button class="gel-btn gel-btn-primary" style="font-size:11px; padding:4px 10px;"><i class="fas fa-check"></i> Approuver</button>
                    <button class="gel-btn gel-btn-secondary" style="font-size:11px; padding:4px 10px;"><i class="fas fa-times"></i></button>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" class="ts-check"></td>
                <td style="font-weight:600;">{{ auth()->user()->name }}</td>
                <td>{{ date('d/m', strtotime('-7 days')) }} — {{ date('d/m/Y', strtotime('-1 day')) }}</td>
                <td>Client B</td>
                <td style="text-align:right; font-weight:600;">40h</td>
                <td style="text-align:right;">1 000 000 FCFA</td>
                <td><span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#D1FAE5; color:#065F46;">Approuvé</span></td>
                <td>
                    <button class="gel-btn gel-btn-secondary" style="font-size:11px; padding:4px 10px;"><i class="fas fa-eye"></i> Voir</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; padding:20px 0 30px;">
    <div style="font-size:13px; color:var(--gel-text-secondary);">2 Entrées trouvées</div>
    <div style="display:flex; gap:8px;">
        <button class="gel-btn gel-btn-primary" style="font-size:12px;" id="bulkApprove" disabled><i class="fas fa-check-double"></i> Approuver la sélection</button>
    </div>
</div>


<script>
document.getElementById('selectAll').addEventListener('change',function(){
    document.querySelectorAll('.ts-check').forEach(c=>c.checked=this.checked);
    document.getElementById('bulkApprove').disabled=!this.checked;
});
document.querySelectorAll('.ts-check').forEach(c=>c.addEventListener('change',function(){
    document.getElementById('bulkApprove').disabled=!document.querySelector('.ts-check:checked');
}));
</script>
@endsection

