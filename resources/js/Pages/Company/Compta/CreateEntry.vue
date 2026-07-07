<script setup>
import { ref, computed, onMounted } from 'vue';
import AccountSelector from '../../../Components/Accounting/AccountSelector.vue';
import MoneyInput from '../../../Components/Accounting/MoneyInput.vue';

const emit = defineEmits(['saved', 'cancel']);

const accounts = ref([]);
const journals = ref([]);
const saving = ref(false);
const form = ref({
    journal_id: '',
    entry_date: new Date().toISOString().split('T')[0],
    reference: '',
    description: '',
    status: 'draft',
    lines: [
        { account_id: '', description: '', debit: 0, credit: 0 },
        { account_id: '', description: '', debit: 0, credit: 0 },
    ],
});

const totalDebit = computed(() => form.value.lines.reduce((s, l) => s + (Number(l.debit) || 0), 0));
const totalCredit = computed(() => form.value.lines.reduce((s, l) => s + (Number(l.credit) || 0), 0));
const difference = computed(() => Math.abs(totalDebit.value - totalCredit.value));
const isBalanced = computed(() => difference.value < 1);

function addLine() {
    form.value.lines.push({ account_id: '', description: '', debit: 0, credit: 0 });
}

function removeLine(index) {
    if (form.value.lines.length > 2) form.value.lines.splice(index, 1);
}

async function saveEntry(status) {
    const validLines = form.value.lines.filter(l => l.account_id && (Number(l.debit) > 0 || Number(l.credit) > 0));
    if (validLines.length < 2) { alert('Minimum 2 lignes avec compte et montant.'); return; }
    if (!form.value.journal_id) { alert('Veuillez sélectionner un journal.'); return; }
    if (!form.value.description) { alert('Veuillez saisir un libellé.'); return; }

    saving.value = true;
    try {
        const payload = {
            journal_id: form.value.journal_id,
            entry_date: form.value.entry_date,
            reference: form.value.reference || null,
            description: form.value.description,
            status: status,
            lines: validLines.map(l => ({
                account_id: l.account_id,
                description: l.description || form.value.description,
                debit: Number(l.debit) || 0,
                credit: Number(l.credit) || 0,
            })),
        };

        const res = await fetch('/api/entries', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await res.json();
        if (res.ok) {
            emit('saved', result.data);
        } else {
            alert(result.message || 'Erreur lors de la création');
        }
    } catch (e) {
        console.error('Erreur:', e);
        alert('Erreur réseau');
    } finally {
        saving.value = false;
    }
}

async function loadAccounts() {
    try {
        const res = await fetch('/api/chart-accounts?per_page=500', { headers: { Accept: 'application/json' } });
        const result = await res.json();
        accounts.value = result.data?.data || result.data || [];
    } catch (e) { console.error('Erreur chargement comptes:', e); }
}

async function loadJournals() {
    try {
        const res = await fetch('/api/journals', { headers: { Accept: 'application/json' } });
        const result = await res.json();
        journals.value = result.data || [];
    } catch (e) { console.error('Erreur chargement journaux:', e); }
}

function formatMoney(val) {
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val || 0) + ' F';
}

onMounted(() => { loadAccounts(); loadJournals(); });
</script>

<template>
    <div>
        <h5 class="mb-4">Nouvelle écriture comptable</h5>

        <!-- En-tête -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label small">Journal <span class="text-danger">*</span></label>
                <select v-model="form.journal_id" class="form-select form-select-sm" required>
                    <option value="">Sélectionner...</option>
                    <option v-for="j in journals" :key="j.id" :value="j.id">{{ j.code }} — {{ j.label }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date <span class="text-danger">*</span></label>
                <input v-model="form.entry_date" type="date" class="form-control form-control-sm" />
            </div>
            <div class="col-md-2">
                <label class="form-label small">Référence</label>
                <input v-model="form.reference" type="text" class="form-control form-control-sm" placeholder="Auto" />
            </div>
            <div class="col-md-5">
                <label class="form-label small">Libellé <span class="text-danger">*</span></label>
                <input v-model="form.description" type="text" class="form-control form-control-sm" placeholder="Libellé de l'écriture" />
            </div>
        </div>

        <!-- Lignes -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Lignes d'écriture</h6>
                <button @click="addLine" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%;" class="small">Compte <span class="text-danger">*</span></th>
                            <th style="width: 25%;" class="small">Libellé</th>
                            <th style="width: 15%;" class="small text-end">Débit</th>
                            <th style="width: 15%;" class="small text-end">Crédit</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(line, index) in form.lines" :key="index">
                            <td>
                                <AccountSelector
                                    v-model="line.account_id"
                                    :accounts="accounts"
                                    placeholder="Code ou nom..."
                                />
                            </td>
                            <td>
                                <input v-model="line.description" type="text"
                                       class="form-control form-control-sm" placeholder="Libellé ligne" />
                            </td>
                            <td>
                                <input v-model.number="line.debit" type="number" min="0" step="1"
                                       class="form-control form-control-sm text-end" placeholder="0"
                                       @input="line.credit = 0" />
                            </td>
                            <td>
                                <input v-model.number="line.credit" type="number" min="0" step="1"
                                       class="form-control form-control-sm text-end" placeholder="0"
                                       @input="line.debit = 0" />
                            </td>
                            <td>
                                <button v-if="form.lines.length > 2" @click="removeLine(index)"
                                        class="btn btn-sm btn-outline-danger py-0 px-1">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="small">TOTAUX</th>
                            <th class="text-end small">{{ formatMoney(totalDebit) }}</th>
                            <th class="text-end small">{{ formatMoney(totalCredit) }}</th>
                            <th></th>
                        </tr>
                        <tr v-if="!isBalanced" class="table-danger">
                            <td colspan="5" class="small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Écriture non équilibrée : écart de {{ formatMoney(difference) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button @click="$emit('cancel')" class="btn btn-outline-secondary btn-sm" :disabled="saving">
                Annuler
            </button>
            <button @click="saveEntry('draft')" :disabled="!isBalanced || saving"
                    class="btn btn-outline-primary btn-sm">
                <i class="bi bi-save me-1"></i>Brouillon
            </button>
            <button @click="saveEntry('posted')" :disabled="!isBalanced || saving"
                    class="btn btn-primary btn-sm">
                <i class="bi bi-check-lg me-1"></i>Valider et poster
            </button>
        </div>
    </div>
</template>
