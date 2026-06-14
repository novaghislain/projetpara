<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { authStore } from '../../stores/auth';

const props = defineProps({
    pageTitle: { type: String, default: 'Assistant Eden Cabinet' }
});

// ─── État du chat ──────────────────────────────────────────────
const messages = ref([]);
const newMessage = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);
const error = ref(null);

// ─── Message de bienvenue ──────────────────────────────────────
const welcomeMessage = {
    role: 'assistant',
    content: 'Bonjour ! Je suis **Eden Cabinet**, votre assistant IA dédié à la gestion d\'entreprise.\n\nJe peux vous aider avec :\n\n• **Comptabilité** : plan comptable, journaux, rapports\n• **Facturation** : devis, paiements, relances\n• **Juridique** : contrats, termes courants\n• **RH** : gestion du personnel\n• **CRM** : suivi clients et prospects\n\nComment puis-je vous assister aujourd\'hui ?',
    timestamp: new Date().toISOString(),
};

// ─── API ───────────────────────────────────────────────────────
const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
const apiHeaders = { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };

async function sendMessage() {
    const text = newMessage.value.trim();
    if (!text || isTyping.value) return;

    // Ajouter le message utilisateur
    messages.value.push({
        role: 'user',
        content: text,
        timestamp: new Date().toISOString(),
    });

    newMessage.value = '';
    isTyping.value = true;
    error.value = null;

    scrollToBottom();

    try {
        const res = await fetch('/api/company/ai/chat', {
            method: 'POST',
            headers: { ...apiHeaders, 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text }),
        });

        if (!res.ok) {
            throw new Error(`Erreur serveur: ${res.status}`);
        }

        const data = await res.json();

        // Ajouter la réponse de l'assistant
        messages.value.push({
            role: 'assistant',
            content: data.response,
            timestamp: data.timestamp || new Date().toISOString(),
        });
    } catch (e) {
        error.value = e.message || 'Impossible de contacter l\'assistant. Veuillez réessayer.';
        console.error('Chat error:', e);
    } finally {
        isTyping.value = false;
        scrollToBottom();
    }
}

// ─── Helpers ───────────────────────────────────────────────────
function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
}

function formatTime(isoString) {
    if (!isoString) return '';
    const date = new Date(isoString);
    const now = new Date();
    const diffMs = now - date;
    const diffMin = Math.floor(diffMs / 60000);
    const diffHour = Math.floor(diffMs / 3600000);
    const diffDay = Math.floor(diffMs / 86400000);

    if (diffMin < 1) return "À l'instant";
    if (diffMin < 60) return `Il y a ${diffMin} min`;
    if (diffHour < 24) return `Il y a ${diffHour} h`;
    if (diffDay < 7) return `Il y a ${diffDay} j`;

    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatMessage(text) {
    if (!text) return '';
    // Convertisseur simple markdown-like : **texte** => <strong>texte</strong>, \n => <br>
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>');
}

function handleKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// ─── Init ──────────────────────────────────────────────────────
onMounted(() => {
    messages.value.push(welcomeMessage);
});
</script>

<template>
    <div class="ai-assistant-container">
        <!-- Header -->
        <div class="card border-0 rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-4"
                 style="background: linear-gradient(135deg, #0d1b2a 0%, #1a237e 100%); color: white;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: rgba(255,255,255,0.15);">
                        <i class="bi-robot" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 font-heading">Assistant Eden Cabinet</h4>
                        <p class="mb-0 opacity-75 small">
                            <i class="bi-lightning-charge me-1"></i>
                            Intelligence Artificielle au service de votre entreprise
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone de chat -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div ref="chatContainer"
                 class="card-body p-4"
                 style="height: 480px; overflow-y: auto; background: #f8f9fa;">
                <!-- Messages -->
                <div v-if="messages.length === 0" class="text-center text-muted py-5">
                    <i class="bi-chat-dots" style="font-size: 3rem;"></i>
                    <p class="mt-2">Commencez une conversation avec l'assistant.</p>
                </div>

                <div v-for="(msg, index) in messages" :key="index" class="mb-3">
                    <!-- Message assistant -->
                    <div v-if="msg.role === 'assistant'" class="d-flex align-items-start gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 36px; height: 36px; background: #e8eaf6; color: #1a237e;">
                            <i class="bi-robot small"></i>
                        </div>
                        <div class="flex-grow-1" style="max-width: 85%;">
                            <div class="bg-white rounded-3 p-3 shadow-sm">
                                <div class="small mb-0" v-html="formatMessage(msg.content)"></div>
                            </div>
                            <div class="text-muted small mt-1 ps-1">
                                {{ formatTime(msg.timestamp) }}
                            </div>
                        </div>
                    </div>

                    <!-- Message utilisateur -->
                    <div v-else class="d-flex align-items-start gap-2 flex-row-reverse">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 36px; height: 36px; background: #1a237e; color: white;">
                            <i class="bi-person small"></i>
                        </div>
                        <div class="flex-grow-1" style="max-width: 85%;">
                            <div class="bg-primary text-white rounded-3 p-3">
                                <div class="small mb-0" v-html="formatMessage(msg.content)"></div>
                            </div>
                            <div class="text-muted small mt-1 text-end pe-1">
                                {{ formatTime(msg.timestamp) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Indicateur de frappe -->
                <div v-if="isTyping" class="d-flex align-items-start gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 36px; height: 36px; background: #e8eaf6; color: #1a237e;">
                        <i class="bi-robot small"></i>
                    </div>
                    <div class="bg-white rounded-3 p-3 shadow-sm">
                        <div class="typing-indicator">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>

                <!-- Erreur -->
                <div v-if="error" class="alert alert-danger py-2 small mb-0">
                    <i class="bi-exclamation-triangle me-1"></i>
                    {{ error }}
                </div>
            </div>

            <!-- Barre de saisie -->
            <div class="card-footer bg-white border-0 px-4 py-3">
                <div class="d-flex gap-2 align-items-end">
                    <div class="flex-grow-1">
                        <textarea
                            v-model="newMessage"
                            @keydown="handleKeydown"
                            class="form-control border-0 bg-light rounded-3"
                            rows="1"
                            placeholder="Posez votre question..."
                            style="resize: none; min-height: 44px; max-height: 120px;"
                            :disabled="isTyping"
                        ></textarea>
                    </div>
                    <button
                        class="btn btn-primary rounded-3 d-flex align-items-center justify-content-center"
                        style="min-width: 44px; height: 44px;"
                        :disabled="!newMessage.trim() || isTyping"
                        @click="sendMessage"
                        title="Envoyer"
                    >
                        <i v-if="isTyping" class="spinner-border spinner-border-sm"></i>
                        <i v-else class="bi-send-fill"></i>
                    </button>
                </div>
                <div class="text-muted small mt-2">
                    <i class="bi-info-circle me-1"></i>
                    Appuyez sur <kbd class="bg-light border">Entr&eacute;e</kbd> pour envoyer,
                    <kbd class="bg-light border">Shift+Entr&eacute;e</kbd> pour une nouvelle ligne.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ai-assistant-container {
    min-height: 400px;
}

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 2px 0;
}

.typing-indicator span {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1a237e;
    animation: typing 1.4s infinite ease-in-out both;
}

.typing-indicator span:nth-child(1) {
    animation-delay: -0.32s;
}

.typing-indicator span:nth-child(2) {
    animation-delay: -0.16s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0s;
}

@keyframes typing {
    0%, 80%, 100% {
        transform: scale(0.6);
        opacity: 0.4;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Scrollbar personnalisée */
#chatContainer::-webkit-scrollbar {
    width: 6px;
}

#chatContainer::-webkit-scrollbar-track {
    background: transparent;
}

#chatContainer::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

#chatContainer::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}
</style>
