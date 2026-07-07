@extends('layouts.gel')

@section('title', 'Flux d\'activité - GEL Cabinet')

@section('styles')
<style>
    .activity-timeline {
        position: relative;
        padding-left: 2rem;
    }
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--gel-border);
    }
    .activity-item {
        position: relative;
        padding-bottom: 1.5rem;
        animation: fadeIn 0.3s ease;
    }
    .activity-item:last-child { padding-bottom: 0; }
    .activity-dot {
        position: absolute;
        left: -2rem;
        top: 4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid white;
        z-index: 1;
    }
    .activity-content {
        background: white;
        border-radius: 10px;
        border: 1px solid var(--gel-border);
        padding: 0.75rem 1rem;
        transition: all 0.2s;
    }
    .activity-content:hover { border-color: var(--gel-accent-2); }
    .activity-content .time { font-size: 0.75rem; color: var(--gel-text-muted); }
    .activity-content .actor { font-weight: 600; font-size: 0.85rem; }
    .activity-content .action { font-size: 0.85rem; color: var(--gel-text-muted); }

    .notification-item {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--gel-border);
        background: white;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
        cursor: pointer;
    }
    .notification-item:hover { border-color: var(--gel-accent-2); }
    .notification-item.unread {
        background: rgba(99, 91, 255, 0.04);
        border-left: 3px solid var(--gel-accent-2);
    }
    .notification-item .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .filter-btn {
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        border: 1px solid var(--gel-border);
        background: white;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-btn.active { background: var(--gel-accent-2); color: white; border-color: var(--gel-accent-2); }
    .filter-btn:hover:not(.active) { border-color: var(--gel-accent-2); color: var(--gel-accent-2); }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">Flux d'activité</h1>
        <p class="page-subtitle">Suivez en temps réel les actions de votre équipe</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-outline-primary btn-sm" onclick="marquerToutLu()">
            <i class="bi bi-check-all me-1"></i>Tout marquer lu
        </button>
        <button class="btn btn-outline-secondary btn-sm" onclick="nettoyerAnciennes()">
            <i class="bi bi-trash me-1"></i>Nettoyer
        </button>
    </div>
</div>

<div class="row g-4">
    {{-- Notifications --}}
    <div class="col-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="font-weight:700;margin:0;">
                <i class="bi bi-bell me-1"></i>Notifications
                <span class="badge bg-danger ms-1" id="notifCount">{{ $notifications->whereNull('read_at')->count() }}</span>
            </h5>
        </div>

        <div style="max-height:600px;overflow-y:auto;" id="notificationsList">
            @forelse($notifications as $notif)
            <div class="notification-item {{ $notif->read_at ? 'read' : 'unread' }}" data-id="{{ $notif->id }}" onclick="marquerLu({{ $notif->id }})">
                <div class="d-flex gap-3">
                    <div class="notif-icon" style="background:{{ $notif->couleur ?? '#635bff' }}15;color:{{ $notif->couleur ?? '#635bff' }};">
                        <i class="bi {{ $notif->icone ?? 'bi-bell' }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:0.85rem;">{{ $notif->titre ?? 'Notification' }}</div>
                        <div style="font-size:0.8rem;color:var(--gel-text-muted);">{{ $notif->message ?? $notif->description ?? '' }}</div>
                        <div style="font-size:0.7rem;color:var(--gel-text-muted);margin-top:0.25rem;">
                            {{ $notif->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @if(!empty($notif->action_url))
                <div class="mt-2">
                    <a href="{{ $notif->action_url }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">
                        {{ $notif->action_label ?? 'Voir' }}
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-bell-slash d-block mb-2" style="font-size:2rem;"></i>
                Aucune notification
            </div>
            @endforelse
        </div>
    </div>

    {{-- Timeline d'activité --}}
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="font-weight:700;margin:0;">
                <i class="bi bi-activity me-1"></i>Activités récentes
            </h5>
            <div class="d-flex gap-1" id="activityFilters">
                <button class="filter-btn active" data-filter="all" onclick="filtrerActivites('all', this)">Tout</button>
                <button class="filter-btn" data-filter="comptabilite" onclick="filtrerActivites('comptabilite', this)">Compta</button>
                <button class="filter-btn" data-filter="facturation" onclick="filtrerActivites('facturation', this)">Facture</button>
                <button class="filter-btn" data-filter="paie" onclick="filtrerActivites('paie', this)">Paie</button>
                <button class="filter-btn" data-filter="client" onclick="filtrerActivites('client', this)">Client</button>
                <button class="filter-btn" data-filter="ia" onclick="filtrerActivites('ia', this)">IA</button>
            </div>
        </div>

        <div class="activity-timeline" id="activityTimeline">
            @forelse($activities as $activity)
            <div class="activity-item" data-type="{{ $activity->event ? explode('.', $activity->event)[0] : 'other' }}">
                <div class="activity-dot" style="background: {{ $activity->couleur ?? '#635bff' }};"></div>
                <div class="activity-content">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="actor">{{ $activity->user?->name ?? 'Système' }}</span>
                            <span class="action">{{ $activity->description ?? $activity->event ?? '' }}</span>
                        </div>
                        <div class="time">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                    @if($activity->old_values || $activity->new_values)
                    <div style="font-size:0.75rem;color:var(--gel-text-muted);margin-top:0.25rem;">
                        <i class="bi bi-info-circle me-1"></i>Modification détectée
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-clock-history d-block mb-2" style="font-size:2rem;"></i>
                Aucune activité récente
            </div>
            @endforelse
        </div>

        <div id="loadMoreBtn" class="text-center mt-3">
            <button class="btn btn-outline-secondary btn-sm" onclick="chargerPlus()">
                <i class="bi bi-arrow-down me-1"></i>Charger plus d'activités
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activitePage = 1;
    let activiteFiltre = 'all';

    // ─── Polling des activités ───
    function pollActivities() {
        fetch('/api/activity/recent', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.activities.length > 0) {
                const timeline = document.getElementById('activityTimeline');
                data.activities.forEach(act => {
                    const existing = timeline.querySelector(`[data-activity-id="${act.id}"]`);
                    if (!existing) {
                        const div = document.createElement('div');
                        div.className = 'activity-item';
                        div.dataset.type = act.event?.split('.')[0] || 'other';
                        div.dataset.activityId = act.id;
                        div.innerHTML = `
                            <div class="activity-dot" style="background: ${act.couleur || '#635bff'};"></div>
                            <div class="activity-content" style="animation: fadeIn 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="actor">${act.user || 'Système'}</span>
                                        <span class="action">${act.description || ''}</span>
                                    </div>
                                    <div class="time">&Agrave; l'instant</div>
                                </div>
                            </div>
                        `;
                        timeline.insertBefore(div, timeline.firstChild);
                    }
                });
                updateNotificationBadge();
            }
        });
    }

    // ─── Marquer une notification comme lue ───
    function marquerLu(id) {
        const item = document.querySelector(`.notification-item[data-id="${id}"]`);
        if (item && item.classList.contains('unread')) {
            fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    updateNotificationBadge();
                }
            });
        }
        const link = item?.querySelector('a');
        if (link) window.location.href = link.href;
    }

    // ─── Marquer tout comme lu ───
    function marquerToutLu() {
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                });
                updateNotificationBadge();
            }
        });
    }

    // ─── Nettoyer les anciennes notifications ───
    function nettoyerAnciennes() {
        if (!confirm('Supprimer les notifications lues de plus de 90 jours ?')) return;
        fetch('/api/notifications/clean-old', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.deleted + ' notification(s) nettoyée(s)');
                location.reload();
            }
        });
    }

    // ─── Filtrer les activités ───
    function filtrerActivites(filter, btn) {
        activiteFiltre = filter;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.activity-item').forEach(item => {
            if (filter === 'all' || item.dataset.type === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // ─── Charger plus d'activités ───
    function chargerPlus() {
        const btn = document.querySelector('#loadMoreBtn button');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Chargement...';

        activitePage++;
        fetch(`/api/activity/recent?limit=50&page=${activitePage}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.activities.length > 0) {
                const timeline = document.getElementById('activityTimeline');
                data.activities.forEach(act => {
                    const div = document.createElement('div');
                    div.className = 'activity-item';
                    div.dataset.type = act.event?.split('.')[0] || 'other';
                    div.innerHTML = `
                        <div class="activity-dot" style="background: ${act.couleur || '#635bff'};"></div>
                        <div class="activity-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="actor">${act.user || 'Système'}</span>
                                    <span class="action">${act.description || ''}</span>
                                </div>
                                <div class="time">${act.time_diff || act.created_at || ''}</div>
                            </div>
                        </div>
                    `;
                    timeline.appendChild(div);
                });
            } else {
                btn.textContent = 'Plus d\'activités à charger';
                btn.disabled = true;
            }
        })
        .finally(() => {
            btn.disabled = false;
        });
    }

    // ─── Mettre à jour le badge ───
    function updateNotificationBadge() {
        fetch('/api/notifications/unread-count', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifCount');
            if (data.success) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline' : 'none';
            }
        });
    }

    // ─── Initialisation ───
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(pollActivities, 30000);
    });
</script>
@endsection
