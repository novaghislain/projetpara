@extends('layouts.gel-secretary')

@section('title', 'Documents — Secrétariat')

@section('content')
<style>
  /* Premium Folder Cards */
  .premium-folder-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    padding: 20px 0;
  }
  .premium-folder-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 16px;
    padding: 24px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.03), inset 0 0 0 1px rgba(255,255,255,0.5);
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    overflow: hidden;
  }
  .premium-folder-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 100%;
    background: linear-gradient(135deg, rgba(13, 148, 136, 0.05) 0%, rgba(255,255,255,0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  .premium-folder-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(13, 148, 136, 0.12), inset 0 0 0 1px rgba(255,255,255,0.8);
    border-color: var(--sec-primary-light);
  }
  .premium-folder-card:hover::before {
    opacity: 1;
  }
  .folder-icon-wrapper {
    position: relative;
    margin-bottom: 12px;
    z-index: 1;
    transition: transform 0.3s ease;
  }
  .premium-folder-card:hover .folder-icon-wrapper {
    transform: scale(1.05);
  }
  .folder-icon {
    font-size: 48px;
    color: #FBBF24;
    filter: drop-shadow(0 4px 6px rgba(245, 158, 11, 0.3));
  }
  .folder-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--sec-text);
    z-index: 1;
    margin-bottom: 4px;
  }
  .folder-meta {
    font-size: 11px;
    color: var(--sec-text-muted);
    z-index: 1;
    background: rgba(241, 245, 249, 0.8);
    padding: 3px 10px;
    border-radius: 12px;
  }

  /* Recent Folders Section */
  #recentFoldersSection {
    margin-bottom: 30px;
    display: none;
    animation: fadeInDown 0.4s ease forwards;
  }
  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .recent-badge {
    position: absolute;
    top: 10px; right: 10px;
    background: var(--sec-info);
    color: white;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 8px;
    font-weight: 700;
    text-transform: uppercase;
    z-index: 2;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
  }
</style>

<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-layer-group" style="color:var(--sec-primary); margin-right:8px; filter: drop-shadow(0 2px 4px rgba(13,148,136,0.3));"></i> Espace Documentaire
    </h1>
    <p class="sec-page-sub">
      @if($activeClient)
        Gestion centralisée des archives pour : <strong>{{ $activeClient->company_name }}</strong>
      @else
        Veuillez sélectionner une entreprise active dans la barre supérieure.
      @endif
    </p>
  </div>
  @if($activeClient)
  <div style="display:flex; gap:12px;">
    @if(count($folders) > 0)
    <form method="POST" action="{{ route('gel-secretary.documents.init-structure') }}" onsubmit="return confirm('Cette action va générer l\'arborescence standard pour l\'année {{ $nextYear }}.\n\nVoulez-vous continuer ?');">
        @csrf
        <button type="submit" class="sec-btn" style="background:#f1f5f9; color:var(--sec-text-muted);" title="Ajoute le sous-dossier de l'année {{ $nextYear }}">
            <i class="fas fa-calendar-plus"></i> Nouvelle Année ({{ $nextYear }})
        </button>
    </form>
    @endif
    <button class="sec-btn sec-btn-primary" onclick="openQuickUploadModal()">
        <i class="fas fa-bolt"></i> Saisie Rapide
    </button>
  </div>
  @endif
</div>

@if($activeClient)

  <!-- Smart Feature: Recent Folders -->
  <div id="recentFoldersSection">
    <h3 style="font-size:14px; font-weight:700; color:var(--sec-text); margin-bottom:12px; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-bolt" style="color:var(--sec-warning);"></i> Accès Rapide (Récents)
    </h3>
    <div class="premium-folder-grid" id="recentFoldersGrid" style="padding-top:0;">
      <!-- Populated via JS -->
    </div>
  </div>

  <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:12px;">
    <h3 style="font-size:14px; font-weight:700; color:var(--sec-text); margin:0;">
      <i class="fas fa-folder-open" style="color:var(--sec-text-muted); margin-right:6px;"></i> Dossiers à la racine
    </h3>
    <div style="position:relative; width: 250px;">
        <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:12px;"></i>
        <input type="text" id="rootFolderSearch" onkeyup="filterRootFolders()" placeholder="Rechercher un dossier..." style="width:100%; padding:8px 12px 8px 32px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; outline:none; transition:border-color 0.2s;">
    </div>
  </div>

  @if(isset($favoriteDocuments) && $favoriteDocuments->count() > 0)
  <div style="margin-bottom:24px;">
    <h3 style="font-size:14px; font-weight:700; color:var(--sec-text); margin-bottom:12px;"><i class="fas fa-star text-warning" style="margin-right:6px;"></i> Documents Favoris</h3>
    <div style="display:flex; gap:16px; overflow-x:auto; padding-bottom:8px;">
        @foreach($favoriteDocuments as $doc)
        <a href="{{ route('gel-secretary.documents.view', $doc->id) }}" target="_blank" style="display:flex; align-items:center; gap:12px; background:white; border:1px solid var(--sec-border); border-radius:8px; padding:10px 14px; min-width:250px; text-decoration:none; color:inherit; box-shadow:0 1px 2px rgba(0,0,0,0.02);">
            <div style="width:36px; height:36px; border-radius:6px; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div style="overflow:hidden;">
                <div style="font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $doc->name }}</div>
                <div style="font-size:11px; color:var(--sec-text-muted);">{{ $doc->folder->name ?? 'Racine' }}</div>
            </div>
        </a>
        @endforeach
    </div>
  </div>
  @endif

  @if(isset($recentDocuments) && $recentDocuments->count() > 0)
  <div style="margin-bottom:24px;">
    <h3 style="font-size:14px; font-weight:700; color:var(--sec-text); margin-bottom:12px;"><i class="fas fa-clock text-info" style="margin-right:6px;"></i> Ajouts Récents</h3>
    <div style="display:flex; gap:16px; overflow-x:auto; padding-bottom:8px;">
        @foreach($recentDocuments as $doc)
        <a href="{{ route('gel-secretary.documents.view', $doc->id) }}" target="_blank" style="display:flex; align-items:center; gap:12px; background:white; border:1px solid var(--sec-border); border-radius:8px; padding:10px 14px; min-width:250px; text-decoration:none; color:inherit; box-shadow:0 1px 2px rgba(0,0,0,0.02);">
            <div style="width:36px; height:36px; border-radius:6px; background:#E0F2FE; color:#0284C7; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div style="overflow:hidden;">
                <div style="font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $doc->name }}</div>
                <div style="font-size:11px; color:var(--sec-text-muted);">{{ $doc->created_at->diffForHumans() }}</div>
            </div>
        </a>
        @endforeach
    </div>
  </div>
  @endif

  @if(count($folders) == 0)
    <div class="sec-card" style="padding:60px 40px; text-align:center; color:var(--sec-text-muted); border-radius:16px;">
        <i class="fas fa-sitemap" style="font-size:48px; color:#cbd5e1; margin-bottom:16px;"></i>
        <h2 style="font-size:18px; font-weight:700; color:var(--sec-text); margin-bottom:8px;">Arborescence non initialisée</h2>
        <p style="font-size:13px; max-width:400px; margin:0 auto 24px;">Ce client n'a pas encore de structure de dossiers. Cliquez ci-dessous pour générer l'arborescence standard complète (EDEN STORE) avec tous les dossiers administratifs, courants, permanents et spéciaux.</p>
        <form method="POST" action="{{ route('gel-secretary.documents.init-structure') }}">
            @csrf
            <button type="submit" class="sec-btn sec-btn-primary">
                <i class="fas fa-magic"></i> Générer l'arborescence EDEN STORE
            </button>
        </form>
    </div>
  @else
    <div class="premium-folder-grid" id="rootFoldersContainer">
        @foreach($folders as $folder)
        <a href="{{ route('gel-secretary.documents.folder', $folder->id) }}" class="premium-folder-card root-folder-item" data-name="{{ strtolower($folder->name) }}" onclick="saveRecentFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}', {{ $folder->documents_count }})">
        <div class="folder-icon-wrapper">
            <i class="fas fa-folder folder-icon"></i>
        </div>
        <div class="folder-name">{{ $folder->name }}</div>
        <div class="folder-meta">{{ $folder->documents_count }} document(s)</div>
        </a>
        @endforeach
    </div>
  @endif

  <script>
    const clientId = {{ $activeClient->id }};
    
    // Save folder to recent
    function saveRecentFolder(id, name, count) {
      let recents = JSON.parse(localStorage.getItem('gel_recent_folders_' + clientId) || '[]');
      
      // Remove if exists
      recents = recents.filter(f => f.id !== id);
      
      // Add to front
      recents.unshift({ id, name, count, time: Date.now() });
      
      // Keep only top 3
      if (recents.length > 3) recents = recents.slice(0, 3);
      
      localStorage.setItem('gel_recent_folders_' + clientId, JSON.stringify(recents));
    }

    // Load recent folders
    function loadRecentFolders() {
      const recents = JSON.parse(localStorage.getItem('gel_recent_folders_' + clientId) || '[]');
      if (recents.length > 0) {
        document.getElementById('recentFoldersSection').style.display = 'block';
        const grid = document.getElementById('recentFoldersGrid');
        
        recents.forEach(folder => {
          const url = "{{ route('gel-secretary.documents.folder', ':id') }}".replace(':id', folder.id);
          const card = `
            <a href="${url}" class="premium-folder-card" onclick="saveRecentFolder(${folder.id}, '${folder.name.replace(/'/g, "\\'")}', ${folder.count})">
              <span class="recent-badge">Récent</span>
              <div class="folder-icon-wrapper">
                <i class="fas fa-folder-open folder-icon" style="color:#60A5FA; filter: drop-shadow(0 4px 6px rgba(96, 165, 250, 0.3));"></i>
              </div>
              <div class="folder-name">${folder.name}</div>
              <div class="folder-meta">${folder.count} document(s)</div>
            </a>
          `;
          grid.insertAdjacentHTML('beforeend', card);
        });
      }
    }

    // Filter Root Folders
    function filterRootFolders() {
        const query = document.getElementById('rootFolderSearch').value.toLowerCase();
        const items = document.querySelectorAll('.root-folder-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if(name.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadRecentFolders);

    // --- QUICK UPLOAD MODAL ---
    function openQuickUploadModal() {
        document.getElementById('quickUploadModal').style.display = 'flex';
    }
    function closeQuickUploadModal() {
        document.getElementById('quickUploadModal').style.display = 'none';
    }

    let searchTimeout = null;
    function searchFolder(query) {
        const resultsDiv = document.getElementById('folderSearchResults');
        const inputHidden = document.getElementById('quFolderId');
        
        inputHidden.value = ''; // Reset selection
        
        if(query.length < 2) {
            resultsDiv.style.display = 'none';
            return;
        }

        resultsDiv.style.display = 'block';
        clearTimeout(searchTimeout);
        resultsDiv.innerHTML = '<div style="padding:12px; color:#64748b; font-size:12px; display:flex; align-items:center; gap:8px;"><i class="fas fa-spinner fa-spin"></i> Recherche en cours...</div>';
        
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('gel-secretary.documents.search-folders') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    if(data.length === 0) {
                        resultsDiv.innerHTML = '<div style="padding:12px; color:#64748b; font-size:12px;"><i class="fas fa-info-circle" style="margin-right:6px;"></i>Aucun dossier trouvé</div>';
                        return;
                    }
                    data.forEach(folder => {
                        const div = document.createElement('div');
                        div.style.padding = '10px 16px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #f1f5f9';
                        div.style.fontSize = '13px';
                        div.style.color = '#334155';
                        div.style.display = 'flex';
                        div.style.alignItems = 'center';
                        div.innerHTML = `<i class="fas fa-folder" style="color:#cbd5e1; margin-right:10px; font-size:14px;"></i> ${folder.path}`;
                        
                        div.onclick = () => {
                            inputHidden.value = folder.id;
                            document.getElementById('quFolderSearch').value = folder.path;
                            resultsDiv.style.display = 'none'; // Cacher après sélection
                        };
                        div.onmouseover = () => {
                            div.style.background = '#f8fafc';
                            div.style.color = 'var(--sec-primary)';
                        };
                        div.onmouseout = () => {
                            div.style.background = 'transparent';
                            div.style.color = '#334155';
                        };
                        resultsDiv.appendChild(div);
                    });
                });
        }, 300);
    }
  </script>

  <!-- QUICK UPLOAD MODAL -->
  <div id="quickUploadModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="sec-card" style="width:500px; padding:24px; position:relative; overflow:visible;">
        <h2 style="margin-top:0; margin-bottom:16px; font-size:18px;"><i class="fas fa-bolt" style="color:var(--sec-warning); margin-right:8px;"></i>Saisie Rapide</h2>
        <form action="{{ route('gel-secretary.documents.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:16px; position:relative;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--sec-text);">Rechercher le dossier de destination</label>
                <div style="position:relative;">
                    <i class="fas fa-folder-open" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:12px;"></i>
                    <input type="text" id="quFolderSearch" onkeyup="searchFolder(this.value)" placeholder="Ex: Banque 2026-04" autocomplete="off" style="width:100%; padding:10px 12px 10px 32px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box; font-size:13px; color:var(--sec-text); transition:all 0.2s;">
                </div>
                <input type="hidden" name="folder_id" id="quFolderId" required>
                <div id="folderSearchResults" style="display:none; max-height:180px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px; background:#fff; position:absolute; width:100%; z-index:50; box-shadow:0 10px 25px rgba(0,0,0,0.1); top:calc(100% + 4px); left:0;">
                </div>
            </div>
            
            <div style="margin-bottom:16px; margin-top:32px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Fichier à uploader</label>
                <input type="file" name="file" required style="width:100%; padding:8px; border:1px dashed #94a3b8; border-radius:6px; background:#f8fafc; box-sizing:border-box;">
            </div>

            <div style="margin-bottom:16px; padding:12px; background:#F0FDFA; border:1px solid #CCFBF1; border-radius:6px; display:flex; gap:8px; align-items:flex-start;">
                <input type="checkbox" name="use_ia" id="use_ia_checkbox" value="1" checked style="margin-top:3px;">
                <label for="use_ia_checkbox" style="font-size:12px; color:#0D9488; font-weight:600; cursor:pointer;">
                    <i class="fas fa-magic"></i> Analyser et classer avec l'IA
                    <div style="font-size:11px; font-weight:400; color:#14B8A6; margin-top:2px;">
                        Détecte le type (Contrat, Facture...), ajoute des tags et peut générer des rappels.
                    </div>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="sec-btn" onclick="closeQuickUploadModal()" style="background:#f1f5f9; color:#475569; border:none;">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Enregistrer le document</button>
            </div>
        </form>
    </div>
  </div>

@else
<div class="sec-card" style="padding:60px 40px; text-align:center; color:var(--sec-text-muted); border-radius:16px; background:linear-gradient(180deg, #fff 0%, #f8fafc 100%);">
  <div style="width:80px; height:80px; background:var(--sec-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; box-shadow:inset 0 2px 4px rgba(0,0,0,0.05);">
    <i class="fas fa-building" style="font-size:36px; color:#cbd5e1;"></i>
  </div>
  <h2 style="font-size:18px; font-weight:700; color:var(--sec-text); margin-bottom:8px;">Aucune entreprise sélectionnée</h2>
  <p style="font-size:13px; max-width:400px; margin:0 auto;">Veuillez utiliser le sélecteur situé dans la barre supérieure pour choisir l'entreprise dont vous souhaitez gérer les documents. Vos préférences seront mémorisées.</p>
</div>
@endif
@endsection
