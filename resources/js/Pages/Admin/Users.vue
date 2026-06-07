<script setup>
import { ref } from 'vue';
import { 
    Search, 
    UserPlus, 
    Mail, 
    Phone, 
    Calendar,
    MoreVertical,
    Shield
} from 'lucide-vue-next';

const users = ref([
    { id: 1, name: 'Admin Victoire', email: 'admin@victoire.com', role: 'Administrateur', joined: '2024-01-10', status: 'active' },
    { id: 2, name: 'Jean Dupont', email: 'jean.dupont@email.com', role: 'Client', joined: '2024-03-12', status: 'active' },
    { id: 3, name: 'Marie Sossa', email: 'marie.sossa@email.com', role: 'Client', joined: '2024-03-14', status: 'active' },
]);

const getRoleBadge = (role) => {
    return role === 'Administrateur' ? 'bg-primary' : 'bg-secondary';
};
</script>

<template>
    <AdminLayout>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gestion des Utilisateurs</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb small mb-0">
                        <li class="breadcrumb-item"><a href="/admin" class="text-muted">Admin</a></li>
                        <li class="breadcrumb-item active text-primary">Utilisateurs</li>
                    </ol>
                </nav>
            </div>
            <button class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2">
                <UserPlus :size="18" /> Ajouter un utilisateur
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase ls-1">
                            <th class="p-4 border-0">Utilisateur</th>
                            <th class="p-4 border-0">Rôle</th>
                            <th class="p-4 border-0">Date d'inscription</th>
                            <th class="p-4 border-0">Statut</th>
                            <th class="p-4 border-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-bottom border-light">
                            <td class="p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ user.name }}</div>
                                        <div class="text-muted small">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="badge rounded-pill px-3 py-2" :class="getRoleBadge(user.role)">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="p-4 text-muted small">
                                <div class="d-flex align-items-center gap-2">
                                    <Calendar :size="14" /> {{ user.joined }}
                                </div>
                            </td>
                            <td class="p-4 text-success small fw-bold">Actif</td>
                            <td class="p-4 text-end">
                                <button class="btn btn-light btn-sm rounded-circle p-2">
                                    <MoreVertical :size="18" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
