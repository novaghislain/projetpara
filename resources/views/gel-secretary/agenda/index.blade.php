@extends('layouts.gel-secretary')
@section('title', 'Agenda')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title"><i class="fas fa-calendar-alt" style="color:var(--sec-primary); margin-right:8px;"></i>Agenda & Événements</h1>
    <p class="sec-page-sub">Gestion des rendez-vous, réunions et échéances du cabinet.</p>
  </div>
  <div>
    <button type="button" class="sec-btn sec-btn-primary" onclick="document.getElementById('modal-event').style.display='flex'">
      <i class="fas fa-plus"></i> Nouvel Événement
    </button>
  </div>
</div>

<div style="display:flex; gap:20px; flex-wrap:wrap;">
  <!-- Colonne Principale: Calendrier Rapide (Liste pour le moment) -->
  <div style="flex:2; min-width:600px;">
    
    <!-- Filtres rapides -->
    <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
      <div style="display:flex; gap:10px;">
        <a href="?client_id={{ request('client_id') }}&view=month&date={{ $carbonDate->format('Y-m-d') }}" class="sec-btn {{ $view == 'month' ? 'sec-btn-primary' : 'sec-btn-secondary' }}">Mois</a>
        <a href="?client_id={{ request('client_id') }}&view=week&date={{ $carbonDate->format('Y-m-d') }}" class="sec-btn {{ $view == 'week' ? 'sec-btn-primary' : 'sec-btn-secondary' }}">Semaine</a>
        <a href="?client_id={{ request('client_id') }}&view=day&date={{ $carbonDate->format('Y-m-d') }}" class="sec-btn {{ $view == 'day' ? 'sec-btn-primary' : 'sec-btn-secondary' }}">Jour</a>
      </div>
      
      <div style="display:flex; align-items:center; gap:15px; font-weight:bold;">
        <a href="?client_id={{ request('client_id') }}&view={{ $view }}&date={{ $carbonDate->copy()->sub(1, $view == 'month' ? 'month' : ($view == 'week' ? 'week' : 'day'))->format('Y-m-d') }}" style="color:var(--sec-text-muted);"><i class="fas fa-chevron-left"></i></a>
        
        <span style="font-size:16px;">
          @if($view == 'month')
            {{ ucfirst($carbonDate->translatedFormat('F Y')) }}
          @elseif($view == 'week')
            Semaine du {{ $carbonDate->copy()->startOfWeek()->format('d/m') }} au {{ $carbonDate->copy()->endOfWeek()->format('d/m') }}
          @else
            {{ ucfirst($carbonDate->translatedFormat('l d F Y')) }}
          @endif
        </span>

        <a href="?client_id={{ request('client_id') }}&view={{ $view }}&date={{ $carbonDate->copy()->add(1, $view == 'month' ? 'month' : ($view == 'week' ? 'week' : 'day'))->format('Y-m-d') }}" style="color:var(--sec-text-muted);"><i class="fas fa-chevron-right"></i></a>
      </div>
    </div>

    <!-- Liste des évènements -->
    <div class="sec-card animate-fade delay-1">
      <div class="sec-card-header">
        <h3 style="margin:0; font-size:16px; font-weight:700;">Événements de la période</h3>
      </div>
      <div class="sec-card-body" style="padding:0;">
        @if($events->isEmpty())
          <div style="text-align:center; padding:40px; color:var(--sec-text-muted);">
            <div style="font-size:40px; margin-bottom:10px; color:#E2E8F0;"><i class="fas fa-calendar-times"></i></div>
            Aucun événement prévu pour cette période.
          </div>
        @else
          <ul style="list-style:none; padding:0; margin:0;">
            @foreach($events as $event)
            <li style="padding:20px; border-bottom:1px solid var(--sec-border); display:flex; gap:20px; align-items:flex-start;">
              <div style="width:50px; text-align:center;">
                <div style="font-size:12px; font-weight:700; color:#EF4444; text-transform:uppercase;">{{ optional($event->start_at)->translatedFormat('M') }}</div>
                <div style="font-size:24px; font-weight:800; line-height:1;">{{ optional($event->start_at)->format('d') }}</div>
                <div style="font-size:11px; color:var(--sec-text-muted); margin-top:4px;">{{ optional($event->start_at)->translatedFormat('l') }}</div>
              </div>
              
              <div style="flex:1;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px;">
                  <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $event->couleur }};"></span>
                  <div style="font-weight:700; font-size:16px; color:var(--sec-text);">{{ $event->title }}</div>
                  @if($event->statut === 'annule')
                    <span class="sec-badge sec-badge-warning">Annulé</span>
                  @endif
                </div>
                
                <div style="display:flex; gap:15px; font-size:13px; color:var(--sec-text-muted); margin-bottom:10px;">
                  <span><i class="far fa-clock" style="margin-right:4px;"></i> {{ $event->all_day ? 'Toute la journée' : optional($event->start_at)->format('H:i') . ' - ' . optional($event->end_at)->format('H:i') }}</span>
                  @if($event->location)
                    <span><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i> {{ $event->location }}</span>
                  @endif
                  @if($event->visio_link)
                    <a href="{{ $event->visio_link }}" target="_blank" style="color:var(--sec-primary); text-decoration:none;"><i class="fas fa-video" style="margin-right:4px;"></i> Rejoindre Visio</a>
                  @endif
                </div>
                
                @if($event->description)
                  <p style="font-size:13px; color:var(--sec-text); margin:0; padding:10px; background:#F8FAFC; border-radius:6px; border-left:3px solid {{ $event->couleur }};">{{ $event->description }}</p>
                @endif
              </div>

              <div>
                <form action="{{ route('gel-secretary.agenda.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Supprimer cet événement ?');">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                  <button class="sec-btn" style="color:#EF4444; background:none; border:none; cursor:pointer;" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                </form>
              </div>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>

  <!-- Colonne Latérale: Prochains RDV -->
  <div style="flex:1; min-width:300px; max-width:350px;">
    <div class="sec-card animate-fade delay-2">
      <div class="sec-card-header" style="background:#F8FAFC;">
        <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fas fa-bolt" style="color:#F59E0B; margin-right:6px;"></i> Prochains événements</h3>
      </div>
      <div class="sec-card-body" style="padding:0;">
        @if($upcoming->isEmpty())
          <div style="padding:20px; text-align:center; color:var(--sec-text-muted); font-size:13px;">Rien de prévu prochainement.</div>
        @else
          <ul style="list-style:none; padding:0; margin:0;">
            @foreach($upcoming as $up)
            <li style="padding:15px; border-bottom:1px solid var(--sec-border);">
              <div style="font-size:12px; color:var(--sec-primary); font-weight:600; margin-bottom:4px;">
                {{ optional($up->start_at)->translatedFormat('d M Y - H:i') }}
              </div>
              <div style="font-size:14px; font-weight:600; color:var(--sec-text);">{{ $up->title }}</div>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Modal Nouvel Événement -->
<div id="modal-event" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
  <div class="sec-card" style="width:100%; max-width:600px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
    <div class="sec-card-header" style="display:flex; justify-content:space-between; align-items:center; background:#F8FAFC;">
      <h3 style="margin:0; font-size:16px; font-weight:700;">Ajouter à l'agenda</h3>
      <button onclick="document.getElementById('modal-event').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:#94a3b8;"><i class="fas fa-times"></i></button>
    </div>
    <div class="sec-card-body" style="padding:25px;">
      <form action="{{ route('gel-secretary.agenda.store') }}" method="POST">
        @csrf
        <input type="hidden" name="client_id" value="{{ request('client_id') }}">
        
        <div style="margin-bottom:15px;">
          <label class="sec-label">Titre de l'événement *</label>
          <input type="text" name="title" class="sec-input" required placeholder="Ex: Réunion client, Visite fiscale...">
        </div>
        
        <div style="display:flex; gap:15px; margin-bottom:15px;">
          <div style="flex:1;">
            <label class="sec-label">Type</label>
            <select name="type" class="sec-input" required>
              <option value="reunion">Réunion</option>
              <option value="rendez_vous">Rendez-vous</option>
              <option value="echeance">Échéance Fiscale/Sociale</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div style="flex:1;">
            <label class="sec-label">Couleur</label>
            <input type="color" name="couleur" class="sec-input" value="#3B82F6" style="padding:4px; height:38px; cursor:pointer;">
          </div>
        </div>

        <div style="display:flex; gap:15px; margin-bottom:15px;">
          <div style="flex:1;">
            <label class="sec-label">Début *</label>
            <input type="datetime-local" name="start_at" class="sec-input" required value="{{ date('Y-m-d\TH:i') }}">
          </div>
          <div style="flex:1;">
            <label class="sec-label">Fin</label>
            <input type="datetime-local" name="end_at" class="sec-input" value="{{ date('Y-m-d\TH:i', strtotime('+1 hour')) }}">
          </div>
        </div>
        
        <div style="margin-bottom:15px;">
          <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--sec-text); cursor:pointer;">
            <input type="checkbox" name="all_day" value="1">
            Événement sur toute la journée
          </label>
        </div>

        <div style="display:flex; gap:15px; margin-bottom:15px;">
          <div style="flex:1;">
            <label class="sec-label">Lieu physique</label>
            <input type="text" name="location" class="sec-input" placeholder="Salle de réunion, Adresse...">
          </div>
          <div style="flex:1;">
            <label class="sec-label">Lien Visio</label>
            <input type="url" name="visio_link" class="sec-input" placeholder="https://zoom.us/...">
          </div>
        </div>
        
        <div style="margin-bottom:20px;">
          <label class="sec-label">Description (Optionnel)</label>
          <textarea name="description" class="sec-input" rows="3" placeholder="Ordre du jour, notes préparatoires..."></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="sec-btn sec-btn-secondary" onclick="document.getElementById('modal-event').style.display='none'">Annuler</button>
          <button type="submit" class="sec-btn sec-btn-primary">Enregistrer l'événement</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
