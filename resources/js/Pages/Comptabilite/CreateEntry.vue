<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a :href="'/comptabilite/ecritures'" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Nouvelle écriture comptable</h4>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Header -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Journal *</label>
                        <select v-model="form.journal_id" class="form-select form-select-sm">
                            <option value="">Sélectionner</option>
                            <option v-for="j in journals" :key="j.id" :value="j.id">{{ j.code }} — {{ j.label || j.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date *</label>
                        <input v-model="form.entry_date" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Référence</label>
                        <input v-model="form.reference" class="form-control form-control-sm" placeholder="Auto si vide">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">N° Pièce</label>
                        <input v-model="form.numero_piece" class="form-control form-control-sm" placeholder="Facultatif">
                    </div>
                    <div class="col-12">
                        <label class="form-label small mb-1">Libellé *</label>
                        <input v-model="form.description" class="form-control form-control-sm" placeholder="Description de l'écriture">
                    </div>
                </div>

                <!-- Lines -->
                <label class="form-label small fw-semibold">Lignes (débit = crédit)</label>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width:220px">Compte</th>
                                <th>Libellé</th>
                                <th style="width:130px" class="text-end">Débit</th>
                                <th style="width:130px" class="text-end">Crédit</th>
                                <th style="width:36px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(line, i) in form.lines" :key="i">
                                <td>
                                    <select v-model="line.account_id" class="form-select form-select-sm">
                                        <option value="">Choisir</option>
                                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.code }} — {{ acc.name }}</option>
                                    </select>
                                </td>
                                <td><input v-model="line.label" class="form-control form-control-sm" placeholder="Libellé ligne"></td>
                                <td><input v-model.number="line.debit" type="number" min="0" class="form-control form-control-sm text-end" @input="line.credit = 0"></td>
                                <td><input v-model.number="line.credit" type="number" min="0" class="form-control form-control-sm text-end" @input="line.debit = 0"></td>
                                <td>
                                    <button v-if="form.lines.length > 2" class="btn btn-sm btn-outline-danger py-0 px-1" @click="removeLine(i)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">TOTAUX</td>
                                <td class="text-end" :class="isBalanced ? 'text-success' : 'text-danger'">{{ fmt(formTotalDebit) }}</td>
                                <td class="text-end" :class="isBalanced ? 'text-success' : 'text-danger'">{{ fmt(formTotalCredit) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <button class="btn btn-sm btn-outline-primary" @click="addLine"><i class="bi bi-plus"></i> Ajouter</button>
                    <span class="small fw-semibold" :class="isBalanced ? 'text-success' : 'text-danger'">
                        <i :class="isBalanced ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-triangle-fill'"></i>
                        {{ isBalanced ? 'Équilibrée' : `Déséquilibre : ${fmt(Math.abs(formTotalDebit - formTotalCredit))}` }}
                    </span>
                </div>

                <hr>
                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-light btn-sm" @click="window.history.back()">Annuler</button>
                    <button class="btn btn-outline-secondary btn-sm" :disabled="!isBalanced || submitting" @click="saveEntry('draft')">
                        <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-save me-1"></i>Brouillon
                    </button>
                    <button class="btn btn-primary btn-sm" :disabled="!isBalanced || submitting" @click="saveEntry('posted')">
                        <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-check-lg me-1"></i>Valider
                    </button>
                </div>

                <div v-if="error" class="alert alert-danger mt-3 mb-0">{{ error }}</div>
                <div v-if="success" class="alert alert-success mt-3 mb-0">{{ success }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

const journals = ref([])
const accounts = ref([])
const submitting = ref(false)
const error = ref('')
const success = ref('')

const form = reactive({
    journal_id: '',
    entry_date: new Date().toISOString().split('T')[0],
    reference: '',
    numero_piece: '',
    description: '',
    lines: [
        { account_id: '', label: '', debit: 0, credit: 0 },
        { account_id: '', label: '', debit: 0, credit: 0 },
    ],
})

const formTotalDebit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0))
const formTotalCredit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0))
const isBalanced = computed(() => Math.abs(formTotalDebit.value - formTotalCredit.value) < 0.01 && formTotalDebit.value > 0)

const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'

const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')

const api = (path, opts = {}) =>
    fetch(path, {
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
        ...opts,
    })

onMounted(async () => {
    try {
        const [jr, ar] = await Promise.all([
            api('/api/journals'),
            api('/api/chart-accounts'),
        ])
        if (jr.ok) journals.value = await jr.json()
        if (ar.ok) accounts.value = await ar.json()
    } catch (e) { error.value = 'Erreur chargement données' }
})

function addLine() { form.lines.push({ account_id: '', label: '', debit: 0, credit: 0 }) }
function removeLine(i) { if (form.lines.length > 2) form.lines.splice(i, 1) }

async function saveEntry(status) {
    submitting.value = true; error.value = ''; success.value = ''
    try {
        const payload = {
            journal_id: form.journal_id,
            entry_date: form.entry_date,
            reference: form.reference || null,
            numero_piece: form.numero_piece || null,
            description: form.description,
            status,
            lines: form.lines.map(l => ({
                account_id: l.account_id,
                label: l.label,
                debit: l.debit || 0,
                credit: l.credit || 0,
            })),
        }
        const r = await api('/api/entries', { method: 'POST', body: JSON.stringify(payload) })
        if (!r.ok) { const e = await r.json().catch(() => ({})); throw new Error(e.message || 'Erreur') }
        success.value = status === 'posted' ? 'Écriture validée et postée avec succès !' : 'Brouillon enregistré.'
        if (status === 'posted') setTimeout(() => { window.location.href = '/comptabilite/ecritures' }, 1000)
    } catch (e) { error.value = e.message } finally { submitting.value = false }
}
</script>
