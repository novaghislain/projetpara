@extends('layouts.gel-admin')

@section('title', 'Invitations')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Invitations en attente</h1>
        <p class="admin-page-sub">Gérez les invitations envoyées pour rejoindre votre cabinet.</p>
    </div>
    <button class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#inviteModal">
        <i class="fas fa-paper-plane"></i> Envoyer une invitation
    </button>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">Invitations envoyées</div>
    </div>
    <div class="admin-card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 px-4 py-3">Email</th>
                    <th class="border-0 px-4 py-3">Rôle proposé</th>
                    <th class="border-0 px-4 py-3">Date d'envoi</th>
                    <th class="border-0 px-4 py-3">Statut</th>
                    <th class="border-0 px-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invitations as $inv)
                    <tr>
                        <td class="px-4 fw-medium text-dark">{{ $inv->email }}</td>
                        <td class="px-4">
                            @php
                                $roleName = 'Membre';
                                if($inv->role_id) {
                                    $role = \Spatie\Permission\Models\Role::find($inv->role_id);
                                    if($role) $roleName = $role->name;
                                }
                            @endphp
                            <span class="badge bg-secondary">{{ $roleName }}</span>
                        </td>
                        <td class="px-4 text-muted">{{ $inv->created_at->format('d/m/Y') }}</td>
                        <td class="px-4">
                            @if($inv->expires_at < now())
                                <span class="badge bg-danger">Expirée</span>
                            @else
                                <span class="badge bg-warning text-dark">En attente</span>
                            @endif
                        </td>
                        <td class="px-4 text-end">
                            <form action="{{ route('gel-admin.team.invitations.cancel', $inv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler cette invitation ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger"><i class="fas fa-times me-1"></i> Annuler</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Aucune invitation en attente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Inviter -->
<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('gel-admin.team.invitations.send') }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Inviter un collaborateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Adresse Email</label>
                        <input type="email" name="email" class="form-control" placeholder="collaborateur@exemple.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="role_id" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Si vous n'avez pas de rôles, créez-en un dans 'Matrice des rôles'.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Envoyer l'invitation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
