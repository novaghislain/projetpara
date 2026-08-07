@extends('layouts.gel-secretary')
@section('title', 'Messagerie Structurée - Secrétariat')

@section('content')
<div class="pro-header animate-fade">
  <div>
    <div class="pro-title">Messagerie</div>
    <div class="pro-subtitle">3 canaux de communication : Entreprises, Comptables, Administrateurs</div>
  </div>
</div>

{{-- ─── Onglets des 3 canaux (S11) ─────────────────────────────────────── --}}
<div class="animate-fade delay-1" style="display:flex; gap:8px; margin-top:20px; flex-wrap:wrap;">
    @php
        $channels = [
            ['key' => 'entreprise',        'label' => 'Client',      'icon' => 'fa-building'],
            ['key' => 'interne_comptable', 'label' => 'Interne', 'icon' => 'fa-users'],
            ['key' => 'interne_admin',     'label' => 'Support', 'icon' => 'fa-headset'],
        ];
    @endphp
    @foreach($channels as $ch)
        <a href="{{ route('gel-secretary.messagerie.index', array_filter(['channel' => $ch['key']])) }}"
           style="padding: 10px 18px; border-radius: 10px; text-decoration:none; font-weight:600; font-size:13px;
                  display:inline-flex; align-items:center; gap:8px;
                  {{ $channel === $ch['key'] ? 'background: var(--sec-primary); color:#fff;' : 'background:#fff; color: var(--sec-text); border:1px solid var(--sec-border);' }}">
            <i class="fas {{ $ch['icon'] }}"></i> {{ $ch['label'] }}
        </a>
    @endforeach
</div>

<div class="animate-fade delay-1" style="display: flex; gap: 24px; margin-top: 16px; min-height: 600px; max-height: 70vh;">

    {{-- Colonne de gauche : Liste des conversations ─────────────────────────── --}}
    <div style="width: 320px; background: white; border-radius: 12px; border: 1px solid var(--sec-border); display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div style="padding: 16px; border-bottom: 1px solid var(--sec-border); background: #F8FAFC;">
            <input type="text" id="searchClientInput" onkeyup="filterClients()" placeholder="{{ $channel === 'entreprise' ? 'Rechercher une entreprise...' : 'Rechercher un collègue...' }}" class="form-control form-control-sm" style="border-radius: 8px;">
        </div>

        <div style="flex: 1; overflow-y: auto;" id="clientList">
            @if($channel === 'entreprise')
                @forelse($clients as $c)
                    @php $isActive = $activeClient && $activeClient->id === $c->id; @endphp
                    <a href="{{ route('gel-secretary.messagerie.index', ['channel' => 'entreprise', 'client_id' => $c->id]) }}"
                       class="client-item"
                       style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--sec-border); text-decoration: none; color: inherit; background: {{ $isActive ? '#F8FAFC' : 'transparent' }}; border-left: {{ $isActive ? '4px solid var(--sec-primary)' : '4px solid transparent' }};">
                        <div class="sec-avatar" style="background: rgba(14, 165, 233, 0.1); color: var(--sec-primary); font-weight: 600; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            {{ strtoupper(substr($c->nom_entreprise ?? 'C', 0, 2)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color:var(--sec-text);">
                                {{ $c->nom_entreprise }}
                            </div>
                            <div style="font-size: 12px; color: var(--sec-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $c->email }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="padding: 30px; text-align: center; color: var(--sec-text-muted);">
                        Aucun client disponible.
                    </div>
                @endforelse
            @else
                @forelse($colleagues as $col)
                    @php $isActive = $activeColleague && $activeColleague->id === $col->id; @endphp
                    <a href="{{ route('gel-secretary.messagerie.index', ['channel' => $channel, 'receiver_id' => $col->id]) }}"
                       class="client-item"
                       style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--sec-border); text-decoration: none; color: inherit; background: {{ $isActive ? '#F8FAFC' : 'transparent' }}; border-left: {{ $isActive ? '4px solid var(--sec-primary)' : '4px solid transparent' }};">
                        <div class="sec-avatar" style="background: rgba(13, 148, 136, 0.1); color: #0D9488; font-weight: 600; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            {{ strtoupper(substr($col->name ?? 'C', 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color:var(--sec-text);">
                                {{ $col->name }}
                            </div>
                            <div style="font-size: 12px; color: var(--sec-text-muted); text-transform: capitalize;">
                                {{ str_replace('_', ' ', $col->role ?? $channel) }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="padding: 30px; text-align: center; color: var(--sec-text-muted);">
                        Aucun collègue disponible sur ce canal.
                    </div>
                @endforelse
            @endif
        </div>
    </div>

    {{-- Colonne de droite : Chat ─────────────────────────────────────────── --}}
    <div style="flex: 1; display: flex; flex-direction: column; background: white; border-radius: 12px; border: 1px solid var(--sec-border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        @if($channel === 'entreprise' && $activeClient)
            @php $recipientName = $activeClient->nom_entreprise; $recipientId = $activeClient->id; $recipientType = 'entreprise'; @endphp
        @elseif($channel !== 'entreprise' && $activeColleague)
            @php $recipientName = $activeColleague->name; $recipientId = $activeColleague->id; $recipientType = 'interne'; @endphp
        @else
            @php $recipientName = null; $recipientId = null; @endphp
        @endif

        @if($recipientName)
            {{-- Header Chat --}}
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--sec-border); display: flex; align-items: center; justify-content: space-between; background: #F8FAFC;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="sec-avatar" style="background: var(--sec-primary); color: white; width:40px; height:40px; border-radius:50%; font-weight:600; display:flex; align-items:center; justify-content:center;">
                        {{ strtoupper(substr($recipientName, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color:var(--sec-text);">{{ $recipientName }}</div>
                        <div style="font-size: 12px; color: #10B981; display: flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block;"></span>
                            {{ $channel === 'entreprise' ? 'En ligne' : 'Canal interne' }}
                        </div>
                    </div>
                </div>
                @if($channel === 'entreprise')
                    <a href="{{ route('gel-secretary.clients.show', $activeClient->id) }}" class="btn btn-sm" style="background: white; border: 1px solid var(--sec-border); color: var(--sec-text);">
                        <i class="fas fa-desktop"></i> Fiche client
                    </a>
                @endif
            </div>

            {{-- Zone des messages --}}
            <div style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; background: #F9FAFB;" id="chatMessageArea">
                @forelse($messages as $msg)
                    @php $isMe = (int) $msg->sender_id === (int) Auth::id(); @endphp
                    <div style="display: flex; flex-direction: column; align-items: {{ $isMe ? 'flex-end' : 'flex-start' }};">
                        <div style="max-width: 65%; padding: 12px 16px; border-radius: 16px; font-size: 14px; line-height: 1.5; {{ $isMe ? 'background: var(--sec-primary); color: white; border-bottom-right-radius: 4px;' : 'background: white; color: var(--sec-text); border: 1px solid var(--sec-border); border-bottom-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);' }}">
                            {{ $msg->message }}
                            @if($msg->piece_jointe)
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid {{ $isMe ? 'rgba(255,255,255,0.2)' : 'var(--sec-border)' }};">
                                    <a href="{{ route('gel-secretary.messagerie.download', $msg->id) }}" target="_blank" style="color: {{ $isMe ? '#fff' : 'var(--sec-primary)' }}; text-decoration: underline; font-size: 12px;">
                                        <i class="fas fa-paperclip"></i> Voir la pièce jointe
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div style="font-size: 11px; color: var(--sec-text-muted); margin-top: 4px; display:flex; align-items:center; gap:8px;">
                            {{ $msg->created_at->format('H:i, d M') }}
                            @if(!$isMe && $channel === 'entreprise')
                                <span>•</span>
                                <a href="#" onclick="openTaskFromMessage({{ $msg->id }}, '{{ addslashes($msg->message) }}')" style="color:#0D9488; text-decoration:none; font-weight:600;">
                                    <i class="fas fa-sparkles" style="color:#F59E0B;"></i> Créer tâche IA
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="margin: auto; text-align: center; color: var(--sec-text-muted);">
                        <i class="fas fa-paper-plane" style="font-size: 32px; margin-bottom: 12px; color: #CBD5E1;"></i>
                        <p style="font-weight: 500;">Aucun message échangé pour le moment.</p>
                        <p style="font-size: 13px;">Envoyez un message pour contacter {{ $recipientName }}.</p>
                    </div>
                @endforelse
            </div>

            {{-- Formulaire d'envoi --}}
            <div style="padding: 16px 20px; border-top: 1px solid var(--sec-border); background: white;">
                <form id="chatForm" action="{{ route('gel-secretary.messagerie.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: center;">
                    @csrf
                    <input type="hidden" name="channel" value="{{ $channel }}">
                    @if($channel === 'entreprise')
                        <input type="hidden" name="client_id" value="{{ $activeClient->id }}">
                    @else
                        <input type="hidden" name="receiver_id" value="{{ $activeColleague->id }}">
                    @endif

                    <label style="cursor: pointer; padding: 10px; border-radius: 50%; color: var(--sec-text-muted); transition: background 0.2s;" title="Joindre un fichier">
                        <i class="fas fa-paperclip" style="font-size: 18px;"></i>
                        <input type="file" name="file" style="display: none;" onchange="secToast('Pièce jointe sélectionnée', 'success')">
                    </label>

                    <input type="text" id="chatInput" name="message" class="form-control" placeholder="Écrivez votre message à {{ $recipientName }}..." required style="flex: 1; border-radius: 24px; padding: 10px 18px; border: 1px solid var(--sec-border); background: #F8FAFC;">

                    <button type="submit" class="btn" style="background: var(--sec-primary); color:white; border-radius: 24px; padding: 10px 20px;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        @else
            <div style="margin: auto; text-align: center; color: var(--sec-text-muted);">
                <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 16px; color: #CBD5E1;"></i>
                <h3>Sélectionnez une conversation</h3>
                <p style="font-size: 14px;">Choisissez un client ou un collègue à gauche pour commencer à discuter.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Création de Tâche -->
<div id="taskFromMsgModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="sec-card" style="width:450px; padding:24px; position:relative;">
        <h2 style="margin-top:0; margin-bottom:16px; font-size:18px;">Créer une tâche issue du message</h2>
        <form action="{{ route('gel-secretary.tasks.store') }}" method="POST">
            @csrf
            @if($channel === 'entreprise' && $activeClient)
                <input type="hidden" name="client_id" value="{{ $activeClient->id }}">
            @endif
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Titre de la tâche</label>
                <input type="text" id="taskTitleInput" name="titre" value="Suite au message client" required style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Description</label>
                <textarea name="description" id="taskDescInput" rows="4" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box; resize:none;"></textarea>
            </div>
            <div style="display:flex; gap:12px; margin-bottom:16px;">
                <div style="flex:1;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Échéance</label>
                    <input type="date" id="taskDateInput" name="date_echeance" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Priorité</label>
                    <select name="priorite" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                        <option value="moyenne" selected>Moyenne</option>
                        <option value="haute">Haute</option>
                        <option value="critique">Critique</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="sec-btn" onclick="document.getElementById('taskFromMsgModal').style.display='none'" style="background:#f1f5f9; color:#475569; border:none;">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Créer la tâche</button>
            </div>
        </form>
    </div>
</div>

<script>
    var chatArea = document.getElementById('chatMessageArea');
    if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;

    function filterClients() {
        var input = document.getElementById('searchClientInput').value.toLowerCase();
        var items = document.getElementsByClassName('client-item');
        for (var i = 0; i < items.length; i++) {
            var text = items[i].innerText.toLowerCase();
            items[i].style.display = text.includes(input) ? 'flex' : 'none';
        }
    }

    function openTaskFromMessage(msgId, msgContent) {
        document.getElementById('taskDescInput').value = "Chargement IA...";
        document.getElementById('taskTitleInput').value = "Chargement IA...";
        document.getElementById('taskDateInput').value = "";
        document.getElementById('taskFromMsgModal').style.display = 'flex';
        let formData = new FormData();
        formData.append('message_id', msgId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        fetch("{{ route('gel-secretary.messagerie.extract-task') }}", {
            method: 'POST', body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            document.getElementById('taskTitleInput').value = data.titre || "Suite au message client";
            document.getElementById('taskDescInput').value = (data.description ? data.description + "\n\n" : "") + "Message original :\n" + msgContent;
            if (data.date_echeance) document.getElementById('taskDateInput').value = data.date_echeance;
        })
        .catch(err => {
            document.getElementById('taskTitleInput').value = "Suite au message client";
            document.getElementById('taskDescInput').value = "Message original :\n" + msgContent;
        });
    }

    // -- CHAT TEMPS REEL -- (canal entreprise ET interne)
    @if($recipientName)
        var channel = @json($channel);
        var cabinetId = {{ Auth::user()->cabinet_id ?? 'null' }};
        var chatForm = document.getElementById('chatForm');
        var chatInput = document.getElementById('chatInput');

        // Envoi asynchrone (AJAX) pour les 2 types de canaux
        if (chatForm) {
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(chatForm);
                fetch(chatForm.getAttribute('action'), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        chatInput.value = '';
                        appendMessage(data.message, true);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }

        // Écoute temps réel
        if (typeof window.Echo !== 'undefined') {
            if (channel === 'entreprise') {
                var clientId = @json($activeClient ? $activeClient->id : null);
                window.Echo.private('chat.' + cabinetId + '.' + clientId)
                    .listen('.MessageEnvoyeEvent', (e) => {
                        if (e.message.sender_type !== 'secretary') appendMessage(e.message, false);
                    });
            } else {
                // Canal interne : on écoute notre propre canal (émetteur + récepteur)
                var myId = {{ Auth::id() }};
                window.Echo.private('chat.interne.' + cabinetId + '.' + myId)
                    .listen('.MessageEnvoyeEvent', (e) => {
                        var isSelf = e.message.sender_id === myId;
                        appendMessage(e.message, isSelf);
                    });
            }
        }

        function appendMessage(msg, isSelf) {
            if (!chatArea) return;
            var div = document.createElement('div');
            div.style.display = 'flex';
            div.style.flexDirection = 'column';
            div.style.alignItems = isSelf ? 'flex-end' : 'flex-start';
            var bubble = document.createElement('div');
            bubble.style.maxWidth = '65%';
            bubble.style.padding = '12px 16px';
            bubble.style.borderRadius = '16px';
            bubble.style.fontSize = '14px';
            bubble.style.lineHeight = '1.5';
            if (isSelf) {
                bubble.style.background = 'var(--sec-primary)';
                bubble.style.color = 'white';
                bubble.style.borderBottomRightRadius = '4px';
            } else {
                bubble.style.background = 'white';
                bubble.style.color = 'var(--sec-text)';
                bubble.style.border = '1px solid var(--sec-border)';
                bubble.style.borderBottomLeftRadius = '4px';
                bubble.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
            }
            bubble.innerText = msg.message;
            if (msg.piece_jointe) {
                var att = document.createElement('div');
                att.style.marginTop = '8px';
                att.style.paddingTop = '8px';
                att.style.borderTop = '1px solid ' + (isSelf ? 'rgba(255,255,255,0.2)' : 'var(--sec-border)');
                var a = document.createElement('a');
                a.href = '/gel-secretary/messagerie/download/' + msg.id;
                a.target = '_blank';
                a.style.color = isSelf ? '#fff' : 'var(--sec-primary)';
                a.style.fontSize = '12px';
                a.innerHTML = '<i class="fas fa-paperclip"></i> Voir la pièce jointe';
                att.appendChild(a);
                bubble.appendChild(att);
            }
            div.appendChild(bubble);
            var timeDiv = document.createElement('div');
            timeDiv.style.fontSize = '11px';
            timeDiv.style.color = 'var(--sec-text-muted)';
            timeDiv.style.marginTop = '4px';
            timeDiv.style.display = 'flex';
            timeDiv.style.alignItems = 'center';
            timeDiv.style.gap = '8px';
            timeDiv.appendChild(document.createTextNode(new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})));
            div.appendChild(timeDiv);
            chatArea.appendChild(div);
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    @endif
</script>
@endsection
