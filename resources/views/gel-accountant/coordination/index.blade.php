@extends('layouts.gel-accountant')
@section('title', 'Coordination Comptable ↔ Secrétaire')

@section('content')
@php $user = auth()->user(); @endphp

<style>
  .coord-card { background:#fff; border:1px solid #E2E8F0; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.05); }
  .coord-card .card-head { padding:16px 20px; border-bottom:1px solid #F1F5F9; font-weight:700; font-size:14px; color:#0F172A; display:flex; align-items:center; gap:10px; }
  .coord-card .card-body { padding:18px 20px; }
  .accordion-icon { cursor:pointer; transition:transform .2s; }
  .accordion-icon.open { transform:rotate(90deg); }
  
  .activity-timeline {
    position: relative;
    padding-left: 20px;
    margin-top: 10px;
  }
  .activity-timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 7px;
    width: 2px;
    background: #E2E8F0;
  }
  .timeline-item {
    position: relative;
    margin-bottom: 24px;
  }
  .timeline-item:last-child {
    margin-bottom: 0;
  }
  .timeline-icon {
    position: absolute;
    left: -20px;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    border: 2px solid #0D9488;
    color: #0D9488;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    z-index: 2;
  }
  .timeline-content {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 16px;
    margin-left: 24px;
    position: relative;
    transition: all 0.2s ease;
  }
  .timeline-content:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }
</style>

<div class="animate-fade">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div>
      <div style="font-size:20px; font-weight:800; color:#0F172A;">Coordination — {{ $client->nom_entreprise }}</div>
      <div style="font-size:13px; color:#64748B;">Espace dédié Comptable ↔ Secrétaire (S4.1). Actions journalisées dans l'Historique.</div>
    </div>
    @if($secretaire)
      <div style="background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:8px 16px; display:flex; align-items:center; gap:10px;">
        <div style="width:38px;height:38px;border-radius:50%;background:#0D9488;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">{{ strtoupper(substr($secretaire->name ?? 'S', 0, 2)) }}</div>
        <div>
          <div style="font-weight:700; font-size:13px; color:#0F172A;">{{ $secretaire->name }}</div>
          <div style="font-size:11px; color:#059669;"><i class="fas fa-circle" style="font-size:7px;"></i> Secrétaire rattachée</div>
        </div>
      </div>
    @else
      <div style="background:#FEF3C7; border:1px solid #F59E0B; border-radius:12px; padding:8px 14px; font-size:12px; color:#92400E;">
        <i class="fas fa-triangle-exclamation me-1"></i> Aucune secrétaire rattachée à cette entreprise.
      </div>
    @endif
  </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;" class="animate-fade delay-1">

  {{-- ─── S2.1 — Accusé de réception des documents transmis ───────────────── --}}
  <div class="coord-card">
    <div class="card-head"><i class="fas fa-paper-plane" style="color:#0D9488;"></i> Documents transmis par le secrétariat</div>
    <div class="card-body">
      @forelse($documentsTransmis as $doc)
        <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #F1F5F9;">
          <div style="width:38px;height:38px;border-radius:8px;background:rgba(13,148,136,.1);color:#0D9488;display:flex;align-items:center;justify-content:center;"><i class="fas fa-file-lines"></i></div>
          <div style="flex:1; min-width:0;">
            <div style="font-weight:600; font-size:13px; color:#0F172A;">{{ $doc->name }}</div>
            <div style="font-size:11px; color:#94A3B8;">Transmis le {{ optional($doc->transmitted_at)->format('d/m/Y H:i') }}</div>
          </div>
          @if($doc->workflow_step === 'en_traitement_comptable')
            <span style="background:#D1FAE5; color:#059669; font-weight:700; font-size:11px; padding:4px 10px; border-radius:20px;"><i class="fas fa-check-circle me-1"></i>Pris en charge</span>
          @elseif($doc->workflow_step === 'valide')
            <span style="background:#DBEAFE; color:#2563EB; font-weight:700; font-size:11px; padding:4px 10px; border-radius:20px;"><i class="fas fa-check-double me-1"></i>Validé</span>
          @else
            <form method="POST" action="{{ route('gel-accountant.coordination.accuser-reception') }}" style="margin:0;">
              @csrf
              <input type="hidden" name="document_id" value="{{ $doc->id }}">
              <button type="submit" class="btn btn-sm" style="background:#0D9488; color:#fff; border-radius:20px; font-size:11px; padding:4px 12px;"><i class="fas fa-check me-1"></i> Accuser réception</button>
            </form>
          @endif
        </div>
      @empty
        <div style="padding:40px; text-align:center; color:#94A3B8;">
          <i class="fas fa-inbox" style="font-size:30px; color:#CBD5E1;"></i>
          <div style="margin-top:8px;">Aucun document transmis en attente.</div>
        </div>
      @endforelse
    </div>
  </div>

  {{-- ─── S3.1 — Demande au secrétariat + S3.3 — Alerte ───────────────────── --}}
  <div class="coord-card">
    <div class="card-head"><i class="fas fa-hand-holding-heart" style="color:#0D9488;"></i> Demander une information au secrétariat</div>
    <div class="card-body">
      <form method="POST" action="{{ route('gel-accountant.coordination.request-document') }}">
        @csrf
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
          <div>
            <label style="font-size:11px; color:#64748B; font-weight:600;">Intitulé de la demande *</label>
            <input type="text" name="intitule" required class="form-control form-control-sm" placeholder="Ex : Attestation fiscale">
          </div>
          <div>
            <label style="font-size:11px; color:#64748B; font-weight:600;">Échéance</label>
            <input type="date" name="date_echeance" class="form-control form-control-sm">
          </div>
        </div>
        <div style="margin-top:10px;">
          <label style="font-size:11px; color:#64748B; font-weight:600;">Détails *</label>
          <textarea name="description" required rows="2" class="form-control form-control-sm" placeholder="Ce que vous attendez de la secrétaire..."></textarea>
        </div>
        <div style="margin-top:10px;">
          <label style="font-size:11px; color:#64748B; font-weight:600;">Priorité</label>
          <select name="priorite" class="form-control form-control-sm">
            <option value="moyenne">Moyenne</option>
            <option value="basse">Basse</option>
            <option value="haute">Haute</option>
            <option value="critique">Critique</option>
          </select>
        </div>
        <button type="submit" class="btn btn-sm mt-2" style="background:#0D9488; color:#fff; border-radius:8px;"><i class="fas fa-paper-plane me-1"></i> Transmettre au secrétariat (Kanban)</button>
      </form>

      <hr style="border-top:1px dashed #E2E8F0; margin:18px 0;">
      <div style="font-weight:700; font-size:12px; color:#DC2626;"><i class="fas fa-triangle-exclamation me-1"></i> Alerte réglementaire (échéance administrative)</div>
      <form method="POST" action="{{ route('gel-accountant.coordination.send-alert') }}" style="margin-top:10px;">
        @csrf
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
          <div>
            <label style="font-size:11px; color:#64748B; font-weight:600;">Titre de l'alerte *</label>
            <input type="text" name="titre" required class="form-control form-control-sm" placeholder="Ex : Dépôt déclaration CNSS">
          </div>
          <div>
            <label style="font-size:11px; color:#64748B; font-weight:600;">Date limite *</label>
            <input type="date" name="date_echeance" required class="form-control form-control-sm">
          </div>
        </div>
        <div style="margin-top:10px;">
          <textarea name="description" required rows="2" class="form-control form-control-sm" placeholder="Détails de l'échéance à traiter..."></textarea>
        </div>
        <button type="submit" class="btn btn-sm mt-2" style="background:#DC2626; color:#fff; border-radius:8px;"><i class="fas fa-bell me-1"></i> Créer l'alerte (Agenda + tâche)</button>
      </form>
    </div>
  </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;" class="animate-fade delay-2">

  {{-- ─── S4.1 — Messagerie de coordination ───────────────────────────────── --}}
  <div class="coord-card" style="display:flex; flex-direction:column; height:450px;">
    <div class="card-head" style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #E2E8F0;">
        <div><i class="fas fa-comments" style="color:#0D9488; margin-right:8px;"></i> Messagerie de coordination</div>
        <div style="font-size:11px; color:#94A3B8; font-weight:500;">Canal sécurisé avec le secrétariat</div>
    </div>
    <div id="chat-messages" class="card-body" style="flex:1; padding:20px; overflow-y:auto; display:flex; flex-direction:column; gap:16px; background:#F8FAFC;">
        @forelse($messages as $m)
          <div style="max-width:85%; padding:12px 16px; border-radius:14px; font-size:13.5px; line-height:1.5; align-self:{{ $m->sender_id === $user->id ? 'flex-end' : 'flex-start' }}; background:{{ $m->sender_id === $user->id ? '#0D9488' : '#fff' }}; color:{{ $m->sender_id === $user->id ? '#fff' : '#1E293B' }}; box-shadow:0 2px 4px rgba(0,0,0,0.04); border:{{ $m->sender_id === $user->id ? 'none' : '1px solid #E2E8F0' }}; border-bottom-{{ $m->sender_id === $user->id ? 'right' : 'left' }}-radius: 2px;">
            <div style="font-weight:700; font-size:11px; margin-bottom:6px; color:{{ $m->sender_id === $user->id ? 'rgba(255,255,255,.9)' : '#64748B' }}; display:flex; justify-content:space-between; gap:12px;">
                <span>{{ $m->sender->name ?? 'Vous' }}</span>
                <span style="font-weight:400; font-size:10px;">{{ $m->created_at->format('H:i') }}</span>
            </div>
            @if($m->message)
                <div style="margin-bottom:{{ $m->piece_jointe ? '8px' : '0' }};">{{ $m->message }}</div>
            @endif
            @if($m->piece_jointe)
                <a href="{{ asset('storage/' . $m->piece_jointe) }}" target="_blank" style="display:inline-flex; align-items:center; gap:8px; background:{{ $m->sender_id === $user->id ? 'rgba(0,0,0,0.15)' : '#F1F5F9' }}; padding:6px 12px; border-radius:6px; color:inherit; text-decoration:none; font-size:12px; font-weight:600; transition:opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                    <i class="fa-solid fa-paperclip"></i>
                    Pièce jointe
                </a>
            @endif
          </div>
        @empty
          <div id="no-messages" style="color:#94A3B8; font-size:13px; text-align:center; margin:auto;">Commencez la discussion...</div>
        @endforelse
    </div>
    
    <div style="padding:16px; border-top:1px solid #E2E8F0; background:white; border-bottom-left-radius:12px; border-bottom-right-radius:12px;">
        <form id="chat-form" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="client_id" id="chat-client-id" value="{{ $client->id }}">
            
            <label for="chat-attachment" style="cursor:pointer; padding:10px; background:#F1F5F9; border-radius:50%; color:#64748B; transition:all 0.2s;" onmouseover="this.style.background='#E2E8F0'; this.style.color='#0D9488'" onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B'">
                <i class="fa-solid fa-paperclip"></i>
            </label>
            <input type="file" id="chat-attachment" name="attachment" style="display:none;" onchange="updateFileName(this)">
            
            <div style="flex:1; display:flex; flex-direction:column; position:relative;">
                <div id="file-preview" style="display:none; font-size:11px; font-weight:600; color:#0D9488; margin-bottom:4px; padding:2px 8px; background:#F0FDF4; border-radius:4px; width:fit-content; border:1px solid #CCFBF1;">
                    <i class="fa-solid fa-file-lines me-1"></i> <span id="file-name-text">fichier</span>
                    <i class="fa-solid fa-times ms-2" style="cursor:pointer;" onclick="removeAttachment()"></i>
                </div>
                <input type="text" id="chat-message" name="message" placeholder="Écrivez votre message..." class="form-control" style="border-radius:20px; border:1px solid #E2E8F0; padding:10px 16px; background:#F8FAFC;" autocomplete="off">
            </div>
            
            <button type="submit" id="chat-submit" class="btn btn-primary" style="background-color:#0D9488; border-color:#0D9488; border-radius:20px; padding:10px 20px; font-weight:600;">
                <span id="btn-text">Envoyer</span>
                <i class="fa-solid fa-paper-plane ms-1"></i>
            </button>
        </form>
    </div>
  </div>

  {{-- ─── S4.3 — Fil d'activité commun ───────────────────────────────────── --}}
  <div class="coord-card">
    <div class="card-head"><i class="fas fa-timeline" style="color:#0D9488;"></i> Historique des actions & Échanges ({{ $client->nom_entreprise }})</div>
    <div class="card-body" style="max-height:420px; overflow-y:auto;">
      @if(count($activity) > 0)
        <div class="activity-timeline">
            @foreach($activity as $evt)
                <div class="timeline-item">
                    <div class="timeline-icon" style="{{ str_contains($evt->icon, 'check') ? 'color:#10B981; border-color:#10B981;' : (str_contains($evt->icon, 'paper-plane') ? 'color:#3B82F6; border-color:#3B82F6;' : (str_contains($evt->icon, 'exclamation') ? 'color:#EF4444; border-color:#EF4444;' : 'color:#0D9488; border-color:#0D9488;')) }}">
                        <i class="{{ $evt->icon ?? 'fas fa-bolt' }}"></i>
                    </div>
                    <div class="timeline-content">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                            <div style="font-size:14px; font-weight:700; color:#1E293B;">{{ $evt->subject }}</div>
                            <div style="font-size:11px; color:#64748B; font-weight:600; background:#F1F5F9; padding:4px 8px; border-radius:20px; white-space:nowrap; border:1px solid #E2E8F0;">
                                <i class="far fa-clock me-1"></i> {{ $evt->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @if($evt->body)
                        <div style="font-size:13px; color:#475569; line-height:1.5; margin-bottom:12px; background:#fff; padding:10px 12px; border-radius:8px; border-left:3px solid #cbd5e1;">
                            {{ $evt->body }}
                        </div>
                        @endif
                        <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:#94A3B8;">
                            <div style="width:24px; height:24px; border-radius:50%; background:#E2E8F0; color:#475569; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700;">
                                {{ strtoupper(substr($evt->actor_name ?? $evt->actor?->name ?? 'S', 0, 1)) }}
                            </div>
                            <span>Par <strong style="color:#64748B;">{{ $evt->actor_name ?? $evt->actor?->name ?? 'Système' }}</strong></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
      @else
        <div style="padding:48px 20px; text-align:center; color:#94A3B8;">
            <div style="font-size:40px; color:#E2E8F0; margin-bottom:16px;"><i class="fas fa-ghost"></i></div>
            <div style="font-size:15px; font-weight:600; color:#475569; margin-bottom:4px;">Aucune activité enregistrée</div>
            <div style="font-size:13px;">Les échanges et actions apparaîtront ici.</div>
        </div>
      @endif
    </div>
  </div>
</div>

@push('scripts')
  <script>
    function updateFileName(input) {
        if(input.files && input.files.length > 0) {
            document.getElementById('file-preview').style.display = 'inline-block';
            document.getElementById('file-name-text').innerText = input.files[0].name;
            document.getElementById('chat-message').placeholder = "Ajouter un commentaire (optionnel)...";
        } else {
            removeAttachment();
        }
    }
    function removeAttachment() {
        let input = document.getElementById('chat-attachment');
        input.value = "";
        document.getElementById('file-preview').style.display = 'none';
        document.getElementById('chat-message').placeholder = "Écrivez votre message...";
    }
    
    const chatContainer = document.getElementById('chat-messages');
    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

    document.getElementById('chat-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const messageInput = document.getElementById('chat-message');
        const attachmentInput = document.getElementById('chat-attachment');
        const submitBtn = document.getElementById('chat-submit');
        const btnText = document.getElementById('btn-text');
        
        if(!messageInput.value.trim() && (!attachmentInput.files || attachmentInput.files.length === 0)) return;
        
        const formData = new FormData(this);
        
        const noMessages = document.getElementById('no-messages');
        if (noMessages) noMessages.remove();
        
        submitBtn.disabled = true;
        btnText.innerText = 'Envoi...';
        
        try {
            const res = await fetch("{{ route('gel-accountant.coordination.send-message') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();
            
            if(data.success && data.message) {
                appendMessage(data.message, true);
                messageInput.value = '';
                removeAttachment();
                chatContainer.scrollTop = chatContainer.scrollHeight;
            } else {
                alert(data.error || 'Erreur lors de l\'envoi');
            }
        } catch(err) {
            console.error(err);
            alert('Erreur réseau');
        } finally {
            submitBtn.disabled = false;
            btnText.innerText = 'Envoyer';
        }
    });
    
    function appendMessage(m, isMe) {
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        const align = isMe ? 'flex-end' : 'flex-start';
        const bg = isMe ? '#0D9488' : '#fff';
        const color = isMe ? '#fff' : '#1E293B';
        const borderColor = isMe ? 'none' : '1px solid #E2E8F0';
        const radiusStyle = isMe ? 'border-bottom-right-radius:2px;' : 'border-bottom-left-radius:2px;';
        const senderName = isMe ? 'Vous' : (m.sender ? m.sender.name : 'Secrétariat');
        const headerColor = isMe ? 'rgba(255,255,255,.9)' : '#64748B';
        const linkBg = isMe ? 'rgba(0,0,0,0.15)' : '#F1F5F9';
        
        let attachmentHtml = '';
        if (m.piece_jointe) {
            attachmentHtml = `<a href="/storage/${m.piece_jointe}" target="_blank" style="display:inline-flex; align-items:center; gap:8px; background:${linkBg}; padding:6px 12px; border-radius:6px; color:inherit; text-decoration:none; font-size:12px; font-weight:600; transition:opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                <i class="fa-solid fa-paperclip"></i> Pièce jointe
                              </a>`;
        }
        let messageHtml = m.message ? `<div style="margin-bottom:${m.piece_jointe ? '8px' : '0'};">${m.message}</div>` : '';
        
        const html = `
          <div style="max-width:85%; padding:12px 16px; border-radius:14px; font-size:13.5px; line-height:1.5; align-self:${align}; background:${bg}; color:${color}; box-shadow:0 2px 4px rgba(0,0,0,0.04); border:${borderColor}; ${radiusStyle}">
              <div style="font-weight:700; font-size:11px; margin-bottom:6px; color:${headerColor}; display:flex; justify-content:space-between; gap:12px;">
                  <span>${senderName}</span>
                  <span style="font-weight:400; font-size:10px;">${time}</span>
              </div>
              ${messageHtml}
              ${attachmentHtml}
          </div>
        `;
        chatContainer.insertAdjacentHTML('beforeend', html);
    }

    (function () {
      if (!window.Echo) return;
      try {
        window.Echo.private('chat.coordination.{{ $client->id }}')
          .listen('.CoordinationActivityEvent', function (e) {
            if (typeof showToast === 'function' && e && e.activity && e.activity.subject) {
              showToast(e.activity.subject, 'info');
            }
          })
          .listen('.MessageEnvoyeEvent', function (e) {
            if (e.message && e.message.sender_id !== {{ $user->id }}) {
                if (typeof showToast === 'function') showToast('Nouveau message du secrétariat', 'info');
                const noMessages = document.getElementById('no-messages');
                if (noMessages) noMessages.remove();
                appendMessage(e.message, false);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
          });
      } catch (err) { console.warn('coordination echo', err); }
    })();
  </script>
@endpush
@endsection