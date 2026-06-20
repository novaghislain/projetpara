<template>
    <GelLayout page-title="Calcul Paie">
        <div class="p-6">
            <h2 class="text-xl fw-bold mb-3">Calculateur de Paie — Barèmes Bénin 2026</h2>

            <div class="row g-3">
                <div class="col-md-5">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h6 class="fw-bold mb-3">Paramètres salaire</h6>
                        <form @submit.prevent="calculer">
                            <div class="mb-3">
                                <label class="form-label small">Salaire brut mensuel (FCFA)</label>
                                <input type="number" v-model.number="form.salaire" class="form-control" min="0" step="1000" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Situation familiale</label>
                                <select v-model="form.situation" class="form-select">
                                    <option value="celibataire">Célibataire</option>
                                    <option value="marie_sans_enf">Marié(e) sans enfants</option>
                                    <option value="marie_1_enf">Marié(e) + 1 enfant</option>
                                    <option value="marie_2_enf">Marié(e) + 2 enfants</option>
                                    <option value="marie_3_enf">Marié(e) + 3 enfants</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi-calculator me-1"></i> Calculer
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-7" v-if="resultat">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h6 class="fw-bold mb-3">Résultats</h6>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Salaire brut</div>
                                    <div class="fw-bold fs-5">{{ formatMille(resultat.salaire_brut) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Salaire net</div>
                                    <div class="fw-bold fs-5 text-success">{{ formatMille(resultat.salaire_net) }}</div>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-semibold small text-uppercase text-muted mb-2">IRPP (Impôt sur le Revenu)</h6>
                        <table class="table table-sm small mb-3">
                            <thead>
                                <tr>
                                    <th>Tranche</th>
                                    <th>Taux</th>
                                    <th>Base</th>
                                    <th>Impôt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(t, i) in resultat.irpp.tranches" :key="i">
                                    <td>{{ formatMille(t.min) }} — {{ t.max === 999999999 ? '∞' : formatMille(t.max) }}</td>
                                    <td>{{ t.taux }}%</td>
                                    <td>{{ formatMille(t.base) }}</td>
                                    <td class="fw-semibold">{{ formatMille(t.impot) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total IRPP mensuel</td>
                                    <td class="text-danger">{{ formatMille(resultat.irpp.irpp_mensuel) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <h6 class="fw-semibold small text-uppercase text-muted mb-2">CNSS</h6>
                        <table class="table table-sm small mb-0">
                            <tbody>
                                <tr>
                                    <td>Assiette (plafonnée à 450 000 FCFA)</td>
                                    <td class="fw-semibold">{{ formatMille(resultat.cnss.assiette) }}</td>
                                </tr>
                                <tr>
                                    <td>Part employeur (15,40 %)</td>
                                    <td class="text-danger fw-semibold">{{ formatMille(resultat.cnss.part_employeur) }}</td>
                                </tr>
                                <tr>
                                    <td>Part salarié (3,36 %)</td>
                                    <td class="text-danger fw-semibold">{{ formatMille(resultat.cnss.part_salarie) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Total cotisation CNSS</td>
                                    <td>{{ formatMille(resultat.cnss.total) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <hr />
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="small text-muted">Abattement</div>
                                <div class="fw-semibold">{{ formatMille(resultat.irpp.abattement) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Revenu imposable</div>
                                <div class="fw-semibold">{{ formatMille(resultat.irpp.revenu_imposable) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Parts</div>
                                <div class="fw-semibold">{{ resultat.irpp.nb_parts }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const form = reactive({ salaire: 350000, situation: 'celibataire' })
const resultat = ref(null)

const formatMille = (v) => {
    if (v == null || v === '') return '—';
    return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' FCFA';
}

const calculer = async () => {
    try {
        const res = await fetch('/api/paie/calculer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ salaire_brut: form.salaire, situation: form.situation }),
        });
        resultat.value = await res.json();
    } catch (e) {
        alert('Erreur de calcul');
    }
}

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
</script>
