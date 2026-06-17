<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const company = ref(null);
const licenses = ref([]);
const loading = ref(true);
const error = ref(null);

const clientId = window.__CLIENT_ID__;

const loadData = async () => {
    if (!clientId) { loading.value = false; return; }
    try {
        const res = await fetch(`/api/company/${clientId}/info`);
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        company.value = data.company;
        licenses.value = data.licenses;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};

const progressPct = (lic) => {
    if (!lic.start_date || !lic.end_date) return 0;
    const start = new Date(lic.start_date).getTime();
    const end = new Date(lic.end_date).getTime();
    const now = Date.now();
    if (now >= end) return 100;
    if (now <= start) return 0;
    return Math.round(((now - start) / (end - start)) * 100);
};

onMounted(loadData);
</script>

<template>
    <CompanyLayout page-title="Mes Services">
        <!-- Loading -->
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888; font-size:14px;">Chargement…</span>
        </div>

        <div v-else-if="error" class="isup-alert-error">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
        </div>

        <template v-else>
            <div class="isup-shell">
                <!-- Header -->
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo">
                            <i class="bi-grid-3x3-gap" style="font-size:20px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="isup-portal-company">Services sous licence</div>
                            <div class="isup-portal-sub">
                                {{ company?.company_name }} — {{ licenses.length }} service{{ licenses.length > 1 ? 's' : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu -->
                <div class="p-3">

                    <!-- Empty -->
                    <div v-if="!licenses.length" class="text-center py-5">
                        <i class="bi-inbox" style="font-size:48px; color:#dce3ee; display:block; margin-bottom:12px;"></i>
                        <p style="font-size:15px; color:#888; margin-bottom:4px;">Aucun service pour le moment.</p>
                        <p style="font-size:12px; color:#aaa;">Contactez votre conseiller pour souscrire à des services.</p>
                    </div>

                    <!-- Grille -->
                    <div v-else class="row g-3">
                        <div v-for="lic in licenses" :key="lic.id" class="col-lg-6">
                            <div class="isup-svc-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="isup-svc-icon">
                                            <i class="bi-gear"></i>
                                        </div>
                                        <div>
                                            <div class="isup-svc-name">{{ lic.service_name }}</div>
                                            <span class="isup-svc-badge" :class="lic.valid ? 'isup-svc-active' : 'isup-svc-expired'">
                                                <i :class="lic.valid ? 'bi-check-circle' : 'bi-exclamation-circle'" class="me-1"></i>
                                                {{ lic.valid ? 'Actif' : 'Expiré' }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="isup-svc-validity">{{ lic.duration_months }} mois</span>
                                </div>

                                <div class="isup-svc-detail">
                                    <div class="isup-svc-row">
                                        <span class="isup-svc-label">Licence</span>
                                        <code class="isup-code">{{ lic.license_key }}</code>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <span class="isup-svc-label">Début</span>
                                            <span class="isup-svc-value">{{ formatDate(lic.start_date) }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="isup-svc-label">Fin</span>
                                            <span class="isup-svc-value">{{ formatDate(lic.end_date) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Barre de progression -->
                                <div v-if="lic.valid" class="mt-3">
                                    <div class="d-flex justify-content-between isup-svc-label mb-1">
                                        <span>Validité</span>
                                    </div>
                                    <div class="isup-progress-bar">
                                        <div class="isup-progress-fill" :style="{ width: progressPct(lic) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

<style scoped>
/* ── Services-specific styles ── */
.isup-svc-card { display:flex; align-items:flex-start; gap:14px; background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:14px 16px; transition:box-shadow .15s; }
.isup-svc-card:hover { box-shadow:0 3px 12px rgba(0,0,0,0.08); }
.isup-svc-icon { width:44px; height:44px; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; background:#e3f2fd; color:#1565c0; }
.isup-svc-name { font-family:'Outfit',sans-serif; font-size:14px; font-weight:700; color:#163A5E; }
.isup-svc-badge { display:inline-flex; font-size:10px; font-weight:700; padding:1px 7px; border-radius:3px; text-transform:uppercase; background:#f0f4f8; color:#555; }
.isup-svc-active { background:#e8f5e9; color:#2e7d32; }
.isup-svc-expired { background:#fff3e0; color:#e65100; }
.isup-svc-validity { font-size:11px; color:#888; margin-top:6px; }
.isup-svc-detail { background:#f8fafc; border:1px solid #eef2f7; border-radius:4px; padding:12px 16px; }
.isup-svc-row { margin-bottom:6px; }
.isup-svc-label { font-size:11px; font-weight:600; color:#888; }
.isup-svc-value { font-size:13px; color:#333; font-weight:500; }
.isup-code { font-family:'Consolas','Monaco','Courier New',monospace; font-size:12px; background:#f5f5f5; border:1px solid #eef2f7; border-radius:3px; padding:8px 12px; color:#c62828; overflow-x:auto; }
.isup-progress-bar { height:6px; background:#eef2f7; border-radius:3px; overflow:hidden; margin:6px 0; }
.isup-progress-fill { height:100%; border-radius:3px; background:linear-gradient(90deg,#43a047,#66bb6a); transition:width .5s ease; }
</style>
