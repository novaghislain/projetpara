@extends('layouts.gel-secretary')
@section('title', 'Boîte Mail (Webmail) - ' . ($activeClient ? $activeClient->nom_entreprise : 'Secrétariat'))

@section('content')
<style>
  .mail-container {
    background: white; border-radius: 12px; border: 1px solid var(--sec-border);
    display: flex; min-height: 700px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }
  .mail-sidebar {
    width: 250px; border-right: 1px solid var(--sec-border); background: #F8FAFC;
    padding: 20px 0;
  }
  .mail-sidebar-item {
    display: flex; align-items: center; gap: 12px; padding: 10px 24px;
    color: var(--sec-text-muted); font-weight: 500; cursor: pointer; text-decoration: none;
  }
  .mail-sidebar-item:hover { background: #F1F5F9; color: var(--sec-primary); }
  .mail-sidebar-item.active { background: #E2E8F0; color: var(--sec-primary); font-weight: 600; border-left: 3px solid var(--sec-primary); }
  
  .mail-list { flex: 1; display: flex; flex-direction: column; background: white; }
  .mail-list-header {
    padding: 16px 24px; border-bottom: 1px solid var(--sec-border);
    display: flex; align-items: center; justify-content: space-between;
  }
  
  .mail-item {
    padding: 16px 24px; border-bottom: 1px solid #F1F5F9;
    display: flex; flex-direction: column; cursor: pointer; transition: background 0.15s;
  }
  .mail-item:hover { background: #F8FAFC; }
  .mail-item.unread { background: #F0FDFA; border-left: 3px solid var(--sec-primary); }
  .mail-sender { font-weight: 700; color: var(--sec-text); font-size: 14px; margin-bottom: 4px; }
  .mail-subject { font-weight: 600; color: var(--sec-text); font-size: 13.5px; margin-bottom: 4px; }
  .mail-preview { color: var(--sec-text-muted); font-size: 12.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

  /* Configuration Form */
  .config-box { max-width: 600px; margin: 40px auto; padding: 32px; background: white; border-radius: 12px; border: 1px solid var(--sec-border); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
</style>

<div class="pro-header animate-fade">
  <div>
    <div class="pro-title">Boîte Mail (Webmail)</div>
    <div class="pro-subtitle">Gérez la véritable adresse e-mail de {{ $activeClient ? $activeClient->nom_entreprise : '...' }} en temps réel</div>
  </div>
</div>

@if(!$activeClient)
  <div class="alert alert-warning animate-fade" style="margin-top:20px;">Veuillez d'abord sélectionner une entreprise.</div>
@else

  @if($imapError)
    <div class="alert alert-danger animate-fade" style="margin-top:20px;">{{ $imapError }}</div>
  @endif

  @if(!$emailConfig || $imapError)
    <!-- Config Form -->
    <div class="config-box animate-fade delay-1">
      <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 24px;"><i class="fas fa-cogs" style="color:var(--sec-primary);"></i> Configuration de la Boîte Mail (IMAP/SMTP)</h3>
      <p style="font-size: 13px; color: var(--sec-text-muted); margin-bottom: 24px;">Liez l'adresse e-mail professionnelle que l'entreprise vous a fournie pour recevoir et envoyer des messages depuis cette interface.</p>
      
      <form action="{{ route('gel-secretary.mail.save-config') }}" method="POST">
        @csrf
        <input type="hidden" name="client_id" value="{{ $activeClient->id }}">
        
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label" style="font-size: 13px; font-weight: 600;">Serveur IMAP (Ex: imap.gmail.com)</label>
            <input type="text" name="imap_host" class="form-control form-control-sm" value="{{ $emailConfig->imap_host ?? '' }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label" style="font-size: 13px; font-weight: 600;">Port</label>
            <input type="number" name="imap_port" class="form-control form-control-sm" value="{{ $emailConfig->imap_port ?? '993' }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label" style="font-size: 13px; font-weight: 600;">Sécurité</label>
            <select name="imap_encryption" class="form-select form-select-sm">
              <option value="ssl" {{ ($emailConfig->imap_encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
              <option value="tls" {{ ($emailConfig->imap_encryption ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
            </select>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-6">
            <label class="form-label" style="font-size: 13px; font-weight: 600;">E-mail (Nom d'utilisateur)</label>
            <input type="email" name="imap_username" class="form-control form-control-sm" value="{{ $emailConfig->imap_username ?? '' }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" style="font-size: 13px; font-weight: 600;">Mot de passe</label>
            <input type="password" name="imap_password" class="form-control form-control-sm" placeholder="***" required>
          </div>
        </div>

        <div style="border-top: 1px solid var(--sec-border); margin: 24px 0;"></div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label" style="font-size: 13px; font-weight: 600;">Serveur SMTP (Ex: smtp.gmail.com)</label>
            <input type="text" name="smtp_host" class="form-control form-control-sm" value="{{ $emailConfig->smtp_host ?? '' }}">
          </div>
          <div class="col-md-3">
            <label class="form-label" style="font-size: 13px; font-weight: 600;">Port SMTP</label>
            <input type="number" name="smtp_port" class="form-control form-control-sm" value="{{ $emailConfig->smtp_port ?? '465' }}">
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="background: var(--sec-primary); border: none;"><i class="fas fa-save"></i> Enregistrer et Connecter</button>
      </form>
    </div>
  @else
    <!-- Webmail Interface -->
    <div class="mail-container animate-fade delay-1" style="margin-top: 24px;">
      
      <!-- Sidebar -->
      <div class="mail-sidebar">
        <div style="padding: 0 24px 20px 24px;">
          <button class="btn w-100" style="background: var(--sec-primary); color: white; font-weight: 600; border-radius: 8px;" onclick="document.getElementById('composeModal').style.display='flex'"><i class="fas fa-pen"></i> Nouveau Message</button>
        </div>
        
        @php
            function translateFolder($name) {
                $map = [
                    'INBOX' => ['Boîte de réception', 'fa-inbox'],
                    'Junk Email' => ['Courrier indésirable', 'fa-ban'],
                    'Junk' => ['Courrier indésirable', 'fa-ban'],
                    'Spam' => ['Courrier indésirable', 'fa-ban'],
                    'Drafts' => ['Brouillons', 'fa-file-alt'],
                    'Sent Items' => ['Éléments envoyés', 'fa-paper-plane'],
                    'Sent' => ['Éléments envoyés', 'fa-paper-plane'],
                    'Deleted Items' => ['Éléments supprimés', 'fa-trash'],
                    'Trash' => ['Corbeille', 'fa-trash'],
                    'Archive' => ['Archives', 'fa-archive'],
                    'Notes_0' => ['Notes', 'fa-sticky-note'],
                    'Notes' => ['Notes', 'fa-sticky-note'],
                    'Outbox' => ['Boîte d\'envoi', 'fa-share-square']
                ];
                
                foreach ($map as $key => $val) {
                    if (strcasecmp($name, $key) === 0) {
                        return $val;
                    }
                }
                return [$name, 'fa-folder'];
            }
        @endphp

        @foreach($folders as $f)
            @php
                $translated = translateFolder($f->name);
                $isActive = $currentFolder == $f->name;
            @endphp
            <a href="{{ route('gel-secretary.mail.index', ['folder' => $f->name]) }}" class="mail-sidebar-item {{ $isActive ? 'active' : '' }}">
              <i class="fas {{ $translated[1] }}"></i> {{ $translated[0] }}
            </a>
        @endforeach

        <div style="margin-top: 40px; padding: 0 24px;">
          <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #94A3B8; margin-bottom: 12px;">Connexion</div>
          <div style="font-size: 12px; color: var(--sec-text);"><i class="fas fa-circle text-success" style="font-size: 8px;"></i> Connecté en tant que<br><b>{{ $emailConfig->imap_username }}</b></div>
        </div>
      </div>

      <!-- Mail List -->
      <div class="mail-list">
        <div class="mail-list-header">
          <div style="font-weight: 700; font-size: 16px;">{{ translateFolder($currentFolder)[0] ?? $currentFolder }}</div>
          <div>
            <button class="btn btn-sm btn-light border" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i> Actualiser</button>
          </div>
        </div>
        
        <div style="flex: 1; overflow-y: auto;">
          @if(count($messages) > 0)
            @foreach($messages as $msg)
              <div class="mail-item {{ $msg->getFlags()->has('\\Seen') ? '' : 'unread' }}" onclick="openMail({{ $msg->getUid() }}, '{{ $currentFolder }}')">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                  <div class="mail-sender">{{ $msg->getFrom()[0]->personal ?? $msg->getFrom()[0]->mail }}</div>
                  <div style="font-size: 12px; color: var(--sec-text-muted);">{{ $msg->getDate()->format('d/m/Y H:i') }}</div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                  <div class="mail-subject">{{ $msg->getSubject() }}</div>
                  
                  <!-- Bouton Automatisation : Créer une tâche -->
                  <form action="{{ route('gel-secretary.tasks.store') }}" method="POST" style="display:inline;" onsubmit="return confirm('Créer une tâche pour cet email ?');" onclick="event.stopPropagation();">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $activeClient?->id }}">
                    <input type="hidden" name="titre" value="[Email] {{ Str::limit($msg->getSubject(), 50) }}">
                    <input type="hidden" name="description" value="Email de : {{ $msg->getFrom()[0]->mail ?? '' }}&#10;Reçu le : {{ $msg->getDate()->format('d/m/Y H:i') }}&#10;&#10;{{ Str::limit($msg->getTextBody(), 500) }}">
                    <input type="hidden" name="priorite" value="haute">
                    <input type="hidden" name="statut" value="a_faire">
                    <input type="hidden" name="date_echeance" value="{{ now()->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-sm" style="background:#F1F5F9; color:#475569; padding:2px 8px; font-size:11px; border: 1px solid #E2E8F0;" title="Transformer en tâche">
                      <i class="fas fa-magic" style="color: var(--sec-primary);"></i> Tâche
                    </button>
                  </form>
                </div>
                <div class="mail-preview">{{ Str::limit($msg->getTextBody(), 100) }}</div>
              </div>
            @endforeach
          @else
            <div style="padding: 64px; text-align: center; color: #94A3B8;">
              <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
              <div style="font-size: 16px; font-weight: 600;">Aucun message trouvé</div>
            </div>
          @endif
        </div>
      </div>

    </div>
  @endif

  <!-- Modal Nouveau Message -->
  <div id="composeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
      <div style="background:white; border-radius:12px; width:100%; max-width:600px; box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden;">
          <div style="background:#F8FAFC; padding:16px 24px; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
              <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fas fa-pen text-primary"></i> Nouveau Message</h3>
              <button class="btn-close" onclick="document.getElementById('composeModal').style.display='none'"></button>
          </div>
          <form action="{{ route('gel-secretary.mail.send') }}" method="POST" style="padding:24px;">
              @csrf
              <div style="margin-bottom:16px;">
                  <input type="email" name="to" id="composeTo" class="form-control" placeholder="À :" required style="border:none; border-bottom:1px solid #E2E8F0; border-radius:0; padding-left:0; box-shadow:none;">
              </div>
              <div style="margin-bottom:16px;">
                  <input type="text" name="subject" id="composeSubject" class="form-control" placeholder="Objet :" required style="border:none; border-bottom:1px solid #E2E8F0; border-radius:0; padding-left:0; box-shadow:none;">
              </div>
              <div style="margin-bottom:24px;">
                  <textarea name="message" id="composeMessage" class="form-control" rows="10" placeholder="Votre message..." required style="border:none; resize:none; padding-left:0; box-shadow:none;"></textarea>
              </div>
              <div style="display:flex; justify-content:flex-end; gap:12px;">
                  <button type="button" class="btn btn-light" onclick="document.getElementById('composeModal').style.display='none'">Annuler</button>
                  <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Envoyer</button>
              </div>
          </form>
      </div>
  </div>

  <!-- Modal Lecture Message -->
  <div id="readModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
      <div style="background:white; border-radius:12px; width:100%; max-width:800px; height:80vh; display:flex; flex-direction:column; box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden;">
          <div style="background:#F8FAFC; padding:16px 24px; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
              <h3 style="margin:0; font-size:16px; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:80%;" id="readSubject">Sujet du message</h3>
              <button class="btn-close" onclick="document.getElementById('readModal').style.display='none'"></button>
          </div>
          <div style="padding:16px 24px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; background:white;">
              <div>
                  <div style="font-weight:700; font-size:14px;" id="readFrom">Expéditeur</div>
                  <div style="font-size:12px; color:var(--sec-text-muted);" id="readDate">Date</div>
              </div>
              <div style="display:flex; gap:8px;">
                  <button class="btn btn-sm btn-light border" onclick="replyMessage()"><i class="fas fa-reply"></i> Répondre</button>
              </div>
          </div>
          <div id="readBody" style="padding:24px; flex:1; overflow-y:auto; background:white; font-size:14px; line-height:1.6;">
              <!-- Message content -->
              <div style="text-align:center; padding:40px;"><div class="spinner-border text-primary" role="status"></div></div>
          </div>
      </div>
  </div>

<script>
function openMail(uid, folder) {
    document.getElementById('readSubject').innerHTML = 'Chargement...';
    document.getElementById('readFrom').innerHTML = '';
    document.getElementById('readDate').innerHTML = '';
    document.getElementById('readBody').innerHTML = '<div style="text-align:center; padding:40px;"><div class="spinner-border text-primary" role="status"></div></div>';
    
    document.getElementById('readModal').style.display = 'flex';

    fetch(`{{ url('gel-secretary/mail') }}/${uid}?folder=${encodeURIComponent(folder)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('readBody').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            document.getElementById('readSubject').innerText = data.subject;
            document.getElementById('readFrom').innerText = data.from;
            document.getElementById('readDate').innerText = data.date;
            
            // Clean/sanitization might be needed in prod, but for now we inject HTML
            document.getElementById('readBody').innerHTML = data.body;
            
            // Store for reply
            window.currentMailData = data;
        })
        .catch(err => {
            document.getElementById('readBody').innerHTML = `<div class="alert alert-danger">Erreur de connexion.</div>`;
        });
}

function replyMessage() {
    if (!window.currentMailData) return;
    document.getElementById('readModal').style.display = 'none';
    
    document.getElementById('composeTo').value = window.currentMailData.from;
    document.getElementById('composeSubject').value = 'Re: ' + window.currentMailData.subject.replace(/^Re:\s*/i, '');
    document.getElementById('composeMessage').value = `\n\n\n--- En réponse à ---\n${window.currentMailData.from} a écrit le ${window.currentMailData.date}:\n\n` + window.currentMailData.body.replace(/<[^>]*>?/gm, '');
    
    document.getElementById('composeModal').style.display = 'flex';
}
</script>

@endif

@endsection
