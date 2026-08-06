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
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
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
      top: 0;
      left: 0;
      right: 0;
      height: 100%;
      background: linear-gradient(135deg, rgba(13, 148, 136, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .premium-folder-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 25px rgba(13, 148, 136, 0.12), inset 0 0 0 1px rgba(255, 255, 255, 0.8);
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
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .recent-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: var(--sec-info);
      color: white;
      font-size: 9px;
      padding: 2px 6px;
      border-radius: 8px;
      font-weight: 700;
      text-transform: uppercase;
      z-index: 2;
      box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);

    .context-menu {
      display: none;
      position: absolute;
      z-index: 10000;
      width: 200px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
      padding: 8px 0;
      border: 1px solid #e2e8f0;
    }
    .context-menu-item {
      padding: 10px 16px;
      font-size: 13px;
      color: #334155;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: background 0.2s;
    }
    .context-menu-item:hover {
      background-color: #f1f5f9;
      color: var(--sec-primary);
    }
    .context-menu-item.danger:hover {
      background-color: #fef2f2;
      color: #ef4444;
    }
  </style>

  <div class="sec-page-header">
    <div>
      <h1 class="sec-page-title">
        <i class="fas fa-layer-group"
          style="color:var(--sec-primary); margin-right:8px; filter: drop-shadow(0 2px 4px rgba(13,148,136,0.3));"></i>
        Espace Documentaire
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
          <form method="POST" action="{{ route('gel-secretary.documents.init-structure') }}"
            onsubmit="return confirm('Cette action va générer l\'arborescence standard pour l\'année {{ $nextYear }}.\n\nVoulez-vous continuer ?');">
            @csrf
            <button type="submit" class="sec-btn" style="background:#f1f5f9; color:var(--sec-text-muted);"
              title="Ajoute le sous-dossier de l'année {{ $nextYear }}">
              <i class="fas fa-calendar-plus"></i> Nouvelle Année ({{ $nextYear }})
            </button>
          </form>
        @endif
        <button class="sec-btn" style="background:white; color:var(--sec-primary); border:1px solid var(--sec-primary);" onclick="openCreateFolderModal()">
          <i class="fas fa-folder-plus"></i> Nouveau Dossier
        </button>
        <button class="sec-btn sec-btn-primary" onclick="openQuickUploadModal()">
          <i class="fas fa-bolt"></i> Saisie Rapide
        </button>
      </div>
    @endif
  </div>

  <!-- Context Menu HTML -->
  <div id="folderContextMenu" class="context-menu">
    <div class="context-menu-item" onclick="openRenameFolderModal()">
      <i class="fas fa-edit"></i> Renommer
    </div>
    <div class="context-menu-item danger" onclick="deleteFolderContext()">
      <i class="fas fa-trash"></i> Supprimer
    </div>
  </div>

  @if($activeClient)
    <div style="display:flex; gap:20px; align-items:flex-start; margin-bottom:24px;">
        <!-- Explorer Sidebar -->
        <div style="width:220px; background:white; border:1px solid #e2e8f0; border-radius:12px; padding:16px; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:12px; padding-left:8px;">Explorateur</div>
            <a href="{{ route('gel-secretary.documents.index') }}" style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#F0FDF4; color:var(--sec-primary); font-weight:600; font-size:13px; border-radius:8px; text-decoration:none; margin-bottom:8px; border:1px solid #bbf7d0;">
                <i class="fas fa-hdd"></i> Mon Espace
            </a>
            <a href="{{ route('gel-secretary.documents.trash') }}" style="display:flex; align-items:center; gap:10px; padding:10px 12px; color:#64748b; font-weight:600; font-size:13px; border-radius:8px; text-decoration:none; transition:0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-trash"></i> Corbeille
            </a>
        </div>
        
        <!-- Explorer Main -->
        <div style="flex:1; background:white; border-radius:12px; padding:20px; min-height:500px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-folder-open" style="color:var(--sec-primary-light);"></i> Dossiers racines
                </h3>
                <div style="position:relative; width: 250px;">
                    <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:12px;"></i>
                    <input type="text" id="rootFolderSearch" onkeyup="filterRootFolders()" placeholder="Rechercher un dossier..." style="width:100%; padding:8px 12px 8px 32px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; transition:border-color 0.2s;">
                </div>
            </div>

            @if(count($folders) == 0)
              <div class="sec-card" style="padding:60px 40px; text-align:center; color:var(--sec-text-muted); border-radius:16px;">
                <i class="fas fa-sitemap" style="font-size:48px; color:#cbd5e1; margin-bottom:16px;"></i>
                <h2 style="font-size:18px; font-weight:700; color:var(--sec-text); margin-bottom:8px;">Arborescence non initialisée
                </h2>
                <p style="font-size:13px; max-width:400px; margin:0 auto 24px;">Ce client n'a pas encore de structure de dossiers.
                  Cliquez ci-dessous pour générer l'arborescence standard complète (EDEN STORE) avec tous les dossiers administratifs,
                  courants, permanents et spéciaux.</p>
                <form method="POST" action="{{ route('gel-secretary.documents.init-structure') }}">
                  @csrf
                  <button type="submit" class="sec-btn sec-btn-primary">
                    <i class="fas fa-magic"></i> Générer l'arborescence EDEN STORE
                  </button>
                </form>
              </div>
            @else
              <div class="premium-folder-grid" id="rootFoldersContainer" style="margin-top:0; padding-top:0;">
                @foreach($folders as $folder)
                  <a href="{{ route('gel-secretary.documents.folder', $folder->id) }}" class="premium-folder-card root-folder-item"
                    data-name="{{ strtolower($folder->name) }}" data-id="{{ $folder->id }}" oncontextmenu="showFolderContextMenu(event, {{ $folder->id }}, '{{ $folder->name }}')">
                    <div class="folder-icon-wrapper">
                      <i class="fas fa-folder folder-icon"></i>
                    </div>
                    <div class="folder-name">{{ $folder->name }}</div>
                    <div class="folder-meta">{{ $folder->documents_count }} document(s)</div>
                  </a>
                @endforeach
              </div>
            @endif
        </div>
    </div>

    <script>
      const clientId = {{ $activeClient->id }};

      // Filter Root Folders
      function filterRootFolders() {
        const query = document.getElementById('rootFolderSearch').value.toLowerCase();
        const items = document.querySelectorAll('.root-folder-item');
        items.forEach(item => {
          const name = item.getAttribute('data-name');
          if (name.includes(query)) {
            item.style.display = 'flex';
          } else {
            item.style.display = 'none';
          }
        });


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

        if (query.length < 2) {
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
              if (data.length === 0) {
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
    <div id="quickUploadModal"
      style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
      <div class="sec-card" style="width:500px; padding:24px; position:relative; overflow:visible;">
        <h2 style="margin-top:0; margin-bottom:16px; font-size:18px;"><i class="fas fa-bolt"
            style="color:var(--sec-warning); margin-right:8px;"></i>Saisie Rapide</h2>
        <form action="{{ route('gel-secretary.documents.upload') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div style="margin-bottom:16px; position:relative;">
            <label
              style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--sec-text);">Rechercher
              le dossier de destination</label>
            <div style="position:relative;">
              <i class="fas fa-folder-open"
                style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:12px;"></i>
              <input type="text" id="quFolderSearch" onkeyup="searchFolder(this.value)" placeholder="Ex: Banque 2026-04"
                autocomplete="off"
                style="width:100%; padding:10px 12px 10px 32px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box; font-size:13px; color:var(--sec-text); transition:all 0.2s;">
            </div>
            <input type="hidden" name="folder_id" id="quFolderId" required>
            <div id="folderSearchResults"
              style="display:none; max-height:180px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px; background:#fff; position:absolute; width:100%; z-index:50; box-shadow:0 10px 25px rgba(0,0,0,0.1); top:calc(100% + 4px); left:0;">
            </div>
          </div>

          <div style="margin-bottom:16px; margin-top:32px;">
            <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Fichier à uploader</label>
            <input type="file" name="file" required
              style="width:100%; padding:8px; border:1px dashed #94a3b8; border-radius:6px; background:#f8fafc; box-sizing:border-box;">
          </div>

          <div
            style="margin-bottom:16px; padding:12px; background:#F0FDFA; border:1px solid #CCFBF1; border-radius:6px; display:flex; gap:8px; align-items:flex-start;">
            <input type="checkbox" name="use_ia" id="use_ia_checkbox" value="1" checked style="margin-top:3px;">
            <label for="use_ia_checkbox" style="font-size:12px; color:#0D9488; font-weight:600; cursor:pointer;">
              <i class="fas fa-magic"></i> Analyser et classer avec l'IA
              <div style="font-size:11px; font-weight:400; color:#14B8A6; margin-top:2px;">
                Détecte le type (Contrat, Facture...), ajoute des tags et peut générer des rappels.
              </div>
            </label>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
            <button type="button" class="sec-btn" onclick="closeQuickUploadModal()"
              style="background:#f1f5f9; color:#475569; border:none;">Annuler</button>
            <button type="submit" class="sec-btn sec-btn-primary">Enregistrer le document</button>
          </div>
        </form>
      </div>
    </div>

  @else
    <div class="sec-card"
      style="padding:60px 40px; text-align:center; color:var(--sec-text-muted); border-radius:16px; background:linear-gradient(180deg, #fff 0%, #f8fafc 100%);">
      <div
        style="width:80px; height:80px; background:var(--sec-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; box-shadow:inset 0 2px 4px rgba(0,0,0,0.05);">
        <i class="fas fa-building" style="font-size:36px; color:#cbd5e1;"></i>
      </div>
      <h2 style="font-size:18px; font-weight:700; color:var(--sec-text); margin-bottom:8px;">Aucune entreprise sélectionnée
      </h2>
      <p style="font-size:13px; max-width:400px; margin:0 auto;">Veuillez utiliser le sélecteur situé dans la barre
        supérieure pour choisir l'entreprise dont vous souhaitez gérer les documents. Vos préférences seront mémorisées.</p>
    </div>
  @endif

  <!-- CREATE FOLDER MODAL -->
  <div id="createFolderModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="sec-card" style="width:400px; padding:24px;">
      <h2 style="margin-top:0; font-size:16px;"><i class="fas fa-folder-plus"></i> Nouveau Dossier</h2>
      <form action="{{ route('gel-secretary.documents.create-folder') }}" method="POST">
        @csrf
        <div style="margin:16px 0;">
          <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Nom du dossier</label>
          <input type="text" name="name" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px;">
          <button type="button" class="sec-btn" onclick="document.getElementById('createFolderModal').style.display='none'">Annuler</button>
          <button type="submit" class="sec-btn sec-btn-primary">Créer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- RENAME FOLDER MODAL -->
  <div id="renameFolderModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="sec-card" style="width:400px; padding:24px;">
      <h2 style="margin-top:0; font-size:16px;"><i class="fas fa-edit"></i> Renommer Dossier</h2>
      <form id="renameFolderForm" method="POST">
        @csrf
        @method('PUT')
        <div style="margin:16px 0;">
          <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Nouveau nom</label>
          <input type="text" name="name" id="renameFolderInput" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px;">
          <button type="button" class="sec-btn" onclick="document.getElementById('renameFolderModal').style.display='none'">Annuler</button>
          <button type="submit" class="sec-btn sec-btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- HIDDEN DELETE FOLDER FORM -->
  <form id="deleteFolderForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
  </form>

  <script>
    function openCreateFolderModal() {
      document.getElementById('createFolderModal').style.display = 'flex';
    }

    let currentContextFolderId = null;
    let currentContextFolderName = null;

    function showFolderContextMenu(e, id, name) {
      e.preventDefault();
      currentContextFolderId = id;
      currentContextFolderName = name;
      const menu = document.getElementById('folderContextMenu');
      menu.style.display = 'block';
      menu.style.left = e.pageX + 'px';
      menu.style.top = e.pageY + 'px';
    }

    // Hide context menu on outside click
    document.addEventListener('click', function() {
      document.getElementById('folderContextMenu').style.display = 'none';
    });

    function openRenameFolderModal() {
      document.getElementById('renameFolderModal').style.display = 'flex';
      document.getElementById('renameFolderInput').value = currentContextFolderName;
      document.getElementById('renameFolderForm').action = `/gel-secretary/documents/folder/${currentContextFolderId}/rename`;
    }

    function deleteFolderContext() {
      if (confirm(`Voulez-vous vraiment supprimer le dossier "${currentContextFolderName}" ?`)) {
        const form = document.getElementById('deleteFolderForm');
        form.action = `/gel-secretary/documents/folder/${currentContextFolderId}`;
        form.submit();
      }
    }
  </script>
@endsection