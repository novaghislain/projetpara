<template>
    <CompanyLayout pageTitle="Exercices comptables">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h4 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2"></i>Exercices comptables</h4>
                <button class="btn isup-btn-primary" @click="openCreateModal"><i class="bi bi-plus-lg me-1"></i>Nouvel exercice</button>
            </div>
            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
            <div v-else class="isup-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table isup-table mb-0">
                        <thead>
                            <tr><th>Libellé</th><th>Début</th><th>Fin</th><th>Statut</th><th>Journaux</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="exercices.length === 0"><td colspan="6" class="text-center py-4 text-muted">Aucun exercice</td></tr>
                            <tr v-for="e in exercices" :key="e.id">
                                <td class="fw-semibold">{{ e.libelle || e.year || e.label }}</td>
                                <td>{{ formatDate(e.date_start || e.date_debut) }}</td>
                                <td>{{ formatDate(e.date_end || e.date_fin) }}</td>
                                <td>
                                    <span class="badge" :class="e.is_clos || e.status === 'closed' ? 'bg-dark' : e.status === 'open' || !e.is_clos ? 'bg-success' : 'bg-secondary'">
                                        {{ e.is_clos || e.status === 'closed' ? 'Clôturé' : e.status === 'open' || !e.is_clos ? 'Ouvert' : e.status }}
                                    </span>
                                </td>
                                <td>{{ e.journals_count || e.journaux_count || 0 }}</td>
                                <td class="text-end">
                                    <button v-if="!(e.is_clos || e.status === 'closed')" class="btn btn-sm isup-btn-ghost text-warning" @click="closeExercice(e)"><i class="bi bi-lock"></i> Clôturer</button>
                                    <button v-else class="btn btn-sm isup-btn-ghost" @click="reopenExercice(e)"><i class="bi bi-unlock"></i> Rouvrir</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue'
const exercices = ref([]), loading = ref(true), error = ref(null)
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
const api = (p, o = {}) => fetch(p, { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, ...o.headers }, ...o })
const load = async () => { loading.value = true; try { const r = await api('/api/company/fiscal-years'); if (r.ok) exercices.value = await r.json() } catch (e) { error.value = e.message } finally { loading.value = false } }
const openCreateModal = () => {}
const closeExercice = async (e) => { if (!confirm('Clôturer cet exercice ?')) return; await api(`/api/company/fiscal-years/${e.id}/close`, { method: 'POST' }); load() }
const reopenExercice = async (e) => { if (!confirm('Rouvrir cet exercice ?')) return; await api(`/api/company/fiscal-years/${e.id}/reopen`, { method: 'POST' }); load() }
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
onMounted(load)
</script>
