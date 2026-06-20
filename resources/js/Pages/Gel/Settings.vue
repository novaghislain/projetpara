<script setup>
import { ref } from 'vue';
import { authStore } from '../../stores/auth';
import GelLayout from '../../Layouts/GelLayout.vue';
import UserProfileSettings from '../../Components/UserProfileSettings.vue';

const user = authStore.user;
</script>

<template>
    <GelLayout page-title="Paramètres">
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;background:#f0f2f5;color:#FF7900;">
                    <i class="bi-gear" style="font-size:20px;"></i>
                </div>
                <div>
                    <div class="h5 fw-bold mb-0">Paramètres du compte</div>
                    <div class="small text-muted">
                        {{ user?.name }} — {{ user?.email }}
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4">
            <!-- Composant réutilisable : photo + mot de passe -->
            <UserProfileSettings />

            <hr class="my-4">

            <!-- Infos du compte -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="bi-person-circle me-2" style="color:#FF7900;"></i>Profil
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div class="fw-medium small text-muted">Nom</div>
                                <div>{{ user?.name || '—' }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div class="fw-medium small text-muted">Email</div>
                                <div>{{ user?.email || '—' }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div class="fw-medium small text-muted">Rôle</div>
                                <div>
                                    <span class="badge bg-info">{{ user?.role || '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="bi-info-circle me-2" style="color:#FF7900;"></i>Informations
                        </div>
                        <div class="text-center py-5 text-muted" style="font-size:13px;">
                            <i class="bi-tools" style="font-size:32px; display:block; margin-bottom:10px; color:#dce3ee;"></i>
                            D'autres options de configuration seront disponibles ici.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
