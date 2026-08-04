@extends('layouts.gel-secretary')

@section('title', 'Agenda — Secrétariat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-calendar-alt" style="color:var(--sec-primary); margin-right:8px;"></i>Agenda
    </h1>
    <p class="sec-page-sub">
      @if($activeClient)
        Rendez-vous et planification pour : <strong>{{ $activeClient->company_name }}</strong>
      @else
        Veuillez sélectionner une entreprise active dans la barre supérieure.
      @endif
    </p>
  </div>
  @if($activeClient)
  <button class="sec-btn sec-btn-primary" onclick="document.getElementById('rdvModal').style.display='flex'">
    <i class="fas fa-plus"></i> Nouveau rendez-vous
  </button>
  @endif
</div>

@if($activeClient)
<div style="display:grid;grid-template-columns:1fr 280px;gap:20px; align-items:start;">
  {{-- Planning des rendez-vous --}}
  <div class="sec-card" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04);">
    <div class="sec-card-header" style="display:flex; justify-content:space-between; align-items:center;">
      <div class="sec-card-title">
        <i class="fas {{ $currentView === 'online' ? 'fa-globe' : 'fa-calendar-week' }}" style="color:{{ $currentView === 'online' ? '#16A34A' : 'var(--sec-primary)' }};margin-right:6px;"></i>
        {{ $currentView === 'online' ? 'Demandes de RDV en ligne' : 'Rendez-vous planifiés' }}
        <span style="font-size:12px; font-weight:normal; color:var(--sec-text-muted); margin-left:8px;">
          ({{ $currentView === 'online' ? $onlineBookings->count() : $events->count() }})
        </span>
      </div>
      <div style="display:flex; gap:8px;">
        <button class="sec-btn sec-btn-primary" onclick="document.getElementById('rdvModal').style.display='flex'" style="padding:6px 12px; font-size:12px;">
          <i class="fas fa-plus"></i> Créer
        </button>
        @if($currentView === 'online')
          <a href="{{ route('gel-secretary.agenda.index') }}" class="sec-btn" style="background:#F1F5F9; color:#475569; padding:6px 12px; font-size:12px; text-decoration:none;"><i class="fas fa-arrow-left"></i> Retour au planning complet</a>
        @else
          <a href="{{ route('gel-secretary.agenda.index', ['view' => 'online']) }}" class="sec-btn" style="background:#F0FDF4; color:#16A34A; border:1px solid #BBF7D0; padding:6px 12px; font-size:12px; text-decoration:none;">
            <i class="fas fa-globe"></i> RDV en ligne @if($onlineBookings->count() > 0)<span style="background:#16A34A;color:white;border-radius:10px;padding:2px 6px;margin-left:4px;font-size:10px;">{{ $onlineBookings->count() }}</span>@endif
          </a>
        @endif
      </div>
    </div>
    <div class="sec-card-body" style="padding:0;">
      @php
        $listToDisplay = $currentView === 'online' ? $onlineBookings : $events;
      @endphp
      
      @forelse($listToDisplay as $event)
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #F3F4F6;">
        <div style="display:flex;align-items:center;gap:14px;flex:1;">
          <div style="width:36px;text-align:center;font-size:11px;font-weight:700;color:var(--sec-text-muted);">
            {{ \Carbon\Carbon::parse($event->start_at)->translatedFormat('D d') }}
          </div>
          <div style="width:75px;font-size:12px;color:var(--sec-text-muted);font-weight:600;">
            {{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}
          </div>
          <div style="width:4px;height:36px;background:{{ $event->couleur }};border-radius:2px;flex-shrink:0;"></div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--sec-text);">
              {{ $event->title }}
              @if($currentView === 'online' && $event->statut === 'a_venir')
                <span style="font-size:10px; background:#FEF3C7; color:#D97706; padding:2px 6px; border-radius:4px; margin-left:8px;">Nouvelle demande</span>
              @endif
              @if($event->invitation_sent)
                <span style="font-size:10px; background:#DCFCE7; color:#16A34A; padding:2px 6px; border-radius:4px; margin-left:4px;" title="Invitation email envoyée"><i class="fas fa-envelope-open-text"></i> Invité</span>
              @endif
            </div>
            <div style="font-size:11px;color:var(--sec-text-muted);margin-top:4px;display:flex;gap:10px;flex-wrap:wrap;">
              @if($event->location)
                <span><i class="fas fa-map-marker-alt"></i> {{ $event->location }}</span>
              @endif
              @if($event->visio_link)
                <a href="{{ $event->visio_link }}" target="_blank" style="color:var(--sec-primary);text-decoration:none;font-weight:600;">
                    @if($event->visio_type === 'zoom') <i class="fas fa-video"></i> Rejoindre Zoom
                    @elseif($event->visio_type === 'teams') <i class="fas fa-video"></i> Rejoindre Teams
                    @elseif($event->visio_type === 'meet') <i class="fas fa-video"></i> Rejoindre Meet
                    @else <i class="fas fa-video"></i> Lien Visio
                    @endif
                </a>
              @endif
              @if(!$event->location && !$event->visio_link)
                <span><i class="fas fa-info-circle"></i> {{ $event->description ?: 'Aucune description' }}</span>
              @endif
            </div>
          </div>
        </div>
        
        <div style="display:flex; gap:8px;">
          @if($currentView === 'online' && $event->statut === 'a_venir')
            <!-- Optionnel: Ajouter une route pour "Accepter" le RDV qui changerait le statut -->
            <button class="sec-btn" style="background:#F0FDF4; color:#16A34A; border:1px solid #BBF7D0; padding:6px 12px; font-size:12px;" onclick="secToast('Fonctionnalité de validation à venir', 'info')"><i class="fas fa-check"></i> Valider</button>
          @endif
          <form action="{{ route('gel-secretary.agenda.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Supprimer ce rendez-vous ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="sec-btn" style="background:none; border:1px solid #E2E8F0; color:#94a3b8; padding:6px 10px; cursor:pointer;" onmouseover="this.style.color='#ef4444';this.style.borderColor='#FECACA';" onmouseout="this.style.color='#94a3b8';this.style.borderColor='#E2E8F0';">
              <i class="fas fa-trash-alt"></i>
            </button>
          </form>
        </div>
      </div>
      @empty
      <div style="padding:40px; text-align:center; color:var(--sec-text-muted);">
        <i class="far {{ $currentView === 'online' ? 'fa-calendar-check' : 'fa-calendar-times' }}" style="font-size:32px; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
        <div style="margin-bottom:16px;">
            {{ $currentView === 'online' ? 'Aucune demande de rendez-vous en ligne.' : 'Aucun rendez-vous planifié pour cette entreprise.' }}
        </div>
        <button class="sec-btn sec-btn-primary" onclick="document.getElementById('rdvModal').style.display='flex'">
          <i class="fas fa-plus" style="margin-right:6px;"></i>Créer un rendez-vous
        </button>
      </div>
      @endforelse
    </div>
  </div>

    {{-- Barre latérale : Calendrier interactif FullCalendar --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="sec-card" style="border-radius:14px; overflow:hidden; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); border:1px solid #e2e8f0; padding: 20px; background:#fff;">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<style>
/* FullCalendar Premium Styling */
.fc {
    font-family: inherit;
}
.fc-theme-standard td, .fc-theme-standard th, .fc-theme-standard .fc-scrollgrid {
    border-color: #f1f5f9;
}
.fc-col-header-cell {
    background-color: #f8fafc;
    padding: 8px 0 !important;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: capitalize;
    border-bottom: 2px solid #e2e8f0 !important;
}
.fc-daygrid-day-number {
    font-size: 12px;
    color: #334155;
    padding: 4px !important;
    font-weight: 600;
}
.fc .fc-button-primary {
    background-color: #f1f5f9 !important;
    border-color: transparent !important;
    color: #475569 !important;
    font-weight: 600;
    font-size: 11px;
    border-radius: 6px;
    padding: 4px 8px;
    text-transform: capitalize;
    box-shadow: none !important;
    transition: all 0.2s ease;
}
.fc .fc-button-primary:hover {
    background-color: #e2e8f0 !important;
    color: #1e293b !important;
}
.fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: var(--sec-primary) !important;
    color: #fff !important;
}
.fc .fc-button-primary .fc-icon {
    font-size: 1.2em;
}
.fc-toolbar-title {
    font-size: 14px !important;
    font-weight: 800 !important;
    color: #0f172a;
    text-transform: capitalize;
    margin: 0 !important;
}
.fc-header-toolbar {
    gap: 4px;
    margin-bottom: 12px !important;
}
.fc-day-today {
    background-color: #f0f9ff !important;
}
.fc-day-today .fc-daygrid-day-number {
    color: #0284c7;
    background: #e0f2fe;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 2px;
    padding: 0 !important;
}
.fc-daygrid-event {
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    border: none !important;
    margin: 1px !important;
    overflow: hidden;
    cursor: pointer;
}
.fc-daygrid-block-event {
    padding: 2px 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.fc-daygrid-dot-event {
    padding: 2px;
    display: flex;
    align-items: center;
}
.fc-daygrid-event:hover {
    opacity: 0.9;
}
.fc-daygrid-day-frame {
    padding: 2px;
}
.fc-view-harness {
    min-height: 250px;
}
.holiday-event {
    background-color: #fee2e2 !important;
    color: #991b1b !important;
    border: 1px dashed #fca5a5 !important;
    font-size: 9px !important;
    font-weight: 700 !important;
    text-align: center;
    border-radius: 4px;
    padding: 2px !important;
    margin-bottom: 4px !important;
}
.holiday-event .fc-event-title {
    white-space: normal;
    line-height: 1.1;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/fr.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawEvents = @json($events);
    
    // Process events for FullCalendar
    const calendarEvents = rawEvents.map(e => ({
        id: e.id,
        title: e.title,
        start: e.start_at,
        backgroundColor: e.couleur || 'var(--sec-primary)',
        borderColor: e.couleur || 'var(--sec-primary)',
        textColor: '#fff',
        extendedProps: {
            description: e.description,
            location: e.location,
            visio_link: e.visio_link
        }
    }));

    // Jours fériés Bénin 2026
    const holidays = [
        { start: '2026-01-01', title: "Jour de l'An" },
        { start: '2026-01-10', title: "Fête du Vodoun" },
        { start: '2026-04-06', title: "Lundi de Pâques" },
        { start: '2026-05-01', title: "Fête du Travail" },
        { start: '2026-05-14', title: "Ascension" },
        { start: '2026-05-25', title: "Lundi Pentecôte" },
        { start: '2026-08-01', title: "Fête Nationale" },
        { start: '2026-08-15', title: "Assomption" },
        { start: '2026-11-01', title: "Toussaint" },
        { start: '2026-12-25', title: "Noël" }
    ];

    const holidayEvents = holidays.map(h => ({
        title: h.title,
        start: h.start,
        display: 'block',
        className: 'holiday-event',
        allDay: true,
        editable: false
    }));

    const allEvents = calendarEvents.concat(holidayEvents);

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today'
        },
        buttonText: {
            today: "Aujourd'hui"
        },
        height: 'auto',
        events: allEvents,
        eventClick: function(info) {
            // Optionnel : Afficher les détails au clic
            // alert('Rendez-vous : ' + info.event.title);
        },
        dateClick: function(info) {
            // Pré-remplir la date dans la modale d'ajout
            const modal = document.getElementById('rdvModal');
            const dateInput = modal.querySelector('input[name="start_at"]');
            if(dateInput) {
                // info.dateStr est YYYY-MM-DD
                dateInput.value = info.dateStr + 'T09:00'; 
            }
            modal.style.display = 'flex';
        }
    });
    calendar.render();
});

function updateVisioPlaceholder() {
    const type = document.getElementById('visio_type_select').value;
    const input = document.getElementById('visio_link_input');
    if (type === 'zoom') input.placeholder = 'https://zoom.us/j/...';
    else if (type === 'teams') input.placeholder = 'https://teams.microsoft.com/l/meetup-join/...';
    else if (type === 'meet') input.placeholder = 'https://meet.google.com/...';
    else input.placeholder = 'Lien de la visioconférence';
}
</script>

{{-- ─── MODAL NOUVEAU RDV ─── --}}
<div id="rdvModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:500px; max-width:95%; max-height:90vh; overflow-y:auto; border:1px solid var(--sec-border); box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="padding:16px 20px; background:#f9fafb; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:14px; font-weight:700; color:var(--sec-text); margin:0;">Nouveau rendez-vous</h3>
            <button type="button" onclick="document.getElementById('rdvModal').style.display='none'" style="background:none; border:none; font-size:18px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('gel-secretary.agenda.store') }}" method="POST" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            @csrf

            <div class="sec-form-group">
                <label>Objet du rendez-vous *</label>
                <input type="text" name="title" required placeholder="Ex: Réunion préparation bilan" class="sec-form-control">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="sec-form-group">
                    <label>Type d'événement *</label>
                    <select name="type" required class="sec-form-select">
                        <option value="rdv">Rendez-vous standard</option>
                        <option value="reunion">Réunion interne</option>
                        <option value="appel">Appel téléphonique</option>
                        <option value="echeance">Échéance critique</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="sec-form-group">
                    <label>Date et heure *</label>
                    <input type="datetime-local" name="start_at" required class="sec-form-control">
                </div>
            </div>

            <div class="sec-form-group">
                <label>Lieu physique</label>
                <input type="text" name="location" placeholder="Ex: Bureau 204, Salle de réunion..." class="sec-form-control">
            </div>

            {{-- Visio --}}
            <div style="background:#F0F9FF; border:1px solid #BAE6FD; border-radius:10px; padding:14px;">
                <div style="font-size:12px; font-weight:700; color:#0369A1; margin-bottom:10px;">
                    <i class="fas fa-video" style="margin-right:6px;"></i> Visioconférence (optionnel)
                </div>
                <div style="display:grid; grid-template-columns:140px 1fr; gap:10px; align-items:center;">
                    <select name="visio_type" id="visio_type_select" class="sec-form-select" style="font-size:12px;" onchange="updateVisioPlaceholder()">
                        <option value="">-- Plateforme --</option>
                        <option value="zoom">&#127909; Zoom</option>
                        <option value="teams">&#128187; Microsoft Teams</option>
                        <option value="meet">&#127744; Google Meet</option>
                        <option value="autre">Autre</option>
                    </select>
                    <input type="url" name="visio_link" id="visio_link_input" placeholder="https://zoom.us/j/..." class="sec-form-control" style="font-size:12px;">
                </div>
            </div>

            {{-- Invitation --}}
            <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:10px; padding:14px;">
                <div style="font-size:12px; font-weight:700; color:#15803D; margin-bottom:10px;">
                    <i class="fas fa-envelope" style="margin-right:6px;"></i> Invitation email (optionnel)
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Nom de l'invité</label>
                        <input type="text" name="guest_name" placeholder="Ex: Jean Dupont" class="sec-form-control" style="font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Email de l'invité</label>
                        <input type="email" name="guest_email" placeholder="jean@exemple.com" class="sec-form-control" style="font-size:12px;">
                    </div>
                </div>
                <p style="font-size:10px; color:#64748b; margin:8px 0 0;">Si renseigné, un email de convocation sera envoyé automatiquement avec les détails du rendez-vous.</p>
            </div>

            <div class="sec-form-group">
                <label>Description / Notes</label>
                <textarea name="description" placeholder="Description du rendez-vous..." class="sec-form-control" rows="2" style="resize:none;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:4px;">
                <button type="button" class="sec-btn sec-btn-secondary" onclick="document.getElementById('rdvModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary"><i class="fas fa-calendar-check me-1"></i> Enregistrer le rendez-vous</button>
            </div>
        </form>
    </div>
</div>
@else
<div class="sec-card" style="padding:40px; text-align:center; color:var(--sec-text-muted);">
  <i class="fas fa-building" style="font-size:48px; margin-bottom:12px; color:#cbd5e1;"></i>
  <p style="font-size:14px; font-weight:600;">Aucune entreprise active n'est actuellement sélectionnée.</p>
  <p style="font-size:12px;">Veuillez utiliser le sélecteur situé dans l'en-tête pour choisir l'entreprise dont vous souhaitez gérer l'agenda.</p>
</div>
@endif
@endsection
