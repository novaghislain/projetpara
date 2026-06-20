<script setup>
import { ref, computed, onMounted } from 'vue';
import { authStore } from '../stores/auth';

const emit = defineEmits(['updated']);

const user = computed(() => authStore.user);
const loading = ref(true);
const saving = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// ── Photo de profil ──
const photoUrl = ref(null);
const photoUploading = ref(false);
const fileInput = ref(null);

async function loadProfile() {
    loading.value = true;
    try {
        const res = await fetch('/api/me', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.user) {
            photoUrl.value = data.user.photo_url || null;
        }
    } catch (e) {
        console.error('Erreur chargement profil', e);
    } finally {
        loading.value = false;
    }
}

function triggerFileInput() {
    fileInput.value?.click();
}

async function handleFileChange(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    // Preview locale
    const reader = new FileReader();
    reader.onload = (e) => {
        photoUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);

    // Upload
    photoUploading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    const formData = new FormData();
    formData.append('photo', file);

    try {
        const res = await fetch('/api/me/photo', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
            body: formData,
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur lors du téléchargement');
        photoUrl.value = data.photo_url;
        successMessage.value = data.message || 'Photo mise à jour.';
        emit('updated');
    } catch (e) {
        errorMessage.value = e.message;
        // Recharger l'URL d'origine
        loadProfile();
    } finally {
        photoUploading.value = false;
        if (fileInput.value) fileInput.value.value = '';
    }
}

async function removePhoto() {
    if (!confirm('Supprimer votre photo de profil ?')) return;
    errorMessage.value = '';
    successMessage.value = '';
    try {
        const res = await fetch('/api/me/photo', {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        photoUrl.value = null;
        successMessage.value = data.message || 'Photo supprimée.';
        emit('updated');
    } catch (e) {
        errorMessage.value = e.message;
    }
}

// ── Mot de passe ──
const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const passwordSaving = ref(false);
const passwordErrors = ref({});

async function updatePassword() {
    passwordSaving.value = true;
    passwordErrors.value = {};
    successMessage.value = '';
    errorMessage.value = '';

    try {
        const res = await fetch('/api/me/password', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
            body: JSON.stringify(passwordForm.value),
        });
        const data = await res.json();
        if (!res.ok) {
            if (data.errors) {
                passwordErrors.value = data.errors;
            }
            throw new Error(data.message || 'Erreur lors de la mise à jour du mot de passe');
        }
        successMessage.value = data.message || 'Mot de passe mis à jour avec succès.';
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (e) {
        if (Object.keys(passwordErrors.value).length === 0) {
            errorMessage.value = e.message;
        }
    } finally {
        passwordSaving.value = false;
    }
}

onMounted(loadProfile);
</script>

<template>
    <div class="row g-4">
        <!-- Colonne de gauche : Photo de profil -->
        <div class="col-lg-5">
            <div class="isup-panel">
                <div class="isup-panel-header">
                    <i class="bi-camera me-2" style="color:#FF7900;"></i>Photo de profil
                </div>
                <div class="isup-panel-body text-center">
                    <!-- Aperçu photo -->
                    <div class="profile-photo-wrap mb-3">
                        <div v-if="photoUrl" class="profile-photo"
                             :style="{ backgroundImage: 'url(' + photoUrl + ')' }">
                        </div>
                        <div v-else class="profile-photo profile-photo-empty">
                            <i class="bi-person" style="font-size:36px; color:#aaa;"></i>
                        </div>
                        <div v-if="photoUploading" class="profile-photo-spinner">
                            <div class="isup-spinner"></div>
                        </div>
                    </div>

                    <input type="file" ref="fileInput"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           @change="handleFileChange"
                           style="display:none;">

                    <button class="isup-btn-primary mb-2 w-100" @click="triggerFileInput" :disabled="photoUploading">
                        <i class="bi-upload me-1"></i>Changer la photo
                    </button>
                    <button v-if="photoUrl" class="isup-btn-grey w-100" @click="removePhoto" :disabled="photoUploading">
                        <i class="bi-trash me-1"></i>Supprimer
                    </button>

                    <p class="text-muted mt-2" style="font-size:10px;">
                        Formats : JPG, PNG, WebP — max 2 Mo
                    </p>
                </div>
            </div>
        </div>

        <!-- Colonne de droite : Mot de passe -->
        <div class="col-lg-7">
            <div class="isup-panel">
                <div class="isup-panel-header">
                    <i class="bi-lock me-2" style="color:#FF7900;"></i>Mot de passe
                </div>
                <div class="isup-panel-body">
                    <!-- Messages -->
                    <div v-if="successMessage" class="isup-alert-success mb-3">
                        <i class="bi-check-circle-fill me-2"></i>{{ successMessage }}
                    </div>
                    <div v-if="errorMessage" class="isup-alert-error mb-3">
                        <i class="bi-exclamation-triangle-fill me-2"></i>{{ errorMessage }}
                    </div>

                    <form @submit.prevent="updatePassword">
                        <div class="mb-3">
                            <label class="isup-label">Mot de passe actuel</label>
                            <input type="password" class="isup-input"
                                   v-model="passwordForm.current_password" required
                                   :class="{ 'isup-input-error': passwordErrors.current_password }">
                            <div v-if="passwordErrors.current_password" class="input-error-text">
                                {{ passwordErrors.current_password[0] }}
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="isup-label">Nouveau mot de passe</label>
                                <input type="password" class="isup-input"
                                       v-model="passwordForm.password" required
                                       :class="{ 'isup-input-error': passwordErrors.password }"
                                       minlength="8">
                                <div v-if="passwordErrors.password" class="input-error-text">
                                    {{ passwordErrors.password[0] }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="isup-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="isup-input"
                                       v-model="passwordForm.password_confirmation" required
                                       :class="{ 'isup-input-error': passwordErrors.password_confirmation }"
                                       minlength="8">
                                <div v-if="passwordErrors.password_confirmation" class="input-error-text">
                                    {{ passwordErrors.password_confirmation[0] }}
                                </div>
                            </div>
                        </div>

                        <div class="isup-info-box mb-3">
                            <i class="bi-info-circle me-1"></i>
                            Le mot de passe doit contenir au moins 8 caractères.
                        </div>

                        <button type="submit" class="isup-btn-primary" :disabled="passwordSaving">
                            <span v-if="passwordSaving" class="isup-spinner-sm me-1"></span>
                            <i v-else class="bi-check2 me-1"></i>
                            {{ passwordSaving ? 'Mise à jour...' : 'Modifier le mot de passe' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-photo-wrap {
    position: relative;
    display: flex;
    justify-content: center;
}
.profile-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    background-color: #f0f4f8;
    border: 3px solid #dce3ee;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.2s;
}
.profile-photo:hover {
    border-color: #FF7900;
}
.profile-photo-empty {
    background: #f5f7fa;
    border: 3px dashed #dce3ee;
}
.profile-photo-spinner {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.7);
    border-radius: 50%;
    width: 120px;
    height: 120px;
}
.isup-input-error {
    border-color: #e53935 !important;
    box-shadow: 0 0 0 2px rgba(229,57,53,0.12) !important;
}
.input-error-text {
    font-size: 11px;
    color: #e53935;
    margin-top: 2px;
}
</style>
