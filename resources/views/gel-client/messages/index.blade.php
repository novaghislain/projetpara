@extends('layouts.portal')

@section('title', 'Messagerie & Documents')

@section('content')

<div class="portal-page-header portal-mb-6">
    <h1 class="portal-title"><i class="fas fa-comments" style="color:var(--portal-accent); margin-right:8px;"></i> Messagerie & Documents</h1>
    <p class="portal-subtitle">Communiquez avec votre cabinet comptable et partagez des documents de manière sécurisée.</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
    
    {{-- Espace Chat --}}
    <div class="portal-card" style="display: flex; flex-direction: column; height: 600px; padding: 0;">
        <div style="padding: 20px; border-bottom: 1px solid var(--portal-border); background: #f8fafc; border-radius: 12px 12px 0 0;">
            <h2 style="font-size: 16px; font-weight: 700; margin: 0;">Conversation avec {{ $client->cabinet->nom ?? 'votre cabinet' }}</h2>
        </div>
        
        <div style="flex: 1; overflow-y: auto; padding: 24px; background: #fff;" id="chat-messages">
            @forelse($messages as $msg)
                @if($msg->sender_type === 'portal_contact')
                    {{-- Message Envoyé par le Client --}}
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
                        <div style="max-width: 70%;">
                            <div style="font-size: 11px; color: var(--portal-text-muted); margin-bottom: 4px; text-align: right;">Vous • {{ $msg->created_at->format('d/m/Y H:i') }}</div>
                            <div style="background: var(--portal-accent); color: white; padding: 12px 16px; border-radius: 16px 16px 0 16px; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                {!! nl2br(e($msg->message)) !!}
                                @if($msg->piece_jointe)
                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.2);">
                                        <a href="{{ asset('storage/' . $msg->piece_jointe) }}" target="_blank" style="color: white; text-decoration: none; font-size: 12px; display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-paperclip"></i> Voir la pièce jointe
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Message Reçu du Cabinet --}}
                    <div style="display: flex; justify-content: flex-start; margin-bottom: 24px;">
                        <div style="max-width: 70%;">
                            <div style="font-size: 11px; color: var(--portal-text-muted); margin-bottom: 4px;">{{ $msg->sender->name ?? 'Cabinet' }} • {{ $msg->created_at->format('d/m/Y H:i') }}</div>
                            <div style="background: #f1f5f9; color: var(--portal-text-primary); padding: 12px 16px; border-radius: 16px 16px 16px 0; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                {!! nl2br(e($msg->message)) !!}
                                @if($msg->piece_jointe)
                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--portal-border);">
                                        <a href="{{ asset('storage/' . $msg->piece_jointe) }}" target="_blank" style="color: var(--portal-accent); text-decoration: none; font-size: 12px; display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-paperclip"></i> Voir la pièce jointe
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div style="text-align: center; color: var(--portal-text-muted); padding: 40px 0;">
                    <i class="fas fa-comments" style="font-size: 40px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <p>Aucun message pour le moment. Vous pouvez démarrer une conversation ici.</p>
                </div>
            @endforelse
        </div>
        
        <div style="padding: 20px; border-top: 1px solid var(--portal-border); background: #f8fafc; border-radius: 0 0 12px 12px;">
            <form action="{{ route('portal.messages.store', ['slug' => request()->route('slug')]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="position: relative;">
                    <textarea name="message" class="portal-input" rows="3" placeholder="Écrivez votre message..." style="padding-right: 120px; resize: none; border-radius: 12px;" required></textarea>
                    
                    <div style="position: absolute; right: 12px; bottom: 12px; display: flex; gap: 8px;">
                        <label for="piece_jointe" class="portal-btn portal-btn-secondary" style="padding: 8px; border-radius: 50%; cursor: pointer;" title="Joindre un fichier">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" id="piece_jointe" name="piece_jointe" style="display: none;" onchange="updateFileName(this)">
                        
                        <button type="submit" class="portal-btn portal-btn-primary" style="padding: 8px 16px; border-radius: 20px;">
                            <i class="fas fa-paper-plane"></i> Envoyer
                        </button>
                    </div>
                </div>
                <div id="file-name" style="font-size: 12px; color: var(--portal-text-secondary); margin-top: 8px; display: none;">
                    <i class="fas fa-file"></i> <span id="file-name-text"></span>
                </div>
                @error('message') <span style="color: var(--portal-danger); font-size: 12px;">{{ $message }}</span> @enderror
                @error('piece_jointe') <span style="color: var(--portal-danger); font-size: 12px; display:block;">{{ $message }}</span> @enderror
            </form>
        </div>
    </div>
    
    {{-- Informations / Actions rapides --}}
    <div>
        <div class="portal-card">
            <h5 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;"><i class="fas fa-info-circle" style="color: var(--portal-accent); margin-right: 8px;"></i> À propos</h5>
            <p style="font-size: 13px; color: var(--portal-text-secondary); line-height: 1.5;">
                Cet espace vous permet d'échanger directement avec votre expert-comptable de manière sécurisée.
            </p>
            <p style="font-size: 13px; color: var(--portal-text-secondary); line-height: 1.5; margin-bottom: 0;">
                Vous pouvez envoyer des documents (factures d'achat, relevés bancaires, etc.) en cliquant sur l'icône trombone ci-contre.
            </p>
        </div>
        
        <div class="portal-card">
            <h5 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;"><i class="fas fa-file-alt" style="color: var(--portal-accent); margin-right: 8px;"></i> Soumettre un Ticket</h5>
            <p style="font-size: 13px; color: var(--portal-text-secondary); margin-bottom: 16px;">
                Pour une demande spécifique (attestation, conseil juridique, etc.), vous pouvez créer un ticket formel.
            </p>
            <a href="{{ route('portal.tickets.create', ['slug' => request()->route('slug')]) }}" class="portal-btn portal-btn-secondary" style="width: 100%;">Créer un ticket</a>
        </div>
    </div>
</div>

<script>
    // Scroll au bas de la conversation
    window.onload = function() {
        var chat = document.getElementById("chat-messages");
        chat.scrollTop = chat.scrollHeight;
    };
    
    function updateFileName(input) {
        var fileName = input.files[0].name;
        var display = document.getElementById("file-name");
        document.getElementById("file-name-text").innerText = fileName;
        display.style.display = "block";
    }
</script>

@endsection
