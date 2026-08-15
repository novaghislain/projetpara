@extends('layouts.gel-super-admin')

@section('title', 'Utilisateurs Globaux')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: #111827;">Utilisateurs Globaux</h1>
        <div style="color: #6B7280; font-size: 14px;">Tous les comptes utilisateurs de la plateforme</div>
    </div>
    <button class="btn text-white" style="background: var(--sa-primary); border: none;">
        <i class="fas fa-user-plus"></i> Forcer un compte
    </button>
</div>

<div class="sa-card">
    <div class="sa-card-body p-0">
        <table class="table mb-0 table-hover" style="font-size: 14px;">
            <thead style="background: #F9FAFB;">
                <tr>
                    <th class="border-0 px-4 py-3 text-muted">Nom Complet</th>
                    <th class="border-0 px-4 py-3 text-muted">Email</th>
                    <th class="border-0 px-4 py-3 text-muted">Affectations / Rôles</th>
                    <th class="border-0 px-4 py-3 text-muted">Statut Global</th>
                    <th class="border-0 px-4 py-3 text-muted text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="px-4 py-3 align-middle">
                        <strong>{{ $user->nom }}</strong>
                    </td>
                    <td class="px-4 py-3 align-middle text-muted">{{ $user->email }}</td>
                    <td class="px-4 py-3 align-middle">
                        @if($user->affectations->count() > 0)
                            @foreach($user->affectations as $aff)
                                <div style="font-size: 12px; margin-bottom: 2px;">
                                    <span class="badge bg-light text-dark border">{{ $aff->entreprise->raison_sociale ?? 'GEL Cabinet' }}</span>
                                    <span class="badge bg-secondary">{{ $aff->role->libelle ?? 'Rôle Inconnu' }}</span>
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted" style="font-size: 12px; font-style: italic;">Aucune affectation</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span class="badge {{ $user->statut == 'actif' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($user->statut) }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle text-end">
                        <button class="btn btn-sm btn-light border"><i class="fas fa-edit text-muted"></i></button>
                        <button class="btn btn-sm btn-light border"><i class="fas fa-ban text-danger"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
