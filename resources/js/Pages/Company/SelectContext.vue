<script setup>
import { ref, onMounted } from 'vue';
import { authStore } from '../../stores/auth';

const props = defineProps({
    companies: {
        type: Array,
        default: () => []
    }
});

const loading = ref(false);
const error = ref(null);
const companyList = ref(props.companies || []);

onMounted(async () => {
    if (companyList.value.length === 0 && authStore.companies?.length) {
        companyList.value = authStore.companies;
    }
});

async function selectCompany(clientId) {
    loading.value = true;
    error.value = null;
    try {
        const success = await authStore.switchToCompany(clientId);
        if (!success) {
            error.value = "Impossible de basculer vers cette entreprise.";
        }
    } catch (e) {
        error.value = "Une erreur est survenue.";
    } finally {
        loading.value = false;
    }
}

function logout() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    if (token) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = token;
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
}
</script>

<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center position-relative"
         style="background: linear-gradient(135deg, #000 0%, #1a1a2e 100%);">
        <!-- Logout button -->
        <button class="btn position-absolute top-0 end-0 m-2 p-2 text-white-50 logout-btn"
                title="Déconnexion"
                @click="logout"
                style="font-size: 1.3rem; background: transparent; border: none; z-index: 10;">
            <i class="bi bi-box-arrow-right fs-4"></i>
        </button>

        <div class="card shadow-lg border-0" style="max-width: 520px; width: 100%; border-radius: 16px;">
            <div class="card-body p-5 text-center">
                <!-- Logo / Icon -->
                <div class="mb-4">
                    <div class="mx-auto d-flex align-items-center justify-content-center"
                         style="width: 72px; height: 72px; background: #FF7900; border-radius: 16px;">
                        <i class="bi bi-building fs-1 text-dark"></i>
                    </div>
                </div>

                <h3 class="fw-bold mb-2">Sélectionnez votre entreprise</h3>
                <p class="text-muted mb-4">
                    Vous avez accès à plusieurs entreprises. Choisissez celle que vous souhaitez administrer.
                </p>

                <!-- Error -->
                <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

                <!-- Company List -->
                <div class="d-flex flex-column gap-3">
                    <button v-for="company in companyList"
                            :key="company.id"
                            class="btn btn-outline-secondary text-start p-3 border-2 company-btn"
                            :disabled="loading"
                            @click="selectCompany(company.id)">
                        <div class="d-flex align-items-center">
                            <div class="me-3 d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background: #f5f5f5; border-radius: 12px;">
                                <i class="bi bi-building fs-4" style="color: #FF7900;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold fs-6">{{ company.company_name }}</div>
                                <div class="small text-muted" v-if="company.rccm || company.ifu">
                                    <span v-if="company.rccm">RCCM: {{ company.rccm }}</span>
                                    <span v-if="company.ifu" class="ms-2">IFU: {{ company.ifu }}</span>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right fs-5 text-muted"></i>
                        </div>
                    </button>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="mt-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <span class="ms-2 text-muted">Basculement en cours...</span>
                </div>

                <!-- Empty State -->
                <div v-if="!loading && companyList.length === 0" class="text-muted py-3">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <p>Aucune entreprise trouvée.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.company-btn {
    border-radius: 12px !important;
    transition: all 0.2s;
}
.company-btn:hover:not(:disabled) {
    border-color: #FF7900 !important;
    background: rgba(255, 121, 0, 0.05) !important;
}
</style>
