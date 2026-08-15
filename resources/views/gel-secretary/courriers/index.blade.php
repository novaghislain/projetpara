@extends('layouts.gel-secretary')
@section('title', 'Boîte aux Lettres (Courriers)')

@push('styles')
<style>
/* ==========================================================================
   COURRIERS - BENTO GRID DESIGN
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

.bento-sidebar { grid-column: span 3; grid-row: span 6; display:flex; flex-direction:column; gap:16px; }
.bento-main { grid-column: span 9; grid-row: span 6; padding:0;}

/* Sidebar Menu */
.c-menu-title { font-size:12px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:8px; padding-left:12px;}
.c-menu-item { display:flex; align-items:center; justify-content:space-between; padding:12px; border-radius:12px; color:#334155; font-size:14px; font-weight:600; text-decoration:none; transition:all 0.2s;}
.c-menu-item:hover { background:#F8FAFC; color:#0D9488;}
.c-menu-item.active { background:#F0FDFA; color:#0D9488;}
.c-menu-icon { width:24px; color:inherit; opacity:0.8;}
.c-menu-badge { background:#EF4444; color:white; font-size:11px; padding:2px 8px; border-radius:10px;}

.c-btn-new { background:#0D9488; color:white; text-decoration:none; text-align:center; padding:14px; border-radius:12px; font-weight:700; display:block; transition:all 0.2s; border:none; width:100%; cursor:pointer;}
.c-btn-new:hover { background:#0F766E; transform:translateY(-2px); box-shadow:0 4px 12px rgba(13,148,136,0.2); color:white;}

/* Courrier List Header */
.cl-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #E2E8F0; background:rgba(255,255,255,0.5); border-radius:20px 20px 0 0;}
.cl-title { font-size:20px; font-weight:800; color:#1E293B;}
.cl-search { position:relative; }
.cl-search input { background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:8px 12px 8px 36px; outline:none; font-size:13px; width:250px;}
.cl-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94A3B8; font-size:13px;}

/* Courrier List Items */
.courrier-list { flex:1; overflow-y:auto; padding:12px;}
.courrier-item { display:grid; grid-template-columns: 48px 1fr 150px 100px; gap:16px; align-items:center; padding:16px; border-bottom:1px solid #F1F5F9; transition:all 0.2s; cursor:pointer;}
.courrier-item:hover { background:#F8FAFC; border-radius:12px; border-bottom-color:transparent;}
.courrier-item:last-child { border-bottom:none;}
.ci-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px;}
.ci-icon.entrant { background:#EFF6FF; color:#3B82F6;}
.ci-icon.sortant { background:#FEF2F2; color:#EF4444;}
.ci-icon.interne { background:#FFFBEB; color:#D97706;}
.ci-sender { font-size:14px; font-weight:700; color:#1E293B; margin-bottom:2px;}
.ci-subject { font-size:13px; color:#64748B;}
.ci-date { font-size:12px; color:#94A3B8;}
.ci-badge { padding:4px 8px; border-radius:6px; font-size:11px; font-weight:600; text-align:center;}

.bg-red { background:#FEF2F2; color:#DC2626;}
.bg-green { background:#ECFDF5; color:#10B981;}
.bg-yellow { background:#FFFBEB; color:#D97706;}
.bg-blue { background:#EFF6FF; color:#3B82F6;}

/* Animations */
.stagger-1 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.2s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

<div class="bento-grid">
    
    <!-- SIDEBAR -->
    <div class="bento-sidebar stagger-1">
        <a href="{{ route('gel-secretary.courriers.create') }}" class="c-btn-new"><i class="fas fa-plus"></i> Nouveau Courrier</a>
        
        <div class="bento-card" style="padding:16px;">
            <div class="c-menu-title">Boîte de réception</div>
            <a href="{{ route('gel-secretary.courriers.index', ['type' => 'entrant']) }}" class="c-menu-item {{ $type == 'entrant' ? 'active' : '' }}">
                <div><i class="fas fa-inbox c-menu-icon"></i> Entrants</div>
                @if($stats['entrants_non_traites'] > 0)
                    <span class="c-menu-badge">{{ $stats['entrants_non_traites'] }}</span>
                @endif
            </a>
            <a href="{{ route('gel-secretary.courriers.index', ['type' => 'sortant']) }}" class="c-menu-item {{ $type == 'sortant' ? 'active' : '' }}">
                <div><i class="fas fa-paper-plane c-menu-icon"></i> Sortants</div>
                @if($stats['sortants_brouillons'] > 0)
                    <span class="c-menu-badge" style="background:#F59E0B;">{{ $stats['sortants_brouillons'] }}</span>
                @endif
            </a>
            <a href="{{ route('gel-secretary.courriers.index', ['type' => 'interne']) }}" class="c-menu-item {{ $type == 'interne' ? 'active' : '' }}">
                <div><i class="fas fa-building c-menu-icon"></i> Internes</div>
            </a>
            
            <div class="c-menu-title" style="margin-top:24px;">Filtres</div>
            <a href="{{ route('gel-secretary.courriers.index', ['type' => $type, 'statut' => 'a_traiter']) }}" class="c-menu-item">
                <div><i class="fas fa-exclamation-circle c-menu-icon" style="color:#EF4444;"></i> À traiter</div>
            </a>
            <a href="{{ route('gel-secretary.courriers.index', ['type' => $type, 'statut' => 'archives']) }}" class="c-menu-item">
                <div><i class="fas fa-archive c-menu-icon"></i> Archives</div>
            </a>
        </div>
    </div>

    <!-- MAIN COURRIER LIST -->
    <div class="bento-card bento-main stagger-2">
        <div class="cl-header">
            <div class="cl-title">
                Courriers {{ ucfirst($type) }}s
            </div>
            <div class="cl-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Rechercher (Expéditeur, Objet)...">
            </div>
        </div>
        
        <div class="courrier-list">
            @forelse($courriers as $c)
                @php
                    $iconClass = $type;
                    $icon = $type == 'entrant' ? 'fa-envelope-open-text' : ($type == 'sortant' ? 'fa-paper-plane' : 'fa-building');
                    
                    $badgeClass = 'bg-blue';
                    $statut = $c->statut ?? 'nouveau';
                    if($statut == 'recu' || $statut == 'nouveau') $badgeClass = 'bg-red';
                    if($statut == 'traite' || $statut == 'envoye') $badgeClass = 'bg-green';
                    if($statut == 'brouillon' || $statut == 'en_cours') $badgeClass = 'bg-yellow';
                @endphp
                <div class="courrier-item" onclick="window.location.href='{{ route('gel-secretary.courriers.show', $c->id) }}'">
                    <div class="ci-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></div>
                    <div>
                        <div class="ci-sender">{{ $type == 'entrant' ? ($c->expediteur ?? 'Inconnu') : ($c->destinataire ?? 'Inconnu') }}</div>
                        <div class="ci-subject">{{ $c->objet ?? 'Sans objet' }} <span style="opacity:0.5;font-size:11px;">#{{ $c->reference }}</span></div>
                    </div>
                    <div class="ci-date">
                        {{ \Carbon\Carbon::parse($c->date_courrier)->format('d/m/Y') }}
                    </div>
                    <div class="ci-badge {{ $badgeClass }}">
                        {{ strtoupper(str_replace('_', ' ', $statut)) }}
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:60px 20px; color:#94A3B8;">
                    <i class="fas fa-inbox" style="font-size:48px; margin-bottom:16px; opacity:0.3;"></i>
                    <div style="font-size:16px; font-weight:600; color:#475569;">Aucun courrier trouvé.</div>
                    <div style="font-size:13px; margin-top:4px;">La boîte est vide pour cette catégorie.</div>
                </div>
            @endforelse
        </div>
        
        <div style="padding:16px; border-top:1px solid #E2E8F0;">
            {{ $courriers->links('vendor.pagination.tailwind') ?? '' }}
        </div>
    </div>

</div>

@endsection
