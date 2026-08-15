<style>
/* ==========================================================================
   GLOBAL DASHBOARD - "WOW" COMMAND CENTER DESIGN
   ========================================================================== */

/* Layout & Grid (Bento Style) */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: minmax(100px, auto);
    gap: 24px;
    margin-bottom: 40px;
}

/* Glassmorphism & Card Styles */
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
    transform: translateY(-2px);
    box-shadow: 0 15px 35px -10px rgba(13, 148, 136, 0.15),
                inset 0 0 0 1px rgba(255, 255, 255, 0.8);
}

/* Specific Grid Placements */
.bento-welcome { grid-column: span 8; grid-row: span 2; background: linear-gradient(135deg, #0F766E 0%, #0D9488 100%); color: white; border:none; position:relative; overflow:hidden;}
.bento-checklist { grid-column: span 4; grid-row: span 4; }
.bento-quick-actions { grid-column: span 8; grid-row: span 1; display:flex; flex-direction:row; gap:16px; padding:16px;}
.bento-radar { grid-column: span 5; grid-row: span 3; }
.bento-feed { grid-column: span 3; grid-row: span 3; }
.bento-kpi { grid-column: span 4; grid-row: span 2; }

/* Welcome Card Enhancements */
.bento-welcome::after {
    content: '';
    position: absolute;
    top: -50%; left: 50%;
    width: 200%; height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    transform: rotate(30deg);
    pointer-events: none;
}
.welcome-title { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px; font-family: 'Inter', sans-serif;}
.welcome-sub { font-size: 15px; opacity: 0.9; margin-bottom: 24px; font-weight: 300; }
.welcome-stats { display: flex; gap: 32px; }
.w-stat { display: flex; flex-direction: column; }
.w-stat-val { font-size: 32px; font-weight: 800; line-height: 1; }
.w-stat-lbl { font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

/* Quick Actions */
.qa-btn {
    flex: 1;
    background: white;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.qa-btn:hover {
    background: #F8FAFC;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.1);
    border-color: #99F6E4;
    color: #0D9488;
}
.qa-icon { width: 36px; height: 36px; border-radius: 10px; background: #F0FDFA; color: #0D9488; display: flex; align-items: center; justify-content: center; font-size: 16px; }

/* Card Headers */
.b-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.b-title { font-size: 16px; font-weight: 700; color: #1E293B; display: flex; align-items: center; gap: 8px; }
.b-icon { color: #8B5CF6; }

/* Radar Grid */
.radar-grid { display: flex; flex-direction: column; gap: 12px; }
.radar-item {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}
.radar-item:hover { border-color: #CBD5E1; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.r-company { font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 4px; }
.r-health { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; }
.r-badges { display: flex; gap: 8px; }
.r-badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; display: flex; align-items: center; gap: 4px;}
.rb-danger { background: #FEF2F2; color: #DC2626; }
.rb-warn { background: #FFFBEB; color: #D97706; }

/* Activity Feed */
.feed-list { display: flex; flex-direction: column; gap: 16px; position: relative; }
.feed-list::before { content: ''; position: absolute; left: 15px; top: 10px; bottom: 10px; width: 2px; background: #E2E8F0; z-index: 1;}
.feed-item { display: flex; gap: 16px; position: relative; z-index: 2; }
.feed-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: white; flex-shrink: 0; border: 3px solid white; box-shadow: 0 0 0 1px #E2E8F0;}
.feed-content { padding-top: 4px; }
.feed-text { font-size: 13px; color: #334155; font-weight: 500; line-height: 1.4; }
.feed-time { font-size: 11px; color: #94A3B8; margin-top: 2px; }

/* Checklist (Redesigned) */
.chk-item {
    display: flex; align-items: flex-start; gap: 12px; padding: 12px;
    border-radius: 12px; margin-bottom: 8px;
    background: white; border: 1px solid #F1F5F9;
    transition: all 0.2s;
    cursor: pointer;
}
.chk-item:hover { border-color: #99F6E4; transform: translateX(4px); box-shadow: 0 2px 8px rgba(13,148,136,0.05);}
.chk-item.done { opacity: 0.6; background: #F8FAFC; }
.chk-item.done .chk-text { text-decoration: line-through; }
.chk-check { width: 22px; height: 22px; border-radius: 6px; border: 2px solid #CBD5E1; display: flex; align-items: center; justify-content: center; margin-top: 2px; transition:all 0.2s;}
.chk-item.done .chk-check { background: #10B981; border-color: #10B981; color: white; }
.chk-item.done .chk-check::after { content: '\2713'; font-size: 14px; font-weight: bold;}
.chk-text { font-size: 13.5px; font-weight: 500; color: #1E293B; line-height: 1.4; }
.chk-meta { font-size: 11px; color: #64748B; margin-top: 4px; display: flex; gap: 8px;}

/* Animations */
.stagger-1 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.2s;}
.stagger-3 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.3s;}
.stagger-4 { animation: fadeUp 0.5s ease-out forwards; opacity: 0; animation-delay: 0.4s;}
</style>

<div class="bento-grid">
    
    <!-- WELCOME CARD -->
    <div class="bento-card bento-welcome stagger-1">
        <div style="position:relative; z-index:2;">
            <div class="welcome-title">Bonjour {{ $user->name ?? 'chère collaboratrice' }} 👋</div>
            <div class="welcome-sub">Voici un aperçu de votre portefeuille client. Prête à attaquer la journée ?</div>
            
            <div class="welcome-stats">
                <div class="w-stat">
                    <span class="w-stat-val">{{ $clients->count() }}</span>
                    <span class="w-stat-lbl">Entreprises gérées</span>
                </div>
                <div class="w-stat">
                    <span class="w-stat-val">{{ $docsToProcessCount }}</span>
                    <span class="w-stat-lbl">Documents à traiter</span>
                </div>
                <div class="w-stat">
                    <span class="w-stat-val" style="color:#A7F3D0;">{{ $stats['completion_rate'] }}%</span>
                    <span class="w-stat-lbl">Productivité IA</span>
                </div>
            </div>
        </div>
    </div>

    <!-- IA CHECKLIST -->
    <div class="bento-card bento-checklist stagger-2">
        <div class="b-header">
            <div class="b-title"><i class="fas fa-sparkles b-icon"></i> Checklist IA multi-clients</div>
            <span style="font-size:12px; font-weight:700; color:#0D9488; background:#F0FDFA; padding:4px 10px; border-radius:12px;">0/{{ count($aiChecklist) }}</span>
        </div>
        
        <div style="flex:1; overflow-y:auto; padding-right:4px;">
            @forelse($aiChecklist as $item)
                @php
                    $done  = !empty($item['statut']);
                    $label = $item['label'] ?? '';
                    $client = $item['client_name'] ?? 'Multiples';
                @endphp
                <div class="chk-item {{ $done ? 'done' : '' }}">
                    <div class="chk-check"></div>
                    <div>
                        <div class="chk-text">{{ $label }}</div>
                        <div class="chk-meta"><i class="fas fa-building" style="color:#94A3B8;"></i> {{ $client }}</div>
                    </div>
                </div>
            @empty
                <div style="padding:40px 20px; text-align:center; color:#94A3B8;">
                    <i class="fas fa-check-circle" style="font-size:32px; margin-bottom:12px; color:#E2E8F0;"></i>
                    <div style="font-size:13px;">Aucune tâche urgente identifiée par l'IA aujourd'hui.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="bento-card bento-quick-actions stagger-3" style="padding:16px;">
        <a href="javascript:void(0)" onclick="alert('Sélectionnez une entreprise avant de créer un courrier.')" class="qa-btn">
            <div class="qa-icon"><i class="fas fa-envelope-open-text"></i></div>
            Nouveau Courrier
        </a>
        <a href="javascript:void(0)" onclick="alert('Sélectionnez une entreprise avant d\'ajouter un document.')" class="qa-btn">
            <div class="qa-icon" style="background:#EFF6FF; color:#3B82F6;"><i class="fas fa-file-upload"></i></div>
            Déposer Document
        </a>
        <a href="javascript:void(0)" onclick="alert('Sélectionnez une entreprise avant de consigner un appel.')" class="qa-btn">
            <div class="qa-icon" style="background:#FFFBEB; color:#D97706;"><i class="fas fa-phone-alt"></i></div>
            Journal d'appel
        </a>
        <button onclick="generateDailyReport()" class="qa-btn" style="border-color:#99F6E4; background:#F0FDFA; color:#0F766E;">
            <div class="qa-icon" style="background:#CCFBF1; color:#0F766E;"><i class="fas fa-robot"></i></div>
            Rapport Global IA
        </button>
    </div>

    <!-- RADAR DES ENTREPRISES -->
    <div class="bento-card bento-radar stagger-4">
        <div class="b-header">
            <div class="b-title"><i class="fas fa-crosshairs b-icon" style="color:#EF4444;"></i> Radar des Entreprises</div>
            <div style="font-size:11px; color:#64748B;">Nécessitent votre attention</div>
        </div>
        
        <div class="radar-grid">
            @forelse($urgentClients as $c)
                <div class="radar-item">
                    <div>
                        <div class="r-company">{{ $c->company_name }}</div>
                        <div class="r-health" style="color: {{ $c->health < 50 ? '#DC2626' : ($c->health < 80 ? '#D97706' : '#10B981') }};">
                            <i class="fas fa-heartbeat"></i> Score de santé : {{ $c->health }}%
                        </div>
                    </div>
                    <div class="r-badges">
                        @if($c->pending_docs > 0)
                            <span class="r-badge rb-danger" title="Documents à traiter"><i class="fas fa-file-alt"></i> {{ $c->pending_docs }}</span>
                        @endif
                        @if($c->unprocessed_courriers > 0)
                            <span class="r-badge rb-warn" title="Courriers en attente"><i class="fas fa-envelope"></i> {{ $c->unprocessed_courriers }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div style="padding:20px; text-align:center; color:#94A3B8; font-size:13px;">
                    Toutes vos entreprises sont à jour ! 🎉
                </div>
            @endforelse
        </div>
    </div>

    <!-- ACTIVITY FEED -->
    <div class="bento-card bento-feed stagger-4">
        <div class="b-header">
            <div class="b-title"><i class="fas fa-history b-icon" style="color:#3B82F6;"></i> Activité Récente</div>
        </div>
        
        <div class="feed-list">
            @forelse($activityFeed as $feed)
                <div class="feed-item">
                    <div class="feed-icon" style="background:{{ $feed->color }};">
                        <i class="fas {{ $feed->icon }}"></i>
                    </div>
                    <div class="feed-content">
                        <div class="feed-text">{{ $feed->text }}</div>
                        <div class="feed-time">{{ $feed->time }}</div>
                    </div>
                </div>
            @empty
                <div style="padding:20px; text-align:center; color:#94A3B8; font-size:13px;">
                    Aucune activité récente.
                </div>
            @endforelse
        </div>
    </div>

</div>

<script>
// Intéractivité simple pour la checklist
document.querySelectorAll('.chk-item').forEach(item => {
    item.addEventListener('click', function() {
        this.classList.toggle('done');
    });
});
</script>
