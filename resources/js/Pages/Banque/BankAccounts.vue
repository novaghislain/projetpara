<template>
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-bank me-2"></i>Comptes bancaires</h4>
            <button class="btn btn-primary btn-sm" @click="showModal = true; editing = null; form = { ...emptyForm }">
                <i class="bi bi-plus-lg me-1"></i>Nouveau compte
            </button>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else>
            <!-- Cards grid -->
            <div class="row g-3">
                <div v-for="acc in accounts" :key="acc.id" class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold mb-0">{{ acc.bank_name }}</h6>
                                    <small class="text-muted">{{ acc.account_type_label || acc.account_type }}</small>
                                </div>
                                <span :class="acc.is_active ? 'badge bg-success' : 'badge bg-secondary'">
                                    {{ acc.is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            <p class="text-muted small mb-2">{{ acc.account_number }}</p>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <small class="text-muted d-block">Solde actuel</small>
                                    <span class="fw-bold fs-5" :class="parseFloat(acc.balance) >= 0 ? 'text-success' : 'text-danger'">
                                        {{ fmt(acc.balance) }}
                                    </span>
                                </div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" @click="viewDetail(acc)" title="Détails">
                                        <i class="bi bi-arrow-right"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" @click="editAccount(acc)" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="accounts.length === 0 && !loading" class="text-center text-muted py-5">
                <i class="bi bi-bank fs-1 d-block mb-2"></i>
                <p>Aucun compte bancaire enregistré</p>
            </div>
        </template>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editing ? 'Modifier' : 'Nouveau' }} compte bancaire</h5>
                        <button type="button" class="btn-close" @click="showModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Banque *</label>
                            <input v-model="form.bank_name" class="form-control form-control-sm" placeholder="Nom de la banque">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Type de compte *</label>
                            <select v-model="form.account_type" class="form-select form-select-sm">
                                <option value="checking">Compte courant</option>
                                <option value="savings">Compte épargne</option>
                                <option value="deposit">Compte de dépôt</option>
                                <option value="loan">Compte de prêt</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Numéro de compte *</label>
                            <input v-model="form.account_number" class="form-control form-control-sm" placeholder="Numéro de compte">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Solde d'ouverture</label>
                            <input v-model.number="form.balance" type="number" class="form-control form-control-sm">
                        </div>
                        <div class="form-check">
                            <input v-model="form.is_active" type="checkbox" class="form-check-input" id="isActive">
                            <label class="form-check-label small" for="isActive">Compte actif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light btn-sm" @click="showModal = false">Annuler</button>
                        <button class="btn btn-primary btn-sm" :disabled="saving" @click="saveAccount">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                            {{ editing ? 'Modifier' : 'Créer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/*
 * BankAccounts.vue - Liste des comptes bancaires
 *
 * Affiche la liste des comptes bancaires sous forme de cartes (grid).
 * Chaque carte présente le nom de la banque, le type de compte,
 * le numéro de compte et le solde actuel. Permet de créer un nouveau
 * compte ou d'en modifier un existant via un modal.
 */
import { ref, reactive, computed, onMounted } from 'vue'

// Liste des comptes bancaires chargés depuis l'API
const accounts = ref([])
const loading = ref(true)
const error = ref(null)
const saving = ref(false)
const showModal = ref(false)
// Compte en cours d'édition (null si création)
const editing = ref(null)

// Formulaire vierge pour la création d'un nouveau compte
const emptyForm = { bank_name: '', account_type: 'checking', account_number: '', balance: 0, is_active: true }
const form = reactive({ ...emptyForm })

// Formateur monétaire en francs CFA
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
// Jeton CSRF pour les requêtes sécurisées
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
// Fonction utilitaire d'appel API avec en-têtes JSON par défaut
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

// Chargement de la liste des comptes bancaires depuis l'API
async function loadAccounts() {
    loading.value = true; error.value = null
    try {
        const r = await api('/api/banking/accounts')
        if (r.ok) accounts.value = await r.json()
        else throw new Error('Erreur chargement')
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

// Pré-remplissage du formulaire avec les données d'un compte existant pour modification
function editAccount(acc) {
    editing.value = acc
    Object.assign(form, {
        bank_name: acc.bank_name,
        account_type: acc.account_type,
        account_number: acc.account_number,
        balance: acc.balance || 0,
        is_active: acc.is_active ?? true,
    })
    showModal.value = true
}

// Enregistrement (création ou modification) d'un compte bancaire
async function saveAccount() {
    saving.value = true
    try {
        const url = editing.value ? `/api/banking/accounts/${editing.value.id}` : '/api/banking/accounts'
        const method = editing.value ? 'PUT' : 'POST'
        const r = await api(url, { method, body: JSON.stringify(form) })
        if (!r.ok) throw new Error('Erreur enregistrement')
        showModal.value = false
        await loadAccounts()
    } catch (e) { error.value = e.message } finally { saving.value = false }
}

// Redirection vers la page de détail d'un compte bancaire
function viewDetail(acc) {
    window.location.href = `/comptabilite/banque/compte/${acc.id}`
}

// Chargement automatique au montage du composant
onMounted(loadAccounts)
</script>
