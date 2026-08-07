@extends('layouts.gel-accountant')
@section('title', 'Approbations en attente')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-check-double" style="color:var(--gel-primary); margin-right:8px;"></i> Approbations en attente</h1>
        <p class="gel-page-subtitle">Passez en revue les opérations sensibles nécessitant votre validation.</p>
    </div>
</div>

<div class="gel-card" style="overflow:hidden;">
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead style="background:var(--gel-sidebar-bg); color:white;">
            <tr>
                <th style="padding:10px 14px; text-align:left;">Date</th>
                <th style="padding:10px 14px; text-align:left;">Type de demande</th>
                <th style="padding:10px 14px; text-align:left;">Demandeur</th>
                <th style="padding:10px 14px; text-align:center;">Étape</th>
                <th style="padding:10px 14px; text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingRequests as $req)
            <tr style="border-bottom:1px solid var(--gel-border);">
                <td style="padding:10px 14px; font-weight:600; color:var(--gel-text-muted);">
                    {{ $req->created_at->format('d/m/Y H:i') }}
                </td>
                <td style="padding:10px 14px; font-weight:700; color:#0f172a;">
                    {{ $req->workflow->name ?? 'Demande #' . $req->id }}
                    <div style="font-size:11px; color:var(--gel-text-muted); font-weight:normal; margin-top:4px;">
                        Référence : {{ $req->model_type }} ID: {{ $req->model_id }}
                    </div>
                </td>
                <td style="padding:10px 14px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:24px; height:24px; border-radius:50%; background:var(--gel-primary); color:white; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">
                            {{ substr($req->requester->name ?? 'U', 0, 1) }}
                        </div>
                        <span>{{ $req->requester->name ?? 'Utilisateur' }}</span>
                    </div>
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    <span style="font-size:11px; padding:2px 8px; border-radius:12px; background:rgba(245,158,11,0.1); color:#f59e0b; font-weight:bold;">
                        Étape {{ $req->current_step + 1 }}
                    </span>
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    <button onclick="openApprovalModal({{ $req->id }}, '{{ addslashes($req->workflow->name ?? 'Demande') }}')" class="gel-btn gel-btn-primary" style="font-size:11px; padding:4px 12px; border-radius:6px;">
                        Traiter
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px; text-align:center; color:var(--gel-text-muted);">
                    <i class="fas fa-check-circle" style="font-size:32px; color:#10b981; margin-bottom:12px; display:block;"></i>
                    Aucune demande d'approbation en attente.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:12px 16px;">
        {{ $pendingRequests->links() }}
    </div>
</div>

{{-- Modale d'approbation --}}
<div class="gel-panel-overlay" id="approvalOverlay" onclick="closeApprovalModal()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:998;"></div>
<div class="gel-panel" id="approvalModal" style="display:none; position:fixed; right:0; top:0; bottom:0; width:400px; background:white; z-index:999; box-shadow:-5px 0 25px rgba(0,0,0,0.1); flex-direction:column;">
    <div style="padding:20px; border-bottom:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center;">
        <span style="font-weight:700; font-size:16px;" id="approvalModalTitle">Traiter la demande</span>
        <button onclick="closeApprovalModal()" style="background:none; border:none; cursor:pointer; font-size:20px; color:#64748b;">&times;</button>
    </div>
    <div style="padding:20px; flex:1;">
        <form id="approveForm" method="POST" action="">
            @csrf
            <div class="gel-form-group">
                <label>Commentaire d'approbation (optionnel)</label>
                <textarea name="comment" class="gel-form-control" rows="3" placeholder="RAS..."></textarea>
            </div>
            <button type="submit" class="gel-btn gel-btn-primary" style="width:100%; background:#10b981; border-color:#10b981;">
                <i class="fas fa-check"></i> Approuver
            </button>
        </form>

        <hr style="margin:20px 0; border:0; border-top:1px solid var(--gel-border);">

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="gel-form-group">
                <label>Motif du rejet (obligatoire)</label>
                <textarea name="comment" class="gel-form-control" rows="3" placeholder="Montant trop élevé..." required></textarea>
            </div>
            <button type="submit" class="gel-btn gel-btn-secondary" style="width:100%; color:#ef4444; border-color:#ef4444; background:rgba(239,68,68,0.05);">
                <i class="fas fa-times"></i> Rejeter
            </button>
        </form>
    </div>
</div>

<script>
function openApprovalModal(id, title) {
    document.getElementById('approvalModalTitle').innerText = title;
    document.getElementById('approveForm').action = `/gel-accountant/workflows/approvals/${id}/approve`;
    document.getElementById('rejectForm').action = `/gel-accountant/workflows/approvals/${id}/reject`;
    
    document.getElementById('approvalOverlay').style.display = 'block';
    document.getElementById('approvalModal').style.display = 'flex';
}

function closeApprovalModal() {
    document.getElementById('approvalOverlay').style.display = 'none';
    document.getElementById('approvalModal').style.display = 'none';
}
</script>

@endsection
