@extends('layouts.gel-secretary')
@section('title', 'Messagerie Interne - Secrétariat')

@section('content')
<div class="pro-header animate-fade">
  <div>
    <div class="pro-title">Messagerie</div>
    <div class="pro-subtitle">Discutez en direct avec vos entreprises clientes</div>
  </div>
</div>

<div class="animate-fade delay-1" style="display: flex; gap: 24px; margin-top: 24px; min-height: 600px; max-height: 70vh;">
    
    {{-- Colonne de gauche : Liste des clients --}}
    <div style="width: 320px; background: white; border-radius: 12px; border: 1px solid var(--sec-border); display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div style="padding: 16px; border-bottom: 1px solid var(--sec-border); background: #F8FAFC;">
            <input type="text" id="searchClientInput" onkeyup="filterClients()" placeholder="Rechercher une entreprise..." class="form-control form-control-sm" style="border-radius: 8px;">
        </div>
        
        <div style="flex: 1; overflow-y: auto;" id="clientList">
            @forelse($clients as $c)
                @php $isActive = $activeClient && $activeClient->id === $c->id; @endphp
                <a href="{{ route('gel-secretary.messagerie.index', ['client_id' => $c->id]) }}" 
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
        </div>
    </div>

    {{-- Colonne de droite : Chat Messenger --}}
    <div style="flex: 1; display: flex; flex-direction: column; background: white; border-radius: 12px; border: 1px solid var(--sec-border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        @if($activeClient)
            {{-- Header Chat --}}
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--sec-border); display: flex; align-items: center; justify-content: space-between; background: #F8FAFC;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="sec-avatar" style="background: var(--sec-primary); color: white; width:40px; height:40px; border-radius:50%; font-weight:600; display:flex; align-items:center; justify-content:center;">
                        {{ strtoupper(substr($activeClient->nom_entreprise ?? 'C', 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color:var(--sec-text);">{{ $activeClient->nom_entreprise }}</div>
                        <div style="font-size: 12px; color: #10B981; display: flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block;"></span> En ligne
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('gel-secretary.clients.show', $activeClient->id) }}" class="btn btn-sm" style="background: white; border: 1px solid var(--sec-border); color: var(--sec-text);">
                        <i class="fas fa-desktop"></i> Fiche client
                    </a>
                </div>
            </div>

            {{-- Zone des messages --}}
            <div style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; background: #F9FAFB;" id="chatMessageArea">
                @forelse($messages as $msg)
                    @php $isMe = $msg->sender_type === 'secretary'; @endphp
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
                            
                            @if(!$isMe)
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
                        <p style="font-size: 13px;">Envoyez un message pour contacter {{ $activeClient->nom_entreprise }}.</p>
                    </div>
                @endforelse
            </div>

            {{-- Formulaire d'envoi --}}
            <div style="padding: 16px 20px; border-top: 1px solid var(--sec-border); background: white;">
                <form id="chatForm" action="{{ route('gel-secretary.messagerie.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: center;">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $activeClient->id }}">

                    <label style="cursor: pointer; padding: 10px; border-radius: 50%; color: var(--sec-text-muted); transition: background 0.2s;" title="Joindre un fichier">
                        <i class="fas fa-paperclip" style="font-size: 18px;"></i>
                        <input type="file" name="file" style="display: none;" onchange="secToast('Pièce jointe sélectionnée', 'success')">
                    </label>

                    <input type="text" id="chatInput" name="message" class="form-control" placeholder="Écrivez votre message à {{ $activeClient->nom_entreprise }}..." required style="flex: 1; border-radius: 24px; padding: 10px 18px; border: 1px solid var(--sec-border); background: #F8FAFC;">

                    <button type="submit" class="btn" style="background: var(--sec-primary); color:white; border-radius: 24px; padding: 10px 20px;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        @else
            <div style="margin: auto; text-align: center; color: var(--sec-text-muted);">
                <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 16px; color: #CBD5E1;"></i>
                <h3>Sélectionnez une entreprise</h3>
                <p style="font-size: 14px;">Choisissez un client via le sélecteur à gauche pour discuter avec lui.</p>
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
            @if($activeClient)
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
    if (chatArea) {
        chatArea.scrollTop = chatArea.scrollHeight;
    }

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
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            document.getElementById('taskTitleInput').value = data.titre || "Suite au message client";
            document.getElementById('taskDescInput').value = (data.description ? data.description + "\n\n" : "") + "Message original :\n" + msgContent;
            if (data.date_echeance) {
                document.getElementById('taskDateInput').value = data.date_echeance;
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('taskDescInput').value = "Message original :\n" + msgContent;
            document.getElementById('taskTitleInput').value = "Suite au message client";
        });
    }

    // -- CHAT TEMPS REEL --
    @if($activeClient)
        var cabinetId = {{ Auth::user()->cabinet_id }};
        var clientId = {{ $activeClient->id }};
        var chatForm = document.getElementById('chatForm');
        var chatInput = document.getElementById('chatInput');

        if (chatForm) {
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(chatForm);
                var url = chatForm.getAttribute('action');

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
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

        // Ecoute des nouveaux messages via Laravel Echo
        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('chat.' + cabinetId + '.' + clientId)
                .listen('MessageEnvoyeEvent', (e) => {
                    // Si le message ne vient pas de nous
                    if (e.message.sender_type !== 'secretary') {
                        appendMessage(e.message, false);
                    }
                });
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

            var timeDiv = document.createElement('div');
            timeDiv.style.fontSize = '11px';
            timeDiv.style.color = 'var(--sec-text-muted)';
            timeDiv.style.marginTop = '4px';
            timeDiv.style.display = 'flex';
            timeDiv.style.alignItems = 'center';
            timeDiv.style.gap = '8px';
            
            var timeText = document.createTextNode(new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
            timeDiv.appendChild(timeText);
            
            if (!isSelf) {
                var sep = document.createElement('span');
                sep.innerText = '•';
                timeDiv.appendChild(sep);
                
                var link = document.createElement('a');
                link.href = '#';
                link.style.color = 'var(--sec-primary)';
                link.style.textDecoration = 'none';
                link.onclick = function(e) {
                    e.preventDefault();
                    openTaskFromMessage(msg.id, msg.message);
                };
                link.innerHTML = '<i class="fas fa-sparkles" style="color:#F59E0B;"></i> Créer tâche IA';
                timeDiv.appendChild(link);
            }

            div.appendChild(bubble);
            div.appendChild(timeDiv);

            chatArea.appendChild(div);
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    @endif
</script>
@endsection
