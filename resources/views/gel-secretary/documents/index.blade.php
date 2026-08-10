@extends('layouts.gel-secretary')

@section('title', 'Documents — Secrétariat')

@section('content')
  <style>
    /* ── Explorateur hiérarchique ── */
    .db-tree-node { display: block; }
    .db-tree-node > summary {
      list-style: none;
      display: flex; align-items: center; gap: 8px;
      padding: 7px 10px;
      font-size: 13px; font-weight: 600; color: #334155;
      border-radius: 8px; cursor: pointer;
      transition: background 0.15s, color 0.15s;
    }
    .db-tree-node > summary::-webkit-details-marker { display: none; }
    .db-tree-node > summary::before {
      content: '▸'; color: #94a3b8; font-size: 10px; width: 12px; text-align: center;
      transition: transform 0.15s;
    }
    .db-tree-node[open] > summary::before { transform: rotate(90deg); }
    .db-tree-node > summary:hover,
    .db-tree-node.db-tree-leaf:hover { background: #f8fafc; color: var(--sec-primary); }
    .db-tree-node > summary i,
    .db-tree-node.db-tree-leaf i { color: #cbd5e1; font-size: 13px; width: 16px; text-align: center; }
    .db-tree-node > summary:hover i,
    .db-tree-node.db-tree-leaf:hover i { color: var(--sec-primary); }
    .db-tree-children {
      list-style: none; margin: 2px 0 2px 14px; padding-left: 10px;
      border-left: 1px solid #eef2f7;
    }
    .db-tree-leaf {
      display: flex; align-items: center; gap: 8px;
      padding: 7px 10px; font-size: 13px; font-weight: 600; color: #334155;
      border-radius: 8px; text-decoration: none;
      transition: background 0.15s, color 0.15s;
    }
    .db-count { font-size: 10px; }

    /* ── Cartes premium (aperçus) ── */
    .premium-folder-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px; padding: 8px 0;
    }
    .premium-folder-card {
      background: rgba(255,255,255,0.7); border: 1px solid rgba(226,232,240,0.9); border-radius: 14px;
      padding: 20px 16px; text-align: center; cursor: pointer;
      transition: all 0.3s cubic-bezier(0.25,0.8,0.25,1);
      box-shadow: 0 4px 15px rgba(0,0,0,0.03); text-decoration: none; color: inherit;
      display: flex; flex-direction: column; align-items: center; position: relative; overflow: hidden;
    }
    .premium-folder-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 25px rgba(13,148,136,0.12); border-color: var(--sec-primary-light);
    }
    .folder-icon { font-size: 42px; color:#FBBF24; filter: drop-shadow(0 4px 6px rgba(245,158,11,0.3)); margin-bottom:10px; }
    .folder-name { font-size: 13px; font-weight:700; color:var(--sec-text); margin-bottom:4px; }
    .folder-meta { font-size:11px; color:var(--sec-text-muted); background:rgba(241,245,249,0.8); padding:3px 10px; border-radius:12px; }
    .recent-badge { position:absolute; top:10px; right:10px; background:var(--sec-info); color:white; font-size:9px; padding:2px 6px; border-radius:8px; font-weight:700; text-transform:uppercase; z-index:2; }

    /* ── Context menu ── */
    .context-menu {
      display:none; position:absolute; z-index:10000; width:220px;
      background:rgba(255,255,255,0.97); border-radius:12px;
      box-shadow:0 10px 30px rgba(0,0,0,0.12); padding:8px; border:1px solid rgba(226,232,240,0.9);
    }
    .context-menu-item {
      padding:10px 12px; font-size:13.5px; font-weight:500; color:#475569; cursor:pointer;
      display:flex; align-items:center; gap:12px; border-radius:6px; transition:all 0.2s ease;
    }
    .context-menu-item i { font-size:14px; color:#94a3b8; width:16px; text-align:center; }
    .context-menu-item:hover { background:#f8fafc; color:var(--sec-primary); }
    .context-menu-item.danger:hover { background:#fef2f2; color:#ef4444; }
  </style>

  <div class="sec-page-header">
    <div>
      <h1 class="sec-page-title">
        <i class="fas fa-layer-group" style="color:var(--sec-primary); margin-right:8px;"></i>
        Espace Documentaire
      </h1>
      <p class="sec-page-sub">
        @if($activeClient)
          Gestion centralisée des archives pour : <strong>{{ $activeClient->company_name ?? $activeClient->nom_entreprise }}</strong>
        @else
          Veuillez sélectionner une entreprise active dans la barre supérieure.
        @endif
      </p>
    </div>
    @if($activeClient)
      <div style="display:flex; gap:10px;">
        <button class="sec-btn sec-btn-primary" onclick="window.SecScanner && SecScanner.open()">
          <i class="fas fa-camera"></i> Scanner
        </button>
        <form method="POST" action="{{ route('gel-secretary.documents.create-year-folder') }}" style="display:inline;">
          @csrf
          <input type="hidden" name="year" value="{{ $nextYear }}">
          <button type="submit" class="sec-btn" style="background:#f1f5f9; color:var(--sec-text-muted);"
            title="Ajoute le dossier année {{ $nextYear }} sous Courant (les mois sont garantis automatiquement)">
            <i class="fas fa-calendar-plus"></i> Nouvelle Année ({{ $nextYear }})
          </button>
        </form>
        <a href="{{ route('gel-secretary.documents.restructure-rapport') }}" class="sec-btn"
          style="background:white; color:var(--sec-text-muted); border:1px solid #cbd5e1;"
          title="Rapport avant/après de la restructuration documentaire">
          <i class="fas fa-clipboard-check"></i> Restructuration
        </a>
        <button class="sec-btn" style="background:white; color:var(--sec-primary); border:1px solid var(--sec-primary);" onclick="openQuickUploadModal()">
          <i class="fas fa-bolt"></i> Saisie Rapide
        </button>
      </div>
    @endif
  </div>

  @if($activeClient)
    <div style="display:flex; gap:20px; align-items:flex-start; margin-bottom:24px;">
      <!-- ── Explorateur : arbre hiérarchique unique (jamais plat) ── -->
      <div style="width:300px; background:white; border:1px solid #e2e8f0; border-radius:12px; padding:14px; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
        <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:10px; padding-left:8px;">
          <i class="fas fa-diagram-project" style="margin-right:6px;"></i> Explorateur
        </div>

        @if(isset($tree) && $tree && count($tree['children'] ?? []))
          @include('gel-secretary.documents._tree_node', ['node' => $tree])
        @else
          <div style="padding:12px; color:#94a3b8; font-size:12px;">Arborescence en cours d'initialisation…</div>
        @endif

        <div style="margin-top:14px; padding-top:12px; border-top:1px solid #f1f5f9;">
          <a href="{{ route('gel-secretary.documents.trash') }}"
             style="display:flex; align-items:center; gap:10px; padding:8px 12px; color:#64748b; font-weight:600; font-size:13px; border-radius:8px; text-decoration:none; transition:background 0.15s;"
             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
            <i class="fas fa-trash" style="color:#f87171;"></i> Corbeille
          </a>
        </div>
      </div>

      <!-- ── Contenu principal ── -->
      <div style="flex:1; background:white; border-radius:12px; padding:20px; min-height:520px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; gap:16px; flex-wrap:wrap;">
          <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0; display:flex; align-items:center; gap:8px; white-space:nowrap;">
            <i class="fas fa-folder-open" style="color:var(--sec-primary-light);"></i> Dossiers racines
          </h3>
          <div style="position:relative; width:280px; max-width:100%;">
            <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:12px;"></i>
            <input type="text" id="rootFolderSearch" onkeyup="filterRootFolders()" placeholder="Rechercher un dossier ou fichier…"
              style="width:100%; padding:7px 12px 7px 32px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none;">
          </div>
        </div>

        @if(empty($tree['children']))
          <div class="sec-card" style="padding:60px 40px; text-align:center; color:var(--sec-text-muted); border-radius:16px;">
            <i class="fas fa-sitemap" style="font-size:48px; color:#cbd5e1; margin-bottom:16px;"></i>
            <h2 style="font-size:18px; font-weight:700; color:var(--sec-text); margin-bottom:8px;">Arborescence non initialisée</h2>
            <p style="font-size:13px; max-width:400px; margin:0 auto 24px;">Ce client n'a pas encore de structure de dossiers.
              Cliquez ci-dessous pour générer l'arborescence standard complète avec tous les dossiers administratifs,
              courants, permanents et spéciaux.</p>
            <form method="POST" action="{{ route('gel-secretary.documents.init-structure') }}">
              @csrf
              <button type="submit" class="sec-btn sec-btn-primary">
                <i class="fas fa-magic"></i> Générer l'arborescence
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

        @if($favoriteDocuments->count() > 0)
          <div style="margin-bottom:20px;">
            <h4 style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:10px;"><i class="fas fa-star" style="color:#F59E0B; margin-right:6px;"></i> Favoris</h4>
            <div style="display:flex; flex-direction:column; gap:8px;">
              @foreach($favoriteDocuments as $doc)
                <a href="{{ route('gel-secretary.documents.folder', $doc->folder_id) }}"
                   style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; text-decoration:none; color:inherit; transition:0.15s;"
                   onmouseover="this.style.background='#FEF3C7'" onmouseout="this.style.background='#FFFBEB'">
                  <i class="fas fa-file-alt" style="color:#F59E0B;"></i>
                  <span style="flex:1; font-size:13px; font-weight:600; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">{{ $doc->name }}</span>
                  <span style="font-size:11px; color:var(--sec-text-muted);">{{ $doc->category }}</span>
                </a>
              @endforeach
            </div>
          </div>
        @endif

        @if($recentDocuments->count() > 0)
          <div style="margin-bottom:20px;">
            <h4 style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:10px;">
              <i class="fas fa-clock" style="color:var(--sec-info); margin-right:6px;"></i> Récents
            </h4>
            <div style="display:flex; flex-direction:column; gap:8px;">
              @foreach($recentDocuments as $doc)
                <a href="{{ route('gel-secretary.documents.folder', $doc->folder_id) }}"
                   style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; text-decoration:none; color:inherit; transition:0.15s;"
                   onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                  <i class="fas fa-{{ $doc->category === 'Facture' ? 'file-invoice' : 'file-alt' }}" style="color:var(--sec-primary-light);"></i>
                  <span style="flex:1; font-size:13px; font-weight:600; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">{{ $doc->name }}</span>
                  <span style="font-size:11px; color:var(--sec-text-muted);">{{ $doc->category }}</span>
                </a>
              @endforeach
            </div>
          </div>
        @endif

        @if($favoriteDocuments->count() === 0 && $recentDocuments->count() === 0)
          <div style="padding:40px 20px; text-align:center; color:var(--sec-text-muted);">
            <i class="fas fa-cloud-upload-alt" style="font-size:40px; color:#cbd5e1; margin-bottom:12px;"></i>
            <p style="font-size:13px; margin:0;">Naviguez dans l'Explorateur (à gauche) ou utilisez <strong>Scanner</strong>.<br>
            Cliquez sur un sous-dossier du mois courant (ex. Factures) puis « Scanner » pour numériser et classer un document.</p>
          </div>
        @endif

        <div id="documentSearchResults" style="display:none; margin-top:24px;"></div>
      </div>
    </div>

    <!-- Context menu (clic droit sur une carte racine) -->
    <div id="folderContextMenu" class="context-menu">
      <div class="context-menu-item" onclick="openRenameFolderModal()">
        <i class="fas fa-edit"></i> Renommer
      </div>
      <div class="context-menu-item danger" onclick="deleteFolderContext()">
        <i class="fas fa-trash"></i> Supprimer
      </div>
    </div>

    <!-- DELETE FOLDER MODAL -->
    <div id="deleteFolderConfirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px); z-index:10000; align-items:center; justify-content:center;">
      <div style="background:#fff; border-radius:16px; width:400px; max-width:90%; border:1px solid var(--sec-border); box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden; text-align:center;">
        <div style="padding:30px 20px 24px;">
          <div style="width:70px; height:70px; border-radius:50%; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:32px; margin:0 auto 20px;">
            <i class="fas fa-trash-alt"></i>
          </div>
          <h3 style="font-size:20px; font-weight:700; color:var(--sec-text); margin:0 0 12px;">Supprimer le dossier</h3>
          <p style="font-size:14px; color:var(--sec-text-muted); margin:0; line-height:1.5;">
            Vous êtes sur le point de supprimer :<br>
            <strong id="deleteFolderConfirmName" style="color:#1e293b; display:inline-block; margin-top:8px;"></strong><br>
            <span style="font-size:12px; color:#ef4444; font-weight:600; display:block; margin-top:12px;">Le dossier passe en Corbeille (récupérable).</span>
          </p>
        </div>
        <div style="padding:20px; background:#f8fafc; border-top:1px solid var(--sec-border); display:flex; justify-content:center; gap:12px;">
          <button type="button" class="sec-btn sec-btn-secondary" style="background:#f1f5f9; color:#475569; border:none;" onclick="document.getElementById('deleteFolderConfirmModal').style.display='none'">Annuler</button>
          <button type="button" class="sec-btn" style="background:#ef4444; color:#fff; border:none;" onclick="submitDeleteFolder()">Oui, supprimer</button>
        </div>
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

    <!-- QUICK UPLOAD MODAL -->
    <div id="quickUploadModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
      <div class="sec-card" style="width:520px; padding:24px; position:relative;">
        <h2 style="margin-top:0; margin-bottom:16px; font-size:18px;"><i class="fas fa-bolt" style="color:var(--sec-warning); margin-right:8px;"></i>Saisie rapide</h2>
        <form action="{{ route('gel-secretary.documents.upload') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div style="margin-bottom:16px; position:relative;">
            <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--sec-text);">Dossier de destination</label>
            <div style="position:relative;">
              <i class="fas fa-folder-open" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:12px;"></i>
              <input type="text" id="quFolderSearch" onkeyup="searchFolder(this.value)" placeholder="Ex: Courant / 2026 / 08_Août / Factures" autocomplete="off"
                style="width:100%; padding:10px 12px 10px 32px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box; font-size:13px;">
            </div>
            <input type="hidden" name="folder_id" id="quFolderId" required>
            <div id="folderSearchResults" style="display:none; max-height:180px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px; background:#fff; position:absolute; width:100%; z-index:50; box-shadow:0 10px 25px rgba(0,0,0,0.1); top:calc(100% + 4px); left:0;"></div>
          </div>

          <div style="margin-bottom:16px;">
            <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Fichier</label>
            <input type="file" name="file" required style="width:100%; padding:8px; border:1px dashed #94a3b8; border-radius:6px; background:#f8fafc; box-sizing:border-box;">
          </div>

          <div style="margin-bottom:16px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Catégorie</label>
              <select name="category" class="form-select" style="font-size:13px;">
                <option value="Divers">Divers</option>
                <option value="Courrier">Courrier</option>
                <option value="Facture">Facture</option>
                <option value="Comptabilité">Comptabilité</option>
                <option value="RH">RH</option>
                <option value="Rapport">Rapport</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Confidentialité</label>
              <select name="privacy_level" class="form-select" style="font-size:13px;">
                <option value="standard">Standard</option>
                <option value="interne">Interne</option>
                <option value="confidentiel">Confidentiel</option>
              </select>
            </div>
          </div>

          <div style="margin-bottom:16px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Année liée</label>
              <input type="number" name="annee_liee" value="{{ date('Y') }}" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
            </div>
            <div>
              <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Mois lié</label>
              <select name="mois_lie" class="form-select" style="font-size:13px;">
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ \Illuminate\Support\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}</option>
                @endfor
              </select>
            </div>
          </div>

          <div style="margin-bottom:16px; padding:12px; background:#F0FDFA; border:1px solid #CCFBF1; border-radius:6px;">
            <label style="font-size:12px; color:#0D9488; font-weight:600; cursor:pointer;">
              <input type="checkbox" name="use_ia" value="1" checked style="margin-right:6px;"> Analyser et classer avec l'IA
            </label>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:12px;">
            <button type="button" class="sec-btn" onclick="closeQuickUploadModal()" style="background:#f1f5f9; color:#475569; border:none;">Annuler</button>
            <button type="submit" class="sec-btn sec-btn-primary">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      const clientId = {{ $activeClient->id }};

      let mainSearchTimeout = null;

      // Recherche globale (nom + contenu OCR via text_content)
      function filterRootFolders() {
        const query = document.getElementById('rootFolderSearch').value.toLowerCase();
        const docResultsContainer = document.getElementById('documentSearchResults');

        // 1. Filtrer visuellement les cartes racine (grille validée)
        document.querySelectorAll('.root-folder-item').forEach(item => {
          const name = item.getAttribute('data-name');
          item.style.display = name.includes(query) ? 'flex' : 'none';
        });

        // 2. Rechercher les documents par AJAX
        clearTimeout(mainSearchTimeout);
        if (query.length < 2) {
          docResultsContainer.style.display = 'none';
          return;
        }

        mainSearchTimeout = setTimeout(() => {
          fetch(`{{ route('gel-secretary.search') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
              const docs = data['Documents'] || [];
              if (docs.length > 0) {
                let html = `<h4 style="font-size:14px; font-weight:700; color:var(--sec-text); margin-bottom:14px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;"><i class="fas fa-file-alt" style="color:var(--sec-primary-light);"></i> Documents trouvés</h4>`;
                html += `<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:12px;">`;
                docs.forEach(doc => {
                  html += `
                    <a href="${doc.url}" style="display:flex; align-items:center; gap:12px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; text-decoration:none; transition:0.2s;">
                      <div style="width:40px; height:40px; border-radius:8px; background:#e0e7ff; color:var(--sec-primary); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:16px;">
                        <i class="${doc.icon}"></i>
                      </div>
                      <div style="flex:1; overflow:hidden;">
                        <div style="font-size:13px; font-weight:600; color:var(--sec-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${doc.title}">${doc.title}</div>
                        <div style="font-size:11px; color:var(--sec-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fas fa-folder-open"></i> ${doc.subtitle}</div>
                      </div>
                    </a>`;
                });
                html += `</div>`;
                docResultsContainer.innerHTML = html;
                docResultsContainer.style.display = 'block';
              } else {
                docResultsContainer.innerHTML = '<div style="padding:16px; color:#94a3b8; font-size:13px; text-align:center;">Aucun document trouvé.</div>';
                docResultsContainer.style.display = 'block';
              }
            })
            .catch(err => console.error("Erreur de recherche:", err));
        }, 400);
      }

      // --- CONTEXT MENU (clic droit sur une carte racine) ---
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

      // Masquer le menu contextuel hors clic
      document.addEventListener('click', function () {
        document.getElementById('folderContextMenu').style.display = 'none';
      });

      function openRenameFolderModal() {
        document.getElementById('renameFolderModal').style.display = 'flex';
        document.getElementById('renameFolderInput').value = currentContextFolderName;
        document.getElementById('renameFolderForm').action = `{{ route('gel-secretary.documents.index') }}folder/${currentContextFolderId}/rename`;
      }

      function deleteFolderContext() {
        document.getElementById('deleteFolderConfirmName').innerText = `"${currentContextFolderName}"`;
        document.getElementById('deleteFolderConfirmModal').style.display = 'flex';
        document.getElementById('folderContextMenu').style.display = 'none';
      }

      function submitDeleteFolder() {
        const form = document.getElementById('deleteFolderForm');
        form.action = `{{ route('gel-secretary.documents.index') }}folder/${currentContextFolderId}`;
        form.submit();
      }

      // --- QUICK UPLOAD MODAL ---
      function openQuickUploadModal() { document.getElementById('quickUploadModal').style.display = 'flex'; }
      function closeQuickUploadModal() { document.getElementById('quickUploadModal').style.display = 'none'; }

      let searchTimeout = null;
      function searchFolder(query) {
        const resultsDiv = document.getElementById('folderSearchResults');
        const inputHidden = document.getElementById('quFolderId');
        inputHidden.value = '';
        if (query.length < 2) { resultsDiv.style.display = 'none'; return; }
        resultsDiv.style.display = 'block';
        clearTimeout(searchTimeout);
        resultsDiv.innerHTML = '<div style="padding:12px; color:#64748b; font-size:12px;"><i class="fas fa-spinner fa-spin"></i> Recherche…</div>';
        searchTimeout = setTimeout(() => {
          fetch(`{{ route('gel-secretary.documents.search-folders') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
              resultsDiv.innerHTML = '';
              if (data.length === 0) {
                resultsDiv.innerHTML = '<div style="padding:12px; color:#64748b; font-size:12px;"><i class="fas fa-info-circle"></i> Aucun dossier trouvé</div>';
                return;
              }
              data.forEach(folder => {
                const div = document.createElement('div');
                div.style.padding = '10px 16px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #f1f5f9';
                div.style.fontSize = '13px';
                div.style.color = '#334155';
                div.innerHTML = `<i class="fas fa-folder" style="color:#cbd5e1; margin-right:10px;"></i> ${folder.path}`;
                div.onclick = () => {
                  inputHidden.value = folder.id;
                  document.getElementById('quFolderSearch').value = folder.path;
                  resultsDiv.style.display = 'none';
                };
                div.onmouseover = () => { div.style.background = '#f8fafc'; div.style.color = 'var(--sec-primary)'; };
                div.onmouseout = () => { div.style.background = 'transparent'; div.style.color = '#334155'; };
                resultsDiv.appendChild(div);
              });
            });
        }, 300);
      }
    </script>

  @else
    <div class="sec-card" style="padding:60px 40px; text-align:center; color:var(--sec-text-muted); border-radius:16px; background:linear-gradient(180deg, #fff 0%, #f8fafc 100%);">
      <div style="width:80px; height:80px; background:var(--sec-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
        <i class="fas fa-building" style="font-size:36px; color:#cbd5e1;"></i>
      </div>
      <h2 style="font-size:18px; font-weight:700; color:var(--sec-text); margin-bottom:8px;">Aucune entreprise sélectionnée</h2>
      <p style="font-size:13px; max-width:400px; margin:0 auto;">Utilisez le sélecteur de la barre supérieure pour choisir l'entreprise dont vous souhaitez gérer les documents.</p>
    </div>
  @endif

  <!-- Zone d'accueil du module de scan caméra (construit par resources/js/scanning.js) -->
  <div id="secScannerRoot"></div>

@endsection

@push('scripts')
  <script>
    // Redondance sécurisée : ces fonctions sont aussi définies inline pour la Saisie rapide.
    function openQuickUploadModal() { document.getElementById('quickUploadModal').style.display = 'flex'; }
    function closeQuickUploadModal() { document.getElementById('quickUploadModal').style.display = 'none'; }
  </script>
  @vite(['resources/js/scanning.js'])
@endpush