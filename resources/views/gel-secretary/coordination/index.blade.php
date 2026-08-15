@extends('layouts.gel-secretary')
@section('title', 'Coordination')

@push('styles')
<style>
/* ==========================================================================
   COORDINATION - BENTO GRID DESIGN
   ========================================================================== */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: minmax(100px, auto);
    gap: 24px;
    margin-bottom: 40px;
}

.bento-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05),
                inset 0 0 0 1px rgba(255, 255, 255, 0.5);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}
.bento-card:hover {
    box-shadow: 0 15px 35px -10px rgba(13, 148, 136, 0.15),
                inset 0 0 0 1px rgba(255, 255, 255, 0.8);
}

.bento-header-card { grid-column: span 12; grid-row: span 1; background: linear-gradient(135deg, #1E293B 0%, #334155 100%); color: white; border:none; display:flex; flex-direction:row; align-items:center; justify-content:space-between; padding:20px 32px;}
.bento-chat { grid-column: span 8; grid-row: span 5; }
.bento-kanban { grid-column: span 4; grid-row: span 3; }
.bento-feed { grid-column: span 4; grid-row: span 2; }

/* Header Card */
.hc-title { font-size: 24px; font-weight: 800; font-family: 'Inter', sans-serif;}
.hc-sub { font-size: 13px; opacity: 0.8; margin-top: 4px; }
.hc-comptable { display:flex; align-items:center; gap:12px; background:rgba(255,255,255,0.1); padding:10px 16px; border-radius:12px;}
.hc-comptable-avatar { width:40px; height:40px; border-radius:50%; background:#10B981; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;}
.hc-comptable-info { display:flex; flex-direction:column;}
.hc-comptable-name { font-weight:700; font-size:14px;}
.hc-comptable-role { font-size:11px; opacity:0.8;}

/* Chat Area */
.b-title { font-size: 16px; font-weight: 700; color: #1E293B; display: flex; align-items: center; gap: 8px; margin-bottom:16px;}
.chat-container { flex:1; overflow-y:auto; padding-right:12px; display:flex; flex-direction:column; gap:16px;}
.chat-msg { display:flex; flex-direction:column; max-width:80%; }
.chat-msg.me { align-self:flex-end; }
.chat-msg.other { align-self:flex-start; }
.chat-bubble { padding:12px 16px; border-radius:12px; font-size:13.5px; line-height:1.5;}
.chat-msg.me .chat-bubble { background:#0D9488; color:white; border-bottom-right-radius:2px;}
.chat-msg.other .chat-bubble { background:#F1F5F9; color:#334155; border-bottom-left-radius:2px;}
.chat-meta { font-size:11px; color:#94A3B8; margin-top:4px;}
.chat-msg.me .chat-meta { text-align:right;}

.chat-input-area { margin-top:20px; display:flex; gap:12px; align-items:center;}
.chat-input { flex:1; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:12px 16px; outline:none; transition:all 0.2s; font-family:inherit;}
.chat-input:focus { border-color:#0D9488; background:white; box-shadow:0 0 0 3px rgba(13,148,136,0.1);}
.chat-btn { background:#0D9488; color:white; border:none; border-radius:12px; width:46px; height:46px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s;}
.chat-btn:hover { background:#0F766E; transform:translateY(-2px);}

/* Kanban Demandes */
.demande-item { background:white; border:1px solid #E2E8F0; border-radius:12px; padding:12px; margin-bottom:12px; transition:all 0.2s;}
.demande-item:hover { border-color:#99F6E4; transform:translateX(4px); box-shadow:0 2px 8px rgba(13,148,136,0.05);}
.d-title { font-size:13.5px; font-weight:600; color:#1E293B; margin-bottom:6px;}
.d-meta { font-size:11px; color:#64748B; display:flex; gap:12px; align-items:center;}
.d-badge { padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;}
.db-afaire { background:#FEF2F2; color:#DC2626;}
.db-encours { background:#FFFBEB; color:#D97706;}
.db-terminee { background:#ECFDF5; color:#10B981;}

/* Activity Feed (shared) */
.feed-list { display: flex; flex-direction: column; gap: 16px; position: relative; margin-top:12px;}
.feed-list::before { content: ''; position: absolute; left: 15px; top: 10px; bottom: 10px; width: 2px; background: #E2E8F0; z-index: 1;}
.feed-item { display: flex; gap: 16px; position: relative; z-index: 2; }
.feed-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: white; flex-shrink: 0; border: 3px solid white; box-shadow: 0 0 0 1px #E2E8F0;}
.feed-content { padding-top: 4px; }
.feed-text { font-size: 13px; color: #334155; font-weight: 500; line-height: 1.4; }
.feed-time { font-size: 11px; color: #94A3B8; margin-top: 2px; }

/* Animations */
.stagger-1 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.2s;}
.stagger-3 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.3s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

<div class="bento-grid">
    
    <!-- HEADER -->
    <div class="bento-card bento-header-card stagger-1">
        <div>
            <div class="hc-title">Coordination: {{ $client->nom_entreprise ?? 'Dossier' }}</div>
            <div class="hc-sub">Espace de communication sécurisé avec le cabinet.</div>
        </div>
        <div class="hc-comptable">
            <div class="hc-comptable-avatar">
                {{ $comptable ? strtoupper(substr($comptable->name ?? 'C', 0, 1)) : '?' }}
            </div>
            <div class="hc-comptable-info">
                <span class="hc-comptable-name">{{ $comptable->name ?? 'Non assigné' }}</span>
                <span class="hc-comptable-role">Comptable du dossier</span>
            </div>
        </div>
    </div>

    <!-- MESSAGERIE (CHAT) -->
    <div class="bento-card bento-chat stagger-2">
        <div class="b-title"><i class="fas fa-comments" style="color:#0D9488;"></i> Canal de discussion</div>
        
        <div class="chat-container" id="chatContainer">
            @forelse($messages as $msg)
                @php $isMe = ($msg->sender_id === auth()->id()); @endphp
                <div class="chat-msg {{ $isMe ? 'me' : 'other' }}">
                    <div class="chat-bubble">
                        {{ $msg->message }}
                        @if($msg->piece_jointe)
                            <div style="margin-top:8px; padding-top:8px; border-top:1px solid rgba(0,0,0,0.1);">
                                <a href="{{ asset('storage/'.$msg->piece_jointe) }}" target="_blank" style="color:inherit; text-decoration:underline; font-size:12px;">
                                    <i class="fas fa-paperclip"></i> Pièce jointe
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="chat-meta">{{ $msg->created_at->format('d/m H:i') }} • {{ $msg->sender->name ?? 'Inconnu' }}</div>
                </div>
            @empty
                <div style="text-align:center; padding:40px; color:#94A3B8;">
                    <i class="fas fa-paper-plane" style="font-size:32px; margin-bottom:12px; opacity:0.5;"></i>
                    <div>Aucun message. Commencez la discussion !</div>
                </div>
            @endforelse
        </div>

        <form action="{{ route('gel-secretary.coordination.send-message') }}" method="POST" enctype="multipart/form-data" class="chat-input-area" id="chatForm">
            @csrf
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <button type="button" class="chat-btn" style="background:#F1F5F9; color:#64748B;" onclick="document.getElementById('attachFile').click()" title="Joindre un fichier">
                <i class="fas fa-paperclip"></i>
            </button>
            <input type="file" name="attachment" id="attachFile" style="display:none;">
            <input type="text" name="message" class="chat-input" placeholder="Écrivez votre message au comptable..." autocomplete="off">
            <button type="submit" class="chat-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <!-- KANBAN DEMANDES (TÂCHES) -->
    <div class="bento-card bento-kanban stagger-3">
        <div class="b-title"><i class="fas fa-tasks" style="color:#F59E0B;"></i> Demandes du comptable</div>
        <div style="flex:1; overflow-y:auto; padding-right:4px;">
            @forelse($demandesRecues as $demande)
                @php
                    $badgeClass = 'db-afaire';
                    if($demande->statut == 'en_cours') $badgeClass = 'db-encours';
                    if($demande->statut == 'terminee') $badgeClass = 'db-terminee';
                @endphp
                <div class="demande-item">
                    <div class="d-title">{{ $demande->titre }}</div>
                    <div class="d-meta">
                        <span class="d-badge {{ $badgeClass }}">{{ str_replace('_', ' ', strtoupper($demande->statut)) }}</span>
                        <span><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($demande->date_echeance)->format('d/m/Y') }}</span>
                    </div>
                </div>
            @empty
                <div style="padding:20px; text-align:center; color:#94A3B8; font-size:13px;">
                    Aucune demande en attente.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ACTIVITY FEED (AUDIT LOGS) -->
    <div class="bento-card bento-feed stagger-3">
        <div class="b-title"><i class="fas fa-history" style="color:#3B82F6;"></i> Évènements récents</div>
        <div style="flex:1; overflow-y:auto;">
            <div class="feed-list">
                @forelse($activity as $feed)
                    <div class="feed-item">
                        <div class="feed-icon" style="background:#3B82F6;">
                            <i class="{{ $feed->icon ?? 'fas fa-info-circle' }}"></i>
                        </div>
                        <div class="feed-content">
                            <div class="feed-text">{{ $feed->description }}</div>
                            <div class="feed-time">{{ \Carbon\Carbon::parse($feed->created_at)->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div style="padding:20px; text-align:center; color:#94A3B8; font-size:13px;">
                        Aucun évènement récent.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script type="module">
    // Auto-scroll au chargement
    const chatContainer = document.getElementById('chatContainer');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Ajax Form Submission
    const chatForm = document.getElementById('chatForm');
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.querySelector('.chat-input');
        const file = document.getElementById('attachFile').files[0];
        
        if (!input.value.trim() && !file) return;

        const formData = new FormData(this);
        input.value = '';
        document.getElementById('attachFile').value = '';

        fetch('{{ route("gel-secretary.coordination.send-message") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success && data.message) {
                appendMessage(data.message, true);
            }
        });
    });

    // Fonction d'affichage d'un message
    function appendMessage(msg, isMe) {
        const div = document.createElement('div');
        div.className = 'chat-msg ' + (isMe ? 'me' : 'other');
        
        const senderName = msg.sender ? msg.sender.name : (isMe ? 'Moi' : 'Inconnu');
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        let attachmentHtml = '';
        if (msg.piece_jointe) {
            attachmentHtml = `
            <div style="margin-top:8px; padding-top:8px; border-top:1px solid rgba(0,0,0,0.1);">
                <a href="/storage/${msg.piece_jointe}" target="_blank" style="color:inherit; text-decoration:underline; font-size:12px;">
                    <i class="fas fa-paperclip"></i> Pièce jointe
                </a>
            </div>`;
        }

        div.innerHTML = `
            <div class="chat-bubble">
                ${msg.message || ''}
                ${attachmentHtml}
            </div>
            <div class="chat-meta">${time} • ${senderName}</div>
        `;
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Écoute des WebSockets (Laravel Echo / Reverb)
    if (window.Echo) {
        window.Echo.private('chat.coordination.{{ $client->id }}')
            .listen('.MessageEnvoyeEvent', (e) => {
                // Ne pas ré-afficher notre propre message (déjà géré par la promesse fetch)
                if (e.message && e.message.sender_id != {{ auth()->id() }}) {
                    appendMessage(e.message, false);
                }
            });
    }
</script>
@endsection
