{{-- Assistant IA Flottant — Inclure sur toutes les pages publiques --}}
{{-- Usage: @include('partials.ai-chat-floating') --}}

<button class="chat-fab" id="chatFab" onclick="toggleChat()" title="Assistant IA (Ctrl+Shift+I)">
    <i id="chatFabIcon" class="bi-robot"></i>
</button>

<div class="chat-window" id="chatWindow" style="display: none;">
    <div class="chat-header">
        <div class="chat-header-left">
            <div class="chat-avatar"><i class="bi-robot"></i></div>
            <div>
                <h4>GEL Assistant</h4>
                <span class="chat-status"><span class="status-dot"></span> En ligne</span>
            </div>
        </div>
        <div class="chat-header-actions">
            <button onclick="minimizeChat()" title="Réduire"><i class="bi-dash-lg"></i></button>
            <button onclick="closeChat()" title="Fermer"><i class="bi-x-lg"></i></button>
        </div>
    </div>
    <div class="chat-messages" id="chatMessages">
        <div class="message message-assistant">
            <div class="message-content">
                <p>Bonjour ! 👋 Je suis <strong>GEL Assistant</strong>. Je peux vous présenter nos modules, vous guider dans la plateforme, ou répondre à vos questions. Comment puis-je vous aider ?</p>
            </div>
        </div>
    </div>
    <div class="chat-input-area">
        <input type="text" id="chatInput" placeholder="Posez votre question..." onkeypress="if(event.key==='Enter') sendChat()">
        <button onclick="sendChat()"><i class="bi-send"></i></button>
    </div>
</div>

<script>
// Chat IA — Fonctions globales
function toggleChat() {
    const w = document.getElementById('chatWindow');
    const fab = document.getElementById('chatFab');
    if (w.style.display === 'none' || w.style.display === '') {
        w.style.display = 'flex';
        w.classList.remove('minimized');
        fab.style.display = 'none';
        setTimeout(() => { const inp = document.getElementById('chatInput'); if(inp) inp.focus(); }, 300);
    } else {
        w.style.display = 'none';
        fab.style.display = 'flex';
    }
}

function closeChat() {
    document.getElementById('chatWindow').style.display = 'none';
    document.getElementById('chatFab').style.display = 'flex';
}

function minimizeChat() {
    const w = document.getElementById('chatWindow');
    w.classList.toggle('minimized');
}

function sendChat() {
    const input = document.getElementById('chatInput');
    const msg = input.value.trim();
    if (!msg) return;

    const messages = document.getElementById('chatMessages');
    const userDiv = document.createElement('div');
    userDiv.className = 'message message-user';
    userDiv.innerHTML = '<div class="message-content"><p>' + escapeHtml(msg) + '</p></div>';
    messages.appendChild(userDiv);
    input.value = '';
    messages.scrollTop = messages.scrollHeight;

    setTimeout(() => {
        const response = getChatResponse(msg);
        const botDiv = document.createElement('div');
        botDiv.className = 'message message-assistant';
        botDiv.innerHTML = '<div class="message-content">' + response + '</div>';
        messages.appendChild(botDiv);
        messages.scrollTop = messages.scrollHeight;
        saveChatHistory();
    }, 800);
    saveChatHistory();
}

function getChatResponse(msg) {
    const m = msg.toLowerCase();
    if (m.includes('bonjour') || m.includes('salut') || m.includes('bonsoir'))
        return '<p>Bonjour ! 👋 Je suis <strong>GEL Assistant</strong>. Comment puis-je vous aider avec votre cabinet ?</p>';

    if (m.includes('module') || m.includes('fonctionnalité'))
        return '<p>GEL Cabinet propose <strong>15+ modules interconnectés</strong> : CRM, Comptabilité SYSCOHADA, GED, ERP, RH & Paie, Juridique, Caisse, Projets, IT Support, et plus encore. Quel module vous intéresse ?</p>';

    if (m.includes('tarif') || m.includes('prix') || m.includes('coût') || m.includes('combien'))
        return '<p>Nos formules sont adaptées à la taille de votre cabinet. Consultez notre page <a href="/tarifs" style="color:#FF7900;font-weight:600;">Tarifs</a> ou <a href="/contact" style="color:#FF7900;font-weight:600;">contactez notre équipe</a> pour un devis personnalisé.</p>';

    if (m.includes('compta') || m.includes('syscohada') || m.includes('ohada') || m.includes('comptabilité'))
        return '<p>Notre <strong>module Comptabilité</strong> couvre : plan comptable SYSCOHADA, journaux (ventes, achats, banque, caisse, OD), balance générale et auxiliaire, grand livre, bilan et compte de résultat. Conforme OHADA et e-MECeF.</p>';

    if (m.includes('ia') || m.includes('intelligence') || m.includes('agent') || m.includes('robot'))
        return '<p><strong>GEL Intelligence</strong> — 6 agents IA spécialisés :</p><ul style="margin:4px 0;padding-left:16px;"><li>📊 Agent OHADA — Catégorisation automatique</li><li>💰 Agent Relance — Canal optimal, escalade</li><li>🏦 Agent Rapprochement — Matching bancaire</li><li>📋 Agent Fiscal — Déclarations automatisées</li><li>📄 Agent OCR — Import factures fournisseurs</li><li>🔮 Agent Trésorerie — Prédiction cash flow</li></ul><p>Chaque suggestion est soumise à votre approbation.</p>';

    if (m.includes('contact') || m.includes('support') || m.includes('téléphone') || m.includes('email'))
        return '<p>📧 <strong>Email</strong> : <a href="mailto:contact@gelcabinet.com" style="color:#FF7900;">contact@gelcabinet.com</a><br>📞 <strong>Téléphone</strong> : +229 XX XX XX XX<br>🕐 <strong>Horaires</strong> : Lun–Ven, 8h–18h</p>';

    if (m.includes('inscription') || m.includes('créer') || m.includes('compte') || m.includes('s\'inscrire'))
        return '<p>Rendez-vous sur notre <a href="/register" style="color:#FF7900;font-weight:600;">page d\'inscription</a>. Créez votre compte gratuitement, configurez vos modules et vous êtes opérationnel. Essai gratuit 30 jours, sans carte bancaire.</p>';

    return '<p>Je suis <strong>GEL Assistant</strong>, votre assistant virtuel. Je peux vous renseigner sur :</p><ul style="margin:4px 0;padding-left:16px;"><li>🔹 Présentation de GEL Cabinet</li><li>🔹 Nos modules et fonctionnalités</li><li>🔹 Tarifs et formules</li><li>🔹 Intelligence Artificielle</li><li>🔹 Contact et support</li></ul><p>Que souhaitez-vous savoir ?</p>';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function saveChatHistory() {
    const msgs = document.getElementById('chatMessages');
    sessionStorage.setItem('gel_chat_history', msgs.innerHTML);
}

function loadChatHistory() {
    const saved = sessionStorage.getItem('gel_chat_history');
    if (saved) {
        const msgs = document.getElementById('chatMessages');
        msgs.innerHTML = saved;
        msgs.scrollTop = msgs.scrollHeight;
    }
}

// Keyboard shortcut Ctrl+Shift+I
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
        e.preventDefault();
        toggleChat();
    }
});

// Load chat history
loadChatHistory();
</script>
