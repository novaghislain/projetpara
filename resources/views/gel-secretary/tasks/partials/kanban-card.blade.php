@php
    $isPast = $task->date_echeance && \Carbon\Carbon::parse($task->date_echeance)->isPast() && $task->statut !== 'terminee';
    
    // IA Logic : suggestion priorité
    $suggestPriority = null;
    if ($task->statut === 'a_faire' || $task->statut === 'en_cours') {
        $isDueSoon = $task->date_echeance && \Carbon\Carbon::parse($task->date_echeance)->diffInDays(now()) <= 2 && \Carbon\Carbon::parse($task->date_echeance)->isFuture();
        
        $keywords = ['urgent', 'important', 'vite', 'critique', 'immédiat', 'asap', 'urgence'];
        $hasKeywords = false;
        $desc = strtolower($task->titre . ' ' . $task->description);
        foreach ($keywords as $kw) {
            if (strpos($desc, $kw) !== false) {
                $hasKeywords = true;
                break;
            }
        }
        
        if (($isPast || $isDueSoon || $hasKeywords) && in_array($task->priorite, ['basse', 'moyenne', 'haute'])) {
            $suggestPriority = ($isPast || $hasKeywords) ? 'critique' : 'haute';
            if ($suggestPriority === $task->priorite) {
                $suggestPriority = null;
            }
        }
    }
@endphp
<div class="kanban-card" data-id="{{ $task->id }}" onclick="document.getElementById('taskDetailsModal{{ $task->id }}').style.display='flex'" style="cursor: pointer;">
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <span class="kanban-badge prio-{{ $task->priorite }}">{{ ucfirst($task->priorite) }}</span>
            @if($suggestPriority)
                <span class="badge" style="background:#FFFBEB; color:#D97706; border:1px solid #FDE68A; font-size:10px;" title="Suggestion IA: Priorité {{ ucfirst($suggestPriority) }}">
                    <i class="fas fa-sparkles"></i> IA: {{ ucfirst($suggestPriority) }}
                </span>
            @endif
        </div>
        
        <!-- Options (dropdown for delete, edit) -->
        <div class="dropdown">
            <button class="k-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none; background:transparent; color:#94A3B8;">
                <i class="fas fa-ellipsis-h"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 13px;">
                <li>
                    <form action="{{ route('gel-secretary.tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Supprimer cette tâche ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i> Supprimer</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="kanban-card-title {{ $task->statut === 'terminee' ? 'text-muted' : '' }}" style="{{ $task->statut === 'terminee' ? 'text-decoration:line-through; opacity:0.6;' : '' }}">
        @if($task->source === 'coordination')
            <i class="fas fa-people-arrows" style="color:#0D9488; font-size:11px;" title="Tâche de coordination Secrétaire ↔ Comptable"></i>
        @endif
        {{ $task->titre }}
    </div>

    @if($task->source === 'coordination')
    <span class="badge" style="background:#CCFBF1; color:#0F766E; border:1px solid #99F6E4; font-size:9px; font-weight:700; margin-top:4px;">
        <i class="fas fa-people-arrows me-1"></i>
        {{ $task->coordination_type === 'alerte' ? 'Alerte comptable' : ($task->coordination_type === 'demande_document' ? 'Demande comptable' : ($task->coordination_type === 'note_liee' ? 'Note secrétariat' : 'Coordination')) }}
    </span>
    @endif

    @if($task->client)
    <div class="kanban-card-meta mb-1">
        <i class="fas fa-building" style="color:var(--sec-text-muted); width:14px; text-align:center;"></i>
        <span>{{ $task->client->nom_entreprise ?? $task->client->nom_entreprise }}</span>
    </div>
    @endif
    
    @if($task->assigne)
    <div class="kanban-card-meta mb-2">
        <i class="fas fa-user" style="color:var(--sec-text-muted); width:14px; text-align:center;"></i>
        <span>{{ $task->assigne->name }}</span>
    </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; border-top: 1px solid #F1F5F9; padding-top: 8px;">
        @if($task->date_echeance)
            <div class="kanban-card-meta {{ $isPast ? 'text-overdue' : '' }}" style="margin:0;">
                <i class="far fa-calendar-alt"></i>
                {{ \Carbon\Carbon::parse($task->date_echeance)->format('d/m/Y') }}
            </div>
        @else
            <div></div>
        @endif
        
        <div style="display:flex; gap:10px; color:#94A3B8; font-size:12px;">
            <span title="Pièces jointes"><i class="fas fa-paperclip"></i> {{ $task->attachments ? $task->attachments->count() : 0 }}</span>
            <span title="Commentaires"><i class="far fa-comment"></i> {{ $task->comments ? $task->comments->count() : 0 }}</span>
        </div>
    </div>
</div>
