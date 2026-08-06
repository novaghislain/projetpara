@php $currentSection = 'tasks'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Tâches - GEL Accountant')
@section('content')
<div class="gel-page-header animate-fade" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 class="gel-page-title" style="font-size: 18px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">Tâches</h1>
        <p class="gel-page-subtitle" style="font-size: 12px; color: var(--gel-text-secondary);">Suivi des tâches et échéances</p>
    </div>
    <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="openPanel('taskPanel')" style="font-weight:600;display:flex;align-items:center;gap:6px;border-radius:6px;padding:8px 14px;">
        <i class="fas fa-plus"></i> Nouvelle tâche
    </button>
</div>
<div class="gel-kpi-grid animate-fade delay-1">
    <div class="gel-kpi-card"><div class="gel-kpi-label">À faire</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">En cours</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Terminées</div><div class="gel-kpi-value">0</div></div>
    <div class="gel-kpi-card"><div class="gel-kpi-label">Échues</div><div class="gel-kpi-value">0</div></div>
</div>
<div class="pro-panel animate-fade delay-1 mb-4" style="margin-top:20px;">
    <div class="panel-body p-4">
        <div class="gel-unicoord-head" style="display:flex; align-items:center; gap:8px; font-weight:700; color:var(--gel-text-primary); margin-bottom:12px;">
            <i class="fas fa-people-arrows" style="color:#0D9488;"></i> Notes de coordination du secrétariat
        </div>
        @php
            $coordTasks = collect($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator ? $tasks->items() : $tasks)
                ->where('source', 'coordination');
            $otherTasks = collect($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator ? $tasks->items() : $tasks)
                ->where('source', '!=', 'coordination');
        @endphp
        @if($coordTasks->count() > 0)
            @foreach($coordTasks as $t)
                <div style="display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-bottom:1px solid var(--gel-border);">
                    <div style="width:34px;height:34px;border-radius:8px;background:#CCFBF1;color:#0F766E;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-people-arrows"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:600; font-size:13px; color:var(--gel-text-primary);">{{ $t->titre }}</div>
                        @if($t->description)<div style="font-size:12px; color:var(--gel-text-secondary);">{{ $t->description }}</div>@endif
                        <div style="font-size:11px; color:#94A3B8; margin-top:2px;">
                            {{ optional($t->date_echeance)->format('d/m/Y') ?: 'sans échéance' }}
                            · <span style="font-weight:700; text-transform:uppercase; font-size:10px; color:{{ $t->priorite==='critique' ? '#DC2626' : ($t->priorite==='haute' ? '#D97706' : '#0D9488') }};">{{ $t->priorite }}</span>
                        </div>
                    </div>
                    <span class="gel-badge gel-badge-{{ $t->statut === 'a_faire' ? 'warning' : 'success' }}" style="font-size:10px;">{{ $t->statut }}</span>
                </div>
            @endforeach
        @else
            <div class="gel-empty">
                <i class="fas fa-tasks"></i>
                <h3>Aucune tâche de coordination</h3>
                <p>Les notes et demandes transmises par la secrétaire apparaîtront ici.</p>
            </div>
        @endif

        @if($otherTasks->count() > 0)
            <div style="font-weight:700; font-size:12px; color:var(--gel-text-primary); margin:16px 0 8px;">Autres tâches</div>
            @foreach($otherTasks as $t)
                <div style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid var(--gel-border);">
                    <i class="fas fa-tasks" style="color:#64748B;"></i>
                    <div style="flex:1; font-size:13px; color:var(--gel-text-primary);">{{ $t->titre }}</div>
                    <span class="background:#F1F5F9; padding:2px 8px; border-radius:6px; font-size:10px;">{{ $t->statut }}</span>
                </div>
            @endforeach
        @endif
    </div>
</div>
<div class="gel-panel-overlay" id="taskPanelOverlay" onclick="closePanel('taskPanel')"></div>
<div class="gel-panel" id="taskPanel">
    <div class="gel-panel-header"><span class="gel-panel-title">Nouvelle tâche</span><button class="gel-panel-close" onclick="closePanel('taskPanel')">&times;</button></div>
    <div class="gel-panel-body">
        <form>
            <div class="gel-form-group"><label>Titre</label><input class="gel-form-control" placeholder="Clôture mensuelle"></div>
            <div class="gel-form-group"><label>Description</label><textarea class="gel-form-control" rows="3"></textarea></div>
            <div class="gel-form-group"><label>Assigné à</label><select class="gel-form-select"></select></div>
            <div class="gel-form-group"><label>Priorité</label>
                <select class="gel-form-select"><option>Basse</option><option selected>Moyenne</option><option>Haute</option><option>Critique</option></select>
            </div>
            <div class="gel-form-group"><label>Date d'échéance</label><input class="gel-form-control" type="date"></div>
        </form>
    </div>
    <div class="gel-panel-footer">
        <button class="gel-btn gel-btn-secondary" onclick="closePanel('taskPanel')">Annuler</button>
        <button class="gel-btn gel-btn-primary">Créer</button>
    </div>
</div>
@endsection
