@extends('layouts.gel-secretary')
@section('title', 'Tâches — Secrétariat')
@section('content')

<style>
    /* KANBAN CSS */
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        align-items: start;
        margin-top: 16px;
    }
    .kanban-col {
        background: #F8FAFC;
        border-radius: 12px;
        padding: 16px;
        min-height: 500px;
        border: 1px solid #E2E8F0;
    }
    .kanban-col-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        font-weight: 700;
        font-size: 14px;
        color: var(--sec-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kanban-badge {
        background: #E2E8F0;
        color: #475569;
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 12px;
    }
    .kanban-card {
        background: white;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #E2E8F0;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .kanban-card-title {
        font-weight: 600;
        font-size: 14px;
        color: var(--sec-text);
        line-height: 1.4;
    }
    .kanban-card-meta {
        font-size: 12px;
        color: var(--sec-text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .k-btn {
        background: none; border: 1px solid #E2E8F0;
        border-radius: 6px; padding: 6px 12px; font-size: 11px; font-weight: 600;
        cursor: pointer; color: var(--sec-text-muted); transition: 0.2s;
        display: flex; align-items: center; gap: 4px;
    }
    .k-btn:hover { background: #F1F5F9; color: var(--sec-text); }
    
    /* PRIORITIES */
    .prio-basse { background: #F1F5F9; color: #64748B; }
    .prio-moyenne { background: #EFF6FF; color: #3B82F6; }
    .prio-haute { background: #FFF7ED; color: #F97316; }
    .prio-critique { background: #FEF2F2; color: #EF4444; }
    
    .text-overdue { color: #DC2626 !important; font-weight: 700; }
</style>

<div class="sec-page-header">
    <div>
        <h1 class="sec-page-title"><i class="fas fa-tasks" style="color:var(--sec-primary); margin-right:8px;"></i>Kanban des Tâches</h1>
        <p class="sec-page-sub">Gérez visuellement le flux de travail du secrétariat</p>
    </div>
    <button class="sec-btn sec-btn-primary" onclick="document.getElementById('taskModal').style.display='flex'">
        <i class="fas fa-plus"></i> Nouvelle Tâche
    </button>
</div>

<!-- KANBAN BOARD -->
<div class="kanban-board">
    
    <!-- COLONNE : A FAIRE -->
    <div class="kanban-col" data-status="a_faire">
        <div class="kanban-col-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:10px;height:10px;border-radius:50%;background:#94A3B8;"></div>
                À FAIRE
            </div>
            <span class="kanban-badge">{{ $tasksTodo->count() }}</span>
        </div>
        
        <div class="kanban-list">
            @foreach($tasksTodo as $task)
                @include('gel-secretary.tasks.partials.kanban-card', ['task' => $task, 'col' => 'todo'])
            @endforeach
        </div>
    </div>

    <!-- COLONNE : EN COURS -->
    <div class="kanban-col" style="background: #F0FDF4; border-color: #DCFCE7;" data-status="en_cours">
        <div class="kanban-col-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:10px;height:10px;border-radius:50%;background:#F59E0B;"></div>
                EN COURS
            </div>
            <span class="kanban-badge" style="background:#FEF3C7; color:#B45309;">{{ $tasksInProgress->count() }}</span>
        </div>
        
        <div class="kanban-list">
            @foreach($tasksInProgress as $task)
                @include('gel-secretary.tasks.partials.kanban-card', ['task' => $task, 'col' => 'in_progress'])
            @endforeach
        </div>
    </div>

    <!-- COLONNE : EN ATTENTE -->
    <div class="kanban-col" style="background: #FDF4FF; border-color: #FAE8FF;" data-status="en_attente">
        <div class="kanban-col-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:10px;height:10px;border-radius:50%;background:#D946EF;"></div>
                EN ATTENTE
            </div>
            <span class="kanban-badge" style="background:#FAE8FF; color:#C026D3;">{{ $tasksPending->count() }}</span>
        </div>
        
        <div class="kanban-list">
            @foreach($tasksPending as $task)
                @include('gel-secretary.tasks.partials.kanban-card', ['task' => $task, 'col' => 'to_validate'])
            @endforeach
        </div>
    </div>

    <!-- COLONNE : TERMINÉES -->
    <div class="kanban-col" style="background: #F8FAFC; opacity: 0.8;" data-status="terminee">
        <div class="kanban-col-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:10px;height:10px;border-radius:50%;background:#10B981;"></div>
                TERMINÉES
            </div>
            <span class="kanban-badge">{{ $tasksDone->count() }}</span>
        </div>
        
        <div class="kanban-list">
            @foreach($tasksDone as $task)
                @include('gel-secretary.tasks.partials.kanban-card', ['task' => $task, 'col' => 'done'])
            @endforeach
        </div>
    </div>

</div>

<!-- Modal Création -->
<div id="taskModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="sec-card" style="width:450px; padding:24px; position:relative;">
        <h2 style="margin-top:0; margin-bottom:16px; font-size:18px;">Nouvelle Tâche</h2>
        <form action="{{ route('gel-secretary.tasks.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Titre</label>
                <input type="text" name="titre" required style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Description</label>
                <textarea name="description" rows="3" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box; resize:none;"></textarea>
            </div>
            <div style="display:flex; gap:12px; margin-bottom:16px;">
                <div style="flex:1;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Priorité</label>
                    <select name="priorite" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                        <option value="basse">Basse</option>
                        <option value="moyenne" selected>Moyenne</option>
                        <option value="haute">Haute</option>
                        <option value="critique">Critique</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Échéance</label>
                    <input type="date" name="date_echeance" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="sec-btn" onclick="document.getElementById('taskModal').style.display='none'" style="background:#f1f5f9; color:#475569; border:none;">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Créer la tâche</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lists = document.querySelectorAll('.kanban-list');
    
    lists.forEach(list => {
        new Sortable(list, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'kanban-ghost',
            onEnd: function (evt) {
                const itemEl = evt.item;
                const toList = evt.to;
                
                const taskId = itemEl.getAttribute('data-id');
                const newStatus = toList.closest('.kanban-col').getAttribute('data-status');
                const oldStatus = evt.from.closest('.kanban-col').getAttribute('data-status');
                
                if (newStatus !== oldStatus) {
                    // Update server
                    fetch(`/gel-secretary/tasks/${taskId}/status/${newStatus}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    }).then(res => res.json())
                      .then(data => {
                          if (data.success) {
                              // Update counters
                              updateCounters();
                          }
                      })
                      .catch(err => {
                          console.error(err);
                          // Revert if error
                          evt.from.insertBefore(itemEl, evt.from.children[evt.oldIndex]);
                      });
                }
            },
        });
    });

    function updateCounters() {
        document.querySelectorAll('.kanban-col').forEach(col => {
            const count = col.querySelectorAll('.kanban-card').length;
            col.querySelector('.kanban-badge').innerText = count;
        });
    }
});
</script>
<style>
    .kanban-list {
        min-height: 100px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .kanban-ghost {
        opacity: 0.4;
        background-color: #f8fafc;
        border: 2px dashed #cbd5e1;
    }
</style>
@endpush
@endsection
