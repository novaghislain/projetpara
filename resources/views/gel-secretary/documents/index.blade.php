@extends('layouts.gel-secretary')
@section('title', 'Espace Documentaire')

@push('styles')
<style>
/* ==========================================================================
   ESPACE DOCUMENTAIRE - BENTO GRID DESIGN
   ========================================================================== */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: minmax(100px, auto);
    gap: 24px;
    margin-bottom: 40px;
}

.bento-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05),
                inset 0 0 0 1px rgba(255, 255, 255, 0.5);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}
.bento-card:hover {
    box-shadow: 0 15px 35px -10px rgba(13, 148, 136, 0.15),
                inset 0 0 0 1px rgba(255, 255, 255, 0.8);
}

.bento-header { grid-column: span 12; grid-row: span 1; display:flex; align-items:center; justify-content:space-between;}
.bento-folders { grid-column: span 8; grid-row: span 4; }
.bento-recent { grid-column: span 4; grid-row: span 4; }
.bento-favorites { grid-column: span 12; grid-row: span 2; }

/* Header Elements */
.doc-title { font-size: 24px; font-weight: 800; color: #1E293B; }
.doc-subtitle { font-size: 14px; color: #64748B; margin-top: 4px; }
.doc-actions { display: flex; gap: 12px; }
.btn-upload { background: linear-gradient(135deg, #0F766E 0%, #0D9488 100%); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; display:flex; align-items:center; gap:8px;}
.btn-upload:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(13, 148, 136, 0.3); }
.btn-vault { background: #1E293B; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; text-decoration:none; display:flex; align-items:center; gap:8px;}
.btn-vault:hover { background: #0F172A; }

/* Folders Grid */
.folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 16px; }
.folder-item { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 16px; text-decoration: none; color: inherit; transition: all 0.2s; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; height:140px;}
.folder-item:hover { background: white; border-color: #0D9488; transform: translateY(-4px); box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.1); }
.folder-icon { font-size: 42px; color: #3B82F6; margin-bottom: 12px; }
.folder-name { font-size: 14px; font-weight: 700; color: #1E293B; margin-bottom: 4px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;}
.folder-count { font-size: 11px; color: #94A3B8; font-weight: 600; background: #F1F5F9; padding: 2px 8px; border-radius: 10px; }

/* Recent & Favorites Documents List */
.doc-list { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; flex:1; overflow-y:auto; padding-right:8px;}
.doc-item { display: flex; align-items: center; gap: 16px; padding: 12px; border-radius: 12px; background: white; border: 1px solid #F1F5F9; transition: all 0.2s; cursor: pointer;}
.doc-item:hover { border-color: #CBD5E1; background: #F8FAFC; }
.doc-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.doc-icon.pdf { background: #FEF2F2; color: #EF4444; }
.doc-icon.image { background: #EFF6FF; color: #3B82F6; }
.doc-icon.word { background: #EEF2FF; color: #4F46E5; }
.doc-icon.excel { background: #ECFDF5; color: #10B981; }
.doc-icon.default { background: #F1F5F9; color: #64748B; }

.doc-info { flex: 1; min-width: 0; }
.doc-name { font-size: 13.5px; font-weight: 600; color: #1E293B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.doc-meta { font-size: 11px; color: #94A3B8; margin-top: 2px; }
.doc-action { color: #94A3B8; transition: color 0.2s; padding:8px;}
.doc-action:hover { color: #0D9488; }

.b-title { font-size: 16px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 8px; }

/* Animations */
.stagger-1 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.2s;}
.stagger-3 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.3s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

<div class="bento-grid">

    <!-- HEADER -->
    <div class="bento-header stagger-1">
        <div>
            <div class="doc-title">Espace Documentaire</div>
            <div class="doc-subtitle">{{ $activeClient ? $activeClient->nom_entreprise : 'Dossier global' }}</div>
        </div>
        <div class="doc-actions">
            <a href="{{ route('gel-secretary.safebox.index') }}" class="btn-vault">
                <i class="fas fa-vault"></i> Coffre-fort Numérique
            </a>
            <button class="btn-upload" onclick="document.getElementById('uploadModal').style.display='flex'">
                <i class="fas fa-cloud-upload-alt"></i> Importer
            </button>
        </div>
    </div>

    <!-- FOLDERS -->
    <div class="bento-card bento-folders stagger-2">
        <div class="b-title"><i class="fas fa-folder-open" style="color:#0D9488;"></i> Arborescence Principale</div>
        
        <div class="folder-grid">
            @forelse($folders as $folder)
                <a href="{{ route('gel-secretary.documents.folder', $folder->id) }}" class="folder-item">
                    <i class="fas {{ $folder->is_secured ? 'fa-folder-minus' : 'fa-folder' }} folder-icon" style="{{ $folder->is_secured ? 'color:#EF4444;' : '' }}"></i>
                    <div class="folder-name">{{ $folder->name }}</div>
                    <div class="folder-count">{{ $folder->documents_count ?? 0 }} document(s)</div>
                </a>
            @empty
                <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #94A3B8;">
                    <i class="fas fa-folder-plus" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                    <div style="font-size: 14px;">Aucun dossier racine trouvé.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- RECENT DOCUMENTS -->
    <div class="bento-card bento-recent stagger-3">
        <div class="b-title"><i class="fas fa-clock" style="color:#F59E0B;"></i> Récemment Ajoutés</div>
        
        <div class="doc-list">
            @forelse($recentDocuments as $doc)
                @php
                    $ext = strtolower(pathinfo($doc->file_path ?? $doc->name, PATHINFO_EXTENSION));
                    $iconClass = 'default'; $icon = 'fa-file';
                    if(in_array($ext, ['pdf'])) { $iconClass = 'pdf'; $icon = 'fa-file-pdf'; }
                    elseif(in_array($ext, ['jpg','jpeg','png'])) { $iconClass = 'image'; $icon = 'fa-file-image'; }
                    elseif(in_array($ext, ['doc','docx'])) { $iconClass = 'word'; $icon = 'fa-file-word'; }
                    elseif(in_array($ext, ['xls','xlsx'])) { $iconClass = 'excel'; $icon = 'fa-file-excel'; }
                @endphp
                <div class="doc-item">
                    <div class="doc-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></div>
                    <div class="doc-info">
                        <div class="doc-name" title="{{ $doc->name }}">{{ $doc->name }}</div>
                        <div class="doc-meta">{{ $doc->created_at->diffForHumans() }} • {{ number_format(($doc->file_size ?? 0) / 1024, 0) }} KB</div>
                    </div>
                    <a href="#" class="doc-action"><i class="fas fa-download"></i></a>
                </div>
            @empty
                <div style="text-align: center; color: #94A3B8; padding: 20px;">
                    Aucun document récent.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- UPLOAD MODAL (Basic UI structure) -->
<div id="uploadModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:white; width:500px; border-radius:20px; padding:32px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="margin:0; font-size:20px; font-weight:800; color:#1E293B;">Importer des documents</h3>
            <button onclick="document.getElementById('uploadModal').style.display='none'" style="background:none; border:none; font-size:20px; color:#94A3B8; cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>
        
        <div style="border: 2px dashed #CBD5E1; border-radius: 16px; padding: 40px; text-align: center; background: #F8FAFC; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#0D9488'; this.style.background='#F0FDFA';" onmouseout="this.style.borderColor='#CBD5E1'; this.style.background='#F8FAFC';">
            <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #94A3B8; margin-bottom: 16px;"></i>
            <div style="font-weight: 700; color: #334155;">Glissez-déposez vos fichiers ici</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 8px;">PDF, Word, Excel, Images (Max 10MB)</div>
        </div>
        
        <div style="margin-top:24px; text-align:right;">
            <button onclick="document.getElementById('uploadModal').style.display='none'" style="background:white; border:1px solid #E2E8F0; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer; margin-right:12px;">Annuler</button>
            <button style="background:#0D9488; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">Sélectionner des fichiers</button>
        </div>
    </div>
</div>

@endsection