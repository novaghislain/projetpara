@extends('layouts.gel')

@section('title', 'Rôles & Permissions — GEL Cabinet')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Rôles & Permissions</h1>
        <p class="page-subtitle">Gérez les rôles et les permissions des utilisateurs.</p>
    </div>
    <button class="btn btn-primary" onclick="createRole()" style="background:linear-gradient(135deg, var(--gel-accent-2), var(--gel-accent));border:none;">
        <i class="bi bi-plus-lg"></i> Nouveau rôle
    </button>
</div>

<div class="card-dashboard mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Rôles</span>
        <span class="badge" style="background:var(--gel-accent-2);" id="roles-count">0</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="roles-table">
                <thead>
                    <tr>
                        <th>Rôle</th>
                        <th>Description</th>
                        <th>Portail</th>
                        <th>Niveau</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="roles-tbody">
                    <tr><td colspan="6" class="text-center py-4">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal pour créer/modifier un rôle --}}
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--gel-border);">
                <h5 class="modal-title fw-bold" id="roleModalTitle">Nouveau rôle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" id="roleId">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw500">Nom technique</label>
                            <input type="text" class="form-control" id="roleName" placeholder="ex: superviseur" required pattern="^[a-z_]+$">
                            <div class="form-text">Lettres minuscules et underscores uniquement.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw500">Label (affiché)</label>
                            <input type="text" class="form-control" id="roleLabel" placeholder="ex: Superviseur" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw500">Portail</label>
                            <select class="form-select" id="rolePortail">
                                <option value="gel">GEL (Cabinet)</option>
                                <option value="entreprise">Entreprise</option>
                                <option value="cpa">CPA (Particulier)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw500">Niveau</label>
                            <select class="form-select" id="roleLevel">
                                <option value="0">0 - Super Admin</option>
                                <option value="1">1 - Gestionnaire</option>
                                <option value="2">2 - Senior</option>
                                <option value="3" selected>3 - Junior</option>
                                <option value="4">4 - Stagiaire</option>
                                <option value="5">5 - Auditeur</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw500">Permissions</label>
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="showPermissionSelector()">
                                <i class="bi bi-shield-check"></i> Sélectionner
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw500">Description</label>
                            <textarea class="form-control" id="roleDescription" rows="2" placeholder="Description du rôle..."></textarea>
                        </div>
                    </div>
                    <div id="selected-permissions" class="d-flex flex-wrap gap-1 mb-3"></div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--gel-border);">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveRole()" style="background:var(--gel-accent-2);border:none;">Enregistrer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let selectedPermissions = [];
    let allPermissions = [];
    let rolesData = [];

    function loadRoles() {
        fetch('/api/gel/admin/roles', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            rolesData = data;
            document.getElementById('roles-count').textContent = data.length;
            const tbody = document.getElementById('roles-tbody');
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--gel-text-muted);">Aucun rôle trouvé</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(role => `
                <tr>
                    <td><strong>${role.label_fr || role.name}</strong><br><small style="color:var(--gel-text-muted);">${role.name}</small></td>
                    <td><small>${role.description || '-'}</small></td>
                    <td><span class="badge" style="background:${role.portail === 'gel' ? '#635bff' : role.portail === 'entreprise' ? '#0ca678' : '#e67700'};">${role.portail || '-'}</span></td>
                    <td>${role.level ?? '-'}</td>
                    <td><span class="badge bg-light text-dark">${role.users_count ?? 0} utilisateurs</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" onclick="editRole('${role.name}')"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteRole('${role.name}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(() => {
            document.getElementById('roles-tbody').innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--gel-text-muted);">Erreur de chargement</td></tr>';
        });
    }

    function loadPermissions() {
        fetch('/api/gel/admin/permissions', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { allPermissions = data; })
        .catch(() => {});
    }

    function createRole() {
        document.getElementById('roleId').value = '';
        document.getElementById('roleName').value = '';
        document.getElementById('roleLabel').value = '';
        document.getElementById('roleDescription').value = '';
        document.getElementById('rolePortail').value = 'gel';
        document.getElementById('roleLevel').value = '3';
        selectedPermissions = [];
        updatePermissionsUI();
        document.getElementById('roleModalTitle').textContent = 'Nouveau rôle';
        new bootstrap.Modal(document.getElementById('roleModal')).show();
    }

    function editRole(roleName) {
        const role = rolesData.find(r => r.name === roleName);
        if (!role) return;
        document.getElementById('roleId').value = role.name;
        document.getElementById('roleName').value = role.name;
        document.getElementById('roleLabel').value = role.label_fr || '';
        document.getElementById('roleDescription').value = role.description || '';
        document.getElementById('rolePortail').value = role.portail || 'gel';
        document.getElementById('roleLevel').value = role.level ?? '3';
        document.getElementById('roleModalTitle').textContent = 'Modifier le rôle';
        new bootstrap.Modal(document.getElementById('roleModal')).show();
    }

    function saveRole() {
        const roleId = document.getElementById('roleId').value;
        const data = {
            name: document.getElementById('roleName').value,
            label_fr: document.getElementById('roleLabel').value,
            description: document.getElementById('roleDescription').value,
            portail: document.getElementById('rolePortail').value,
            level: parseInt(document.getElementById('roleLevel').value),
        };
        if (!data.name || !data.label_fr) { alert('Veuillez remplir les champs obligatoires.'); return; }
        const method = roleId ? 'PUT' : 'POST';
        const url = roleId ? `/api/gel/admin/roles/${roleId}/permissions` : '/api/gel/admin/roles';
        if (selectedPermissions.length) data.permissions = selectedPermissions;
        fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            loadRoles();
        })
        .catch(err => alert('Erreur: ' + err));
    }

    function deleteRole(name) {
        if (!confirm(`Supprimer le rôle "${name}" ?`)) return;
        fetch(`/api/gel/admin/roles/${name}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => { if (r.ok) loadRoles(); else r.json().then(d => alert(d.message)); })
        .catch(err => alert('Erreur: ' + err));
    }

    function showPermissionSelector() {
        // Simple permission picker via prompt
        const perms = allPermissions.map(p => `${p.name} (${p.module})`).join('\n');
        alert('Permissions disponibles:\n\n' + perms + '\n\nUtilisez l\'API pour assigner les permissions.');
    }

    function updatePermissionsUI() {
        const container = document.getElementById('selected-permissions');
        container.innerHTML = selectedPermissions.map(p => `<span class="badge" style="background:var(--gel-accent-2);">${p} <i class="bi bi-x ms-1" style="cursor:pointer;" onclick="removePermission('${p}')"></i></span>`).join('');
    }

    function removePermission(name) {
        selectedPermissions = selectedPermissions.filter(p => p !== name);
        updatePermissionsUI();
    }

    document.addEventListener('DOMContentLoaded', () => { loadRoles(); loadPermissions(); });
</script>
@endsection
