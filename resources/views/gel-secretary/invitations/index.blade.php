@extends('layouts.gel-secretary')
@section('title', 'Invitations - Secrétariat')

@section('content')
<style>
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

<div class="pro-header animate-fade">
  <div>
    <div class="pro-title">Mes Invitations</div>
    <div class="pro-subtitle">Acceptez ou refusez les invitations à rejoindre une entreprise</div>
  </div>
</div>

<div class="animate-fade delay-1" style="max-width: 800px; margin-top: 24px;">
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

<!-- Modal Refus -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function openRejectModal(id) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    const form = document.getElementById('rejectForm');
    form.action = `/gel-secretary/invitations/${id}/reject`;
    modal.show();
  }
</script>
@endsection
