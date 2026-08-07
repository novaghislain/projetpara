@extends('layouts.gel-secretary')
@section('title', 'Demandes clients — Secrétariat')

@section('content')
<style>
  .req-badge { display:inline-block; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; }
  .req-new { background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; }
  .req-pending { background:#FFFBEB; color:#D97706; border:1px solid #FDE68A; }
  .req-processed { background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; }
  .req-rejected { background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; }
  
  /* Invitations styles */
  .invitation-card {
    background: white; border-radius: 12px; padding: 24px;
    border: 1px solid var(--sec-border);
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  }
  .inv-info { display: flex; align-items: center; gap: 16px; }
  .inv-icon {
    width: 48px; height: 48px; border-radius: 50%;
    background: #F0FDFA; color: var(--sec-primary);
    display: flex; align-items: center; justify-content: center; font-size: 20px;
  }
  .inv-actions { display: flex; gap: 12px; }
  .btn-accept {
    background: var(--sec-primary); color: white; border: none;
    padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer;
  }
  .btn-accept:hover { background: #0D9488; }
  .btn-reject {
    background: white; color: #DC2626; border: 1px solid #FECACA;
    padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer;
  }
  .btn-reject:hover { background: #FEF2F2; }
</style>

<div class="sec-page-header">
  <div>
    <div class="sec-page-title">Boîte de réception</div>
    <div class="sec-page-sub">Gérez les demandes de vos clients (publics) et les invitations des entreprises.</div>
  </div>
</div>

@if(session('success'))
  <div style="padding: 12px 16px; background: #ECFDF5; color: #065F46; border-radius: 8px; margin-bottom: 24px;">
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div style="padding: 12px 16px; background: #FEF2F2; color: #991B1B; border-radius: 8px; margin-bottom: 24px;">
    {{ session('error') }}
  </div>
@endif

<!-- Nav tabs -->
<ul class="nav nav-pills mb-4" id="inboxTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="invitations-tab" data-bs-toggle="tab" data-bs-target="#invitations" type="button" role="tab" aria-controls="invitations" aria-selected="true" style="font-weight:600; border-radius:10px; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-envelope-open-text"></i> Invitations Entreprises
      @if(count($invitations) > 0)
        <span class="badge" style="background:#EF4444;">{{ count($invitations) }}</span>
      @endif
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab" aria-controls="requests" aria-selected="false" style="font-weight:600; border-radius:10px; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-inbox"></i> Demandes B2C (Prospects)
      @if($stats['new'] > 0)
        <span class="badge" style="background:#EF4444;">{{ $stats['new'] }}</span>
      @endif
    </button>
  </li>
</ul>

<!-- Tab panes -->
<div class="tab-content" id="inboxTabsContent">
  <!-- INVITATIONS TAB -->
  <div class="tab-pane fade show active" id="invitations" role="tabpanel" aria-labelledby="invitations-tab">
    <div style="max-width: 800px;">
      @forelse($invitations as $invitation)
        <div class="invitation-card">
          <div class="inv-info" style="flex: 1; min-width: 0;">
            <div class="inv-icon" style="flex-shrink: 0;">
              <i class="fas fa-building"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
              <div style="font-weight: 700; font-size: 16px; color: var(--sec-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $invitation->client->nom_entreprise ?? 'Entreprise' }}</div>
              <div style="font-size: 13px; color: var(--sec-text-muted);">Souhaite vous ajouter comme secrétaire</div>
            </div>
          </div>
          <div class="inv-actions" style="flex-shrink: 0;">
            <button type="button" class="btn-reject" onclick="openRejectModal({{ $invitation->id }})"><i class="fas fa-times me-1"></i> Refuser</button>
            <form action="{{ route('gel-secretary.invitations.accept', $invitation->id) }}" method="POST" style="margin: 0;">
              @csrf
              <button type="submit" class="btn-accept"><i class="fas fa-check me-1"></i> Accepter</button>
            </form>
          </div>
        </div>
      @empty
        <div style="text-align: center; padding: 64px 24px; background: white; border-radius: 12px; border: 1px dashed var(--sec-border);">
          <div style="font-size: 32px; color: #CBD5E1; margin-bottom: 16px;"><i class="fas fa-envelope-open"></i></div>
          <div style="font-weight: 600; font-size: 16px; color: var(--sec-text);">Aucune invitation en attente</div>
          <div style="font-size: 13px; color: var(--sec-text-muted); margin-top: 4px;">Vous n'avez pas de nouvelles demandes de rattachement.</div>
        </div>
      @endforelse
    </div>
  </div>

  <!-- REQUESTS TAB -->
  <div class="tab-pane fade" id="requests" role="tabpanel" aria-labelledby="requests-tab">
<div class="sec-kpi-grid">
  <div class="sec-kpi">
    <div class="sec-kpi-icon" style="background:#FEF2F2; color:#EF4444;"><i class="fas fa-bolt"></i></div>
    <div><div class="sec-kpi-val">{{ $stats['new'] }}</div><div class="sec-kpi-label">Nouvelles (urgentes)</div></div>
  </div>
  <div class="sec-kpi">
    <div class="sec-kpi-icon" style="background:#FFFBEB; color:#F59E0B;"><i class="fas fa-clock"></i></div>
    <div><div class="sec-kpi-val">{{ $stats['pending'] }}</div><div class="sec-kpi-label">En attente</div></div>
  </div>
  <div class="sec-kpi">
    <div class="sec-kpi-icon" style="background:#ECFDF5; color:#10B981;"><i class="fas fa-check-double"></i></div>
    <div><div class="sec-kpi-val">{{ $stats['processed'] }}</div><div class="sec-kpi-label">Traitées</div></div>
  </div>
</div>

{{-- Filtres --}}
<form method="GET" class="mb-3 d-flex gap-2" style="max-width:520px;">
  <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher entreprise, contact, e-mail…"
    class="sec-form-control" style="flex:1;">
  <select name="statut" class="sec-form-select" style="width:180px;">
    <option value="">Tous les statuts</option>
    <option value="new" @selected(request('statut')==='new')>Nouvelle</option>
    <option value="pending" @selected(request('statut')==='pending')>En attente</option>
    <option value="contacted" @selected(request('statut')==='contacted')>Contactée</option>
    <option value="processed" @selected(request('statut')==='processed')>Traitée</option>
    <option value="rejected" @selected(request('statut')==='rejected')>Rejetée</option>
  </select>
  <button class="sec-btn sec-btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
</form>

<div class="sec-card">
  <div class="sec-card-body p-0">
    @forelse($requests as $r)
      <div style="padding:14px 18px; border-bottom:1px solid var(--sec-border); display:flex; gap:14px; align-items:flex-start;">
        <div style="width:38px; height:38px; border-radius:8px; background:#F0FDFA; color:#0D9488; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0;">
          {{ strtoupper(substr($r->name ?? 'D', 0, 2)) }}
        </div>
        <div style="flex:1; min-width:0;">
          <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span style="font-weight:700; color:var(--sec-text);">{{ $r->name }}</span>
            <span class="req-badge req-{{ $r->status }}">{{ match($r->status) { 'new'=>'Nouvelle', 'pending'=>'En attente', 'contacted'=>'Contactée', 'processed'=>'Traitée', 'rejected'=>'Rejetée', default=>$r->status } }}</span>
          </div>
          <div style="font-size:12px; color:var(--sec-text-muted); margin-top:2px;">
            {{ $r->email }}
            @if($r->phone) · {{ $r->phone }} @endif
          </div>
          @if($r->title)
            <div style="font-weight:600; font-size:13px; color:var(--sec-text); margin-top:6px;">Sujet : {{ $r->title }}</div>
          @endif
          @if($r->description)
            <div style="font-size:13px; color:var(--sec-text); margin-top:6px; background:#F8FAFC; border-radius:6px; padding:8px 10px; border-left:3px solid var(--sec-primary);">
              {{ \Illuminate\Support\Str::limit($r->description, 240) }}
            </div>
          @endif
          @if($r->type)
            <div style="margin-top:6px;">
              <span class="req-badge req-pending" style="margin-right:4px;">{{ ucfirst($r->type) }}</span>
            </div>
          @endif
          <div style="font-size:11px; color:#94A3B8; margin-top:6px;">Reçue {{ \Carbon\Carbon::parse($r->created_at)->locale('fr')->diffForHumans() }}</div>

          <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
            <form method="POST" action="{{ route('gel-secretary.requests.to-task', $r->id) }}" style="margin:0;">
              @csrf
              <button class="sec-btn sec-btn-primary sec-btn-sm"><i class="fas fa-tasks"></i> Traiter comme tâche</button>
            </form>
            <form method="POST" action="{{ route('gel-secretary.requests.to-courrier', $r->id) }}" style="margin:0;">
              @csrf
              <button class="sec-btn sec-btn-secondary sec-btn-sm"><i class="fas fa-envelope-open-text"></i> Traiter comme courrier</button>
            </form>
            <form method="POST" action="{{ route('gel-secretary.requests.to-client', $r->id) }}" style="margin:0;">
              @csrf
              <button class="sec-btn sec-btn-secondary sec-btn-sm"><i class="fas fa-user-plus"></i> Créer Prospect/Client</button>
            </form>
            @if(in_array($r->status, ['new','pending']))
            <form method="POST" action="{{ route('gel-secretary.requests.status', $r->id) }}" style="margin:0;">
              @csrf
              <input type="hidden" name="status" value="contacted">
              <button class="sec-btn sec-btn-secondary sec-btn-sm"><i class="fas fa-phone-alt"></i> Marquer contactée</button>
            </form>
            @endif
            @if($r->status === 'contacted')
            <form method="POST" action="{{ route('gel-secretary.requests.status', $r->id) }}" style="margin:0;">
              @csrf
              <input type="hidden" name="status" value="processed">
              <button class="sec-btn sec-btn-secondary sec-btn-sm"><i class="fas fa-check"></i> Marquer traitée</button>
            </form>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="empty-state" style="padding:48px 24px;">
        <i class="fas fa-inbox" style="font-size:32px; color:#CBD5E1; display:block; margin-bottom:10px;"></i>
        Aucune demande client pour le moment.
      </div>
    @endforelse
  </div>
</div>

@if($requests->hasPages())
  <div class="mt-3">{{ $requests->links() }}</div>
@endif

  </div>
</div>

<!-- Modal Refus Invitation -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="rejectModalLabel">Refuser l'invitation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form id="rejectForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="text-muted small mb-3">Veuillez indiquer à l'entreprise la raison pour laquelle vous refusez cette invitation.</p>
          <div class="mb-3">
            <label for="motif_rejet" class="form-label fw-bold small">Motif du refus (optionnel)</label>
            <textarea name="motif_rejet" id="motif_rejet" rows="3" class="form-control" placeholder="Ex: Je ne travaille plus avec ce cabinet..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Confirmer le refus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openRejectModal(id) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    const form = document.getElementById('rejectForm');
    form.action = `/gel-secretary/invitations/${id}/reject`;
    modal.show();
  }
</script>
@endsection
