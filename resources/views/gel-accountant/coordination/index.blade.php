@extends('layouts.gel-accountant')

@section('title', 'Coordination Secrétariat - Comptabilité')

@push('styles')
<style>
/* ==========================================================================
   COORDINATION ACCOUNTANT - BENTO GRID DESIGN
   ========================================================================== */
.coordination-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 24px;
}

.bento-card {
    background: white;
    border: 1px solid var(--gel-border);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    height: 100%;
}

.bento-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid #E2E8F0;
    padding-bottom: 12px;
}

.bento-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--gel-text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}
.bento-title i { color: var(--gel-primary); font-size: 18px; }

/* Timeline Activity (S4.3) */
.timeline { position: relative; margin-top: 10px; }
.timeline::before {
    content: ''; position: absolute; left: 16px; top: 0;
    bottom: 0; width: 2px; background: #E2E8F0;
}
.timeline-item { position: relative; padding-left: 48px; margin-bottom: 24px; }
.timeline-icon {
    position: absolute; left: 0; top: 0;
    width: 34px; height: 34px; border-radius: 50%;
    background: white; border: 2px solid var(--gel-primary);
    display: flex; align-items: center; justify-content: center;
    color: var(--gel-primary); font-size: 14px; z-index: 1;
}
.timeline-content {
    background: #F8FAFC; border-radius: 8px; padding: 12px 16px;
    border: 1px solid #E2E8F0; position: relative;
}
.timeline-content::before {
    content: ''; position: absolute; left: -6px; top: 12px;
    width: 10px; height: 10px; background: #F8FAFC;
    border-left: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0;
    transform: rotate(45deg);
}
.timeline-time { font-size: 11px; color: #64748B; font-weight: 600; margin-bottom: 4px; display:block; }
.timeline-title { font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 4px; }
.timeline-desc { font-size: 13px; color: #475569; margin: 0; }

/* Documents Area */
.doc-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 16px; border: 1px solid #E2E8F0; border-radius: 8px;
    margin-bottom: 12px; background: #F8FAFC; transition: all 0.2s;
}
.doc-item:hover { background: white; border-color: var(--gel-primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.doc-info { display: flex; align-items: center; gap: 12px; }
.doc-icon { font-size: 24px; color: #EF4444; }
.doc-name { font-size: 14px; font-weight: 600; color: #1E293B; }
.doc-meta { font-size: 12px; color: #64748B; }

/* Chat Area */
.chat-container { display: flex; flex-direction: column; height: 400px; }
.chat-messages { flex: 1; overflow-y: auto; padding: 16px; background: #F8FAFC; border-radius: 8px; margin-bottom: 16px; border: 1px solid #E2E8F0; }
.chat-message { margin-bottom: 16px; display: flex; flex-direction: column; max-width: 85%; }
.chat-message.sent { align-self: flex-end; align-items: flex-end; }
.chat-message.received { align-self: flex-start; align-items: flex-start; }
.chat-bubble { padding: 12px 16px; border-radius: 12px; font-size: 13px; position: relative; }
.chat-message.sent .chat-bubble { background: var(--gel-primary); color: white; border-bottom-right-radius: 4px; }
.chat-message.received .chat-bubble { background: white; border: 1px solid #E2E8F0; color: #1E293B; border-bottom-left-radius: 4px; }
.chat-meta { font-size: 11px; color: #64748B; margin-top: 4px; }
.chat-input-area { display: flex; gap: 12px; align-items: flex-end; }
.chat-input { flex: 1; resize: none; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px; font-size: 13px; font-family: inherit; outline: none; }
.chat-input:focus { border-color: var(--gel-primary); }
.btn-send { background: var(--gel-primary); color: white; border: none; width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-send:hover { background: var(--gel-primary-hover); transform: translateY(-2px); }

/* Buttons & Inputs for Modals */
.form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #475569; }
.form-control { width: 100%; padding: 10px 12px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 14px; margin-bottom: 16px; }
</style>
@endpush

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Coordination Secrétariat</h1>
        <div class="gel-page-subtitle">Dossier : <strong>{{ $client->nom_entreprise }}</strong> | Secrétaire rattaché(e) : {{ $secretaire ? $secretaire->name : 'Non assigné' }}</div>
    </div>
    <div style="display:flex; gap:12px;">
        <button class="gel-btn gel-btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAlerte">
            <i class="fas fa-exclamation-triangle text-danger"></i> Envoyer une Alerte
        </button>
        <button class="gel-btn gel-btn-primary" data-bs-toggle="modal" data-bs-target="#modalDemande">
            <i class="fas fa-file-export"></i> Demander un Document
        </button>
    </div>
</div>

<div class="coordination-grid">
    
    <!-- Colonne de gauche : Documents transmis & Activité -->
    <div style="grid-column: span 7; display:flex; flex-direction:column; gap:24px;">
        
        <!-- Documents Transmis à réceptionner -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="bento-title"><i class="fas fa-inbox"></i> Documents transmis par le Secrétariat</div>
                <span class="badge bg-danger rounded-pill">{{ count($documentsTransmis) }}</span>
            </div>
            
            <div class="documents-list">
                @forelse($documentsTransmis as $doc)
                <div class="doc-item">
                    <div class="doc-info">
                        <i class="fas fa-file-pdf doc-icon"></i>
                        <div>
                            <div class="doc-name">{{ $doc->name }}</div>
                            <div class="doc-meta">Transmis le {{ \Carbon\Carbon::parse($doc->transmitted_at)->format('d/m/Y à H:i') }} • Réf: {{ $doc->reference }}</div>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('gel-accountant.secretariat.documents.download', $doc->id) }}" class="gel-btn gel-btn-secondary gel-btn-sm" title="Télécharger">
                            <i class="fas fa-download"></i>
                        </a>
                        <form action="{{ route('gel-accountant.coordination.accuser-reception') }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="document_id" value="{{ $doc->id }}">
                            <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm" title="Accuser réception et prendre en charge">
                                <i class="fas fa-check"></i> Accuser réception
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="gel-empty" style="padding: 20px;">
                    <i class="fas fa-check-circle" style="font-size:32px; color:#10B981; margin-bottom:12px;"></i>
                    <p>Aucun document en attente de réception pour ce dossier.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Fil d'activité Commun -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="bento-title"><i class="fas fa-stream"></i> Fil d'Activité Commun</div>
            </div>
            <div class="timeline" style="max-height: 400px; overflow-y:auto; padding-right:10px;">
                @forelse($activity as $event)
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="{{ $event->icon ?? 'fas fa-info' }}"></i>
                    </div>
                    <div class="timeline-content">
                        <span class="timeline-time">{{ $event->created_at->format('d/m/Y H:i') }} • {{ $event->user?->name ?? 'Système' }}</span>
                        <div class="timeline-title">{{ $event->titre }}</div>
                        @if($event->description)
                            <p class="timeline-desc">{{ $event->description }}</p>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted">Aucune activité récente.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Colonne de droite : Messagerie Directe & Demandes envoyées -->
    <div style="grid-column: span 5; display:flex; flex-direction:column; gap:24px;">
        
        <!-- Messagerie Directe -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="bento-title"><i class="fas fa-comments"></i> Chat Secrétariat</div>
            </div>
            
            <div class="chat-container">
                <div class="chat-messages" id="chatBox">
                    @foreach($messages as $msg)
                        <div class="chat-message {{ $msg->sender_id === auth()->id() ? 'sent' : 'received' }}">
                            <div class="chat-bubble">
                                {{ $msg->message }}
                                @if($msg->piece_jointe)
                                    <div style="margin-top:8px;">
                                        <a href="{{ Storage::url($msg->piece_jointe) }}" target="_blank" style="color:inherit; text-decoration:underline; font-size:12px;">
                                            <i class="fas fa-paperclip"></i> Pièce jointe
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="chat-meta">{{ $msg->created_at->format('H:i') }} • {{ $msg->sender->name }}</div>
                        </div>
                    @endforeach
                </div>
                
                <form action="{{ route('gel-accountant.coordination.send-message') }}" method="POST" enctype="multipart/form-data" class="chat-input-area">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <label class="gel-btn gel-btn-secondary" style="margin:0; padding:10px 14px; cursor:pointer;" title="Joindre un fichier">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="attachment" style="display:none;">
                    </label>
                    <textarea name="message" class="chat-input" rows="1" placeholder="Écrivez un message..."></textarea>
                    <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <!-- Demandes / Tâches en cours -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="bento-title"><i class="fas fa-tasks"></i> Demandes en cours</div>
            </div>
            <div>
                @forelse($demandesEnvoyees as $demande)
                    <div style="padding:12px; border:1px solid #E2E8F0; border-radius:8px; margin-bottom:12px; border-left:4px solid {{ $demande->statut == 'termine' ? '#10B981' : '#F59E0B' }};">
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <strong style="font-size:13px;">{{ $demande->titre }}</strong>
                            <span class="badge bg-{{ $demande->statut == 'termine' ? 'success' : 'warning' }}">{{ strtoupper(str_replace('_', ' ', $demande->statut)) }}</span>
                        </div>
                        <div style="font-size:12px; color:#64748B;">Échéance : {{ \Carbon\Carbon::parse($demande->date_echeance)->format('d/m/Y') }}</div>
                    </div>
                @empty
                    <p class="text-muted" style="font-size:13px;">Aucune demande en cours.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- Modal : Demander Document -->
<div class="modal fade" id="modalDemande" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header" style="border-bottom:1px solid #E2E8F0; padding:20px;">
        <h5 class="modal-title" style="font-weight:700;"><i class="fas fa-file-export text-primary me-2"></i> Demander un Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('gel-accountant.coordination.request-document') }}" method="POST">
          @csrf
          <input type="hidden" name="client_id" value="{{ $client->id }}">
          <div class="modal-body" style="padding:20px;">
              <label class="form-label">Titre du document demandé <span class="text-danger">*</span></label>
              <input type="text" name="intitule" class="form-control" required placeholder="Ex: Relevé bancaire BICI CI de Mars">
              
              <label class="form-label">Description / Instructions <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="3" required placeholder="Précisez ce dont vous avez besoin..."></textarea>
              
              <div class="row">
                  <div class="col-md-6">
                      <label class="form-label">Date limite</label>
                      <input type="date" name="date_echeance" class="form-control">
                  </div>
                  <div class="col-md-6">
                      <label class="form-label">Priorité</label>
                      <select name="priorite" class="form-control">
                          <option value="basse">Basse</option>
                          <option value="moyenne" selected>Moyenne</option>
                          <option value="haute">Haute</option>
                          <option value="critique">Critique</option>
                      </select>
                  </div>
              </div>
          </div>
          <div class="modal-footer" style="border-top:none; padding:20px;">
            <button type="button" class="gel-btn gel-btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="gel-btn gel-btn-primary">Envoyer la demande</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Envoyer Alerte -->
<div class="modal fade" id="modalAlerte" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header" style="border-bottom:1px solid #E2E8F0; padding:20px;">
        <h5 class="modal-title" style="font-weight:700;"><i class="fas fa-exclamation-triangle text-danger me-2"></i> Alerte Réglementaire</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('gel-accountant.coordination.send-alert') }}" method="POST">
          @csrf
          <input type="hidden" name="client_id" value="{{ $client->id }}">
          <div class="modal-body" style="padding:20px;">
              <p class="text-muted" style="font-size:13px; margin-bottom:16px;">Utilisez cette alerte pour informer le secrétariat d'une échéance réglementaire critique (TVA, Impôts) qui nécessite leur action urgente.</p>
              
              <label class="form-label">Titre de l'alerte <span class="text-danger">*</span></label>
              <input type="text" name="titre" class="form-control" required placeholder="Ex: Retard Déclaration TVA">
              
              <label class="form-label">Message <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="3" required placeholder="Expliquez l'urgence..."></textarea>
              
              <label class="form-label">Date butoir <span class="text-danger">*</span></label>
              <input type="date" name="date_echeance" class="form-control" required>
          </div>
          <div class="modal-footer" style="border-top:none; padding:20px;">
            <button type="button" class="gel-btn gel-btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="gel-btn gel-btn-primary" style="background:#EF4444;">Envoyer l'alerte</button>
          </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
    // Scroll chat to bottom
    const chatBox = document.getElementById('chatBox');
    if(chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endpush

@endsection