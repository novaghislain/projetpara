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
          Gestion centralisée des archives pour : <strong>{{ $activeClient->nom_entreprise }}</strong>
        @else
          Veuillez sélectionner une entreprise active dans la barre supérieure.
        @endif
      </p>
    </div>

  </div>

  @if($activeClient)
    <div style="display:flex; gap:20px; align-items:flex-start; margin-bottom:24px;">
        <!-- Explorer Sidebar -->
        <div style="width:220px; background:white; border:1px solid #e2e8f0; border-radius:12px; padding:16px; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:12px; padding-left:8px;">Explorateur</div>
            <a href="{{ route('gel-secretary.documents.index') }}" style="display:flex; align-items:center; gap:10px; padding:10px 12px; color:#64748b; font-weight:600; font-size:13px; border-radius:8px; text-decoration:none; transition:0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-hdd"></i> Mon Espace
            </a>
            <a href="{{ route('gel-secretary.documents.trash') }}" style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#F0FDF4; color:var(--sec-primary); font-weight:600; font-size:13px; border-radius:8px; text-decoration:none; margin-bottom:8px; border:1px solid #bbf7d0;">
                <i class="fas fa-trash"></i> Corbeille
            </a>
        </div>
        
        <!-- Explorer Main -->
        <div style="flex:1; background:white; border-radius:12px; padding:20px; min-height:500px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-trash-restore" style="color:var(--sec-primary-light);"></i> Corbeille
                </h3>
            </div>

            @if(count($trashedFolders) == 0 && count($trashedDocuments) == 0)
              <div style="text-align:center; padding:60px 20px; color:#94a3b8;">
                <i class="fas fa-dumpster" style="font-size:48px; margin-bottom:16px; color:#cbd5e1;"></i>
                <h3 style="font-size:16px; font-weight:600; color:#475569;">La corbeille est vide</h3>
                <p style="font-size:13px;">Les éléments supprimés apparaîtront ici.</p>
              </div>
            @else
              
              <!-- Folders -->
              @if(count($trashedFolders) > 0)
                <h4 style="font-size:13px; font-weight:600; color:#64748b; margin-bottom:12px; text-transform:uppercase;">Dossiers supprimés</h4>
                <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:24px;">
                  @foreach($trashedFolders as $folder)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                      <div style="display:flex; align-items:center; gap:12px;">
                        <i class="fas fa-folder" style="color:#94a3b8; font-size:20px;"></i>
                        <div>
                          <div style="font-weight:600; font-size:14px; color:#334155;">{{ $folder->name }}</div>
                          <div style="font-size:11px; color:#94a3b8;">Supprimé le {{ $folder->deleted_at->format('d/m/Y H:i') }}</div>
                        </div>
                      </div>
                      <div style="display:flex; gap:8px;">
                        <form action="{{ route('gel-secretary.documents.restore-folder', $folder->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="sec-btn" style="padding:6px 12px; font-size:12px; background:white; border:1px solid #cbd5e1; color:#0f172a;">
                            <i class="fas fa-trash-restore text-success"></i> Restaurer
                          </button>
                        </form>
                        <form action="{{ route('gel-secretary.documents.force-delete-folder', $folder->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer définitivement ce dossier et son contenu ?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="sec-btn" style="padding:6px 12px; font-size:12px; background:white; border:1px solid #fca5a5; color:#ef4444;">
                            <i class="fas fa-times"></i> Supprimer définitivement
                          </button>
                        </form>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif

              <!-- Documents -->
              @if(count($trashedDocuments) > 0)
                <h4 style="font-size:13px; font-weight:600; color:#64748b; margin-bottom:12px; text-transform:uppercase;">Documents supprimés</h4>
                <div style="display:flex; flex-direction:column; gap:8px;">
                  @foreach($trashedDocuments as $doc)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                      <div style="display:flex; align-items:center; gap:12px;">
                        <i class="fas fa-file-alt" style="color:#94a3b8; font-size:20px;"></i>
                        <div>
                          <div style="font-weight:600; font-size:14px; color:#334155;">{{ $doc->name }}</div>
                          <div style="font-size:11px; color:#94a3b8;">Supprimé le {{ $doc->deleted_at->format('d/m/Y H:i') }}</div>
                        </div>
                      </div>
                      <div style="display:flex; gap:8px;">
                        <form action="{{ route('gel-secretary.documents.restore-document', $doc->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="sec-btn" style="padding:6px 12px; font-size:12px; background:white; border:1px solid #cbd5e1; color:#0f172a;">
                            <i class="fas fa-trash-restore text-success"></i> Restaurer
                          </button>
                        </form>
                        <form action="{{ route('gel-secretary.documents.force-delete-document', $doc->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer définitivement ce document ?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="sec-btn" style="padding:6px 12px; font-size:12px; background:white; border:1px solid #fca5a5; color:#ef4444;">
                            <i class="fas fa-times"></i> Supprimer définitivement
                          </button>
                        </form>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            @endif
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
@endsection
