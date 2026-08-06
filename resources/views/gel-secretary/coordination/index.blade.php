@extends('layouts.gel-secretary')
@section('title', 'Coordination Secrétaire ↔ Comptable')

@section('content')
@php
  $user = auth()->user();
@endphp

<style>
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
    border: 2px solid var(--sec-primary);
    color: var(--sec-primary);
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

<div class="pro-header animate-fade">
  <div>
    <div class="pro-title">Coordination — {{ $client->nom_entreprise }}</div>
    <div class="pro-subtitle">Espace dédié Secrétaire ↔ Comptable (S4.1). Chaque échange est journalisé dans l'Historique.</div>
  </div>
  <div style="display:flex; align-items:center; gap:12px;">
    @if($comptable)
      <div style="background:#fff; border:1px solid var(--sec-border); border-radius:12px; padding:8px 16px; display:flex; align-items:center; gap:10px;">
        <div class="sec-avatar" style="background: rgba(13,148,136,.12); color:var(--sec-primary); width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;">
          {{ strtoupper(substr($comptable->name ?? 'C', 0, 2)) }}
        </div>
        <div>
          <div style="font-weight:700; font-size:13px; color:var(--sec-text);">{{ $comptable->name }}</div>
          <div style="font-size:11px; color:#10B981;"><i class="fas fa-circle" style="font-size:7px;"></i> Comptable rattaché ({{ $comptable->role }})</div>
        </div>
      </div>
    @else
      <div style="background:#FEF3C7; border:1px solid #F59E0B; border-radius:12px; padding:8px 14px; font-size:12px; color:#92400E;">
        <i class="fas fa-triangle-exclamation me-1"></i> Aucun comptable rattaché à cette entreprise.
      </div>
    @endif
  </div>
</div>

<div class="animate-fade delay-1 toolbar" style="margin-top:20px; display:flex; gap:8px; flex-wrap:wrap;">
  <a href="{{ route('gel-secretary.coordination.index', ['client_id' => $client->id]) }}" class="btn btn-sm" style="{{ request()->get('view')==='feed' ? '' : 'background:var(--sec-primary);color:#fff;' }} border:1px solid var(--sec-border);">🧭 Vue d'ensemble</a>
  <a href="{{ route('gel-secretary.coordination.index', ['client_id' => $client->id, 'view' => 'transmis']) }}" class="btn btn-sm" style="{{ request()->get('view')==='transmis' ? 'background:var(--sec-primary);color:#fff;' : '' }} border:1px solid var(--sec-border);">📤 Documents transmis ({{ $transmis->count() }})</a>
  <a href="{{ route('gel-secretary.coordination.index', ['client_id' => $client->id, 'view' => 'demandes']) }}" class="btn btn-sm" style="{{ request()->get('view')==='demandes' ? 'background:var(--sec-primary);color:#fff;' : '' }} border:1px solid var(--sec-border);">📥 Demandes comptable ({{ $demandesRecues->count() }})</a>
  <a href="{{ route('gel-secretary.coordination.index', ['client_id' => $client->id, 'view' => 'fil']) }}" class="btn btn-sm" style="{{ request()->get('view')==='fil' ? 'background:var(--sec-primary);color:#fff;' : '' }} border:1px solid var(--sec-border);">📋 Fil d'activité</a>
</div>

{{-- ─── Contenu ─────────────────────────────────────────────────────────── --}}
<div class="animate-fade delay-1" style="display:flex; gap:24px; margin-top:16px;">
    {{-- Colonne principale ──────────────────────────────────────────────── --}}
    <div style="flex:1; min-width:0;">

        {{-- Vue documents transmis (S2.1) ───────────────────────────────── --}}
        @if(request()->get('view') === 'transmis')
            <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; padding:20px;">
                <div style="font-weight:700; margin-bottom:14px;"><i class="fas fa-paper-plane me-2"></i> Documents transmis au comptable</div>
                @forelse($transmis as $doc)
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--sec-border);">
                        <div class="sec-avatar" style="background:rgba(13,148,136,.1); color:var(--sec-primary); width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:13px;">{{ $doc->name }}</div>
                            <div style="font-size:11px; color:var(--sec-text-muted);">
                                Transmis le {{ optional($doc->transmitted_at)->format('d/m/Y H:i') }}
                                @if($doc->workflow_step === 'en_traitement_comptable')
                                    · <span style="color:#059669; font-weight:600;"><i class="fa-solid fa-circle-check"></i> Pris en charge</span>
                                @elseif($doc->workflow_step === 'valide')
                                    · <span style="color:#2563EB; font-weight:600;"><i class="fa-solid fa-check-double"></i> Validé</span>
                                @else
                                    · <span style="color:#D97706;"><i class="fa-solid fa-clock"></i> En attente d'accusé</span>
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('gel-secretary.coordination.document-note') }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <input type="hidden" name="document_id" value="{{ $doc->id }}">
                            <input type="text" name="note" placeholder="Note pour le comptable (S2.2)..." class="form-control form-control-sm" style="width:260px; border-radius:8px;" title="Adresser une information liée à ce document">
                        </form>
                    </div>
                @empty
                    <div style="padding:40px; text-align:center; color:var(--sec-text-muted);">
                        <i class="fas fa-inbox" style="font-size:32px; color:#CBD5E1;"></i>
                        <div>Aucun document transmis au comptable pour cette entreprise.</div>
                    </div>
                @endforelse
            </div>
        @endif

        @if(request()->get('view') === 'demandes')
            <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; padding:20px;">
                <div style="font-weight:700; margin-bottom:14px;"><i class="fa-solid fa-inbox me-2"></i> Demandes & alertes du comptable (Kanban)</div>
                @forelse($demandesRecues as $t)
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--sec-border);">
                        <div class="sec-avatar" style="background:{{ $t->coordination_type==='alerte' ? 'rgba(220,38,38,.1)' : 'rgba(13,148,136,.1)' }}; color:{{ $t->coordination_type==='alerte' ? '#DC2626' : 'var(--sec-primary)' }}; width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid {{ $t->coordination_type==='alerte' ? 'fa-triangle-exclamation' : 'fa-file-export' }}"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:13px;">{{ $t->titre }}</div>
                            <div style="font-size:11px; color:var(--sec-text-muted);">
                                {{ optional($t->date_echeance)->format('d/m/Y') ?: 'Sans échéance' }}
                                · <span style="font-weight:600; text-transform:uppercase; font-size:10px; color:{{ $t->priorite==='critique' ? '#DC2626' : ($t->priorite==='haute' ? '#D97706' : '#059669') }};">{{ $t->priorite }}</span>
                            </div>
                            @if($t->description)<div style="font-size:12px; margin-top:4px; color:var(--sec-text);">{{ $t->description }}</div>@endif
                        </div>
                        <span class="sec-nav-badge" style="background:{{ $t->statut==='a_faire' ? 'var(--sec-primary)' : '#64748B' }};">{{ $t->statut }}</span>
                    </div>
                @empty
                    <div style="padding:40px; text-align:center; color:var(--sec-text-muted);">Aucune demande du comptable.</div>
                @endforelse
            </div>
        @endif

        {{-- Vue ensemble par défaut ─────────────────────────────────────── --}}
        @if(!request()->get('view'))
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; padding:20px;">
                    <div style="font-weight:700; margin-bottom:14px;"><i class="fa-solid fa-paper-plane me-2"></i> Documents transmis <span class="sec-nav-badge" style="background:var(--sec-primary);">{{ $transmis->count() }}</span></div>
                    @forelse($transmis->take(5) as $doc)
                        <a href="{{ route('gel-secretary.coordination.index', ['client_id' => $client->id, 'view' => 'transmis']) }}" style="display:flex; align-items:center; gap:10px; padding:8px 8px; border-bottom:1px solid var(--sec-border); text-decoration:none; color:inherit; transition:background 0.2s; border-radius:6px;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-file-lines" style="color:var(--sec-primary);"></i>
                            <div style="flex:1; font-size:12px;">{{ $doc->name }}</div>
                            <span style="font-size:11px; {{ $doc->workflow_step==='en_traitement_comptable' ? 'color:#059669;' : 'color:#D97706;' }}">{{ $doc->workflow_step==='en_traitement_comptable' ? 'Pris en charge' : 'En attente' }}</span>
                        </a>
                    @empty
                        <div style="color:var(--sec-text-muted); font-size:13px;">Aucun document transmis.</div>
                    @endforelse
                </div>
                <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; padding:20px;">
                    <div style="font-weight:700; margin-bottom:14px;"><i class="fa-solid fa-inbox me-2"></i> Demandes du comptable <span class="sec-nav-badge" style="background:{{ $demandesRecues->where('statut','!=','termine')->count() ? '#DC2626' : 'var(--sec-primary)' }};">{{ $demandesRecues->where('statut','!=','termine')->count() }}</span></div>
                    @forelse($demandesRecues->take(5) as $d)
                        <a href="{{ route('gel-secretary.coordination.index', ['client_id' => $client->id, 'view' => 'demandes']) }}" style="display:flex; align-items:center; gap:10px; padding:8px 8px; border-bottom:1px solid var(--sec-border); text-decoration:none; color:inherit; transition:background 0.2s; border-radius:6px;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                            <i class="fa-solid {{ $d->coordination_type==='alerte' ? 'fa-triangle-exclamation' : 'fa-file-export' }}" style="color:{{ $d->coordination_type==='alerte' ? '#DC2626' : 'var(--sec-primary)' }};"></i>
                            <div style="flex:1; min-width:0; font-size:12px; text-overflow:ellipsis; white-space:nowrap; overflow:hidden;">{{ $d->titre }}</div>
                        </a>
                    @empty
                        <div style="color:var(--sec-text-muted); font-size:13px;">Aucune demande en attente.</div>
                    @endforelse
                </div>
            </div>

            {{-- Messagerie de coordination (S4.1) ─────────────────────────── --}}
            <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; display:flex; flex-direction:column; height:450px; margin-top:20px;">
                <div style="font-weight:700; padding:16px 20px; border-bottom:1px solid var(--sec-border); display:flex; align-items:center; justify-content:space-between;">
                    <div><i class="fa-solid fa-comments" style="color:var(--sec-primary); margin-right:8px;"></i> Messagerie de coordination</div>
                    <div style="font-size:11px; color:#94A3B8; font-weight:500;">Canal sécurisé avec le comptable</div>
                </div>

                <div id="chat-messages" style="flex:1; padding:20px; overflow-y:auto; display:flex; flex-direction:column; gap:16px; background:#F8FAFC;">
                    @forelse($messages as $m)
                        <div style="max-width:85%; padding:12px 16px; border-radius:14px; font-size:13.5px; line-height:1.5; align-self:{{ $m->sender_id === $user->id ? 'flex-end' : 'flex-start' }}; background:{{ $m->sender_id === $user->id ? 'var(--sec-primary)' : '#fff' }}; color:{{ $m->sender_id === $user->id ? '#fff' : '#1E293B' }}; box-shadow:0 2px 4px rgba(0,0,0,0.04); border:{{ $m->sender_id === $user->id ? 'none' : '1px solid #E2E8F0' }}; border-bottom-{{ $m->sender_id === $user->id ? 'right' : 'left' }}-radius: 2px;">
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

                <div style="padding:16px; border-top:1px solid var(--sec-border); background:white; border-bottom-left-radius:12px; border-bottom-right-radius:12px;">
                    <form id="chat-form" style="display:flex; gap:10px; align-items:center;">
                        <input type="hidden" name="client_id" id="chat-client-id" value="{{ $client->id }}">
                        
                        <label for="chat-attachment" style="cursor:pointer; padding:10px; background:#F1F5F9; border-radius:50%; color:#64748B; transition:all 0.2s;" onmouseover="this.style.background='#E2E8F0'; this.style.color='var(--sec-primary)'" onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B'">
                            <i class="fa-solid fa-paperclip"></i>
                        </label>
                        <input type="file" id="chat-attachment" name="attachment" style="display:none;" onchange="updateFileName(this)">
                        
                        <div style="flex:1; display:flex; flex-direction:column; position:relative;">
                            <div id="file-preview" style="display:none; font-size:11px; font-weight:600; color:var(--sec-primary); margin-bottom:4px; padding:2px 8px; background:#F0FDF4; border-radius:4px; width:fit-content; border:1px solid #DCFCE7;">
                                <i class="fa-solid fa-file-lines me-1"></i> <span id="file-name-text">fichier</span>
                                <i class="fa-solid fa-times ms-2" style="cursor:pointer;" onclick="removeAttachment()"></i>
                            </div>
                            <input type="text" id="chat-message" name="message" placeholder="Écrivez votre message..." class="form-control" style="border-radius:20px; border:1px solid #E2E8F0; padding:10px 16px; background:#F8FAFC;" autocomplete="off">
                        </div>
                        
                        <button type="submit" id="chat-submit" class="btn btn-primary" style="border-radius:20px; padding:10px 20px; font-weight:600;">
                            <span id="btn-text">Envoyer</span>
                            <i class="fa-solid fa-paper-plane ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endif


        {{-- Vue Fil d'activité (S4.3) ───────────────────────────────────── --}}
        @if(request()->get('view') === 'fil' || !request()->get('view'))
            <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; padding:24px; margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid #f1f5f9;">
                    <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-timeline" style="color:var(--sec-primary);"></i> Historique des actions & Échanges
                    </h3>
                </div>

                @if(count($activity) > 0)
                    <div class="activity-timeline">
                        @foreach($activity as $evt)
                            <div class="timeline-item">
                                <div class="timeline-icon" style="{{ str_contains($evt->icon, 'check') ? 'color:#10B981; border-color:#10B981;' : (str_contains($evt->icon, 'paper-plane') ? 'color:#3B82F6; border-color:#3B82F6;' : (str_contains($evt->icon, 'exclamation') ? 'color:#EF4444; border-color:#EF4444;' : 'color:var(--sec-primary); border-color:var(--sec-primary);')) }}">
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
                        <div style="font-size:13px;">Les échanges et actions avec la comptabilité apparaîtront ici.</div>
                    </div>
                @endif
            </div>
        @endif

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
          const clientId = document.getElementById('chat-client-id').value;
          
          const noMessages = document.getElementById('no-messages');
          if (noMessages) noMessages.remove();
          
          submitBtn.disabled = true;
          btnText.innerText = 'Envoi...';
          
          try {
              const res = await fetch("{{ route('gel-secretary.coordination.send-message') }}", {
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
          const bg = isMe ? 'var(--sec-primary)' : '#fff';
          const color = isMe ? '#fff' : '#1E293B';
          const borderColor = isMe ? 'none' : '1px solid #E2E8F0';
          const radiusStyle = isMe ? 'border-bottom-right-radius:2px;' : 'border-bottom-left-radius:2px;';
          const senderName = isMe ? 'Vous' : (m.sender ? m.sender.name : 'Comptable');
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
        // S4.1 — écoute du canal de coordination propres à cette entreprise
        try {
          window.Echo.private('chat.coordination.{{ $client->id }}')
            .listen('.CoordinationActivityEvent', function (e) {
              if (typeof secToast === 'function' && e && e.activity && e.activity.subject) {
                secToast(e.activity.subject, 'info');
              }
            })
            .listen('.MessageEnvoyeEvent', function (e) {
              if (e.message && e.message.sender_id !== {{ $user->id }}) {
                  if (typeof secToast === 'function') secToast('Nouveau message de la comptabilité', 'info');
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