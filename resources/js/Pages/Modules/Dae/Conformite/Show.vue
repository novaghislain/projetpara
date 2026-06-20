<template>
    <div class="dae-conformite-show">
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement...</p>
        </div>
        <div v-else-if="!item" class="dae-loading">
            <i class="bi bi-shield-exclamation text-muted" style="font-size:3rem;"></i>
            <p class="mt-3 text-muted">Élément introuvable</p>
            <a href="/dae/conformite" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
        </div>
        <div v-else class="dae-content">
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/dae/conformite" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
                <div>
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-shield-check me-2 text-primary"></i>{{ item.titre }}
                    </h4>
                    <p class="text-muted small mb-0 mt-1">
                        <span class="badge" :class="statutBadge(item.statut)">{{ statutLabel(item.statut) }}</span>
                        <span class="ms-2">{{ typeLabel(item.type_conformite) }}</span>
                        <span class="ms-2" v-if="item.autorite_competente">· {{ item.autorite_competente }}</span>
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Main info -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 px-4">
                            <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Détails</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12" v-if="item.description">
                                    <label class="text-muted small mb-0">Description</label>
                                    <p class="mb-0">{{ item.description }}</p>
                                </div>
                                <div class="col-12" v-if="item.exigence_reglementaire">
                                    <label class="text-muted small mb-0">Exigence réglementaire</label>
                                    <p class="mb-0">{{ item.exigence_reglementaire }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Client</label>
                                    <p class="mb-0">{{ item.client?.nom || item.client?.raison_sociale || item.client?.name || '#' + item.client_id }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Type</label>
                                    <p class="mb-0">{{ typeLabel(item.type_conformite) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Autorité compétente</label>
                                    <p class="mb-0">{{ item.autorite_competente || '—' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Notes</label>
                                    <p class="mb-0">{{ item.notes || '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-12 col-lg-4">
                    <!-- Statut card -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-shield me-2 text-primary"></i>Statut</h6>
                            <p class="mb-1">
                                <span class="badge" :class="statutBadge(item.statut)" style="font-size:0.9rem;">
                                    {{ statutLabel(item.statut) }}
                                </span>
                            </p>
                            <!-- Quick verify -->
                            <div v-if="item.statut !== 'valide'" class="mt-3">
                                <label class="small text-muted mb-1">Marquer comme</label>
                                <select v-model="verifyStatut" class="form-select form-select-sm mb-2">
                                    <option value="a_faire">À faire</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="valide">Valide</option>
                                    <option value="non_conforme">Non conforme</option>
                                    <option value="expire">Expiré</option>
                                </select>
                                <button class="btn btn-sm btn-primary w-100" @click="submitVerification" :disabled="verifyLoading">
                                    <span v-if="verifyLoading" class="spinner-border spinner-border-sm me-1"></span>
                                    <span v-else><i class="bi bi-check2 me-1"></i>Valider le statut</span>
                                </button>
                            </div>
                            <p v-else class="text-muted small mt-3 mb-0">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                Vérifié{{ item.verified_by ? ' par #' + item.verified_by : '' }}
                                {{ item.verified_at ? formatDate(item.verified_at) : '' }}
                            </p>
                        </div>
                    </div>

                    <!-- Dates card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-calendar me-2 text-primary"></i>Dates</h6>
                            <div class="mb-2"><label class="text-muted small mb-0">Soumission</label><p class="mb-0">{{ formatDate(item.date_soumission) || '—' }}</p></div>
                            <div class="mb-2"><label class="text-muted small mb-0">Expiration</label><p class="mb-0" :class="dateExpiree(item.date_expiration) ? 'text-danger fw-semibold' : ''">{{ formatDate(item.date_expiration) || '—' }}</p></div>
                            <div class="mb-2"><label class="text-muted small mb-0">Validation</label><p class="mb-0">{{ formatDate(item.date_validation) || '—' }}</p></div>
                            <div class="mb-2"><label class="text-muted small mb-0">Créé le</label><p class="mb-0">{{ formatDate(item.created_at) }}</p></div>
                            <div><label class="text-muted small mb-0">Modifié le</label><p class="mb-0">{{ formatDate(item.updated_at) }}</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

const STATUT_MAP = {
    a_faire:       { label: 'À faire',       badge: 'bg-secondary' },
    en_cours:      { label: 'En cours',      badge: 'bg-primary' },
    valide:        { label: 'Valide',        badge: 'bg-success' },
    non_conforme:  { label: 'Non conforme',  badge: 'bg-danger' },
    expire:        { label: 'Expiré',        badge: 'bg-dark' },
};

export default {
    name: 'DaeConformiteShow',
    data() {
        return {
            loading: true,
            item: null,
            verifyStatut: 'a_faire',
            verifyLoading: false,
        };
    },
    created() {
        this.fetch();
    },
    methods: {
        async fetch() {
            this.loading = true;
            const id = window.location.pathname.split('/').pop();
            try {
                const r = await axios.get(`/dae/conformite/${id}`);
                this.item = r.data;
                this.verifyStatut = this.item.statut || 'a_faire';
            } catch (e) { this.item = null; }
            finally { this.loading = false; }
        },
        async submitVerification() {
            this.verifyLoading = true;
            try {
                await axios.patch(`/dae/conformite/${this.item.id}/verifier`, { statut: this.verifyStatut });
                await this.fetch();
            } catch (e) { alert('Erreur lors de la mise à jour du statut.'); }
            finally { this.verifyLoading = false; }
        },
        statutBadge(s) { return STATUT_MAP[s]?.badge || 'bg-secondary'; },
        statutLabel(s) { return STATUT_MAP[s]?.label || s || '-'; },
        typeLabel(t) { const m = { reglementaire: 'Réglementaire', fiscal: 'Fiscal', social: 'Social', juridique: 'Juridique', qualite: 'Qualité' }; return m[t] || t || '-'; },
        dateExpiree(d) { if (!d) return false; return new Date(d) < new Date(); },
        formatDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }); } catch { return d; } },
    },
};
</script>

<style scoped>
.dae-conformite-show { padding: 20px; min-height: 80vh; }
.dae-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 20px; }
.dae-spinner { width: 48px; height: 48px; border: 4px solid rgba(255,121,0,0.1); border-top-color: #FF7900; border-radius: 50%; animation: dae-spin 0.7s linear infinite; }
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
</style>
