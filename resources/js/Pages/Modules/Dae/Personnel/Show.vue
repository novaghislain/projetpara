<template>
    <div class="dae-personnel-show">
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement...</p>
        </div>
        <div v-else-if="!personne" class="dae-loading">
            <i class="bi bi-exclamation-circle text-muted" style="font-size:3rem;"></i>
            <p class="mt-3 text-muted">Membre introuvable</p>
            <a href="/dae/personnel" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
        </div>
        <div v-else class="dae-content">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/dae/personnel" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
                <div>
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-person-badge me-2 text-primary"></i>{{ personne.prenom }} {{ personne.nom }}
                    </h4>
                    <p class="text-muted small mb-0 mt-1">
                        <span class="badge" :class="statutBadge(personne.statut)">{{ statutLabel(personne.statut) }}</span>
                        <span class="ms-2">{{ personne.poste }}</span>
                        <span class="ms-2" v-if="personne.departement">· {{ personne.departement }}</span>
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 px-4">
                            <h6 class="fw-semibold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="text-muted small mb-0">Email</label><p class="mb-0">{{ personne.email || '—' }}</p></div>
                                <div class="col-md-6"><label class="text-muted small mb-0">Téléphone</label><p class="mb-0">{{ personne.telephone || '—' }}</p></div>
                                <div class="col-md-6"><label class="text-muted small mb-0">Type de contrat</label><p class="mb-0">{{ personne.type_contrat || '—' }}</p></div>
                                <div class="col-md-6"><label class="text-muted small mb-0">Salaire</label><p class="mb-0">{{ formatCurrency(personne.salaire) }}</p></div>
                                <div class="col-md-6"><label class="text-muted small mb-0">Date d'embauche</label><p class="mb-0">{{ formatDate(personne.date_embauche) }}</p></div>
                                <div class="col-md-6"><label class="text-muted small mb-0">Date de départ</label><p class="mb-0">{{ formatDate(personne.date_depart) || '—' }}</p></div>
                                <div class="col-12" v-if="personne.notes"><label class="text-muted small mb-0">Notes</label><p class="mb-0">{{ personne.notes }}</p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-shield me-2 text-primary"></i>Statut</h6>
                            <p class="mb-1"><span class="badge" :class="statutBadge(personne.statut)" style="font-size:0.9rem;">{{ statutLabel(personne.statut) }}</span></p>
                            <p class="text-muted small mb-0 mt-2">N° SS : {{ personne.numero_securite_sociale || 'Non renseigné' }}</p>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-calendar me-2 text-primary"></i>Dates</h6>
                            <div class="mb-2"><label class="text-muted small mb-0">Créé le</label><p class="mb-0">{{ formatDate(personne.created_at) }}</p></div>
                            <div><label class="text-muted small mb-0">Modifié le</label><p class="mb-0">{{ formatDate(personne.updated_at) }}</p></div>
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
    actif: { label: 'Actif', badge: 'bg-success' },
    conge: { label: 'Congé', badge: 'bg-warning text-dark' },
    suspendu: { label: 'Suspendu', badge: 'bg-secondary' },
    sorti: { label: 'Sorti', badge: 'bg-danger' },
};

export default {
    name: 'DaePersonnelShow',
    data() {
        return { loading: true, personne: null };
    },
    created() {
        this.fetch();
    },
    methods: {
        async fetch() {
            this.loading = true;
            const id = window.location.pathname.split('/').pop();
            try {
                const r = await axios.get(`/dae/personnel/${id}`);
                this.personne = r.data;
            } catch (e) { this.personne = null; }
            finally { this.loading = false; }
        },
        statutBadge(s) { return STATUT_MAP[s]?.badge || 'bg-secondary'; },
        statutLabel(s) { return STATUT_MAP[s]?.label || s || '-'; },
        formatDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }); } catch { return d; } },
        formatCurrency(v) { if (v === null || v === undefined) return '-'; return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(v); },
    },
};
</script>
<style scoped>
.dae-personnel-show { padding: 20px; min-height: 80vh; }
.dae-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 20px; }
.dae-spinner { width: 48px; height: 48px; border: 4px solid rgba(255,121,0,0.1); border-top-color: #FF7900; border-radius: 50%; animation: dae-spin 0.7s linear infinite; }
@keyframes dae-spin { to { transform: rotate(360deg); } }
.dae-loading-text { color: #6c757d; font-size: 14px; font-weight: 500; }
</style>
