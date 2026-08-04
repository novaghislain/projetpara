@php $currentSection = 'messagerie'; @endphp
@extends('layouts.gel-accountant')

@section('title', 'Messagerie — GEL Accountant')

@section('content')
<div class="gel-page-header" style="margin-bottom:16px;">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-comments" style="color:var(--gel-primary);"></i> Messagerie Cabinet</h1>
        <p class="gel-page-subtitle">Discutez en direct avec vos entreprises clientes</p>
    </div>
</div>

<div class="gel-card" style="height: calc(100vh - 170px); display: flex; overflow: hidden;">
    {{-- Colonne de gauche : Liste des clients --}}
    <div style="width: 320px; border-right: 1px solid var(--gel-border); display: flex; flex-direction: column; background: var(--gel-body-bg);">
        <div style="padding: 16px; border-bottom: 1px solid var(--gel-border);">
            <div class="gel-form-group" style="margin: 0;">
                <input type="text" class="gel-form-control" placeholder="Rechercher un client..." id="searchClientInput" onkeyup="filterClients()">
            </div>
        </div>
        <div style="flex: 1; overflow-y: auto;" id="clientList">
            @forelse($clients as $c)
                @php $isActive = $activeClient && $activeClient->id === $c->id; @endphp
                <a href="{{ route('gel-accountant.messagerie', ['client_id' => $c->id]) }}" 
                   class="client-item" 
                   style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--gel-border-light); text-decoration: none; color: inherit; background: {{ $isActive ? 'var(--gel-card-bg)' : 'transparent' }}; border-left: {{ $isActive ? '4px solid var(--gel-primary)' : '4px solid transparent' }};">
                    <div class="gel-avatar" style="background: var(--gel-primary-light); color: var(--gel-primary); font-weight: 600;">
                        {{ strtoupper(substr($c->nom_entreprise ?? 'C', 0, 2)) }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $c->nom_entreprise }}
                        </div>
                        <div style="font-size: 12px; color: var(--gel-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $c->email }}
                        </div>
                    </div>
                </a>
            @empty
                <div style="padding: 30px; text-align: center; color: var(--gel-text-muted);">
                    Aucun client disponible.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Colonne de droite : Chat Messenger --}}
    <div style="flex: 1; display: flex; flex-direction: column; background: var(--gel-card-bg);">
        @if($activeClient)
            {{-- Header Chat --}}
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--gel-border); display: flex; align-items: center; justify-content: space-between; background: var(--gel-card-bg);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="gel-avatar" style="background: var(--gel-primary); color: white; font-weight: 600;">
                        {{ strtoupper(substr($activeClient->nom_entreprise, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 15px;">{{ $activeClient->nom_entreprise }}</div>
                        <div style="font-size: 12px; color: var(--gel-success); display: flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--gel-success); display: inline-block;"></span> En ligne
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('gel-accountant.client.select', ['clientId' => $activeClient->id]) }}" class="gel-btn gel-btn-secondary gel-btn-sm">
                        <i class="fas fa-desktop"></i> Travailler sur ce dossier
                    </a>
                </div>
            </div>

            {{-- Zone des messages --}}
            <div style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; background: #F9FAFB;" id="chatMessageArea">
                @forelse($messages as $msg)
                    @php $isMe = $msg->sender_type === 'accountant'; @endphp
                    <div style="display: flex; flex-direction: column; align-items: {{ $isMe ? 'flex-end' : 'flex-start' }};">
                        <div style="max-width: 65%; padding: 12px 16px; border-radius: 16px; font-size: 14px; line-height: 1.5; {{ $isMe ? 'background: var(--gel-primary); color: white; border-bottom-right-radius: 4px;' : 'background: white; color: var(--gel-text-primary); border: 1px solid var(--gel-border); border-bottom-left-radius: 4px; box-shadow: var(--gel-shadow-sm);' }}">
                            {{ $msg->message }}

                            @if($msg->piece_jointe)
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid {{ $isMe ? 'rgba(255,255,255,0.2)' : 'var(--gel-border)' }};">
                                    <a href="{{ asset('storage/' . $msg->piece_jointe) }}" target="_blank" style="color: {{ $isMe ? '#fff' : 'var(--gel-primary)' }}; text-decoration: underline; font-size: 12px;">
                                        <i class="fas fa-paperclip"></i> Voir la pièce jointe
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div style="font-size: 11px; color: var(--gel-text-muted); margin-top: 4px;">
                            {{ $msg->created_at->format('H:i, d M') }}
                        </div>
                    </div>
                @empty
                    <div style="margin: auto; text-align: center; color: var(--gel-text-muted);">
                        <i class="fas fa-paper-plane" style="font-size: 32px; margin-bottom: 12px; color: var(--gel-primary-light);"></i>
                        <p style="font-weight: 500;">Aucun message échangé pour le moment.</p>
                        <p style="font-size: 13px;">Envoyez un premier message pour démarrer la discussion avec {{ $activeClient->nom_entreprise }}.</p>
                    </div>
                @endforelse
            </div>

            {{-- Formulaire d'envoi --}}
            <div style="padding: 16px 20px; border-top: 1px solid var(--gel-border); background: var(--gel-card-bg);">
                <form id="chatForm" action="{{ route('gel-accountant.messagerie.send') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: center;">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $activeClient->id }}">
                    <input type="hidden" name="sender_type" value="accountant">

                    <label style="cursor: pointer; padding: 10px; border-radius: 50%; color: var(--gel-text-muted); transition: background 0.2s;" title="Joindre un fichier">
                        <i class="fas fa-paperclip" style="font-size: 18px;"></i>
                        <input type="file" name="piece_jointe" style="display: none;" onchange="showToast('Pièce jointe sélectionnée', 'info')">
                    </label>

                    <input type="text" id="chatInput" name="message" class="gel-form-control" placeholder="Écrivez votre message à {{ $activeClient->nom_entreprise }}..." required style="flex: 1; border-radius: 24px; padding: 10px 18px;">

                    <button type="submit" class="gel-btn gel-btn-primary" style="border-radius: 24px; padding: 10px 20px;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        @else
            <div style="margin: auto; text-align: center; color: var(--gel-text-muted);">
                <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 16px; color: var(--gel-border);"></i>
                <h3>Sélectionnez une entreprise</h3>
                <p style="font-size: 14px;">Choisissez un client dans la liste de gauche pour démarrer la conversation Messenger.</p>
            </div>
        @endif
    </div>
</div>

<script>
    // Auto-scroll chat to bottom
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
                    if (e.message.sender_type !== 'accountant') {
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
                bubble.style.background = 'var(--gel-primary)';
                bubble.style.color = 'white';
                bubble.style.borderBottomRightRadius = '4px';
            } else {
                bubble.style.background = 'white';
                bubble.style.color = 'var(--gel-text-primary)';
                bubble.style.border = '1px solid var(--gel-border)';
                bubble.style.borderBottomLeftRadius = '4px';
                bubble.style.boxShadow = 'var(--gel-shadow-sm)';
            }
            
            bubble.innerText = msg.message;

            var time = document.createElement('div');
            time.style.fontSize = '11px';
            time.style.color = 'var(--gel-text-muted)';
            time.style.marginTop = '4px';
            time.innerText = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

            div.appendChild(bubble);
            div.appendChild(time);

            chatArea.appendChild(div);
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    @endif
</script>
@endsection
