<template>
    <GelLayout page-title="Sécurité & 2FA">
        <div class="p-6">
            <h2 class="text-xl fw-bold mb-3">Sécurité du compte</h2>

            <div class="row g-3">
                <!-- 2FA Section -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi-shield-lock me-1"></i> Authentification à Deux Facteurs
                        </h6>

                        <div v-if="twoFactorEnabled" class="mb-3">
                            <div class="alert alert-success d-flex align-items-center gap-2">
                                <i class="bi-check-circle-fill"></i>
                                <span>2FA est activé sur votre compte.</span>
                            </div>
                            <form method="POST" action="/user/two-factor/disable">
                                <input type="hidden" name="_token" :value="csrf" />
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="bi-x-circle me-1"></i> Désactiver 2FA
                                </button>
                            </form>
                        </div>

                        <div v-else>
                            <p class="small text-muted mb-3">
                                Activez l'authentification à deux facteurs pour renforcer la sécurité
                                de votre compte. Vous aurez besoin d'une application comme
                                <strong>Google Authenticator</strong> ou <strong>Authy</strong>.
                            </p>
                            <form method="POST" action="/user/two-factor/enable">
                                <input type="hidden" name="_token" :value="csrf" />
                                <button class="btn btn-primary">
                                    <i class="bi-qr-code me-1"></i> Activer 2FA
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sessions actives -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi-laptop me-1"></i> Sessions actives
                        </h6>

                        <div v-if="loadingSessions" class="text-center py-3">
                            <span class="text-muted small">Chargement...</span>
                        </div>

                        <div v-else>
                            <div v-for="s in sessions" :key="s.id" class="border-bottom py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold small">{{ s.user_agent || 'Navigateur inconnu' }}</div>
                                        <div class="text-muted small">
                                            <i class="bi-globe me-1"></i>{{ s.ip_address }}
                                            · {{ s.last_active }}
                                        </div>
                                        <span v-if="s.is_current" class="badge bg-success bg-opacity-10 text-success">Actuelle</span>
                                    </div>
                                    <form v-if="!s.is_current" method="POST" :action="`/user/sessions/${s.id}/revoke`" style="display:inline">
                                        <input type="hidden" name="_token" :value="csrf" />
                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div v-if="!sessions.length" class="text-center text-muted small py-3">
                                Aucune session active.
                            </div>
                            <div class="mt-3">
                                <form method="POST" action="/user/sessions/revoke-others">
                                    <input type="hidden" name="_token" :value="csrf" />
                                    <button class="btn btn-outline-warning btn-sm w-100">
                                        <i class="bi-x-circle me-1"></i> Révoquer toutes les autres sessions
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique connexions -->
                <div class="col-12">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi-clock-history me-1"></i> Dernières connexions
                        </h6>
                        <div v-if="loadingHistory" class="text-center py-3">
                            <span class="text-muted small">Chargement...</span>
                        </div>
                        <div v-else>
                            <table class="table table-sm small mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>IP</th>
                                        <th>Navigateur</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="h in history" :key="h.id">
                                        <td>{{ h.created_at }}</td>
                                        <td><code>{{ h.ip_address }}</code></td>
                                        <td class="text-truncate" style="max-width:300px;">{{ h.user_agent }}</td>
                                        <td>
                                            <span :class="'badge ' + (h.event === 'login' ? 'bg-success' : 'bg-warning')">
                                                {{ h.event }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!history.length">
                                        <td colspan="4" class="text-center text-muted">Aucun historique.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

defineProps(['twoFactorEnabled'])

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const sessions = ref([])
const history = ref([])
const loadingSessions = ref(true)
const loadingHistory = ref(true)

onMounted(async () => {
    try {
        const res = await fetch('/user/sessions');
        sessions.value = await res.json();
    } catch (e) { /* ignore */ }
    loadingSessions.value = false;

    try {
        const res = await fetch('/user/login-history');
        history.value = await res.json();
    } catch (e) { /* ignore */ }
    loadingHistory.value = false;
})
</script>
