@php $currentSection = 'clients'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Invitations - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Invitations</h1>
        <p class="gel-page-subtitle">Gérez les invitations envoyées À  vos clients</p>
    </div>
    <a href="{{ route('gel-accountant.clients') }}" class="gel-btn gel-btn-secondary gel-btn-sm">
        <i class="fas fa-arrow-left"></i> Retour aux clients
    </a>
</div>

<div class="gel-card p-4 mb-4">
    <div class="gel-card-body p-4 mb-4">
        @if(isset($invitations) && $invitations->count() > 0)
            <div class="table-responsive">
                <table class="table gel-table">
                    <thead>
                        <tr>
                            <th>Entreprise</th>
                            <th>Email Émetteur</th>
                            <th>Rôle Proposé</th>
                            <th>Message</th>
                            <th>Date d'expiration</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invitations as $invitation)
                            <tr>
                                <td>{{ $invitation->entreprise->nom ?? 'Entreprise' }}</td>
                                <td>{{ $invitation->email }}</td>
                                <td><span class="gel-badge gel-badge-info">{{ ucfirst($invitation->role_invite) }}</span></td>
                                <td>{{ $invitation->message ?? '-' }}</td>
                                <td>{{ $invitation->expire_at->format('d/m/Y') }}</td>
                                <td>
                                    <form action="{{ route('gel-accountant.invitations.accept', $invitation->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm" style="margin-right: 4px;">
                                            <i class="fas fa-check"></i> Accepter
                                        </button>
                                    </form>
                                    <button type="button" class="gel-btn gel-btn-danger gel-btn-sm" onclick="openRejectModal({{ $invitation->id }})">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="gel-empty" style="padding:60px 40px;">
                <i class="fas fa-envelope-open-text" style="font-size:48px;color:var(--gel-text-muted);margin-bottom:16px;display:block;"></i>
                <h3>Aucune invitation en attente</h3>
                <p>Les invitations envoyées par les entreprises apparaîtront ici.</p>
            </div>
        @endif
    </div>
</div>

{{-- Modal Rejeter --}}
<div id="rejectModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div class="gel-card" style="width:100%;max-width:500px;padding:24px;">
        <h3 style="margin-top:0;margin-bottom:16px;">Rejeter l'invitation</h3>
        <p style="color:var(--gel-text-secondary);margin-bottom:16px;">Veuillez indiquer le motif du rejet. Ce motif sera envoyé à l'administrateur de l'entreprise.</p>
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="gel-form-group">
                <label>Motif du rejet *</label>
                <textarea name="motif" class="gel-form-control" rows="4" required></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="closeRejectModal()">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-danger">Confirmer le rejet</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id) {
        var modal = document.getElementById('rejectModal');
        var form = document.getElementById('rejectForm');
        form.action = '/gel-accountant/invitations/' + id + '/reject';
        modal.style.display = 'flex';
    }

    function closeRejectModal() {
        var modal = document.getElementById('rejectModal');
        modal.style.display = 'none';
    }
</script>
@endsection

