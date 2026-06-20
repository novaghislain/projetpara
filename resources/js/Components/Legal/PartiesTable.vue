<template>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Rôle</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th v-if="editable" class="text-center" style="width:60px">
                        <button class="btn btn-sm btn-outline-success" @click="$emit('add')">
                            <i class="bi bi-plus"></i>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(partie, i) in parties" :key="i">
                    <td>{{ partie.nom }}</td>
                    <td>{{ partie.role }}</td>
                    <td>{{ partie.email || '—' }}</td>
                    <td>{{ partie.telephone || '—' }}</td>
                    <td v-if="editable" class="text-center">
                        <button class="btn btn-sm btn-outline-danger" @click="$emit('remove', i)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr v-if="!parties.length">
                    <td :colspan="editable ? 5 : 4" class="text-center text-muted py-3">
                        Aucune partie
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    parties: { type: Array, default: () => [] },
    editable: { type: Boolean, default: false },
});

defineEmits(['add', 'remove']);
</script>
