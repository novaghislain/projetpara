<template>
    <CompanyLayout pageTitle="Immobilisations">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h4 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>Immobilisations</h4>
                <button class="btn isup-btn-primary" @click="openCreateModal"><i class="bi bi-plus-lg me-1"></i>Nouveau bien</button>
            </div>
            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
            <div v-else class="isup-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table isup-table mb-0">
                        <thead>
                            <tr><th>Désignation</th><th>Catégorie</th><th>Date acquisition</th><th class="text-end">Valeur</th><th class="text-end">Amort.</th><th class="text-end">VNC</th><th>Statut</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="assets.length === 0"><td colspan="8" class="text-center py-4 text-muted">Aucune immobilisation</td></tr>
                            <tr v-for="a in assets" :key="a.id">
                                <td class="fw-semibold">{{ a.name || a.designation }}</td>
                                <td>{{ a.category || a.categorie || '—' }}</td>
                                <td>{{ formatDate(a.acquisition_date || a.date_acquisition) }}</td>
                                <td class="text-end">{{ formatCurrency(a.acquisition_cost || a.valeur_acquisition || 0) }}</td>
                                <td class="text-end">{{ formatCurrency(a.accumulated_depreciation || a.amortissement_cumule || 0) }}</td>
                                <td class="text-end fw-semibold">{{ formatCurrency((a.acquisition_cost || a.valeur_acquisition || 0) - (a.accumulated_depreciation || a.amortissement_cumule || 0)) }}</td>
                                <td><span :class="'badge ' + (a.status === 'active' || a.statut === 'actif' ? 'bg-success' : 'bg-secondary')">{{ a.status || a.statut }}</span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm isup-btn-ghost" @click="editAsset(a)"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm isup-btn-ghost text-danger" @click="deleteAsset(a)"><i class="bi bi-trash"></i></button>
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
const assets = ref([]), loading = ref(true), error = ref(null)
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
const api = (p, o = {}) => fetch(p, { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, ...o.headers }, ...o })
const load = async () => { loading.value = true; try { const r = await api('/api/company/fixed-assets'); if (r.ok) assets.value = await r.json() } catch (e) { error.value = e.message } finally { loading.value = false } }
const openCreateModal = () => {}
const editAsset = (a) => {}
const deleteAsset = async (a) => { if (!confirm('Supprimer ?')) return; await api(`/api/company/fixed-assets/${a.id}`, { method: 'DELETE' }); load() }
const formatCurrency = (v) => v || v === 0 ? new Intl.NumberFormat('fr-FR').format(v) + ' FCFA' : '0'
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
onMounted(load)
</script>
