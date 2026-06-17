<script setup>
import { ref } from 'vue';
import { authStore } from '../../stores/auth';
import GelLayout from '../../Layouts/GelLayout.vue';

const user = authStore.user;
</script>

<template>
    <GelLayout page-title="Paramètres">
        <div class="isup-shell">
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-gear" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">Paramètres du compte</div>
                        <div class="isup-portal-sub">
                            {{ user?.name }} — {{ user?.email }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-person-circle me-2" style="color:#FF7900;"></i>Profil
                            </div>
                            <div class="isup-panel-body">
                                <div class="isup-field-row">
                                    <div class="isup-field-label">Nom</div>
                                    <div class="isup-field-val">{{ user?.name || '—' }}</div>
                                </div>
                                <div class="isup-field-row">
                                    <div class="isup-field-label">Email</div>
                                    <div class="isup-field-val">{{ user?.email || '—' }}</div>
                                </div>
                                <div class="isup-field-row">
                                    <div class="isup-field-label">Rôle</div>
                                    <div class="isup-field-val">
                                        <span class="isup-status isup-status-blue">{{ user?.role || '—' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-info-circle me-2" style="color:#FF7900;"></i>Informations
                            </div>
                            <div class="isup-panel-body text-center py-5 text-muted" style="font-size:13px;">
                                <i class="bi-tools" style="font-size:32px; display:block; margin-bottom:10px; color:#dce3ee;"></i>
                                D'autres options de configuration seront disponibles ici.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ══ Settings — unique styles ══ */
.gel-settings-trigger {
    background: #fff;
    border: 2px dashed #dce3ee;
    border-radius: 4px;
    padding: 10px 18px;
    font-size: 12px;
    font-weight: 600;
    color: #888;
    cursor: pointer;
    transition: all 0.15s;
    width: 100%;
    text-align: left;
}
.gel-settings-trigger:hover { border-color: #FF7900; color: #FF7900; }
</style>
