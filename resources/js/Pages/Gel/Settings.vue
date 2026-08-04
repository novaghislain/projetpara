/*
 * Composant : Settings.vue
 * Description : Page des parametres du compte utilisateur dans le module GEL.
 *              Permet de modifier la photo de profil et le mot de passe
 *              via le composant UserProfileSettings. Affiche les
 *              informations du compte (nom, email, role).
 */
<script setup>
import { ref } from 'vue';
import { authStore } from '../../stores/auth';       /* Store d'authentification pour recuperer l'utilisateur courant */
import GelLayout from '../../Layouts/GelLayout.vue';  /* Layout principal du module GEL */
import UserProfileSettings from '../../Components/UserProfileSettings.vue'; /* Composant : photo + mot de passe */

/* Utilisateur connecte recupere depuis le store */
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
            <!-- Composant reutilisable : photo de profil + mot de passe -->
            <UserProfileSettings />

            <hr class="my-4">

            <!-- Informations du compte : nom, email, role -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <!-- Section Profil : nom, email, role de l'utilisateur -->
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
