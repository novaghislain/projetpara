@extends('layouts.gel-secretary')
@section('title', 'Assistant IA — Secrétariat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-robot" style="color:var(--sec-primary); margin-right:8px;"></i>GEL Intelligence
    </h1>
    <p class="sec-page-sub">Votre assistant virtuel pour faciliter vos tâches administratives</p>
  </div>
</div>

<div class="sec-card animate-fade delay-1" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04); display: flex; flex-direction: column; height: calc(100vh - 200px);">
  <div class="sec-card-header" style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:16px 20px;">
    <div style="display:flex; align-items:center; gap:12px;">
      <div style="width:40px; height:40px; border-radius:50%; background:var(--sec-primary-light); color:var(--sec-primary); display:flex; align-items:center; justify-content:center; font-size:18px;">
        <i class="fas fa-sparkles"></i>
      </div>
      <div>
        <div style="font-weight:700; color:#1e293b;">Assistant Secrétariat</div>
        <div style="font-size:12px; color:#64748b;">Propulsé par Gemini & Claude 3.5</div>
      </div>
    </div>
  </div>
  
  <div id="aiChatWindowBody" style="flex:1; overflow-y:auto; padding:20px; background:#ffffff; display:flex; flex-direction:column; gap:16px;">
    <div class="ai-msg received" style="align-self: flex-start; max-width: 80%; padding: 12px 16px; border-radius: 12px; border-bottom-left-radius: 2px; font-size: 14px; line-height: 1.5; background: #F1F5F9; color: #1E293B;">
      Bonjour ! Je suis l'Assistant IA GEL. Je peux vous aider à rédiger des courriers, analyser des documents, ou répondre à des questions sur les dossiers clients. Comment puis-je vous aider aujourd'hui ?
    </div>
  </div>

  <div style="padding:16px 20px; background:#F8FAFC; border-top:1px solid #E2E8F0;">
    <form id="aiChatWindowForm" style="display:flex; gap:10px;">
      <input type="text" id="aiChatWindowInput" placeholder="Posez votre question à l'IA..." style="flex:1; border:1px solid #CBD5E1; border-radius:24px; padding:10px 16px; font-size:14px; outline:none;" autocomplete="off" required>
      <button type="submit" class="sec-btn sec-btn-primary" style="border-radius:24px; padding:0 20px; width: 44px; display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-paper-plane"></i>
      </button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('aiChatWindowForm');
    const input = document.getElementById('aiChatWindowInput');
    const body = document.getElementById('aiChatWindowBody');

    function appendMsg(text, type) {
        const div = document.createElement('div');
        div.className = 'ai-msg ' + type;
        
        if (type === 'sent') {
            div.style.cssText = 'align-self: flex-end; max-width: 80%; padding: 12px 16px; border-radius: 12px; border-bottom-right-radius: 2px; font-size: 14px; line-height: 1.5; background: var(--sec-primary); color: white;';
        } else {
            div.style.cssText = 'align-self: flex-start; max-width: 80%; padding: 12px 16px; border-radius: 12px; border-bottom-left-radius: 2px; font-size: 14px; line-height: 1.5; background: #F1F5F9; color: #1E293B;';
        }
        
        div.innerText = text;
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
        return div;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;

        appendMsg(msg, 'sent');
        input.value = '';
        const loadingDiv = appendMsg('Réflexion en cours...', 'received');

        fetch('{{ route("gel-secretary.ai-chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: msg })
        })
        .then(r => r.json())
        .then(data => {
            body.removeChild(loadingDiv);
            if (data.reply) {
                appendMsg(data.reply, 'received');
            } else {
                appendMsg('Erreur de réponse de l\'IA.', 'received');
            }
        })
        .catch(err => {
            body.removeChild(loadingDiv);
            appendMsg('Erreur réseau : Impossible de contacter l\'IA.', 'received');
            console.error(err);
        });
    });
});
</script>
@endsection
