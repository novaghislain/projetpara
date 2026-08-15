<div class="sec-card" style="margin-bottom:0; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
  <div class="sec-card-body" style="padding:15px;">
    
    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
      @if($task->priorite == 'basse')
        <span style="font-size:11px; font-weight:700; color:#64748B; background:#F1F5F9; padding:2px 6px; border-radius:4px;">Basse</span>
      @elseif($task->priorite == 'normale')
        <span style="font-size:11px; font-weight:700; color:#3B82F6; background:#EFF6FF; padding:2px 6px; border-radius:4px;">Normale</span>
      @elseif($task->priorite == 'haute')
        <span style="font-size:11px; font-weight:700; color:#F59E0B; background:#FEF3C7; padding:2px 6px; border-radius:4px;">Haute</span>
      @elseif($task->priorite == 'urgente')
        <span style="font-size:11px; font-weight:700; color:#EF4444; background:#FEF2F2; padding:2px 6px; border-radius:4px;">Urgente</span>
      @endif

      <form action="{{ route('gel-secretary.tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Supprimer cette tâche ?')">
        @csrf
        @method('DELETE')
        <input type="hidden" name="client_id" value="{{ request('client_id') }}">
        <button type="submit" style="background:none; border:none; cursor:pointer; color:#94a3b8;"><i class="fas fa-trash-alt"></i></button>
      </form>
    </div>

    <div style="font-weight:600; font-size:14px; color:var(--sec-text); margin-bottom:8px;">
      {{ $task->titre }}
    </div>

    @if($task->description)
      <div style="font-size:12px; color:var(--sec-text-muted); margin-bottom:12px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
        {{ $task->description }}
      </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--sec-border); padding-top:10px; margin-top:10px;">
      
      <div style="display:flex; gap:10px; align-items:center; font-size:12px; color:var(--sec-text-muted);">
        @if($task->echeance)
          <span style="{{ $task->echeance < now() && $task->statut != 'terminee' ? 'color:#EF4444;' : '' }}">
            <i class="far fa-calendar-alt"></i> {{ $task->echeance->format('d/m') }}
          </span>
        @endif
        
        @if($task->assigned_to)
          <span title="{{ $task->assignedTo->name ?? '' }}">
            <i class="far fa-user"></i> {{ substr($task->assignedTo->name ?? '?', 0, 10) }}
          </span>
        @endif
      </div>

      <!-- Actions Rapides -->
      @if($task->statut == 'a_faire')
        <form action="{{ route('gel-secretary.tasks.change-status', ['id' => $task->id, 'status' => 'en_cours']) }}" method="POST">
          @csrf
          <input type="hidden" name="client_id" value="{{ request('client_id') }}">
          <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#DBEAFE; color:#1D4ED8;">Commencer</button>
        </form>
      @elseif($task->statut == 'en_cours')
        <form action="{{ route('gel-secretary.tasks.change-status', ['id' => $task->id, 'status' => 'terminee']) }}" method="POST">
          @csrf
          <input type="hidden" name="client_id" value="{{ request('client_id') }}">
          <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#DCFCE7; color:#15803D;">Terminer</button>
        </form>
      @elseif($task->statut == 'terminee')
        <form action="{{ route('gel-secretary.tasks.change-status', ['id' => $task->id, 'status' => 'a_faire']) }}" method="POST">
          @csrf
          <input type="hidden" name="client_id" value="{{ request('client_id') }}">
          <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#F1F5F9; color:#475569;"><i class="fas fa-undo"></i></button>
        </form>
      @endif
    </div>
  </div>
</div>
