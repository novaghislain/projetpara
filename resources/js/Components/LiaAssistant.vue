<script setup>
import { ref, nextTick } from 'vue';
import { MessageCircle, X, Send, Leaf } from 'lucide-vue-next';

const isOpen = ref(false);
const input = ref('');
const messages = ref([
  { role: 'assistant', text: 'Bonjour ! Je suis Lia, votre assistante bien-être. Comment puis-je vous aider aujourd\'hui ?' }
]);

const handleSend = () => {
  if (!input.value.trim()) return;
  messages.value.push({ role: 'user', text: input.value });
  const userText = input.value;
  input.value = '';
  
  // Mock response
  setTimeout(() => {
    messages.value.push({ role: 'assistant', text: 'Merci pour votre message. Je peux vous recommander nos produits ou vous aider à prendre rendez-vous !' });
  }, 1000);
};
</script>

<template>
  <div class="fixed-bottom d-flex justify-content-end p-4 z-3 pointer-events-none">
    <div class="pointer-events-auto">
      <div v-if="isOpen" class="card border-0 shadow-lg rounded-4 overflow-hidden mb-3" style="width: 350px">
        <!-- Header -->
        <div class="bg-primary p-3 text-white d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px">
              <Leaf :size="20" />
            </div>
            <div>
              <h6 class="fw-bold mb-0">Lia</h6>
              <small class="opacity-75 text-uppercase" style="font-size: 9px">Conseillère Bien-être</small>
            </div>
          </div>
          <button @click="isOpen = false" class="btn btn-link text-white p-0 opacity-75">
            <X :size="20" />
          </button>
        </div>

        <!-- Messages -->
        <div class="card-body p-3 bg-light overflow-auto" style="height: 350px">
          <div class="d-flex flex-column gap-3">
            <div v-for="(msg, i) in messages" :key="i" class="d-flex" :class="msg.role === 'user' ? 'justify-content-end' : 'justify-content-start'">
              <div class="p-3 rounded-4 shadow-sm small" :class="msg.role === 'user' ? 'bg-primary text-white' : 'bg-white text-dark'" style="max-width: 85%">
                {{ msg.text }}
              </div>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="p-3 bg-white border-top">
          <div class="input-group">
            <input 
              v-model="input"
              type="text" 
              class="form-control rounded-pill-start border-0 bg-light px-3 small" 
              placeholder="Posez votre question..." 
              @keypress.enter="handleSend"
            />
            <button @click="handleSend" class="btn btn-primary rounded-pill-end px-3">
              <Send :size="16" />
            </button>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <button 
          @click="isOpen = !isOpen"
          class="btn bg-gold text-white rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0 position-relative transition-all hover-scale"
          style="width: 60px; height: 60px"
        >
          <MessageCircle :size="28" />
          <span v-if="!isOpen" class="position-absolute top-0 end-0 translate-middle-y translate-middle-x mt-2 me-2">
            <span class="position-absolute translate-middle p-2 bg-warning rounded-circle animate-ping"></span>
            <span class="position-absolute translate-middle p-2 bg-warning rounded-circle"></span>
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pointer-events-none { pointer-events: none; }
.pointer-events-auto { pointer-events: auto; }
.rounded-pill-start { border-top-left-radius: 50px; border-bottom-left-radius: 50px; }
.rounded-pill-end { border-top-right-radius: 50px; border-bottom-right-radius: 50px; }
.hover-scale:hover { transform: scale(1.1); }
.animate-slide-up { animation: slideUp 0.3s ease-out; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes ping { 75%, 100% { transform: scale(2); opacity: 0; } }
.animate-ping { animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite; }
</style>
