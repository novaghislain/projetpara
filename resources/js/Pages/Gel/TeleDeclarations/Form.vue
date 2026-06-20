<template>
    <GelLayout page-title="Nouvelle Déclaration">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-4" style="max-width:720px;">
                <h2 class="text-xl fw-bold mb-3">Nouvelle Déclaration Fiscale</h2>

                <form method="POST" action="/tele-declarations">
                    <input type="hidden" name="_token" :value="csrf" />
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Client</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Type de déclaration</label>
                            <select name="tax_type" class="form-select" required>
                                <option value="">—</option>
                                <option value="tva">TVA</option>
                                <option value="is">Impôt sur les Sociétés</option>
                                <option value="its">ITS (Traitements & Salaires)</option>
                                <option value="cnss">CNSS</option>
                                <option value="vps">VPS</option>
                                <option value="aib">AIB</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Année</label>
                            <select name="period_year" class="form-select" required>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Mois (si mensuel)</label>
                            <select name="period_month" class="form-select">
                                <option value="">—</option>
                                <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Trimestre (si trimestriel)</label>
                            <select name="period_quarter" class="form-select">
                                <option value="">—</option>
                                <option value="1">T1</option>
                                <option value="2">T2</option>
                                <option value="3">T3</option>
                                <option value="4">T4</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Date début période</label>
                            <input type="date" name="date_debut" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Date fin période</label>
                            <input type="date" name="date_fin" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Date échéance</label>
                            <input type="date" name="date_echeance" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Base imposable (FCFA)</label>
                            <input type="number" step="0.01" min="0" name="base_imposable" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Taux (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="taux" class="form-control" placeholder="Ex: 18" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Montant dû (FCFA)</label>
                            <input type="number" step="0.01" min="0" name="montant_dut" class="form-control" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Notes</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi-check-circle me-1"></i> Créer la déclaration
                        </button>
                        <a href="/tele-declarations" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['clients'])
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
</script>
