@extends('layouts.gel-secretary')
@section('title', 'Carnet d\'Adresses')

@push('styles')
<style>
/* ==========================================================================
   CONTACTS - BENTO GRID DESIGN
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

.bento-header { grid-column: span 12; grid-row: span 1; display:flex; align-items:center; justify-content:space-between; }
.bento-kpi { grid-column: span 4; grid-row: span 1; display:flex; flex-direction:column; justify-content:center;}
.bento-list { grid-column: span 12; grid-row: span 5; padding:0; overflow:hidden;}

/* Header Elements */
.hc-title { font-size: 24px; font-weight: 800; font-family: 'Inter', sans-serif;}
.hc-sub { font-size: 13px; opacity: 0.8; margin-top: 4px; color:#64748B;}
.btn-new { background: #0D9488; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; display:flex; align-items:center; gap:8px;}
.btn-new:hover { background: #0F766E; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(13, 148, 136, 0.3);}

/* KPIs */
.kpi-value { font-size: 28px; font-weight: 800; color: #1E293B; line-height: 1; margin-bottom: 8px;}
.kpi-label { font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;}

/* Search & Filters */
.cl-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #E2E8F0; background:rgba(255,255,255,0.5);}
.cl-title { font-size:18px; font-weight:800; color:#1E293B;}
.cl-search { position:relative; }
.cl-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94A3B8; }
.cl-search input { background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px 10px 36px; outline:none; font-size:13px; width:280px; transition:all 0.2s; font-family:inherit;}
.cl-search input:focus { background:white; border-color:#0D9488; box-shadow:0 0 0 3px rgba(13,148,136,0.1); width:320px;}

/* Table Styles */
.table-wrapper { width: 100%; overflow-x: auto; flex:1;}
.f-table { width: 100%; border-collapse: collapse; text-align: left; }
.f-table th { padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; border-bottom: 1px solid #E2E8F0; background: #F8FAFC; }
.f-table td { padding: 16px 24px; font-size: 13.5px; color: #334155; border-bottom: 1px solid #F1F5F9; font-weight: 500;}
.f-table tbody tr { transition: all 0.2s; cursor:pointer;}
.f-table tbody tr:hover { background: #F8FAFC; transform:scale(1.002); }

/* Avatar & Info */
.contact-cell { display:flex; align-items:center; gap:12px; }
.contact-avatar { width:40px; height:40px; border-radius:50%; background:#E0F2FE; color:#0369A1; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; flex-shrink:0;}
.contact-info { display:flex; flex-direction:column; }
.contact-name { font-weight:700; color:#1E293B; font-size:14px;}
.contact-role { font-size:12px; color:#64748B; margin-top:2px;}

/* Badges */
.badge-type { padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }
.bt-internal { background: #EFF6FF; color: #3B82F6; }
.bt-portal { background: #F5F3FF; color: #7C3AED; }

.action-btn { color:#94A3B8; padding:8px; border-radius:8px; transition:all 0.2s; border:none; background:none; cursor:pointer;}
.action-btn:hover { color:#EF4444; background:#FEF2F2; }

/* Animations */
.stagger-1 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.2s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

@php
    $nbTotal = $contacts->count();
    $nbInternal = $contacts->where('type', 'internal')->count();
    $nbPortal = $contacts->where('type', 'portal')->count();
@endphp

<div class="bento-grid">

    <!-- HEADER -->
    <div class="bento-card bento-header stagger-1">
        <div>
            <div class="hc-title">Carnet d'Adresses</div>
            <div class="hc-sub">{{ $activeClient ? $activeClient->nom_entreprise : 'Tous les contacts' }}</div>
        </div>
        <div>
            <button class="btn-new" onclick="document.getElementById('modalContact').style.display='flex'">
                <i class="fas fa-user-plus"></i> Nouveau Contact
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="bento-card bento-kpi stagger-2">
        <div class="kpi-value">{{ $nbTotal }}</div>
        <div class="kpi-label"><i class="fas fa-users" style="color:#0D9488;"></i> Total Contacts</div>
    </div>
    <div class="bento-card bento-kpi stagger-2" style="animation-delay:0.3s;">
        <div class="kpi-value" style="color:#3B82F6;">{{ $nbInternal }}</div>
        <div class="kpi-label"><i class="fas fa-address-card" style="color:#3B82F6;"></i> Contacts Internes</div>
    </div>
    <div class="bento-card bento-kpi stagger-2" style="animation-delay:0.4s;">
        <div class="kpi-value" style="color:#7C3AED;">{{ $nbPortal }}</div>
        <div class="kpi-label"><i class="fas fa-globe" style="color:#7C3AED;"></i> Accès Portail</div>
    </div>

    <!-- TABLE -->
    <div class="bento-card bento-list stagger-2">
        <div class="cl-header">
            <div class="cl-title">Répertoire</div>
            <div class="cl-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchContact" placeholder="Rechercher par nom, email, téléphone...">
            </div>
        </div>
        
        <div class="table-wrapper">
            <table class="f-table" id="contactsTable">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Type</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $c)
                        @php
                            $badge = $c->type == 'portal' ? 'bt-portal' : 'bt-internal';
                            $typeName = $c->type == 'portal' ? 'Portail' : 'Interne';
                            $initials = strtoupper(substr($c->name, 0, 2));
                        @endphp
                        <tr onclick="if(!event.target.closest('button') && !event.target.closest('form')) window.location.href='{{ route('gel-secretary.contacts.show', $c->id) }}'">
                            <td>
                                <div class="contact-cell">
                                    <div class="contact-avatar">{{ $initials }}</div>
                                    <div class="contact-info">
                                        <div class="contact-name">{{ $c->name }}</div>
                                        <div class="contact-role">{{ $c->position ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $c->email ?? '—' }}</td>
                            <td>{{ $c->phone ?? '—' }}</td>
                            <td><span class="badge-type {{ $badge }}">{{ $typeName }}</span></td>
                            <td style="text-align:right;">
                                @if($c->type == 'internal')
                                <form action="{{ route('gel-secretary.contacts.destroy', $c->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:#94A3B8;">
                                <i class="fas fa-address-book" style="font-size:32px; margin-bottom:12px; opacity:0.5;"></i>
                                <div>Aucun contact trouvé dans le carnet d'adresses.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL ADD CONTACT -->
<div id="modalContact" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:white; width:450px; border-radius:20px; padding:32px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="margin:0; font-size:20px; font-weight:800; color:#1E293B;">Nouveau Contact</h3>
            <button onclick="document.getElementById('modalContact').style.display='none'" style="background:none; border:none; font-size:20px; color:#94A3B8; cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('gel-secretary.contacts.store') }}" method="POST">
            @csrf
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#64748B; margin-bottom:8px;">Nom complet <span style="color:#EF4444;">*</span></label>
                <input type="text" name="name" required style="width:100%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px; outline:none; font-family:inherit;">
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#64748B; margin-bottom:8px;">Fonction / Rôle</label>
                <input type="text" name="position" style="width:100%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px; outline:none; font-family:inherit;">
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#64748B; margin-bottom:8px;">Email</label>
                <input type="email" name="email" style="width:100%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px; outline:none; font-family:inherit;">
            </div>
            
            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#64748B; margin-bottom:8px;">Téléphone</label>
                <input type="text" name="phone" style="width:100%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px; outline:none; font-family:inherit;">
            </div>
            
            <div style="text-align:right;">
                <button type="button" onclick="document.getElementById('modalContact').style.display='none'" style="background:white; border:1px solid #E2E8F0; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer; margin-right:12px;">Annuler</button>
                <button type="submit" style="background:#0D9488; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Live Search
    document.getElementById('searchContact').addEventListener('keyup', function(e) {
        let term = e.target.value.toLowerCase();
        let rows = document.querySelectorAll('#contactsTable tbody tr');
        rows.forEach(row => {
            if(row.innerText.toLowerCase().includes(term)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush

@endsection
