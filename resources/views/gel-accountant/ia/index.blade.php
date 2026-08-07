@extends('layouts.gel-accountant')

@section('title', 'Assistant IA - GEL Accountant')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-robot" style="color:var(--gel-primary); margin-right:8px;"></i> Assistant IA Expert-Comptable</h1>
        <p class="gel-page-subtitle">Suggestions intelligentes et assistance comptable.</p>
    </div>
</div>

<div class="row">
    <!-- Colonne Suggestions IA -->
    <div class="col-md-7">
        <div class="gel-card p-4 mb-4" style="min-height: 500px;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px;"><i class="fas fa-lightbulb me-2 text-warning"></i> Suggestions & Alertes</h3>
            
            <div class="ia-suggestions-list">
                @forelse($suggestions as $suggestion)
                    <div class="suggestion-item p-3 mb-3" style="border:1px solid var(--gel-border); border-radius:8px; border-left:4px solid {{ $suggestion->priority === 'high' ? '#e11d48' : ($suggestion->priority === 'normal' ? '#2563eb' : '#16a34a') }};">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <h4 style="font-size:14px; font-weight:600; margin:0;">
                                @if($suggestion->type === 'anomaly') <i class="fas fa-exclamation-triangle text-danger me-1"></i> 
                                @elseif($suggestion->type === 'optimization') <i class="fas fa-chart-line text-primary me-1"></i>
                                @else <i class="fas fa-info-circle text-success me-1"></i> @endif
                                {{ $suggestion->title }}
                            </h4>
                            <span style="font-size:11px; color:var(--gel-text-secondary);">{{ \Carbon\Carbon::parse($suggestion->created_at)->diffForHumans() }}</span>
                        </div>
                        <p style="font-size:13px; color:var(--gel-text-secondary); margin:0;">{{ $suggestion->message }}</p>
                    </div>
                @empty
                    <div class="text-center py-5" style="color:var(--gel-text-secondary);">
                        <i class="fas fa-check-circle" style="font-size:32px; color:#16a34a; margin-bottom:12px;"></i>
                        <p>Aucune anomalie ou suggestion pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Colonne Chat IA -->
    <div class="col-md-5">
        <div class="gel-card p-0 mb-4" style="height: 500px; display:flex; flex-direction:column;">
            <div class="p-3" style="border-bottom:1px solid var(--gel-border); background:var(--gel-sidebar-bg); border-radius: 8px 8px 0 0;">
                <h3 style="font-size: 14px; font-weight: 600; margin: 0; color:white;"><i class="fas fa-comments me-2"></i> Chatbot Assistant</h3>
            </div>
            
            <div class="chat-messages p-3" id="chatMessages" style="flex:1; overflow-y:auto; background:#f8fafc;">
                <div class="chat-message ia-message mb-3" style="display:flex; gap:10px;">
                    <div class="avatar" style="width:32px; height:32px; background:var(--gel-primary); border-radius:50%; color:white; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="bubble p-2" style="background:white; border:1px solid var(--gel-border); border-radius:8px; font-size:13px; max-width:85%;">
                        Bonjour ! Je suis votre assistant IA. Posez-moi une question sur le plan SYSCOHADA, la fiscalité ou demandez-moi d'analyser vos données.
                    </div>
                </div>
            </div>

            <div class="chat-input p-3" style="border-top:1px solid var(--gel-border); background:white; border-radius: 0 0 8px 8px;">
                <form id="chatForm" style="display:flex; gap:10px;">
                    <input type="text" id="chatInput" class="gel-input" placeholder="Posez votre question..." style="flex:1; padding:8px 12px; font-size:13px;" required>
                    <button type="submit" class="gel-btn gel-btn-primary" style="padding:8px 12px;"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;
    
    // Add user message to chat
    addMessage(message, 'user');
    input.value = '';
    
    // Send to server
    fetch('{{ route("gel-accountant.ia.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        addMessage(data.reply, 'ia');
    })
    .catch(error => {
        addMessage("Désolé, une erreur est survenue lors de la communication avec l'IA.", 'ia');
    });
});

function addMessage(text, sender) {
    const chatContainer = document.getElementById('chatMessages');
    const isUser = sender === 'user';
    
    const wrapper = document.createElement('div');
    wrapper.className = `chat-message mb-3`;
    wrapper.style.display = 'flex';
    wrapper.style.gap = '10px';
    if (isUser) wrapper.style.flexDirection = 'row-reverse';
    
    const avatar = document.createElement('div');
    avatar.className = 'avatar';
    avatar.style.width = '32px';
    avatar.style.height = '32px';
    avatar.style.borderRadius = '50%';
    avatar.style.display = 'flex';
    avatar.style.alignItems = 'center';
    avatar.style.justifyContent = 'center';
    avatar.style.flexShrink = '0';
    avatar.style.color = 'white';
    avatar.style.background = isUser ? '#475569' : 'var(--gel-primary)';
    avatar.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
    
    const bubble = document.createElement('div');
    bubble.className = 'bubble p-2';
    bubble.style.border = '1px solid var(--gel-border)';
    bubble.style.borderRadius = '8px';
    bubble.style.fontSize = '13px';
    bubble.style.maxWidth = '85%';
    bubble.style.background = isUser ? '#f1f5f9' : 'white';
    bubble.innerText = text;
    
    wrapper.appendChild(avatar);
    wrapper.appendChild(bubble);
    
    chatContainer.appendChild(wrapper);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}
</script>

@endsection
