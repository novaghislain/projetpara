<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const props = defineProps({
    pageTitle: { type: String, default: 'Assistant Eden Cabinet' }
});

const messages = ref([]);
const newMessage = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);
const error = ref(null);

const welcomeMessage = {
    role: 'assistant',
    content: 'Bonjour ! Je suis **Eden Cabinet**, votre assistant IA dédié à la gestion d\'entreprise.\n\nJe peux vous aider avec :\n\n• **Comptabilité** : plan comptable, journaux, rapports\n• **Facturation** : devis, paiements, relances\n• **Juridique** : contrats, termes courants\n• **RH** : gestion du personnel\n• **CRM** : suivi clients et prospects\n\nComment puis-je vous assister aujourd\'hui ?',
    timestamp: new Date().toISOString(),
};

const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
const apiHeaders = { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };

async function sendMessage() {
    const text = newMessage.value.trim();
    if (!text || isTyping.value) return;

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

        if (!res.ok) throw new Error(`Erreur serveur: ${res.status}`);

        const data = await res.json();

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
    });
}

function formatMessage(text) {
    if (!text) return '';
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

onMounted(() => {
    messages.value.push(welcomeMessage);
});
</script>

<template>
    <CompanyLayout page-title="Assistant IA">
        <div class="isup-shell">
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo"><i class="bi-robot" style="font-size:20px;"></i></div>
                    <div>
                        <div class="isup-portal-company">Assistant Eden Cabinet</div>
                        <div class="isup-portal-sub"><i class="bi-lightning-charge me-1"></i>Intelligence Artificielle au service de votre entreprise</div>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <!-- Zone de chat -->
                <div class="isup-chat-panel">
                    <div ref="chatContainer" class="isup-chat-body">
                        <!-- Messages vides -->
                        <div v-if="messages.length === 0" class="text-center py-5 isup-chat-empty">
                            <i class="bi-chat-dots" style="font-size:2.5rem;color:#dce3ee;"></i>
                            <p class="mt-2" style="color:#aaa;">Commencez une conversation avec l'assistant.</p>
                        </div>

                        <!-- Messages -->
                        <div v-for="(msg, index) in messages" :key="index" class="mb-3">
                            <!-- Assistant -->
                            <div v-if="msg.role === 'assistant'" class="isup-msg-row">
                                <div class="isup-msg-avatar isup-msg-avatar-ai"><i class="bi-robot"></i></div>
                                <div class="isup-msg-content">
                                    <div class="isup-msg-bubble isup-msg-bubble-ai">
                                        <div class="isup-msg-text" v-html="formatMessage(msg.content)"></div>
                                    </div>
                                    <div class="isup-msg-time">{{ formatTime(msg.timestamp) }}</div>
                                </div>
                            </div>

                            <!-- Utilisateur -->
                            <div v-else class="isup-msg-row isup-msg-row-user">
                                <div class="isup-msg-content">
                                    <div class="isup-msg-bubble isup-msg-bubble-user">
                                        <div class="isup-msg-text" v-html="formatMessage(msg.content)"></div>
                                    </div>
                                    <div class="isup-msg-time isup-msg-time-user">{{ formatTime(msg.timestamp) }}</div>
                                </div>
                                <div class="isup-msg-avatar isup-msg-avatar-user"><i class="bi-person"></i></div>
                            </div>
                        </div>

                        <!-- Typing indicator -->
                        <div v-if="isTyping" class="isup-msg-row">
                            <div class="isup-msg-avatar isup-msg-avatar-ai"><i class="bi-robot"></i></div>
                            <div class="isup-msg-bubble isup-msg-bubble-ai">
                                <div class="isup-typing-indicator"><span></span><span></span><span></span></div>
                            </div>
                        </div>

                        <!-- Erreur -->
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                    </div>

                    <!-- Barre de saisie -->
                    <div class="isup-chat-footer">
                        <div class="d-flex gap-2 align-items-end">
                            <div class="flex-grow-1">
                                <textarea v-model="newMessage" @keydown="handleKeydown"
                                    class="isup-input" rows="1"
                                    placeholder="Posez votre question..."
                                    style="resize:none;min-height:44px;max-height:120px;"
                                    :disabled="isTyping"></textarea>
                            </div>
                            <button class="isup-btn-primary" style="min-width:44px;height:44px;border-radius:4px;"
                                :disabled="!newMessage.trim() || isTyping"
                                @click="sendMessage" title="Envoyer">
                                <i v-if="isTyping" class="isup-spinner-sm"></i>
                                <i v-else class="bi-send-fill"></i>
                            </button>
                        </div>
                        <div class="small mt-2" style="color:#aaa;font-size:11px;">
                            <i class="bi-info-circle me-1"></i>
                            Appuyez sur <kbd style="background:#f0f4f8;border:1px solid #dce3ee;border-radius:3px;padding:1px 5px;font-size:10px;">Entr&eacute;e</kbd> pour envoyer,
                            <kbd style="background:#f0f4f8;border:1px solid #dce3ee;border-radius:3px;padding:1px 5px;font-size:10px;">Shift+Entr&eacute;e</kbd> pour une nouvelle ligne.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
/* ── AI Assistant-specific styles ── */
.isup-chat-panel { background:#fff;border:1px solid #dce3ee;border-radius:4px;overflow:hidden; }
.isup-chat-body { padding:16px;height:460px;overflow-y:auto;background:#f8fafc; }
.isup-chat-body::-webkit-scrollbar { width:6px; }
.isup-chat-body::-webkit-scrollbar-thumb { background:#ccc;border-radius:3px; }
.isup-chat-footer { padding:12px 16px;border-top:1px solid #f0f4f8;background:#fff; }
.isup-msg-row { display:flex;align-items:flex-start;gap:10px;margin-bottom:16px; }
.isup-msg-row-user { flex-direction:row-reverse; }
.isup-msg-avatar { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0; }
.isup-msg-avatar-ai { background:#e3f2fd;color:#1565c0; }
.isup-msg-avatar-user { background:#163A5E;color:#fff; }
.isup-msg-content { max-width:80%; }
.isup-msg-bubble { padding:10px 14px;border-radius:6px;font-size:13px;line-height:1.5; }
.isup-msg-bubble-ai { background:#fff;border:1px solid #dce3ee;color:#333; }
.isup-msg-bubble-user { background:#163A5E;color:#fff; }
.isup-msg-text { word-wrap:break-word; }
.isup-msg-text :deep(strong) { font-weight:700; }
.isup-msg-text :deep(br) { display:block;margin:4px 0;content:''; }
.isup-msg-time { font-size:10px;color:#aaa;margin-top:4px;padding-left:4px; }
.isup-msg-time-user { text-align:right;padding-right:4px; }
.isup-typing-indicator { display:flex;align-items:center;gap:4px;padding:2px 0; }
.isup-typing-indicator span { display:inline-block;width:8px;height:8px;border-radius:50%;background:#1565c0;animation:typing 1.4s infinite ease-in-out both; }
.isup-typing-indicator span:nth-child(1) { animation-delay:-0.32s; }
.isup-typing-indicator span:nth-child(2) { animation-delay:-0.16s; }
.isup-typing-indicator span:nth-child(3) { animation-delay:0s; }
@keyframes typing { 0%,80%,100% { transform:scale(0.6);opacity:0.4; } 40% { transform:scale(1);opacity:1; } }
</style>
