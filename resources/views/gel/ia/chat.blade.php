@extends('layouts.gel')

@section('title', 'Chat IA - GEL Cabinet')

@section('styles')
<style>
    .chat-layout {
        display: flex;
        gap: 1.5rem;
        height: calc(100vh - 160px);
        min-height: 500px;
    }
    .chat-sidebar {
        width: 300px;
        flex-shrink: 0;
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .chat-sidebar-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gel-border);
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-sidebar-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem;
    }
    .conv-item {
        padding: 0.75rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 0.25rem;
    }
    .conv-item:hover { background: var(--gel-bg); }
    .conv-item.active { background: rgba(99,91,255,0.08); border-left: 3px solid var(--gel-accent-2); }
    .conv-item .conv-title { font-weight: 600; font-size: 0.85rem; }
    .conv-item .conv-meta { font-size: 0.75rem; color: var(--gel-text-muted); }

    .chat-main {
        flex: 1;
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .chat-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gel-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafbfc;
    }
    .chat-header .chat-title { font-weight: 700; font-size: 0.95rem; }
    .chat-context-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .message {
        max-width: 80%;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-size: 0.9rem;
        line-height: 1.5;
        animation: fadeIn 0.3s ease;
    }
    .message.user {
        align-self: flex-end;
        background: var(--gel-accent-2);
        color: white;
        border-bottom-right-radius: 4px;
    }
    .message.assistant {
        align-self: flex-start;
        background: var(--gel-bg);
        color: var(--gel-primary);
        border-bottom-left-radius: 4px;
    }
    .message.system {
        align-self: center;
        background: #fffbeb;
        color: #92400e;
        font-size: 0.8rem;
        border: 1px solid #fde68a;
    }
    .message .msg-time {
        font-size: 0.65rem;
        opacity: 0.6;
        margin-top: 0.25rem;
    }
    .message.assistant .msg-time { color: var(--gel-text-muted); }

    .chat-input-area {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--gel-border);
        background: white;
    }
    .chat-input-wrapper {
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
    }
    .chat-input-wrapper textarea {
        flex: 1;
        border: 1px solid var(--gel-border);
        border-radius: 10px;
        padding: 0.65rem 1rem;
        font-size: 0.9rem;
        resize: none;
        font-family: inherit;
        outline: none;
        transition: border-color 0.2s;
        min-height: 44px;
        max-height: 120px;
    }
    .chat-input-wrapper textarea:focus { border-color: var(--gel-accent-2); }
    .chat-input-wrapper .send-btn {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        border: none;
        background: var(--gel-accent-2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .chat-input-wrapper .send-btn:hover { opacity: 0.9; transform: scale(1.02); }
    .chat-input-wrapper .send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .suggestion-chips {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        padding: 0.75rem 1.25rem 0;
    }
    .suggestion-chip {
        padding: 0.3rem 0.75rem;
        border-radius: 20px;
        background: var(--gel-bg);
        border: 1px solid var(--gel-border);
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.2s;
        color: var(--gel-text-muted);
    }
    .suggestion-chip:hover { border-color: var(--gel-accent-2); color: var(--gel-accent-2); }

    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 0;
    }
    .typing-indicator span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--gel-text-muted);
        animation: typing 1.4s infinite ease-in-out;
    }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing {
        0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
        30% { opacity: 1; transform: translateY(-4px); }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .context-selector {
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        border: 1px solid var(--gel-border);
        font-size: 0.8rem;
        font-family: inherit;
        background: white;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">Chat IA</h1>
        <p class="page-subtitle">Assistant intelligent pour votre cabinet</p>
    </div>
</div>

<div class="chat-layout">
    {{-- Sidebar conversations --}}
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <span><i class="bi bi-chat-dots me-1"></i>Conversations</span>
            <button class="btn btn-sm btn-primary" onclick="nouvelleConversation()" style="background:var(--gel-accent-2);border:none;">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <div class="chat-sidebar-list" id="conversationsList">
            @forelse($conversations as $conv)
            <div class="conv-item {{ $loop->first ? 'active' : '' }}"
                 data-id="{{ $conv->id }}"
                 onclick="chargerConversation({{ $conv->id }})">
                <div class="conv-title">{{ $conv->title }}</div>
                <div class="conv-meta">
                    {{ $conv->created_at->diffForHumans() }}
                    @if($conv->contexte)
                    &bull; <span class="badge bg-light text-dark" style="font-size:0.65rem;">{{ $conv->contexte }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="bi bi-chat d-block mb-2" style="font-size:1.5rem;"></i>
                <small>Aucune conversation</small>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Zone de chat --}}
    <div class="chat-main" id="chatMain">
        <div class="chat-header">
            <div>
                <span class="chat-title" id="chatTitle">
                    {{ $conversations->first()->title ?? 'Nouvelle conversation' }}
                </span>
                <span class="chat-context-badge bg-light text-dark ms-2" id="chatContext">
                    {{ $conversations->first()->contexte ?? 'general' }}
                </span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <select class="context-selector" id="nouveauContexte" onchange="changerContexte(this.value)">
                    <option value="general">Général</option>
                    <option value="comptabilite">Comptabilité</option>
                    <option value="fiscal">Fiscal</option>
                    <option value="paie">Paie & RH</option>
                    <option value="client">Client</option>
                    <option value="juridique">Juridique</option>
                </select>
                <button class="btn btn-sm btn-outline-secondary" onclick="exporterConversation()" title="Exporter">
                    <i class="bi bi-download"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="supprimerConversation()" title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div class="chat-messages" id="chatMessages">
            <div class="message assistant">
                Bonjour ! Je suis votre assistant IA. Posez-moi des questions sur la comptabilité, la fiscalité, la paie ou la gestion de votre cabinet.
                <div class="msg-time">À l'instant</div>
            </div>
        </div>

        {{-- Suggestions --}}
        <div class="suggestion-chips" id="suggestionChips">
            <span class="suggestion-chip" onclick="envoyerSuggestion(this)">Analyser un compte</span>
            <span class="suggestion-chip" onclick="envoyerSuggestion(this)">Calculer la TVA</span>
            <span class="suggestion-chip" onclick="envoyerSuggestion(this)">Prévision trésorerie</span>
            <span class="suggestion-chip" onclick="envoyerSuggestion(this)">Détection anomalies</span>
        </div>

        {{-- Input --}}
        <div class="chat-input-area">
            <div class="chat-input-wrapper">
                <textarea id="chatInput" rows="1" placeholder="Posez votre question..." onkeydown="handleKey(event)"></textarea>
                <button class="send-btn" id="sendBtn" onclick="envoyerMessage()">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let conversationActiveId = {{ $conversations->first()->id ?? 'null' }};
    let envoiEnCours = false;
    const zoneMessages = document.getElementById('chatMessages');
    const inputChat = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');

    // ─── Auto-resize textarea ───
    inputChat?.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // ─── Envoyer avec Entrée (Shift+Entrée = nouvelle ligne) ───
    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            envoyerMessage();
        }
    }

    // ─── Envoyer un message ───
    function envoyerMessage() {
        const message = inputChat?.value.trim();
        if (!message || envoiEnCours || !conversationActiveId) return;

        envoiEnCours = true;
        sendBtn.disabled = true;

        // Afficher le message utilisateur
        ajouterMessage('user', message);
        inputChat.value = '';
        inputChat.style.height = 'auto';

        // Afficher l'indicateur de frappe
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message assistant';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
        zoneMessages.appendChild(typingDiv);
        zoneMessages.scrollTop = zoneMessages.scrollHeight;

        fetch(`/api/ai/chat/conversations/${conversationActiveId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message }),
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('typingIndicator')?.remove();
            if (data.success) {
                ajouterMessage('assistant', data.message, data.suggestions);
            } else {
                ajouterMessage('assistant', 'Désolé, une erreur est survenue. Veuillez réessayer.');
            }
        })
        .catch(() => {
            document.getElementById('typingIndicator')?.remove();
            ajouterMessage('assistant', 'Erreur de connexion. Veuillez réessayer.');
        })
        .finally(() => {
            envoiEnCours = false;
            sendBtn.disabled = false;
            inputChat.focus();
        });
    }

    // ─── Ajouter un message ───
    function ajouterMessage(role, contenu, suggestions) {
        const div = document.createElement('div');
        div.className = `message ${role}`;
        div.innerHTML = `
            ${contenu}
            <div class="msg-time">${new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</div>
        `;
        zoneMessages.appendChild(div);

        if (suggestions && suggestions.length > 0) {
            const suggestDiv = document.createElement('div');
            suggestDiv.className = 'suggestion-chips';
            suggestDiv.style.padding = '0 0 0 1rem';
            suggestDiv.innerHTML = suggestions.map(s =>
                `<span class="suggestion-chip" onclick="envoyerSuggestion(this)">${s}</span>`
            ).join('');
            zoneMessages.appendChild(suggestDiv);
        }

        zoneMessages.scrollTop = zoneMessages.scrollHeight;
    }

    // ─── Créer une nouvelle conversation ───
    function nouvelleConversation() {
        const titre = prompt('Titre de la conversation :');
        if (!titre) return;

        const contexte = document.getElementById('nouveauContexte')?.value || 'general';

        fetch('/api/ai/chat/conversations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ title: titre, contexte }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    // ─── Charger une conversation ───
    function chargerConversation(id) {
        conversationActiveId = id;
        document.querySelectorAll('.conv-item').forEach(c => c.classList.remove('active'));
        document.querySelector(`.conv-item[data-id="${id}"]`)?.classList.add('active');

        fetch(`/api/ai/chat/conversations/${id}/history`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('chatTitle').textContent = data.conversation.title;
                document.getElementById('chatContext').textContent = data.conversation.contexte || 'general';
                zoneMessages.innerHTML = '';
                data.messages.forEach(m => ajouterMessage(m.role, m.content));
                if (!data.messages.length) {
                    zoneMessages.innerHTML = '<div class="message assistant">Bonjour ! Je suis votre assistant IA. Posez-moi vos questions.<div class="msg-time">À l\'instant</div></div>';
                }
            }
        });
    }

    // ─── Envoyer une suggestion ───
    function envoyerSuggestion(el) {
        inputChat.value = el.textContent.trim();
        envoyerMessage();
    }

    // ─── Exporter la conversation ───
    function exporterConversation() {
        if (!conversationActiveId) return;
        window.open(`/api/ai/chat/conversations/${conversationActiveId}/export/txt`, '_blank');
    }

    // ─── Supprimer la conversation ───
    function supprimerConversation() {
        if (!conversationActiveId || !confirm('Supprimer cette conversation ?')) return;
        fetch(`/api/ai/chat/conversations/${conversationActiveId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
    }

    // ─── Changer le contexte par défaut ───
    function changerContexte(value) {
        // Le contexte sera utilisé pour la prochaine nouvelle conversation
        console.log('Contexte sélectionné :', value);
    }

    // ─── Focus input au chargement ───
    document.addEventListener('DOMContentLoaded', () => inputChat?.focus());
</script>
@endsection
