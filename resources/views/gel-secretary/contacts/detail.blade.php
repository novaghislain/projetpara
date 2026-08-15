@extends('layouts.gel-secretary')
@section('title', 'Fiche Contact 360°')

@push('styles')
<style>
/* ==========================================================================
   CONTACT DETAIL 360 - BENTO GRID DESIGN
   ========================================================================== */
.contact-detail-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: minmax(100px, auto);
    gap: 24px;
    margin-bottom: 40px;
}

.cd-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05),
                inset 0 0 0 1px rgba(255, 255, 255, 0.5);
    display: flex;
    flex-direction: column;
}

/* Sections */
.cd-header { grid-column: span 12; display: flex; align-items: center; justify-content: space-between; padding:32px 40px;}
.cd-profile { grid-column: span 4; grid-row: span 3; align-items: center; text-align: center; }
.cd-activity { grid-column: span 8; grid-row: span 3; }

/* Profile Card */
.cd-avatar-large {
    width: 100px; height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0EA5E9, #2563EB);
    color: white;
    font-size: 32px;
    font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px auto;
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
}
.cd-name { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 4px; }
.cd-role { font-size: 14px; font-weight: 600; color: #64748B; margin-bottom: 16px; }
.cd-badge { display:inline-block; padding: 6px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; background: #EFF6FF; color: #3B82F6; margin-bottom:24px;}
.cd-badge.portal { background: #F5F3FF; color: #7C3AED; }

.cd-info-list { width: 100%; text-align: left; }
.cd-info-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #E2E8F0; }
.cd-info-item:last-child { border-bottom: none; }
.cd-info-icon { width: 32px; height: 32px; border-radius: 10px; background: #F8FAFC; color: #64748B; display: flex; align-items: center; justify-content: center; }
.cd-info-text { font-size: 14px; font-weight: 500; color: #334155; }
.cd-info-label { font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase; margin-bottom: 2px;}

/* Activity Card */
.activity-header { font-size:18px; font-weight:800; color:#1E293B; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; gap:12px;}
.activity-tabs { display:flex; gap:16px; margin-bottom:24px; border-bottom:2px solid #E2E8F0; }
.activity-tab { padding:8px 16px; font-size:14px; font-weight:700; color:#64748B; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s;}
.activity-tab.active { color:#0D9488; border-bottom-color:#0D9488; }
.activity-tab:hover:not(.active) { color:#1E293B; }

.timeline { position:relative; padding-left:24px; }
.timeline::before { content:''; position:absolute; left:7px; top:0; bottom:0; width:2px; background:#E2E8F0; }
.timeline-item { position:relative; margin-bottom:24px; }
.timeline-item::before { content:''; position:absolute; left:-24px; top:4px; width:16px; height:16px; border-radius:50%; background:#0D9488; border:3px solid white; box-shadow:0 0 0 1px #E2E8F0; }
.timeline-date { font-size:12px; font-weight:700; color:#64748B; margin-bottom:4px; }
.timeline-title { font-size:14px; font-weight:700; color:#1E293B; }
.timeline-desc { font-size:13px; color:#475569; margin-top:4px; line-height:1.5; }

/* Buttons */
.btn-back { background: white; color: #1E293B; border: 1px solid #E2E8F0; padding: 10px 20px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration:none; display:inline-flex; align-items:center; gap:8px;}
.btn-back:hover { background: #F8FAFC; }
.btn-action { background: #0D9488; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; display:inline-flex; align-items:center; gap:8px;}
.btn-action:hover { background: #0F766E; }

/* Animations */
.stagger-1 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.2s;}
.stagger-3 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.3s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

@php
    $initials = strtoupper(substr($contact->name, 0, 2));
    $badgeType = isset($contact->type) && $contact->type == 'portal' ? 'portal' : 'internal';
    $badgeLabel = $badgeType == 'portal' ? 'Accès Portail' : 'Contact Interne';
@endphp

<div class="contact-detail-grid">

    <!-- HEADER -->
    <div class="cd-card cd-header stagger-1">
        <div>
            <a href="{{ route('gel-secretary.contacts.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au carnet</a>
        </div>
        <div style="display:flex; gap:12px;">
            <button class="btn-back"><i class="fas fa-edit"></i> Modifier</button>
            <button class="btn-action"><i class="fas fa-paper-plane"></i> Envoyer un message</button>
        </div>
    </div>

    <!-- PROFILE CARD -->
    <div class="cd-card cd-profile stagger-2">
        <div class="cd-avatar-large">{{ $initials }}</div>
        <div class="cd-name">{{ $contact->name }}</div>
        <div class="cd-role">{{ $contact->position ?? 'Fonction non renseignée' }}</div>
        <div class="cd-badge {{ $badgeType }}">{{ $badgeLabel }}</div>

        <div class="cd-info-list">
            <div class="cd-info-item">
                <div class="cd-info-icon"><i class="fas fa-envelope"></i></div>
                <div>
                    <div class="cd-info-label">Email</div>
                    <div class="cd-info-text">{{ $contact->email ?? 'Non renseigné' }}</div>
                </div>
            </div>
            <div class="cd-info-item">
                <div class="cd-info-icon"><i class="fas fa-phone"></i></div>
                <div>
                    <div class="cd-info-label">Téléphone</div>
                    <div class="cd-info-text">{{ $contact->phone ?? 'Non renseigné' }}</div>
                </div>
            </div>
            <div class="cd-info-item">
                <div class="cd-info-icon"><i class="fas fa-building"></i></div>
                <div>
                    <div class="cd-info-label">Entreprise</div>
                    <div class="cd-info-text">{{ $activeClient ? $activeClient->nom_entreprise : 'Autonome' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIVITY & 360 VIEW -->
    <div class="cd-card cd-activity stagger-3">
        <div class="activity-header">
            <i class="fas fa-history" style="color:#0D9488;"></i> Activité & Historique 360°
        </div>
        
        <div class="activity-tabs">
            <div class="activity-tab active" onclick="switchTab('tasks')">Tâches Associées</div>
            <div class="activity-tab" onclick="switchTab('calls')">Appels & Échanges</div>
            <div class="activity-tab" onclick="switchTab('events')">Événements</div>
        </div>

        <!-- Tâches -->
        <div id="tab-tasks" class="tab-content timeline">
            @forelse($tasks as $task)
                <div class="timeline-item">
                    <div class="timeline-date">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                    <div class="timeline-title">{{ $task->titre }}</div>
                    <div class="timeline-desc">{{ Str::limit($task->description, 100) }}</div>
                </div>
            @empty
                <div style="padding:20px; color:#94A3B8; text-align:center;">
                    <i class="fas fa-tasks" style="font-size:24px; margin-bottom:12px; opacity:0.5;"></i>
                    <br>Aucune tâche associée.
                </div>
            @endforelse
        </div>

        <!-- Appels -->
        <div id="tab-calls" class="tab-content timeline" style="display:none;">
            @forelse($calls as $call)
                <div class="timeline-item">
                    <div class="timeline-date">{{ $call->created_at->format('d/m/Y H:i') }}</div>
                    <div class="timeline-title">{{ $call->subject ?? 'Appel téléphonique' }}</div>
                    <div class="timeline-desc">{{ $call->notes ?? '' }}</div>
                </div>
            @empty
                <div style="padding:20px; color:#94A3B8; text-align:center;">
                    <i class="fas fa-phone-alt" style="font-size:24px; margin-bottom:12px; opacity:0.5;"></i>
                    <br>Aucun appel logué.
                </div>
            @endforelse
        </div>

        <!-- Événements -->
        <div id="tab-events" class="tab-content timeline" style="display:none;">
            @forelse($events as $event)
                <div class="timeline-item">
                    <div class="timeline-date">{{ \Carbon\Carbon::parse($event->date_debut)->format('d/m/Y H:i') }}</div>
                    <div class="timeline-title">{{ $event->titre }}</div>
                    <div class="timeline-desc">{{ $event->lieu ?? 'Aucun lieu spécifié' }}</div>
                </div>
            @empty
                <div style="padding:20px; color:#94A3B8; text-align:center;">
                    <i class="fas fa-calendar-alt" style="font-size:24px; margin-bottom:12px; opacity:0.5;"></i>
                    <br>Aucun événement lié.
                </div>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.activity-tab').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabId).style.display = 'block';
        event.currentTarget.classList.add('active');
    }
</script>
@endpush

@endsection
