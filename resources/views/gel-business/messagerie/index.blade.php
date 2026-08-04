@php $currentSection = 'messagerie'; @endphp
@extends('layouts.gel-business')

@section('title', 'Messagerie — Mon Entreprise')

@section('content')
<div class="gel-page-header" style="margin-bottom:16px;">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-comments" style="color:var(--gel-primary);"></i> Messagerie avec votre Cabinet</h1>
        <p class="gel-page-subtitle">Discutez directement avec votre Expert-Comptable</p>
    </div>
</div>

<div class="gel-card" style="height: calc(100vh - 170px); display: flex; overflow: hidden;">
    {{-- Colonne de gauche : Information du Cabinet --}}
    <div style="width: 320px; border-right: 1px solid var(--gel-border); display: flex; flex-direction: column; background: var(--gel-body-bg); padding: 20px;">
        @if($cabinet)
            <div style="text-align: center; margin-bottom: 24px;">
                <div class="gel-avatar" style="width: 64px; height: 64px; font-size: 24px; margin: 0 auto 12px; background: var(--gel-primary); color: white;">
                    {{ strtoupper(substr($cabinet->nom ?? 'C', 0, 2)) }}
                </div>
                <h3 style="font-size: 16px; margin: 0 0 4px; font-weight: 700;">{{ $cabinet->nom }}</h3>
                <span class="gel-badge gel-badge-success" style="font-size: 11px;">Cabinet Assigné</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px; font-size: 13px;">
                <div style="display: flex; align-items: center; gap: 10px; color: var(--gel-text-secondary);">
                    <i class="fas fa-envelope" style="width: 16px; color: var(--gel-primary);"></i>
                    <span>{{ $cabinet->email ?? 'cabinet@support.bj' }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--gel-text-secondary);">
                    <i class="fas fa-phone" style="width: 16px; color: var(--gel-primary);"></i>
                    <span>{{ $cabinet->telephone ?? '+229 01 23 45 67' }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--gel-text-secondary);">
                    <i class="fas fa-map-marker-alt" style="width: 16px; color: var(--gel-primary);"></i>
                    <span>{{ $cabinet->adresse ?? 'Cotonou, Bénin' }}</span>
                </div>
            </div>

            <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid var(--gel-border); text-align: center;">
                <p style="font-size: 12px; color: var(--gel-text-muted); margin: 0;">Vos échanges sont sécurisés et accessibles uniquement par vous et votre comptable.</p>
            </div>
        @else
            <div style="margin: auto; text-align: center; color: var(--gel-text-muted);">
                <i class="fas fa-exclamation-circle" style="font-size: 32px; margin-bottom: 12px;"></i>
                <p>Aucun cabinet connecté.</p>
            </div>
        @endif
    </div>

    {{-- Colonne de droite : Chat Messenger --}}
    <div style="flex: 1; display: flex; flex-direction: column; background: var(--gel-card-bg);">
        @if($cabinet)
            {{-- Header Chat --}}
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--gel-border); display: flex; align-items: center; justify-content: space-between; background: var(--gel-card-bg);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="gel-avatar" style="background: var(--gel-primary); color: white; font-weight: 600;">
                        {{ strtoupper(substr($cabinet->nom, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 15px;">{{ $cabinet->nom }}</div>
                        <div style="font-size: 12px; color: var(--gel-success); display: flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--gel-success); display: inline-block;"></span> Service comptable disponible
                        </div>
                    </div>
                </div>
            </div>

            {{-- Zone des messages --}}
            <div style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; background: #F9FAFB;" id="chatBusinessArea">
                @forelse($messages as $msg)
                    @php $isMe = $msg->sender_type === 'business'; @endphp
                    <div style="display: flex; flex-direction: column; align-items: {{ $isMe ? 'flex-end' : 'flex-start' }};">
                        @if(!$isMe)
                            <div style="font-size: 11px; font-weight: 600; color: var(--gel-text-secondary); margin-bottom: 4px; padding-left: 4px;">
                                <i class="{{ $msg->sender_type === 'secretary' ? 'fas fa-headset' : 'fas fa-calculator' }}"></i> 
                                {{ $msg->sender_type === 'secretary' ? 'Secrétariat' : 'Comptable' }}
                            </div>
                        @endif
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
                        <p style="font-weight: 500;">Aucun message pour le moment.</p>
                        <p style="font-size: 13px;">Posez une question ou envoyez vos documents à votre comptable ci-dessous.</p>
                    </div>
                @endforelse
            </div>

            {{-- Formulaire d'envoi --}}
            <div style="padding: 16px 20px; border-top: 1px solid var(--gel-border); background: var(--gel-card-bg);">
                <form id="chatForm" action="{{ route('gel-business.messagerie.send') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: center;">
                    @csrf
                    <input type="hidden" name="sender_type" value="business">

                    <label style="cursor: pointer; padding: 10px; border-radius: 50%; color: var(--gel-text-muted); transition: background 0.2s;" title="Joindre un document">
                        <i class="fas fa-paperclip" style="font-size: 18px;"></i>
                        <input type="file" name="piece_jointe" style="display: none;" onchange="showToast('Pièce jointe sélectionnée', 'info')">
                    </label>

                    <input type="text" id="chatInput" name="message" class="gel-form-control" placeholder="Écrivez votre message à votre comptable..." required style="flex: 1; border-radius: 24px; padding: 10px 18px;">

                    <button type="submit" class="gel-btn gel-btn-primary" style="border-radius: 24px; padding: 10px 20px;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        @else
            <div style="margin: auto; text-align: center; color: var(--gel-text-muted);">
                <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 16px; color: var(--gel-border);"></i>
                <h3>Aucun Cabinet Relié</h3>
                <p style="font-size: 14px;">Vous devez vous faire inviter ou inviter un cabinet pour activer la messagerie instantanée.</p>
            </div>
        @endif
    </div>
</div>

<script>
    // Auto-scroll chat to bottom
    var chatArea = document.getElementById('chatBusinessArea');
    if (chatArea) {
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    // -- CHAT TEMPS REEL --
    @if($cabinet)
        var cabinetId = {{ $cabinet->id }};
        var clientId = {{ $client->id }};
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
                    if (e.message.sender_type !== 'business') {
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

            if (!isSelf) {
                var senderLabel = document.createElement('div');
                senderLabel.style.fontSize = '11px';
                senderLabel.style.fontWeight = '600';
                senderLabel.style.color = 'var(--gel-text-secondary)';
                senderLabel.style.marginBottom = '4px';
                senderLabel.style.paddingLeft = '4px';
                senderLabel.innerHTML = msg.sender_type === 'secretary' 
                    ? '<i class="fas fa-headset"></i> Secrétariat' 
                    : '<i class="fas fa-calculator"></i> Comptable';
                div.appendChild(senderLabel);
            }

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
