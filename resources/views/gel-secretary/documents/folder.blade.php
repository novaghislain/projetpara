@extends('layouts.gel-secretary')

@section('title', $folder->name . " — Documents")

@section('content')
<style>
  /* Premium Table */
  .premium-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid var(--sec-border);
    overflow: hidden;
  }
  .premium-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 20px;
    border-bottom: 2px solid #e2e8f0;
  }
  .premium-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
  }
  .premium-table tr:last-child td { border-bottom: none; }
  .premium-table tr:hover td { background: #f8fafc; }
  
  /* Drag & Drop Zone */
  .dropzone-area {
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    background: #f8fafc;
    padding: 40px 20px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
  }
  .dropzone-area:hover, .dropzone-area.dragover {
    border-color: var(--sec-primary);
    background: var(--sec-primary-light);
  }
  .dropzone-area.dragover {
    transform: scale(1.02);
    box-shadow: 0 10px 25px rgba(13, 148, 136, 0.15);
  }
  .dropzone-icon {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 16px;
    transition: color 0.3s ease;
  }
  .dropzone-area:hover .dropzone-icon, .dropzone-area.dragover .dropzone-icon {
    color: var(--sec-primary);
  }
  
  /* Buttons */
  .btn-action-premium {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    background: #f1f5f9; color: #475569;
    border: none; cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
  }
  .btn-action-premium:hover {
    background: var(--sec-primary); color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(13, 148, 136, 0.2);
  }
  .btn-delete-premium:hover {
    background: #ef4444; color: white;
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
  }
</style>

<div class="sec-page-header" style="margin-bottom:24px;">
  <div>
    <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:var(--sec-text-muted); margin-bottom:6px;">
        <a href="{{ route('gel-secretary.documents.index') }}" style="color:var(--sec-primary); font-weight:600; text-decoration:none;"><i class="fas fa-layer-group"></i> Espace Documentaire</a>
        <i class="fas fa-chevron-right" style="font-size:10px;"></i>
        <span style="background:#e2e8f0; padding:2px 8px; border-radius:12px; font-weight:600; color:#475569;">{{ $folder->name }}</span>
    </div>
    <h1 class="sec-page-title" style="margin:0; font-size:24px;">
      <i class="fas fa-folder-open" style="color:#FBBF24; margin-right:8px; filter: drop-shadow(0 2px 4px rgba(245,158,11,0.3));"></i>{{ $folder->name }}
    </h1>
  </div>
  <div>
      <button class="sec-btn sec-btn-primary" onclick="openNewFolderModal()">
          <i class="fas fa-folder-plus"></i> Nouveau Sous-Dossier
      </button>
  </div>
</div>

@if($subfolders && count($subfolders) > 0)
<div style="margin-bottom: 24px;">
    <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin-bottom:12px;">
      <i class="fas fa-sitemap" style="color:var(--sec-text-muted); margin-right:8px;"></i> Sous-dossiers
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
        @foreach($subfolders as $sub)
        <a href="{{ route('gel-secretary.documents.folder', $sub->id) }}" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-folder" style="color: #60A5FA; font-size: 20px;"></i>
            </div>
            <div>
                <div style="font-weight: 700; color: #1e293b; font-size: 13px;">{{ $sub->name }}</div>
                <div style="font-size: 11px; color: #94a3b8;">{{ $sub->documents_count }} fichier(s)</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- Smart Drag & Drop Zone -->
<form action="{{ route('gel-secretary.documents.upload') }}" method="POST" enctype="multipart/form-data" id="dropzoneForm">
  @csrf
  <input type="hidden" name="folder_id" value="{{ $folder->id }}">
  <input type="file" name="file" id="fileInput" style="display:none;" onchange="submitForm()">
  
  <div class="dropzone-area" id="dropzoneArea" onclick="document.getElementById('fileInput').click()">
    <i class="fas fa-cloud-upload-alt dropzone-icon"></i>
    <h3 style="font-size:18px; font-weight:700; color:var(--sec-text); margin-bottom:8px;">Glissez vos fichiers ici ou cliquez pour parcourir</h3>
    <p style="font-size:13px; color:var(--sec-text-muted); max-width:400px; margin:0 auto;">
      Le téléversement démarrera instantanément. Formats acceptés : PDF, Excel, Word, Images (Max 10 Mo).
    </p>
  </div>
</form>

<div class="premium-card">
  <div style="padding:20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">
      <i class="fas fa-file-alt" style="color:var(--sec-text-muted); margin-right:8px;"></i> Fichiers archivés ({{ $documents->count() }})
    </h3>
  </div>

  <div style="overflow-x:auto;">
    <table class="sec-table premium-table" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="width:35%;">Nom du fichier</th>
                <th>Type / Version</th>
                <th>Taille</th>
                <th>Statut</th>
                <th>Date d'import</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <form method="POST" action="{{ route('gel-secretary.documents.favorite', $doc->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" style="background:none; border:none; padding:0; cursor:pointer; font-size:18px; color:{{ $doc->is_favorite ? '#FBBF24' : '#cbd5e1' }}; transition:color 0.2s;" title="Favori">
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                            <div style="width:40px; height:40px; border-radius:10px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:20px; color:var(--sec-primary);">
                                <i class="far {{ in_array($doc->file_type, ['png','jpg','jpeg']) ? 'fa-file-image' : ($doc->file_type === 'pdf' ? 'fa-file-pdf' : 'fa-file-alt') }}"></i>
                            </div>
                            <div>
                                <div style="font-weight:700; color:#1e293b; font-size:14px; display:flex; align-items:center; gap:6px;">
                                    {{ $doc->name }}
                                    @if($doc->privacy_level === 'confidentiel')
                                        <span style="font-size:9px; background:#FEE2E2; color:#EF4444; padding:2px 6px; border-radius:4px; text-transform:uppercase;">Confidentiel</span>
                                    @elseif($doc->privacy_level === 'interne')
                                        <span style="font-size:9px; background:#FEF3C7; color:#D97706; padding:2px 6px; border-radius:4px; text-transform:uppercase;">Interne</span>
                                    @endif
                                </div>
                                @if($doc->tags && count($doc->tags) > 0)
                                    <div style="margin-top:4px; display:flex; gap:4px; flex-wrap:wrap;">
                                        @foreach($doc->tags as $tag)
                                            <span style="font-size:10px; background:#F3F4F6; color:#4B5563; padding:2px 6px; border-radius:10px;">#{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($doc->description)
                                    <div style="font-size:11px; color:#64748b; margin-top:2px;">{{ Str::limit($doc->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="background:#e2e8f0; color:#475569; font-size:10px; font-weight:700; padding:4px 8px; border-radius:6px; text-transform:uppercase;">
                            {{ $doc->file_type ?? 'N/A' }}
                        </span>
                        <div style="font-size:11px; color:#64748b; margin-top:4px; font-weight:600;">v{{ $doc->version }}</div>
                    </td>
                    <td style="font-family:monospace; color:#475569; font-size:13px;">
                        {{ $doc->formatted_size }}
                    </td>
                    <td style="color:#64748b; font-size:13px;">
                        @if($doc->share_token)
                            <span style="font-size:10px; background:#DCFCE7; color:#16A34A; padding:2px 6px; border-radius:4px;"><i class="fas fa-link"></i> Partagé</span>
                        @else
                            <span style="font-size:10px; background:#F1F5F9; color:#64748b; padding:2px 6px; border-radius:4px;">Privé</span>
                        @endif
                    </td>
                    <td style="color:#64748b; font-size:13px;">
                        {{ \Carbon\Carbon::parse($doc->created_at)->format('d/m/Y H:i') }}
                    </td>
                    <td style="text-align:right; position:relative;">
                        <div style="display:inline-flex; gap:6px; align-items:center;">
                            @if(strtolower($doc->file_type) === 'pdf')
                                <button type="button" onclick="previewPDF('{{ route('gel-secretary.documents.view', $doc->id) }}', '{{ addslashes($doc->name) }}')" class="btn-action-premium" title="Aperçu rapide">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @else
                                <a href="{{ route('gel-secretary.documents.view', $doc->id) }}" target="_blank" class="btn-action-premium" title="Lire le document">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @endif
                            <button type="button" onclick="showHistoryModal({{ $doc->id }})" class="btn-action-premium" title="Historique">
                                <i class="fas fa-history"></i>
                            </button>
                            <a href="{{ route('gel-secretary.documents.download', $doc->id) }}" class="btn-action-premium" title="Télécharger">
                                <i class="fas fa-download"></i>
                            </a>
                            <div class="dropdown" style="display:inline-block;">
                                <button type="button" class="btn-action-premium" data-bs-toggle="dropdown" aria-expanded="false" title="Plus d'actions">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border:none; border-radius:12px; font-size:13px;">
                                    <li>
                                        <a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); showMetadataModal({{ $doc->id }}, '{{ $doc->privacy_level }}', '{{ implode(',', $doc->tags ?? []) }}')">
                                            <i class="fas fa-tags text-secondary me-2"></i> Propriétés & Étiquettes
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('gel-secretary.documents.share', $doc->id) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2">
                                                <i class="fas fa-share-alt text-primary me-2"></i> Générer un lien
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); showVersionModal({{ $doc->id }})">
                                            <i class="fas fa-upload text-info me-2"></i> Téléverser une V{{ $doc->version + 1 }}
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item py-2 text-danger" href="#" onclick="event.preventDefault(); confirmDelete('{{ route('gel-secretary.documents.destroy', $doc->id) }}', '{{ addslashes($doc->name) }}')">
                                            <i class="fas fa-trash-alt me-2"></i> Supprimer
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        {{-- Modale Historique --}}
                        <div id="historyModal-{{ $doc->id }}" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.4); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; text-align:left;">
                            <div style="background:#fff; border-radius:16px; width:500px; max-width:90%; border:1px solid var(--sec-border); box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden; max-height:80vh; display:flex; flex-direction:column;">
                                <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
                                    <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">
                                        <i class="fas fa-history" style="color:var(--sec-primary); margin-right:6px;"></i> Historique du document
                                    </h3>
                                    <button type="button" onclick="document.getElementById('historyModal-{{ $doc->id }}').style.display='none'" style="background:none; border:none; font-size:20px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
                                </div>
                                <div style="padding:24px; overflow-y:auto; flex:1;">
                                    @if($doc->historyLogs && $doc->historyLogs->count() > 0)
                                        <div style="position:relative; padding-left:24px; border-left:2px solid #e2e8f0; margin-left:12px;">
                                            @foreach($doc->historyLogs as $log)
                                                <div style="position:relative; margin-bottom:24px;">
                                                    <div style="position:absolute; left:-33px; top:0; width:16px; height:16px; border-radius:50%; background:#fff; border:2px solid var(--sec-primary); z-index:1;"></div>
                                                    <div style="font-size:12px; color:var(--sec-text-muted); margin-bottom:6px; font-weight:600;">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</div>
                                                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                                                        <strong style="color:var(--sec-text); font-size:14px;">{{ $log->actor_name ?? $log->actor_email ?? 'Système' }}</strong>
                                                        <span style="font-size:11px; background:#e2e8f0; color:#475569; padding:2px 8px; border-radius:10px; margin-left:8px;">{{ $log->actor_role ?? 'N/A' }}</span>
                                                        <div style="margin-top:10px; font-size:13px; font-weight:600;">
                                                            @if($log->action === 'document.upload') <span style="color:#10b981;"><i class="fas fa-upload" style="margin-right:6px;"></i>Téléversement</span>
                                                            @elseif($log->action === 'document.read') <span style="color:#3b82f6;"><i class="fas fa-eye" style="margin-right:6px;"></i>Consultation</span>
                                                            @elseif($log->action === 'document.download') <span style="color:#6366f1;"><i class="fas fa-download" style="margin-right:6px;"></i>Téléchargement</span>
                                                            @elseif($log->action === 'document.delete') <span style="color:#ef4444;"><i class="fas fa-trash-alt" style="margin-right:6px;"></i>Suppression</span>
                                                            @else {{ $log->action }} @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="text-align:center; color:var(--sec-text-muted); padding:30px 0;">
                                            <i class="fas fa-ghost" style="font-size:40px; margin-bottom:16px; color:#cbd5e1;"></i><br>
                                            <span style="font-weight:600;">Aucun historique</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:60px; text-align:center; color:#94a3b8;">
                        <i class="far fa-folder-open" style="font-size:48px; display:block; margin-bottom:16px; color:#e2e8f0;"></i>
                        <p style="font-size:15px; font-weight:600; color:var(--sec-text); margin-bottom:8px;">Ce dossier est vide</p>
                        <p style="font-size:13px;">Glissez des fichiers dans la zone au-dessus pour les ajouter.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Modale Confirmation Suppression --}}
<div id="deleteConfirmModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:400px; max-width:90%; border:1px solid var(--sec-border); box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden; text-align:center; animation:fadeInDown 0.3s ease;">
        <div style="padding:30px 20px 24px;">
            <div style="width:70px; height:70px; border-radius:50%; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:32px; margin:0 auto 20px; box-shadow:0 4px 10px rgba(239, 68, 68, 0.15);">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3 style="font-size:20px; font-weight:700; color:var(--sec-text); margin:0 0 12px;">Supprimer le document</h3>
            <p style="font-size:14px; color:var(--sec-text-muted); margin:0; line-height:1.5;">
                Vous êtes sur le point de supprimer :<br>
                <strong id="deleteDocName" style="color:#1e293b; display:inline-block; margin-top:8px;"></strong><br>
                <span style="font-size:12px; color:#ef4444; font-weight:600; display:block; margin-top:12px;">Action irréversible.</span>
            </p>
        </div>
        <div style="padding:20px; background:#f8fafc; border-top:1px solid var(--sec-border); display:flex; justify-content:center; gap:12px;">
            <button type="button" class="sec-btn sec-btn-secondary" style="border-radius:8px; padding:10px 20px;" onclick="document.getElementById('deleteConfirmModal').style.display='none'">Annuler</button>
            <form id="deleteForm" action="" method="POST" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="sec-btn" style="background:#ef4444; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; box-shadow:0 4px 10px rgba(239, 68, 68, 0.2);">Oui, supprimer</button>
            </form>
        </div>
    </div>
</div>

{{-- Modale Nouveau Dossier --}}
<div id="newFolderModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:400px; max-width:90%; border:1px solid var(--sec-border); box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden; animation:fadeInDown 0.3s ease;">
        <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">
                <i class="fas fa-folder-plus" style="color:var(--sec-primary); margin-right:6px;"></i> Nouveau sous-dossier
            </h3>
            <button type="button" onclick="document.getElementById('newFolderModal').style.display='none'" style="background:none; border:none; font-size:20px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('gel-secretary.documents.create-folder') }}" method="POST" style="margin:0;">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $folder->id }}">
            <div style="padding:20px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:8px;">Nom du dossier</label>
                <input type="text" name="name" required placeholder="Ex: Contrats 2026" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
            </div>
            <div style="padding:16px 20px; background:#f8fafc; border-top:1px solid var(--sec-border); display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="sec-btn sec-btn-secondary" style="border-radius:8px;" onclick="document.getElementById('newFolderModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary" style="border-radius:8px;">Créer le dossier</button>
            </div>
        </form>
    </div>
</div>

<script>
// Drag and Drop Script
const dropzone = document.getElementById('dropzoneArea');
const fileInput = document.getElementById('fileInput');
const form = document.getElementById('dropzoneForm');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
  dropzone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults (e) {
  e.preventDefault();
  e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
  dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
});

['dragleave', 'drop'].forEach(eventName => {
  dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
});

dropzone.addEventListener('drop', (e) => {
  let dt = e.dataTransfer;
  let files = dt.files;
  if(files.length > 0) {
      fileInput.files = files;
      submitForm();
  }
});

function submitForm() {
    if(fileInput.files.length > 0) {
        dropzone.innerHTML = '<div style="padding:20px;"><i class="fas fa-spinner fa-spin" style="font-size:32px; color:var(--sec-primary); margin-bottom:12px;"></i><h3 style="font-size:16px; margin:0;">Envoi en cours...</h3></div>';
        form.submit();
    }
}

// Modals
function showHistoryModal(id) {
    document.getElementById('historyModal-' + id).style.display = 'flex';
}

function confirmDelete(url, docName) {
    document.getElementById('deleteDocName').innerText = docName;
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteConfirmModal').style.display = 'flex';
}

function openNewFolderModal() {
    document.getElementById('newFolderModal').style.display = 'flex';
}
</script>

{{-- ─── MODALE PRÉVISUALISATION PDF ────────────────────────────────── --}}
<div id="pdfPreviewModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.7); backdrop-filter:blur(6px); z-index:99999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:80vw; max-width:900px; height:85vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,0.2); overflow:hidden; animation:fadeInDown 0.3s ease;">
        <div style="padding:14px 20px; background:#1e293b; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-file-pdf" style="color:#ef4444; font-size:18px;"></i>
                <span id="pdfPreviewTitle" style="color:#fff; font-weight:600; font-size:14px;"></span>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <a id="pdfPreviewDownload" href="#" target="_blank" style="color:#94a3b8; font-size:13px; text-decoration:none; padding:6px 12px; border:1px solid #334155; border-radius:6px; transition:all 0.2s;">
                    <i class="fas fa-external-link-alt me-1"></i> Ouvrir
                </a>
                <button type="button" onclick="closePdfModal()" style="background:none; border:none; color:#94a3b8; font-size:24px; cursor:pointer; line-height:1; padding:0;">&times;</button>
            </div>
        </div>
        <iframe id="pdfPreviewFrame" src="" style="flex:1; border:none; width:100%;"></iframe>
    </div>
</div>

{{-- ─── MODALE MÉTADONNÉES (Confidentialité + Étiquettes) ──────────── --}}
<div id="metadataModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:450px; max-width:90%; border:1px solid var(--sec-border); box-shadow:0 10px 30px rgba(0,0,0,0.12); overflow:hidden; animation:fadeInDown 0.3s ease;">
        <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">
                <i class="fas fa-sliders-h" style="color:var(--sec-primary); margin-right:6px;"></i> Propriétés du document
            </h3>
            <button type="button" onclick="document.getElementById('metadataModal').style.display='none'" style="background:none; border:none; font-size:20px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form id="metadataForm" method="POST" action="">
            @csrf
            <div style="padding:24px; display:flex; flex-direction:column; gap:20px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                        <i class="fas fa-lock me-1"></i> Niveau de confidentialité
                    </label>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <label id="privacy-standard" style="border:2px solid #e2e8f0; border-radius:10px; padding:12px 8px; text-align:center; cursor:pointer; transition:all 0.2s;">
                            <input type="radio" name="privacy_level" value="standard" style="display:none;">
                            <i class="fas fa-globe" style="font-size:18px; color:#64748b; display:block; margin-bottom:4px;"></i>
                            <span style="font-size:11px; font-weight:600; color:#475569;">Standard</span>
                        </label>
                        <label id="privacy-interne" style="border:2px solid #e2e8f0; border-radius:10px; padding:12px 8px; text-align:center; cursor:pointer; transition:all 0.2s;">
                            <input type="radio" name="privacy_level" value="interne" style="display:none;">
                            <i class="fas fa-building" style="font-size:18px; color:#D97706; display:block; margin-bottom:4px;"></i>
                            <span style="font-size:11px; font-weight:600; color:#D97706;">Interne</span>
                        </label>
                        <label id="privacy-confidentiel" style="border:2px solid #e2e8f0; border-radius:10px; padding:12px 8px; text-align:center; cursor:pointer; transition:all 0.2s;">
                            <input type="radio" name="privacy_level" value="confidentiel" style="display:none;">
                            <i class="fas fa-user-shield" style="font-size:18px; color:#EF4444; display:block; margin-bottom:4px;"></i>
                            <span style="font-size:11px; font-weight:600; color:#EF4444;">Confidentiel</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
                        <i class="fas fa-tags me-1"></i> Étiquettes
                    </label>
                    <input type="text" name="tags" id="metadataTags" placeholder="Ex: Urgent, À signer, 2026" style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; transition:border-color 0.2s; box-sizing:border-box;">
                    <p style="font-size:11px; color:#94a3b8; margin-top:6px; margin-bottom:0;">Séparez les étiquettes par des virgules.</p>
                </div>
            </div>
            <div style="padding:16px 20px; background:#f8fafc; border-top:1px solid var(--sec-border); display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="sec-btn sec-btn-secondary" style="border-radius:8px;" onclick="document.getElementById('metadataModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary" style="border-radius:8px;"><i class="fas fa-save me-1"></i> Sauvegarder</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── MODALE NOUVELLE VERSION ─────────────────────────────────────── --}}
<div id="versionModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:440px; max-width:90%; border:1px solid var(--sec-border); box-shadow:0 10px 30px rgba(0,0,0,0.12); overflow:hidden; animation:fadeInDown 0.3s ease;">
        <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">
                <i class="fas fa-code-branch" style="color:#0EA5E9; margin-right:6px;"></i> Téléverser une nouvelle version
            </h3>
            <button type="button" onclick="document.getElementById('versionModal').style.display='none'" style="background:none; border:none; font-size:20px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form id="versionForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div style="padding:24px;">
                <div style="border:2px dashed #cbd5e1; border-radius:12px; padding:32px 20px; text-align:center; cursor:pointer; transition:border-color 0.2s;" onclick="document.getElementById('versionFileInput').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size:32px; color:#94a3b8; margin-bottom:12px; display:block;"></i>
                    <p style="font-size:13px; font-weight:600; color:var(--sec-text); margin-bottom:4px;">Cliquez pour choisir le fichier</p>
                    <p id="versionFileName" style="font-size:12px; color:#94a3b8; margin:0;">Formats : PDF, Excel, Word (Max 10 Mo)</p>
                </div>
                <input type="file" name="file" id="versionFileInput" style="display:none;" onchange="document.getElementById('versionFileName').textContent = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';" required>
            </div>
            <div style="padding:16px 20px; background:#f8fafc; border-top:1px solid var(--sec-border); display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="sec-btn sec-btn-secondary" style="border-radius:8px;" onclick="document.getElementById('versionModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn" style="background:#0EA5E9; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-upload me-1"></i> Enregistrer la version
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ─── PDF Preview ──────────────────────────────────────────────────────
function previewPDF(url, name) {
    document.getElementById('pdfPreviewFrame').src = url;
    document.getElementById('pdfPreviewTitle').textContent = name;
    document.getElementById('pdfPreviewDownload').href = url;
    document.getElementById('pdfPreviewModal').style.display = 'flex';
}
function closePdfModal() {
    document.getElementById('pdfPreviewModal').style.display = 'none';
    document.getElementById('pdfPreviewFrame').src = '';
}

// ─── Metadata Modal ───────────────────────────────────────────────────
function showMetadataModal(docId, privacyLevel, tags) {
    const form = document.getElementById('metadataForm');
    form.action = '{{ url("/gel-secretary/documents") }}/' + docId + '/metadata';

    // Mise à jour tags
    document.getElementById('metadataTags').value = tags;

    // Mise à jour confidentialité
    const levels = ['standard', 'interne', 'confidentiel'];
    const colors = { standard: '#0d9488', interne: '#D97706', confidentiel: '#EF4444' };
    levels.forEach(level => {
        const lbl = document.getElementById('privacy-' + level);
        const radio = lbl.querySelector('input[type="radio"]');
        radio.checked = (level === privacyLevel);
        lbl.style.borderColor = (level === privacyLevel) ? (colors[level] || '#0d9488') : '#e2e8f0';
        lbl.style.background = (level === privacyLevel) ? (level === 'standard' ? '#F0FDFA' : (level === 'interne' ? '#FEF3C7' : '#FEF2F2')) : '#fff';
    });

    // Écoute les clics pour le style visuel
    levels.forEach(level => {
        const lbl = document.getElementById('privacy-' + level);
        lbl.onclick = () => {
            levels.forEach(l => {
                document.getElementById('privacy-' + l).style.borderColor = '#e2e8f0';
                document.getElementById('privacy-' + l).style.background = '#fff';
            });
            lbl.style.borderColor = colors[level];
            lbl.style.background = level === 'standard' ? '#F0FDFA' : (level === 'interne' ? '#FEF3C7' : '#FEF2F2');
        };
    });

    document.getElementById('metadataModal').style.display = 'flex';
}

// ─── New Version Modal ────────────────────────────────────────────────
function showVersionModal(docId) {
    const form = document.getElementById('versionForm');
    form.action = '{{ url("/gel-secretary/documents") }}/' + docId + '/version';
    document.getElementById('versionFileName').textContent = 'Formats : PDF, Excel, Word (Max 10 Mo)';
    document.getElementById('versionFileInput').value = '';
    document.getElementById('versionModal').style.display = 'flex';
}
</script>

@endsection

