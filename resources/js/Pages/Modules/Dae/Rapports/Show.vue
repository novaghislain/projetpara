<template>
<GelLayout>
    <div class="dae-rapports-show">
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement...</p>
        </div>
        <div v-else-if="!item" class="dae-loading">
            <i class="bi bi-file-earmark-excel text-muted" style="font-size:3rem;"></i>
            <p class="mt-3 text-muted">Rapport introuvable</p>
            <a href="/dae/rapports" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
        </div>
        <div v-else class="dae-content">
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/dae/rapports" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
                <div class="flex-grow-1">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up me-2 text-primary"></i>{{ item.titre }}
                    </h4>
                    <p class="text-muted small mb-0 mt-1">
                        <span class="badge" :class="statutBadge(item.statut)">{{ statutLabel(item.statut) }}</span>
                        <span class="ms-2">{{ typeLabel(item.type_rapport) }}</span>
                        <span class="ms-2" v-if="item.client">· {{ item.client.nom || item.client.raison_sociale || item.client.name }}</span>
                    </p>
                </div>
                <a v-if="item.fichier" :href="`/dae/rapports/${item.id}/telecharger`"
                   class="btn btn-primary btn-sm flex-shrink-0">
                    <i class="bi bi-download me-1"></i>Télécharger
                </a>
            </div>

            <div class="row g-4">
                <!-- Main info -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 px-4">
                            <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Détails du rapport</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12" v-if="item.description">
                                    <label class="text-muted small mb-0">Description</label>
                                    <p class="mb-0">{{ item.description }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Type</label>
                                    <p class="mb-0">{{ typeLabel(item.type_rapport) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Client</label>
                                    <p class="mb-0">{{ item.client?.nom || item.client?.raison_sociale || item.client?.name || '#' + item.client_id }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Période début</label>
                                    <p class="mb-0">{{ formatDate(item.periode_debut) || '—' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-0">Période fin</label>
                                    <p class="mb-0">{{ formatDate(item.periode_fin) || '—' }}</p>
                                </div>
                                <div class="col-12" v-if="item.metriques">
                                    <label class="text-muted small mb-0">Métriques</label>
                                    <pre class="small bg-light p-3 rounded mt-1 mb-0" style="max-height:200px;overflow:auto;">{{ JSON.stringify(item.metriques, null, 2) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-file-text me-2 text-primary"></i>Statut</h6>
                            <p class="mb-1"><span class="badge" :class="statutBadge(item.statut)" style="font-size:0.9rem;">{{ statutLabel(item.statut) }}</span></p>
                            <p class="text-muted small mb-0 mt-2" v-if="item.fichier">
                                <i class="bi bi-paperclip me-1"></i>{{ item.fichier }}
                            </p>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-calendar me-2 text-primary"></i>Dates</h6>
                            <div class="mb-2"><label class="text-muted small mb-0">Créé le</label><p class="mb-0">{{ formatDate(item.created_at) }}</p></div>
                            <div><label class="text-muted small mb-0">Modifié le</label><p class="mb-0">{{ formatDate(item.updated_at) }}</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</GelLayout>
</template>

<script>
import axios from 'axios';

const STATUT_MAP = {
    brouillon: { label: 'Brouillon', badge: 'bg-secondary' },
    genere:    { label: 'Généré',    badge: 'bg-primary' },
    finalise:  { label: 'Finalisé',  badge: 'bg-success' },
    envoye:    { label: 'Envoyé',    badge: 'bg-info' },
};

export default {
    name: 'DaeRapportsShow',
    data() {
        return { loading: true, item: null };
    },
    created() { this.fetch(); },
    methods: {
        async fetch() {
            this.loading = true;
            const id = window.location.pathname.split('/').pop();
            try {
                const r = await axios.get(`/dae/rapports/${id}`);
                this.item = r.data;
            } catch (e) { this.item = null; }
            finally { this.loading = false; }
        },
        typeLabel(t) { const m = { activite: 'Activité', financier: 'Financier', rh: 'Ressources humaines', conformite: 'Conformité', mission: 'Mission' }; return m[t] || t || '-'; },
        statutBadge(s) { return STATUT_MAP[s]?.badge || 'bg-secondary'; },
        statutLabel(s) { return STATUT_MAP[s]?.label || s || '-'; },
        formatDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }); } catch { return d; } },
    },
};
</script>

<style scoped>
.dae-rapports-show { padding: 20px; min-height: 80vh; }
.dae-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 20px; }
.dae-spinner { width: 48px; height: 48px; border: 4px solid rgba(255,121,0,0.1); border-top-color: #FF7900; border-radius: 50%; animation: dae-spin 0.7s linear infinite; }
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
</style>
