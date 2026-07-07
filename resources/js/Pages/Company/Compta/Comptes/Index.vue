<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';

const props = defineProps({
    comptes: Object,
    filters: Object,
});

const recherche = ref(props.filters?.q ?? '');
const classeFilter = ref(props.filters?.classe ?? '');
const showModal = ref(false);

const form = ref({
    numero: '',
    intitule: '',
    classe: '',
    type: 'actif',
    sous_type: '',
    parent_id: null,
});

const classes = [
    { val: 1, label: 'Classe 1 — Ressources durables' },
    { val: 2, label: 'Classe 2 — Actif immobilisé' },
    { val: 3, label: 'Classe 3 — Stocks' },
    { val: 4, label: 'Classe 4 — Tiers' },
    { val: 5, label: 'Classe 5 — Trésorerie' },
    { val: 6, label: 'Classe 6 — Charges' },
    { val: 7, label: 'Classe 7 — Produits' },
    { val: 8, label: 'Classe 8 — HAO' },
    { val: 9, label: 'Classe 9 — Analytique' },
];

const typeCompte = [
    { val: 'actif', label: 'Actif' },
    { val: 'passif', label: 'Passif' },
    { val: 'charge', label: 'Charge' },
    { val: 'produit', label: 'Produit' },
    { val: 'autre', label: 'Autre' },
];

const classeBadgeColor = (classe) => {
    const map = {1:'#163A5E',2:'#1a4a76',3:'#0f5132',4:'#92400e',5:'#065f46',6:'#7f1d1d',7:'#1e3a5f',8:'#44337a',9:'#1a2938'};
    return map[classe] ?? '#333';
};

const applyFilters = () => {
    router.get('/company/comptabilite/comptes', { q: recherche.value, classe: classeFilter.value }, { preserveState: true });
};

const submitForm = () => {
    router.post('/company/comptabilite/comptes', form.value, {
        onSuccess: () => { showModal.value = false; form.value = { numero: '', intitule: '', classe: '', type: 'actif', sous_type: '', parent_id: null }; },
    });
};

const deleteCompte = (id, numero) => {
    if (confirm(`Supprimer le compte ${numero} ?`)) {
        router.delete(`/company/comptabilite/comptes/${id}`);
    }
};
</script>

<template>
    <CompanyLayout page-title="Plan Comptable — SYSCOHADA">
        <div class="isup-comptes-page">

            <!-- Header -->
            <div class="isup-page-header mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-hdr-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
                        <div>
                            <h1 class="isup-ttl mb-0">Plan Comptable</h1>
                            <p class="isup-sub mb-0">SYSCOHADA Révisé — Classes 1 à 9</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/company/comptabilite/comptes/import" class="isup-btn isup-btn-outline">
                            <i class="bi bi-upload me-1"></i> Importer CSV
                        </a>
                        <button @click="showModal = true" class="isup-btn isup-btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Nouveau compte
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="isup-filters-bar mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <input v-model="recherche" type="text" class="isup-input"
                            placeholder="🔍 Rechercher par numéro ou intitulé..." @keyup.enter="applyFilters">
                    </div>
                    <div class="col-md-4">
                        <select v-model="classeFilter" class="isup-input">
                            <option value="">Toutes les classes</option>
                            <option v-for="c in classes" :key="c.val" :value="c.val">{{ c.label }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button @click="applyFilters" class="isup-btn isup-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filtrer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tableau -->
            <div class="isup-card">
                <div class="table-responsive">
                    <table class="isup-table">
                        <thead>
                            <tr>
                                <th>N° Compte</th>
                                <th>Intitulé</th>
                                <th>Classe</th>
                                <th>Type</th>
                                <th class="text-end">Solde Débiteur</th>
                                <th class="text-end">Solde Créditeur</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="comptes?.data?.length === 0">
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Aucun compte trouvé. Créez votre premier compte ou importez un plan comptable.
                                </td>
                            </tr>
                            <tr v-for="compte in comptes?.data" :key="compte.id" class="isup-tr">
                                <td>
                                    <code class="isup-code">{{ compte.numero }}</code>
                                </td>
                                <td class="fw-semibold">{{ compte.intitule }}</td>
                                <td>
                                    <span class="isup-classe-badge" :style="`background: ${classeBadgeColor(compte.classe)}`">
                                        Cl. {{ compte.classe }}
                                    </span>
                                </td>
                                <td><span class="isup-type-badge">{{ compte.type }}</span></td>
                                <td class="text-end">{{ new Intl.NumberFormat('fr-FR').format(compte.solde_debiteur) }}</td>
                                <td class="text-end">{{ new Intl.NumberFormat('fr-FR').format(compte.solde_crediteur) }}</td>
                                <td>
                                    <span v-if="compte.is_actif" class="isup-badge-success">Actif</span>
                                    <span v-else class="isup-badge-danger">Inactif</span>
                                </td>
                                <td class="text-end">
                                    <div class="isup-actions">
                                        <button class="isup-action-btn edit" title="Modifier"><i class="bi bi-pencil"></i></button>
                                        <button @click="deleteCompte(compte.id, compte.numero)"
                                            class="isup-action-btn delete" title="Supprimer">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="comptes?.last_page > 1" class="isup-pagination">
                    <a v-for="link in comptes?.links" :key="link.label"
                        :href="link.url" v-html="link.label"
                        class="isup-page-link" :class="{ active: link.active, disabled: !link.url }">
                    </a>
                </div>
            </div>

        </div>

        <!-- Modal Nouveau Compte -->
        <teleport to="body">
            <div v-if="showModal" class="isup-modal-overlay" @click.self="showModal = false">
                <div class="isup-modal">
                    <div class="isup-modal-header">
                        <h3 class="mb-0"><i class="bi bi-plus-circle-fill me-2" style="color:#FF7900"></i>Nouveau Compte</h3>
                        <button @click="showModal = false" class="isup-close-btn"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="isup-modal-body">
                        <form @submit.prevent="submitForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="isup-label">Numéro de compte *</label>
                                    <input v-model="form.numero" class="isup-input" placeholder="Ex: 411000" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="isup-label">Intitulé *</label>
                                    <input v-model="form.intitule" class="isup-input" placeholder="Ex: Clients - Comptes ordinaires" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="isup-label">Classe SYSCOHADA</label>
                                    <select v-model="form.classe" class="isup-input">
                                        <option value="">Sélectionner...</option>
                                        <option v-for="c in classes" :key="c.val" :value="c.val">{{ c.label }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="isup-label">Type *</label>
                                    <select v-model="form.type" class="isup-input" required>
                                        <option v-for="t in typeCompte" :key="t.val" :value="t.val">{{ t.label }}</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="isup-label">Sous-type (optionnel)</label>
                                    <input v-model="form.sous_type" class="isup-input" placeholder="Ex: Créances commerciales">
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <button type="button" @click="showModal = false" class="isup-btn isup-btn-outline">Annuler</button>
                                <button type="submit" class="isup-btn isup-btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </teleport>
    </CompanyLayout>
</template>

<style scoped>
.isup-comptes-page { font-family: 'Inter', sans-serif; }

.isup-page-header {
    background: linear-gradient(135deg, #163A5E, #1f4d7a);
    border-radius: 16px;
    padding: 24px 28px;
    color: white;
    box-shadow: 0 4px 20px rgba(22,58,94,0.22);
}
.isup-hdr-icon {
    width: 50px; height: 50px;
    background: rgba(255,121,0,0.18);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #FF7900; flex-shrink: 0;
}
.isup-ttl { font-size: 20px; font-weight: 800; }
.isup-sub { font-size: 13px; color: rgba(255,255,255,0.65); }

.isup-filters-bar {
    background: white; border-radius: 12px;
    padding: 16px 20px; border: 1px solid #eef0f4;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.isup-input {
    width: 100%; padding: 10px 14px; border-radius: 10px;
    border: 1.5px solid #e2e8f0; font-size: 13.5px;
    outline: none; transition: border 0.2s;
    font-family: 'Inter', sans-serif;
}
.isup-input:focus { border-color: #FF7900; }

.isup-btn {
    padding: 10px 18px; border-radius: 10px; font-size: 13.5px;
    font-weight: 600; cursor: pointer; border: none; transition: all 0.2s;
    display: inline-flex; align-items: center;
}
.isup-btn-primary { background: #FF7900; color: white; }
.isup-btn-primary:hover { background: #e06b00; transform: translateY(-1px); }
.isup-btn-outline { background: transparent; border: 2px solid #163A5E; color: #163A5E; }
.isup-btn-outline:hover { background: #163A5E; color: white; }

.isup-card { background: white; border-radius: 16px; border: 1px solid #eef0f4; overflow: hidden; box-shadow: 0 2px 14px rgba(0,0,0,0.05); }
.isup-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.isup-table thead tr { background: #f8f9fc; }
.isup-table th { padding: 12px 16px; font-weight: 700; color: #4a5568; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #eef0f4; }
.isup-table td { padding: 11px 16px; border-bottom: 1px solid #f4f6fa; }
.isup-tr:hover { background: #fafbff; }
.isup-code { font-size: 12px; background: #edf2f7; padding: 3px 8px; border-radius: 6px; color: #163A5E; font-weight: 700; }
.isup-classe-badge { color: white; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700; }
.isup-type-badge { background: #f0f4ff; color: #163A5E; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
.isup-badge-success { background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.isup-badge-danger { background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.isup-actions { display: flex; gap: 4px; justify-content: flex-end; }
.isup-action-btn { width: 30px; height: 30px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; transition: all 0.2s; }
.isup-action-btn.edit { background: #dbeafe; color: #1d4ed8; }
.isup-action-btn.edit:hover { background: #1d4ed8; color: white; }
.isup-action-btn.delete { background: #fee2e2; color: #dc2626; }
.isup-action-btn.delete:hover { background: #dc2626; color: white; }
.isup-label { font-size: 12px; font-weight: 700; color: #4a5568; display: block; margin-bottom: 5px; }
.isup-pagination { display: flex; gap: 4px; padding: 16px 20px; justify-content: flex-end; flex-wrap: wrap; }
.isup-page-link { padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 13px; border: 1px solid #eef0f4; color: #4a5568; transition: all 0.15s; }
.isup-page-link.active { background: #163A5E; color: white; border-color: #163A5E; }
.isup-page-link.disabled { opacity: 0.4; pointer-events: none; }

/* Modal */
.isup-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    z-index: 1050; backdrop-filter: blur(3px);
}
.isup-modal {
    background: white; border-radius: 20px; width: 90%; max-width: 580px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.2); overflow: hidden;
    animation: slideUp 0.25s ease;
}
@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.isup-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px; background: #f8f9fc; border-bottom: 1px solid #eef0f4;
}
.isup-modal-header h3 { font-size: 17px; font-weight: 700; color: #1a2938; }
.isup-close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8; }
.isup-close-btn:hover { color: #dc2626; }
.isup-modal-body { padding: 24px; }
</style>
