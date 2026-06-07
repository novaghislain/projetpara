<script setup>
import { ref } from 'vue';
import { 
    Settings, 
    Bell, 
    Shield, 
    Globe, 
    Save,
    Palette
} from 'lucide-vue-next';

const settings = ref({
    siteName: 'Victoire Parapharmacie',
    currency: 'FCFA',
    emailNotifications: true,
    ordersAlerts: true,
    maintenanceMode: false
});
</script>

<template>
    <AdminLayout>
        <div class="mb-4">
            <h2 class="fw-bold mb-1">Paramètres du Système</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="/admin" class="text-muted">Admin</a></li>
                    <li class="breadcrumb-item active text-primary">Paramètres</li>
                </ol>
            </nav>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <Globe :size="20" class="text-primary" /> Général
                    </h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Nom du Site</label>
                        <input type="text" v-model="settings.siteName" class="form-control bg-light border-0 px-3 py-2" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Devise</label>
                        <select v-model="settings.currency" class="form-select bg-light border-0 px-3 py-2">
                            <option value="FCFA">FCFA</option>
                            <option value="EUR">Euro (€)</option>
                            <option value="USD">Dollar ($)</option>
                        </select>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <Bell :size="20" class="text-primary" /> Notifications
                    </h5>
                    <div class="form-check form-switch mb-3">
                        <input border-0 class="form-check-input" type="checkbox" v-model="settings.emailNotifications" id="emailNotif">
                        <label class="form-check-label" for="emailNotif">Notifications par email</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input border-0 class="form-check-input" type="checkbox" v-model="settings.ordersAlerts" id="orderNotif">
                        <label class="form-check-label" for="orderNotif">Alertes pour nouvelles commandes</label>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <Save :size="20" class="text-primary" /> Enregistrer
                    </h5>
                    <p class="text-muted small">Mettez à jour les paramètres globaux de votre plateforme.</p>
                    <button class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-lg">
                        Enregistrer les modifications
                    </button>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-danger">
                        <Shield :size="20" /> Maintenance
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" v-model="settings.maintenanceMode" id="maintenance">
                        <label class="form-check-label text-danger fw-bold" for="maintenance">Mode Maintenance</label>
                    </div>
                    <p class="text-muted x-small mt-2 mb-0">Rendra le site inaccessible aux clients pendant les travaux.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.x-small { font-size: 0.75rem; }
</style>
