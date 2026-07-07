@extends('layouts.gel')

@section('title', 'Utilisateurs — GEL Cabinet')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Utilisateurs</h1>
        <p class="page-subtitle">Gérez les utilisateurs du cabinet et leurs rôles.</p>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-header">Liste des utilisateurs</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôles</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <tr><td colspan="6" class="text-center py-4">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function loadUsers() {
        fetch('/api/gel/admin/users', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('users-tbody');
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--gel-text-muted);">Aucun utilisateur</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(u => `
                <tr>
                    <td><strong>${u.name}</strong></td>
                    <td>${u.email}</td>
                    <td>${(u.roles || []).map(r => `<span class="badge bg-light text-dark me-1">${r}</span>`).join('')}</td>
                    <td><span class="badge ${u.is_active ? 'bg-success' : 'bg-secondary'}">${u.is_active ? 'Actif' : 'Inactif'}</span></td>
                    <td><small style="color:var(--gel-text-muted);">${u.last_login ? new Date(u.last_login).toLocaleDateString('fr-FR') : '-'}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="assignRoles(${u.id}, '${u.name}')"><i class="bi bi-shield"></i></button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(() => {
            document.getElementById('users-tbody').innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--gel-text-muted);">Erreur de chargement</td></tr>';
        });
    }

    function assignRoles(userId, userName) {
        const roles = prompt(`Assigner des rôles à ${userName} (séparés par des virgules) :\nRôles disponibles: super_admin, gestionnaire_cabinet, comptable_senior, chef_comptable, comptable_junior, agent_paie, agent_client, stagiaire, auditeur`);
        if (!roles) return;
        const roleArray = roles.split(',').map(r => r.trim());
        fetch(`/api/gel/admin/users/${userId}/roles`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ roles: roleArray })
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message || 'Rôles mis à jour');
            loadUsers();
        })
        .catch(err => alert('Erreur: ' + err));
    }

    document.addEventListener('DOMContentLoaded', loadUsers);
</script>
@endsection
