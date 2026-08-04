@extends('layouts.gel-accountant')

@section('title', 'Créer une Tâche')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-tasks" style="color:var(--gel-primary); margin-right:8px;"></i> Créer une Tâche</h1>
        <p class="gel-page-subtitle">Attribuez une nouvelle Tâche À  un membre de votre équipe ou À  vous-même.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="taskForm">
@csrf

<div class="gel-card p-4 mb-4">
    <div class="doc-form-grid" style="grid-template-columns:1fr;">
        <div class="doc-form-group">
            <label class="doc-label">Titre de la Tâche *</label>
            <input type="text" name="title" class="doc-input" required placeholder="Ex: révision clôture annuelle Client ABC" style="font-size:16px; font-weight:600;">
        </div>
    </div>
    <div class="doc-form-grid" style="margin-top:16px;">
        <div class="doc-form-group">
            <label class="doc-label">Assigné À  *</label>
            <select name="assignee_id" class="doc-input" required>
                <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Moi-même)</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Lié au client</label>
            <select name="partner_id" class="doc-input">
                <option value="">— Aucun client spécifique —</option>
                @foreach(\App\Models\Partner::where('type', 'client')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name ?: $c->first_name.' '.$c->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date limite *</label>
            <input type="date" name="due_date" class="doc-input" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);">Détails de la Tâche</h3>
    <div class="doc-form-grid" style="grid-template-columns:1fr;">
        <div class="doc-form-group">
            <label class="doc-label">Priorité</label>
            <div style="display:flex; gap:16px; margin-top:4px;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="radio" name="priority" value="low"> <span style="padding:2px 8px; border-radius:4px; background:#f3f4f6; font-size:12px;">Basse</span>
                </label>
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="radio" name="priority" value="medium" checked> <span style="padding:2px 8px; border-radius:4px; background:#FEF3C7; color:#92400E; font-size:12px;">Moyenne</span>
                </label>
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="radio" name="priority" value="high"> <span style="padding:2px 8px; border-radius:4px; background:#FEE2E2; color:#B91C1C; font-size:12px;">Haute</span>
                </label>
            </div>
        </div>
        <div class="doc-form-group" style="margin-top:16px;">
            <label class="doc-label">Description / Instructions</label>
            <textarea name="description" class="doc-input" rows="5" placeholder="Décrivez la Tâche en détail..."></textarea>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Créer la Tâche</button>
</div>
</form>


@endsection

