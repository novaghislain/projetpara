<template>
    <GelLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex-wrap d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi-pen me-2"></i>Signatures électroniques</h5>
                <a :href="route('gel.document-signatures.create')" class="btn btn-primary btn-sm">
                    <i class="bi-plus-lg"></i> Nouvelle signature
                </a>
            </div>
            <div class="p-3">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" v-model="search" placeholder="Rechercher par nom ou email..." @input="debouncedSearch">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr>
                            <th>Document</th><th>Signataire</th><th>Email</th><th>Signé le</th><th>Actions</th>
                        </tr></thead>
                        <tbody>
                            <tr v-for="s in signatures.data" :key="s.id">
                                <td>{{ s.document?.title || 'N/A' }}</td>
                                <td>{{ s.signer_name }}</td>
                                <td>{{ s.signer_email }}</td>
                                <td>{{ s.signed_at ? new Date(s.signed_at).toLocaleDateString('fr-FR') : '—' }}</td>
                                <td>
                                    <a :href="`/signatures/${s.id}`" class="btn btn-primary btn-sm me-1"><i class="bi-eye"></i></a>
                                </td>
                            </tr>
                            <tr v-if="!signatures.data?.length"><td colspan="5" class="text-center text-muted py-4">Aucune signature.</td></tr>
                        </tbody>
                    </table>
                </div>
                <nav v-if="signatures.last_page > 1" class="mt-3">
                    <ul class="pagination pagination-sm mb-0">
                        <li v-for="p in signatures.last_page" :key="p" class="page-item" :class="{active:p===signatures.current_page}">
                            <a class="page-link" :href="`/signatures?page=${p}`">{{ p }}</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
import { ref } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
defineProps({ signatures: { type: Object, default: () => ({ data: [], current_page: 1, last_page: 1 }) } })
const search = ref('')
let timer; const debouncedSearch = () => { clearTimeout(timer); timer = setTimeout(() => { window.location = `/signatures?search=${search.value}` }, 400) }
</script>
