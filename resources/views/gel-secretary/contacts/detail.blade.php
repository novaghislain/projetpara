@extends('layouts.gel-secretary')
@section('title', ($contact->name ?? 'Contact') . ' — Fiche 360°')

@section('content')
<style>
  .pro-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--sec-border); }
  .pro-title { font-size: 18px; font-weight: 700; color: var(--sec-text); margin-bottom: 4px; }
  .pro-subtitle { font-size: 12px; color: var(--sec-text-muted); }
  
  .pro-btn {
    background: white; border: 1px solid var(--sec-border); color: var(--sec-text);
    padding: 7px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
  }
  .pro-btn:hover { background: #F8FAFC; border-color: #CBD5E1; }
  
  .pro-panel { background: white; border-radius: 8px; border: 1px solid var(--sec-border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; overflow: hidden; margin-bottom: 24px; }
  .panel-header { padding: 14px 16px; border-bottom: 1px solid var(--sec-border); display: flex; justify-content: space-between; align-items: center; background: #FAFAFA; }
  .panel-title { font-size: 13px; font-weight: 700; color: var(--sec-text); display: flex; align-items: center; gap: 8px; }
  .panel-body { padding: 16px; }

  .timeline { position: relative; padding-left: 24px; margin-top: 16px; }
  .timeline::before { content: ''; position: absolute; left: 6px; top: 0; bottom: 0; width: 2px; background: #E2E8F0; }
  .timeline-item { position: relative; margin-bottom: 20px; }
  .timeline-item:last-child { margin-bottom: 0; }
  .timeline-icon { position: absolute; left: -24px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: var(--sec-primary); border: 2px solid white; box-shadow: 0 0 0 2px #E2E8F0; }
  .timeline-content { background: #F8FAFC; padding: 12px; border-radius: 6px; border: 1px solid var(--sec-border); }
  .timeline-title { font-size: 13px; font-weight: 600; margin-bottom: 4px; }
  .timeline-meta { font-size: 11px; color: var(--sec-text-muted); }

  .contact-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
  .info-group { margin-bottom: 12px; }
  .info-label { font-size: 11px; color: var(--sec-text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
  .info-value { font-size: 14px; font-weight: 500; color: var(--sec-text); }
</style>

<div class="pro-header">
  <div>
    <div class="pro-title">
      <a href="{{ route('gel-secretary.contacts.index') }}" style="color:var(--sec-text-muted); text-decoration:none;"><i class="fas fa-arrow-left" style="font-size:14px; margin-right:8px;"></i></a>
      {{ $contact->name }}
    </div>
    <div class="pro-subtitle">Contact chez {{ $activeClient->nom_entreprise }}</div>
  </div>
  <div class="pro-actions">
    <a href="mailto:{{ $contact->email }}" class="pro-btn"><i class="fas fa-envelope"></i> Envoyer un email</a>
    <a href="tel:{{ $contact->phone }}" class="pro-btn"><i class="fas fa-phone"></i> Appeler</a>
  </div>
</div>

<div class="row">
  <!-- COLONNE GAUCHE : IDENTITÉ -->
  <div class="col-md-4">
    <div class="pro-panel" style="position: sticky; top: 80px;">
      <div class="panel-header">
        <div class="panel-title"><i class="fas fa-id-card text-primary"></i> Fiche d'identité</div>
      </div>
      <div class="panel-body">
        <div style="text-align:center; margin-bottom: 24px;">
          <div style="width: 80px; height: 80px; border-radius: 50%; background: #E2E8F0; color: #475569; font-size: 32px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
            {{ strtoupper(substr($contact->name, 0, 1)) }}
          </div>
          <div style="font-size: 16px; font-weight: 700;">{{ $contact->name }}</div>
          <div style="font-size: 13px; color: var(--sec-text-muted);">{{ $contact->position ?? 'Fonction non précisée' }}</div>
        </div>

        <div class="contact-info-grid">
          <div class="info-group">
            <div class="info-label">Email</div>
            <div class="info-value">{{ $contact->email ?? '—' }}</div>
          </div>
          <div class="info-group">
            <div class="info-label">Téléphone</div>
            <div class="info-value">{{ $contact->phone ?? '—' }}</div>
          </div>
          <div class="info-group" style="grid-column: span 2;">
            <div class="info-label">Entreprise liée</div>
            <div class="info-value">{{ $activeClient->nom_entreprise }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- COLONNE DROITE : HISTORIQUE 360 -->
  <div class="col-md-8">
    
    <ul class="nav nav-tabs" id="myTab" role="tablist" style="border-bottom:1px solid var(--sec-border); margin-bottom:20px;">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" style="font-size:13px; font-weight:600; padding:10px 16px; border:none; background:transparent; border-bottom:2px solid var(--sec-primary); color:var(--sec-primary);" id="activite-tab" data-bs-toggle="tab" data-bs-target="#activite" type="button" role="tab">Activité Récente</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" style="font-size:13px; font-weight:600; padding:10px 16px; border:none; background:transparent; color:var(--sec-text-muted);" id="taches-tab" data-bs-toggle="tab" data-bs-target="#taches" type="button" role="tab">Tâches</button>
      </li>
    </ul>

    <div class="tab-content">
      <!-- ACTIVITÉ RÉCENTE -->
      <div class="tab-pane fade show active" id="activite" role="tabpanel">
        <div class="pro-panel">
          <div class="panel-header">
            <div class="panel-title"><i class="fas fa-history text-muted"></i> Chronologie (Appels & RDV)</div>
          </div>
          <div class="panel-body">
            @if($calls->count() == 0 && $events->count() == 0)
              <div style="text-align:center; padding:32px; color:var(--sec-text-muted); font-size:13px;">Aucune activité récente pour ce contact ou cette entreprise.</div>
            @else
              <div class="timeline">
                @foreach($calls as $call)
                  <div class="timeline-item">
                    <div class="timeline-icon" style="background: #3B82F6;"></div>
                    <div class="timeline-content">
                      <div class="timeline-title"><i class="fas fa-phone" style="font-size:10px; margin-right:4px;"></i> Appel téléphonique ({{ $call->direction }})</div>
                      <div class="timeline-meta">{{ \Carbon\Carbon::parse($call->called_at)->format('d/m/Y H:i') }} • Résumé: {{ $call->summary ?? '—' }}</div>
                    </div>
                  </div>
                @endforeach
                
                @foreach($events as $event)
                  <div class="timeline-item">
                    <div class="timeline-icon" style="background: #10B981;"></div>
                    <div class="timeline-content">
                      <div class="timeline-title"><i class="fas fa-calendar" style="font-size:10px; margin-right:4px;"></i> Rendez-vous</div>
                      <div class="timeline-meta">{{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }} • {{ $event->title }}</div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- TÂCHES -->
      <div class="tab-pane fade" id="taches" role="tabpanel">
        <div class="pro-panel">
          <div class="panel-header">
            <div class="panel-title"><i class="fas fa-check-square text-success"></i> Tâches liées</div>
          </div>
          <div class="panel-body p-0">
            @forelse($tasks as $task)
              <div style="padding:12px 16px; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:13px; font-weight:600;">{{ $task->titre }}</div>
                  <div style="font-size:11px; color:var(--sec-text-muted);">{{ \Illuminate\Support\Str::limit($task->description, 50) }}</div>
                </div>
                <div style="font-size:10px; font-weight:700; padding:4px 8px; border-radius:4px; background:#F1F5F9;">
                  {{ str_replace('_', ' ', strtoupper($task->statut)) }}
                </div>
              </div>
            @empty
              <div style="text-align:center; padding:32px; color:var(--sec-text-muted); font-size:13px;">Aucune tâche liée.</div>
            @endforelse
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Simple tab styling sync
  document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
    btn.addEventListener('shown.bs.tab', e => {
      document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(b => {
        b.style.borderBottom = 'none';
        b.style.color = 'var(--sec-text-muted)';
      });
      e.target.style.borderBottom = '2px solid var(--sec-primary)';
      e.target.style.color = 'var(--sec-primary)';
    });
  });
</script>
@endsection
