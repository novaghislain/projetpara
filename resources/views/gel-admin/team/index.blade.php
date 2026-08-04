@extends('layouts.gel-admin')

@section('title', 'Membres de l\'équipe')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Membres de l'équipe</h1>
        <p class="admin-page-sub">Gérez les accès de vos collaborateurs (Secrétaires, Comptables).</p>
    </div>
    <a href="{{ route('gel-admin.team.invitations.index') }}" class="admin-btn admin-btn-primary">
        <i class="fas fa-user-plus"></i> Inviter un membre
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">Utilisateurs actifs</div>
    </div>
    <div class="admin-card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 px-4 py-3">Utilisateur</th>
                    <th class="border-0 px-4 py-3">Type / Rôle</th>
                    <th class="border-0 px-4 py-3">Statut</th>
                    <th class="border-0 px-4 py-3">Dernière connexion</th>
                    <th class="border-0 px-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teamMembers as $member)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="admin-avatar" style="width:32px; height:32px; font-size:11px;">
                                    {{ strtoupper(substr($member->name ?? $member->email, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-medium text-dark">{{ $member->name ?? 'Sans nom' }}</div>
                                    <div class="small text-muted">{{ $member->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4">
                            @if($member->is_company_admin)
                                <span class="badge bg-primary">Propriétaire</span>
                            @else
                                <span class="badge bg-secondary">{{ $member->account_type == 'cabinet_secretary' ? 'Secrétaire' : ($member->account_type == 'cabinet_accountant' ? 'Comptable' : 'Collaborateur') }}</span>
                            @endif
                        </td>
                        <td class="px-4">
                            @if($member->is_suspended)
                                <span class="badge bg-warning text-dark"><i class="fas fa-pause-circle me-1"></i>Suspendu</span>
                            @elseif(!$member->is_active)
                                <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Révoqué</span>
                            @else
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Actif</span>
                            @endif
                        </td>
                        <td class="px-4 text-muted small">
                            {{ $member->last_login_at ? \Carbon\Carbon::parse($member->last_login_at)->diffForHumans() : 'Jamais' }}
                        </td>
                        <td class="px-4 text-end">
                            @if($member->id !== auth()->id())
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <form action="{{ route('gel-admin.team.suspend', $member->id) }}" method="POST">
                                                @csrf
                                                <button class="dropdown-item text-warning" onclick="return confirm('Suspendre temporairement cet utilisateur ?');">
                                                    <i class="fas fa-pause-circle me-2"></i> Suspendre
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('gel-admin.team.revoke', $member->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger" onclick="return confirm('Révoquer définitivement l\'accès de cet utilisateur ?');">
                                                    <i class="fas fa-trash me-2"></i> Révoquer l'accès
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
