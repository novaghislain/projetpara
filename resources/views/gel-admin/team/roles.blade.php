@extends('layouts.gel-admin')

@section('title', 'Matrice des Rôles')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Matrice des Rôles</h1>
        <p class="admin-page-sub">Gérez les rôles et les permissions personnalisés de vos collaborateurs.</p>
    </div>
    <a href="{{ route('gel-admin.team.roles.create') }}" class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i> Créer un rôle
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">Rôles configurés</div>
    </div>
    <div class="admin-card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 px-4 py-3">Nom du rôle</th>
                    <th class="border-0 px-4 py-3">Permissions liées</th>
                    <th class="border-0 px-4 py-3">Membres assignés</th>
                    <th class="border-0 px-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td class="px-4 fw-bold text-dark">{{ $role->name }}</td>
                        <td class="px-4">
                            <span class="badge bg-secondary">{{ $role->permissions->count() }} permissions</span>
                        </td>
                        <td class="px-4 text-muted">
                            {{ $role->users->count() }}
                        </td>
                        <td class="px-4 text-end">
                            <a href="{{ route('gel-admin.team.roles.edit', $role->id) }}" class="btn btn-sm btn-light text-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('gel-admin.team.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce rôle ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Aucun rôle configuré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
