@extends('layouts.gel-secretary')
@section('title', 'Détails du Contact')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <a href="{{ route('gel-secretary.contacts.index', ['client_id' => request('client_id')]) }}" style="color:var(--sec-text-muted); text-decoration:none;"><i class="fas fa-arrow-left"></i></a> 
      Fiche Contact : {{ $contact->first_name }} {{ $contact->last_name }}
    </h1>
    <p class="sec-page-sub">{{ $contact->company }} - {{ $contact->category }}</p>
  </div>
  <div>
    <form action="{{ route('gel-secretary.contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?');" style="display:inline-block;">
      @csrf
      @method('DELETE')
      <input type="hidden" name="client_id" value="{{ request('client_id') }}">
      <button type="submit" class="sec-btn sec-btn-danger">
        <i class="fas fa-trash-alt"></i> Supprimer
      </button>
    </form>
  </div>
</div>

<div style="display:flex; gap:20px; flex-wrap:wrap;">
  
  <!-- Colonne Informations du Contact -->
  <div style="flex:1; min-width:350px;">
    <div class="sec-card animate-fade delay-1">
      <div class="sec-card-header" style="background:#F8FAFC;">
        <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fas fa-info-circle" style="color:var(--sec-primary); margin-right:8px;"></i> Informations</h3>
      </div>
      <div class="sec-card-body" style="padding:25px;">
        
        <div style="display:flex; align-items:center; gap:20px; margin-bottom:25px;">
          <div style="width:60px; height:60px; border-radius:50%; background:var(--sec-primary-light); color:var(--sec-primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:24px;">
            {{ strtoupper(substr($contact->first_name ?? 'C', 0, 1)) }}{{ strtoupper(substr($contact->last_name ?? '', 0, 1)) }}
          </div>
          <div>
            <div style="font-weight:800; font-size:18px; color:var(--sec-text);">{{ $contact->first_name }} {{ $contact->last_name }}</div>
            <div style="font-size:14px; color:var(--sec-text-muted);">{{ $contact->position ? $contact->position . ' chez ' : '' }}{{ $contact->company }}</div>
            <span class="sec-badge sec-badge-secondary" style="margin-top:5px;">{{ $contact->category }}</span>
          </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:15px; font-size:14px; color:var(--sec-text);">
          <div style="display:flex; gap:15px;">
            <i class="fas fa-phone-alt" style="color:#94a3b8; width:20px; text-align:center; padding-top:4px;"></i>
            <div>
              <div style="font-size:12px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase;">Téléphone</div>
              <div>
                @if($contact->phone)
                  <a href="tel:{{ $contact->phone }}" style="color:var(--sec-text); text-decoration:none; font-weight:600;">{{ $contact->phone }}</a>
                @else
                  <span style="color:#94a3b8;">Non renseigné</span>
                @endif
              </div>
            </div>
          </div>

          <div style="display:flex; gap:15px;">
            <i class="fas fa-envelope" style="color:#94a3b8; width:20px; text-align:center; padding-top:4px;"></i>
            <div>
              <div style="font-size:12px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase;">Email</div>
              <div>
                @if($contact->email)
                  <a href="mailto:{{ $contact->email }}" style="color:var(--sec-primary); text-decoration:none; font-weight:600;">{{ $contact->email }}</a>
                @else
                  <span style="color:#94a3b8;">Non renseigné</span>
                @endif
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Colonne Journal des Appels -->
  <div style="flex:2; min-width:450px;">
    
    <!-- Bouton Saisir Appel -->
    <div style="margin-bottom:20px; display:flex; justify-content:flex-end;">
      <button type="button" class="sec-btn sec-btn-primary" onclick="document.getElementById('modal-call').style.display='flex'">
        <i class="fas fa-phone-volume"></i> Consigner un appel
      </button>
    </div>

    <div class="sec-card animate-fade delay-2">
      <div class="sec-card-header" style="background:#F8FAFC;">
        <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fas fa-history" style="color:#64748B; margin-right:8px;"></i> Journal des Appels</h3>
      </div>
      <div class="sec-card-body" style="padding:0;">
        @if($callLogs->isEmpty())
          <div style="text-align:center; padding:40px; color:var(--sec-text-muted);">
            <div style="font-size:40px; margin-bottom:10px; color:#E2E8F0;"><i class="fas fa-phone-slash"></i></div>
            Aucun historique d'appel avec ce contact.
          </div>
        @else
          <ul style="list-style:none; padding:0; margin:0;">
            @foreach($callLogs as $call)
            <li style="padding:20px; border-bottom:1px solid var(--sec-border); display:flex; gap:15px;">
              <div style="width:40px; height:40px; border-radius:50%; background:{{ $call->direction == 'entrant' ? '#DCFCE7' : '#DBEAFE' }}; color:{{ $call->direction == 'entrant' ? '#15803D' : '#1D4ED8' }}; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                <i class="fas fa-arrow-{{ $call->direction == 'entrant' ? 'down' : 'up' }}"></i>
              </div>
              <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                  <span style="font-weight:700; font-size:14px; color:var(--sec-text);">Appel {{ ucfirst($call->direction) }}</span>
                  <span style="font-size:12px; color:var(--sec-text-muted);">{{ $call->called_at->translatedFormat('d M Y à H:i') }}</span>
                </div>
                
                @if($call->duration_minutes > 0)
                  <div style="font-size:12px; color:#64748B; margin-bottom:8px;"><i class="far fa-clock"></i> {{ $call->duration_minutes }} min</div>
                @endif
                
                <p style="font-size:13px; color:var(--sec-text); line-height:1.5; margin:0; padding:10px; background:#F1F5F9; border-radius:6px;">
                  {{ $call->notes }}
                </p>
                <div style="margin-top:8px; font-size:11px; color:#94a3b8; text-align:right;">
                  Par {{ $call->user->name ?? 'Utilisateur' }}
                </div>
              </div>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>

</div>

<!-- Modal Consigner Appel -->
<div id="modal-call" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
  <div class="sec-card" style="width:100%; max-width:500px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
    <div class="sec-card-header" style="display:flex; justify-content:space-between; align-items:center; background:#F8FAFC;">
      <h3 style="margin:0; font-size:16px; font-weight:700;">Consigner un appel</h3>
      <button onclick="document.getElementById('modal-call').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:16px; color:#94a3b8;"><i class="fas fa-times"></i></button>
    </div>
    <div class="sec-card-body" style="padding:25px;">
      <form action="{{ route('gel-secretary.contacts.storeCallLog', $contact->id) }}" method="POST">
        @csrf
        <input type="hidden" name="client_id" value="{{ request('client_id') }}">
        
        <div style="display:flex; gap:15px; margin-bottom:15px;">
          <div style="flex:1;">
            <label class="sec-label">Sens de l'appel *</label>
            <select name="direction" class="sec-input" required>
              <option value="entrant">Appel Entrant (Reçu)</option>
              <option value="sortant">Appel Sortant (Émis)</option>
            </select>
          </div>
          <div style="flex:1;">
            <label class="sec-label">Durée (minutes)</label>
            <input type="number" name="duration_minutes" class="sec-input" min="0" value="5">
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <label class="sec-label">Résumé / Notes de l'appel *</label>
          <textarea name="notes" class="sec-input" rows="4" required placeholder="Sujet abordé, actions à prendre..."></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="sec-btn sec-btn-secondary" onclick="document.getElementById('modal-call').style.display='none'">Annuler</button>
          <button type="submit" class="sec-btn sec-btn-primary">Enregistrer l'appel</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
