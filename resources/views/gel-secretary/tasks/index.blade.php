@extends('layouts.gel-secretary')
@section('title', 'Tâches')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title"><i class="fas fa-tasks" style="color:var(--sec-primary); margin-right:8px;"></i>Gestion des Tâches</h1>
    <p class="sec-page-sub">Organisation et suivi des tâches du secrétariat.</p>
  </div>
  <div>
    <button type="button" class="sec-btn sec-btn-primary" onclick="document.getElementById('modal-task').style.display='flex'">
      <i class="fas fa-plus"></i> Nouvelle Tâche
    </button>
  </div>
</div>

<div style="display:flex; gap:20px; flex-wrap:nowrap; overflow-x:auto; padding-bottom:20px;">
  
  <!-- Colonne : À Faire -->
  <div style="flex:1; min-width:300px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:15px; padding:0 10px;">
      <h3 style="margin:0; font-size:15px; font-weight:700; color:var(--sec-text);">À Faire</h3>
      <span style="background:#E2E8F0; color:#475569; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:700;">{{ $aFaire->count() }}</span>
    </div>
    
    <div style="display:flex; flex-direction:column; gap:15px;">
      @forelse($aFaire as $task)
        @include('gel-secretary.tasks._task_card', ['task' => $task])
      @empty
        <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; padding:20px; text-align:center; color:#94a3b8; font-size:13px;">
          Aucune tâche à faire.
        </div>
      @endforelse
    </div>
  </div>

  <!-- Colonne : En Cours -->
  <div style="flex:1; min-width:300px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:15px; padding:0 10px;">
      <h3 style="margin:0; font-size:15px; font-weight:700; color:var(--sec-text);">En Cours</h3>
      <span style="background:#DBEAFE; color:#1D4ED8; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:700;">{{ $enCours->count() }}</span>
    </div>
    
    <div style="display:flex; flex-direction:column; gap:15px;">
      @forelse($enCours as $task)
        @include('gel-secretary.tasks._task_card', ['task' => $task])
      @empty
        <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; padding:20px; text-align:center; color:#94a3b8; font-size:13px;">
          Aucune tâche en cours.
        </div>
      @endforelse
    </div>
  </div>

  <!-- Colonne : Terminées -->
  <div style="flex:1; min-width:300px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:15px; padding:0 10px;">
      <h3 style="margin:0; font-size:15px; font-weight:700; color:var(--sec-text);">Terminées</h3>
      <span style="background:#DCFCE7; color:#15803D; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:700;">{{ $terminees->count() }}</span>
    </div>
    
    <div style="display:flex; flex-direction:column; gap:15px;">
      @forelse($terminees as $task)
        @include('gel-secretary.tasks._task_card', ['task' => $task])
      @empty
        <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; padding:20px; text-align:center; color:#94a3b8; font-size:13px;">
          Aucune tâche terminée.
        </div>
      @endforelse
    </div>
  </div>

</div>

<!-- Modal Nouvelle Tâche -->
<div id="modal-task" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
  <div class="sec-card" style="width:100%; max-width:500px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
    <div class="sec-card-header" style="display:flex; justify-content:space-between; align-items:center; background:#F8FAFC;">
      <h3 style="margin:0; font-size:16px; font-weight:700;">Nouvelle Tâche</h3>
      <button onclick="document.getElementById('modal-task').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:#94a3b8;"><i class="fas fa-times"></i></button>
    </div>
    <div class="sec-card-body" style="padding:25px;">
      <form action="{{ route('gel-secretary.tasks.store') }}" method="POST">
        @csrf
        <input type="hidden" name="client_id" value="{{ request('client_id') }}">
        
        <div style="margin-bottom:15px;">
          <label class="sec-label">Titre de la tâche *</label>
          <input type="text" name="titre" class="sec-input" required placeholder="Que faut-il faire ?">
        </div>
        
        <div style="margin-bottom:15px;">
          <label class="sec-label">Description (Optionnel)</label>
          <textarea name="description" class="sec-input" rows="3" placeholder="Détails, consignes..."></textarea>
        </div>

        <div style="display:flex; gap:15px; margin-bottom:15px;">
          <div style="flex:1;">
            <label class="sec-label">Priorité</label>
            <select name="priorite" class="sec-input" required>
              <option value="basse">Basse</option>
              <option value="normale" selected>Normale</option>
              <option value="haute">Haute</option>
              <option value="urgente">Urgente</option>
            </select>
          </div>
          <div style="flex:1;">
            <label class="sec-label">Échéance</label>
            <input type="date" name="echeance" class="sec-input">
          </div>
        </div>
        
        <div style="margin-bottom:20px;">
          <label class="sec-label">Assigner à</label>
          <select name="assigned_to" class="sec-input">
            <option value="">(Moi-même)</option>
            @foreach(\App\Models\User::whereNotNull('cabinet_id')->get() as $user)
              <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
            @endforeach
          </select>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="sec-btn sec-btn-secondary" onclick="document.getElementById('modal-task').style.display='none'">Annuler</button>
          <button type="submit" class="sec-btn sec-btn-primary">Créer la tâche</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
