@extends('layouts.gel-direction')

@section('title', 'Équipe & Invitations')
@section('page_title', 'Équipe & Collaborateurs')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-md-8">
        <!-- Liste de l'équipe active -->
        <div class="sec-card shadow-sm border-0 mb-4" style="border-radius: 8px;">
            <div class="sec-card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="mb-0">Membres de l'équipe</h5>
            </div>
            <div class="sec-card-body">
                @if(isset($team) && $team->count() > 0)
                    <div class="table-responsive">
                        <table class="sec-table align-middle">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($team as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background:var(--sec-primary);">
                                                {{ strtoupper(substr($member->utilisateur->nom ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $member->utilisateur->nom ?? 'Utilisateur' }} {{ $member->utilisateur->prenom ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $member->utilisateur->email }}</td>
                                    <td><span class="sec-badge sec-badge-info">{{ $member->role->name ?? $member->role->code ?? 'N/A' }}</span></td>
                                    <td><span class="sec-badge sec-badge-success">Actif</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted">Aucun membre dans votre équipe pour le moment.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Liste des invitations en attente -->
        <div class="sec-card shadow-sm border-0" style="border-radius: 8px;">
            <div class="sec-card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="mb-0">Invitations en attente</h5>
            </div>
            <div class="sec-card-body">
                @if(isset($invitations) && $invitations->count() > 0)
                    <div class="table-responsive">
                        <table class="sec-table align-middle">
                            <thead>
                                <tr>
                                    <th>Email invité</th>
                                    <th>Rôle proposé</th>
                                    <th>Date d'invitation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invitations as $invitation)
                                <tr>
                                    <td>{{ $invitation->email }}</td>
                                    <td><span class="sec-badge sec-badge-muted">{{ $invitation->role_invite }}</span></td>
                                    <td>{{ $invitation->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('gel-direction.team.invitations.cancel', $invitation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="sec-btn sec-btn-sm" style="color:var(--sec-danger); background:transparent;" onclick="return confirm('Annuler cette invitation ?')">
                                                <i class="fas fa-times"></i> Annuler
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Aucune invitation en attente.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Formulaire d'invitation -->
        <div class="sec-card shadow-sm border-0" style="border-radius: 8px;">
            <div class="sec-card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="mb-0">Inviter un collaborateur</h5>
            </div>
            <div class="sec-card-body">
                <form action="{{ route('gel-direction.team.invite') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Adresse Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="email@exemple.com">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Rôle assigné <span class="text-danger">*</span></label>
                        <select name="role_invite" class="form-select" required>
                            <option value="">-- Choisir un rôle --</option>
                            <option value="comptable">Expert-Comptable</option>
                            <option value="secretaire">Secrétaire</option>
                            <option value="rh">Ressources Humaines</option>
                            <option value="legal">Juridique</option>
                        </select>
                    </div>
                    <button type="submit" class="sec-btn sec-btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i> Envoyer l'invitation
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
